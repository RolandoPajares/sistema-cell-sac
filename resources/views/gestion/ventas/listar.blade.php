@extends('layouts.administracion')

@section('title', 'Gestión de ventas')
@section('body_class', 'modulo-ventas')

@section('content')
    @php
        $formatCurrency = static fn (float|int|string $value): string => 'S/ '.number_format((float) $value, 2);
        $initialsFor = static function (?string $name): string {
            $parts = preg_split('/\s+/', trim((string) $name), -1, PREG_SPLIT_NO_EMPTY);

            return collect(array_slice($parts ?: ['CL'], 0, 2))
                ->map(static fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))
                ->implode('');
        };
        $imageUrl = static function (?string $path): ?string {
            if (! $path) {
                return null;
            }

            return str_starts_with($path, 'http') ? $path : asset(ltrim($path, '/'));
        };
        $statusFor = static function (string $status): array {
            return match ($status) {
                'Pendiente' => ['label' => 'Pendiente', 'class' => 'pending'],
                'En proceso' => ['label' => 'En proceso', 'class' => 'processing'],
                'Enviado' => ['label' => 'Enviado', 'class' => 'sent'],
                'Entregado' => ['label' => 'Completada', 'class' => 'completed'],
                'Cancelado', 'Cancelada' => ['label' => 'Cancelada', 'class' => 'cancelled'],
                default => ['label' => $status ?: 'Sin estado', 'class' => 'muted'],
            };
        };
        $chartMax = max(1, ...array_column($salesTrend, 'sales'));
        $channelSegments = [];
        $channelStart = 0;
        foreach ($channelSummary as $channel) {
            $channelEnd = $channelStart + $channel['percent'];
            $channelSegments[] = $channel['color'].' '.$channelStart.'% '.$channelEnd.'%';
            $channelStart = $channelEnd;
        }
        $channelDonut = 'background: conic-gradient('.implode(', ', $channelSegments ?: ['#d9e7f6 0% 100%']).');';
        $queryWithoutPage = request()->except(['page']);
    @endphp

    <section class="ventas-page" data-sales-page>
        <header class="ventas-hero">
            <div>
                <nav class="ventas-breadcrumb" aria-label="Ruta de navegación">
                    <a href="{{ route('management.dashboard') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Administración</a>
                    <span aria-hidden="true">/</span>
                    <span>Ventas</span>
                </nav>
                <h1>Gestión de ventas <i class="bi bi-cart3" aria-hidden="true"></i></h1>
                <p>Administra y controla todas tus ventas, clientes y comprobantes en un solo lugar.</p>
            </div>
            <div class="ventas-hero-actions">
                <button class="ventas-secondary-button" type="button" disabled title="No existe un registro de venta independiente; las ventas provienen de los pedidos.">Más opciones <i class="bi bi-chevron-down" aria-hidden="true"></i></button>
                <a class="ventas-secondary-button" href="{{ route('management.sales.export', $queryWithoutPage) }}"><i class="bi bi-download" aria-hidden="true"></i> Exportar <i class="bi bi-chevron-down" aria-hidden="true"></i></a>
                <button class="ventas-primary-button" type="button" data-open-sales-create><i class="bi bi-plus-lg" aria-hidden="true"></i> Nueva venta</button>
            </div>
        </header>

        <section class="ventas-kpi-grid" aria-label="Resumen de ventas">
            <article class="ventas-kpi ventas-kpi--blue">
                <span class="ventas-kpi-icon"><i class="bi bi-currency-dollar" aria-hidden="true"></i></span>
                <div><small>Ventas totales</small><strong>{{ $formatCurrency($totalSales) }}</strong><span><i class="bi bi-arrow-up" aria-hidden="true"></i> Datos del periodo</span></div>
            </article>
            <article class="ventas-kpi ventas-kpi--green">
                <span class="ventas-kpi-icon"><i class="bi bi-cart-check" aria-hidden="true"></i></span>
                <div><small>Ventas realizadas</small><strong>{{ number_format($realizedSales) }}</strong><span><i class="bi bi-arrow-up" aria-hidden="true"></i> Pedidos no cancelados</span></div>
            </article>
            <article class="ventas-kpi ventas-kpi--purple">
                <span class="ventas-kpi-icon"><i class="bi bi-people" aria-hidden="true"></i></span>
                <div><small>Clientes atendidos</small><strong>{{ number_format($customersServed) }}</strong><span>Clientes con pedidos</span></div>
            </article>
            <article class="ventas-kpi ventas-kpi--orange">
                <span class="ventas-kpi-icon"><i class="bi bi-receipt" aria-hidden="true"></i></span>
                <div><small>Ticket promedio</small><strong>{{ $formatCurrency($averageTicket) }}</strong><span>Promedio por venta realizada</span></div>
            </article>
        </section>

        <div class="ventas-main-grid">
            <section class="ventas-card ventas-chart-card">
                <header class="ventas-card-header">
                    <div><h2><i class="bi bi-bar-chart-line" aria-hidden="true"></i> Ventas y tickets</h2><p>Comportamiento de ventas del periodo filtrado.</p></div>
                    <span class="ventas-period-label">{{ \Carbon\CarbonImmutable::parse($filters['desde'])->format('d/m/Y') }} - {{ \Carbon\CarbonImmutable::parse($filters['hasta'])->format('d/m/Y') }}</span>
                </header>
                <div class="ventas-chart-legend"><span><i class="ventas-dot ventas-dot--blue" aria-hidden="true"></i> Ventas (S/)</span><span><i class="ventas-dot ventas-dot--green" aria-hidden="true"></i> N.º ventas</span><strong data-sales-chart-value>{{ $formatCurrency($totalSales) }}</strong></div>
                <div class="ventas-chart" aria-label="Gráfico de ventas y número de ventas">
                    <div class="ventas-chart-y-axis"><span>{{ $formatCurrency($chartMax) }}</span><span>{{ $formatCurrency($chartMax / 2) }}</span><span>S/ 0</span></div>
                    <div class="ventas-chart-plot">
                        <div class="ventas-chart-grid-lines" aria-hidden="true"><span></span><span></span><span></span></div>
                        <div class="ventas-chart-bars">
                            @foreach ($salesTrend as $point)
                                <div class="ventas-chart-point">
                                    <div class="ventas-bar-group" title="{{ $point['label'] }}: {{ $formatCurrency($point['sales']) }} · {{ $point['orders'] }} ventas">
                                        <span class="ventas-bar ventas-bar--sales" style="height: {{ $point['sales_height'] }}%" data-sales-bar data-sales-value="{{ $formatCurrency($point['sales']) }}"></span>
                                        <span class="ventas-bar ventas-bar--orders" style="height: {{ $point['orders_height'] }}%"></span>
                                    </div>
                                    <small>{{ $point['label'] }}</small>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <section class="ventas-card ventas-channel-card">
                <header class="ventas-card-header"><div><h2><i class="bi bi-pie-chart" aria-hidden="true"></i> Ventas por canal</h2><p>Distribución de las ventas registradas.</p></div><span class="ventas-period-label">Periodo actual</span></header>
                <div class="ventas-channel-content">
                    <div class="ventas-donut" style="{{ $channelDonut }}"><span><strong>{{ $formatCurrency($totalSales) }}</strong><small>Total</small></span></div>
                    <div class="ventas-channel-list">
                        @foreach ($channelSummary as $channel)
                            <div><span><i class="ventas-dot" style="background: {{ $channel['color'] }}" aria-hidden="true"></i>{{ $channel['label'] }}</span><strong>{{ number_format($channel['percent'], 1) }}%</strong><small>{{ number_format($channel['count']) }}</small></div>
                        @endforeach
                    </div>
                </div>
            </section>
        </div>

        <div class="ventas-layout-grid">
            <div class="ventas-content-column">
                <form class="ventas-filters" method="get" action="{{ route('management.sales') }}">
                    <label class="ventas-search"><i class="bi bi-search" aria-hidden="true"></i><span class="visually-hidden">Buscar venta</span><input type="search" name="buscar" value="{{ $filters['buscar'] ?? '' }}" placeholder="Buscar por cliente, número de venta, producto..."></label>
                    <label><span class="visually-hidden">Desde</span><input type="date" name="desde" value="{{ $filters['desde'] }}" aria-label="Fecha desde"></label>
                    <label><span class="visually-hidden">Hasta</span><input type="date" name="hasta" value="{{ $filters['hasta'] }}" aria-label="Fecha hasta"></label>
                    <label><span class="visually-hidden">Estado</span><select name="estado" aria-label="Estado"><option value="">Estado: Todos</option><option value="pendiente" @selected(($filters['estado'] ?? null) === 'Pendiente')>Pendiente</option><option value="en_proceso" @selected(($filters['estado'] ?? null) === 'En proceso')>En proceso</option><option value="enviado" @selected(($filters['estado'] ?? null) === 'Enviado')>Enviado</option><option value="entregado" @selected(($filters['estado'] ?? null) === 'Entregado')>Completada</option><option value="cancelado" @selected(($filters['estado'] ?? null) === 'Cancelado')>Cancelada</option></select></label>
                    <label><span class="visually-hidden">Canal</span><select name="canal" aria-label="Canal"><option value="">Canal: Todos</option><option value="tienda_online" @selected(($filters['canal'] ?? null) === 'tienda_online')>Tienda online</option></select></label>
                    <button class="ventas-filter-button" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Filtros</button>
                    <button class="ventas-primary-button ventas-filter-submit" type="submit"><i class="bi bi-search" aria-hidden="true"></i> Buscar</button>
                    @if (($filters['buscar'] ?? null) || ($filters['estado'] ?? null) || ($filters['canal'] ?? null) || $filters['desde'] !== now()->subDays(29)->toDateString() || $filters['hasta'] !== now()->toDateString())
                        <a class="ventas-clear-link" href="{{ route('management.sales') }}">Limpiar</a>
                    @endif
                </form>

                <section class="ventas-card ventas-list-card">
                    <header class="ventas-card-header"><div><h2><i class="bi bi-receipt-cutoff" aria-hidden="true"></i> Listado de ventas</h2><p>Mostrando {{ $orders->firstItem() ?? 0 }} a {{ $orders->lastItem() ?? 0 }} de {{ number_format($orders->total()) }} ventas</p></div><span class="ventas-total-badge">{{ number_format($ordersCount) }} ventas totales</span></header>
                    <div class="ventas-table-wrap">
                        <table class="ventas-table">
                            <thead><tr><th>#</th><th>Fecha</th><th>Cliente</th><th>Productos</th><th>Total</th><th>Canal</th><th>Estado</th><th>Comprobante</th><th>Acciones</th></tr></thead>
                            <tbody>
                                @forelse ($orders as $order)
                                    @php($status = $statusFor($order->estado))
                                    <tr>
                                        <td><strong class="ventas-order-number">{{ str_pad((string) $order->id, 6, '0', STR_PAD_LEFT) }}</strong></td>
                                        <td class="ventas-date">{{ $order->creado_en?->format('d/m/Y') }}<small>{{ $order->creado_en?->format('H:i') }}</small></td>
                                        <td><span class="ventas-customer"><b>{{ $initialsFor($order->user?->nombre) }}</b><span><strong>{{ $order->user?->nombre ?: 'Cliente eliminado' }}</strong><small>{{ $order->user?->correo ?: 'Sin correo' }}</small></span></span></td>
                                        <td>{{ number_format((int) $order->details_count) }} productos</td>
                                        <td><strong class="ventas-total">{{ $formatCurrency($order->total) }}</strong></td>
                                        <td><span class="ventas-channel"><i class="bi bi-shop" aria-hidden="true"></i> Tienda online</span></td>
                                        <td><span class="ventas-status ventas-status--{{ $status['class'] }}"><i class="bi bi-circle-fill" aria-hidden="true"></i>{{ $status['label'] }}</span></td>
                                        <td><span class="ventas-receipt"><i class="bi bi-file-earmark-text" aria-hidden="true"></i> {{ $status['class'] === 'cancelled' ? 'Anulado' : 'Boleta' }}</span></td>
                                        <td><span class="ventas-row-actions"><a class="ventas-action-button" href="{{ route('management.orders') }}?buscar={{ $order->id }}" aria-label="Ver venta {{ $order->id }}"><i class="bi bi-eye" aria-hidden="true"></i></a><a class="ventas-action-button" href="{{ route('management.sales', ['editar' => $order->id]) }}" aria-label="Editar venta {{ $order->id }}"><i class="bi bi-pencil" aria-hidden="true"></i></a></span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="9" class="ventas-empty"><i class="bi bi-inbox" aria-hidden="true"></i><strong>No hay ventas para los filtros seleccionados.</strong><span>Prueba con otro periodo o estado.</span></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <footer class="ventas-pagination"><span>Mostrando {{ $orders->firstItem() ?? 0 }}–{{ $orders->lastItem() ?? 0 }} de {{ number_format($orders->total()) }} ventas</span><div>{{ $orders->onEachSide(1)->links() }}</div></footer>
                </section>
            </div>

            <aside class="ventas-side-column">
                <section class="ventas-card ventas-quick-card">
                    <header class="ventas-card-header"><h2>Acciones rápidas</h2></header>
                    <div class="ventas-quick-actions">
                        <button type="button" data-open-sales-create><i class="bi bi-plus-lg" aria-hidden="true"></i><span>Nueva venta</span><i class="bi bi-chevron-right" aria-hidden="true"></i></button>
                        <button type="button" data-open-sales-create><i class="bi bi-shop" aria-hidden="true"></i><span>Registrar venta física</span><i class="bi bi-chevron-right" aria-hidden="true"></i></button>
                        <a href="{{ route('quotations.index') }}"><i class="bi bi-file-earmark-text" aria-hidden="true"></i><span>Crear cotización</span><i class="bi bi-chevron-right" aria-hidden="true"></i></a>
                        <a href="{{ route('management.orders', ['estado' => 'pendiente']) }}"><i class="bi bi-cart-check" aria-hidden="true"></i><span>Ver pedidos pendientes</span><i class="bi bi-chevron-right" aria-hidden="true"></i></a>
                        <a href="{{ route('management.customers') }}"><i class="bi bi-people" aria-hidden="true"></i><span>Gestionar clientes</span><i class="bi bi-chevron-right" aria-hidden="true"></i></a>
                        <a href="{{ route('management.reports') }}"><i class="bi bi-bar-chart" aria-hidden="true"></i><span>Ver reportes de ventas</span><i class="bi bi-chevron-right" aria-hidden="true"></i></a>
                    </div>
                </section>

                <section class="ventas-card ventas-side-list-card">
                    <header class="ventas-card-header"><h2>Últimas ventas</h2><a href="{{ route('management.sales') }}">Ver todas</a></header>
                    <div class="ventas-recent-list">
                        @forelse ($latestSales as $sale)
                            <div class="ventas-recent-row"><span class="ventas-recent-avatar"><i class="bi bi-person" aria-hidden="true"></i></span><span><strong>{{ $sale->user?->nombre ?: 'Cliente eliminado' }}</strong><small><i class="bi bi-circle-fill" aria-hidden="true"></i> Hace {{ $sale->creado_en?->diffForHumans(null, true) ?: 'un momento' }}</small></span><b>{{ $formatCurrency($sale->total) }}</b></div>
                        @empty
                            <p class="ventas-side-empty">Aún no hay ventas registradas.</p>
                        @endforelse
                    </div>
                </section>

                <section class="ventas-card ventas-side-list-card">
                    <header class="ventas-card-header"><h2>Productos más vendidos</h2><a href="{{ route('management.products') }}">Ver todos</a></header>
                    <div class="ventas-product-list">
                        @forelse ($topProducts as $index => $product)
                            <div class="ventas-product-row"><span class="ventas-product-rank">{{ $index + 1 }}</span><span class="ventas-product-image">@if ($imageUrl($product['image']))<img src="{{ $imageUrl($product['image']) }}" alt="">@else<i class="bi bi-box-seam" aria-hidden="true"></i>@endif</span><span><strong>{{ $product['name'] }}</strong><small>{{ number_format($product['units']) }} unidades</small></span><b>{{ $formatCurrency($product['revenue']) }}</b></div>
                        @empty
                            <p class="ventas-side-empty">Aún no hay productos vendidos.</p>
                        @endforelse
                    </div>
                </section>
            </aside>
        </div>

        <dialog class="ventas-dialog" id="nueva-venta" data-sales-auto-open="{{ $errors->any() && old('_method') !== 'PUT' ? '1' : '0' }}" aria-labelledby="nueva-venta-titulo">
            <div class="ventas-dialog-card">
                <header class="ventas-dialog-header">
                    <div><span class="ventas-dialog-icon"><i class="bi bi-cart-plus" aria-hidden="true"></i></span><span><small>Ventas</small><h2 id="nueva-venta-titulo">Nueva venta</h2><p>Registra una nueva venta y genera su comprobante.</p></span></div>
                    <button class="ventas-dialog-close" type="button" data-close-sales-dialog aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
                </header>
                @include('gestion.ventas.crear', ['customers' => $customers, 'products' => $products])
            </div>
        </dialog>

        @if ($editOrder)
            <dialog class="ventas-dialog" id="editar-venta-{{ $editOrder->id }}" data-sales-auto-open="1" aria-labelledby="editar-venta-titulo-{{ $editOrder->id }}">
                <div class="ventas-dialog-card">
                    <header class="ventas-dialog-header">
                        <div><span class="ventas-dialog-icon"><i class="bi bi-pencil-square" aria-hidden="true"></i></span><span><small>Ventas</small><h2 id="editar-venta-titulo-{{ $editOrder->id }}">Editar venta #{{ str_pad((string) $editOrder->id, 6, '0', STR_PAD_LEFT) }}</h2><p>Actualiza el cliente, la fecha o los productos de esta venta.</p></span></div>
                        <button class="ventas-dialog-close" type="button" data-close-sales-dialog aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
                    </header>
                    @include('gestion.ventas.editar', ['order' => $editOrder, 'customers' => $customers, 'products' => $products])
                </div>
            </dialog>
        @endif
    </section>
@endsection
