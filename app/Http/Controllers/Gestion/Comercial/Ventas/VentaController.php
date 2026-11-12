<?php

namespace App\Http\Controllers\Gestion\Comercial\Ventas;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gestion\Comercial\Ventas\ActualizarVentaRequest;
use App\Http\Requests\Gestion\Comercial\Ventas\AnularVentaRequest;
use App\Http\Requests\Gestion\Comercial\Ventas\ConfirmarVentaCotizacionRequest;
use App\Http\Requests\Gestion\Comercial\Ventas\ConvertirCotizacionRequest;
use App\Http\Requests\Gestion\Comercial\Ventas\FiltroVentaRequest;
use App\Http\Requests\Gestion\Comercial\Ventas\GuardarVentaRequest;
use App\Models\Catalogo\Producto;
use App\Models\Clientes\Cliente;
use App\Models\Comercio\Cotizacion;
use App\Models\Comercio\Venta;
use App\Models\Finanzas\MedioPago;
use App\Models\Organizacion\Sede;
use App\Servicios\Compartidos\Auditoria\RegistradorAuditoria;
use App\Servicios\Compartidos\Comercial\ConsultaVentas;
use App\Servicios\Compartidos\Exportacion\ExportadorTabular;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use App\Servicios\Gestion\Comercial\Cotizaciones\ServicioCotizaciones;
use App\Servicios\Gestion\Comercial\Ventas\ServicioVentas;
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
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $filtros = $this->filtros($request->validated());
        // Prepara en $query la consulta que obtiene o modifica los registros necesarios.
        $query = $this->ventas->query($request->user(), $filtros);
        $estados = $this->ventas->query($request->user(), array_replace($filtros, ['estado' => null]))
            ->select('ventas.estado')->distinct()->orderBy('ventas.estado')->pluck('estado');
        // Guarda en $customers el resultado necesario para continuar esta operacion.
        $customers = collect();
        // Guarda en $products el resultado necesario para continuar esta operacion.
        $products = collect();
        // Guarda en $canCreate el resultado necesario para continuar esta operacion.
        $canCreate = false;
        // Obtiene en $canUpdate la informacion de la solicitud o del usuario actual.
        $canUpdate = $this->autorizacion->allows($request->user(), 'gestion.ventas.actualizar');
        // Guarda en $editOrder el resultado necesario para continuar esta operacion.
        $editOrder = null;
        // Comprueba la condicion Schema::hasColumns('ventas', ['canal', 'observaciones'] antes de ejecutar este bloque.
        if (Schema::hasColumns('ventas', ['canal', 'observaciones'])
            && ($this->autorizacion->allows($request->user(), 'gestion.ventas.crear') || $canUpdate)) {
            // Ejecuta la operacion dentro de un bloque controlado para atender posibles excepciones.
            try {
                // Obtiene en $ubicacion la informacion de la solicitud o del usuario actual.
                $ubicacion = $this->autorizacion->operationLocation($request->user());
                $ubicacionValida = $ubicacion['empresa_id'] !== null && $ubicacion['sede_id'] !== null && $ubicacion['almacen_id'] !== null;
                // Obtiene en $canCreate la informacion de la solicitud o del usuario actual.
                $canCreate = $ubicacionValida && $this->autorizacion->allows($request->user(), 'gestion.ventas.crear')
                    && $this->autorizacion->allowsRecord($request->user(), 'gestion.ventas.crear', $ubicacion['empresa_id'], $ubicacion['sede_id']);
                // Guarda en $canUpdate el resultado necesario para continuar esta operacion.
                $canUpdate = $ubicacionValida && $canUpdate
                    && $this->autorizacion->allowsRecord($request->user(), 'gestion.ventas.actualizar', $ubicacion['empresa_id'], $ubicacion['sede_id']);
            } catch (HttpExceptionInterface) {
                // Guarda en $canCreate el resultado necesario para continuar esta operacion.
                $canCreate = false;
                // Guarda en $canUpdate el resultado necesario para continuar esta operacion.
                $canUpdate = false;
            }
            // Comprueba la condicion $canCreate || $canUpdate antes de ejecutar este bloque.
            if ($canCreate || $canUpdate) {
                // Prepara en $customers la consulta que obtiene o modifica los registros necesarios.
                $customers = Cliente::query()->where('empresa_id', $ubicacion['empresa_id'])->where('activo', true)->orderBy('razon_social')->orderBy('id')->get();
                // Prepara en $saldos la consulta que obtiene o modifica los registros necesarios.
                $saldos = DB::table('existencias_almacen')->where('empresa_id', $ubicacion['empresa_id'])
                    ->where('sede_id', $ubicacion['sede_id'])->where('almacen_id', $ubicacion['almacen_id'])
                    ->selectRaw('producto_id, MIN(cantidad_fisica - cantidad_reservada) AS disponible')
                    ->groupBy('producto_id')->havingRaw('COUNT(*) = 1')->get()->keyBy('producto_id');
                // Prepara en $products la consulta que obtiene o modifica los registros necesarios.
                $products = Producto::query()->active()->where('empresa_id', $ubicacion['empresa_id'])
                    ->whereIn('id', $saldos->keys())->orderBy('nombre')->get();
                $products->each(static function (Producto $producto) use ($saldos): void {
                    $producto->existencias = max(0, (int) floor((float) $saldos->get($producto->id)->disponible));
                });
                // Comprueba la condicion $canCreate && ! is_string($request->session()->get('venta_token')) antes de ejecutar este bloque.
                if ($canCreate && ! is_string($request->session()->get('venta_token'))) {
                    $request->session()->put('venta_token', bin2hex(random_bytes(32)));
                }
                // Comprueba la condicion $request->filled('editar') antes de ejecutar este bloque.
                if ($request->filled('editar')) {
                    // Detiene la solicitud si se cumple la validacion de seguridad indicada.
                    abort_unless(Schema::hasColumns('ventas', ['pedido_id', 'descuento', 'impuestos']), 404);
                    // Detiene la solicitud si se cumple la validacion de seguridad indicada.
                    abort_unless($canUpdate && ctype_digit((string) $request->query('editar')), 404);
                    // Guarda en $editOrder el resultado necesario para continuar esta operacion.
                    $editOrder = $this->autorizacion->scopeRecordsFor(
                        Venta::query()->whereKey((int) $request->query('editar'))->where('ventas.estado', 'borrador')
                            ->where('ventas.canal', 'presencial')->whereNull('ventas.pedido_id')
                            ->where('ventas.descuento', 0)->where('ventas.impuestos', 0),
                        $request->user(), 'gestion.ventas.actualizar',
                    )->where('ventas.empresa_id', $ubicacion['empresa_id'])->where('ventas.sede_id', $ubicacion['sede_id'])
                        ->where('ventas.almacen_id', $ubicacion['almacen_id'])->with(['detalles.producto', 'cliente', 'usuario'])->first();
                    // Detiene la solicitud si se cumple la validacion de seguridad indicada.
                    abort_unless($editOrder !== null, 404);
                }
            }
        }

        // Guarda en $orders el resultado necesario para continuar esta operacion.
        $orders = (clone $query)->with(['cliente', 'pedido:id,canal', 'detalles'])->withCount('detalles')
            ->orderByDesc('ventas.fecha_venta')->orderByDesc('ventas.id')->paginate(10)->withQueryString();
        // Guarda en $cancelables el resultado necesario para continuar esta operacion.
        $cancelables = $this->autorizacion->scopeRecordsFor(
            Venta::query()->whereIn('ventas.id', $orders->getCollection()->modelKeys()), $request->user(), 'gestion.ventas.anular',
        )->pluck('ventas.id')->all();
        // Agrupa en $editables los datos que se utilizaran en la siguiente operacion.
        $editables = [];
        // Comprueba la condicion Schema::hasColumns('ventas', ['canal', 'pedido_id', 'descuento', 'impuestos']) antes de ejecutar este bloque.
        if (Schema::hasColumns('ventas', ['canal', 'pedido_id', 'descuento', 'impuestos'])) {
            // Guarda en $editables el resultado necesario para continuar esta operacion.
            $editables = $this->autorizacion->scopeRecordsFor(
                Venta::query()->whereIn('ventas.id', $orders->getCollection()->modelKeys())->where('ventas.estado', 'borrador')
                    ->where('ventas.canal', 'presencial')->whereNull('ventas.pedido_id')
                    ->where('ventas.descuento', 0)->where('ventas.impuestos', 0),
                $request->user(), 'gestion.ventas.actualizar',
            )->pluck('ventas.id')->all();
        }
        // Agrupa en $confirmables los datos que se utilizaran en la siguiente operacion.
        $confirmables = [];
        // Comprueba la condicion $this->autorizacion->allows($request->user(), 'gestion.ventas.crear' antes de ejecutar este bloque.
        if ($this->autorizacion->allows($request->user(), 'gestion.ventas.crear')
            && Schema::hasColumns('ventas', ['pedido_id', 'canal'])) {
            // Guarda en $confirmables el resultado necesario para continuar esta operacion.
            $confirmables = $this->autorizacion->scopeRecordsFor(
                Venta::query()->whereIn('ventas.id', $orders->getCollection()->modelKeys())
                    ->where('ventas.estado', 'borrador')->whereNotNull('ventas.pedido_id')->where('ventas.canal', 'presencial'),
                $request->user(), 'gestion.ventas.crear',
            )->pluck('ventas.id')->all();
        }
        // Guarda en $dispositivosPorDetalle el resultado necesario para continuar esta operacion.
        $dispositivosPorDetalle = collect();
        // Comprueba la condicion $confirmables !== [] && Schema::hasColumns('dispositivos', ['id', 'producto_id', 'variante_id', 'almacen_actual_id', 'estado']) antes de ejecutar este bloque.
        if ($confirmables !== [] && Schema::hasColumns('dispositivos', ['id', 'producto_id', 'variante_id', 'almacen_actual_id', 'estado'])) {
            // Recorre $orders->getCollection()->whereIn('id', $confirmables) as $ventaPendiente y procesa cada elemento de la coleccion.
            foreach ($orders->getCollection()->whereIn('id', $confirmables) as $ventaPendiente) {
                // Recorre $ventaPendiente->detalles as $detalle y procesa cada elemento de la coleccion.
                foreach ($ventaPendiente->detalles as $detalle) {
                    $varianteId = $detalle->variante_id === null ? null : (int) $detalle->variante_id;
                    // Prepara en $dispositivosDisponibles la consulta que obtiene o modifica los registros necesarios.
                    $dispositivosDisponibles = DB::table('dispositivos')->where('producto_id', $detalle->producto_id)
                        ->when($varianteId === null, fn ($consulta) => $consulta->whereNull('variante_id'), fn ($consulta) => $consulta->where('variante_id', $varianteId))
                        ->where('almacen_actual_id', $ventaPendiente->almacen_id)->where('estado', 'disponible')
                        ->orderBy('id')->get(['id', 'imei', 'numero_serie']);
                    // Prepara en $requiereDispositivos la consulta que obtiene o modifica los registros necesarios.
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
        // Guarda en $salesPresentation el resultado necesario para continuar esta operacion.
        $salesPresentation = $orders->getCollection()->mapWithKeys(function (Venta $order): array {
            // Guarda en $customerName el resultado necesario para continuar esta operacion.
            $customerName = $order->cliente?->razon_social ?: $order->cliente?->nombre_contacto;

            return [$order->id => [
                'initials' => $this->inicialesPara($customerName),
                'status' => $this->estadoPresentacion($order->estado),
            ]];
        });
        // Guarda en $devicesUnavailable el resultado necesario para continuar esta operacion.
        $devicesUnavailable = $orders->getCollection()->mapWithKeys(function (Venta $order) use ($dispositivosPorDetalle): array {
            // Guarda en $unavailable el resultado necesario para continuar esta operacion.
            $unavailable = $order->detalles->contains(function ($detail) use ($dispositivosPorDetalle): bool {
                // Guarda en $configuration el resultado necesario para continuar esta operacion.
                $configuration = $dispositivosPorDetalle->get($detail->getKey());

                return ($configuration['requiere'] ?? false)
                    && $configuration['dispositivos']->count() < (int) $detail->cantidad;
            });

            return [$order->id => $unavailable];
        });
        $filtrosDisponibles = array_replace($filtros, ['cliente_id' => null, 'sede_id' => null]);
        // Prepara en $clientesDisponibles la consulta que obtiene o modifica los registros necesarios.
        $clientesDisponibles = Cliente::query()->whereIn('id', $this->ventas->query($request->user(), $filtrosDisponibles)->select('ventas.cliente_id'))
            ->orderBy('razon_social')->orderBy('id')->get(['id', 'razon_social', 'nombre_contacto']);
        // Prepara en $sedesDisponibles la consulta que obtiene o modifica los registros necesarios.
        $sedesDisponibles = Sede::query()->whereIn('id', $this->ventas->query($request->user(), $filtrosDisponibles)->select('ventas.sede_id'))
            ->orderBy('nombre')->orderBy('id')->get(['id', 'nombre']);

        // Devuelve la vista 'gestion.comercial.ventas.listar' con los datos preparados por este controlador.
        return view('gestion.comercial.ventas.listar', [
            ...$this->ventas->resumen($request->user(), $filtros),
            'filters' => $filtros,
            'estados' => $estados,
            'orders' => $orders,
            'cancelables' => $cancelables,
            'editables' => $editables,
            'confirmables' => $confirmables,
            'dispositivosPorDetalle' => $dispositivosPorDetalle,
            'devicesUnavailable' => $devicesUnavailable,
            'salesPresentation' => $salesPresentation,
            'queryWithoutPage' => $request->except(['page']),
            'periodStartLabel' => CarbonImmutable::parse($filtros['desde'])->format('d/m/Y'),
            'periodEndLabel' => CarbonImmutable::parse($filtros['hasta'])->format('d/m/Y'),
            'hasActiveFilters' => $this->tieneFiltrosActivos($filtros),
            'clientesDisponibles' => $clientesDisponibles,
            'sedesDisponibles' => $sedesDisponibles,
            'latestSales' => (clone $query)->with('cliente')->orderByDesc('ventas.fecha_venta')->orderByDesc('ventas.id')->limit(5)->get(),
            'customers' => $customers,
            'products' => $products,
            'paymentMethods' => Schema::hasTable('medios_pago')
                ? MedioPago::query()->where('activo', true)->orderBy('nombre')->get(['id', 'codigo', 'nombre'])
                : collect(),
            'currentUserName' => $request->user()?->nombre ?? '',
            'defaultSaleDate' => now()->toDateString(),
            'canCreate' => $canCreate,
            'editOrder' => $editOrder,
        ]);
    }

    private function inicialesPara(?string $name): string
    {
        // Guarda en $parts el resultado necesario para continuar esta operacion.
        $parts = preg_split('/\s+/', trim((string) $name), -1, PREG_SPLIT_NO_EMPTY);

        return collect(array_slice($parts ?: ['CL'], 0, 2))
            ->map(static fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');
    }

    /** @return array{label: string, class: string} */
    private function estadoPresentacion(?string $status): array
    {
        return ['label' => $status ?: 'Sin estado', 'class' => 'muted'];
    }

    private function tieneFiltrosActivos(array $filters): bool
    {
        return (bool) (($filters['buscar'] ?? null)
            || ($filters['estado'] ?? null)
            || ($filters['canal'] ?? null)
            || ($filters['cliente_id'] ?? null)
            || ($filters['sede_id'] ?? null)
            || $filters['desde'] !== now()->subDays(29)->toDateString()
            || $filters['hasta'] !== now()->toDateString());
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
        // Obtiene en $usuarioActual la informacion de la solicitud o del usuario actual.
        $usuarioActual = $request->user();
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($usuarioActual !== null, 403);
        $this->autorizacion->assertAllowed($usuarioActual, 'gestion.ventas.consultar');

        // Guarda en $filtros el resultado necesario para continuar esta operacion.
        $filtros = $request->validate([
            'buscar' => ['nullable', 'string', 'max:120'],
            'estado' => ['nullable', 'string', 'max:30'],
            'canal' => ['nullable', 'in:ecommerce,presencial'],
            'pago' => ['nullable', 'integer', 'min:1', 'exists:medios_pago,id'],
        ]);
        $consultaBase = $this->ventas->query($usuarioActual, [])->whereHas('cliente', fn ($consulta) => $consulta->where('tipo', $tipoCliente));
        $consultaFiltrada = $this->ventas->query($usuarioActual, $filtros)->whereHas('cliente', fn ($consulta) => $consulta->where('tipo', $tipoCliente));
        // Comprueba la condicion isset($filtros['pago']) antes de ejecutar este bloque.
        if (isset($filtros['pago'])) {
            $consultaFiltrada->whereHas('pagos', fn ($consulta) => $consulta->where('medio_pago_id', $filtros['pago']));
        }
        // Guarda en $ventasDelMes el resultado necesario para continuar esta operacion.
        $ventasDelMes = (clone $consultaBase)->whereBetween('ventas.fecha_venta', [now()->startOfMonth(), now()->endOfMonth()]);
        // Guarda en $cantidadDelMes el resultado necesario para continuar esta operacion.
        $cantidadDelMes = (clone $ventasDelMes)->count();
        // Guarda en $ventasDelMesEnSoles el resultado necesario para continuar esta operacion.
        $ventasDelMesEnSoles = (clone $ventasDelMes)->where('ventas.moneda', 'PEN');
        // Guarda en $cantidadVentasDelMesEnSoles el resultado necesario para continuar esta operacion.
        $cantidadVentasDelMesEnSoles = (clone $ventasDelMesEnSoles)->count();
        // Guarda en $importeDelMes el resultado necesario para continuar esta operacion.
        $importeDelMes = (float) (clone $ventasDelMesEnSoles)->sum('ventas.total');
        // Guarda en $importeMesAnterior el resultado necesario para continuar esta operacion.
        $importeMesAnterior = (float) (clone $consultaBase)->where('ventas.moneda', 'PEN')
            ->whereBetween('ventas.fecha_venta', [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()])->sum('ventas.total');
        // Guarda en $ventasMesAnterior el resultado necesario para continuar esta operacion.
        $ventasMesAnterior = (clone $consultaBase)->whereBetween('ventas.fecha_venta', [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()])->count();
        // Guarda en $tendenciaImporte el resultado necesario para continuar esta operacion.
        $tendenciaImporte = $importeMesAnterior > 0 ? round((($importeDelMes - $importeMesAnterior) / $importeMesAnterior) * 100, 1).'%' : 'Sin comparación';
        // Guarda en $tendenciaCantidad el resultado necesario para continuar esta operacion.
        $tendenciaCantidad = $ventasMesAnterior > 0 ? round((($cantidadDelMes - $ventasMesAnterior) / $ventasMesAnterior) * 100, 1).'%' : 'Sin comparación';

        // Guarda en $ventas el resultado necesario para continuar esta operacion.
        $ventas = (clone $consultaFiltrada)->with(['cliente', 'usuario', 'pedido', 'pagos.medioPago'])
            ->orderByDesc('ventas.fecha_venta')->orderByDesc('ventas.id')->limit(10)->get();
        // Guarda en $estados el resultado necesario para continuar esta operacion.
        $estados = (clone $consultaBase)->select('ventas.estado')->distinct()->orderBy('ventas.estado')->pluck('estado');
        $mediosPago = MedioPago::query()->whereHas('pagos', fn ($consulta) => $consulta->whereIn('venta_id', (clone $consultaBase)->select('ventas.id')))
            ->orderBy('nombre')->get(['id', 'nombre']);
        // Guarda en $canales el resultado necesario para continuar esta operacion.
        $canales = collect();
        // Comprueba la condicion Schema::hasColumn('ventas', 'canal') antes de ejecutar este bloque.
        if (Schema::hasColumn('ventas', 'canal')) {
            // Guarda en $canales el resultado necesario para continuar esta operacion.
            $canales = (clone $consultaBase)->whereNotNull('ventas.canal')->distinct()->orderBy('ventas.canal')->pluck('ventas.canal');
        } elseif (Schema::hasColumn('pedidos', 'canal')) {
            $canales = (clone $consultaBase)->whereHas('pedido', fn ($consulta) => $consulta->whereNotNull('canal'))
                ->join('pedidos', 'pedidos.id', '=', 'ventas.pedido_id')->distinct()->orderBy('pedidos.canal')->pluck('pedidos.canal');
        }
        // Guarda en $consultaCanales el resultado necesario para continuar esta operacion.
        $consultaCanales = (clone $consultaBase)->leftJoin('pedidos', 'pedidos.id', '=', 'ventas.pedido_id');
        // Guarda en $campoCanal el resultado necesario para continuar esta operacion.
        $campoCanal = Schema::hasColumn('ventas', 'canal') ? 'COALESCE(ventas.canal, pedidos.canal)' : 'pedidos.canal';
        // Guarda en $resumenCanales el resultado necesario para continuar esta operacion.
        $resumenCanales = $consultaCanales->selectRaw($campoCanal.' AS canal, COUNT(*) AS cantidad')
            ->groupByRaw($campoCanal)->orderByDesc('cantidad')->get();
        // Guarda en $conteosEstado el resultado necesario para continuar esta operacion.
        $conteosEstado = (clone $consultaBase)->select('ventas.estado')->selectRaw('COUNT(*) AS cantidad')
            ->groupBy('ventas.estado')->orderBy('ventas.estado')->get();
        // Prepara en $ventasPorCliente la consulta que obtiene o modifica los registros necesarios.
        $ventasPorCliente = DB::table('ventas')->join('clientes', 'clientes.id', '=', 'ventas.cliente_id')
            ->whereIn('ventas.id', (clone $consultaBase)->select('ventas.id'))
            ->select('clientes.razon_social', 'clientes.nombre_contacto', 'ventas.moneda')
            ->selectRaw('SUM(ventas.total) AS importe')
            ->groupBy('clientes.id', 'clientes.razon_social', 'clientes.nombre_contacto', 'ventas.moneda')
            ->orderByDesc('importe')->limit(5)->get();

        // Agrupa en $datos los datos que se utilizaran en la siguiente operacion.
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
                // Guarda en $cliente el resultado necesario para continuar esta operacion.
                $cliente = $venta->cliente?->razon_social ?: $venta->cliente?->nombre_contacto ?: 'Sin cliente asociado';
                // Guarda en $estado el resultado necesario para continuar esta operacion.
                $estado = (string) $venta->estado;
                // Guarda en $canal el resultado necesario para continuar esta operacion.
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

        // Devuelve la vista con los datos preparados por el controlador.
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
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless(Schema::hasColumns('ventas', ['canal', 'observaciones']), 503, 'La migración de canal y observaciones está pendiente de autorización y aplicación.');
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $datos = $request->validated();
        // Obtiene en $venta la informacion de la solicitud o del usuario actual.
        $venta = $this->registro->registrarFisica($datos, $request->user(), $datos['venta_token'], $request->ip());
        $request->session()->forget('venta_token');

        // Guarda en $mensajePago el resultado necesario para continuar esta operacion.
        $mensajePago = isset($datos['medio_pago_id'])
            ? ' y pago registrado.'
            : '. No se registró ningún pago.';

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return redirect()->route('management.sales')->with('status', 'Venta '.$venta->codigo.' confirmada'.$mensajePago);
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
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $venta = $this->cotizaciones->convertirAventa($quotation, (int) $request->validated('almacen_id'), $request->user(), $request->ip());

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
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
        // Guarda en $ventaConfirmada el resultado necesario para continuar esta operacion.
        $ventaConfirmada = $this->registro->confirmarBorradorCotizacion(
            $venta,
            $request->user(),
            $request->ip(),
            // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
            $request->validated('dispositivos') ?? [],
        );

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
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
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $venta = $this->registro->actualizarBorrador($order, $request->validated(), $request->user(), $request->ip());

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
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
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $this->registro->anular($venta, $request->user(), $request->validated('motivo'), $request->ip());

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
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
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless(in_array($format, ['csv', 'excel', 'pdf'], true), 422, 'Formato de exportación no permitido.');
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $ventas = $this->ventas->query($request->user(), $this->filtros($request->validated()))
            ->with(['cliente:id,razon_social,nombre_contacto', 'pedido:id,canal'])
            ->orderBy('ventas.fecha_venta')->orderBy('ventas.id')->limit(5000)->get();
        // Guarda en $safe el resultado necesario para continuar esta operacion.
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
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $filters = $this->filtros($request->validated());
        // Guarda en $response el resultado necesario para continuar esta operacion.
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
    private function filtros(array $input): array
    {
        // Guarda en $today el resultado necesario para continuar esta operacion.
        $today = CarbonImmutable::today();
        // Guarda en $to el resultado necesario para continuar esta operacion.
        $to = $this->convertirFecha((string) ($input['hasta'] ?? ''), $today);
        // Guarda en $defaultFrom el resultado necesario para continuar esta operacion.
        $defaultFrom = filled($input['hasta'] ?? null) ? $to->subDays(29) : $today->subDays(29);
        // Guarda en $from el resultado necesario para continuar esta operacion.
        $from = $this->convertirFecha((string) ($input['desde'] ?? ''), $defaultFrom);
        // Comprueba la condicion $from->greaterThan($to) && filled($input['desde'] ?? null) antes de ejecutar este bloque.
        if ($from->greaterThan($to) && filled($input['desde'] ?? null)) {
            // Guarda en $to el resultado necesario para continuar esta operacion.
            $to = $from;
        }
        // Guarda en $canal el resultado necesario para continuar esta operacion.
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
     * Convierte una fecha de entrada y aplica el valor alternativo indicado.
     *
     * @param  mixed  $value  Parámetro recibido por la operación.
     * @param  mixed  $fallback  Parámetro recibido por la operación.
     * @return CarbonImmutable Resultado de la operación.
     */
    private function convertirFecha(string $value, CarbonImmutable $fallback): CarbonImmutable
    {
        // Comprueba la condicion ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) antes de ejecutar este bloque.
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $fallback;
        }
        // Ejecuta la operacion dentro de un bloque controlado para atender posibles excepciones.
        try {
            // Guarda en $date el resultado necesario para continuar esta operacion.
            $date = CarbonImmutable::createFromFormat('!Y-m-d', $value);
        } catch (\Throwable) {
            return $fallback;
        }

        return $date instanceof CarbonImmutable && $date->format('Y-m-d') === $value ? $date : $fallback;
    }
}
