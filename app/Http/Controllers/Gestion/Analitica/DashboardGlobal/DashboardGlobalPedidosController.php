<?php

namespace App\Http\Controllers\Gestion\Analitica\DashboardGlobal;

use App\Http\Controllers\Controller;
use App\Models\Auditoria\RegistroAuditoria;
use App\Models\Comercio\Pedido;
use App\Models\User;
use App\Servicios\Compartidos\Auditoria\RegistradorAuditoria;
use App\Servicios\Compartidos\Exportacion\ExportadorTabular;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
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
        // Solicita al servicio o dependencia la operacion indicada en esta instruccion.
        $this->autorizarPanel($request->user(), $authorization);
        // Guarda en $filters el resultado necesario para continuar esta operacion.
        $filters = $this->filtros($request, $authorization);
        // Obtiene en $orders la informacion de la solicitud o del usuario actual.
        $orders = $this->pedidos($request->user(), $filters, $authorization)
            ->with('user:id,nombre')
            ->get();
        $byStatus = $orders->groupBy(static fn (Pedido $order): string => $order->estado)
            ->map(static fn (Collection $group): int => $group->count());
        // Guarda en $byChannel el resultado necesario para continuar esta operacion.
        $byChannel = $this->tieneCanal()
            ? $orders->groupBy(static fn (Pedido $order): string => $order->canal ?: 'Sin canal')
                ->map(static fn (Collection $group): int => $group->count())
            : collect();
        // Guarda en $activity el resultado necesario para continuar esta operacion.
        $activity = Schema::hasTable('registros_auditoria')
            ? RegistroAuditoria::query()->latest('creado_en')->limit(5)->get(['accion', 'entidad', 'entidad_id', 'creado_en'])
            : collect();
        // Obtiene en $availableOrders la informacion de la solicitud o del usuario actual.
        $availableOrders = $this->pedidos($request->user(), $filters, $authorization, false);
        // Agrupa en $statusColors los datos que se utilizaran en la siguiente operacion.
        $statusColors = ['#0767f5', '#0bc4ee', '#38d5a1', '#ff6575', '#9b82f6'];
        // Agrupa en $statusLegend los datos que se utilizaran en la siguiente operacion.
        $statusLegend = [];
        // Agrupa en $gradientStops los datos que se utilizaran en la siguiente operacion.
        $gradientStops = [];
        // Guarda en $position el resultado necesario para continuar esta operacion.
        $position = 0;
        // Recorre $byStatus as $status => $count y procesa cada elemento de la coleccion.
        foreach ($byStatus as $status => $count) {
            // Guarda en $color el resultado necesario para continuar esta operacion.
            $color = $statusColors[count($statusLegend) % count($statusColors)];
            // Guarda en $next el resultado necesario para continuar esta operacion.
            $next = $position + ($orders->count() ? $count / $orders->count() * 100 : 0);
            $gradientStops[] = sprintf('%s %.2f%% %.2f%%', $color, $position, $next);
            $statusLegend[] = ['nombre' => $status, 'valor' => number_format($count / max(1, $orders->count()) * 100, 1).'%', 'color' => $color];
            // Guarda en $position el resultado necesario para continuar esta operacion.
            $position = $next;
        }
        // Guarda en $statusGradient el resultado necesario para continuar esta operacion.
        $statusGradient = $gradientStops ? 'conic-gradient('.implode(', ', $gradientStops).')' : 'conic-gradient(#dce9f6 0 100%)';
        // Guarda en $maxChannel el resultado necesario para continuar esta operacion.
        $maxChannel = max(1, (int) $byChannel->max());
        $channelBars = $byChannel->map(static fn ($count, $name): array => ['etiqueta' => $name, 'valor' => $count, 'altura' => max(4, round($count / $maxChannel * 100))])->values();
        $exportParameters = array_filter($filters, static fn ($value): bool => $value !== null && $value !== '');
        // Agrupa en $statusCounts los datos que se utilizaran en la siguiente operacion.
        $statusCounts = [
            'new' => $byStatus->filter(static fn ($count, $status): bool => in_array(mb_strtolower($status), ['pendiente', 'nuevo'], true))->sum(),
            'processing' => $byStatus->filter(static fn ($count, $status): bool => in_array(mb_strtolower($status), ['en proceso', 'procesando', 'enviado'], true))->sum(),
            'completed' => $byStatus->filter(static fn ($count, $status): bool => in_array(mb_strtolower($status), ['entregado', 'completado'], true))->sum(),
            'cancelled' => $byStatus->filter(static fn ($count, $status): bool => in_array(mb_strtolower($status), ['cancelado', 'anulado'], true))->sum(),
            'active' => $byStatus->filter(static fn ($count, $status): bool => in_array(mb_strtolower($status), ['pendiente', 'nuevo', 'en proceso', 'procesando', 'enviado'], true))->sum(),
        ];
        // Guarda en $metrics el resultado necesario para continuar esta operacion.
        $metrics = collect([
            ['titulo' => 'Pedidos nuevos', 'icono' => 'bi-cart3', 'valor' => number_format($statusCounts['new']), 'detalle' => 'En el período seleccionado'],
            ['titulo' => 'En proceso', 'icono' => 'bi-gear', 'valor' => number_format($statusCounts['processing']), 'detalle' => 'En el período seleccionado'],
            ['titulo' => 'Entregados', 'icono' => 'bi-check-circle', 'tono' => 'verde', 'valor' => number_format($statusCounts['completed']), 'detalle' => 'En el período seleccionado'],
            ['titulo' => 'Cancelados', 'icono' => 'bi-x-lg', 'tono' => 'rojo', 'valor' => number_format($statusCounts['cancelled']), 'detalle' => 'En el período seleccionado'],
            ['titulo' => 'Tiempo promedio', 'icono' => 'bi-clock', 'tono' => 'ambar', 'valor' => '—', 'detalle' => 'Sin marca de entrega verificable'],
            ['titulo' => 'Operaciones activas', 'icono' => 'bi-lightning', 'tono' => 'violeta', 'valor' => number_format($statusCounts['active']), 'detalle' => 'Pedidos pendientes o en proceso'],
        ]);

        // Devuelve la vista 'gestion.analitica.dashboard-global.pedidos.listar' con los datos preparados por este controlador.
        return view('gestion.analitica.dashboard-global.pedidos.listar', [
            'filters' => $filters,
            'sites' => $this->sedesDisponibles($request->user(), $authorization),
            'channels' => $this->tieneCanal() ? (clone $availableOrders)->whereNotNull('canal')->distinct()->orderBy('canal')->pluck('canal') : collect(),
            'statuses' => (clone $availableOrders)->distinct()->orderBy('estado')->pluck('estado'),
            'hasSite' => $this->tieneSede(),
            'hasChannel' => $this->tieneCanal(),
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
        // Solicita al servicio o dependencia la operacion indicada en esta instruccion.
        $this->autorizarPanel($request->user(), $authorization);
        $authorization->assertAllowed($request->user(), 'gestion.analitica.reportes.exportar');
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless(in_array($format, ['pdf', 'excel', 'csv'], true), 404);
        // Guarda en $filters el resultado necesario para continuar esta operacion.
        $filters = $this->filtros($request, $authorization);
        // Obtiene en $orders la informacion de la solicitud o del usuario actual.
        $orders = $this->pedidos($request->user(), $filters, $authorization);
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_if((clone $orders)->limit(self::MAX_EXPORT_ROWS + 1)->pluck('id')->count() > self::MAX_EXPORT_ROWS, 422, 'El período supera el límite de exportación. Reduce los filtros.');

        // Guarda en $hasChannel el resultado necesario para continuar esta operacion.
        $hasChannel = $this->tieneCanal();
        // Guarda en $hasSite el resultado necesario para continuar esta operacion.
        $hasSite = $this->tieneSede();
        // Agrupa en $columns los datos que se utilizaran en la siguiente operacion.
        $columns = ['Pedido', 'Fecha', 'Estado'];
        // Comprueba la condicion $hasChannel antes de ejecutar este bloque.
        if ($hasChannel) {
            $columns[] = 'Canal';
        }
        // Guarda en $siteColumnIndex el resultado necesario para continuar esta operacion.
        $siteColumnIndex = null;
        // Comprueba la condicion $hasSite antes de ejecutar este bloque.
        if ($hasSite) {
            // Guarda en $siteColumnIndex el resultado necesario para continuar esta operacion.
            $siteColumnIndex = count($columns);
            $columns[] = 'Sede ID';
        }
        $columns[] = 'Importe del pedido PEN (no cobro)';
        $columnTypes = [0 => 'integer', 1 => 'datetime'];
        // Comprueba la condicion $siteColumnIndex !== null antes de ejecutar este bloque.
        if ($siteColumnIndex !== null) {
            $columnTypes[$siteColumnIndex] = 'integer';
        }
        $columnTypes[count($columns) - 1] = 'currency';

        // Guarda en $rows el resultado necesario para continuar esta operacion.
        $rows = $orders->orderByDesc('creado_en')->get()->map(static function (Pedido $order) use ($format, $hasChannel, $hasSite): array {
            // Agrupa en $row los datos que se utilizaran en la siguiente operacion.
            $row = [
                $order->id,
                $order->creado_en?->format('Y-m-d H:i') ?? '',
                self::textoExportacionSeguro((string) $order->estado, $format),
            ];
            // Comprueba la condicion $hasChannel antes de ejecutar este bloque.
            if ($hasChannel) {
                $row[] = self::textoExportacionSeguro((string) ($order->canal ?? ''), $format);
            }
            // Comprueba la condicion $hasSite antes de ejecutar este bloque.
            if ($hasSite) {
                $row[] = $order->sede_id ?? '';
            }
            $row[] = number_format((float) $order->total, 2, '.', '');

            return $row;
        })->all();
        $statuses = array_unique(array_map(static fn (array $row): string => (string) ($row[2] ?? ''), $rows));
        // Agrupa en $report los datos que se utilizaran en la siguiente operacion.
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
        // Guarda en $response el resultado necesario para continuar esta operacion.
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
    private function filtros(Request $request, ServicioAutorizacion $authorization): array
    {
        // Guarda en $data el resultado necesario para continuar esta operacion.
        $data = $request->validate([
            'periodo' => ['nullable', 'integer', Rule::in([7, 30, 90])],
            'estado' => ['nullable', 'string', 'max:40'],
            'canal' => ['nullable', 'string', 'max:30'],
            'sede' => ['nullable', 'integer', 'min:1'],
        ], [
            'periodo.in' => 'El período debe ser de 7, 30 o 90 días.',
            'sede.integer' => 'La sede seleccionada no es válida.',
        ]);

        // Agrupa en $filters los datos que se utilizaran en la siguiente operacion.
        $filters = [
            'periodo' => (int) ($data['periodo'] ?? 7),
            'estado' => $data['estado'] ?? null,
            'canal' => $data['canal'] ?? null,
            'sede' => isset($data['sede']) ? (int) $data['sede'] : null,
        ];
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_if($filters['sede'] !== null && ! $this->tieneSede(), 422, 'El filtro de sede no está disponible.');
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_if($filters['canal'] !== null && ! $this->tieneCanal(), 422, 'El filtro de canal no está disponible.');

        // Obtiene en $availableSites la informacion de la solicitud o del usuario actual.
        $availableSites = $this->sedesDisponibles($request->user(), $authorization);
        // Comprueba la condicion $filters['sede'] !== null antes de ejecutar este bloque.
        if ($filters['sede'] !== null) {
            // Detiene la solicitud si se cumple la validacion de seguridad indicada.
            abort_unless($availableSites->contains('id', $filters['sede']), 403);
        }

        // Obtiene en $availableOrders la informacion de la solicitud o del usuario actual.
        $availableOrders = $this->pedidos($request->user(), $filters, $authorization, false);
        // Comprueba la condicion $filters['estado'] !== null antes de ejecutar este bloque.
        if ($filters['estado'] !== null) {
            // Detiene la solicitud si se cumple la validacion de seguridad indicada.
            abort_unless((clone $availableOrders)->where('estado', $filters['estado'])->exists(), 422, 'El estado seleccionado no existe en el período autorizado.');
        }
        // Comprueba la condicion $filters['canal'] !== null antes de ejecutar este bloque.
        if ($filters['canal'] !== null) {
            // Detiene la solicitud si se cumple la validacion de seguridad indicada.
            abort_unless((clone $availableOrders)->where('canal', $filters['canal'])->exists(), 422, 'El canal seleccionado no existe en el período autorizado.');
        }

        return $filters;
    }

    /** @param array{periodo:int,estado:?string,canal:?string,sede:?int} $filters */
    private function pedidos(User $user, array $filters, ServicioAutorizacion $authorization, bool $applySelectedFilters = true): Builder
    {
        // Prepara en $query la consulta que obtiene o modifica los registros necesarios.
        $query = Pedido::query()
            ->where('creado_en', '>=', Carbon::now()->subDays($filters['periodo'])->startOfDay())
            ->where('creado_en', '<=', now())
            ->when($applySelectedFilters ? $filters['estado'] : null, static fn (Builder $query, string $status): Builder => $query->where('estado', $status))
            ->when($applySelectedFilters ? $filters['canal'] : null, static fn (Builder $query, string $channel): Builder => $query->where('canal', $channel))
            ->when($applySelectedFilters ? $filters['sede'] : null, static fn (Builder $query, int $siteId): Builder => $query->where('sede_id', $siteId))
            ->orderByDesc('creado_en');

        // Comprueba la condicion $authorization->isSeeded() antes de ejecutar este bloque.
        if ($authorization->isSeeded()) {
            // Guarda en $isGlobalAdministrator el resultado necesario para continuar esta operacion.
            $isGlobalAdministrator = $authorization->allowsGlobal($user, 'gestion.seguridad.consultar');
            // Comprueba la condicion $isGlobalAdministrator antes de ejecutar este bloque.
            if ($isGlobalAdministrator) {
                // Comprueba la condicion Schema::hasColumn('pedidos', 'empresa_id') antes de ejecutar este bloque.
                if (Schema::hasColumn('pedidos', 'empresa_id')) {
                    // Guarda en $companyId el resultado necesario para continuar esta operacion.
                    $companyId = $authorization->activeCompanyId();
                    $companyId === null ? $query->whereRaw('1 = 0') : $query->where('empresa_id', $companyId);
                }
            } else {
                // Detiene la solicitud si se cumple la validacion de seguridad indicada.
                abort_unless($this->tieneSede(), 403);
                $query->whereIn('sede_id', $this->idsSedesAsignadas($user, $authorization));
            }
            // Comprueba la condicion ! $isGlobalAdministrator && Schema::hasColumn('pedidos', 'empresa_id') && Schema::hasTable('sedes') && Schema::hasColumn('sedes', 'empresa_id') antes de ejecutar este bloque.
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
    private function autorizarPanel(User $user, ServicioAutorizacion $authorization): void
    {
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($authorization->allows($user, 'gestion.seguridad.consultar'), 403);
        $authorization->assertAllowed($user, 'gestion.panel.consultar');
    }

    /** @return Collection<int, object> */
    private function sedesDisponibles(User $user, ServicioAutorizacion $authorization): Collection
    {
        // Comprueba la condicion ! $this->tieneSede() antes de ejecutar este bloque.
        if (! $this->tieneSede()) {
            return collect();
        }

        return DB::table('sedes')
            ->when(Schema::hasColumn('sedes', 'activa'), static fn ($query) => $query->where('activa', true))
            ->when(
                // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
                Schema::hasTable('empresas') && Schema::hasColumn('sedes', 'empresa_id') && Schema::hasColumn('empresas', 'activo'),
                static fn ($query) => $query->join('empresas as dashboard_empresas', 'dashboard_empresas.id', '=', 'sedes.empresa_id')
                    ->where('dashboard_empresas.activo', true),
            )
            ->when($authorization->isSeeded() && ! $this->tieneAccesoOrganizacionGlobal($user), fn ($query) => $query->whereIn('sedes.id', $this->idsSedesAsignadas($user, $authorization)))
            ->orderBy('sedes.nombre')
            ->get(['sedes.id', 'sedes.nombre']);
    }

    /**
     * Realiza la operación específica definida por este método y devuelve su resultado.
     *
     * @param  mixed  $user  Parámetro recibido por la operación.
     * @return bool Resultado de la operación.
     */
    private function tieneAccesoOrganizacionGlobal(User $user): bool
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
    private function idsSedesAsignadas(User $user, ServicioAutorizacion $authorization): array
    {
        // Prepara en $siteAssignments la consulta que obtiene o modifica los registros necesarios.
        $siteAssignments = DB::table('usuarios_sedes_roles')
            ->join('roles', 'roles.id', '=', 'usuarios_sedes_roles.rol_id')
            ->join('sedes as dashboard_sedes', 'dashboard_sedes.id', '=', 'usuarios_sedes_roles.sede_id')
            ->when(Schema::hasColumn('sedes', 'activa'), static fn ($query) => $query->where('dashboard_sedes.activa', true))
            ->when(
                // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
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
        // Guarda en $idSedeActiva el resultado necesario para continuar esta operacion.
        $idSedeActiva = $authorization->activeSiteIdFor($user);

        return $idSedeActiva === null ? $siteIds : array_values(array_intersect($siteIds, [$idSedeActiva]));
    }

    /**
     * Realiza la operación específica definida por este método y devuelve su resultado.
     *
     * @return bool Resultado de la operación.
     */
    private function tieneSede(): bool
    {
        return Schema::hasTable('sedes') && Schema::hasColumn('pedidos', 'sede_id');
    }

    /**
     * Realiza la operación específica definida por este método y devuelve su resultado.
     *
     * @return bool Resultado de la operación.
     */
    private function tieneCanal(): bool
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
    private static function textoExportacionSeguro(string $value, string $format): string
    {
        // Guarda en $clean el resultado necesario para continuar esta operacion.
        $clean = trim(preg_replace('/[\p{C}\p{Z}]+/u', ' ', $value) ?? '');
        // Comprueba la condicion $format === 'pdf' antes de ejecutar este bloque.
        if ($format === 'pdf') {
            // Guarda en $clean el resultado necesario para continuar esta operacion.
            $clean = mb_convert_encoding(mb_convert_encoding($clean, 'Windows-1252', 'UTF-8'), 'UTF-8', 'Windows-1252');
        }

        return preg_match('/^[=+\-@]/u', $clean) === 1 ? "'".$clean : $clean;
    }
}
