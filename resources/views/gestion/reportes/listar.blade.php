@extends('layouts.administracion')

@section('title', 'Reportes comerciales')
@section('body_class', 'modulo-reportes')

@section('content')
    @php
        $reportTabs = [
            'resumen' => ['label' => 'Resumen', 'icon' => 'bi-grid-1x2-fill'],
            'ventas' => ['label' => 'Ventas', 'icon' => 'bi-cart3'],
            'pedidos' => ['label' => 'Pedidos', 'icon' => 'bi-receipt'],
            'productos' => ['label' => 'Productos', 'icon' => 'bi-box-seam'],
            'inventario' => ['label' => 'Inventario', 'icon' => 'bi-boxes'],
            'clientes' => ['label' => 'Clientes', 'icon' => 'bi-people'],
            'campanas' => ['label' => 'Campañas', 'icon' => 'bi-megaphone'],
        ];
        $salesMax = max(1, ...array_column($salesTrend, 'sales'));
        $ordersMax = max(1, ...array_column($salesTrend, 'orders'));
        $chartPoints = [];

        foreach ($salesTrend as $index => $point) {
            $x = count($salesTrend) > 1 ? ($index / (count($salesTrend) - 1)) * 100 : 50;
            $y = 100 - (($point['orders'] / $ordersMax) * 100);
            $chartPoints[] = round($x, 2).','.round($y, 2);
        }

        $categoryCursor = 0;
        $categorySegments = [];
        foreach ($categorySales as $category) {
            $next = $categoryCursor + $category['percent'];
            $categorySegments[] = $category['color'].' '.$categoryCursor.'% '.$next.'%';
            $categoryCursor = $next;
        }
        if ($categoryCursor < 100) {
            $categorySegments[] = '#e8f0fa '.$categoryCursor.'% 100%';
        }
        $categoryGradient = $categorySegments !== [] ? implode(', ', $categorySegments) : '#e8f0fa 0 100%';

        $inventoryTotal = max(1, array_sum(array_column($inventorySummary, 'count')));
        $inventoryCursor = 0;
        $inventorySegments = [];
        foreach ($inventorySummary as $stock) {
            $next = $inventoryCursor + (($stock['count'] / $inventoryTotal) * 100);
            $inventorySegments[] = $stock['color'].' '.$inventoryCursor.'% '.$next.'%';
            $inventoryCursor = $next;
        }
        if ($inventoryCursor < 100) {
            $inventorySegments[] = '#e8f0fa '.$inventoryCursor.'% 100%';
        }
        $inventoryGradient = implode(', ', $inventorySegments);
        $orderStatuses = [
            'Pendiente' => ['label' => 'Pendiente', 'class' => 'pending'],
            'En proceso' => ['label' => 'En proceso', 'class' => 'processing'],
            'Enviado' => ['label' => 'Enviado', 'class' => 'processing'],
            'Entregado' => ['label' => 'Completado', 'class' => 'completed'],
            'Cancelado' => ['label' => 'Cancelado', 'class' => 'cancelled'],
        ];
        $statusTotal = max(1, $orderStatusSummary['total']);
        $campaignViews = $campaignReport['items']->sum('vistas');
        $campaignClicks = $campaignReport['items']->sum('clics');
        $campaignConversion = $campaignViews > 0 ? ($campaignClicks / $campaignViews) * 100 : 0;
        $activeReportView = $activeView ?? 'resumen';
    @endphp

    <section class="reportes-page" data-reportes-page>
        <header class="reportes-hero">
            <div>
                <span class="reportes-eyebrow">Analítica y reportes</span>
                <h1>Reportes comerciales <i class="bi bi-bar-chart-fill" aria-hidden="true"></i></h1>
                <p>Consulta y analiza la información de ventas, inventario, pedidos y campañas del sistema.</p>
            </div>
            <a class="reportes-export-button" href="{{ route('management.reports.export', $filters) }}">
                <i class="bi bi-download" aria-hidden="true"></i>
                Exportar CSV
            </a>
        </header>

        <form class="reportes-toolbar" method="GET" action="{{ route('management.reports') }}" data-report-filter-form>
            <nav class="reportes-tabs" role="tablist" aria-label="Secciones del reporte">
                @foreach ($reportTabs as $key => $tab)
                    <button class="{{ $activeReportView === $key ? 'is-active' : '' }}" type="button" role="tab" aria-selected="{{ $activeReportView === $key ? 'true' : 'false' }}" data-report-tab="{{ $key }}" aria-controls="report-panel-{{ $key }}">
                        <i class="bi {{ $tab['icon'] }}" aria-hidden="true"></i>{{ $tab['label'] }}
                    </button>
                @endforeach
            </nav>
            <div class="reportes-period-filter">
                <i class="bi bi-calendar3" aria-hidden="true"></i>
                <select aria-label="Periodo del reporte" data-report-period>
                    <option value="30" @selected(! request()->filled('desde') && ! request()->filled('hasta'))>Últimos 30 días</option>
                    <option value="7">Últimos 7 días</option>
                    <option value="90">Últimos 90 días</option>
                    <option value="custom" @selected(request()->filled('desde') || request()->filled('hasta'))>{{ $periodLabel }}</option>
                </select>
                <i class="bi bi-chevron-down" aria-hidden="true"></i>
            </div>
            <input type="hidden" name="vista" value="{{ $activeReportView }}" data-report-view>
            <input type="hidden" name="desde" value="{{ $filters['desde'] }}" data-report-from>
            <input type="hidden" name="hasta" value="{{ $filters['hasta'] }}" data-report-to>
        </form>

        <div class="reportes-tab-panel {{ $activeReportView === 'resumen' ? 'is-active' : '' }}" id="report-panel-resumen" data-report-panel="resumen" role="tabpanel" @if ($activeReportView !== 'resumen') hidden @endif>
            <div class="reportes-kpi-grid">
                <article class="reportes-kpi reportes-kpi--green"><span class="reportes-kpi-icon"><i class="bi bi-cash-stack" aria-hidden="true"></i></span><div><small>Ventas totales</small><strong>S/ {{ number_format($sales, 2) }}</strong><span><i class="bi bi-arrow-up" aria-hidden="true"></i> Periodo consultado</span></div></article>
                <article class="reportes-kpi reportes-kpi--blue"><span class="reportes-kpi-icon"><i class="bi bi-cart3" aria-hidden="true"></i></span><div><small>Pedidos</small><strong>{{ number_format($orders) }}</strong><span><i class="bi bi-check-circle" aria-hidden="true"></i> Operaciones no canceladas</span></div></article>
                <article class="reportes-kpi reportes-kpi--violet"><span class="reportes-kpi-icon"><i class="bi bi-box-seam" aria-hidden="true"></i></span><div><small>Productos vendidos</small><strong>{{ number_format($productsSold) }}</strong><span><i class="bi bi-arrow-up" aria-hidden="true"></i> Unidades registradas</span></div></article>
                <article class="reportes-kpi reportes-kpi--orange"><span class="reportes-kpi-icon"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i></span><div><small>Stock bajo</small><strong>{{ number_format($lowStock) }}</strong><span><i class="bi bi-arrow-up" aria-hidden="true"></i> Productos por reponer</span></div></article>
            </div>

            <div class="reportes-grid reportes-grid--main">
                <section class="reportes-card reportes-chart-card">
                    <header class="reportes-card-header"><div><h2>Ventas y pedidos</h2><p>Comparativo de actividad en el periodo seleccionado.</p></div><span class="reportes-card-period">Diario <i class="bi bi-chevron-down" aria-hidden="true"></i></span></header>
                    <div class="reportes-chart-legend"><span><i class="reportes-legend-dot reportes-legend-dot--blue"></i>Ventas (S/)</span><span><i class="reportes-legend-dot reportes-legend-dot--green"></i>Pedidos</span></div>
                    @if ($orders > 0 || $sales > 0)
                        <div class="reportes-chart" aria-label="Gráfico de ventas y pedidos">
                            <div class="reportes-chart-scale"><span>S/ {{ number_format($salesMax, 0) }}</span><span>S/ {{ number_format($salesMax * .75, 0) }}</span><span>S/ {{ number_format($salesMax * .5, 0) }}</span><span>S/ {{ number_format($salesMax * .25, 0) }}</span><span>S/ 0</span></div>
                            <div class="reportes-chart-plot"><div class="reportes-chart-lines"><span></span><span></span><span></span><span></span><span></span></div><div class="reportes-bars">@foreach ($salesTrend as $point)<div class="reportes-bar-column" title="{{ $point['label'] }}: S/ {{ number_format($point['sales'], 2) }} · {{ $point['orders'] }} pedidos"><span class="reportes-bar" style="height: {{ $point['sales_height'] }}%"></span><small>{{ $point['label'] }}</small></div>@endforeach</div><svg class="reportes-orders-line" viewBox="0 0 100 100" preserveAspectRatio="none" role="img" aria-label="Pedidos por día"><polyline points="{{ implode(' ', $chartPoints) }}"></polyline>@foreach ($salesTrend as $index => $point)@php $pointX = count($salesTrend) > 1 ? ($index / (count($salesTrend) - 1)) * 100 : 50; $pointY = 100 - (($point['orders'] / $ordersMax) * 100); @endphp<circle cx="{{ round($pointX, 2) }}" cy="{{ round($pointY, 2) }}" r="1.2"></circle>@endforeach</svg></div>
                        </div>
                    @else
                        <div class="reportes-empty"><i class="bi bi-bar-chart" aria-hidden="true"></i><strong>Sin ventas en este periodo</strong><span>Los datos aparecerán cuando se registren pedidos.</span></div>
                    @endif
                </section>
                <section class="reportes-card reportes-donut-card"><header class="reportes-card-header"><div><h2>Ventas por categoría</h2><p>Distribución de ventas del periodo.</p></div><span class="reportes-card-period">Este mes <i class="bi bi-chevron-down" aria-hidden="true"></i></span></header><div class="reportes-donut-layout"><div class="reportes-donut" style="--donut-gradient: conic-gradient({{ $categoryGradient }})"><div><strong>S/ {{ number_format($sales, 0) }}</strong><span>Total</span></div></div><ul class="reportes-legend-list">@forelse ($categorySales as $category)<li><span><i style="background: {{ $category['color'] }}"></i>{{ $category['name'] }}</span><b>{{ number_format($category['percent'], 1) }}%</b></li>@empty<li class="reportes-legend-empty">Sin categorías con ventas</li>@endforelse</ul></div></section>
            </div>

            <div class="reportes-grid reportes-grid--secondary">
                <section class="reportes-card reportes-top-products"><header class="reportes-card-header"><div><h2>Productos más vendidos</h2><p>Ranking por unidades y valor acumulado.</p></div><button type="button" class="reportes-inline-link" data-report-tab-link="productos">Ver detalle <i class="bi bi-arrow-right" aria-hidden="true"></i></button></header><div class="reportes-product-list">@forelse ($topProducts as $product)<div class="reportes-product-row"><span class="reportes-rank reportes-rank--{{ $loop->iteration }}">{{ $loop->iteration }}</span>@if ($product['image'])<img src="{{ \Illuminate\Support\Str::startsWith($product['image'], ['http://', 'https://']) ? $product['image'] : asset($product['image']) }}" alt="{{ $product['name'] }}">@else<span class="reportes-product-placeholder"><i class="bi bi-box-seam" aria-hidden="true"></i></span>@endif<span class="reportes-product-name"><strong>{{ $product['name'] }}</strong><small>{{ number_format($product['units']) }} unidades</small></span><b>S/ {{ number_format($product['revenue'], 2) }}</b></div>@empty<div class="reportes-empty reportes-empty--compact"><i class="bi bi-box-seam" aria-hidden="true"></i><strong>Aún no hay productos vendidos</strong><span>El ranking aparecerá cuando existan pedidos.</span></div>@endforelse</div></section>
                <section class="reportes-card reportes-inventory-card"><header class="reportes-card-header"><div><h2>Estado de inventario</h2><p>{{ number_format($activeProducts) }} productos activos en el catálogo.</p></div><button type="button" class="reportes-inline-link" data-report-tab-link="inventario">Ver detalle <i class="bi bi-arrow-right" aria-hidden="true"></i></button></header><div class="reportes-donut-layout reportes-donut-layout--inventory"><div class="reportes-donut reportes-donut--inventory" style="--donut-gradient: conic-gradient({{ $inventoryGradient }})"><div><strong>{{ number_format($activeProducts) }}</strong><span>Productos</span></div></div><ul class="reportes-legend-list">@foreach ($inventorySummary as $stock)<li><span><i style="background: {{ $stock['color'] }}"></i>{{ $stock['label'] }}</span><b>{{ number_format(($stock['count'] / $inventoryTotal) * 100, 1) }}%</b></li>@endforeach</ul></div><div class="reportes-inventory-alert"><span><i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i></span><div><strong>{{ number_format($lowStock) }} productos con stock bajo</strong><small>Requieren reposición.</small></div><button type="button" class="reportes-alert-link" data-report-tab-link="inventario">Ver productos</button></div></section>
            </div>

            <section class="reportes-card reportes-orders-card"><header class="reportes-card-header"><div><h2>Últimos pedidos</h2><p>Operaciones registradas en el periodo seleccionado.</p></div><button type="button" class="reportes-inline-link" data-report-tab-link="pedidos">Ver detalle <i class="bi bi-arrow-right" aria-hidden="true"></i></button></header>@include('gestion.reportes._orders-table', ['latestOrders' => $latestOrders, 'orderStatuses' => $orderStatuses])</section>
        </div>

        <div class="reportes-footer-note"><i class="bi bi-info-circle" aria-hidden="true"></i><span>Las métricas se calculan con la información registrada en la base de datos. Periodo actual: <strong>{{ $periodLabel }}</strong>.</span><span class="reportes-footer-campaigns">Campañas: {{ number_format($campaignViews) }} vistas · {{ number_format($campaignClicks) }} clics</span></div>
    </div>

        <div class="reportes-tab-panel {{ $activeReportView === 'ventas' ? 'is-active' : '' }}" id="report-panel-ventas" data-report-panel="ventas" role="tabpanel" @if ($activeReportView !== 'ventas') hidden @endif>
            <header class="reportes-view-header"><div><span class="reportes-eyebrow">Ventas</span><h2>Análisis de ventas</h2><p>Detalle del ingreso y comportamiento diario dentro del periodo seleccionado.</p></div></header>
            <div class="reportes-mini-kpi-grid"><article><span class="reportes-mini-icon reportes-mini-icon--green"><i class="bi bi-cash-stack"></i></span><div><small>Ventas totales</small><strong>S/ {{ number_format($sales, 2) }}</strong></div></article><article><span class="reportes-mini-icon reportes-mini-icon--blue"><i class="bi bi-calculator"></i></span><div><small>Ticket promedio</small><strong>S/ {{ number_format($orders > 0 ? $sales / $orders : 0, 2) }}</strong></div></article><article><span class="reportes-mini-icon reportes-mini-icon--violet"><i class="bi bi-receipt"></i></span><div><small>Pedidos con venta</small><strong>{{ number_format($orders) }}</strong></div></article><article><span class="reportes-mini-icon reportes-mini-icon--orange"><i class="bi bi-box-seam"></i></span><div><small>Unidades vendidas</small><strong>{{ number_format($productsSold) }}</strong></div></article></div>
            <div class="reportes-grid reportes-grid--main"><section class="reportes-card reportes-chart-card"><header class="reportes-card-header"><div><h2>Evolución diaria</h2><p>Ventas y pedidos no cancelados.</p></div><span class="reportes-card-period">{{ $periodLabel }}</span></header><div class="reportes-chart-legend"><span><i class="reportes-legend-dot reportes-legend-dot--blue"></i>Ventas (S/)</span><span><i class="reportes-legend-dot reportes-legend-dot--green"></i>Pedidos</span></div>@if ($orders > 0 || $sales > 0)<div class="reportes-chart" aria-label="Evolución de ventas"><div class="reportes-chart-scale"><span>S/ {{ number_format($salesMax, 0) }}</span><span>S/ {{ number_format($salesMax * .75, 0) }}</span><span>S/ {{ number_format($salesMax * .5, 0) }}</span><span>S/ {{ number_format($salesMax * .25, 0) }}</span><span>S/ 0</span></div><div class="reportes-chart-plot"><div class="reportes-chart-lines"><span></span><span></span><span></span><span></span><span></span></div><div class="reportes-bars">@foreach ($salesTrend as $point)<div class="reportes-bar-column" title="{{ $point['label'] }}: S/ {{ number_format($point['sales'], 2) }} · {{ $point['orders'] }} pedidos"><span class="reportes-bar" style="height: {{ $point['sales_height'] }}%"></span><small>{{ $point['label'] }}</small></div>@endforeach</div><svg class="reportes-orders-line" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true"><polyline points="{{ implode(' ', $chartPoints) }}"></polyline></svg></div></div>@else<div class="reportes-empty"><i class="bi bi-bar-chart"></i><strong>Sin ventas en este periodo</strong><span>Los datos aparecerán cuando se registren pedidos.</span></div>@endif</section><section class="reportes-card reportes-detail-card"><header class="reportes-card-header"><div><h2>Detalle diario</h2><p>Valores registrados por fecha.</p></div></header><div class="reportes-data-list">@forelse ($salesTrend as $point)<div><span>{{ $point['label'] }}</span><b>S/ {{ number_format($point['sales'], 2) }}</b><small>{{ $point['orders'] }} pedidos</small></div>@empty<div class="reportes-empty reportes-empty--compact">Sin datos.</div>@endforelse</div></section></div>
        </div>

        <div class="reportes-tab-panel {{ $activeReportView === 'pedidos' ? 'is-active' : '' }}" id="report-panel-pedidos" data-report-panel="pedidos" role="tabpanel" @if ($activeReportView !== 'pedidos') hidden @endif>
            <header class="reportes-view-header"><div><span class="reportes-eyebrow">Pedidos</span><h2>Estado de pedidos</h2><p>Consulta la distribución de pedidos y el detalle de cada estado.</p></div></header>
            <div class="reportes-mini-kpi-grid"><article><span class="reportes-mini-icon reportes-mini-icon--blue"><i class="bi bi-receipt"></i></span><div><small>Pedidos totales</small><strong>{{ number_format($orderStatusSummary['total']) }}</strong></div></article><article><span class="reportes-mini-icon reportes-mini-icon--orange"><i class="bi bi-clock"></i></span><div><small>Pendientes</small><strong>{{ number_format($orderStatusSummary['items'][0]['count']) }}</strong></div></article><article><span class="reportes-mini-icon reportes-mini-icon--green"><i class="bi bi-check-circle"></i></span><div><small>Entregados</small><strong>{{ number_format($orderStatusSummary['items'][3]['count']) }}</strong></div></article><article><span class="reportes-mini-icon reportes-mini-icon--red"><i class="bi bi-x-circle"></i></span><div><small>Cancelados</small><strong>{{ number_format($orderStatusSummary['items'][4]['count']) }}</strong></div></article></div>
            <div class="reportes-grid reportes-grid--main"><section class="reportes-card reportes-status-card"><header class="reportes-card-header"><div><h2>Distribución por estado</h2><p>{{ number_format($orderStatusSummary['total']) }} pedidos en el periodo.</p></div></header><div class="reportes-status-list">@foreach ($orderStatusSummary['items'] as $status)<div><span><i style="background: {{ $status['color'] }}"></i>{{ $status['label'] }}</span><div class="reportes-progress"><i style="width: {{ ($status['count'] / $statusTotal) * 100 }}%; background: {{ $status['color'] }}"></i></div><b>{{ number_format($status['count']) }}</b><small>{{ number_format(($status['count'] / $statusTotal) * 100, 1) }}%</small></div>@endforeach</div></section><section class="reportes-card reportes-orders-card"><header class="reportes-card-header"><div><h2>Pedidos recientes</h2><p>Últimas operaciones registradas.</p></div></header>@include('gestion.reportes._orders-table', ['latestOrders' => $latestOrders, 'orderStatuses' => $orderStatuses])</section></div>
        </div>

        <div class="reportes-tab-panel {{ $activeReportView === 'productos' ? 'is-active' : '' }}" id="report-panel-productos" data-report-panel="productos" role="tabpanel" @if ($activeReportView !== 'productos') hidden @endif>
            <header class="reportes-view-header"><div><span class="reportes-eyebrow">Productos</span><h2>Rendimiento del catálogo</h2><p>Productos vendidos, catálogo activo y existencias registradas.</p></div></header>
            <div class="reportes-mini-kpi-grid"><article><span class="reportes-mini-icon reportes-mini-icon--blue"><i class="bi bi-box-seam"></i></span><div><small>Productos activos</small><strong>{{ number_format($productReport['active']) }}</strong></div></article><article><span class="reportes-mini-icon reportes-mini-icon--violet"><i class="bi bi-boxes"></i></span><div><small>Categorías</small><strong>{{ number_format($productReport['categories']) }}</strong></div></article><article><span class="reportes-mini-icon reportes-mini-icon--green"><i class="bi bi-cart-check"></i></span><div><small>Unidades vendidas</small><strong>{{ number_format($productsSold) }}</strong></div></article><article><span class="reportes-mini-icon reportes-mini-icon--orange"><i class="bi bi-exclamation-triangle"></i></span><div><small>Stock bajo</small><strong>{{ number_format($lowStock) }}</strong></div></article></div>
            <div class="reportes-grid reportes-grid--main"><section class="reportes-card reportes-table-card"><header class="reportes-card-header"><div><h2>Catálogo de productos</h2><p>{{ number_format($productReport['active']) }} activos · {{ number_format($productReport['inactive']) }} inactivos.</p></div></header><div class="reportes-simple-table-wrap"><table class="reportes-simple-table"><thead><tr><th>Producto</th><th>Categoría</th><th>Precio</th><th>Stock</th><th>Estado</th></tr></thead><tbody>@forelse ($productReport['catalog'] as $product)<tr><td><strong>{{ $product->nombre }}</strong><small>{{ $product->marca }}</small></td><td>{{ $product->category?->nombre ?: $product->categoria }}</td><td>S/ {{ number_format((float) $product->precio, 2) }}</td><td>{{ number_format($product->existencias) }}</td><td><span class="reportes-status reportes-status--{{ $product->activo ? 'completed' : 'cancelled' }}">{{ $product->activo ? 'Activo' : 'Inactivo' }}</span></td></tr>@empty<tr><td colspan="5" class="reportes-table-empty">No hay productos registrados.</td></tr>@endforelse</tbody></table></div></section><section class="reportes-card reportes-top-products"><header class="reportes-card-header"><div><h2>Más vendidos</h2><p>Ordenados por ingresos del periodo.</p></div></header><div class="reportes-product-list">@forelse ($topProducts as $product)<div class="reportes-product-row"><span class="reportes-rank reportes-rank--{{ $loop->iteration }}">{{ $loop->iteration }}</span><span class="reportes-product-placeholder"><i class="bi bi-box-seam"></i></span><span class="reportes-product-name"><strong>{{ $product['name'] }}</strong><small>{{ number_format($product['units']) }} unidades</small></span><b>S/ {{ number_format($product['revenue'], 2) }}</b></div>@empty<div class="reportes-empty reportes-empty--compact">Sin ventas de productos.</div>@endforelse</div></section></div>
        </div>

        <div class="reportes-tab-panel {{ $activeReportView === 'inventario' ? 'is-active' : '' }}" id="report-panel-inventario" data-report-panel="inventario" role="tabpanel" @if ($activeReportView !== 'inventario') hidden @endif>
            <header class="reportes-view-header"><div><span class="reportes-eyebrow">Inventario</span><h2>Estado de inventario</h2><p>Existencias, valor de inventario y movimientos registrados.</p></div></header>
            <div class="reportes-mini-kpi-grid"><article><span class="reportes-mini-icon reportes-mini-icon--green"><i class="bi bi-cash-stack"></i></span><div><small>Valor de inventario</small><strong>S/ {{ number_format($inventoryReport['value'], 2) }}</strong></div></article><article><span class="reportes-mini-icon reportes-mini-icon--blue"><i class="bi bi-box-seam"></i></span><div><small>Productos activos</small><strong>{{ number_format($activeProducts) }}</strong></div></article><article><span class="reportes-mini-icon reportes-mini-icon--orange"><i class="bi bi-exclamation-triangle"></i></span><div><small>Stock bajo</small><strong>{{ number_format($lowStock) }}</strong></div></article><article><span class="reportes-mini-icon reportes-mini-icon--violet"><i class="bi bi-arrow-left-right"></i></span><div><small>Movimientos</small><strong>{{ number_format($inventoryReport['movements']) }}</strong></div></article></div>
            <div class="reportes-grid reportes-grid--main"><section class="reportes-card reportes-table-card"><header class="reportes-card-header"><div><h2>Productos con stock bajo</h2><p>Productos activos con cinco unidades o menos.</p></div></header><div class="reportes-simple-table-wrap"><table class="reportes-simple-table"><thead><tr><th>Producto</th><th>Categoría</th><th>Stock actual</th><th>Acción</th></tr></thead><tbody>@forelse ($inventoryReport['lowStock'] as $product)<tr><td><strong>{{ $product->nombre }}</strong><small>{{ $product->marca }}</small></td><td>{{ $product->category?->nombre ?: $product->categoria }}</td><td><span class="reportes-stock-number {{ $product->existencias === 0 ? 'is-critical' : 'is-warning' }}">{{ number_format($product->existencias) }} unidades</span></td><td><span class="reportes-status reportes-status--pending">Reponer</span></td></tr>@empty<tr><td colspan="4" class="reportes-table-empty">No hay productos con stock bajo.</td></tr>@endforelse</tbody></table></div></section><section class="reportes-card reportes-detail-card"><header class="reportes-card-header"><div><h2>Movimientos recientes</h2><p>Últimas entradas, salidas y ajustes.</p></div></header><div class="reportes-data-list">@forelse ($inventoryReport['recentMovements'] as $movement)<div><span><i class="bi bi-arrow-left-right"></i>{{ $movement->product?->nombre ?: 'Producto' }}</span><b>{{ $movement->tipo_movimiento }}</b><small>{{ $movement->cantidad }} unidades · {{ $movement->creado_en?->format('d/m/Y H:i') }}</small></div>@empty<div class="reportes-empty reportes-empty--compact">No hay movimientos registrados.</div>@endforelse</div></section></div>
        </div>

        <div class="reportes-tab-panel {{ $activeReportView === 'clientes' ? 'is-active' : '' }}" id="report-panel-clientes" data-report-panel="clientes" role="tabpanel" @if ($activeReportView !== 'clientes') hidden @endif>
            <header class="reportes-view-header"><div><span class="reportes-eyebrow">Clientes</span><h2>Comportamiento de clientes</h2><p>Clientes registrados y compradores con mayor movimiento en el periodo.</p></div></header>
            <div class="reportes-mini-kpi-grid"><article><span class="reportes-mini-icon reportes-mini-icon--blue"><i class="bi bi-people"></i></span><div><small>Total de clientes</small><strong>{{ number_format($customerReport['total']) }}</strong></div></article><article><span class="reportes-mini-icon reportes-mini-icon--green"><i class="bi bi-person-check"></i></span><div><small>Clientes activos</small><strong>{{ number_format($customerReport['active']) }}</strong></div></article><article><span class="reportes-mini-icon reportes-mini-icon--violet"><i class="bi bi-building"></i></span><div><small>Mayoristas</small><strong>{{ number_format($customerReport['mayoristas']) }}</strong></div></article><article><span class="reportes-mini-icon reportes-mini-icon--orange"><i class="bi bi-person"></i></span><div><small>Minoristas</small><strong>{{ number_format($customerReport['minoristas']) }}</strong></div></article></div>
            <div class="reportes-grid reportes-grid--main"><section class="reportes-card reportes-table-card"><header class="reportes-card-header"><div><h2>Clientes con mayor compra</h2><p>Ranking por total acumulado en el periodo.</p></div></header><div class="reportes-simple-table-wrap"><table class="reportes-simple-table"><thead><tr><th>Cliente</th><th>Tipo</th><th>Pedidos</th><th>Total comprado</th></tr></thead><tbody>@forelse ($customerReport['top'] as $customerOrder)@php $customer = $customerOrder->user?->customer; $customerName = $customer?->razon_social ?: $customer?->nombre_contacto ?: $customerOrder->user?->nombre ?: 'Cliente'; @endphp<tr><td><strong>{{ $customerName }}</strong><small>{{ $customerOrder->user?->correo }}</small></td><td>{{ ucfirst($customer?->tipo ?: 'cliente') }}</td><td>{{ number_format($customerOrder->customer_orders) }}</td><td><strong>S/ {{ number_format((float) $customerOrder->customer_sales, 2) }}</strong></td></tr>@empty<tr><td colspan="4" class="reportes-table-empty">No hay compras de clientes en este periodo.</td></tr>@endforelse</tbody></table></div></section><section class="reportes-card reportes-status-card"><header class="reportes-card-header"><div><h2>Distribución de clientes</h2><p>Clientes activos por tipo.</p></div></header><div class="reportes-status-list">@foreach ([['label' => 'Mayoristas', 'count' => $customerReport['mayoristas'], 'color' => '#7b55ed'], ['label' => 'Minoristas', 'count' => $customerReport['minoristas'], 'color' => '#18b78d']] as $customerType)<div><span><i style="background: {{ $customerType['color'] }}"></i>{{ $customerType['label'] }}</span><div class="reportes-progress"><i style="width: {{ $customerReport['active'] > 0 ? ($customerType['count'] / $customerReport['active']) * 100 : 0 }}%; background: {{ $customerType['color'] }}"></i></div><b>{{ number_format($customerType['count']) }}</b><small>{{ $customerReport['active'] > 0 ? number_format(($customerType['count'] / $customerReport['active']) * 100, 1) : '0.0' }}%</small></div>@endforeach</div></section></div>
        </div>

        <div class="reportes-tab-panel {{ $activeReportView === 'campanas' ? 'is-active' : '' }}" id="report-panel-campanas" data-report-panel="campanas" role="tabpanel" @if ($activeReportView !== 'campanas') hidden @endif>
            <header class="reportes-view-header"><div><span class="reportes-eyebrow">Campañas</span><h2>Rendimiento de campañas</h2><p>Vistas, clics y campañas registradas en el periodo seleccionado.</p></div></header>
            <div class="reportes-mini-kpi-grid"><article><span class="reportes-mini-icon reportes-mini-icon--blue"><i class="bi bi-eye"></i></span><div><small>Vistas totales</small><strong>{{ number_format($campaignViews) }}</strong></div></article><article><span class="reportes-mini-icon reportes-mini-icon--violet"><i class="bi bi-cursor-fill"></i></span><div><small>Clics totales</small><strong>{{ number_format($campaignClicks) }}</strong></div></article><article><span class="reportes-mini-icon reportes-mini-icon--green"><i class="bi bi-graph-up-arrow"></i></span><div><small>Tasa de conversión</small><strong>{{ number_format($campaignConversion, 1) }}%</strong></div></article><article><span class="reportes-mini-icon reportes-mini-icon--orange"><i class="bi bi-megaphone"></i></span><div><small>Campañas activas</small><strong>{{ number_format($campaignReport['active']) }}</strong></div></article></div>
            <div class="reportes-grid reportes-grid--main"><section class="reportes-card reportes-table-card"><header class="reportes-card-header"><div><h2>Campañas registradas</h2><p>{{ number_format($campaignReport['total']) }} campañas en el periodo.</p></div></header><div class="reportes-simple-table-wrap"><table class="reportes-simple-table"><thead><tr><th>Campaña</th><th>Ubicación</th><th>Vistas</th><th>Clics</th><th>Estado</th></tr></thead><tbody>@forelse ($campaignReport['items'] as $campaign)<tr><td><strong>{{ $campaign->nombre }}</strong><small>{{ $campaign->titulo }}</small></td><td>{{ ucfirst($campaign->ubicacion) }}</td><td>{{ number_format($campaign->vistas) }}</td><td>{{ number_format($campaign->clics) }}</td><td><span class="reportes-status reportes-status--{{ $campaign->activo ? 'completed' : 'cancelled' }}">{{ $campaign->activo ? 'Activa' : 'Inactiva' }}</span></td></tr>@empty<tr><td colspan="5" class="reportes-table-empty">No hay campañas en este periodo.</td></tr>@endforelse</tbody></table></div></section><section class="reportes-card reportes-status-card"><header class="reportes-card-header"><div><h2>Distribución por ubicación</h2><p>Campañas y vistas por espacio.</p></div></header><div class="reportes-status-list">@forelse ($campaignReport['locations'] as $location)<div><span><i style="background: #2583f7"></i>{{ ucfirst($location->ubicacion) }}</span><div class="reportes-progress"><i style="width: {{ $campaignReport['total'] > 0 ? ($location->total / $campaignReport['total']) * 100 : 0 }}%; background: #2583f7"></i></div><b>{{ $location->total }}</b><small>{{ number_format($location->views) }} vistas</small></div>@empty<div class="reportes-empty reportes-empty--compact">No hay ubicaciones registradas.</div>@endforelse</div></section></div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        (() => {
            const form = document.querySelector('[data-report-filter-form]');
            const tabs = [...document.querySelectorAll('[data-report-tab]')];
            const panels = [...document.querySelectorAll('[data-report-panel]')];
            const viewInput = form?.querySelector('[data-report-view]');
            const period = form?.querySelector('[data-report-period]');
            const from = form?.querySelector('[data-report-from]');
            const to = form?.querySelector('[data-report-to]');

            const activate = (view, updateUrl = true) => {
                const selectedTab = tabs.find((tab) => tab.dataset.reportTab === view);
                if (!selectedTab) {
                    return;
                }

                tabs.forEach((tab) => {
                    const selected = tab === selectedTab;
                    tab.classList.toggle('is-active', selected);
                    tab.setAttribute('aria-selected', selected ? 'true' : 'false');
                });
                panels.forEach((panel) => {
                    const selected = panel.dataset.reportPanel === view;
                    panel.hidden = !selected;
                    panel.classList.toggle('is-active', selected);
                });
                if (viewInput) {
                    viewInput.value = view;
                }
                if (updateUrl) {
                    const url = new URL(window.location.href);
                    url.searchParams.set('vista', view);
                    window.history.replaceState({}, '', url);
                }
            };

            tabs.forEach((tab) => tab.addEventListener('click', () => activate(tab.dataset.reportTab)));
            document.querySelectorAll('[data-report-tab-link]').forEach((link) => {
                link.addEventListener('click', () => activate(link.dataset.reportTabLink));
            });
            form?.addEventListener('submit', () => {
                if (viewInput) {
                    viewInput.value = document.querySelector('[data-report-tab].is-active')?.dataset.reportTab || 'resumen';
                }
            });
            period?.addEventListener('change', () => {
                if (period.value === 'custom') {
                    return;
                }
                if (period.value === 'all') {
                    from?.removeAttribute('name');
                    to?.removeAttribute('name');
                    form?.submit();
                    return;
                }
                const days = Number(period.value);
                const today = new Date();
                const start = new Date(today);
                start.setDate(today.getDate() - (days - 1));
                const toIso = (date) => [date.getFullYear(), String(date.getMonth() + 1).padStart(2, '0'), String(date.getDate()).padStart(2, '0')].join('-');
                if (from && to) {
                    from.value = toIso(start);
                    to.value = toIso(today);
                }
                form?.submit();
            });
        })();
    </script>
@endpush
