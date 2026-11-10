@extends('layouts.panel.principal')

@section('title', 'Gestión de ventas')
@section('body_class', 'modulo-ventas')

@section('content')
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
            <details class="ventas-export-menu"><summary class="ventas-secondary-button"><i class="bi bi-download" aria-hidden="true"></i> Exportar <i class="bi bi-chevron-down" aria-hidden="true"></i></summary><div class="ventas-export-options"><a href="{{ route('management.sales.export', ['format' => 'pdf'] + $queryWithoutPage) }}"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i><span><strong>Exportar PDF</strong><small>Documento imprimible</small></span></a><a href="{{ route('management.sales.export', ['format' => 'excel'] + $queryWithoutPage) }}"><i class="bi bi-file-earmark-spreadsheet" aria-hidden="true"></i><span><strong>Exportar Excel</strong><small>Archivo compatible .xlsx</small></span></a><a href="{{ route('management.sales.export', ['format' => 'csv'] + $queryWithoutPage) }}"><i class="bi bi-filetype-csv" aria-hidden="true"></i><span><strong>Exportar CSV</strong><small>Datos delimitados</small></span></a></div></details>
            <button class="ventas-primary-button" type="button" @disabled(! $canCreate) @if ($canCreate) data-open-sales-create @endif title="{{ $canCreate ? 'Registrar venta física' : 'Registro no disponible: faltan permisos, almacén o la migración requerida.' }}"><i class="bi bi-plus-lg" aria-hidden="true"></i> Nueva venta</button>
        </div>
    </header>

    <section class="ventas-kpi-grid" aria-label="Resumen de ventas">
        <article class="ventas-kpi ventas-kpi--blue">
            <span class="ventas-kpi-icon"><i class="bi bi-currency-dollar" aria-hidden="true"></i></span>
            <div><small>Importe registrado</small><strong>{{ $totalSalesLabel }}</strong><span>Todos los estados; no implica cobro</span></div>
        </article>
        <article class="ventas-kpi ventas-kpi--green">
            <span class="ventas-kpi-icon"><i class="bi bi-cart-check" aria-hidden="true"></i></span>
            <div><small>Ventas registradas</small><strong>{{ number_format($realizedSales) }}</strong><span>Todos los estados</span></div>
        </article>
        <article class="ventas-kpi ventas-kpi--purple">
            <span class="ventas-kpi-icon"><i class="bi bi-people" aria-hidden="true"></i></span>
            <div><small>Clientes registrados</small><strong>{{ number_format($customersServed) }}</strong><span>Clientes asociados a ventas</span></div>
        </article>
        <article class="ventas-kpi ventas-kpi--orange">
            <span class="ventas-kpi-icon"><i class="bi bi-receipt" aria-hidden="true"></i></span>
            <div><small>Importe promedio</small><strong>{{ $averageTicketLabel }}</strong><span>Promedio de registros</span></div>
        </article>
    </section>

    <div class="ventas-main-grid">
        <section class="ventas-card ventas-chart-card">
            <header class="ventas-card-header">
                <div>
                    <h2><i class="bi bi-bar-chart-line" aria-hidden="true"></i> Ventas y tickets</h2>
                    <p>Comportamiento de ventas del periodo filtrado.</p>
                </div>
                <span class="ventas-period-label">{{ $periodStartLabel }} - {{ $periodEndLabel }}</span>
            </header>
            @if ($monedasMultiples)
            <p>La gráfica de importes no está disponible para ventas en monedas diferentes.</p>
            @else
            <div class="ventas-chart-legend"><span><i class="ventas-dot ventas-dot--blue" aria-hidden="true"></i> Importes registrados</span><span><i class="ventas-dot ventas-dot--green" aria-hidden="true"></i> N.º ventas</span><strong data-sales-chart-value>{{ $totalSalesLabel }}</strong></div>
            <div class="ventas-chart" aria-label="Gráfico de ventas y número de ventas">
                <div class="ventas-chart-y-axis"><span>{{ $chartMaxLabel }}</span><span>{{ $chartMidLabel }}</span><span>{{ $chartZeroLabel }}</span></div>
                <div class="ventas-chart-plot">
                    <div class="ventas-chart-grid-lines" aria-hidden="true"><span></span><span></span><span></span></div>
                    <div class="ventas-chart-bars">
                        @foreach ($salesTrend as $point)
                        <div class="ventas-chart-point">
                            <div class="ventas-bar-group" title="{{ $point['label'] }}: {{ $point['sales_label'] }} · {{ $point['orders'] }} ventas">
                                <span class="ventas-bar ventas-bar--sales" data-sales-bar data-sales-height="{{ $point['sales_height'] }}" data-sales-value="{{ $point['sales_label'] }}"></span>
                                <span class="ventas-bar ventas-bar--orders" data-orders-bar data-orders-height="{{ $point['orders_height'] }}"></span>
                            </div>
                            <small>{{ $point['label'] }}</small>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </section>

        <section class="ventas-card ventas-channel-card">
            <header class="ventas-card-header">
                <div>
                    <h2><i class="bi bi-pie-chart" aria-hidden="true"></i> Ventas por canal</h2>
                    <p>Distribución de las ventas registradas.</p>
                </div><span class="ventas-period-label">Periodo actual</span>
            </header>
            <div class="ventas-channel-content">
                <div class="ventas-donut" data-sales-donut="{{ $channelDonut }}"><span><strong>{{ $totalSalesLabel }}</strong><small>Total</small></span></div>
                <div class="ventas-channel-list">
                    @foreach ($channelSummary as $channel)
                    <div><span><i class="ventas-dot" data-channel-color="{{ $channel['color'] }}" aria-hidden="true"></i>{{ $channel['label'] }}</span><strong>{{ number_format($channel['percent'], 1) }}%</strong><small>{{ number_format($channel['count']) }}</small></div>
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
                <label><span class="visually-hidden">Cliente</span><select name="cliente_id" aria-label="Cliente">
                        <option value="">Cliente: Todos</option>@foreach ($clientesDisponibles as $cliente)<option value="{{ $cliente->id }}" @selected(($filters['cliente_id'] ?? null)===$cliente->id)>{{ $cliente->razon_social ?: $cliente->nombre_contacto ?: 'Cliente '.$cliente->id }}</option>@endforeach
                    </select></label>
                <label><span class="visually-hidden">Sede</span><select name="sede_id" aria-label="Sede">
                        <option value="">Sede: Todas</option>@foreach ($sedesDisponibles as $sede)<option value="{{ $sede->id }}" @selected(($filters['sede_id'] ?? null)===$sede->id)>{{ $sede->nombre }}</option>@endforeach
                    </select></label>
                <label><span class="visually-hidden">Estado</span><select name="estado" aria-label="Estado">
                        <option value="">Estado: Todos</option>@foreach ($estados as $estado)<option value="{{ $estado }}" @selected(($filters['estado'] ?? null)===$estado)>{{ $estado }}</option>@endforeach
                    </select></label>
                <label><span class="visually-hidden">Canal</span><select name="canal" aria-label="Canal">
                        <option value="">Canal: Todos</option>
                        <option value="ecommerce" @selected(($filters['canal'] ?? null)==='ecommerce' )>Ecommerce</option>
                        <option value="presencial" @selected(($filters['canal'] ?? null)==='presencial' )>Presencial</option>
                    </select></label>
                <button class="ventas-filter-button" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Filtros</button>
                <button class="ventas-primary-button ventas-filter-submit" type="submit"><i class="bi bi-search" aria-hidden="true"></i> Buscar</button>
                @if ($hasActiveFilters)
                <a class="ventas-clear-link" href="{{ route('management.sales') }}">Limpiar</a>
                @endif
            </form>

            <section class="ventas-card ventas-list-card">
                <header class="ventas-card-header">
                    <div>
                        <h2><i class="bi bi-receipt-cutoff" aria-hidden="true"></i> Listado de ventas</h2>
                        <p>Mostrando {{ $orders->firstItem() ?? 0 }} a {{ $orders->lastItem() ?? 0 }} de {{ number_format($orders->total()) }} ventas</p>
                    </div><span class="ventas-total-badge">{{ number_format($ordersCount) }} ventas totales</span>
                </header>
                <div class="ventas-table-wrap">
                    <table class="ventas-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Productos</th>
                                <th>Total</th>
                                <th>Canal</th>
                                <th>Estado</th>
                                <th>Comprobante</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($orders as $order)
                            <tr>
                                <td><strong class="ventas-order-number">{{ $order->codigo }}</strong></td>
                                <td class="ventas-date">{{ $order->fecha_venta?->format('d/m/Y') }}<small>{{ $order->fecha_venta?->format('H:i') }}</small></td>
                                <td><span class="ventas-customer"><b>{{ $salesPresentation[$order->id]['initials'] }}</b><span><strong>{{ $order->cliente?->razon_social ?: $order->cliente?->nombre_contacto ?: 'Sin cliente asociado' }}</strong><small>{{ $order->cliente?->correo ?: 'Sin correo' }}</small></span></span></td>
                                <td>{{ number_format((int) $order->detalles_count) }} productos</td>
                                <td><strong class="ventas-total">{{ $order->moneda }} {{ number_format((float) $order->total, 2) }}</strong></td>
                                <td><span class="ventas-channel"><i class="bi bi-shop" aria-hidden="true"></i> {{ $order->canal ?: $order->pedido?->canal ?: 'Sin canal registrado' }}</span></td>
                                <td><span class="ventas-status ventas-status--{{ $salesPresentation[$order->id]['status']['class'] }}"><i class="bi bi-circle-fill" aria-hidden="true"></i>{{ $salesPresentation[$order->id]['status']['label'] }}</span></td>
                                <td><span class="ventas-receipt">No consultado</span></td>
                                <td><span class="ventas-row-actions"><button class="ventas-action-button" type="button" data-sales-details="{{ $order->id }}" title="Ver detalle" aria-label="Ver detalle de venta {{ $order->codigo }}"><i class="bi bi-eye" aria-hidden="true"></i></button>@if (in_array($order->id, $editables, true))<a class="ventas-action-button" href="{{ route('management.sales', ['editar' => $order->id]) }}" title="Editar borrador" aria-label="Editar venta {{ $order->codigo }}"><i class="bi bi-pencil" aria-hidden="true"></i></a>@else<button class="ventas-action-button" type="button" disabled title="Solo se editan borradores autorizados sin movimientos asociados" aria-label="Editar venta no disponible"><i class="bi bi-pencil" aria-hidden="true"></i></button>@endif
                                        @if (in_array($order->id, $confirmables, true) && $order->estado === 'borrador' && $order->pedido_id !== null)
                                        <button class="ventas-action-button" type="button" data-open-sales-confirm="confirmar-venta-{{ $order->id }}" title="Confirmar venta y descontar existencias" aria-label="Confirmar venta {{ $order->codigo }}"><i class="bi bi-check-circle" aria-hidden="true"></i></button>
                                        @endif
                                        @if (in_array($order->id, $cancelables) && $order->estado === 'confirmada' && $order->canal === 'presencial' && $order->pedido_id === null)
                                        <form method="post" action="{{ route('management.sales.cancel', $order) }}" data-sales-cancel>
                                            @csrf
                                            <input type="hidden" name="motivo">
                                            <button class="ventas-action-button" type="submit" title="Anular venta" aria-label="Anular venta {{ $order->codigo }}"><i class="bi bi-x-circle" aria-hidden="true"></i></button>
                                        </form>
                                        @endif
                                    </span></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="ventas-empty"><i class="bi bi-inbox" aria-hidden="true"></i><strong>No hay ventas para los filtros seleccionados.</strong><span>Prueba con otro periodo o estado.</span></td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @foreach ($orders as $order)
                @if (in_array($order->id, $confirmables, true) && $order->estado === 'borrador' && $order->pedido_id !== null)
                <dialog class="ventas-dialog" id="confirmar-venta-{{ $order->id }}" aria-labelledby="confirmar-venta-titulo-{{ $order->id }}">
                    <div class="ventas-dialog-card">
                        <header class="ventas-dialog-header"><div><h2 id="confirmar-venta-titulo-{{ $order->id }}">Confirmar venta {{ $order->codigo }}</h2><p>Se validarán las existencias y los identificadores antes de descontar inventario.</p></div><button class="ventas-dialog-close" type="button" data-close-sales-dialog aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header>
                        <form class="ventas-modal-form" method="post" action="{{ route('management.sales.confirm', $order) }}" data-sales-confirm>
                            @csrf
                            <section class="ventas-modal-section">
                                <div class="ventas-modal-section-header"><i class="bi bi-phone" aria-hidden="true"></i><h3>IMEI y números de serie</h3></div>
                            @foreach ($order->detalles as $detalle)
                                @if (($dispositivosPorDetalle->get($detalle->getKey())['requiere'] ?? false))
                                <label class="ventas-modal-field"><span>{{ $detalle->descripcion_historica }} — {{ number_format((float) $detalle->cantidad, 0) }} unidad(es) serializadas</span>
                                    <select name="dispositivos[{{ $detalle->getKey() }}][]" multiple required size="{{ min(5, max(2, (int) $detalle->cantidad)) }}">
                                        @foreach (($dispositivosPorDetalle->get($detalle->getKey())['dispositivos'] ?? collect()) as $dispositivo)
                                            <option value="{{ $dispositivo->id }}">{{ $dispositivo->imei ?: $dispositivo->numero_serie ?: 'Dispositivo '.$dispositivo->id }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                @endif
                            @endforeach
                                @if ($devicesUnavailable->get($order->id, false))
                                    <p role="status">No hay suficientes IMEI o series disponibles en el almacén asociado. Registra o recibe las unidades antes de confirmar.</p>
                                @endif
                            </section>
                            <footer class="ventas-modal-footer"><button class="ventas-modal-cancel" type="button" data-close-sales-dialog>Cancelar</button><span></span><button class="ventas-primary-button" type="submit" @disabled($devicesUnavailable->get($order->id, false))>Confirmar y descontar existencias</button></footer>
                        </form>
                    </div>
                </dialog>
                @endif
                <dialog class="ventas-dialog" id="detalle-venta-{{ $order->id }}" aria-labelledby="detalle-venta-titulo-{{ $order->id }}">
                    <div class="ventas-dialog-card">
                        <header class="ventas-dialog-header">
                            <div>
                                <h2 id="detalle-venta-titulo-{{ $order->id }}">Venta {{ $order->codigo }}</h2>
                                <p>{{ $order->fecha_venta?->format('d/m/Y H:i') }} · {{ $order->estado }}</p>
                            </div><button class="ventas-dialog-close" type="button" data-close-sales-dialog aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
                        </header>
                        <div class="ventas-modal-table-wrap">
                            <table class="ventas-modal-table">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>SKU</th>
                                        <th>Cantidad</th>
                                        <th>Precio</th>
                                        <th>Importe</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($order->detalles as $detalle)
                                    <tr>
                                        <td>{{ $detalle->descripcion_historica }}</td>
                                        <td>{{ $detalle->sku_historico ?: '—' }}</td>
                                        <td>{{ $detalle->cantidad }}</td>
                                        <td>{{ $order->moneda }} {{ number_format((float) $detalle->precio_unitario, 2) }}</td>
                                        <td>{{ $order->moneda }} {{ number_format((float) $detalle->total_linea, 2) }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5">La venta no tiene detalles registrados.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if ($order->observaciones)<p>{{ $order->observaciones }}</p>@endif
                        <p>Total: {{ $order->moneda }} {{ number_format((float) $order->total, 2) }}</p>
                    </div>
                </dialog>
                @endforeach
                <footer class="ventas-pagination"><span>Mostrando {{ $orders->firstItem() ?? 0 }}–{{ $orders->lastItem() ?? 0 }} de {{ number_format($orders->total()) }} ventas</span>
                    <div>{{ $orders->onEachSide(1)->links() }}</div>
                </footer>
            </section>
        </div>

        <aside class="ventas-side-column">
            <section class="ventas-card ventas-quick-card">
                <header class="ventas-card-header">
                    <h2>Acciones rápidas</h2>
                </header>
                <div class="ventas-quick-actions">
                    <button type="button" @disabled(! $canCreate) @if ($canCreate) data-open-sales-create @endif><i class="bi bi-plus-lg" aria-hidden="true"></i><span>Nueva venta</span><i class="bi bi-chevron-right" aria-hidden="true"></i></button>
                    <button type="button" @disabled(! $canCreate) @if ($canCreate) data-open-sales-create @endif><i class="bi bi-shop" aria-hidden="true"></i><span>Registrar venta física</span><i class="bi bi-chevron-right" aria-hidden="true"></i></button>
                    <a href="{{ route('quotations.index') }}"><i class="bi bi-file-earmark-text" aria-hidden="true"></i><span>Crear cotización</span><i class="bi bi-chevron-right" aria-hidden="true"></i></a>
                    <a href="{{ route('management.orders', ['estado' => 'pendiente']) }}"><i class="bi bi-cart-check" aria-hidden="true"></i><span>Ver pedidos pendientes</span><i class="bi bi-chevron-right" aria-hidden="true"></i></a>
                    <a href="{{ route('management.customers') }}"><i class="bi bi-people" aria-hidden="true"></i><span>Gestionar clientes</span><i class="bi bi-chevron-right" aria-hidden="true"></i></a>
                    <a href="{{ route('management.reports') }}"><i class="bi bi-bar-chart" aria-hidden="true"></i><span>Ver reportes de ventas</span><i class="bi bi-chevron-right" aria-hidden="true"></i></a>
                </div>
            </section>

            <section class="ventas-card ventas-side-list-card">
                <header class="ventas-card-header">
                    <h2>Últimas ventas</h2><a href="{{ route('management.sales') }}">Ver todas</a>
                </header>
                <div class="ventas-recent-list">
                    @forelse ($latestSales as $sale)
                    <div class="ventas-recent-row"><span class="ventas-recent-avatar"><i class="bi bi-person" aria-hidden="true"></i></span><span><strong>{{ $sale->cliente?->razon_social ?: $sale->cliente?->nombre_contacto ?: 'Sin cliente asociado' }}</strong><small><i class="bi bi-circle-fill" aria-hidden="true"></i> {{ $sale->fecha_venta?->format('d/m/Y H:i') }}</small></span><b>{{ $sale->moneda }} {{ number_format((float) $sale->total, 2) }}</b></div>
                    @empty
                    <p class="ventas-side-empty">Aún no hay ventas registradas.</p>
                    @endforelse
                </div>
            </section>

            <section class="ventas-card ventas-side-list-card">
                <header class="ventas-card-header">
                    <h2>Productos más vendidos</h2><a href="{{ route('management.products') }}">Ver todos</a>
                </header>
                <div class="ventas-product-list">
                    @forelse ($monedasMultiples ? [] : $topProducts as $index => $product)
                    <div class="ventas-product-row"><span class="ventas-product-rank">{{ $index + 1 }}</span><span class="ventas-product-image">@if ($product['image'])<img src="{{ $product['image'] }}" alt="">@else<i class="bi bi-box-seam" aria-hidden="true"></i>@endif</span><span><strong>{{ $product['name'] }}</strong><small>{{ rtrim(rtrim(number_format($product['units'], 6, '.', ''), '0'), '.') }} unidades</small></span><b>{{ $product['revenue_label'] }}</b></div>
                    @empty
                    <p class="ventas-side-empty">{{ $monedasMultiples ? 'No se suman importes de monedas diferentes.' : 'Aún no hay productos registrados en ventas.' }}</p>
                    @endforelse
                </div>
            </section>
        </aside>
    </div>

    <dialog class="ventas-dialog" id="nueva-venta" data-sales-auto-open="{{ $errors->any() && old('_method') !== 'PUT' ? '1' : '0' }}" aria-labelledby="nueva-venta-titulo">
        <div class="ventas-dialog-card">
            <header class="ventas-dialog-header">
                <div><span class="ventas-dialog-icon"><i class="bi bi-cart-plus" aria-hidden="true"></i></span><span><small>Ventas</small>
                        <h2 id="nueva-venta-titulo">Nueva venta</h2>
                        <p>Confirma la venta y descuenta existencias. El pago y comprobante se registran por separado.</p>
                    </span></div>
                <button class="ventas-dialog-close" type="button" data-close-sales-dialog aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
            </header>
            @include('gestion.comercial.ventas.registrar', ['customers' => $customers, 'products' => $products])
        </div>
    </dialog>

    @if ($editOrder)
    <dialog class="ventas-dialog" id="editar-venta-{{ $editOrder->id }}" data-sales-auto-open="1" aria-labelledby="editar-venta-titulo-{{ $editOrder->id }}">
        <div class="ventas-dialog-card">
            <header class="ventas-dialog-header">
                <div><span class="ventas-dialog-icon"><i class="bi bi-pencil-square" aria-hidden="true"></i></span><span><small>Ventas</small>
                        <h2 id="editar-venta-titulo-{{ $editOrder->id }}">Editar venta #{{ str_pad((string) $editOrder->id, 6, '0', STR_PAD_LEFT) }}</h2>
                        <p>Actualiza el cliente, la fecha o los productos de esta venta.</p>
                    </span></div>
                <button class="ventas-dialog-close" type="button" data-close-sales-dialog aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
            </header>
            @include('gestion.comercial.ventas.editar', ['order' => $editOrder, 'customers' => $customers, 'products' => $products])
        </div>
    </dialog>
    @endif
</section>
@endsection



