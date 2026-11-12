<?php

namespace App\Http\Controllers\Gestion\Analitica\Reportes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gestion\Analitica\Reportes\FiltroReporteRequest;
use App\Models\Catalogo\Categoria;
use App\Models\Catalogo\Producto;
use App\Models\Clientes\Cliente;
use App\Models\Comercio\DetallePedido;
use App\Models\Comercio\Pedido;
use App\Models\Inventario\MovimientoInventario;
use App\Models\Marketing\Campania;
use App\Servicios\Compartidos\Auditoria\RegistradorAuditoria;
use App\Servicios\Compartidos\Exportacion\ExportadorTabular;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Define la responsabilidad de la clase ReporteController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ReporteController extends Controller
{
    /**
     * Obtiene y prepara la información solicitada para la operación.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function index(FiltroReporteRequest $request): View
    {
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $filters = $this->filtrosReporte($request->validated());
        // Guarda en $period el resultado necesario para continuar esta operacion.
        $period = $this->periodo($filters);
        // Guarda en $salesQuery el resultado necesario para continuar esta operacion.
        $salesQuery = $this->consultaVentas($filters);
        // Guarda en $summary el resultado necesario para continuar esta operacion.
        $summary = (clone $salesQuery)
            ->selectRaw('COALESCE(SUM(total), 0) as total_sales, COUNT(*) as total_orders')
            ->first();
        // Guarda en $details el resultado necesario para continuar esta operacion.
        $details = $this->consultaDetalles($filters)
            ->with(['product:id,nombre,categoria,categoria_id,precio,url_imagen', 'product.category:id,nombre'])
            ->get();
        // Guarda en $trend el resultado necesario para continuar esta operacion.
        $trend = $this->tendenciaVentas($filters, $period['from'], $period['to']);
        // Prepara en $campaignMetrics la consulta que obtiene o modifica los registros necesarios.
        $campaignMetrics = Campania::query()
            ->whereBetween('creado_en', [$period['from'], $period['to']])
            ->selectRaw('COALESCE(SUM(vistas), 0) as views, COALESCE(SUM(clics), 0) as clicks')
            ->first();
        // Prepara en $activeProducts la consulta que obtiene o modifica los registros necesarios.
        $activeProducts = Producto::query()->where('activo', true)->count();
        // Agrupa en $productReport los datos que se utilizaran en la siguiente operacion.
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
        // Guarda en $customerReport el resultado necesario para continuar esta operacion.
        $customerReport = $this->reporteClientes($filters);
        // Guarda en $inventoryReport el resultado necesario para continuar esta operacion.
        $inventoryReport = $this->reporteInventario();
        // Guarda en $campaignReport el resultado necesario para continuar esta operacion.
        $campaignReport = $this->reporteCampanias($period['from'], $period['to']);
        // Guarda en $categorySales el resultado necesario para continuar esta operacion.
        $categorySales = $this->ventasPorCategoria($details);
        // Guarda en $inventorySummary el resultado necesario para continuar esta operacion.
        $inventorySummary = $this->resumenInventario();
        // Guarda en $orderStatusSummary el resultado necesario para continuar esta operacion.
        $orderStatusSummary = $this->resumenEstadosPedidos($filters);
        $customerReport['top'] = $customerReport['top']
            ->map(static function (Pedido $order): array {
                // Guarda en $customer el resultado necesario para continuar esta operacion.
                $customer = $order->user?->customer;

                return [
                    'name' => $customer?->razon_social
                        ?: $customer?->nombre_contacto
                        ?: $order->user?->nombre
                        ?: 'Cliente',
                    'email' => $order->user?->correo,
                    'type' => ucfirst($customer?->tipo ?: 'cliente'),
                    'orders' => (int) $order->customer_orders,
                    'sales' => (float) $order->customer_sales,
                ];
            })
            ->all();
        // Guarda en $presentation el resultado necesario para continuar esta operacion.
        $presentation = $this->datosPresentacion(
            $trend,
            $categorySales,
            $inventorySummary,
            $orderStatusSummary,
            $campaignReport,
            $customerReport,
            $request,
        );
        // Guarda en $latestOrders el resultado necesario para continuar esta operacion.
        $latestOrders = $this->pedidosRecientes($filters)->map(function (Pedido $order) use ($presentation): Pedido {
            // Guarda en $customer el resultado necesario para continuar esta operacion.
            $customer = $order->user?->customer;
            $status = $presentation['orderStatuses'][$order->estado] ?? ['label' => $order->estado, 'class' => 'processing'];
            $order->setAttribute('customer_name', $customer?->razon_social ?: $customer?->nombre_contacto ?: $order->user?->nombre ?: 'Cliente');
            $order->setAttribute('status_label', $status['label']);
            $order->setAttribute('status_class', $status['class']);

            return $order;
        });

        // Devuelve la vista 'gestion.analitica.reportes.listar' con los datos preparados por este controlador.
        return view('gestion.analitica.reportes.listar', [
            'filters' => $filters,
            'activeView' => $this->vistaActiva($request),
            'periodLabel' => $this->etiquetaPeriodo($request, $period['from'], $period['to']),
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
            'categorySales' => $categorySales,
            'topProducts' => $this->productosDestacados($details),
            'inventorySummary' => $inventorySummary,
            'latestOrders' => $latestOrders,
            'orderStatusSummary' => $orderStatusSummary,
            'productReport' => $productReport,
            'customerReport' => $customerReport,
            'inventoryReport' => $inventoryReport,
            'campaignReport' => $campaignReport,
            ...$presentation,
        ]);
    }

    /**
     * Prepara estructuras exclusivamente visuales para el informe.
     *
     * @param  array<int, array{label:string,sales:float,orders:int,sales_height:float,orders_height:float}>  $salesTrend
     * @param  array<int, array{name:string,revenue:float,percent:float,color:string}>  $categorySales
     * @param  array<int, array{label:string,count:int,color:string}>  $inventorySummary
     * @param  array{total:int,items:array<int, array{label:string,count:int,color:string}>}  $orderStatusSummary
     * @param  array{items:Collection<int, Campania>}  $campaignReport
     * @return array<string, mixed>
     */
    private function datosPresentacion(
        array $salesTrend,
        array $categorySales,
        array $inventorySummary,
        array $orderStatusSummary,
        array $campaignReport,
        array $customerReport,
        FiltroReporteRequest $request,
    ): array {
        // Guarda en $salesMax el resultado necesario para continuar esta operacion.
        $salesMax = max(1, ...array_column($salesTrend, 'sales'));
        // Guarda en $ordersMax el resultado necesario para continuar esta operacion.
        $ordersMax = max(1, ...array_column($salesTrend, 'orders'));
        // Agrupa en $chartPoints los datos que se utilizaran en la siguiente operacion.
        $chartPoints = [];
        // Agrupa en $chartCircles los datos que se utilizaran en la siguiente operacion.
        $chartCircles = [];

        // Recorre $salesTrend as $index => $point y procesa cada elemento de la coleccion.
        foreach ($salesTrend as $index => $point) {
            // Guarda en $x el resultado necesario para continuar esta operacion.
            $x = count($salesTrend) > 1 ? ($index / (count($salesTrend) - 1)) * 100 : 50;
            // Guarda en $y el resultado necesario para continuar esta operacion.
            $y = 100 - (($point['orders'] / $ordersMax) * 100);
            $chartPoints[] = round($x, 2).','.round($y, 2);
            $chartCircles[] = ['x' => round($x, 2), 'y' => round($y, 2)];
        }

        return [
            'reportTabs' => ['resumen' => ['label' => 'Reportes globales', 'icon' => 'bi-bar-chart-line']],
            'chartPoints' => $chartPoints,
            'chartPointsString' => implode(' ', $chartPoints),
            'chartCircles' => $chartCircles,
            'salesMax' => $salesMax,
            'ordersMax' => $ordersMax,
            'salesScale' => [
                $salesMax,
                $salesMax * .75,
                $salesMax * .5,
                $salesMax * .25,
                0,
            ],
            'categoryGradient' => $this->degradadoPara($categorySales, 'percent'),
            'inventoryGradient' => $this->degradadoPara($inventorySummary, 'count'),
            'inventoryTotal' => max(1, array_sum(array_column($inventorySummary, 'count'))),
            'orderStatusItems' => $this->elementosEstado($orderStatusSummary),
            'customerTypes' => $this->elementosTipoCliente($customerReport),
            'orderStatuses' => [
                'Pendiente' => ['label' => 'Pendiente', 'class' => 'pending'],
                'En proceso' => ['label' => 'En proceso', 'class' => 'processing'],
                'Enviado' => ['label' => 'Enviado', 'class' => 'processing'],
                'Entregado' => ['label' => 'Entregado', 'class' => 'completed'],
                'Cancelado' => ['label' => 'Cancelado', 'class' => 'cancelled'],
            ],
            'statusTotal' => max(1, $orderStatusSummary['total']),
            'campaignViews' => (int) $campaignReport['items']->sum('vistas'),
            'campaignClicks' => (int) $campaignReport['items']->sum('clics'),
            'campaignConversion' => $this->conversionCampanias($campaignReport['items']),
            'activeReportView' => $request->string('vista')->toString() ?: 'resumen',
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function degradadoPara(array $items, string $valueKey): string
    {
        $total = $valueKey === 'count'
            ? max(1, array_sum(array_column($items, $valueKey)))
            : 100;
        // Guarda en $cursor el resultado necesario para continuar esta operacion.
        $cursor = 0;
        // Agrupa en $segments los datos que se utilizaran en la siguiente operacion.
        $segments = [];

        // Recorre $items as $item y procesa cada elemento de la coleccion.
        foreach ($items as $item) {
            // Guarda en $next el resultado necesario para continuar esta operacion.
            $next = $cursor + (($item[$valueKey] / $total) * 100);
            $segments[] = $item['color'].' '.$cursor.'% '.$next.'%';
            // Guarda en $cursor el resultado necesario para continuar esta operacion.
            $cursor = $next;
        }

        // Comprueba la condicion $cursor < 100 antes de ejecutar este bloque.
        if ($cursor < 100) {
            $segments[] = '#e8f0fa '.$cursor.'% 100%';
        }

        return $segments !== [] ? implode(', ', $segments) : '#e8f0fa 0 100%';
    }

    /**
     * @param  Collection<int, Campania>  $campaigns
     */
    private function conversionCampanias(Collection $campaigns): float
    {
        // Guarda en $views el resultado necesario para continuar esta operacion.
        $views = (int) $campaigns->sum('vistas');

        return $views > 0 ? ((int) $campaigns->sum('clics') / $views) * 100 : 0;
    }

    /**
     * @param  array{total:int,items:array<int, array{label:string,count:int,color:string}>}  $summary
     * @return array<int, array{label:string,count:int,color:string,percent:float}>
     */
    private function elementosEstado(array $summary): array
    {
        // Guarda en $total el resultado necesario para continuar esta operacion.
        $total = max(1, $summary['total']);

        return array_map(static fn (array $item): array => $item + [
            'percent' => ($item['count'] / $total) * 100,
        ], $summary['items']);
    }

    /**
     * @param  array{active:int,mayoristas:int,minoristas:int}  $report
     * @return array<int, array{label:string,count:int,color:string,percent:float}>
     */
    private function elementosTipoCliente(array $report): array
    {
        // Agrupa en $items los datos que se utilizaran en la siguiente operacion.
        $items = [
            ['label' => 'Mayoristas', 'count' => $report['mayoristas'], 'color' => '#7b55ed'],
            ['label' => 'Minoristas', 'count' => $report['minoristas'], 'color' => '#18b78d'],
        ];
        // Guarda en $total el resultado necesario para continuar esta operacion.
        $total = max(1, $report['active']);

        return array_map(static fn (array $item): array => $item + [
            'percent' => ($item['count'] / $total) * 100,
        ], $items);
    }

    /**
     * Genera la salida exportable de la información disponible.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $exporter  Parámetro recibido por la operación.
     * @param  mixed  $audit  Parámetro recibido por la operación.
     * @param  mixed  $format  Parámetro recibido por la operación.
     * @return Response|StreamedResponse Resultado de la operación.
     */
    public function export(FiltroReporteRequest $request, ExportadorTabular $exporter, RegistradorAuditoria $audit, string $format = 'csv'): Response|StreamedResponse
    {
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless(in_array($format, ['csv', 'excel', 'pdf'], true), 422, 'Formato de exportación no permitido.');
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $filters = $this->filtrosReporte($request->validated());
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $orders = $this->consultaVentas($request->validated())
            ->select(['id', 'usuario_id', 'subtotal', 'descuento', 'total', 'estado', 'creado_en'])
            ->orderBy('creado_en')
            ->orderBy('id')
            ->get();
        $rows = $orders->map(static fn (Pedido $order): array => [
            $order->id,
            $order->usuario_id,
            $order->subtotal,
            $order->descuento,
            $order->total,
            $order->estado,
            $order->creado_en?->toDateTimeString(),
        ])->all();
        // Guarda en $response el resultado necesario para continuar esta operacion.
        $response = $exporter->download($format, 'reporte-pedidos',
            ['id', 'usuario_id', 'subtotal', 'descuento', 'total', 'estado', 'creado_en'],
            $rows,
            [
                'title' => 'REPORTE COMERCIAL DE PEDIDOS',
                'subtitle' => 'Datos reales consultados desde MariaDB.',
                'module' => 'Análisis y Auditoría',
                'report_name' => 'Pedidos',
                'filters' => $filters,
                'generated_by' => (string) $request->user()->nombre,
                'role' => 'Usuario autorizado',
                'scope' => 'Empresa y permisos RBAC vigentes.',
                'indicators' => [['label' => 'Pedidos incluidos', 'value' => count($rows)]],
                'column_types' => ['integer', 'integer', 'currency', 'currency', 'currency', 'text', 'datetime'],
            ]);
        $audit->record($request->user()->getKey(), 'exportar', 'reportes', null, null,
            ['formato' => $format, 'reporte' => 'pedidos', 'filtros' => $filters, 'cantidad' => count($rows)], $request->ip());

        return $response;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<Pedido>
     */
    private function consultaVentas(array $filters): Builder
    {
        return $this->aplicarFiltrosFecha(
            Pedido::query()->whereNotIn('estado', ['Cancelado', 'Cancelada']),
            $filters,
        );
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<DetallePedido>
     */
    private function consultaDetalles(array $filters): Builder
    {
        return DetallePedido::query()->whereHas('order', function (Builder $query) use ($filters): void {
            // Solicita al servicio o dependencia la operacion indicada en esta instruccion.
            $this->aplicarFiltrosFecha($query->whereNotIn('estado', ['Cancelado', 'Cancelada']), $filters);
        });
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{from:CarbonImmutable,to:CarbonImmutable}
     */
    private function periodo(array $filters): array
    {
        // Guarda en $from el resultado necesario para continuar esta operacion.
        $from = isset($filters['desde'])
            ? CarbonImmutable::parse((string) $filters['desde'])->startOfDay()
            : CarbonImmutable::now()->subDays(29)->startOfDay();
        // Guarda en $to el resultado necesario para continuar esta operacion.
        $to = isset($filters['hasta'])
            ? CarbonImmutable::parse((string) $filters['hasta'])->endOfDay()
            : CarbonImmutable::now()->endOfDay();

        return ['from' => $from, 'to' => $to];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, string>
     */
    private function filtrosReporte(array $filters): array
    {
        // Guarda en $period el resultado necesario para continuar esta operacion.
        $period = $this->periodo($filters);

        return [
            'desde' => $period['from']->toDateString(),
            'hasta' => $period['to']->toDateString(),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<int, array{label:string,sales:float,orders:int,sales_height:float,orders_height:float}>
     */
    private function tendenciaVentas(array $filters, CarbonImmutable $from, CarbonImmutable $to): array
    {
        // Guarda en $rows el resultado necesario para continuar esta operacion.
        $rows = $this->consultaVentas($filters)
            ->selectRaw('DATE(creado_en) as day, COALESCE(SUM(total), 0) as sales, COUNT(*) as orders')
            ->groupByRaw('DATE(creado_en)')
            ->orderBy('day')
            ->get()
            ->keyBy('day');
        // Guarda en $days el resultado necesario para continuar esta operacion.
        $days = $from->diffInDays($to) + 1;
        // Agrupa en $points los datos que se utilizaran en la siguiente operacion.
        $points = [];

        // Comprueba la condicion $days <= 31 antes de ejecutar este bloque.
        if ($days <= 31) {
            // Repite este bloque usando la configuracion del ciclo $index = 0; $index < $days; $index++.
            for ($index = 0; $index < $days; $index++) {
                // Guarda en $date el resultado necesario para continuar esta operacion.
                $date = $from->addDays($index);
                // Guarda en $row el resultado necesario para continuar esta operacion.
                $row = $rows->get($date->toDateString());
                $points[] = [
                    'label' => $date->format('d M'),
                    'sales' => (float) ($row?->sales ?? 0),
                    'orders' => (int) ($row?->orders ?? 0),
                ];
            }
        } else {
            // Recorre $rows as $row y procesa cada elemento de la coleccion.
            foreach ($rows as $row) {
                // Guarda en $date el resultado necesario para continuar esta operacion.
                $date = CarbonImmutable::parse((string) $row->day);
                $points[] = [
                    'label' => $date->format('d M'),
                    'sales' => (float) $row->sales,
                    'orders' => (int) $row->orders,
                ];
            }
        }

        // Guarda en $maxSales el resultado necesario para continuar esta operacion.
        $maxSales = max(1, ...array_column($points, 'sales'));
        // Guarda en $maxOrders el resultado necesario para continuar esta operacion.
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
    private function ventasPorCategoria(Collection $details): array
    {
        $total = (float) $details->sum(static fn (DetallePedido $detail): float => (float) $detail->cantidad * (float) $detail->precio_unitario);
        // Agrupa en $colors los datos que se utilizaran en la siguiente operacion.
        $colors = ['#2583f7', '#18b78d', '#139bc3', '#f35b3e', '#fb9a17', '#7b7cf6'];
        // Guarda en $rows el resultado necesario para continuar esta operacion.
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
    private function productosDestacados(Collection $details): array
    {
        return $details
            ->groupBy('producto_id')
            ->map(static function (Collection $productDetails): array {
                // Guarda en $product el resultado necesario para continuar esta operacion.
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
    private function resumenInventario(): array
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
    private function pedidosRecientes(array $filters): Collection
    {
        return $this->aplicarFiltrosFecha(Pedido::query(), $filters)
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
    private function resumenEstadosPedidos(array $filters): array
    {
        // Guarda en $counts el resultado necesario para continuar esta operacion.
        $counts = $this->aplicarFiltrosFecha(
            Pedido::query()->selectRaw('estado, COUNT(*) as total'),
            $filters,
        )
            ->groupBy('estado')
            ->pluck('total', 'estado');
        // Agrupa en $statuses los datos que se utilizaran en la siguiente operacion.
        $statuses = [
            ['key' => 'Pendiente', 'label' => 'Pendientes', 'color' => '#f7a719'],
            ['key' => 'En proceso', 'label' => 'En preparación', 'color' => '#2583f7'],
            ['key' => 'Enviado', 'label' => 'Enviados', 'color' => '#7b55ed'],
            ['key' => 'Entregado', 'label' => 'Entregados', 'color' => '#18b78d'],
            ['key' => 'Cancelado', 'label' => 'Cancelados', 'color' => '#f23c52'],
        ];
        // Guarda en $items el resultado necesario para continuar esta operacion.
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
    private function reporteClientes(array $filters): array
    {
        // Prepara en $typeCounts la consulta que obtiene o modifica los registros necesarios.
        $typeCounts = Cliente::query()
            ->where('activo', true)
            ->selectRaw('tipo, COUNT(*) as total')
            ->groupBy('tipo')
            ->pluck('total', 'tipo');
        // Guarda en $topCustomers el resultado necesario para continuar esta operacion.
        $topCustomers = $this->aplicarFiltrosFecha(
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
    private function reporteInventario(): array
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
    private function reporteCampanias(CarbonImmutable $from, CarbonImmutable $to): array
    {
        // Prepara en $periodQuery la consulta que obtiene o modifica los registros necesarios.
        $periodQuery = Campania::query()->whereBetween('creado_en', [$from, $to]);
        // Guarda en $locationRows el resultado necesario para continuar esta operacion.
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

    /**
     * Ejecuta la operación activeView definida por la clase ReporteController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @return string Resultado de la operación.
     */
    private function vistaActiva(FiltroReporteRequest $request): string
    {
        // Guarda en $view el resultado necesario para continuar esta operacion.
        $view = $request->string('vista')->toString();

        return in_array($view, ['resumen', 'ventas', 'pedidos', 'productos', 'inventario', 'clientes', 'campanas'], true)
            ? $view
            : 'resumen';
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function etiquetaPeriodo(FiltroReporteRequest $request, CarbonImmutable $from, CarbonImmutable $to): string
    {
        // Comprueba la condicion ! $request->filled('desde') && ! $request->filled('hasta') antes de ejecutar este bloque.
        if (! $request->filled('desde') && ! $request->filled('hasta')) {
            return 'Últimos 30 días';
        }

        return $from->format('d/m/Y').' - '.$to->format('d/m/Y');
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function aplicarFiltrosFecha(Builder $query, array $filters): Builder
    {
        // Recorre ['desde' => '>=', 'hasta' => '<='] as $field => $operator y procesa cada elemento de la coleccion.
        foreach (['desde' => '>=', 'hasta' => '<='] as $field => $operator) {
            // Comprueba la condicion ! isset($filters[$field]) antes de ejecutar este bloque.
            if (! isset($filters[$field])) {
                continue;
            }

            $query->whereDate('creado_en', $operator, $filters[$field]);
        }

        return $query;
    }
}
