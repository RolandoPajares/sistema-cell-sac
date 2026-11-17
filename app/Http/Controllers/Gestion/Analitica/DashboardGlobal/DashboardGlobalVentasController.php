<?php

namespace App\Http\Controllers\Gestion\Analitica\DashboardGlobal;

use App\Http\Controllers\Controller;
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
 * Define la responsabilidad de la clase DashboardGlobalVentasController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
final class DashboardGlobalVentasController extends Controller
{
    private const BUSINESS_TIMEZONE = 'America/Lima';

    private const STORAGE_TIMEZONE = 'UTC';

    /** Bound the in-memory PDF/XLSX payload until streaming export infrastructure exists. */
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
        $orders = $this->pedidos($request->user(), $filters, $authorization);
        // Guarda en $registeredCount el resultado necesario para continuar esta operacion.
        $registeredCount = (clone $orders)->count();
        // Guarda en $nonCancelledCount el resultado necesario para continuar esta operacion.
        $nonCancelledCount = (clone $orders)->whereNotIn('estado', ['Cancelado', 'Cancelada'])->count();
        // Guarda en $dailyTotals el resultado necesario para continuar esta operacion.
        $dailyTotals = collect();
        // Guarda en $channelTotals el resultado necesario para continuar esta operacion.
        $channelTotals = collect();
        // Guarda en $maxDaily el resultado necesario para continuar esta operacion.
        $maxDaily = max(1, (float) $dailyTotals->max());
        // Guarda en $maxChannel el resultado necesario para continuar esta operacion.
        $maxChannel = max(1, (float) $channelTotals->max());
        $dailyBars = $dailyTotals->map(static fn ($amount, $day): array => [
            'etiqueta' => Carbon::parse($day)->format('d/m'),
            'valor' => 'S/ '.number_format($amount, 2),
            'altura' => max(4, round($amount / $maxDaily * 100)),
        ])->values();
        $channelBars = $channelTotals->map(static fn ($amount, $name): array => [
            'etiqueta' => $name,
            'valor' => 'S/ '.number_format($amount, 2),
            'altura' => max(4, round($amount / $maxChannel * 100)),
        ])->values();
        // Guarda en $exportParameters el resultado necesario para continuar esta operacion.
        $exportParameters = array_filter([
            'periodo' => $filters['periodo'],
            'sede' => $filters['sede'],
            'canal' => $filters['canal'],
        ]);
        // Guarda en $metrics el resultado necesario para continuar esta operacion.
        $metrics = collect([
            ['titulo' => 'Pedidos registrados', 'icono' => 'bi-cart3', 'valor' => number_format($registeredCount), 'detalle' => 'Pedidos del período'],
            ['titulo' => 'Pedidos no cancelados', 'icono' => 'bi-coin', 'tono' => 'verde', 'valor' => number_format($nonCancelledCount), 'detalle' => 'Excluye pedidos cancelados'],
            ['titulo' => 'Ventas confirmadas', 'icono' => 'bi-tag', 'tono' => 'violeta', 'valor' => '—', 'detalle' => 'Pendiente de fuente de ventas confirmadas'],
            ['titulo' => 'Cobros confirmados', 'icono' => 'bi-percent', 'tono' => 'ambar', 'valor' => '—', 'detalle' => 'Pendiente de conciliación de pagos'],
            ['titulo' => 'Ingresos netos', 'icono' => 'bi-check-circle', 'valor' => '—', 'detalle' => 'Pendiente de cobros y reembolsos'],
            ['titulo' => 'Ticket promedio', 'icono' => 'bi-graph-up', 'tono' => 'verde', 'valor' => '—', 'detalle' => 'Pendiente de ventas confirmadas'],
        ]);

        // Devuelve la vista 'gestion.analitica.dashboard-global.ventas.listar' con los datos preparados por este controlador.
        return view('gestion.analitica.dashboard-global.ventas.listar', [
            'filters' => $filters,
            'sites' => $this->sedesDisponibles($request->user(), $authorization),
            'channels' => $this->tieneCanal() ? (clone $orders)->whereNotNull('canal')->distinct()->orderBy('canal')->pluck('canal') : collect(),
            'hasSite' => $this->tieneSede(),
            'hasChannel' => $this->tieneCanal(),
            'orderCount' => $registeredCount,
            'nonCancelledCount' => $nonCancelledCount,
            'income' => null,
            'averageTicket' => null,
            'completedCount' => null,
            'dailyBars' => $dailyBars,
            'channelBars' => $channelBars,
            'dailyTotals' => $dailyTotals,
            'channelTotals' => $channelTotals,
            'exportParameters' => $exportParameters,
            'canExport' => $authorization->allows($request->user(), 'gestion.analitica.reportes.exportar'),
            'titulo' => 'Indicadores de ventas e ingresos',
            'icono' => 'bi-graph-up-arrow',
            'descripcion' => 'Analiza el desempeño de ventas e ingresos de tu negocio.',
            'exportRoute' => $canExport = $authorization->allows($request->user(), 'gestion.analitica.reportes.exportar') ? 'management.reports.dashboard_sales.export' : null,
            'metrics' => $metrics,
            'periodOptions' => [7 => 'Últimos 7 días', 30 => 'Últimos 30 días', 90 => 'Últimos 90 días'],
            'dailyChartDescription' => 'Ingresos diarios reales',
            'channelChartDescription' => 'Ingresos reales por canal',
            'dailyChart' => ['descripcion' => 'Ingresos diarios reales', 'barras' => $dailyBars],
            'channelChart' => ['descripcion' => 'Ingresos reales por canal', 'barras' => $channelBars],
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
        // Agrupa en $selectColumns los datos que se utilizaran en la siguiente operacion.
        $selectColumns = ['id', 'creado_en', 'estado', 'total'];
        // Agrupa en $columns los datos que se utilizaran en la siguiente operacion.
        $columns = ['Pedido', 'Fecha (Lima)', 'Estado', 'Importe del pedido PEN (no cobro)'];
        $columnTypes = [0 => 'integer', 1 => 'datetime', 3 => 'currency'];
        // Comprueba la condicion $this->tieneSede() antes de ejecutar este bloque.
        if ($this->tieneSede()) {
            $selectColumns[] = 'sede_id';
            $columns[] = 'Sede ID';
            $columnTypes[count($columns) - 1] = 'integer';
        }
        // Comprueba la condicion $this->tieneCanal() antes de ejecutar este bloque.
        if ($this->tieneCanal()) {
            $selectColumns[] = 'canal';
            $columns[] = 'Canal';
        }
        // Guarda en $rows el resultado necesario para continuar esta operacion.
        $rows = $orders->orderBy('id')->limit(self::MAX_EXPORT_ROWS)->get($selectColumns)->map(function (Pedido $order) use ($format): array {
            // Agrupa en $row los datos que se utilizaran en la siguiente operacion.
            $row = [
                $order->id,
                $order->creado_en?->timezone(self::BUSINESS_TIMEZONE)->format('Y-m-d H:i') ?? '',
                self::textoExportacionSeguro($order->estado, $format),
                (string) $order->total,
            ];
            // Comprueba la condicion $this->tieneSede() antes de ejecutar este bloque.
            if ($this->tieneSede()) {
                $row[] = $order->sede_id ?? '';
            }
            // Comprueba la condicion $this->tieneCanal() antes de ejecutar este bloque.
            if ($this->tieneCanal()) {
                $row[] = self::textoExportacionSeguro($order->canal ?? '', $format);
            }

            return $row;
        })->all();

        $distinctStatuses = count(array_unique(array_map(static fn (array $row): string => (string) ($row[2] ?? ''), $rows)));
        // Agrupa en $report los datos que se utilizaran en la siguiente operacion.
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
        // Guarda en $response el resultado necesario para continuar esta operacion.
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
    private function filtros(Request $request, ServicioAutorizacion $authorization): array
    {
        // Guarda en $validated el resultado necesario para continuar esta operacion.
        $validated = $request->validate([
            'periodo' => ['nullable', 'integer', Rule::in([7, 30, 90])],
            'sede' => ['nullable', 'integer', 'min:1'],
            'canal' => ['nullable', 'string', 'max:50'],
        ]);

        // Guarda en $siteId el resultado necesario para continuar esta operacion.
        $siteId = isset($validated['sede']) ? (int) $validated['sede'] : null;
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_if($siteId !== null && ! $this->tieneSede(), 422, 'El filtro de sede no está disponible.');
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_if(isset($validated['canal']) && ! $this->tieneCanal(), 422, 'El filtro de canal no está disponible.');
        // Comprueba la condicion $siteId !== null antes de ejecutar este bloque.
        if ($siteId !== null) {
            // Detiene la solicitud si se cumple la validacion de seguridad indicada.
            abort_unless($this->sedesDisponibles($request->user(), $authorization)->contains('id', $siteId), 403);
        }

        // Agrupa en $filters los datos que se utilizaran en la siguiente operacion.
        $filters = [
            'periodo' => (int) ($validated['periodo'] ?? 30),
            'sede' => $siteId,
            'canal' => null,
        ];

        // Comprueba la condicion isset($validated['canal']) antes de ejecutar este bloque.
        if (isset($validated['canal'])) {
            // Guarda en $channel el resultado necesario para continuar esta operacion.
            $channel = (string) $validated['canal'];
            // Obtiene en $channelExists la informacion de la solicitud o del usuario actual.
            $channelExists = $this->pedidos($request->user(), $filters, $authorization)
                ->where('canal', $channel)
                ->exists();

            // Detiene la solicitud si se cumple la validacion de seguridad indicada.
            abort_unless($channelExists, 422, 'El canal seleccionado no está disponible en el período autorizado.');
            $filters['canal'] = $channel;
        }

        return $filters;
    }

    /** @param array{periodo:int,sede:?int,canal:?string} $filters */
    private function pedidos(User $user, array $filters, ServicioAutorizacion $authorization): Builder
    {
        // Guarda en $now el resultado necesario para continuar esta operacion.
        $now = Carbon::now(self::BUSINESS_TIMEZONE);
        // Guarda en $today el resultado necesario para continuar esta operacion.
        $today = $now->copy()->startOfDay();
        // Guarda en $start el resultado necesario para continuar esta operacion.
        $start = $today->copy()->subDays($filters['periodo'] - 1)->timezone(self::STORAGE_TIMEZONE);
        // Guarda en $end el resultado necesario para continuar esta operacion.
        $end = $now->copy()->timezone(self::STORAGE_TIMEZONE);
        // Prepara en $query la consulta que obtiene o modifica los registros necesarios.
        $query = Pedido::query()
            ->where('creado_en', '>=', $start)
            ->where('creado_en', '<=', $end)
            ->when($filters['sede'], static fn (Builder $query, int $siteId): Builder => $query->where('sede_id', $siteId))
            ->when($filters['canal'], static fn (Builder $query, string $channel): Builder => $query->where('canal', $channel));

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

            // Comprueba la condicion ! $isGlobalAdministrator && Schema::hasColumn('pedidos', 'empresa_id') && Schema::hasColumn('sedes', 'empresa_id') antes de ejecutar este bloque.
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

        // Prepara en $sites la consulta que obtiene o modifica los registros necesarios.
        $sites = DB::table('sedes')
            ->when(Schema::hasColumn('sedes', 'activa'), static fn ($query) => $query->where('activa', true))
            ->when(
                // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
                Schema::hasTable('empresas') && Schema::hasColumn('sedes', 'empresa_id') && Schema::hasColumn('empresas', 'activo'),
                static fn ($query) => $query->join('empresas as dashboard_empresas', 'dashboard_empresas.id', '=', 'sedes.empresa_id')
                    ->where('dashboard_empresas.activo', true),
            )
            ->when($authorization->isSeeded() && ! $this->tieneAccesoOrganizacionGlobal($user), fn ($query) => $query->whereIn('sedes.id', $this->idsSedesAsignadas($user, $authorization)))
            ->orderBy('sedes.nombre');

        return $sites->get(['sedes.id', 'sedes.nombre']);
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
     * Ejecuta la operación safeExportText definida por la clase DashboardGlobalVentasController.
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
