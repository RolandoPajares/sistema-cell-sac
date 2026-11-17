@extends('layouts.panel.principal')


{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('title', $dashboardTitle ?? 'Panel')
{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('body_class', $dashboardBodyClass ?? 'modulo-dashboard')

{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('content')
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
@if (($dashboardKind ?? 'management') === 'account')
<section class="cuenta-fondo">
    <div class="container cuenta-migas"><a href="{{ route('dashboard') }}">Panel</a><i class="bi bi-chevron-right"></i><span>Mi cuenta</span></div>
    <div class="container cuenta-diseno">
        <main class="cuenta-contenido">
            <header class="cuenta-banner">
                <div>
                    <span class="cuenta-banner-etiqueta"><i class="bi bi-stars"></i> Mi cuenta</span>
                    <h1>Tu espacio en CELL SAC</h1>
                    <p>Gestiona tus pedidos, consulta el catálogo y encuentra tu próximo equipo.</p>
                    <div class="cuenta-banner-botones">
                        <a class="cuenta-btn-blanco" href="{{ route('catalog.index') }}"><i class="bi bi-grid"></i> Ver catálogo</a>
                        <a class="cuenta-btn-borde" href="{{ route('recommendations') }}"><i class="bi bi-stars"></i> Probar SmartMatch</a>
                    </div>
                </div>
                <img src="{{ asset('assets/cell-legacy/img/publico/inicio/secciones/hero-devices.png') }}" alt="Celulares y accesorios">
            </header>

            <div class="cuenta-resumen">
                <article><i class="bi bi-receipt"></i>
                    <div><strong>{{ $orderCount }}</strong><span>Pedidos registrados</span></div>
                </article>
                <article><i class="bi bi-clock-history"></i>
                    <div><strong>{{ $pendingCount }}</strong><span>Pedidos en proceso</span></div>
                </article>
                <article><i class="bi bi-cart3"></i>
                    <div><strong>{{ $cartUnits }}</strong><span>Unidades en carrito</span></div>
                </article>
                <article><i class="bi bi-person-check"></i>
                    <div><strong>Activa</strong><span>Estado de cuenta</span></div>
                </article>
            </div>

            <section class="cuenta-panel">
                <div class="cuenta-panel-titulo">
                    <h2>Pedidos recientes</h2><a href="{{ route('orders.index') }}">Ver todos <i class="bi bi-chevron-right"></i></a>
                </div>
                <div class="lista-pedidos-cuenta">
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
                    @forelse($orders->take(5) as $order)
{{-- logica auxiliar de la vista que prepara valores para la presentacion. --}}
                    @php($firstProduct = $order->details->first()?->product)
                    <article>
                        <div class="pedido-codigo"><b>#{{ $order->id }}</b><small>{{ $order->creado_en?->format('d/m/Y') }}</small></div>
                            <div class="pedido-producto"><span><i class="bi bi-phone"></i></span>
                                <div><b>{{ $firstProduct?->nombre ?: 'Pedido CELL SAC' }}</b><small>{{ $order->details->count() }} producto(s)</small></div>
                            </div>

                            <span class="pedido-estado"><i class="bi bi-check-circle"></i>{{ $order->estado }}</span>
                        <div class="pedido-total"><b>S/ {{ number_format((float) $order->total, 2) }}</b><small>Total</small></div>
                        <a href="{{ route('orders.show', $order) }}">Ver detalle</a>
                    </article>
                    @empty
                    <div class="cuenta-vacio"><i class="bi bi-receipt"></i>
                        <p>Aún no tienes pedidos registrados.</p><a class="btn btn-primary" href="{{ route('catalog.index') }}">Explorar catálogo</a>
                    </div>
                    @endforelse
                </div>
            </section>

            <div class="cuenta-dos-columnas">
                <section class="cuenta-panel">
                    <div class="cuenta-panel-titulo">
                        <h2>Accesos rápidos</h2>
                    </div>
                    <div class="cuenta-accesos">
                        <a href="{{ route('orders.index') }}"><i class="bi bi-box-seam"></i> Mis pedidos</a>
                        <a href="{{ route('cart.index') }}"><i class="bi bi-cart3"></i> Mi carrito</a>
                        <a href="{{ route('comparison') }}"><i class="bi bi-columns-gap"></i> Comparar equipos</a>
                        <a href="{{ route('pages.contact') }}"><i class="bi bi-headset"></i> Contactar asesor</a>
                    </div>
                </section>
                <section class="cuenta-panel cuenta-recomendado">
                    <i class="bi bi-stars"></i>
                    <div>
                        <h2>Recomendado para ti</h2>
                        <p>Encuentra equipos según tus necesidades y presupuesto.</p><a class="btn btn-primary" href="{{ route('recommendations') }}">Probar SmartMatch</a>
                    </div>
                </section>
            </div>

        </main>
    </div>
</section>

{{-- alternativa del condicional cuando la condicion anterior no se cumple. --}}
@else
<section class="dashboard-page" data-admin-dashboard>
    <header class="dashboard-hero">
        <div>
            <span class="dashboard-eyebrow">Administración</span>
            <h1>Panel de administración <span aria-hidden="true"></span></h1>
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
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
                @if ($salesTrendPercent !== null)
                <span class="dashboard-trend {{ $salesTrendPercent >= 0 ? 'is-positive' : 'is-negative' }}"><i class="bi bi-arrow-{{ $salesTrendPercent >= 0 ? 'up' : 'down' }}" aria-hidden="true"></i> {{ number_format(abs($salesTrendPercent), 1) }}% <em>vs. mes anterior</em></span>
{{-- alternativa del condicional cuando la condicion anterior no se cumple. --}}
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
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
                @if ($ordersTrendPercent !== null)
                <span class="dashboard-trend {{ $ordersTrendPercent >= 0 ? 'is-positive' : 'is-negative' }}"><i class="bi bi-arrow-{{ $ordersTrendPercent >= 0 ? 'up' : 'down' }}" aria-hidden="true"></i> {{ number_format(abs($ordersTrendPercent), 1) }}% <em>vs. mes anterior</em></span>
{{-- alternativa del condicional cuando la condicion anterior no se cumple. --}}
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
                <small>Productos sin existencias</small>
                <strong>{{ number_format($lowStock) }}</strong>
                <span class="dashboard-trend {{ $activeProducts === 0 ? 'is-neutral' : ($lowStock > 0 ? 'is-negative' : 'is-positive') }}"><i class="bi bi-{{ $activeProducts === 0 ? 'inbox' : ($lowStock > 0 ? 'exclamation-triangle' : 'check-circle') }}" aria-hidden="true"></i> {{ $activeProducts === 0 ? 'Sin productos registrados' : ($lowStock > 0 ? 'Requiere reposición' : 'Existencias disponibles') }}</span>
            </div>
        </article>

        <article class="dashboard-kpi dashboard-kpi--customers">
            <span class="dashboard-kpi-icon"><i class="bi bi-people" aria-hidden="true"></i></span>
            <div>
                <small>Clientes nuevos</small>
                <strong>{{ number_format($newCustomers) }}</strong>
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
                @if ($newCustomersTrendPercent !== null)
                <span class="dashboard-trend {{ $newCustomersTrendPercent >= 0 ? 'is-positive' : 'is-negative' }}"><i class="bi bi-arrow-{{ $newCustomersTrendPercent >= 0 ? 'up' : 'down' }}" aria-hidden="true"></i> {{ number_format(abs($newCustomersTrendPercent), 1) }}% <em>vs. mes anterior</em></span>
{{-- alternativa del condicional cuando la condicion anterior no se cumple. --}}
                @else
                <span class="dashboard-trend is-neutral">Sin periodo anterior</span>
                @endif
            </div>
        </article>
    </div>

    <div class="dashboard-grid dashboard-grid--charts">
        <section class="dashboard-card dashboard-sales-card">
            <header class="dashboard-card-header">
                <div class="dashboard-card-title"><i class="bi bi-bar-chart-line-fill" aria-hidden="true"></i>
                    <div>
                        <h2>Ventas de los últimos 12 meses</h2>
                        <p>Ventas confirmadas en MariaDB.</p>
                    </div>
                </div>
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
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
                        @foreach ($monthlySales as $month)
{{-- logica auxiliar de la vista que prepara valores para la presentacion. --}}
                        @php($barHeight = $salesChartMax > 0 ? max(4, round(($month['value'] / $salesChartMax) * 100, 2)) : 4)
                        <div class="dashboard-chart-column" data-sales-month data-value="{{ $month['value'] }}" data-month-key="{{ $month['key'] }}" title="{{ $month['label'] }}: S/ {{ number_format($month['value'], 2) }}">
                            <span class="dashboard-chart-bar" style="--bar-height: {{ $barHeight }}%"></span>
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
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
                <div class="dashboard-card-title"><i class="bi bi-clock-history" aria-hidden="true"></i>
                    <div>
                        <h2>Estado de pedidos</h2>
                        <p>Distribución de todos los pedidos registrados.</p>
                    </div>
                </div>
                <span class="dashboard-period-label">Todos</span>
            </header>
            <div class="dashboard-order-status">
                <div class="dashboard-donut" style="--donut-background: {{ $orderStatusChart }}">
                    <div><strong>{{ number_format($orderStatusTotal) }}</strong><span>Pedidos<br>totales</span></div>
                </div>
                <ul class="dashboard-status-list">
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
                    @forelse ($orderStatuses as $status)
                    <li><span class="dashboard-status-dot" style="--status-color: {{ $status['color'] }}"></span><b>{{ $status['label'] }}</b><strong>{{ number_format($status['total']) }}</strong><small>({{ $orderStatusTotal > 0 ? number_format(($status['total'] / $orderStatusTotal) * 100, 1) : '0.0' }}%)</small></li>
                    @empty
                    <li class="dashboard-list-empty"><i class="bi bi-inbox" aria-hidden="true"></i><span>Aún no hay pedidos registrados.</span></li>
                    @endforelse
                </ul>
            </div>
        </section>

        <section class="dashboard-card dashboard-quick-card">
            <header class="dashboard-card-header">
                <div class="dashboard-card-title"><i class="bi bi-grid-3x3-gap-fill" aria-hidden="true"></i>
                    <div>
                        <h2>Accesos rápidos</h2>
                        <p>Atajos de administración.</p>
                    </div>
                </div>
            </header>
            <div class="dashboard-quick-grid">
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
                @foreach ($quickLinks ?? [] as $quickLink)
                <a href="{{ route($quickLink['route']) }}"><span><i class="bi {{ $quickLink['icon'] }}" aria-hidden="true"></i></span><b>{{ $quickLink['label'] }}</b></a>
                @endforeach
            </div>
        </section>
    </div>

    <div class="dashboard-grid dashboard-grid--data">
        <section class="dashboard-card dashboard-table-card">
            <header class="dashboard-card-header">
                <div class="dashboard-card-title"><i class="bi bi-clipboard2-check" aria-hidden="true"></i>
                    <div>
                        <h2>Pedidos recientes</h2>
                        <p>Últimas operaciones comerciales.</p>
                    </div>
                </div><a href="{{ route('management.orders') }}">Ver todos</a>
            </header>
            <div class="dashboard-table-wrap">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th># Pedido</th>
                            <th>Cliente</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Total</th>
                            <th><span class="visually-hidden">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody>
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
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
                        <tr>
                            <td colspan="6" class="dashboard-table-empty"><i class="bi bi-inbox" aria-hidden="true"></i>No hay pedidos recientes.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="dashboard-card dashboard-list-card">
            <header class="dashboard-card-header">
                <div class="dashboard-card-title"><i class="bi bi-exclamation-triangle-fill is-danger" aria-hidden="true"></i>
                    <div>
                        <h2>Alertas de inventario</h2>
                        <p>Productos sin saldo físico en almacén.</p>
                    </div>
                </div><a href="{{ route('management.inventory') }}">Ver todos</a>
            </header>
            <div class="dashboard-product-list">
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
                @forelse ($lowStockProducts as $product)
                <a href="{{ route('management.inventory') }}" class="dashboard-product-row">
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
                    @if ($product->url_imagen)
                    <img src="{{ \Illuminate\Support\Str::startsWith($product->url_imagen, ['http://', 'https://']) ? $product->url_imagen : asset($product->url_imagen) }}" alt="{{ $product->nombre }}">
{{-- alternativa del condicional cuando la condicion anterior no se cumple. --}}
                    @else
                    <span class="dashboard-product-placeholder"><i class="bi bi-phone" aria-hidden="true"></i></span>
                    @endif
                    <span><b>{{ $product->nombre }}</b><small>{{ $product->marca ?: 'Producto' }}</small></span>
                    <strong>{{ number_format((float) $product->existencias_alcance) }} {{ (float) $product->existencias_alcance === 1.0 ? 'unidad' : 'unidades' }}</strong><i class="bi bi-chevron-right" aria-hidden="true"></i>
                </a>
                @empty
                <div class="dashboard-empty-state"><i class="bi bi-{{ $activeProducts === 0 ? 'inbox' : 'shield-check' }}" aria-hidden="true"></i><strong>{{ $activeProducts === 0 ? 'No hay productos registrados' : 'Sin alertas de inventario' }}</strong><span>{{ $activeProducts === 0 ? 'Registra productos para consultar sus existencias por almacén.' : 'Todos los productos activos tienen existencias disponibles.' }}</span></div>
                @endforelse
            </div>
        </section>

        <section class="dashboard-card dashboard-list-card">
            <header class="dashboard-card-header">
                <div class="dashboard-card-title"><i class="bi bi-trophy-fill is-warning" aria-hidden="true"></i>
                    <div>
                        <h2>Productos más vendidos</h2>
                        <p>Ranking basado en ventas confirmadas.</p>
                    </div>
                </div><a href="{{ route('management.reports') }}">Ver todos</a>
            </header>
            <ol class="dashboard-ranking-list">
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
                @forelse ($topProducts as $index => $venta)
                <li><span class="dashboard-ranking-number">{{ $index + 1 }}</span>
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
                    @if ($venta->producto?->url_imagen)
                    <img src="{{ \Illuminate\Support\Str::startsWith($venta->producto->url_imagen, ['http://', 'https://']) ? $venta->producto->url_imagen : asset($venta->producto->url_imagen) }}" alt="{{ $venta->producto->nombre }}">
{{-- alternativa del condicional cuando la condicion anterior no se cumple. --}}
                    @else
                    <span class="dashboard-product-placeholder"><i class="bi bi-phone" aria-hidden="true"></i></span>
                    @endif
                    <span><b>{{ $venta->producto?->nombre ?? $venta->descripcion_historica }}</b><small>{{ number_format((int) $venta->unidades_vendidas) }} unidades vendidas</small></span><strong>S/ {{ number_format((float) $venta->importe_vendido, 2) }}</strong>
                </li>
                @empty
                <li class="dashboard-empty-state"><i class="bi bi-bar-chart" aria-hidden="true"></i><strong>Aún no hay ventas</strong><span>El ranking aparecerá cuando existan ventas confirmadas.</span></li>
                @endforelse
            </ol>
        </section>
    </div>

    <div class="dashboard-grid dashboard-grid--bottom">
        <section class="dashboard-card dashboard-campaign-card">
            <header class="dashboard-card-header">
                <div class="dashboard-card-title"><i class="bi bi-megaphone-fill" aria-hidden="true"></i>
                    <div>
                        <h2>Rendimiento de campañas</h2>
                        <p>Vistas y clics registrados en publicidad.</p>
                    </div>
                </div><a href="{{ route('management.campaigns') }}">Ver todas</a>
            </header>
            <div class="dashboard-campaign-list">
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
                @forelse ($campaigns as $campaign)
{{-- logica auxiliar de la vista que prepara valores para la presentacion. --}}
                @php($campaignProgress = min(100, round(((int) $campaign->vistas / $campaignMaxViews) * 100)))
                <div class="dashboard-campaign-row"><span><b>{{ $campaign->nombre }}</b><small>{{ number_format((int) $campaign->vistas) }} vistas · {{ number_format((int) $campaign->clics) }} clics</small></span>
                    <div class="dashboard-progress"><span data-dashboard-progress style="--progress: {{ $campaignProgress }}%"></span></div><strong>{{ $campaign->vistas > 0 ? number_format(($campaign->clics / $campaign->vistas) * 100, 1) : '0.0' }}%</strong>
                </div>
                @empty
                <div class="dashboard-empty-state"><i class="bi bi-megaphone" aria-hidden="true"></i><strong>No hay campañas activas</strong><span>Las campañas registradas aparecerán aquí.</span></div>
                @endforelse
            </div>
        </section>

        <section class="dashboard-card dashboard-activity-card">
            <header class="dashboard-card-header">
                <div class="dashboard-card-title"><i class="bi bi-clock-history" aria-hidden="true"></i>
                    <div>
                        <h2>Actividad reciente</h2>
                        <p>Últimos movimientos auditados.</p>
                    </div>
                </div><a href="{{ route('management.audit') }}">Ver todas</a>
            </header>
            <div class="dashboard-activity-list">
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
                @forelse ($recentActivity as $activity)
                <article><span class="dashboard-activity-dot"><i class="bi bi-{{ $activity->entidad === 'pedidos' ? 'cart-check' : ($activity->entidad === 'productos' ? 'box-seam' : 'person') }}" aria-hidden="true"></i></span>
                    <div><b>{{ $activity->accion }}</b><small>{{ $activity->user?->nombre ?? 'Sistema' }} · {{ $activity->entidad }}{{ $activity->entidad_id ? ' #'.$activity->entidad_id : '' }}</small></div><time datetime="{{ $activity->creado_en?->toIso8601String() }}">{{ $activity->creado_en?->diffForHumans() }}</time>
                </article>
                @empty
                <div class="dashboard-empty-state"><i class="bi bi-clock-history" aria-hidden="true"></i><strong>Sin actividad reciente</strong><span>Las acciones auditadas aparecerán aquí.</span></div>
                @endforelse
            </div>
        </section>

        <section class="dashboard-card dashboard-task-card">
            <header class="dashboard-card-header">
                <div class="dashboard-card-title"><i class="bi bi-check2-circle" aria-hidden="true"></i>
                    <div>
                        <h2>Tareas / recordatorios</h2>
                        <p>Acciones derivadas de tus datos.</p>
                    </div>
                </div>
            </header>
            <ul class="dashboard-task-list">
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
                @forelse ($reminders as $reminder)
                <li class="dashboard-task dashboard-task--{{ $reminder['tone'] }}"><span class="dashboard-task-check" aria-hidden="true"><i class="bi bi-square"></i></span><span class="dashboard-task-icon"><i class="bi {{ $reminder['icon'] }}" aria-hidden="true"></i></span>
                    <div><b>{{ $reminder['title'] }}</b><small>{{ $reminder['detail'] }}</small></div>
                </li>
                @empty
                <li class="dashboard-task-empty"><i class="bi bi-check-circle-fill" aria-hidden="true"></i><span><b>No hay tareas pendientes</b><small>Tu operación está al día.</small></span></li>
                @endforelse
            </ul>
        </section>
    </div>
</section>
@endif
@endsection
