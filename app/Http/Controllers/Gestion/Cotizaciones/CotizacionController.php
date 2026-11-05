<?php

namespace App\Http\Controllers\Gestion\Cotizaciones;

use App\Http\Controllers\Controller;
use App\Http\Requests\GuardarCotizacionRequest;
use App\Http\Requests\ActualizarCotizacionRequest;
use App\Http\Requests\RegistrarEnvioCotizacionRequest;
use App\Models\Catalogo\Producto;
use App\Models\Clientes\Cliente;
use App\Models\Comercio\Cotizacion;
use App\Models\Organizacion\Empresa;
use App\Models\Organizacion\Sede;
use App\Servicios\ExportadorTabular;
use App\Servicios\RegistradorAuditoria;
use App\Servicios\ServicioAutorizacion;
use App\Servicios\ServicioCotizaciones;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CotizacionController extends Controller
{
    public function __construct(
        private readonly ServicioCotizaciones $quotationService,
        private readonly ServicioAutorizacion $authorization,
    ) {}

    public function index(Request $request): View
    {
        if ($request->routeIs('management.quotations')) {
            return $this->administratorIndex($request);
        }

        $query = Cotizacion::with('customer', 'details.product')->latest('creado_en')->latest('id');
        $this->authorization->assertAllowed($request->user(), 'gestion.cotizaciones.consultar');
        if ($this->authorization->hasRole($request->user(), 'cliente_mayorista')) {
            $query->whereHas('customer', fn ($builder) => $builder->where('usuario_id', $request->user()->id));
        } else {
            $query = $this->authorization->scopeRecordsFor($query, $request->user(), 'gestion.cotizaciones.consultar');
        }

        if ($this->authorization->hasRole($request->user(), 'cliente_mayorista')) {
            $customers = Cliente::query()->where('usuario_id', $request->user()->getKey())
                ->where('tipo', 'mayorista')->where('activo', true)->orderBy('nombre_contacto')->get();
            $empresasAutorizadas = $customers->pluck('empresa_id')->filter()->unique()->values();
        } else {
            $sedesAutorizadas = $this->authorization->authorizedSiteIds($request->user(), 'gestion.ventas.crear');
            $empresasAutorizadas = DB::table('sedes')->whereIn('id', $sedesAutorizadas)->where('activa', true)
                ->pluck('empresa_id')->unique()->values();
            $customers = Cliente::query()->whereIn('empresa_id', $empresasAutorizadas)
                ->where('tipo', 'mayorista')->where('activo', true)->orderBy('nombre_contacto')->get();
        }

        return view('gestion.cotizaciones.listar', [
            'quotations' => $query->paginate(20),
            'customers' => $customers,
            'products' => Producto::query()->active()->whereIn('empresa_id', $empresasAutorizadas)->orderBy('nombre')->get(),
        ]);
    }

    private function administratorIndex(Request $request): View
    {
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
        $applyFilters = static function (Builder $query) use ($filters): Builder {
            if (($filters['buscar'] ?? '') !== '') {
                $search = trim($filters['buscar']);
                $query->where(function (Builder $query) use ($search): void {
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

            if (isset($filters['cliente']) && $filters['cliente'] !== '') {
                $query->where('cliente_id', $filters['cliente']);
            }

            if (($filters['estado'] ?? '') !== '') {
                $query->where('estado', $filters['estado']);
            }

            if (($filters['desde'] ?? '') !== '') {
                $query->whereDate('creado_en', '>=', $filters['desde']);
            }

            if (($filters['hasta'] ?? '') !== '') {
                $query->whereDate('creado_en', '<=', $filters['hasta']);
            }

            return $query;
        };

        $quotations = $applyFilters($activeQuotations())
            ->with(['customer', 'details.product'])
            ->withCount('details')
            ->latest('creado_en')
            ->latest('id')
            ->paginate(8)
            ->withQueryString();
        $aprobables = $this->authorization->scopeRecordsFor(
            Cotizacion::query()->whereIn('id', $quotations->getCollection()->modelKeys())->whereIn('estado', ['Pendiente', 'Enviada']),
            $request->user(),
            'gestion.cotizaciones.aprobar',
        )->pluck('id')->all();

        $allQuotations = $activeQuotations();
        $recentQuotations = (clone $allQuotations)
            ->with('customer')
            ->latest('creado_en')
            ->latest('id')
            ->limit(4)
            ->get();
        $statusSummary = (clone $allQuotations)
            ->select('estado')
            ->selectRaw('COUNT(*) as cantidad')
            ->groupBy('estado')
            ->orderByDesc('cantidad')
            ->get();
        $totalQuotations = (clone $allQuotations)->count();
        $sedesAutorizadas = $this->authorization->authorizedSiteIds($request->user(), 'gestion.ventas.crear');
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
        $empresasAutorizadas = $almacenesVenta->pluck('empresa_id')->unique()->values();

        return view('gestion.cotizaciones.administrador', [
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
        ]);
    }

    public function export(
        Request $request,
        string $format,
        ExportadorTabular $exportador,
        RegistradorAuditoria $auditoria,
    ): Response|StreamedResponse {
        abort_unless(in_array($format, ['csv', 'excel', 'pdf'], true), 422, 'Formato de exportación no permitido.');
        $usuario = $request->user();
        abort_unless($usuario !== null, 403);
        $this->authorization->assertAllowed($usuario, 'gestion.cotizaciones.exportar');
        $filtros = $request->validate([
            'buscar' => ['nullable', 'string', 'max:160'],
            'cliente' => ['nullable', 'integer'],
            'estado' => ['nullable', 'string', 'max:30'],
            'desde' => ['nullable', 'date_format:Y-m-d'],
            'hasta' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:desde'],
        ]);
        $consulta = $this->authorization->scopeRecordsFor(
            Cotizacion::query()->where('activo', true),
            $usuario,
            'gestion.cotizaciones.exportar',
        )->with('customer')->withCount('details');
        if (($filtros['buscar'] ?? '') !== '') {
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

    public function update(ActualizarCotizacionRequest $request, Cotizacion $quotation): RedirectResponse
    {
        $this->quotationService->actualizar($quotation, $request->validated(), $request->user(), $request->ip());

        return back()->with('status', 'La cotización se actualizó en MariaDB.');
    }

    public function store(GuardarCotizacionRequest $request): RedirectResponse
    {
        $this->quotationService->create($request->validated(), $request->user());

        return back()->with('status', 'Cotización guardada como borrador; aún no se envía al cliente.');
    }

    public function aprobar(Request $request, Cotizacion $quotation): RedirectResponse
    {
        $this->quotationService->aprobar($quotation, $request->user(), $request->ip());

        return back()->with('status', 'La cotizacion fue aprobada y registrada en MariaDB.');
    }

    public function deactivate(Request $request, Cotizacion $quotation): RedirectResponse
    {
        $this->quotationService->desactivar($quotation, $request->user(), $request->ip());

        return back()->with('status', 'La cotizacion fue desactivada y conservada en el historial.');
    }

    public function registrarEnvioManual(RegistrarEnvioCotizacionRequest $request, Cotizacion $quotation): RedirectResponse
    {
        $cotizacion = $this->quotationService->registrarEnvioManual($quotation, $request->user(), $request->ip());

        return back()->with('status', 'Se registro el envio manual de la cotizacion '.$cotizacion->id.'.');
    }

    public function descargar(Request $request, Cotizacion $quotation, ExportadorTabular $exportador, RegistradorAuditoria $auditoria): Response|StreamedResponse
    {
        $usuario = $request->user();
        abort_unless($usuario !== null, 403);
        $this->authorization->assertAllowed($usuario, 'gestion.cotizaciones.exportar');
        $cotizacion = $this->authorization->scopeRecordsFor(
            Cotizacion::query()->whereKey($quotation->getKey())->where('activo', true),
            $usuario,
            'gestion.cotizaciones.exportar',
        )->with(['customer', 'details.product'])->firstOrFail();
        abort_unless($cotizacion->customer !== null && $cotizacion->details->isNotEmpty(), 409, 'La cotizacion requiere cliente y productos para descargarse.');
        $empresa = is_numeric($cotizacion->empresa_id) ? Empresa::query()->find($cotizacion->empresa_id) : null;
        $sede = is_numeric($cotizacion->sede_id) ? Sede::query()->find($cotizacion->sede_id) : null;
        $nombreEmpresa = $empresa?->razon_social ?? 'Empresa autorizada';
        $alcance = $sede === null ? $nombreEmpresa : $nombreEmpresa.' / '.$sede->nombre;
        $referencia = 'COT-'.($cotizacion->creado_en?->format('Y') ?? now()->format('Y')).'-'.str_pad((string) $cotizacion->id, 4, '0', STR_PAD_LEFT);
        $filas = $cotizacion->details->map(static fn ($detalle): array => [
            $detalle->product?->nombre ?? $detalle->descripcion_historica ?? 'Producto no disponible',
            $detalle->cantidad,
            $detalle->precio_unitario,
            $detalle->total_linea ?? round((float) $detalle->precio_unitario * (float) $detalle->cantidad, 2),
        ])->all();
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
