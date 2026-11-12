<?php

namespace App\Http\Controllers\Gestion\Comercial\Cotizaciones;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gestion\Comercial\Cotizaciones\ActualizarCotizacionRequest;
use App\Http\Requests\Gestion\Comercial\Cotizaciones\GuardarCotizacionRequest;
use App\Http\Requests\Gestion\Comercial\Cotizaciones\RegistrarEnvioCotizacionRequest;
use App\Models\Catalogo\Producto;
use App\Models\Clientes\Cliente;
use App\Models\Comercio\Cotizacion;
use App\Models\Organizacion\Empresa;
use App\Models\Organizacion\Sede;
use App\Servicios\Compartidos\Auditoria\RegistradorAuditoria;
use App\Servicios\Compartidos\Exportacion\ExportadorTabular;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use App\Servicios\Gestion\Comercial\Cotizaciones\ServicioCotizaciones;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Define la responsabilidad de la clase CotizacionController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class CotizacionController extends Controller
{
    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @return mixed Resultado de la operación.
     */
    public function __construct(
        private readonly ServicioCotizaciones $quotationService,
        private readonly ServicioAutorizacion $authorization,
    ) {}

    /**
     * Obtiene y prepara la información solicitada para la operación.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function index(Request $request): View
    {
        // Comprueba la condicion $request->routeIs('management.quotations') antes de ejecutar este bloque.
        if ($request->routeIs('management.quotations')) {
            return $this->indiceAdministrador($request);
        }

        // Guarda en $query el resultado necesario para continuar esta operacion.
        $query = Cotizacion::with('customer', 'details.product')->latest('creado_en')->latest('id');
        $this->authorization->assertAllowed($request->user(), 'gestion.cotizaciones.consultar');
        // Comprueba la condicion $this->authorization->allows($request->user(), 'gestion.cotizaciones.consultar') antes de ejecutar este bloque.
        if ($this->authorization->allows($request->user(), 'gestion.cotizaciones.consultar')) {
            $query->whereHas('customer', fn ($builder) => $builder->where('usuario_id', $request->user()->id));
        } else {
            // Obtiene en $query la informacion de la solicitud o del usuario actual.
            $query = $this->authorization->scopeRecordsFor($query, $request->user(), 'gestion.cotizaciones.consultar');
        }

        // Comprueba la condicion $this->authorization->allows($request->user(), 'gestion.cotizaciones.consultar') antes de ejecutar este bloque.
        if ($this->authorization->allows($request->user(), 'gestion.cotizaciones.consultar')) {
            // Prepara en $customers la consulta que obtiene o modifica los registros necesarios.
            $customers = Cliente::query()->where('usuario_id', $request->user()->getKey())
                ->where('tipo', 'mayorista')->where('activo', true)->orderBy('nombre_contacto')->get();
            // Guarda en $empresasAutorizadas el resultado necesario para continuar esta operacion.
            $empresasAutorizadas = $customers->pluck('empresa_id')->filter()->unique()->values();
        } else {
            // Obtiene en $sedesAutorizadas la informacion de la solicitud o del usuario actual.
            $sedesAutorizadas = $this->authorization->authorizedSiteIds($request->user(), 'gestion.ventas.crear');
            // Prepara en $empresasAutorizadas la consulta que obtiene o modifica los registros necesarios.
            $empresasAutorizadas = DB::table('sedes')->whereIn('id', $sedesAutorizadas)->where('activa', true)
                ->pluck('empresa_id')->unique()->values();
            // Prepara en $customers la consulta que obtiene o modifica los registros necesarios.
            $customers = Cliente::query()->whereIn('empresa_id', $empresasAutorizadas)
                ->where('tipo', 'mayorista')->where('activo', true)->orderBy('nombre_contacto')->get();
        }

        // Devuelve la vista 'gestion.comercial.cotizaciones.listar' con los datos preparados por este controlador.
        return view('gestion.comercial.cotizaciones.listar', [
            'quotations' => $query->paginate(20),
            'customers' => $customers,
            'products' => Producto::query()->active()->whereIn('empresa_id', $empresasAutorizadas)->orderBy('nombre')->get(),
        ]);
    }

    /**
     * Ejecuta la operación administratorIndex definida por la clase CotizacionController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    private function indiceAdministrador(Request $request): View
    {
        // Guarda en $filters el resultado necesario para continuar esta operacion.
        $filters = $request->validate([
            'buscar' => ['nullable', 'string', 'max:160'],
            'cliente' => ['nullable', 'integer'],
            'estado' => ['nullable', 'string', 'max:30'],
            'desde' => ['nullable', 'date_format:Y-m-d'],
            'hasta' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:desde'],
        ]);

        $activeQuotations = fn (): Builder => $this->authorization->scopeRecordsFor(
            Cotizacion::query()->where('activo', true),
            $request->user(),
            'gestion.cotizaciones.consultar',
        );
        // Guarda en $applyFilters el resultado necesario para continuar esta operacion.
        $applyFilters = static function (Builder $query) use ($filters): Builder {
            // Comprueba la condicion ($filters['buscar'] ?? '') !== '' antes de ejecutar este bloque.
            if (($filters['buscar'] ?? '') !== '') {
                // Guarda en $search el resultado necesario para continuar esta operacion.
                $search = trim($filters['buscar']);
                $query->where(function (Builder $query) use ($search): void {
                    // Comprueba la condicion ctype_digit($search) antes de ejecutar este bloque.
                    if (ctype_digit($search)) {
                        $query->where('id', (int) $search)
                            ->orWhereHas('customer', fn (Builder $customerQuery) => $customerQuery
                                ->where('nombre_contacto', 'like', "%{$search}%")
                                ->orWhere('documento', 'like', "%{$search}%"))
                            ->orWhereHas('details.product', fn (Builder $productQuery) => $productQuery
                                ->where('nombre', 'like', "%{$search}%"));

                        return;
                    }

                    $query->whereHas('customer', fn (Builder $customerQuery) => $customerQuery
                        ->where('nombre_contacto', 'like', "%{$search}%")
                        ->orWhere('documento', 'like', "%{$search}%"))
                        ->orWhereHas('details.product', fn (Builder $productQuery) => $productQuery
                            ->where('nombre', 'like', "%{$search}%"));
                });
            }

            // Comprueba la condicion isset($filters['cliente']) && $filters['cliente'] !== '' antes de ejecutar este bloque.
            if (isset($filters['cliente']) && $filters['cliente'] !== '') {
                $query->where('cliente_id', $filters['cliente']);
            }

            // Comprueba la condicion ($filters['estado'] ?? '') !== '' antes de ejecutar este bloque.
            if (($filters['estado'] ?? '') !== '') {
                $query->where('estado', $filters['estado']);
            }

            // Comprueba la condicion ($filters['desde'] ?? '') !== '' antes de ejecutar este bloque.
            if (($filters['desde'] ?? '') !== '') {
                $query->whereDate('creado_en', '>=', $filters['desde']);
            }

            // Comprueba la condicion ($filters['hasta'] ?? '') !== '' antes de ejecutar este bloque.
            if (($filters['hasta'] ?? '') !== '') {
                $query->whereDate('creado_en', '<=', $filters['hasta']);
            }

            return $query;
        };

        // Guarda en $quotations el resultado necesario para continuar esta operacion.
        $quotations = $applyFilters($activeQuotations())
            ->with(['customer', 'details.product'])
            ->withCount('details')
            ->latest('creado_en')
            ->latest('id')
            ->paginate(8)
            ->withQueryString();
        // Guarda en $aprobables el resultado necesario para continuar esta operacion.
        $aprobables = $this->authorization->scopeRecordsFor(
            Cotizacion::query()->whereIn('id', $quotations->getCollection()->modelKeys())->whereIn('estado', ['Pendiente', 'Enviada']),
            $request->user(),
            'gestion.cotizaciones.aprobar',
        )->pluck('id')->all();

        // Guarda en $allQuotations el resultado necesario para continuar esta operacion.
        $allQuotations = $activeQuotations();
        // Guarda en $recentQuotations el resultado necesario para continuar esta operacion.
        $recentQuotations = (clone $allQuotations)
            ->with('customer')
            ->latest('creado_en')
            ->latest('id')
            ->limit(4)
            ->get();
        // Guarda en $statusSummary el resultado necesario para continuar esta operacion.
        $statusSummary = (clone $allQuotations)
            ->select('estado')
            ->selectRaw('COUNT(*) as cantidad')
            ->groupBy('estado')
            ->orderByDesc('cantidad')
            ->get();
        // Guarda en $totalQuotations el resultado necesario para continuar esta operacion.
        $totalQuotations = (clone $allQuotations)->count();
        // Obtiene en $sedesAutorizadas la informacion de la solicitud o del usuario actual.
        $sedesAutorizadas = $this->authorization->authorizedSiteIds($request->user(), 'gestion.ventas.crear');
        // Prepara en $almacenesVenta la consulta que obtiene o modifica los registros necesarios.
        $almacenesVenta = DB::table('almacenes')
            ->join('sedes', 'sedes.id', '=', 'almacenes.sede_id')
            ->join('empresas', 'empresas.id', '=', 'sedes.empresa_id')
            ->whereIn('sedes.id', $sedesAutorizadas)
            ->where('almacenes.activo', true)
            ->where('almacenes.permite_venta', true)
            ->where('sedes.activa', true)
            ->where('empresas.activo', true)
            ->orderBy('sedes.nombre')->orderBy('almacenes.nombre')
            ->get(['almacenes.id', 'almacenes.nombre', 'almacenes.sede_id', 'sedes.empresa_id', 'sedes.nombre as sede_nombre']);
        // Guarda en $empresasAutorizadas el resultado necesario para continuar esta operacion.
        $empresasAutorizadas = $almacenesVenta->pluck('empresa_id')->unique()->values();
        // Guarda en $quotationPresentation el resultado necesario para continuar esta operacion.
        $quotationPresentation = $quotations->getCollection()
            ->concat($recentQuotations)
            ->unique('id')
            ->mapWithKeys(function (Cotizacion $quotation): array {
                // Guarda en $status el resultado necesario para continuar esta operacion.
                $status = (string) $quotation->estado;

                return [$quotation->id => [
                    'status' => $status,
                    'status_class' => $this->claseEstado($status),
                    'customer_name' => $quotation->customer?->nombre_contacto ?? 'Cliente no disponible',
                    'reference' => 'COT-'.($quotation->creado_en?->format('Y') ?? now()->format('Y')).'-'.str_pad((string) $quotation->id, 4, '0', STR_PAD_LEFT),
                ]];
            })
            ->all();

        // Devuelve la vista 'gestion.comercial.cotizaciones.listar' con los datos preparados por este controlador.
        return view('gestion.comercial.cotizaciones.listar', [
            'quotations' => $quotations,
            'aprobables' => $aprobables,
            'puedeEnviar' => $this->authorization->allows($request->user(), 'gestion.cotizaciones.actualizar'),
            'puedeDescargar' => $this->authorization->allows($request->user(), 'gestion.cotizaciones.exportar'),
            'puedeCrearVenta' => $this->authorization->allows($request->user(), 'gestion.ventas.crear') && $almacenesVenta->isNotEmpty(),
            'almacenesVenta' => $almacenesVenta,
            'customers' => Cliente::query()
                ->whereIn('empresa_id', $empresasAutorizadas)
                ->where('tipo', 'mayorista')
                ->where('activo', true)
                ->orderBy('nombre_contacto')
                ->get(),
            'products' => Producto::query()->active()->whereIn('empresa_id', $empresasAutorizadas)->orderBy('nombre')->get(),
            'filters' => $filters,
            'totalQuotations' => $totalQuotations,
            'pendingQuotations' => (clone $allQuotations)->whereIn('estado', ['Borrador', 'Pendiente', 'Enviada'])->count(),
            'approvedQuotations' => (clone $allQuotations)->where('estado', 'Aprobada')->count(),
            'quotedTotal' => (float) (clone $allQuotations)->sum('total'),
            'recentQuotations' => $recentQuotations,
            'statusSummary' => $statusSummary->map(fn ($status): array => [
                'label' => $status->estado,
                'count' => (int) $status->cantidad,
                'percent' => $totalQuotations > 0 ? round(((int) $status->cantidad / $totalQuotations) * 100) : 0,
            ]),
            'quotationPresentation' => $quotationPresentation,
        ]);
    }

    private function claseEstado(string $status): string
    {
        return match ($status) {
            'Borrador' => 'draft',
            'Pendiente', 'Enviada' => 'pending',
            'Aprobada' => 'approved',
            'Convertida' => 'converted',
            'Rechazada', 'Vencida' => 'rejected',
            default => 'muted',
        };
    }

    /**
     * Muestra el formulario completo de una cotización que todavía puede editarse.
     */
    public function edit(Request $request, Cotizacion $quotation): View
    {
        // Obtiene en $usuario la informacion de la solicitud o del usuario actual.
        $usuario = $request->user();
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($usuario !== null, 403);
        $this->authorization->assertAllowed($usuario, 'gestion.cotizaciones.actualizar');

        // Guarda en $quotation el resultado necesario para continuar esta operacion.
        $quotation = $this->authorization->scopeRecordsFor(
            Cotizacion::query()->whereKey($quotation->getKey())->where('activo', true),
            $usuario,
            'gestion.cotizaciones.actualizar',
        )->with(['customer', 'details.product'])->firstOrFail();

        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless(in_array($quotation->estado, ['Borrador', 'Pendiente'], true), 409,
            'Solo se pueden editar cotizaciones en borrador o pendientes.');

        // Guarda en $empresaId el resultado necesario para continuar esta operacion.
        $empresaId = (int) $quotation->empresa_id;
        // Prepara en $customers la consulta que obtiene o modifica los registros necesarios.
        $customers = Cliente::query()
            ->where('empresa_id', $empresaId)
            ->where('tipo', 'mayorista')
            ->where('activo', true)
            ->orderBy('nombre_contacto')
            ->get();
        // Prepara en $products la consulta que obtiene o modifica los registros necesarios.
        $products = Producto::query()
            ->active()
            ->where('empresa_id', $empresaId)
            ->orderBy('nombre')
            ->get();

        // Devuelve la vista 'gestion.comercial.cotizaciones.editar' con los datos preparados por este controlador.
        return view('gestion.comercial.cotizaciones.editar', [
            'quotation' => $quotation,
            'customers' => $customers,
            'products' => $products,
        ]);
    }

    /**
     * Genera la salida exportable de la información disponible.
     *
     * @return mixed Resultado de la operación.
     */
    public function export(
        Request $request,
        string $format,
        ExportadorTabular $exportador,
        RegistradorAuditoria $auditoria,
    ): Response|StreamedResponse {
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless(in_array($format, ['csv', 'excel', 'pdf'], true), 422, 'Formato de exportación no permitido.');
        // Obtiene en $usuario la informacion de la solicitud o del usuario actual.
        $usuario = $request->user();
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($usuario !== null, 403);
        $this->authorization->assertAllowed($usuario, 'gestion.cotizaciones.exportar');
        // Guarda en $filtros el resultado necesario para continuar esta operacion.
        $filtros = $request->validate([
            'buscar' => ['nullable', 'string', 'max:160'],
            'cliente' => ['nullable', 'integer'],
            'estado' => ['nullable', 'string', 'max:30'],
            'desde' => ['nullable', 'date_format:Y-m-d'],
            'hasta' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:desde'],
        ]);
        // Guarda en $consulta el resultado necesario para continuar esta operacion.
        $consulta = $this->authorization->scopeRecordsFor(
            Cotizacion::query()->where('activo', true),
            $usuario,
            'gestion.cotizaciones.exportar',
        )->with('customer')->withCount('details');
        // Comprueba la condicion ($filtros['buscar'] ?? '') !== '' antes de ejecutar este bloque.
        if (($filtros['buscar'] ?? '') !== '') {
            // Guarda en $buscar el resultado necesario para continuar esta operacion.
            $buscar = trim($filtros['buscar']);
            $consulta->where(function (Builder $query) use ($buscar): void {
                $query->whereHas('customer', fn (Builder $customer): Builder => $customer
                    ->where('nombre_contacto', 'like', '%'.$buscar.'%')
                    ->orWhere('documento', 'like', '%'.$buscar.'%'))
                    ->orWhereHas('details.product', fn (Builder $product): Builder => $product->where('nombre', 'like', '%'.$buscar.'%'));
            });
        }
        $consulta->when(($filtros['cliente'] ?? '') !== '', fn (Builder $query): Builder => $query->where('cliente_id', $filtros['cliente']))
            ->when(($filtros['estado'] ?? '') !== '', fn (Builder $query): Builder => $query->where('estado', $filtros['estado']))
            ->when(($filtros['desde'] ?? '') !== '', fn (Builder $query): Builder => $query->whereDate('creado_en', '>=', $filtros['desde']))
            ->when(($filtros['hasta'] ?? '') !== '', fn (Builder $query): Builder => $query->whereDate('creado_en', '<=', $filtros['hasta']));
        // Guarda en $cotizaciones el resultado necesario para continuar esta operacion.
        $cotizaciones = $consulta->latest('creado_en')->latest('id')->get();
        $filas = $cotizaciones->map(static fn (Cotizacion $cotizacion): array => [
            'COT-'.($cotizacion->creado_en?->format('Y') ?? now()->format('Y')).'-'.str_pad((string) $cotizacion->id, 4, '0', STR_PAD_LEFT),
            $cotizacion->customer?->nombre_contacto ?? 'Cliente no disponible',
            'Mayorista',
            $cotizacion->details_count,
            $cotizacion->moneda.' '.number_format((float) $cotizacion->total, 2, '.', ''),
            $cotizacion->estado,
            $cotizacion->creado_en,
        ])->all();
        // Guarda en $response el resultado necesario para continuar esta operacion.
        $response = $exportador->download($format, 'cotizaciones',
            ['Cotización', 'Cliente', 'Tipo', 'Productos', 'Total', 'Estado', 'Creado en'],
            $filas,
            [
                'title' => 'COTIZACIONES MAYORISTAS',
                'subtitle' => 'Datos reales consultados desde MariaDB.',
                'module' => 'Operaciones Comerciales',
                'report_name' => 'Cotizaciones',
                'filters' => $filtros,
                'generated_by' => (string) $usuario->nombre,
                'role' => 'Usuario autorizado',
                'scope' => 'Empresa, sede y permisos RBAC vigentes.',
                'indicators' => [['label' => 'Cotizaciones incluidas', 'value' => count($filas)]],
                'column_types' => ['text', 'text', 'text', 'integer', 'currency', 'text', 'datetime'],
            ]);
        $auditoria->record($usuario->getKey(), 'exportar', 'cotizaciones', null, null,
            ['formato' => $format, 'cantidad' => count($filas), 'filtros' => $filtros], $request->ip());

        return $response;
    }

    /**
     * Actualiza la información existente con los datos recibidos.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $quotation  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function update(ActualizarCotizacionRequest $request, Cotizacion $quotation): RedirectResponse
    {
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $this->quotationService->actualizar($quotation, $request->validated(), $request->user(), $request->ip());

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()->with('status', 'La cotización se actualizó en MariaDB.');
    }

    /**
     * Registra la información recibida y coordina su persistencia.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function store(GuardarCotizacionRequest $request): RedirectResponse
    {
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $this->quotationService->create($request->validated(), $request->user());

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()->with('status', 'Cotización guardada como borrador; aún no se envía al cliente.');
    }

    /**
     * Ejecuta la operación aprobar definida por la clase CotizacionController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $quotation  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function aprobar(Request $request, Cotizacion $quotation): RedirectResponse
    {
        $this->quotationService->aprobar($quotation, $request->user(), $request->ip());

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()->with('status', 'La cotizacion fue aprobada y registrada en MariaDB.');
    }

    /**
     * Modifica el estado o elimina el registro según la operación solicitada.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $quotation  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function deactivate(Request $request, Cotizacion $quotation): RedirectResponse
    {
        $this->quotationService->desactivar($quotation, $request->user(), $request->ip());

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()->with('status', 'La cotizacion fue desactivada y conservada en el historial.');
    }

    /**
     * Registra la información recibida y coordina su persistencia.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $quotation  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function registrarEnvioManual(RegistrarEnvioCotizacionRequest $request, Cotizacion $quotation): RedirectResponse
    {
        // Obtiene en $cotizacion la informacion de la solicitud o del usuario actual.
        $cotizacion = $this->quotationService->registrarEnvioManual($quotation, $request->user(), $request->ip());

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()->with('status', 'Se registro el envio manual de la cotizacion '.$cotizacion->id.'.');
    }

    /**
     * Ejecuta la operación descargar definida por la clase CotizacionController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $quotation  Parámetro recibido por la operación.
     * @param  mixed  $exportador  Parámetro recibido por la operación.
     * @param  mixed  $auditoria  Parámetro recibido por la operación.
     * @return Response|StreamedResponse Resultado de la operación.
     */
    public function descargar(Request $request, Cotizacion $quotation, ExportadorTabular $exportador, RegistradorAuditoria $auditoria): Response|StreamedResponse
    {
        // Obtiene en $usuario la informacion de la solicitud o del usuario actual.
        $usuario = $request->user();
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($usuario !== null, 403);
        $this->authorization->assertAllowed($usuario, 'gestion.cotizaciones.exportar');
        // Guarda en $cotizacion el resultado necesario para continuar esta operacion.
        $cotizacion = $this->authorization->scopeRecordsFor(
            Cotizacion::query()->whereKey($quotation->getKey())->where('activo', true),
            $usuario,
            'gestion.cotizaciones.exportar',
        )->with(['customer', 'details.product'])->firstOrFail();
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($cotizacion->customer !== null && $cotizacion->details->isNotEmpty(), 409, 'La cotizacion requiere cliente y productos para descargarse.');
        // Prepara en $empresa la consulta que obtiene o modifica los registros necesarios.
        $empresa = is_numeric($cotizacion->empresa_id) ? Empresa::query()->find($cotizacion->empresa_id) : null;
        // Prepara en $sede la consulta que obtiene o modifica los registros necesarios.
        $sede = is_numeric($cotizacion->sede_id) ? Sede::query()->find($cotizacion->sede_id) : null;
        // Guarda en $nombreEmpresa el resultado necesario para continuar esta operacion.
        $nombreEmpresa = $empresa?->razon_social ?? 'Empresa autorizada';
        $alcance = $sede === null ? $nombreEmpresa : $nombreEmpresa.' / '.$sede->nombre;
        // Guarda en $referencia el resultado necesario para continuar esta operacion.
        $referencia = 'COT-'.($cotizacion->creado_en?->format('Y') ?? now()->format('Y')).'-'.str_pad((string) $cotizacion->id, 4, '0', STR_PAD_LEFT);
        $filas = $cotizacion->details->map(static fn ($detalle): array => [
            $detalle->product?->nombre ?? $detalle->descripcion_historica ?? 'Producto no disponible',
            $detalle->cantidad,
            $detalle->precio_unitario,
            $detalle->total_linea ?? round((float) $detalle->precio_unitario * (float) $detalle->cantidad, 2),
        ])->all();
        // Guarda en $respuesta el resultado necesario para continuar esta operacion.
        $respuesta = $exportador->download('pdf', 'cotizacion-'.$cotizacion->id, ['Producto', 'Cantidad', 'Precio unitario', 'Importe'], $filas, [
            'company' => $empresa?->razon_social ?? 'Empresa autorizada',
            'title' => 'COTIZACION '.$referencia,
            'subtitle' => 'Propuesta comercial para '.($cotizacion->customer->razon_social ?: $cotizacion->customer->nombre_contacto),
            'period' => $cotizacion->creado_en?->format('Y-m-d') ?? now()->toDateString(),
            'module' => 'Cotizaciones',
            'report_name' => $referencia,
            'generated_by' => $usuario->nombre,
            'role' => (string) ($request->attributes->get('rol_contexto') ?: $usuario->rol),
            'scope' => $alcance,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'indicators' => [
                ['label' => 'Estado', 'value' => $cotizacion->estado],
                ['label' => 'Cliente', 'value' => $cotizacion->customer->nombre_contacto],
                ['label' => 'Total '.$cotizacion->moneda, 'value' => (float) $cotizacion->total],
            ],
            'column_types' => ['text', 'integer', 'currency', 'currency'],
            'summary_row' => ['label' => 'Total '.$cotizacion->moneda, 'values' => ['', '', '', (float) $cotizacion->total]],
            'observations' => array_values(array_filter([$cotizacion->notas, 'Esta descarga no registra por si sola que el documento haya sido enviado.'])),
            'orientation' => 'portrait',
        ]);
        $auditoria->record((int) $usuario->getKey(), 'cotizacion.descargada', 'cotizaciones', (int) $cotizacion->getKey(), null,
            ['referencia' => $referencia, 'formato' => 'pdf'], $request->ip(),
            scope: ['empresa_id' => (int) $cotizacion->empresa_id, 'sede_id' => (int) $cotizacion->sede_id]);

        return $respuesta;
    }
}
