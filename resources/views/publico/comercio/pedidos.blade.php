@php
    $isAdmin = in_array(auth()->user()->rol, ['administrador', 'ventas_minoristas', 'ventas_mayoristas'], true);
@endphp
@extends($isAdmin ? 'layouts.administracion' : 'layouts.tienda')

@section('title', 'Pedidos')
@section('body_class', 'modulo-pedidos')

@section('content')
    @if ($isAdmin && auth()->user()->rol === 'administrador')
        @php
            $productImage = static function (?string $path): ?string {
                if (!$path) {
                    return null;
                }

                return str_starts_with($path, 'http') ? $path : asset(ltrim($path, '/'));
            };
            $statusFor = static function (string $status): array {
                return match ($status) {
                    'Pendiente' => ['label' => 'Pendiente', 'class' => 'pending'],
                    'En proceso' => ['label' => 'En preparación', 'class' => 'preparing'],
                    'Enviado' => ['label' => 'Enviado', 'class' => 'sent'],
                    'Entregado' => ['label' => 'Entregado', 'class' => 'delivered'],
                    'Cancelado' => ['label' => 'Cancelado', 'class' => 'cancelled'],
                    default => ['label' => $status, 'class' => 'muted'],
                };
            };
            $paymentFor = static function (string $status): array {
                return match ($status) {
                    'Pendiente' => ['label' => 'Pendiente', 'class' => 'pending'],
                    'Cancelado' => ['label' => 'Reembolsado', 'class' => 'cancelled'],
                    default => ['label' => 'Pagado', 'class' => 'paid'],
                };
            };
            $shippingFor = static function (string $status): array {
                return match ($status) {
                    'Pendiente' => ['label' => 'Por preparar', 'class' => 'muted'],
                    'En proceso' => ['label' => 'En preparación', 'class' => 'sent'],
                    'Enviado' => ['label' => 'En tránsito', 'class' => 'sent'],
                    'Entregado' => ['label' => 'Entregado', 'class' => 'delivered'],
                    'Cancelado' => ['label' => 'Cancelado', 'class' => 'cancelled'],
                    default => ['label' => 'Sin información', 'class' => 'muted'],
                };
            };
            $initialsFor = static function (?string $name): string {
                $parts = preg_split('/\s+/', trim((string) $name), -1, PREG_SPLIT_NO_EMPTY);

                return collect(array_slice($parts ?: ['CL'], 0, 2))->map(static fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
            };
            $months = [1 => 'ene.', 2 => 'feb.', 3 => 'mar.', 4 => 'abr.', 5 => 'may.', 6 => 'jun.', 7 => 'jul.', 8 => 'ago.', 9 => 'sep.', 10 => 'oct.', 11 => 'nov.', 12 => 'dic.'];
            $orderDate = static fn ($date): string => $date ? $date->format('d').' '.$months[(int) $date->format('n')].' '.$date->format('Y') : 'Sin fecha';
            $palette = ['yellow' => '#f5a900', 'blue' => '#2b8df3', 'purple' => '#7137e8', 'green' => '#08a66b', 'red' => '#ef334f'];
            $gradientSegments = [];
            $gradientStart = 0;
            foreach ($statusSummary as $summary) {
                $gradientEnd = $gradientStart + $summary['percent'];
                $gradientSegments[] = $palette[$summary['color']].' '.$gradientStart.'% '.$gradientEnd.'%';
                $gradientStart = $gradientEnd;
            }
            $donutStyle = 'background: conic-gradient('.implode(', ', $gradientSegments ?: ['#dce8f5 0% 100%']).');';
            $alerts = [
                ['show' => $pendingOrders > 0, 'icon' => 'exclamation-triangle', 'class' => 'warning', 'title' => number_format($pendingOrders).' pedidos pendientes', 'detail' => 'Requieren atención inmediata.'],
                ['show' => $cancelledOrders > 0, 'icon' => 'bag-x', 'class' => 'danger', 'title' => number_format($cancelledOrders).' pedidos cancelados', 'detail' => 'Revisa los reembolsos y el stock.'],
                ['show' => $staleOrders > 0, 'icon' => 'bell', 'class' => 'purple', 'title' => number_format($staleOrders).' pedidos sin movimiento', 'detail' => 'Sin actualización en 48 horas.'],
            ];
        @endphp

        <section class="pedidos-page" data-admin-orders-page>
            <header class="pedidos-hero">
                <div>
                    <span class="pedidos-eyebrow">Operación comercial</span>
                    <h1>Pedidos <i class="bi bi-cart3" aria-hidden="true"></i></h1>
                    <p>Gestiona pedidos, pagos, envíos y estados desde un solo lugar.</p>
                </div>
                <div class="pedidos-period-card"><i class="bi bi-calendar3" aria-hidden="true"></i><span><small>Periodo consultado</small><strong>{{ $filters['periodo'] === 'all' ? 'Todo el historial' : 'Últimos '.$filters['periodo'].' días' }}</strong></span><i class="bi bi-chevron-down" aria-hidden="true"></i></div>
            </header>

            <section class="pedidos-kpi-grid" aria-label="Resumen de pedidos">
                <article class="pedidos-kpi pedidos-kpi--blue"><span class="pedidos-kpi-icon"><i class="bi bi-cart3" aria-hidden="true"></i></span><div><small>Pedidos hoy</small><strong>{{ number_format($todayOrders) }}</strong><span>vs. ayer</span></div><b class="pedidos-kpi-trend {{ ($todayOrdersTrend ?? 0) >= 0 ? 'is-positive' : 'is-negative' }}"><i class="bi bi-arrow-{{ ($todayOrdersTrend ?? 0) >= 0 ? 'up' : 'down' }}" aria-hidden="true"></i> @if ($todayOrdersTrend !== null){{ number_format(abs($todayOrdersTrend), 1) }}%@else sin periodo anterior @endif</b></article>
                <article class="pedidos-kpi pedidos-kpi--orange"><span class="pedidos-kpi-icon"><i class="bi bi-clock" aria-hidden="true"></i></span><div><small>Pendientes</small><strong>{{ number_format($pendingOrders) }}</strong><span>Requieren atención</span></div><b class="pedidos-kpi-trend {{ $pendingOrders > 0 ? 'is-negative' : 'is-positive' }}"><i class="bi bi-{{ $pendingOrders > 0 ? 'arrow-down' : 'check' }}" aria-hidden="true"></i> {{ $pendingOrders > 0 ? 'Revisar' : 'Al día' }}</b></article>
                <article class="pedidos-kpi pedidos-kpi--green"><span class="pedidos-kpi-icon"><i class="bi bi-credit-card" aria-hidden="true"></i></span><div><small>Pagados</small><strong>{{ number_format($paidOrders) }}</strong><span>Pedidos confirmados</span></div><b class="pedidos-kpi-trend is-positive"><i class="bi bi-arrow-up" aria-hidden="true"></i> Estado actual</b></article>
                <article class="pedidos-kpi pedidos-kpi--blue"><span class="pedidos-kpi-icon"><i class="bi bi-truck" aria-hidden="true"></i></span><div><small>En envío</small><strong>{{ number_format($shippingOrders) }}</strong><span>En tránsito</span></div><b class="pedidos-kpi-trend is-positive"><i class="bi bi-arrow-up" aria-hidden="true"></i> Seguimiento</b></article>
                <article class="pedidos-kpi pedidos-kpi--purple"><span class="pedidos-kpi-icon"><i class="bi bi-box-seam" aria-hidden="true"></i></span><div><small>Entregados</small><strong>{{ number_format($deliveredOrders) }}</strong><span>Pedidos completados</span></div><b class="pedidos-kpi-trend is-positive"><i class="bi bi-check2" aria-hidden="true"></i> Finalizados</b></article>
                <article class="pedidos-kpi pedidos-kpi--red"><span class="pedidos-kpi-icon"><i class="bi bi-x-circle" aria-hidden="true"></i></span><div><small>Cancelados</small><strong>{{ number_format($cancelledOrders) }}</strong><span>Pedidos cancelados</span></div><b class="pedidos-kpi-trend {{ $cancelledOrders > 0 ? 'is-negative' : 'is-positive' }}"><i class="bi bi-{{ $cancelledOrders > 0 ? 'arrow-down' : 'check' }}" aria-hidden="true"></i> {{ $cancelledOrders > 0 ? 'Revisar' : 'Sin casos' }}</b></article>
            </section>

            <div class="pedidos-toolbar">
                <button class="pedidos-primary-button" type="button" disabled title="Los pedidos se crean desde la tienda"><i class="bi bi-plus-lg" aria-hidden="true"></i> Nuevo pedido</button>
                <button class="pedidos-toolbar-button" type="button" disabled><i class="bi bi-download" aria-hidden="true"></i> Exportar <i class="bi bi-chevron-down" aria-hidden="true"></i></button>
                <button class="pedidos-toolbar-button" type="button" data-refresh-orders><i class="bi bi-arrow-repeat" aria-hidden="true"></i> Actualizar</button>
            </div>

            <form class="pedidos-filters" method="get" action="{{ route('management.orders') }}">
                <label class="pedidos-search"><i class="bi bi-search" aria-hidden="true"></i><span class="visually-hidden">Buscar pedido o cliente</span><input type="search" name="buscar" value="{{ $filters['buscar'] ?? '' }}" placeholder="Buscar pedido o cliente..."></label>
                <label><span class="visually-hidden">Filtrar por estado</span><select name="estado"><option value="">Estado: Todos</option><option value="pendiente" @selected(($filters['estado'] ?? null) === 'Pendiente')>Pendiente</option><option value="en_proceso" @selected(($filters['estado'] ?? null) === 'En proceso')>En preparación</option><option value="enviado" @selected(($filters['estado'] ?? null) === 'Enviado')>Enviado</option><option value="entregado" @selected(($filters['estado'] ?? null) === 'Entregado')>Entregado</option><option value="cancelado" @selected(($filters['estado'] ?? null) === 'Cancelado')>Cancelado</option></select></label>
                <label><span class="visually-hidden">Filtrar por fecha</span><select name="periodo"><option value="7" @selected($filters['periodo'] === '7')>Últimos 7 días</option><option value="30" @selected($filters['periodo'] === '30')>Últimos 30 días</option><option value="90" @selected($filters['periodo'] === '90')>Últimos 90 días</option><option value="all" @selected($filters['periodo'] === 'all')>Todo el historial</option></select></label>
                <label><span class="visually-hidden">Filtrar por canal</span><select name="canal"><option value="">Canal: Todos</option><option value="tienda_online" @selected(($filters['canal'] ?? null) === 'tienda_online')>Tienda online</option></select></label>
                <button class="pedidos-more-filters" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Más filtros</button>
                @if (($filters['buscar'] ?? null) || ($filters['estado'] ?? null) || $filters['periodo'] !== '30')<a class="pedidos-clear-filters" href="{{ route('management.orders') }}">Limpiar</a>@endif
            </form>

            <section class="pedidos-list-card">
                <header class="pedidos-card-header"><div class="pedidos-card-title"><i class="bi bi-receipt" aria-hidden="true"></i><div><h2>Lista de pedidos</h2><p>Mostrando {{ $orders->firstItem() ?? 0 }} a {{ $orders->lastItem() ?? 0 }} de {{ number_format($orders->total()) }} pedidos</p></div></div><span class="pedidos-total-badge">{{ number_format($totalOrders) }} pedidos totales</span></header>
                <div class="pedidos-table-wrap">
                    <table class="pedidos-table">
                        <thead><tr><th><span class="pedidos-checkbox" aria-hidden="true"></span></th><th>Pedido</th><th>Cliente</th><th>Fecha</th><th>Canal</th><th>Total</th><th>Estado</th><th>Pago</th><th>Envío</th><th>Acciones</th></tr></thead>
                        <tbody>
                            @forelse ($orders as $order)
                                @php
                                    $status = $statusFor($order->estado);
                                    $payment = $paymentFor($order->estado);
                                    $shipping = $shippingFor($order->estado);
                                @endphp
                                <tr>
                                    <td><span class="pedidos-checkbox" aria-hidden="true"></span></td>
                                    <td><button class="pedidos-number" type="button" data-open-order-dialog="pedido-{{ $order->id }}">#PED-{{ str_pad((string) $order->id, 5, '0', STR_PAD_LEFT) }}</button></td>
                                    <td><span class="pedidos-customer"><b>{{ $initialsFor($order->user?->nombre) }}</b><span><strong>{{ $order->user?->nombre ?: 'Cliente eliminado' }}</strong><small>{{ $order->user?->correo ?: 'Sin correo' }}</small></span></span></td>
                                    <td class="pedidos-date">{{ $orderDate($order->creado_en) }}<small>{{ $order->creado_en?->format('H:i') }}</small></td>
                                    <td><span class="pedidos-channel"><i class="bi bi-shop" aria-hidden="true"></i> Tienda online</span></td>
                                    <td><strong class="pedidos-total">S/ {{ number_format((float) $order->total, 2) }}</strong></td>
                                    <td><span class="pedidos-status pedidos-status--{{ $status['class'] }}"><i class="bi bi-circle-fill" aria-hidden="true"></i>{{ $status['label'] }}</span></td>
                                    <td><span class="pedidos-status pedidos-status--{{ $payment['class'] }}"><i class="bi bi-circle-fill" aria-hidden="true"></i>{{ $payment['label'] }}</span></td>
                                    <td><span class="pedidos-status pedidos-status--{{ $shipping['class'] }}"><i class="bi bi-circle-fill" aria-hidden="true"></i>{{ $shipping['label'] }}</span></td>
                                    <td><button class="pedidos-action-button" type="button" data-open-order-dialog="pedido-{{ $order->id }}" aria-label="Ver pedido {{ $order->id }}"><i class="bi bi-three-dots" aria-hidden="true"></i></button></td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="pedidos-empty"><i class="bi bi-inbox" aria-hidden="true"></i><strong>No hay pedidos que coincidan con los filtros.</strong><span>Prueba con otro periodo o estado.</span></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <footer class="pedidos-pagination"><span>Mostrando {{ $orders->firstItem() ?? 0 }}–{{ $orders->lastItem() ?? 0 }} de {{ number_format($orders->total()) }} pedidos</span><form class="pedidos-per-page" method="get" action="{{ route('management.orders') }}">@foreach (request()->except(['page', 'por_pagina']) as $key => $value)@if(is_scalar($value))<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif @endforeach<label for="pedidos-por-pagina">Pedidos por página</label><select id="pedidos-por-pagina" name="por_pagina" onchange="this.form.submit()" aria-label="Pedidos por página">@foreach ([10, 20, 50] as $option)<option value="{{ $option }}" @selected(($filters['por_pagina'] ?? 10) === $option)>{{ $option }}</option>@endforeach</select></form>{{ $orders->onEachSide(1)->links() }}</footer>
            </section>

            <div class="pedidos-insights-grid">
                <section class="pedidos-insight-card">
                    <header class="pedidos-card-header"><div class="pedidos-card-title"><i class="bi bi-pie-chart" aria-hidden="true"></i><div><h2>Estado de pedidos</h2><p>{{ number_format($totalOrders) }} pedidos totales</p></div></div><span class="pedidos-insight-period">Periodo actual</span></header>
                    <div class="pedidos-status-chart"><div class="pedidos-donut" style="{{ $donutStyle }}"><span><strong>{{ number_format($totalOrders) }}</strong><small>Pedidos totales</small></span></div><div class="pedidos-legend">@foreach ($statusSummary as $summary)<div><span><i class="pedidos-legend-dot pedidos-legend-dot--{{ $summary['color'] }}" aria-hidden="true"></i>{{ $summary['label'] }}</span><strong>{{ number_format($summary['count']) }}</strong><small>{{ number_format($summary['percent'], 1) }}%</small></div>@endforeach</div></div>
                </section>

                <section class="pedidos-insight-card">
                    <header class="pedidos-card-header"><div class="pedidos-card-title"><i class="bi bi-people" aria-hidden="true"></i><div><h2>Pedidos por cliente</h2><p>Distribución de compradores registrados.</p></div></div><span class="pedidos-insight-period">{{ number_format($totalOrders) }} pedidos</span></header>
                    <div class="pedidos-bars">@foreach ($customerSummary as $summary)<div class="pedidos-bar-row"><span><i class="pedidos-legend-dot pedidos-legend-dot--{{ $summary['color'] }}" aria-hidden="true"></i>{{ $summary['label'] }}</span><div class="pedidos-bar"><i class="pedidos-bar-fill pedidos-bar-fill--{{ $summary['color'] }}" style="width: {{ min(100, $summary['percent']) }}%"></i></div><strong>{{ number_format($summary['percent'], 1) }}%</strong><small>{{ number_format($summary['count']) }}</small></div>@endforeach</div>
                </section>
            </div>

            <div class="pedidos-bottom-grid">
                <section class="pedidos-secondary-card"><header class="pedidos-card-header"><div class="pedidos-card-title"><i class="bi bi-bag-check" aria-hidden="true"></i><div><h2>Pedidos recientes</h2><p>Últimas operaciones registradas.</p></div></div><button class="pedidos-view-all" type="button" data-refresh-orders>Actualizar</button></header><div class="pedidos-recent-list">
                    @forelse ($recentOrders as $order)
                        @php $recentStatus = $statusFor($order->estado); $recentProduct = $order->details->first()?->product; $recentImage = $productImage($recentProduct?->url_imagen); @endphp
                        <button class="pedidos-recent-row" type="button" data-open-order-dialog="pedido-{{ $order->id }}">@if($recentImage)<img src="{{ $recentImage }}" alt="{{ $recentProduct?->nombre }}">@else<span class="pedidos-recent-image"><i class="bi bi-box-seam" aria-hidden="true"></i></span>@endif<span><strong>#PED-{{ str_pad((string) $order->id, 5, '0', STR_PAD_LEFT) }}</strong><small>{{ $order->user?->nombre ?: 'Cliente eliminado' }} · S/ {{ number_format((float) $order->total, 2) }}</small></span><em class="pedidos-status pedidos-status--{{ $recentStatus['class'] }}">{{ $recentStatus['label'] }}</em></button>
                    @empty
                        <div class="pedidos-secondary-empty">Todavía no hay pedidos recientes.</div>
                    @endforelse
                </div></section>

                <section class="pedidos-secondary-card"><header class="pedidos-card-header"><div class="pedidos-card-title"><i class="bi bi-stars" aria-hidden="true"></i><div><h2>Actividad reciente</h2><p>Últimos cambios de la operación.</p></div></div><button class="pedidos-view-all" type="button" data-refresh-orders>Ver todo →</button></header><div class="pedidos-activity-list">
                    @forelse ($recentOrders as $order)
                        @php $activity = $statusFor($order->estado); $activityIcon = match($order->estado) { 'Entregado' => 'check2', 'Cancelado' => 'x-lg', 'Enviado' => 'truck', 'En proceso' => 'box-seam', default => 'cart3' }; @endphp
                        <div class="pedidos-activity-row"><span class="pedidos-activity-icon pedidos-activity-icon--{{ $activity['class'] }}"><i class="bi bi-{{ $activityIcon }}" aria-hidden="true"></i></span><span><strong>Pedido #PED-{{ str_pad((string) $order->id, 5, '0', STR_PAD_LEFT) }} {{ strtolower($activity['label']) }}</strong><small>{{ $order->user?->nombre ?: 'Cliente eliminado' }} · {{ $order->creado_en?->format('d/m/Y H:i') }}</small></span></div>
                    @empty
                        <div class="pedidos-secondary-empty">Todavía no hay actividad.</div>
                    @endforelse
                </div></section>

                <section class="pedidos-secondary-card pedidos-alert-card"><header class="pedidos-card-header"><div class="pedidos-card-title"><i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i><div><h2>Alertas e incidencias</h2><p>Situaciones que requieren seguimiento.</p></div></div><span class="pedidos-alert-count">{{ collect($alerts)->where('show', true)->count() }}</span></header><div class="pedidos-alert-list">
                    @forelse (collect($alerts)->where('show', true) as $alert)<div class="pedidos-alert-row"><span class="pedidos-alert-icon pedidos-alert-icon--{{ $alert['class'] }}"><i class="bi bi-{{ $alert['icon'] }}" aria-hidden="true"></i></span><span><strong>{{ $alert['title'] }}</strong><small>{{ $alert['detail'] }}</small></span></div>@empty<div class="pedidos-secondary-empty"><i class="bi bi-check-circle" aria-hidden="true"></i>Operación al día.</div>@endforelse
                </div></section>
            </div>
        </section>

        @foreach ($orders as $order)
            @php $dialogStatus = $statusFor($order->estado); @endphp
            <dialog class="pedidos-dialog" id="pedido-{{ $order->id }}"><div class="pedidos-dialog-card"><header class="pedidos-dialog-header"><div><span class="pedidos-eyebrow">Detalle del pedido</span><h2>#PED-{{ str_pad((string) $order->id, 5, '0', STR_PAD_LEFT) }}</h2><p>{{ $order->user?->nombre ?: 'Cliente eliminado' }} · {{ $orderDate($order->creado_en) }}</p></div><button class="pedidos-dialog-close" type="button" data-close-order-dialog aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header><div class="pedidos-dialog-body"><div class="pedidos-dialog-summary"><div><small>Total</small><strong>S/ {{ number_format((float) $order->total, 2) }}</strong></div><div><small>Estado</small><span class="pedidos-status pedidos-status--{{ $dialogStatus['class'] }}">{{ $dialogStatus['label'] }}</span></div><div><small>Cliente</small><strong>{{ $order->user?->correo ?: 'Sin correo' }}</strong></div></div><h3>Productos del pedido</h3><div class="pedidos-dialog-products">@forelse($order->details as $line)<div><span>{{ $line->product?->nombre ?: 'Producto eliminado' }} × {{ $line->cantidad }}</span><strong>S/ {{ number_format($line->cantidad * $line->precio_unitario, 2) }}</strong></div>@empty<div class="pedidos-secondary-empty">Sin detalle de productos.</div>@endforelse</div><form method="post" action="{{ route('legacy.admin.orders.status', $order) }}" class="pedidos-status-form">@csrf @method('PATCH')<label><span>Actualizar estado</span><select name="estado">@foreach(['Pendiente', 'En proceso', 'Enviado', 'Entregado', 'Cancelado'] as $state)<option value="{{ $state }}" @selected($order->estado === $state)>{{ $state === 'En proceso' ? 'En preparación' : $state }}</option>@endforeach</select></label><button class="pedidos-primary-button" type="submit"><i class="bi bi-check2" aria-hidden="true"></i> Guardar estado</button></form></div></div></dialog>
        @endforeach
    @else
        <section class="container content-page">
            <header class="admin-page-head">
                <div><span class="eyebrow">Operación comercial</span><h1>Pedidos <i class="bi bi-cart3"></i></h1><p>Consulta el historial y actualiza el estado según tu rol.</p></div>
            </header>
            @forelse ($orders as $order)
                <article class="data-row"><div><b>Pedido #{{ $order->id }}</b><small>{{ $order->creado_en?->format('d/m/Y H:i') ?? $order->creado_en }} · {{ $order->estado }}</small></div><strong>S/ {{ number_format((float) $order->total, 2) }}</strong><a class="btn btn-secondary" href="{{ route('orders.show', $order) }}">Ver detalle</a></article>
            @empty
                <p>Aún no hay pedidos.</p>
            @endforelse
            {{ $orders->links() }}
        </section>
    @endif
@endsection

@if ($isAdmin && auth()->user()->rol === 'administrador')
    @push('scripts')
        <script>
            (() => {
                const page = document.querySelector('[data-admin-orders-page]');

                if (!page) {
                    return;
                }

                page.querySelectorAll('[data-open-order-dialog]').forEach((button) => {
                    button.addEventListener('click', () => document.getElementById(button.dataset.openOrderDialog)?.showModal());
                });
                document.querySelectorAll('.pedidos-dialog').forEach((dialog) => {
                    dialog.querySelectorAll('[data-close-order-dialog]').forEach((button) => button.addEventListener('click', () => dialog.close()));
                    dialog.addEventListener('click', (event) => {
                        if (event.target === dialog) {
                            dialog.close();
                        }
                    });
                });
                page.querySelectorAll('[data-refresh-orders]').forEach((button) => button.addEventListener('click', () => window.location.reload()));
            })();
        </script>
    @endpush
@endif
