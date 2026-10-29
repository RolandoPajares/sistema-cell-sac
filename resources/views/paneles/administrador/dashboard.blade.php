@extends('layouts.administracion')

@section('title', 'Panel de administración')
@section('body_class', 'modulo-dashboard modulo-admin-dashboard')

@section('content')
    <section class="dashboard-page" data-admin-dashboard>
        <header class="dashboard-hero">
            <div>
                <span class="dashboard-eyebrow">Administración</span>
                <h1>Panel de administración <span aria-hidden="true">👋</span></h1>
                <p>Control total de tu operación comercial, inventario y marketing en un solo lugar.</p>
            </div>
            <time class="dashboard-date-card" datetime="{{ $dashboardNow->toDateString() }}">
                <span class="dashboard-date-icon"><i class="bi bi-calendar3" aria-hidden="true"></i></span>
                <span>
                    <small>Hoy es</small>
                    <strong>{{ $dashboardNow->format('d/m/Y') }}</strong>
                    <em>{{ ucfirst($dashboardNow->locale('es')->translatedFormat('l')) }}</em>
                </span>
            </time>
        </header>

        <div class="dashboard-kpi-grid">
            <article class="dashboard-kpi dashboard-kpi--sales">
                <span class="dashboard-kpi-icon"><i class="bi bi-cart3" aria-hidden="true"></i></span>
                <div>
                    <small>Ventas del mes</small>
                    <strong>S/ {{ number_format($salesThisMonth, 2) }}</strong>
                    @if ($salesTrendPercent !== null)
                        <span class="dashboard-trend {{ $salesTrendPercent >= 0 ? 'is-positive' : 'is-negative' }}"><i class="bi bi-arrow-{{ $salesTrendPercent >= 0 ? 'up' : 'down' }}" aria-hidden="true"></i> {{ number_format(abs($salesTrendPercent), 1) }}% <em>vs. mes anterior</em></span>
                    @else
                        <span class="dashboard-trend is-neutral">Sin periodo anterior</span>
                    @endif
                </div>
            </article>

            <article class="dashboard-kpi dashboard-kpi--orders">
                <span class="dashboard-kpi-icon"><i class="bi bi-clipboard2-check" aria-hidden="true"></i></span>
                <div>
                    <small>Pedidos</small>
                    <strong>{{ number_format($ordersCount) }}</strong>
                    @if ($ordersTrendPercent !== null)
                        <span class="dashboard-trend {{ $ordersTrendPercent >= 0 ? 'is-positive' : 'is-negative' }}"><i class="bi bi-arrow-{{ $ordersTrendPercent >= 0 ? 'up' : 'down' }}" aria-hidden="true"></i> {{ number_format(abs($ordersTrendPercent), 1) }}% <em>vs. mes anterior</em></span>
                    @else
                        <span class="dashboard-trend is-neutral">{{ number_format($ordersThisMonth) }} este mes</span>
                    @endif
                </div>
            </article>

            <article class="dashboard-kpi dashboard-kpi--products">
                <span class="dashboard-kpi-icon"><i class="bi bi-box-seam" aria-hidden="true"></i></span>
                <div>
                    <small>Productos activos</small>
                    <strong>{{ number_format($activeProducts) }}</strong>
                    <span class="dashboard-trend is-positive"><i class="bi bi-arrow-up" aria-hidden="true"></i> {{ number_format($productsAddedThisMonth) }} <em>nuevos este mes</em></span>
                </div>
            </article>

            <article class="dashboard-kpi dashboard-kpi--stock">
                <span class="dashboard-kpi-icon"><i class="bi bi-boxes" aria-hidden="true"></i></span>
                <div>
                    <small>Stock bajo</small>
                    <strong>{{ number_format($lowStock) }}</strong>
                    <span class="dashboard-trend {{ $lowStock > 0 ? 'is-negative' : 'is-positive' }}"><i class="bi bi-{{ $lowStock > 0 ? 'exclamation-triangle' : 'check-circle' }}" aria-hidden="true"></i> {{ $lowStock > 0 ? 'Requiere atención' : 'Inventario saludable' }}</span>
                </div>
            </article>

            <article class="dashboard-kpi dashboard-kpi--customers">
                <span class="dashboard-kpi-icon"><i class="bi bi-people" aria-hidden="true"></i></span>
                <div>
                    <small>Clientes nuevos</small>
                    <strong>{{ number_format($newCustomers) }}</strong>
                    @if ($newCustomersTrendPercent !== null)
                        <span class="dashboard-trend {{ $newCustomersTrendPercent >= 0 ? 'is-positive' : 'is-negative' }}"><i class="bi bi-arrow-{{ $newCustomersTrendPercent >= 0 ? 'up' : 'down' }}" aria-hidden="true"></i> {{ number_format(abs($newCustomersTrendPercent), 1) }}% <em>vs. mes anterior</em></span>
                    @else
                        <span class="dashboard-trend is-neutral">Sin periodo anterior</span>
                    @endif
                </div>
            </article>
        </div>

        <div class="dashboard-grid dashboard-grid--charts">
            <section class="dashboard-card dashboard-sales-card">
                <header class="dashboard-card-header">
                    <div class="dashboard-card-title"><i class="bi bi-bar-chart-line-fill" aria-hidden="true"></i><div><h2>Ventas de los últimos 12 meses</h2><p>Ingresos registrados en pedidos no cancelados.</p></div></div>
                    <label class="dashboard-select-wrap">
                        <span class="visually-hidden">Periodo de ventas</span>
                        <select data-dashboard-range>
                            <option value="12">Últimos 12 meses</option>
                            <option value="6">Últimos 6 meses</option>
                        </select>
                    </label>
                </header>
                <div class="dashboard-sales-chart" data-dashboard-sales-chart data-chart-max="{{ $salesChartMax }}">
                    <div class="dashboard-chart-y-axis" aria-hidden="true">
                        <span>S/ {{ number_format($salesChartMax, 0) }}</span>
                        <span>S/ {{ number_format($salesChartMax * .75, 0) }}</span>
                        <span>S/ {{ number_format($salesChartMax * .5, 0) }}</span>
                        <span>S/ {{ number_format($salesChartMax * .25, 0) }}</span>
                        <span>S/ 0</span>
                    </div>
                    <div class="dashboard-chart-plot">
                        <div class="dashboard-chart-grid-lines" aria-hidden="true"><span></span><span></span><span></span><span></span><span></span></div>
                        <svg class="dashboard-sales-line" viewBox="0 0 1000 160" preserveAspectRatio="none" aria-hidden="true">
                            <polyline data-dashboard-sales-line points="0,150 1000,150"></polyline>
                        </svg>
                        <div class="dashboard-chart-bars">
                            @foreach ($monthlySales as $month)
                                @php($barHeight = $salesChartMax > 0 ? max(4, round(($month['value'] / $salesChartMax) * 100, 2)) : 4)
                                <div class="dashboard-chart-column" data-sales-month data-value="{{ $month['value'] }}" data-month-key="{{ $month['key'] }}" title="{{ $month['label'] }}: S/ {{ number_format($month['value'], 2) }}">
                                    <span class="dashboard-chart-bar" style="--bar-height: {{ $barHeight }}%"></span>
                                    @if ($month['key'] === $dashboardNow->format('Y-m'))
                                        <span class="dashboard-chart-tooltip"><b>{{ $month['label'] }} {{ $dashboardNow->format('Y') }}</b><strong>S/ {{ number_format($month['value'], 2) }}</strong></span>
                                    @endif
                                    <b>{{ $month['label'] }}</b>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <section class="dashboard-card dashboard-orders-card">
                <header class="dashboard-card-header">
                    <div class="dashboard-card-title"><i class="bi bi-clock-history" aria-hidden="true"></i><div><h2>Estado de pedidos</h2><p>Distribución de todos los pedidos registrados.</p></div></div>
                    <span class="dashboard-period-label">Todos</span>
                </header>
                <div class="dashboard-order-status">
                    <div class="dashboard-donut" style="--donut-background: {{ $orderStatusChart }}">
                        <div><strong>{{ number_format($orderStatusTotal) }}</strong><span>Pedidos<br>totales</span></div>
                    </div>
                    <ul class="dashboard-status-list">
                        @forelse ($orderStatuses as $status)
                            <li><span class="dashboard-status-dot" style="--status-color: {{ $status['color'] }}"></span><b>{{ $status['label'] }}</b><strong>{{ number_format($status['total']) }}</strong><small>({{ $orderStatusTotal > 0 ? number_format(($status['total'] / $orderStatusTotal) * 100, 1) : '0.0' }}%)</small></li>
                        @empty
                            <li class="dashboard-list-empty"><i class="bi bi-inbox" aria-hidden="true"></i><span>Aún no hay pedidos registrados.</span></li>
                        @endforelse
                    </ul>
                </div>
            </section>

            <section class="dashboard-card dashboard-quick-card">
                <header class="dashboard-card-header"><div class="dashboard-card-title"><i class="bi bi-grid-3x3-gap-fill" aria-hidden="true"></i><div><h2>Accesos rápidos</h2><p>Atajos de administración.</p></div></div></header>
                <div class="dashboard-quick-grid">
                    @foreach ([
                        ['label' => 'Productos', 'icon' => 'bi-box-seam', 'route' => 'management.products'],
                        ['label' => 'Categorías', 'icon' => 'bi-diagram-3', 'route' => 'management.categories'],
                        ['label' => 'Inventario', 'icon' => 'bi-boxes', 'route' => 'management.inventory'],
                        ['label' => 'Pedidos', 'icon' => 'bi-receipt', 'route' => 'management.orders'],
                        ['label' => 'Proveedores', 'icon' => 'bi-truck', 'route' => 'management.suppliers'],
                        ['label' => 'Compras', 'icon' => 'bi-cart-plus', 'route' => 'management.purchases'],
                        ['label' => 'Campañas', 'icon' => 'bi-megaphone', 'route' => 'management.campaigns'],
                        ['label' => 'Publicidad', 'icon' => 'bi-bullseye', 'route' => 'smart.ads'],
                        ['label' => 'Reportes', 'icon' => 'bi-bar-chart', 'route' => 'management.reports'],
                    ] as $quickLink)
                        <a href="{{ route($quickLink['route']) }}"><span><i class="bi {{ $quickLink['icon'] }}" aria-hidden="true"></i></span><b>{{ $quickLink['label'] }}</b></a>
                    @endforeach
                </div>
            </section>
        </div>

        <div class="dashboard-grid dashboard-grid--data">
            <section class="dashboard-card dashboard-table-card">
                <header class="dashboard-card-header"><div class="dashboard-card-title"><i class="bi bi-clipboard2-check" aria-hidden="true"></i><div><h2>Pedidos recientes</h2><p>Últimas operaciones comerciales.</p></div></div><a href="{{ route('management.orders') }}">Ver todos</a></header>
                <div class="dashboard-table-wrap">
                    <table class="dashboard-table">
                        <thead><tr><th># Pedido</th><th>Cliente</th><th>Fecha</th><th>Estado</th><th>Total</th><th><span class="visually-hidden">Acciones</span></th></tr></thead>
                        <tbody>
                            @forelse ($recentOrders as $order)
                                <tr>
                                    <td><strong>#{{ $order->id }}</strong></td>
                                    <td>{{ $order->user?->nombre ?? 'Cliente no disponible' }}</td>
                                    <td>{{ $order->creado_en?->format('d/m/Y H:i') }}</td>
                                    <td><span class="dashboard-order-badge dashboard-order-badge--{{ \Illuminate\Support\Str::slug($order->estado) }}">{{ $order->estado }}</span></td>
                                    <td><strong>S/ {{ number_format((float) $order->total, 2) }}</strong></td>
                                    <td class="dashboard-table-actions"><a href="{{ route('management.orders') }}" aria-label="Ver pedido #{{ $order->id }}"><i class="bi bi-three-dots" aria-hidden="true"></i></a></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="dashboard-table-empty"><i class="bi bi-inbox" aria-hidden="true"></i>No hay pedidos recientes.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="dashboard-card dashboard-list-card">
                <header class="dashboard-card-header"><div class="dashboard-card-title"><i class="bi bi-exclamation-triangle-fill is-danger" aria-hidden="true"></i><div><h2>Alertas de inventario</h2><p>Productos con existencias mínimas.</p></div></div><a href="{{ route('management.inventory') }}">Ver todos</a></header>
                <div class="dashboard-product-list">
                    @forelse ($lowStockProducts as $product)
                        <a href="{{ route('management.inventory') }}" class="dashboard-product-row">
                            @if ($product->url_imagen)
                                <img src="{{ \Illuminate\Support\Str::startsWith($product->url_imagen, ['http://', 'https://']) ? $product->url_imagen : asset($product->url_imagen) }}" alt="{{ $product->nombre }}">
                            @else
                                <span class="dashboard-product-placeholder"><i class="bi bi-phone" aria-hidden="true"></i></span>
                            @endif
                            <span><b>{{ $product->nombre }}</b><small>{{ $product->marca ?: 'Producto' }}</small></span>
                            <strong>{{ number_format($product->existencias) }} {{ $product->existencias === 1 ? 'unidad' : 'unidades' }}</strong><i class="bi bi-chevron-right" aria-hidden="true"></i>
                        </a>
                    @empty
                        <div class="dashboard-empty-state"><i class="bi bi-shield-check" aria-hidden="true"></i><strong>Sin alertas de inventario</strong><span>Los productos activos superan el nivel mínimo.</span></div>
                    @endforelse
                </div>
            </section>

            <section class="dashboard-card dashboard-list-card">
                <header class="dashboard-card-header"><div class="dashboard-card-title"><i class="bi bi-trophy-fill is-warning" aria-hidden="true"></i><div><h2>Productos más vendidos</h2><p>Ranking basado en pedidos registrados.</p></div></div><a href="{{ route('management.reports') }}">Ver todos</a></header>
                <ol class="dashboard-ranking-list">
                    @forelse ($topProducts as $index => $sale)
                        <li><span class="dashboard-ranking-number">{{ $index + 1 }}</span>
                            @if ($sale->product?->url_imagen)
                                <img src="{{ \Illuminate\Support\Str::startsWith($sale->product->url_imagen, ['http://', 'https://']) ? $sale->product->url_imagen : asset($sale->product->url_imagen) }}" alt="{{ $sale->product->nombre }}">
                            @else
                                <span class="dashboard-product-placeholder"><i class="bi bi-phone" aria-hidden="true"></i></span>
                            @endif
                            <span><b>{{ $sale->product?->nombre ?? 'Producto eliminado' }}</b><small>{{ number_format((int) $sale->units_sold) }} unidades vendidas</small></span><strong>S/ {{ number_format((float) $sale->revenue, 2) }}</strong>
                        </li>
                    @empty
                        <li class="dashboard-empty-state"><i class="bi bi-bar-chart" aria-hidden="true"></i><strong>Aún no hay ventas</strong><span>El ranking aparecerá cuando existan pedidos.</span></li>
                    @endforelse
                </ol>
            </section>
        </div>

        <div class="dashboard-grid dashboard-grid--bottom">
            <section class="dashboard-card dashboard-campaign-card">
                <header class="dashboard-card-header"><div class="dashboard-card-title"><i class="bi bi-megaphone-fill" aria-hidden="true"></i><div><h2>Rendimiento de campañas</h2><p>Vistas y clics registrados en publicidad.</p></div></div><a href="{{ route('management.campaigns') }}">Ver todas</a></header>
                <div class="dashboard-campaign-list">
                    @forelse ($campaigns as $campaign)
                        @php($campaignProgress = min(100, round(((int) $campaign->vistas / $campaignMaxViews) * 100)))
                        <div class="dashboard-campaign-row"><span><b>{{ $campaign->nombre }}</b><small>{{ number_format((int) $campaign->vistas) }} vistas · {{ number_format((int) $campaign->clics) }} clics</small></span><div class="dashboard-progress"><span data-dashboard-progress style="--progress: {{ $campaignProgress }}%"></span></div><strong>{{ $campaign->vistas > 0 ? number_format(($campaign->clics / $campaign->vistas) * 100, 1) : '0.0' }}%</strong></div>
                    @empty
                        <div class="dashboard-empty-state"><i class="bi bi-megaphone" aria-hidden="true"></i><strong>No hay campañas activas</strong><span>Las campañas registradas aparecerán aquí.</span></div>
                    @endforelse
                </div>
            </section>

            <section class="dashboard-card dashboard-activity-card">
                <header class="dashboard-card-header"><div class="dashboard-card-title"><i class="bi bi-clock-history" aria-hidden="true"></i><div><h2>Actividad reciente</h2><p>Últimos movimientos auditados.</p></div></div><a href="{{ route('management.audit') }}">Ver todas</a></header>
                <div class="dashboard-activity-list">
                    @forelse ($recentActivity as $activity)
                        <article><span class="dashboard-activity-dot"><i class="bi bi-{{ $activity->entidad === 'pedidos' ? 'cart-check' : ($activity->entidad === 'productos' ? 'box-seam' : 'person') }}" aria-hidden="true"></i></span><div><b>{{ $activity->accion }}</b><small>{{ $activity->user?->nombre ?? 'Sistema' }} · {{ $activity->entidad }}{{ $activity->entidad_id ? ' #'.$activity->entidad_id : '' }}</small></div><time datetime="{{ $activity->creado_en?->toIso8601String() }}">{{ $activity->creado_en?->diffForHumans() }}</time></article>
                    @empty
                        <div class="dashboard-empty-state"><i class="bi bi-clock-history" aria-hidden="true"></i><strong>Sin actividad reciente</strong><span>Las acciones auditadas aparecerán aquí.</span></div>
                    @endforelse
                </div>
            </section>

            <section class="dashboard-card dashboard-task-card">
                <header class="dashboard-card-header"><div class="dashboard-card-title"><i class="bi bi-check2-circle" aria-hidden="true"></i><div><h2>Tareas / recordatorios</h2><p>Acciones derivadas de tus datos.</p></div></div></header>
                <ul class="dashboard-task-list">
                    @forelse ($reminders as $reminder)
                        <li class="dashboard-task dashboard-task--{{ $reminder['tone'] }}"><span class="dashboard-task-check" aria-hidden="true"><i class="bi bi-square"></i></span><span class="dashboard-task-icon"><i class="bi {{ $reminder['icon'] }}" aria-hidden="true"></i></span><div><b>{{ $reminder['title'] }}</b><small>{{ $reminder['detail'] }}</small></div></li>
                    @empty
                        <li class="dashboard-task-empty"><i class="bi bi-check-circle-fill" aria-hidden="true"></i><span><b>No hay tareas pendientes</b><small>Tu operación está al día.</small></span></li>
                    @endforelse
                </ul>
            </section>
        </div>
    </section>
@endsection
