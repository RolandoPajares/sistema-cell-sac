<?php

namespace App\Http\Controllers\Gestion\Reportes;

use App\Http\Controllers\Controller;
use App\Http\Requests\FiltroReporteRequest;
use App\Models\Catalogo\Categoria;
use App\Models\Catalogo\Producto;
use App\Models\Clientes\Cliente;
use App\Models\Comercio\DetallePedido;
use App\Models\Comercio\Pedido;
use App\Models\Inventario\MovimientoInventario;
use App\Models\Marketing\Campania;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteController extends Controller
{
    public function index(FiltroReporteRequest $request): View
    {
        $filters = $this->reportFilters($request->validated());
        $period = $this->period($filters);
        $salesQuery = $this->salesQuery($filters);
        $summary = (clone $salesQuery)
            ->selectRaw('COALESCE(SUM(total), 0) as total_sales, COUNT(*) as total_orders')
            ->first();
        $details = $this->detailQuery($filters)
            ->with(['product:id,nombre,categoria,categoria_id,precio,url_imagen', 'product.category:id,nombre'])
            ->get();
        $trend = $this->salesTrend($filters, $period['from'], $period['to']);
        $campaignMetrics = Campania::query()
            ->whereBetween('creado_en', [$period['from'], $period['to']])
            ->selectRaw('COALESCE(SUM(vistas), 0) as views, COALESCE(SUM(clics), 0) as clicks')
            ->first();
        $activeProducts = Producto::query()->where('activo', true)->count();
        $productReport = [
            'active' => $activeProducts,
            'inactive' => Producto::query()->where('activo', false)->count(),
            'categories' => Categoria::query()->count(),
            'catalog' => Producto::query()
                ->with('category:id,nombre')
                ->orderByDesc('creado_en')
                ->limit(8)
                ->get(['id', 'nombre', 'marca', 'categoria', 'categoria_id', 'precio', 'existencias', 'activo']),
        ];
        $customerReport = $this->customerReport($filters);
        $inventoryReport = $this->inventoryReport();
        $campaignReport = $this->campaignReport($period['from'], $period['to']);

        return view('gestion.reportes.listar', [
            'filters' => $filters,
            'activeView' => $this->activeView($request),
            'periodLabel' => $this->periodLabel($request, $period['from'], $period['to']),
            'sales' => (float) ($summary?->total_sales ?? 0),
            'orders' => (int) ($summary?->total_orders ?? 0),
            'productsSold' => (int) $details->sum(static fn (DetallePedido $detail): int => (int) $detail->cantidad),
            'lowStock' => Producto::query()->where('activo', true)->where('existencias', '<=', 5)->count(),
            'activeProducts' => $activeProducts,
            'campaignMetrics' => [
                'views' => (int) ($campaignMetrics?->views ?? 0),
                'clicks' => (int) ($campaignMetrics?->clicks ?? 0),
            ],
            'salesTrend' => $trend,
            'categorySales' => $this->categorySales($details),
            'topProducts' => $this->topProducts($details),
            'inventorySummary' => $this->inventorySummary(),
            'latestOrders' => $this->latestOrders($filters),
            'orderStatusSummary' => $this->orderStatusSummary($filters),
            'productReport' => $productReport,
            'customerReport' => $customerReport,
            'inventoryReport' => $inventoryReport,
            'campaignReport' => $campaignReport,
        ]);
    }

    public function export(FiltroReporteRequest $request): StreamedResponse
    {
        $orders = $this->salesQuery($request->validated())
            ->select(['id', 'usuario_id', 'subtotal', 'descuento', 'total', 'estado', 'creado_en'])
            ->orderBy('creado_en')
            ->orderBy('id')
            ->cursor();

        return response()->streamDownload(function () use ($orders): void {
            $output = fopen('php://output', 'w');

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['id', 'usuario_id', 'subtotal', 'descuento', 'total', 'estado', 'creado_en']);

            foreach ($orders as $order) {
                fputcsv($output, [
                    $order->id,
                    $order->usuario_id,
                    $order->subtotal,
                    $order->descuento,
                    $order->total,
                    $order->estado,
                    $order->creado_en?->toDateTimeString(),
                ]);
            }

            fclose($output);
        }, 'reporte-pedidos.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<Pedido>
     */
    private function salesQuery(array $filters): Builder
    {
        return $this->applyDateFilters(
            Pedido::query()->whereNotIn('estado', ['Cancelado', 'Cancelada']),
            $filters,
        );
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<DetallePedido>
     */
    private function detailQuery(array $filters): Builder
    {
        return DetallePedido::query()->whereHas('order', function (Builder $query) use ($filters): void {
            $this->applyDateFilters($query->whereNotIn('estado', ['Cancelado', 'Cancelada']), $filters);
        });
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{from:CarbonImmutable,to:CarbonImmutable}
     */
    private function period(array $filters): array
    {
        $from = isset($filters['desde'])
            ? CarbonImmutable::parse((string) $filters['desde'])->startOfDay()
            : CarbonImmutable::now()->subDays(29)->startOfDay();
        $to = isset($filters['hasta'])
            ? CarbonImmutable::parse((string) $filters['hasta'])->endOfDay()
            : CarbonImmutable::now()->endOfDay();

        return ['from' => $from, 'to' => $to];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, string>
     */
    private function reportFilters(array $filters): array
    {
        $period = $this->period($filters);

        return [
            'desde' => $period['from']->toDateString(),
            'hasta' => $period['to']->toDateString(),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<int, array{label:string,sales:float,orders:int,sales_height:float,orders_height:float}>
     */
    private function salesTrend(array $filters, CarbonImmutable $from, CarbonImmutable $to): array
    {
        $rows = $this->salesQuery($filters)
            ->selectRaw('DATE(creado_en) as day, COALESCE(SUM(total), 0) as sales, COUNT(*) as orders')
            ->groupByRaw('DATE(creado_en)')
            ->orderBy('day')
            ->get()
            ->keyBy('day');
        $days = $from->diffInDays($to) + 1;
        $points = [];

        if ($days <= 31) {
            for ($index = 0; $index < $days; $index++) {
                $date = $from->addDays($index);
                $row = $rows->get($date->toDateString());
                $points[] = [
                    'label' => $date->format('d M'),
                    'sales' => (float) ($row?->sales ?? 0),
                    'orders' => (int) ($row?->orders ?? 0),
                ];
            }
        } else {
            foreach ($rows as $row) {
                $date = CarbonImmutable::parse((string) $row->day);
                $points[] = [
                    'label' => $date->format('d M'),
                    'sales' => (float) $row->sales,
                    'orders' => (int) $row->orders,
                ];
            }
        }

        $maxSales = max(1, ...array_column($points, 'sales'));
        $maxOrders = max(1, ...array_column($points, 'orders'));

        return array_map(static function (array $point) use ($maxSales, $maxOrders): array {
            return [
                'label' => $point['label'],
                'sales' => $point['sales'],
                'orders' => $point['orders'],
                'sales_height' => $point['sales'] > 0 ? max(5, round(($point['sales'] / $maxSales) * 100, 2)) : 0,
                'orders_height' => $point['orders'] > 0 ? max(5, round(($point['orders'] / $maxOrders) * 100, 2)) : 0,
            ];
        }, $points);
    }

    /**
     * @param  Collection<int, DetallePedido>  $details
     * @return array<int, array{name:string,revenue:float,percent:float,color:string}>
     */
    private function categorySales(Collection $details): array
    {
        $total = (float) $details->sum(static fn (DetallePedido $detail): float => (float) $detail->cantidad * (float) $detail->precio_unitario);
        $colors = ['#2583f7', '#18b78d', '#139bc3', '#f35b3e', '#fb9a17', '#7b7cf6'];
        $rows = $details
            ->groupBy(static fn (DetallePedido $detail): string => $detail->product?->category?->nombre ?? $detail->product?->categoria ?? 'Sin categoría')
            ->map(function (Collection $categoryDetails, string $name) use ($total): array {
                $revenue = (float) $categoryDetails->sum(static fn (DetallePedido $detail): float => (float) $detail->cantidad * (float) $detail->precio_unitario);

                return [
                    'name' => $name,
                    'revenue' => $revenue,
                    'percent' => $total > 0 ? round(($revenue / $total) * 100, 1) : 0,
                ];
            })
            ->sortByDesc('revenue')
            ->take(6)
            ->values()
            ->all();

        return array_map(static fn (array $row, int $index): array => $row + ['color' => $colors[$index] ?? '#9bb2d5'], $rows, array_keys($rows));
    }

    /**
     * @param  Collection<int, DetallePedido>  $details
     * @return array<int, array{name:string,units:int,revenue:float,image:?string}>
     */
    private function topProducts(Collection $details): array
    {
        return $details
            ->groupBy('producto_id')
            ->map(static function (Collection $productDetails): array {
                $product = $productDetails->first()?->product;

                return [
                    'name' => $product?->nombre ?? 'Producto',
                    'units' => (int) $productDetails->sum(static fn (DetallePedido $detail): int => (int) $detail->cantidad),
                    'revenue' => (float) $productDetails->sum(static fn (DetallePedido $detail): float => (float) $detail->cantidad * (float) $detail->precio_unitario),
                    'image' => $product?->url_imagen,
                ];
            })
            ->sortByDesc('revenue')
            ->take(5)
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{label:string,count:int,color:string}>
     */
    private function inventorySummary(): array
    {
        return [
            ['label' => 'En stock', 'count' => Producto::query()->where('activo', true)->where('existencias', '>', 10)->count(), 'color' => '#18b78d'],
            ['label' => 'Stock bajo', 'count' => Producto::query()->where('activo', true)->whereBetween('existencias', [1, 5])->count(), 'color' => '#f7a719'],
            ['label' => 'Sin stock', 'count' => Producto::query()->where('activo', true)->where('existencias', 0)->count(), 'color' => '#f23c52'],
            ['label' => 'En tránsito', 'count' => 0, 'color' => '#2583f7'],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function latestOrders(array $filters): Collection
    {
        return $this->applyDateFilters(Pedido::query(), $filters)
            ->with([
                'user:id,nombre,correo',
                'user.customer:id,usuario_id,razon_social,nombre_contacto',
            ])
            ->withCount('details')
            ->latest('creado_en')
            ->latest('id')
            ->limit(5)
            ->get();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{total:int,items:array<int,array{key:string,label:string,count:int,color:string}>}
     */
    private function orderStatusSummary(array $filters): array
    {
        $counts = $this->applyDateFilters(
            Pedido::query()->selectRaw('estado, COUNT(*) as total'),
            $filters,
        )
            ->groupBy('estado')
            ->pluck('total', 'estado');
        $statuses = [
            ['key' => 'Pendiente', 'label' => 'Pendientes', 'color' => '#f7a719'],
            ['key' => 'En proceso', 'label' => 'En preparación', 'color' => '#2583f7'],
            ['key' => 'Enviado', 'label' => 'Enviados', 'color' => '#7b55ed'],
            ['key' => 'Entregado', 'label' => 'Entregados', 'color' => '#18b78d'],
            ['key' => 'Cancelado', 'label' => 'Cancelados', 'color' => '#f23c52'],
        ];
        $items = array_map(static function (array $status) use ($counts): array {
            return $status + ['count' => (int) $counts->get($status['key'], 0)];
        }, $statuses);

        return [
            'total' => array_sum(array_column($items, 'count')),
            'items' => $items,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{total:int,active:int,mayoristas:int,minoristas:int,top:Collection<int,Pedido>}
     */
    private function customerReport(array $filters): array
    {
        $typeCounts = Cliente::query()
            ->where('activo', true)
            ->selectRaw('tipo, COUNT(*) as total')
            ->groupBy('tipo')
            ->pluck('total', 'tipo');
        $topCustomers = $this->applyDateFilters(
            Pedido::query()
                ->whereNotIn('estado', ['Cancelado', 'Cancelada'])
                ->selectRaw('usuario_id, COALESCE(SUM(total), 0) as customer_sales, COUNT(*) as customer_orders'),
            $filters,
        )
            ->groupBy('usuario_id')
            ->orderByDesc('customer_sales')
            ->with([
                'user:id,nombre,correo',
                'user.customer:id,usuario_id,razon_social,nombre_contacto,tipo',
            ])
            ->limit(5)
            ->get();

        return [
            'total' => Cliente::query()->count(),
            'active' => (int) $typeCounts->sum(),
            'mayoristas' => (int) $typeCounts->get('mayorista', 0),
            'minoristas' => (int) $typeCounts->get('minorista', 0),
            'top' => $topCustomers,
        ];
    }

    /**
     * @return array{value:float,movements:int,lowStock:Collection<int,Producto>,recentMovements:Collection<int,MovimientoInventario>}
     */
    private function inventoryReport(): array
    {
        return [
            'value' => (float) Producto::query()
                ->where('activo', true)
                ->selectRaw('COALESCE(SUM(existencias * precio), 0) as inventory_value')
                ->value('inventory_value'),
            'movements' => MovimientoInventario::query()->count(),
            'lowStock' => Producto::query()
                ->where('activo', true)
                ->where('existencias', '<=', 5)
                ->with('category:id,nombre')
                ->orderBy('existencias')
                ->orderBy('nombre')
                ->limit(8)
                ->get(['id', 'nombre', 'marca', 'categoria', 'categoria_id', 'existencias']),
            'recentMovements' => MovimientoInventario::query()
                ->with('product:id,nombre,marca')
                ->latest('creado_en')
                ->limit(8)
                ->get(),
        ];
    }

    /**
     * @return array{active:int,total:int,locations:Collection<int,Campania>,items:Collection<int,Campania>}
     */
    private function campaignReport(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $periodQuery = Campania::query()->whereBetween('creado_en', [$from, $to]);
        $locationRows = (clone $periodQuery)
            ->selectRaw('ubicacion, COUNT(*) as total, COALESCE(SUM(vistas), 0) as views, COALESCE(SUM(clics), 0) as clicks')
            ->groupBy('ubicacion')
            ->orderByDesc('views')
            ->get();

        return [
            'active' => (int) (clone $periodQuery)->where('activo', true)->count(),
            'total' => (int) (clone $periodQuery)->count(),
            'locations' => $locationRows,
            'items' => (clone $periodQuery)
                ->orderByDesc('vistas')
                ->limit(8)
                ->get(['id', 'nombre', 'titulo', 'ubicacion', 'activo', 'vistas', 'clics', 'creado_en']),
        ];
    }

    private function activeView(FiltroReporteRequest $request): string
    {
        $view = $request->string('vista')->toString();

        return in_array($view, ['resumen', 'ventas', 'pedidos', 'productos', 'inventario', 'clientes', 'campanas'], true)
            ? $view
            : 'resumen';
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function periodLabel(FiltroReporteRequest $request, CarbonImmutable $from, CarbonImmutable $to): string
    {
        if (! $request->filled('desde') && ! $request->filled('hasta')) {
            return 'Últimos 30 días';
        }

        return $from->format('d/m/Y').' - '.$to->format('d/m/Y');
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function applyDateFilters(Builder $query, array $filters): Builder
    {
        foreach (['desde' => '>=', 'hasta' => '<='] as $field => $operator) {
            if (! isset($filters[$field])) {
                continue;
            }

            $query->whereDate('creado_en', $operator, $filters[$field]);
        }

        return $query;
    }
}
