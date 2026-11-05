<?php

namespace App\Http\Controllers\Paneles\Administrador;

use App\Http\Controllers\Controller;
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

final class DashboardGlobalVentasController extends Controller
{
    private const BUSINESS_TIMEZONE = 'America/Lima';

    private const STORAGE_TIMEZONE = 'UTC';

    /** Bound the in-memory PDF/XLSX payload until streaming export infrastructure exists. */
    private const MAX_EXPORT_ROWS = 5000;

    public function index(Request $request, ServicioAutorizacion $authorization): View
    {
        $this->authorizeDashboard($request->user(), $authorization);
        $filters = $this->filters($request, $authorization);
        $orders = $this->orders($request->user(), $filters, $authorization);
        $registeredCount = (clone $orders)->count();
        $nonCancelledCount = (clone $orders)->whereNotIn('estado', ['Cancelado', 'Cancelada'])->count();

        return view('paneles.administrador.dashboard-global.ventas.listar', [
            'filters' => $filters,
            'sites' => $this->availableSites($request->user(), $authorization),
            'channels' => $this->hasChannel() ? (clone $orders)->whereNotNull('canal')->distinct()->orderBy('canal')->pluck('canal') : collect(),
            'hasSite' => $this->hasSite(),
            'hasChannel' => $this->hasChannel(),
            'orderCount' => $registeredCount,
            'nonCancelledCount' => $nonCancelledCount,
            'income' => null,
            'averageTicket' => null,
            'completedCount' => null,
            'dailyTotals' => collect(),
            'channelTotals' => collect(),
            'canExport' => $authorization->allows($request->user(), 'gestion.reportes.exportar'),
        ]);
    }

    public function export(Request $request, string $format, ExportadorTabular $exporter, ServicioAutorizacion $authorization, RegistradorAuditoria $auditor): Response|StreamedResponse
    {
        $this->authorizeDashboard($request->user(), $authorization);
        $authorization->assertAllowed($request->user(), 'gestion.reportes.exportar');
        abort_unless(in_array($format, ['pdf', 'excel', 'csv'], true), 404);
        $filters = $this->filters($request, $authorization);
        $orders = $this->orders($request->user(), $filters, $authorization);
        abort_if((clone $orders)->limit(self::MAX_EXPORT_ROWS + 1)->pluck('id')->count() > self::MAX_EXPORT_ROWS, 422, 'El período supera el límite de exportación. Reduce los filtros.');
        $selectColumns = ['id', 'creado_en', 'estado', 'total'];
        $columns = ['Pedido', 'Fecha (Lima)', 'Estado', 'Importe del pedido PEN (no cobro)'];
        $columnTypes = [0 => 'integer', 1 => 'datetime', 3 => 'currency'];
        if ($this->hasSite()) {
            $selectColumns[] = 'sede_id';
            $columns[] = 'Sede ID';
            $columnTypes[count($columns) - 1] = 'integer';
        }
        if ($this->hasChannel()) {
            $selectColumns[] = 'canal';
            $columns[] = 'Canal';
        }
        $rows = $orders->orderBy('id')->limit(self::MAX_EXPORT_ROWS)->get($selectColumns)->map(function (Pedido $order) use ($format): array {
            $row = [
                $order->id,
                $order->creado_en?->timezone(self::BUSINESS_TIMEZONE)->format('Y-m-d H:i') ?? '',
                self::safeExportText($order->estado, $format),
                (string) $order->total,
            ];
            if ($this->hasSite()) {
                $row[] = $order->sede_id ?? '';
            }
            if ($this->hasChannel()) {
                $row[] = self::safeExportText($order->canal ?? '', $format);
            }

            return $row;
        })->all();

        $distinctStatuses = count(array_unique(array_map(static fn (array $row): string => (string) ($row[2] ?? ''), $rows)));
        $report = [
            'title' => 'REPORTE DE VENTAS',
            'subtitle' => 'Pedidos registrados durante el período consultado; el importe indicado no representa cobros.',
            'module' => 'Dashboard Global / Ventas',
            'report_name' => 'Reporte de ventas',
            'period' => 'Últimos '.$filters['periodo'].' días',
            'filters' => [
                'Período' => $filters['periodo'].' días',
                'Sede' => $filters['sede'] === null ? 'Todas las sedes autorizadas' : 'ID '.$filters['sede'],
                'Canal' => $filters['canal'] ?? 'Todos',
            ],
            'generated_by' => (string) $request->user()->nombre,
            'role' => 'Administrador',
            'scope' => $filters['sede'] === null ? 'Sedes accesibles para el usuario según autorización.' : 'Sede autorizada ID '.$filters['sede'],
            'indicators' => [
                ['label' => 'Pedidos incluidos', 'value' => count($rows)],
                ['label' => 'Estados distintos', 'value' => $distinctStatuses],
            ],
            'column_types' => $columnTypes,
            'summary_row' => [
                'label' => 'Pedidos del conjunto filtrado: '.count($rows),
                'values' => [],
            ],
        ];
        $response = $exporter->download($format, 'ventas-'.$filters['periodo'].'-dias', $columns, $rows, $report);

        $auditor->record((int) $request->user()->getKey(), 'exportar', 'pedidos', null, null, [
            'formato' => $format,
            'periodo' => $filters['periodo'],
            'sede_id' => $filters['sede'],
            'cantidad' => count($rows),
        ], $request->ip());

        return $response;
    }

    /** @return array{periodo:int,sede:?int,canal:?string} */
    private function filters(Request $request, ServicioAutorizacion $authorization): array
    {
        $validated = $request->validate([
            'periodo' => ['nullable', 'integer', Rule::in([7, 30, 90])],
            'sede' => ['nullable', 'integer', 'min:1'],
            'canal' => ['nullable', 'string', 'max:50'],
        ]);

        $siteId = isset($validated['sede']) ? (int) $validated['sede'] : null;
        abort_if($siteId !== null && ! $this->hasSite(), 422, 'El filtro de sede no está disponible.');
        abort_if(isset($validated['canal']) && ! $this->hasChannel(), 422, 'El filtro de canal no está disponible.');
        if ($siteId !== null) {
            abort_unless($this->availableSites($request->user(), $authorization)->contains('id', $siteId), 403);
        }

        $filters = [
            'periodo' => (int) ($validated['periodo'] ?? 30),
            'sede' => $siteId,
            'canal' => null,
        ];

        if (isset($validated['canal'])) {
            $channel = (string) $validated['canal'];
            $channelExists = $this->orders($request->user(), $filters, $authorization)
                ->where('canal', $channel)
                ->exists();

            abort_unless($channelExists, 422, 'El canal seleccionado no está disponible en el período autorizado.');
            $filters['canal'] = $channel;
        }

        return $filters;
    }

    /** @param array{periodo:int,sede:?int,canal:?string} $filters */
    private function orders(User $user, array $filters, ServicioAutorizacion $authorization): Builder
    {
        $now = Carbon::now(self::BUSINESS_TIMEZONE);
        $today = $now->copy()->startOfDay();
        $start = $today->copy()->subDays($filters['periodo'] - 1)->timezone(self::STORAGE_TIMEZONE);
        $end = $now->copy()->timezone(self::STORAGE_TIMEZONE);
        $query = Pedido::query()
            ->where('creado_en', '>=', $start)
            ->where('creado_en', '<=', $end)
            ->when($filters['sede'], static fn (Builder $query, int $siteId): Builder => $query->where('sede_id', $siteId))
            ->when($filters['canal'], static fn (Builder $query, string $channel): Builder => $query->where('canal', $channel));

        if ($authorization->isSeeded()) {
            $isGlobalAdministrator = $this->hasGlobalAdministratorAssignment($user);
            if ($isGlobalAdministrator) {
                if (Schema::hasColumn('pedidos', 'empresa_id')) {
                    $companyId = $authorization->activeCompanyId();
                    $companyId === null ? $query->whereRaw('1 = 0') : $query->where('empresa_id', $companyId);
                }
            } else {
                abort_unless($this->hasSite(), 403);
                $query->whereIn('sede_id', $this->assignedSiteIds($user, $authorization));
            }

            if (! $isGlobalAdministrator && Schema::hasColumn('pedidos', 'empresa_id') && Schema::hasColumn('sedes', 'empresa_id')) {
                $query->whereExists(static function ($siteQuery): void {
                    $siteQuery->selectRaw('1')->from('sedes as dashboard_sedes')
                        ->whereColumn('dashboard_sedes.id', 'pedidos.sede_id')
                        ->whereColumn('dashboard_sedes.empresa_id', 'pedidos.empresa_id');
                });
            }
        }

        return $query;
    }

    private function authorizeDashboard(User $user, ServicioAutorizacion $authorization): void
    {
        abort_unless($authorization->hasRole($user, 'administrador'), 403);
        $authorization->assertAllowed($user, 'gestion.panel.consultar');
    }

    /** @return Collection<int, object> */
    private function availableSites(User $user, ServicioAutorizacion $authorization): Collection
    {
        if (! $this->hasSite()) {
            return collect();
        }

        $sites = DB::table('sedes')
            ->when(Schema::hasColumn('sedes', 'activa'), static fn ($query) => $query->where('activa', true))
            ->when(
                Schema::hasTable('empresas') && Schema::hasColumn('sedes', 'empresa_id') && Schema::hasColumn('empresas', 'activo'),
                static fn ($query) => $query->join('empresas as dashboard_empresas', 'dashboard_empresas.id', '=', 'sedes.empresa_id')
                    ->where('dashboard_empresas.activo', true),
            )
            ->when($authorization->isSeeded() && ! $this->hasGlobalAdministratorAssignment($user), fn ($query) => $query->whereIn('sedes.id', $this->assignedSiteIds($user, $authorization)))
            ->orderBy('sedes.nombre');

        return $sites->get(['sedes.id', 'sedes.nombre']);
    }

    private function hasGlobalAdministratorAssignment(User $user): bool
    {
        return DB::table('usuarios_roles')->join('roles', 'roles.id', '=', 'usuarios_roles.rol_id')
            ->where('usuarios_roles.usuario_id', $user->getKey())
            ->where('roles.codigo', 'administrador')
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
            ->where('roles.codigo', 'administrador')
            ->where('roles.activo', true)
            ->where('usuarios_sedes_roles.activo', true)
            ->where(static fn ($query) => $query->whereNull('usuarios_sedes_roles.vigente_hasta')->orWhere('usuarios_sedes_roles.vigente_hasta', '>', now()))
            ->distinct();
        $siteIds = $siteAssignments->pluck('usuarios_sedes_roles.sede_id')->map(static fn ($id): int => (int) $id)->all();

        $activeSiteId = $authorization->activeSiteIdFor($user);

        return $activeSiteId === null ? $siteIds : array_values(array_intersect($siteIds, [$activeSiteId]));
    }

    private function hasSite(): bool
    {
        return Schema::hasTable('sedes') && Schema::hasColumn('pedidos', 'sede_id');
    }

    private function hasChannel(): bool
    {
        return Schema::hasColumn('pedidos', 'canal');
    }

    private static function safeExportText(string $value, string $format): string
    {
        $clean = trim(preg_replace('/[\p{C}\p{Z}]+/u', ' ', $value) ?? '');
        if ($format === 'pdf') {
            $clean = mb_convert_encoding(mb_convert_encoding($clean, 'Windows-1252', 'UTF-8'), 'UTF-8', 'Windows-1252');
        }

        return preg_match('/^[=+\-@]/u', $clean) === 1 ? "'".$clean : $clean;
    }
}
