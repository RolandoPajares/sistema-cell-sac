<?php

namespace App\Http\Controllers\Paneles\Administrador;

use App\Http\Controllers\Controller;
use App\Models\Auditoria\RegistroAuditoria;
use App\Models\Comercio\Pedido;
use App\Models\User;
use App\Servicios\ExportadorTabular;
use App\Servicios\RegistradorAuditoria;
use App\Servicios\ServicioAutorizacion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Define la responsabilidad de la clase DashboardGlobalPedidosController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
final class DashboardGlobalPedidosController extends Controller
{
    private const MAX_EXPORT_ROWS = 5000;

    /**
     * Obtiene y prepara la información solicitada para la operación.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $authorization  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function index(Request $request, ServicioAutorizacion $authorization): View
    {
        $this->authorizeDashboard($request->user(), $authorization);
        $filters = $this->filters($request, $authorization);
        $orders = $this->orders($request->user(), $filters, $authorization)
            ->with('user:id,nombre')
            ->get();
        $byStatus = $orders->groupBy(static fn (Pedido $order): string => $order->estado)
            ->map(static fn (Collection $group): int => $group->count());
        $byChannel = $this->hasChannel()
            ? $orders->groupBy(static fn (Pedido $order): string => $order->canal ?: 'Sin canal')
                ->map(static fn (Collection $group): int => $group->count())
            : collect();
        $activity = Schema::hasTable('registros_auditoria')
            ? RegistroAuditoria::query()->latest('creado_en')->limit(5)->get(['accion', 'entidad', 'entidad_id', 'creado_en'])
            : collect();
        $availableOrders = $this->orders($request->user(), $filters, $authorization, false);
        $statusColors = ['#0767f5', '#0bc4ee', '#38d5a1', '#ff6575', '#9b82f6'];
        $statusLegend = [];
        $gradientStops = [];
        $position = 0;
        foreach ($byStatus as $status => $count) {
            $color = $statusColors[count($statusLegend) % count($statusColors)];
            $next = $position + ($orders->count() ? $count / $orders->count() * 100 : 0);
            $gradientStops[] = sprintf('%s %.2f%% %.2f%%', $color, $position, $next);
            $statusLegend[] = ['nombre' => $status, 'valor' => number_format($count / max(1, $orders->count()) * 100, 1).'%', 'color' => $color];
            $position = $next;
        }
        $statusGradient = $gradientStops ? 'conic-gradient('.implode(', ', $gradientStops).')' : 'conic-gradient(#dce9f6 0 100%)';
        $maxChannel = max(1, (int) $byChannel->max());
        $channelBars = $byChannel->map(static fn ($count, $name): array => ['etiqueta' => $name, 'valor' => $count, 'altura' => max(4, round($count / $maxChannel * 100))])->values();
        $exportParameters = array_filter($filters, static fn ($value): bool => $value !== null && $value !== '');
        $statusCounts = [
            'new' => $byStatus->filter(static fn ($count, $status): bool => in_array(mb_strtolower($status), ['pendiente', 'nuevo'], true))->sum(),
            'processing' => $byStatus->filter(static fn ($count, $status): bool => in_array(mb_strtolower($status), ['en proceso', 'procesando', 'enviado'], true))->sum(),
            'completed' => $byStatus->filter(static fn ($count, $status): bool => in_array(mb_strtolower($status), ['entregado', 'completado'], true))->sum(),
            'cancelled' => $byStatus->filter(static fn ($count, $status): bool => in_array(mb_strtolower($status), ['cancelado', 'anulado'], true))->sum(),
            'active' => $byStatus->filter(static fn ($count, $status): bool => in_array(mb_strtolower($status), ['pendiente', 'nuevo', 'en proceso', 'procesando', 'enviado'], true))->sum(),
        ];
        $metrics = collect([
            ['titulo' => 'Pedidos nuevos', 'icono' => 'bi-cart3', 'valor' => number_format($statusCounts['new']), 'detalle' => 'En el período seleccionado'],
            ['titulo' => 'En proceso', 'icono' => 'bi-gear', 'valor' => number_format($statusCounts['processing']), 'detalle' => 'En el período seleccionado'],
            ['titulo' => 'Entregados', 'icono' => 'bi-check-circle', 'tono' => 'verde', 'valor' => number_format($statusCounts['completed']), 'detalle' => 'En el período seleccionado'],
            ['titulo' => 'Cancelados', 'icono' => 'bi-x-lg', 'tono' => 'rojo', 'valor' => number_format($statusCounts['cancelled']), 'detalle' => 'En el período seleccionado'],
            ['titulo' => 'Tiempo promedio', 'icono' => 'bi-clock', 'tono' => 'ambar', 'valor' => '—', 'detalle' => 'Sin marca de entrega verificable'],
            ['titulo' => 'Operaciones activas', 'icono' => 'bi-lightning', 'tono' => 'violeta', 'valor' => number_format($statusCounts['active']), 'detalle' => 'Pedidos pendientes o en proceso'],
        ]);

        return view('gestion.analitica.dashboard-global.pedidos.listar', [
            'filters' => $filters,
            'sites' => $this->availableSites($request->user(), $authorization),
            'channels' => $this->hasChannel() ? (clone $availableOrders)->whereNotNull('canal')->distinct()->orderBy('canal')->pluck('canal') : collect(),
            'statuses' => (clone $availableOrders)->distinct()->orderBy('estado')->pluck('estado'),
            'hasSite' => $this->hasSite(),
            'hasChannel' => $this->hasChannel(),
            'orders' => $orders,
            'byStatus' => $byStatus,
            'byChannel' => $byChannel,
            'statusLegend' => $statusLegend,
            'statusGradient' => $statusGradient,
            'channelBars' => $channelBars,
            'exportParameters' => $exportParameters,
            'statusCounts' => $statusCounts,
            'activity' => $activity,
            'canExport' => $authorization->allows($request->user(), 'gestion.analitica.reportes.exportar'),
            'titulo' => 'Pedidos y operaciones recientes',
            'icono' => 'bi-box-seam',
            'descripcion' => 'Gestiona los pedidos del negocio y monitorea las operaciones recientes.',
            'exportRoute' => $authorization->allows($request->user(), 'gestion.analitica.reportes.exportar') ? 'management.reports.dashboard_orders.export' : null,
            'metrics' => $metrics,
            'periodOptions' => [7 => 'Últimos 7 días', 30 => 'Últimos 30 días', 90 => 'Últimos 90 días'],
            'donutCenter' => number_format($orders->count()),
            'donutLabel' => 'Pedidos totales',
            'channelChartDescription' => 'Pedidos reales por canal',
            'ordersDonut' => ['centro' => number_format($orders->count()), 'etiqueta' => 'Pedidos totales', 'gradiente' => $statusGradient, 'leyenda' => $statusLegend],
            'channelChart' => ['descripcion' => 'Pedidos reales por canal', 'barras' => $channelBars],
        ]);
    }

    /**
     * Genera la salida exportable de la información disponible.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $format  Parámetro recibido por la operación.
     * @param  mixed  $exporter  Parámetro recibido por la operación.
     * @param  mixed  $authorization  Parámetro recibido por la operación.
     * @param  mixed  $auditor  Parámetro recibido por la operación.
     * @return Response|StreamedResponse Resultado de la operación.
     */
    public function export(Request $request, string $format, ExportadorTabular $exporter, ServicioAutorizacion $authorization, RegistradorAuditoria $auditor): Response|StreamedResponse
    {
        $this->authorizeDashboard($request->user(), $authorization);
        $authorization->assertAllowed($request->user(), 'gestion.analitica.reportes.exportar');
        abort_unless(in_array($format, ['pdf', 'excel', 'csv'], true), 404);
        $filters = $this->filters($request, $authorization);
        $orders = $this->orders($request->user(), $filters, $authorization);
        abort_if((clone $orders)->limit(self::MAX_EXPORT_ROWS + 1)->pluck('id')->count() > self::MAX_EXPORT_ROWS, 422, 'El período supera el límite de exportación. Reduce los filtros.');

        $hasChannel = $this->hasChannel();
        $hasSite = $this->hasSite();
        $columns = ['Pedido', 'Fecha', 'Estado'];
        if ($hasChannel) {
            $columns[] = 'Canal';
        }
        $siteColumnIndex = null;
        if ($hasSite) {
            $siteColumnIndex = count($columns);
            $columns[] = 'Sede ID';
        }
        $columns[] = 'Importe del pedido PEN (no cobro)';
        $columnTypes = [0 => 'integer', 1 => 'datetime'];
        if ($siteColumnIndex !== null) {
            $columnTypes[$siteColumnIndex] = 'integer';
        }
        $columnTypes[count($columns) - 1] = 'currency';

        $rows = $orders->orderByDesc('creado_en')->get()->map(static function (Pedido $order) use ($format, $hasChannel, $hasSite): array {
            $row = [
                $order->id,
                $order->creado_en?->format('Y-m-d H:i') ?? '',
                self::safeExportText((string) $order->estado, $format),
            ];
            if ($hasChannel) {
                $row[] = self::safeExportText((string) ($order->canal ?? ''), $format);
            }
            if ($hasSite) {
                $row[] = $order->sede_id ?? '';
            }
            $row[] = number_format((float) $order->total, 2, '.', '');

            return $row;
        })->all();
        $statuses = array_unique(array_map(static fn (array $row): string => (string) ($row[2] ?? ''), $rows));
        $report = [
            'title' => 'REPORTE DE PEDIDOS',
            'subtitle' => 'Detalle de pedidos filtrados; el importe nominal no representa un cobro confirmado.',
            'module' => 'Dashboard Global / Pedidos',
            'report_name' => 'Reporte de pedidos',
            'period' => 'Últimos '.$filters['periodo'].' días',
            'filters' => [
                'Período' => $filters['periodo'].' días',
                'Estado' => $filters['estado'] ?? 'Todos',
                'Canal' => $filters['canal'] ?? 'Todos',
                'Sede' => $filters['sede'] === null ? 'Todas las sedes autorizadas' : 'ID '.$filters['sede'],
            ],
            'generated_by' => (string) $request->user()->nombre,
            'role' => 'Administrador',
            'scope' => $filters['sede'] === null ? 'Sedes accesibles para el usuario según autorización.' : 'Sede autorizada ID '.$filters['sede'],
            'indicators' => [
                ['label' => 'Pedidos incluidos', 'value' => count($rows)],
                ['label' => 'Estados distintos', 'value' => count($statuses)],
            ],
            'column_types' => $columnTypes,
            'summary_row' => [
                'label' => 'Pedidos del conjunto filtrado: '.count($rows),
                'values' => [],
            ],
            'observations' => ['El importe mostrado proviene del total del pedido y no acredita un pago.'],
        ];
        $response = $exporter->download($format, 'pedidos-'.$filters['periodo'].'-dias', $columns, $rows, $report);

        $auditor->record((int) $request->user()->getKey(), 'exportar', 'pedidos', null, null, [
            'formato' => $format,
            'periodo' => $filters['periodo'],
            'sede_id' => $filters['sede'],
            'cantidad' => count($rows),
        ], $request->ip());

        return $response;
    }

    /** @return array{periodo:int,estado:?string,canal:?string,sede:?int} */
    private function filters(Request $request, ServicioAutorizacion $authorization): array
    {
        $data = $request->validate([
            'periodo' => ['nullable', 'integer', Rule::in([7, 30, 90])],
            'estado' => ['nullable', 'string', 'max:40'],
            'canal' => ['nullable', 'string', 'max:30'],
            'sede' => ['nullable', 'integer', 'min:1'],
        ], [
            'periodo.in' => 'El período debe ser de 7, 30 o 90 días.',
            'sede.integer' => 'La sede seleccionada no es válida.',
        ]);

        $filters = [
            'periodo' => (int) ($data['periodo'] ?? 7),
            'estado' => $data['estado'] ?? null,
            'canal' => $data['canal'] ?? null,
            'sede' => isset($data['sede']) ? (int) $data['sede'] : null,
        ];
        abort_if($filters['sede'] !== null && ! $this->hasSite(), 422, 'El filtro de sede no está disponible.');
        abort_if($filters['canal'] !== null && ! $this->hasChannel(), 422, 'El filtro de canal no está disponible.');

        $availableSites = $this->availableSites($request->user(), $authorization);
        if ($filters['sede'] !== null) {
            abort_unless($availableSites->contains('id', $filters['sede']), 403);
        }

        $availableOrders = $this->orders($request->user(), $filters, $authorization, false);
        if ($filters['estado'] !== null) {
            abort_unless((clone $availableOrders)->where('estado', $filters['estado'])->exists(), 422, 'El estado seleccionado no existe en el período autorizado.');
        }
        if ($filters['canal'] !== null) {
            abort_unless((clone $availableOrders)->where('canal', $filters['canal'])->exists(), 422, 'El canal seleccionado no existe en el período autorizado.');
        }

        return $filters;
    }

    /** @param array{periodo:int,estado:?string,canal:?string,sede:?int} $filters */
    private function orders(User $user, array $filters, ServicioAutorizacion $authorization, bool $applySelectedFilters = true): Builder
    {
        $query = Pedido::query()
            ->where('creado_en', '>=', Carbon::now()->subDays($filters['periodo'])->startOfDay())
            ->where('creado_en', '<=', now())
            ->when($applySelectedFilters ? $filters['estado'] : null, static fn (Builder $query, string $status): Builder => $query->where('estado', $status))
            ->when($applySelectedFilters ? $filters['canal'] : null, static fn (Builder $query, string $channel): Builder => $query->where('canal', $channel))
            ->when($applySelectedFilters ? $filters['sede'] : null, static fn (Builder $query, int $siteId): Builder => $query->where('sede_id', $siteId))
            ->orderByDesc('creado_en');

        if ($authorization->isSeeded()) {
            $isGlobalAdministrator = $authorization->allowsGlobal($user, 'gestion.seguridad.consultar');
            if ($isGlobalAdministrator) {
                if (Schema::hasColumn('pedidos', 'empresa_id')) {
                    $companyId = $authorization->activeCompanyId();
                    $companyId === null ? $query->whereRaw('1 = 0') : $query->where('empresa_id', $companyId);
                }
            } else {
                abort_unless($this->hasSite(), 403);
                $query->whereIn('sede_id', $this->assignedSiteIds($user, $authorization));
            }
            if (! $isGlobalAdministrator && Schema::hasColumn('pedidos', 'empresa_id') && Schema::hasTable('sedes') && Schema::hasColumn('sedes', 'empresa_id')) {
                $query->whereExists(static function ($siteQuery): void {
                    $siteQuery->selectRaw('1')->from('sedes as dashboard_sedes')
                        ->whereColumn('dashboard_sedes.id', 'pedidos.sede_id')
                        ->whereColumn('dashboard_sedes.empresa_id', 'pedidos.empresa_id');
                });
            }
        }

        return $query;
    }

    /**
     * Determina si la operación cumple las condiciones de autorización o validación.
     *
     * @param  mixed  $user  Parámetro recibido por la operación.
     * @param  mixed  $authorization  Parámetro recibido por la operación.
     * @return void Resultado de la operación.
     */
    private function authorizeDashboard(User $user, ServicioAutorizacion $authorization): void
    {
        abort_unless($authorization->allows($user, 'gestion.seguridad.consultar'), 403);
        $authorization->assertAllowed($user, 'gestion.panel.consultar');
    }

    /** @return Collection<int, object> */
    private function availableSites(User $user, ServicioAutorizacion $authorization): Collection
    {
        if (! $this->hasSite()) {
            return collect();
        }

        return DB::table('sedes')
            ->when(Schema::hasColumn('sedes', 'activa'), static fn ($query) => $query->where('activa', true))
            ->when(
                Schema::hasTable('empresas') && Schema::hasColumn('sedes', 'empresa_id') && Schema::hasColumn('empresas', 'activo'),
                static fn ($query) => $query->join('empresas as dashboard_empresas', 'dashboard_empresas.id', '=', 'sedes.empresa_id')
                    ->where('dashboard_empresas.activo', true),
            )
            ->when($authorization->isSeeded() && ! $this->hasGlobalOrganizationAccess($user), fn ($query) => $query->whereIn('sedes.id', $this->assignedSiteIds($user, $authorization)))
            ->orderBy('sedes.nombre')
            ->get(['sedes.id', 'sedes.nombre']);
    }

    /**
     * Realiza la operación específica definida por este método y devuelve su resultado.
     *
     * @param  mixed  $user  Parámetro recibido por la operación.
     * @return bool Resultado de la operación.
     */
    private function hasGlobalOrganizationAccess(User $user): bool
    {
        return DB::table('usuarios_roles')->join('roles', 'roles.id', '=', 'usuarios_roles.rol_id')
            ->where('usuarios_roles.usuario_id', $user->getKey())
            ->join('roles_permisos', 'roles_permisos.rol_id', '=', 'roles.id')
            ->join('permisos', 'permisos.id', '=', 'roles_permisos.permiso_id')
            ->where('permisos.codigo', 'gestion.organizacion.consultar')
            ->where('roles_permisos.alcance', 'empresa')
            ->where('roles.activo', true)
            ->where('usuarios_roles.activo', true)
            ->where(static fn ($query) => $query->whereNull('usuarios_roles.vigente_hasta')->orWhere('usuarios_roles.vigente_hasta', '>', now()))
            ->exists();
    }

    /** @return array<int, int> */
    private function assignedSiteIds(User $user, ServicioAutorizacion $authorization): array
    {
        $siteAssignments = DB::table('usuarios_sedes_roles')
            ->join('roles', 'roles.id', '=', 'usuarios_sedes_roles.rol_id')
            ->join('sedes as dashboard_sedes', 'dashboard_sedes.id', '=', 'usuarios_sedes_roles.sede_id')
            ->when(Schema::hasColumn('sedes', 'activa'), static fn ($query) => $query->where('dashboard_sedes.activa', true))
            ->when(
                Schema::hasTable('empresas') && Schema::hasColumn('sedes', 'empresa_id') && Schema::hasColumn('empresas', 'activo'),
                static fn ($query) => $query->join('empresas as dashboard_empresas', 'dashboard_empresas.id', '=', 'dashboard_sedes.empresa_id')
                    ->where('dashboard_empresas.activo', true),
            )
            ->where('usuarios_sedes_roles.usuario_id', $user->getKey())
            ->join('roles_permisos', 'roles_permisos.rol_id', '=', 'roles.id')
            ->join('permisos', 'permisos.id', '=', 'roles_permisos.permiso_id')
            ->where('permisos.codigo', 'gestion.organizacion.consultar')
            ->where('roles_permisos.alcance', 'sede')
            ->where('roles.activo', true)
            ->where('usuarios_sedes_roles.activo', true)
            ->where(static fn ($query) => $query->whereNull('usuarios_sedes_roles.vigente_hasta')->orWhere('usuarios_sedes_roles.vigente_hasta', '>', now()))
            ->distinct();
        $siteIds = $siteAssignments->pluck('usuarios_sedes_roles.sede_id')->map(static fn ($id): int => (int) $id)->all();
        $activeSiteId = $authorization->activeSiteIdFor($user);

        return $activeSiteId === null ? $siteIds : array_values(array_intersect($siteIds, [$activeSiteId]));
    }

    /**
     * Realiza la operación específica definida por este método y devuelve su resultado.
     *
     * @return bool Resultado de la operación.
     */
    private function hasSite(): bool
    {
        return Schema::hasTable('sedes') && Schema::hasColumn('pedidos', 'sede_id');
    }

    /**
     * Realiza la operación específica definida por este método y devuelve su resultado.
     *
     * @return bool Resultado de la operación.
     */
    private function hasChannel(): bool
    {
        return Schema::hasColumn('pedidos', 'canal');
    }

    /**
     * Ejecuta la operación safeExportText definida por la clase DashboardGlobalPedidosController.
     *
     * @param  mixed  $value  Parámetro recibido por la operación.
     * @param  mixed  $format  Parámetro recibido por la operación.
     * @return string Resultado de la operación.
     */
    private static function safeExportText(string $value, string $format): string
    {
        $clean = trim(preg_replace('/[\p{C}\p{Z}]+/u', ' ', $value) ?? '');
        if ($format === 'pdf') {
            $clean = mb_convert_encoding(mb_convert_encoding($clean, 'Windows-1252', 'UTF-8'), 'UTF-8', 'Windows-1252');
        }

        return preg_match('/^[=+\-@]/u', $clean) === 1 ? "'".$clean : $clean;
    }
}
