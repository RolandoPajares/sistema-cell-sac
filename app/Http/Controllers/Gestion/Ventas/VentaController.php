<?php

namespace App\Http\Controllers\Gestion\Ventas;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActualizarVentaRequest;
use App\Http\Requests\AnularVentaRequest;
use App\Http\Requests\ConfirmarVentaCotizacionRequest;
use App\Http\Requests\ConvertirCotizacionRequest;
use App\Http\Requests\FiltroVentaRequest;
use App\Http\Requests\GuardarVentaRequest;
use App\Models\Catalogo\Producto;
use App\Models\Clientes\Cliente;
use App\Models\Comercio\Cotizacion;
use App\Models\Comercio\Venta;
use App\Models\Finanzas\MedioPago;
use App\Models\Organizacion\Sede;
use App\Servicios\ConsultaVentas;
use App\Servicios\ExportadorTabular;
use App\Servicios\RegistradorAuditoria;
use App\Servicios\ServicioAutorizacion;
use App\Servicios\ServicioCotizaciones;
use App\Servicios\ServicioVentas;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Define la responsabilidad de la clase VentaController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class VentaController extends Controller
{
    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @return mixed Resultado de la operación.
     */
    public function __construct(
        private readonly ConsultaVentas $ventas,
        private readonly ServicioVentas $registro,
        private readonly ServicioAutorizacion $autorizacion,
        private readonly ServicioCotizaciones $cotizaciones,
    ) {}

    /**
     * Obtiene y prepara la información solicitada para la operación.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function index(FiltroVentaRequest $request): View
    {
        $filtros = $this->filters($request->validated());
        $query = $this->ventas->query($request->user(), $filtros);
        $estados = $this->ventas->query($request->user(), array_replace($filtros, ['estado' => null]))
            ->select('ventas.estado')->distinct()->orderBy('ventas.estado')->pluck('estado');
        $customers = collect();
        $products = collect();
        $canCreate = false;
        $canUpdate = $this->autorizacion->allows($request->user(), 'gestion.ventas.actualizar');
        $editOrder = null;
        if (Schema::hasColumns('ventas', ['canal', 'observaciones'])
            && ($this->autorizacion->allows($request->user(), 'gestion.ventas.crear') || $canUpdate)) {
            try {
                $ubicacion = $this->autorizacion->operationLocation($request->user());
                $ubicacionValida = $ubicacion['empresa_id'] !== null && $ubicacion['sede_id'] !== null && $ubicacion['almacen_id'] !== null;
                $canCreate = $ubicacionValida && $this->autorizacion->allows($request->user(), 'gestion.ventas.crear')
                    && $this->autorizacion->allowsRecord($request->user(), 'gestion.ventas.crear', $ubicacion['empresa_id'], $ubicacion['sede_id']);
                $canUpdate = $ubicacionValida && $canUpdate
                    && $this->autorizacion->allowsRecord($request->user(), 'gestion.ventas.actualizar', $ubicacion['empresa_id'], $ubicacion['sede_id']);
            } catch (HttpExceptionInterface) {
                $canCreate = false;
                $canUpdate = false;
            }
            if ($canCreate || $canUpdate) {
                $customers = Cliente::query()->where('empresa_id', $ubicacion['empresa_id'])->where('activo', true)->orderBy('razon_social')->orderBy('id')->get();
                $saldos = DB::table('existencias_almacen')->where('empresa_id', $ubicacion['empresa_id'])
                    ->where('sede_id', $ubicacion['sede_id'])->where('almacen_id', $ubicacion['almacen_id'])
                    ->selectRaw('producto_id, MIN(cantidad_fisica - cantidad_reservada) AS disponible')
                    ->groupBy('producto_id')->havingRaw('COUNT(*) = 1')->get()->keyBy('producto_id');
                $products = Producto::query()->active()->where('empresa_id', $ubicacion['empresa_id'])
                    ->whereIn('id', $saldos->keys())->orderBy('nombre')->get();
                $products->each(static function (Producto $producto) use ($saldos): void {
                    $producto->existencias = max(0, (int) floor((float) $saldos->get($producto->id)->disponible));
                });
                if ($canCreate && ! is_string($request->session()->get('venta_token'))) {
                    $request->session()->put('venta_token', bin2hex(random_bytes(32)));
                }
                if ($request->filled('editar')) {
                    abort_unless(Schema::hasColumns('ventas', ['pedido_id', 'descuento', 'impuestos']), 404);
                    abort_unless($canUpdate && ctype_digit((string) $request->query('editar')), 404);
                    $editOrder = $this->autorizacion->scopeRecordsFor(
                        Venta::query()->whereKey((int) $request->query('editar'))->where('ventas.estado', 'borrador')
                            ->where('ventas.canal', 'presencial')->whereNull('ventas.pedido_id')
                            ->where('ventas.descuento', 0)->where('ventas.impuestos', 0),
                        $request->user(), 'gestion.ventas.actualizar',
                    )->where('ventas.empresa_id', $ubicacion['empresa_id'])->where('ventas.sede_id', $ubicacion['sede_id'])
                        ->where('ventas.almacen_id', $ubicacion['almacen_id'])->with(['detalles.producto', 'cliente', 'usuario'])->first();
                    abort_unless($editOrder !== null, 404);
                }
            }
        }

        $orders = (clone $query)->with(['cliente', 'pedido:id,canal', 'detalles'])->withCount('detalles')
            ->orderByDesc('ventas.fecha_venta')->orderByDesc('ventas.id')->paginate(10)->withQueryString();
        $cancelables = $this->autorizacion->scopeRecordsFor(
            Venta::query()->whereIn('ventas.id', $orders->getCollection()->modelKeys()), $request->user(), 'gestion.ventas.anular',
        )->pluck('ventas.id')->all();
        $editables = [];
        if (Schema::hasColumns('ventas', ['canal', 'pedido_id', 'descuento', 'impuestos'])) {
            $editables = $this->autorizacion->scopeRecordsFor(
                Venta::query()->whereIn('ventas.id', $orders->getCollection()->modelKeys())->where('ventas.estado', 'borrador')
                    ->where('ventas.canal', 'presencial')->whereNull('ventas.pedido_id')
                    ->where('ventas.descuento', 0)->where('ventas.impuestos', 0),
                $request->user(), 'gestion.ventas.actualizar',
            )->pluck('ventas.id')->all();
        }
        $confirmables = [];
        if ($this->autorizacion->allows($request->user(), 'gestion.ventas.crear')
            && Schema::hasColumns('ventas', ['pedido_id', 'canal'])) {
            $confirmables = $this->autorizacion->scopeRecordsFor(
                Venta::query()->whereIn('ventas.id', $orders->getCollection()->modelKeys())
                    ->where('ventas.estado', 'borrador')->whereNotNull('ventas.pedido_id')->where('ventas.canal', 'presencial'),
                $request->user(), 'gestion.ventas.crear',
            )->pluck('ventas.id')->all();
        }
        $dispositivosPorDetalle = collect();
        if ($confirmables !== [] && Schema::hasColumns('dispositivos', ['id', 'producto_id', 'variante_id', 'almacen_actual_id', 'estado'])) {
            foreach ($orders->getCollection()->whereIn('id', $confirmables) as $ventaPendiente) {
                foreach ($ventaPendiente->detalles as $detalle) {
                    $varianteId = $detalle->variante_id === null ? null : (int) $detalle->variante_id;
                    $dispositivosDisponibles = DB::table('dispositivos')->where('producto_id', $detalle->producto_id)
                        ->when($varianteId === null, fn ($consulta) => $consulta->whereNull('variante_id'), fn ($consulta) => $consulta->where('variante_id', $varianteId))
                        ->where('almacen_actual_id', $ventaPendiente->almacen_id)->where('estado', 'disponible')
                        ->orderBy('id')->get(['id', 'imei', 'numero_serie']);
                    $requiereDispositivos = DB::table('dispositivos')->where('producto_id', $detalle->producto_id)
                        ->when($varianteId === null, fn ($consulta) => $consulta->whereNull('variante_id'), fn ($consulta) => $consulta->where('variante_id', $varianteId))->exists()
                        || ($varianteId !== null
                        && (bool) DB::table('variantes_productos')->where('id', $varianteId)->value('controla_serie'));
                    $dispositivosPorDetalle->put($detalle->getKey(), [
                        'requiere' => $requiereDispositivos, 'dispositivos' => $dispositivosDisponibles,
                    ]);
                }
            }
        }
        $filtrosDisponibles = array_replace($filtros, ['cliente_id' => null, 'sede_id' => null]);
        $clientesDisponibles = Cliente::query()->whereIn('id', $this->ventas->query($request->user(), $filtrosDisponibles)->select('ventas.cliente_id'))
            ->orderBy('razon_social')->orderBy('id')->get(['id', 'razon_social', 'nombre_contacto']);
        $sedesDisponibles = Sede::query()->whereIn('id', $this->ventas->query($request->user(), $filtrosDisponibles)->select('ventas.sede_id'))
            ->orderBy('nombre')->orderBy('id')->get(['id', 'nombre']);

        return view('gestion.comercial.ventas.listar', [
            ...$this->ventas->resumen($request->user(), $filtros),
            'filters' => $filtros,
            'estados' => $estados,
            'orders' => $orders,
            'cancelables' => $cancelables,
            'editables' => $editables,
            'confirmables' => $confirmables,
            'dispositivosPorDetalle' => $dispositivosPorDetalle,
            'clientesDisponibles' => $clientesDisponibles,
            'sedesDisponibles' => $sedesDisponibles,
            'latestSales' => (clone $query)->with('cliente')->orderByDesc('ventas.fecha_venta')->orderByDesc('ventas.id')->limit(5)->get(),
            'customers' => $customers,
            'products' => $products,
            'currentUserName' => $request->user()?->nombre ?? '',
            'defaultSaleDate' => now()->toDateString(),
            'canCreate' => $canCreate,
            'editOrder' => $editOrder,
        ]);
    }

    /**
     * Ejecuta la operación panelMayorista definida por la clase VentaController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function panelMayorista(Request $request): View
    {
        return $this->panelPorTipoCliente($request, 'mayorista');
    }

    /**
     * Ejecuta la operación panelMinorista definida por la clase VentaController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function panelMinorista(Request $request): View
    {
        return $this->panelPorTipoCliente($request, 'minorista');
    }

    /**
     * Ejecuta la operación panelPorTipoCliente definida por la clase VentaController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $tipoCliente  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    private function panelPorTipoCliente(Request $request, string $tipoCliente): View
    {
        $usuarioActual = $request->user();
        abort_unless($usuarioActual !== null, 403);
        $this->autorizacion->assertAllowed($usuarioActual, 'gestion.ventas.consultar');

        $filtros = $request->validate([
            'buscar' => ['nullable', 'string', 'max:120'],
            'estado' => ['nullable', 'string', 'max:30'],
            'canal' => ['nullable', 'in:ecommerce,presencial'],
            'pago' => ['nullable', 'integer', 'min:1', 'exists:medios_pago,id'],
        ]);
        $consultaBase = $this->ventas->query($usuarioActual, [])->whereHas('cliente', fn ($consulta) => $consulta->where('tipo', $tipoCliente));
        $consultaFiltrada = $this->ventas->query($usuarioActual, $filtros)->whereHas('cliente', fn ($consulta) => $consulta->where('tipo', $tipoCliente));
        if (isset($filtros['pago'])) {
            $consultaFiltrada->whereHas('pagos', fn ($consulta) => $consulta->where('medio_pago_id', $filtros['pago']));
        }
        $ventasDelMes = (clone $consultaBase)->whereBetween('ventas.fecha_venta', [now()->startOfMonth(), now()->endOfMonth()]);
        $cantidadDelMes = (clone $ventasDelMes)->count();
        $ventasDelMesEnSoles = (clone $ventasDelMes)->where('ventas.moneda', 'PEN');
        $cantidadVentasDelMesEnSoles = (clone $ventasDelMesEnSoles)->count();
        $importeDelMes = (float) (clone $ventasDelMesEnSoles)->sum('ventas.total');
        $importeMesAnterior = (float) (clone $consultaBase)->where('ventas.moneda', 'PEN')
            ->whereBetween('ventas.fecha_venta', [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()])->sum('ventas.total');
        $ventasMesAnterior = (clone $consultaBase)->whereBetween('ventas.fecha_venta', [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()])->count();
        $tendenciaImporte = $importeMesAnterior > 0 ? round((($importeDelMes - $importeMesAnterior) / $importeMesAnterior) * 100, 1).'%' : 'Sin comparación';
        $tendenciaCantidad = $ventasMesAnterior > 0 ? round((($cantidadDelMes - $ventasMesAnterior) / $ventasMesAnterior) * 100, 1).'%' : 'Sin comparación';

        $ventas = (clone $consultaFiltrada)->with(['cliente', 'usuario', 'pedido', 'pagos.medioPago'])
            ->orderByDesc('ventas.fecha_venta')->orderByDesc('ventas.id')->limit(10)->get();
        $estados = (clone $consultaBase)->select('ventas.estado')->distinct()->orderBy('ventas.estado')->pluck('estado');
        $mediosPago = MedioPago::query()->whereHas('pagos', fn ($consulta) => $consulta->whereIn('venta_id', (clone $consultaBase)->select('ventas.id')))
            ->orderBy('nombre')->get(['id', 'nombre']);
        $canales = collect();
        if (Schema::hasColumn('ventas', 'canal')) {
            $canales = (clone $consultaBase)->whereNotNull('ventas.canal')->distinct()->orderBy('ventas.canal')->pluck('ventas.canal');
        } elseif (Schema::hasColumn('pedidos', 'canal')) {
            $canales = (clone $consultaBase)->whereHas('pedido', fn ($consulta) => $consulta->whereNotNull('canal'))
                ->join('pedidos', 'pedidos.id', '=', 'ventas.pedido_id')->distinct()->orderBy('pedidos.canal')->pluck('pedidos.canal');
        }
        $consultaCanales = (clone $consultaBase)->leftJoin('pedidos', 'pedidos.id', '=', 'ventas.pedido_id');
        $campoCanal = Schema::hasColumn('ventas', 'canal') ? 'COALESCE(ventas.canal, pedidos.canal)' : 'pedidos.canal';
        $resumenCanales = $consultaCanales->selectRaw($campoCanal.' AS canal, COUNT(*) AS cantidad')
            ->groupByRaw($campoCanal)->orderByDesc('cantidad')->get();
        $conteosEstado = (clone $consultaBase)->select('ventas.estado')->selectRaw('COUNT(*) AS cantidad')
            ->groupBy('ventas.estado')->orderBy('ventas.estado')->get();
        $ventasPorCliente = DB::table('ventas')->join('clientes', 'clientes.id', '=', 'ventas.cliente_id')
            ->whereIn('ventas.id', (clone $consultaBase)->select('ventas.id'))
            ->select('clientes.razon_social', 'clientes.nombre_contacto', 'ventas.moneda')
            ->selectRaw('SUM(ventas.total) AS importe')
            ->groupBy('clientes.id', 'clientes.razon_social', 'clientes.nombre_contacto', 'ventas.moneda')
            ->orderByDesc('importe')->limit(5)->get();

        $datos = [
            'titulo' => $tipoCliente === 'mayorista' ? 'Ventas mayoristas' : 'Ventas minoristas', 'icono' => $tipoCliente === 'mayorista' ? 'bi-people' : 'bi-shop',
            'descripcion' => 'Consulta las ventas registradas para clientes '.($tipoCliente === 'mayorista' ? 'mayoristas.' : 'minoristas.'),
            'acciones' => [['texto' => 'Nueva venta '.($tipoCliente === 'mayorista' ? 'mayorista' : 'minorista'), 'icono' => 'bi-plus-lg', 'primaria' => true, 'ruta' => route('management.sales')], ['texto' => 'Nueva cotización', 'icono' => 'bi-file-earmark-text', 'ruta' => route('management.quotations')]],
            'datos_reales' => true,
            'metricas' => [
                ['titulo' => 'Ingresos PEN del mes', 'valor' => 'PEN '.number_format($importeDelMes, 2), 'icono' => 'bi-coin', 'tendencia' => $tendenciaImporte],
                ['titulo' => 'Ventas del mes', 'valor' => (string) $cantidadDelMes, 'icono' => 'bi-cart3', 'tono' => 'verde', 'tendencia' => $tendenciaCantidad],
                ['titulo' => $tipoCliente === 'mayorista' ? 'Clientes empresariales activos' : 'Clientes minoristas activos', 'valor' => (string) (clone $consultaBase)->whereHas('cliente', fn ($consulta) => $consulta->where('activo', true))->distinct('ventas.cliente_id')->count('ventas.cliente_id'), 'icono' => $tipoCliente === 'mayorista' ? 'bi-buildings' : 'bi-people', 'tono' => 'rojo', 'tendencia' => 'Dato actual'],
                ['titulo' => 'Ticket promedio PEN del mes', 'valor' => 'PEN '.number_format($cantidadVentasDelMesEnSoles > 0 ? $importeDelMes / $cantidadVentasDelMesEnSoles : 0, 2), 'icono' => 'bi-tag', 'tono' => 'violeta', 'tendencia' => 'Dato actual'],
            ],
            'buscar' => 'Buscar por cliente, código de venta o asesor…',
            'filtros' => [
                ['titulo' => 'Canal', 'campo' => 'canal', 'todos' => 'Todos los canales', 'opciones' => $canales->all()],
                ['titulo' => 'Estado', 'campo' => 'estado', 'todos' => 'Todos los estados', 'opciones' => $estados->all()],
                ['titulo' => 'Medio de pago', 'campo' => 'pago', 'todos' => 'Todos los medios de pago', 'opciones' => $mediosPago->map(fn (MedioPago $medio): array => ['valor' => (string) $medio->id, 'etiqueta' => $medio->nombre])->all()],
            ],
            'tablaTitulo' => 'Ventas '.($tipoCliente === 'mayorista' ? 'mayoristas' : 'minoristas').' registradas', 'tablaIcono' => 'bi-cart3',
            'total' => (clone $consultaFiltrada)->count(),
            'columnas' => ['Venta', 'Empresa / cliente', 'Asesor', 'Fecha', 'Total', 'Medio de pago', 'Estado'],
            'filas' => $ventas->map(static function (Venta $venta): array {
                $cliente = $venta->cliente?->razon_social ?: $venta->cliente?->nombre_contacto ?: 'Sin cliente asociado';
                $estado = (string) $venta->estado;
                $canal = $venta->canal ?? $venta->pedido?->canal ?? '';

                return [
                    'celdas' => [
                        ['texto' => $venta->codigo, 'tono' => 'azul'], $cliente,
                        $venta->usuario?->nombre ?? 'Sin asesor registrado',
                        $venta->fecha_venta?->format('d/m/Y') ?? '—',
                        $venta->moneda.' '.number_format((float) $venta->total, 2),
                        ['texto' => $venta->pagos->pluck('medioPago.nombre')->filter()->unique()->join(', ') ?: 'Sin pago registrado', 'tono' => 'azul'],
                        ['texto' => $estado, 'tono' => in_array(mb_strtolower($estado), ['confirmada', 'completada'], true) ? 'verde' : 'ambar'],
                    ],
                    'filtros' => ['canal' => $canal, 'estado' => $estado, 'pago' => $venta->pagos->pluck('medio_pago_id')->unique()->implode(',')],
                ];
            })->all(),
            'formTitulo' => 'Nueva venta '.($tipoCliente === 'mayorista' ? 'mayorista' : 'minorista'), 'formIcono' => 'bi-cart3', 'formAccion' => 'Abrir formulario de venta',
            'formCampos' => [['etiqueta' => 'Empresa / cliente', 'ayuda' => 'Los clientes mayoristas se consultan desde la gestión de ventas'], ['etiqueta' => 'Productos', 'ayuda' => 'Los productos y existencias se consultan desde la gestión de ventas'], ['etiqueta' => 'Cantidad', 'ayuda' => 'Completa la venta en el formulario autorizado', 'tipo' => 'input']],
            'rutaFormulario' => route('management.sales'),
            'anillo' => ['titulo' => 'Ventas por canal', 'icono' => 'bi-bar-chart', 'valor' => 'PEN '.number_format($importeDelMes, 2), 'subtitulo' => 'Ingresos registrados este mes', 'gradiente' => 'conic-gradient(#178bfa 0 100%)', 'filas' => $resumenCanales->isEmpty() ? [['Sin canal registrado', '0']] : $resumenCanales->map(fn ($fila): array => [$fila->canal ?: 'Sin canal registrado', (string) $fila->cantidad])->all()],
            'grafico' => ['titulo' => 'Ventas por estado', 'icono' => 'bi-bar-chart', 'barras' => $conteosEstado->isEmpty() ? [['Sin registros', '0', 0]] : $conteosEstado->map(fn ($fila): array => [$fila->estado, (string) $fila->cantidad, (int) round(((int) $fila->cantidad / max(1, (int) $conteosEstado->max('cantidad'))) * 100)])->all()],
            'ranking' => ['titulo' => 'Clientes con mayor venta registrada', 'icono' => 'bi-trophy', 'filas' => $ventasPorCliente->isEmpty() ? [['Sin registros', 'PEN 0.00']] : $ventasPorCliente->map(fn (object $fila): array => [$fila->razon_social ?: $fila->nombre_contacto, $fila->moneda.' '.number_format((float) $fila->importe, 2)])->all()],
        ];

        $vista = $tipoCliente === 'mayorista'
            ? 'gestion.comercial.ventas.mayoristas.listar'
            : 'gestion.comercial.ventas.minoristas.listar';

        return view($vista, compact('datos', 'filtros'));
    }

    /**
     * Registra la información recibida y coordina su persistencia.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function store(GuardarVentaRequest $request): RedirectResponse
    {
        abort_unless(Schema::hasColumns('ventas', ['canal', 'observaciones']), 503, 'La migración de canal y observaciones está pendiente de autorización y aplicación.');
        $datos = $request->validated();
        $venta = $this->registro->registrarFisica($datos, $request->user(), $datos['venta_token'], $request->ip());
        $request->session()->forget('venta_token');

        return redirect()->route('management.sales')->with('status', 'Venta '.$venta->codigo.' confirmada. No se registró ningún pago.');
    }

    /**
     * Ejecuta la operación convertirCotizacion definida por la clase VentaController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $quotation  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function convertirCotizacion(ConvertirCotizacionRequest $request, Cotizacion $quotation): RedirectResponse
    {
        $venta = $this->cotizaciones->convertirAventa($quotation, (int) $request->validated('almacen_id'), $request->user(), $request->ip());

        return redirect()->route('management.sales')->with('status', 'La cotizacion se convirtio en la venta '.$venta->codigo.' como borrador. No se descontaron existencias.');
    }

    /**
     * Ejecuta la operación confirmarBorrador definida por la clase VentaController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $venta  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function confirmarBorrador(ConfirmarVentaCotizacionRequest $request, Venta $venta): RedirectResponse
    {
        $ventaConfirmada = $this->registro->confirmarBorradorCotizacion(
            $venta,
            $request->user(),
            $request->ip(),
            $request->validated('dispositivos') ?? [],
        );

        return redirect()->route('management.sales')->with('status', 'La venta '.$ventaConfirmada->codigo.' fue confirmada y sus existencias se descontaron una sola vez. No se registro pago.');
    }

    /**
     * Actualiza la información existente con los datos recibidos.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $order  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function update(ActualizarVentaRequest $request, Venta $order): RedirectResponse
    {
        $venta = $this->registro->actualizarBorrador($order, $request->validated(), $request->user(), $request->ip());

        return redirect()->route('management.sales')->with('status', 'Se actualizaron los datos de la venta '.$venta->codigo.'.');
    }

    /**
     * Modifica el estado o elimina el registro según la operación solicitada.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $venta  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function cancel(AnularVentaRequest $request, Venta $venta): RedirectResponse
    {
        $this->registro->anular($venta, $request->user(), $request->validated('motivo'), $request->ip());

        return redirect()->route('management.sales')->with('status', 'La venta está anulada.');
    }

    /**
     * Genera la salida exportable de la información disponible.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $format  Parámetro recibido por la operación.
     * @param  mixed  $exporter  Parámetro recibido por la operación.
     * @param  mixed  $audit  Parámetro recibido por la operación.
     * @return Response|StreamedResponse Resultado de la operación.
     */
    public function export(FiltroVentaRequest $request, string $format = 'csv', ?ExportadorTabular $exporter = null, ?RegistradorAuditoria $audit = null): Response|StreamedResponse
    {
        abort_unless(in_array($format, ['csv', 'excel', 'pdf'], true), 422, 'Formato de exportación no permitido.');
        $ventas = $this->ventas->query($request->user(), $this->filters($request->validated()))
            ->with(['cliente:id,razon_social,nombre_contacto', 'pedido:id,canal'])
            ->orderBy('ventas.fecha_venta')->orderBy('ventas.id')->limit(5000)->get();
        $safe = static function (?string $value): string {
            $value ??= '';

            return preg_match('/^[\s\x00-\x1f]*[=+\-@]/u', $value) ? "'".$value : $value;
        };
        $rows = $ventas->map(fn (Venta $venta): array => [
            $safe($venta->codigo),
            $venta->fecha_venta?->toDateTimeString(),
            $safe($venta->cliente?->razon_social ?: $venta->cliente?->nombre_contacto),
            $venta->total,
            $safe($venta->moneda),
            $safe($venta->estado),
            $safe($venta->canal ?: $venta->pedido?->canal),
        ])->all();
        $filters = $this->filters($request->validated());
        $response = ($exporter ?? app(ExportadorTabular::class))->download($format, 'ventas-'.now()->format('Ymd'),
            ['Código', 'Fecha', 'Cliente', 'Total', 'Moneda', 'Estado', 'Canal'],
            $rows,
            [
                'title' => 'REPORTE DE VENTAS',
                'subtitle' => 'Ventas reales consultadas desde MariaDB.',
                'module' => 'Operaciones Comerciales',
                'report_name' => 'Ventas',
                'filters' => $filters,
                'generated_by' => (string) $request->user()->nombre,
                'role' => 'Usuario autorizado',
                'scope' => 'Empresa, sede, almacén y permisos RBAC vigentes.',
                'indicators' => [['label' => 'Ventas incluidas', 'value' => count($rows)]],
            ]);
        ($audit ?? app(RegistradorAuditoria::class))->record($request->user()->getKey(), 'exportar', 'ventas', null, null, ['formato' => $format, 'filtros' => $filters, 'cantidad' => count($rows)], $request->ip());

        return $response;
    }

    /**
     * Ejecuta la operación filters definida por la clase VentaController.
     *
     * @param  mixed  $input  Parámetro recibido por la operación.
     * @return array Resultado de la operación.
     */
    private function filters(array $input): array
    {
        $today = CarbonImmutable::today();
        $to = $this->parseDate((string) ($input['hasta'] ?? ''), $today);
        $defaultFrom = filled($input['hasta'] ?? null) ? $to->subDays(29) : $today->subDays(29);
        $from = $this->parseDate((string) ($input['desde'] ?? ''), $defaultFrom);
        if ($from->greaterThan($to) && filled($input['desde'] ?? null)) {
            $to = $from;
        }
        $canal = (string) ($input['canal'] ?? '');

        return [
            'buscar' => mb_substr(trim((string) ($input['buscar'] ?? '')), 0, 120) ?: null,
            'estado' => mb_substr(trim((string) ($input['estado'] ?? '')), 0, 30) ?: null,
            'canal' => in_array($canal, ['ecommerce', 'presencial'], true) ? $canal : null,
            'cliente_id' => (int) ($input['cliente_id'] ?? 0) > 0 ? (int) $input['cliente_id'] : null,
            'sede_id' => (int) ($input['sede_id'] ?? 0) > 0 ? (int) $input['sede_id'] : null,
            'desde' => $from->toDateString(), 'hasta' => $to->toDateString(),
            'from' => $from->startOfDay(), 'to' => $to->endOfDay(),
        ];
    }

    /**
     * Ejecuta la operación parseDate definida por la clase VentaController.
     *
     * @param  mixed  $value  Parámetro recibido por la operación.
     * @param  mixed  $fallback  Parámetro recibido por la operación.
     * @return CarbonImmutable Resultado de la operación.
     */
    private function parseDate(string $value, CarbonImmutable $fallback): CarbonImmutable
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $fallback;
        }
        try {
            $date = CarbonImmutable::createFromFormat('!Y-m-d', $value);
        } catch (\Throwable) {
            return $fallback;
        }

        return $date instanceof CarbonImmutable && $date->format('Y-m-d') === $value ? $date : $fallback;
    }
}
