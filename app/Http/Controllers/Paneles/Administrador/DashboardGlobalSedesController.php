<?php

namespace App\Http\Controllers\Paneles\Administrador;

use App\Http\Controllers\Controller;
use App\Servicios\ExportadorTabular;
use App\Servicios\RegistradorAuditoria;
use App\Servicios\ServicioAutorizacion;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Define la responsabilidad de la clase DashboardGlobalSedesController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
final class DashboardGlobalSedesController extends Controller
{
    /**
     * Obtiene y prepara la información solicitada para la operación.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $authorization  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function index(Request $request, ServicioAutorizacion $authorization): View
    {
        $filters = $this->filters($request);
        $sites = $this->sites();
        $results = $this->results($filters, $sites);
        $totalOrders = $results->sum('pedidos');
        $totalIncome = $results->sum('ingresos');
        $maxIncome = max(1, (float) $results->max('ingresos'));
        $siteBars = $results->take(8)->map(static fn ($site): array => [
            'etiqueta' => $site->nombre,
            'valor' => 'S/ '.number_format($site->ingresos, 2),
            'altura' => $site->ingresos > 0 ? max(4, round($site->ingresos / $maxIncome * 100)) : 0,
        ])->values();
        $exportParameters = array_filter([
            'periodo' => $filters['periodo'],
            'sede' => $filters['sede'],
            'canal' => $filters['canal'],
        ]);
        $metrics = collect([
            ['titulo' => 'Sedes registradas', 'icono' => 'bi-shop', 'valor' => number_format($results->count()), 'detalle' => 'Sedes incluidas en el filtro'],
            ['titulo' => 'Importe de pedidos', 'icono' => 'bi-coin', 'tono' => 'verde', 'valor' => 'S/ '.number_format($totalIncome, 2), 'detalle' => 'Pedidos no cancelados del período'],
            ['titulo' => 'Ticket promedio', 'icono' => 'bi-tag', 'tono' => 'violeta', 'valor' => 'S/ '.number_format($totalOrders ? $totalIncome / $totalOrders : 0, 2), 'detalle' => 'Por pedido no cancelado'],
            ['titulo' => 'Crecimiento', 'icono' => 'bi-graph-up', 'tono' => 'ambar', 'valor' => '—', 'detalle' => 'Pendiente de comparación entre períodos'],
            ['titulo' => 'Pedidos', 'icono' => 'bi-calendar-check', 'valor' => number_format($totalOrders), 'detalle' => 'Pedidos no cancelados por sede'],
            ['titulo' => 'Cumplimiento', 'icono' => 'bi-check-circle', 'tono' => 'verde', 'valor' => '—', 'detalle' => 'Sin metas por sede configuradas'],
        ]);

        return view('gestion.analitica.dashboard-global.sedes.listar', [
            'filters' => $filters,
            'sites' => $sites,
            'results' => $results,
            'totalOrders' => $totalOrders,
            'totalIncome' => $totalIncome,
            'siteBars' => $siteBars,
            'exportParameters' => $exportParameters,
            'hasSite' => $this->hasSite(),
            'hasChannel' => $this->hasChannel(),
            'channels' => $this->hasChannel() ? DB::table('pedidos')->whereNotNull('canal')->distinct()->orderBy('canal')->pluck('canal') : collect(),
            'canExport' => $authorization->allows($request->user(), 'gestion.analitica.reportes.exportar'),
            'titulo' => 'Indicadores por sede',
            'icono' => 'bi-buildings',
            'descripcion' => 'Analiza los pedidos y sus importes registrados por sede.',
            'exportRoute' => $authorization->allows($request->user(), 'gestion.analitica.reportes.exportar') ? 'management.reports.dashboard_sites.export' : null,
            'metrics' => $metrics,
            'periodOptions' => [7 => 'Últimos 7 días', 30 => 'Últimos 30 días', 90 => 'Últimos 90 días'],
            'siteChartDescription' => 'Importes reales de pedidos por sede',
            'siteChart' => ['descripcion' => 'Importes reales de pedidos por sede', 'barras' => $siteBars],
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
        $authorization->assertAllowed($request->user(), 'gestion.analitica.reportes.exportar');
        abort_unless(in_array($format, ['pdf', 'excel', 'csv'], true), 404);

        $filters = $this->filters($request);
        $results = $this->results($filters, $this->sites());
        $rows = $results->map(static fn (object $site): array => [
            $site->id,
            $site->nombre,
            $site->pedidos,
            number_format($site->ingresos, 2, '.', ''),
        ])->all();

        $columns = ['Sede ID', 'Sede', 'Pedidos', 'Importe nominal de pedidos PEN (no cobro)'];
        $totalOrders = array_sum(array_map(static fn (array $row): int => (int) ($row[2] ?? 0), $rows));
        $nominalOrderAmount = array_sum(array_map(static fn (array $row): float => (float) ($row[3] ?? 0), $rows));
        $report = [
            'title' => 'REPORTE DE PEDIDOS POR SEDE',
            'subtitle' => 'Resumen por sede de pedidos no anulados dentro del período consultado.',
            'module' => 'Dashboard Global / Sedes',
            'report_name' => 'Reporte de pedidos por sede',
            'period' => 'Últimos '.$filters['periodo'].' días',
            'filters' => [
                'Período' => $filters['periodo'].' días',
                'Sede' => $filters['sede'] === null ? 'Todas las sedes incluidas en la consulta' : 'ID '.$filters['sede'],
                'Canal' => $filters['canal'] ?? 'Todos',
            ],
            'generated_by' => (string) $request->user()->nombre,
            'role' => 'Administrador',
            'scope' => $filters['sede'] === null ? 'Conjunto de sedes retornado por la consulta actual.' : 'Sede ID '.$filters['sede'],
            'indicators' => [
                ['label' => 'Sedes incluidas', 'value' => count($rows)],
                ['label' => 'Pedidos incluidos', 'value' => $totalOrders],
                ['label' => 'Importe nominal PEN (no cobro)', 'value' => number_format($nominalOrderAmount, 2, '.', ',')],
            ],
            'column_types' => [0 => 'integer', 2 => 'integer', 3 => 'currency'],
            'summary_row' => [
                'label' => 'Total del conjunto filtrado',
                'values' => ['', 'Suma de sedes incluidas', $totalOrders, $nominalOrderAmount],
            ],
            'observations' => ['El importe suma los totales de pedidos elegibles y no acredita cobros recibidos.'],
        ];
        $response = $exporter->download($format, 'pedidos-por-sede-'.$filters['periodo'].'-dias', $columns, $rows, $report);
        $auditor->record((int) $request->user()->getKey(), 'exportar', 'sedes', null, null, [
            'formato' => $format,
            'periodo' => $filters['periodo'],
            'sede_id' => $filters['sede'],
            'cantidad' => count($rows),
        ], $request->ip());

        return $response;
    }

    /** @return array{periodo:int,sede:?int,canal:?string} */
    private function filters(Request $request): array
    {
        $data = $request->validate([
            'periodo' => ['nullable', 'integer', Rule::in([7, 30, 90])],
            'sede' => ['nullable', 'integer', 'min:1'],
            'canal' => ['nullable', 'string', 'max:30'],
        ]);

        return [
            'periodo' => (int) ($data['periodo'] ?? 30),
            'sede' => $this->hasSite() ? ($data['sede'] ?? null) : null,
            'canal' => $this->hasChannel() ? ($data['canal'] ?? null) : null,
        ];
    }

    /** @return Collection<int, object> */
    private function sites(): Collection
    {
        return Schema::hasTable('sedes') ? DB::table('sedes')->orderBy('nombre')->get(['id', 'nombre']) : collect();
    }

    /**
     * @param  array{periodo:int,sede:?int,canal:?string}  $filters
     * @param  Collection<int, object>  $sites
     * @return Collection<int, object>
     */
    private function results(array $filters, Collection $sites): Collection
    {
        if (! $this->hasSite()) {
            return collect();
        }

        $totals = DB::table('pedidos')
            ->select('sede_id')
            ->selectRaw('COUNT(*) AS pedidos, SUM(total) AS ingresos')
            ->whereNotNull('sede_id')
            ->where('creado_en', '>=', now()->subDays($filters['periodo'])->startOfDay())
            ->whereRaw('LOWER(estado) NOT IN (?, ?)', ['cancelado', 'anulado'])
            ->when($filters['sede'], static fn ($query, $siteId) => $query->where('sede_id', $siteId))
            ->when($filters['canal'], static fn ($query, $channel) => $query->where('canal', $channel))
            ->groupBy('sede_id')
            ->get()
            ->keyBy('sede_id');

        return $sites->when($filters['sede'], static fn (Collection $items, int $siteId) => $items->where('id', $siteId))
            ->map(static function (object $site) use ($totals): object {
                $total = $totals->get($site->id);

                return (object) [
                    'id' => $site->id,
                    'nombre' => $site->nombre,
                    'pedidos' => (int) ($total->pedidos ?? 0),
                    'ingresos' => (float) ($total->ingresos ?? 0),
                ];
            })
            ->sortByDesc('ingresos')
            ->values();
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
}
