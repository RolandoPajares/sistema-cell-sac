@extends('layouts.administracion')

@section('title', 'Gestión de compras')
@section('body_class', 'modulo-compras')

@section('content')
    @php
        $statusClass = static fn (string $status): string => match ($status) {
            'Pendiente', 'En proceso' => 'pending',
            'Aprobada' => 'approved',
            'Recibida', 'Completada' => 'received',
            'Parcial' => 'partial',
            'Cancelada', 'Cancelado', 'Rechazada' => 'cancelled',
            default => 'muted',
        };
        $receptionLabel = static fn (string $status): string => match ($status) {
            'Recibida', 'Completada' => 'Completa',
            'Parcial' => 'Parcial',
            'Pendiente', 'En proceso', 'Aprobada' => 'Sin recibir',
            default => $status,
        };
    @endphp

    <section class="compras-page">
        <header class="compras-hero">
            <div>
                <nav class="compras-breadcrumb" aria-label="Ruta de navegación">
                    <a href="{{ route('management.dashboard') }}">Administración</a>
                    <span aria-hidden="true">/</span>
                    <span>Operaciones comerciales</span>
                    <span aria-hidden="true">/</span>
                    <span aria-current="page">Compras</span>
                </nav>
                <h1>Gestión de compras</h1>
                <p>Controla las compras a proveedores y las recepciones que actualizan tu inventario.</p>
            </div>
            <div class="compras-hero-actions">
                <button class="compras-secondary-button" type="button" disabled title="La importación de compras aún no está disponible."><i class="bi bi-upload" aria-hidden="true"></i> Importar</button>
                <button class="compras-secondary-button" type="button" disabled title="La exportación de compras aún no está disponible."><i class="bi bi-download" aria-hidden="true"></i> Exportar</button>
                <button class="compras-primary-button" type="button" data-admin-dialog-open="nueva-compra" @disabled($suppliers->isEmpty() || $products->isEmpty())>
                    <i class="bi bi-plus-lg" aria-hidden="true"></i> Nueva compra
                </button>
            </div>
        </header>

        <section class="compras-kpi-grid" aria-label="Resumen de compras">
            <article class="compras-kpi compras-kpi--blue">
                <span class="compras-kpi-icon"><i class="bi bi-receipt" aria-hidden="true"></i></span>
                <div><small>Órdenes de compra</small><strong>{{ number_format($totalPurchases) }}</strong><span>Compras registradas</span></div>
            </article>
            <article class="compras-kpi compras-kpi--orange">
                <span class="compras-kpi-icon"><i class="bi bi-cart" aria-hidden="true"></i></span>
                <div><small>Compras pendientes</small><strong>{{ number_format($pendingPurchases) }}</strong><span>Por recibir</span></div>
            </article>
            <article class="compras-kpi compras-kpi--green">
                <span class="compras-kpi-icon"><i class="bi bi-box-seam" aria-hidden="true"></i></span>
                <div><small>Compras recibidas</small><strong>{{ number_format($receivedPurchases) }}</strong><span>Recepciones completadas</span></div>
            </article>
            <article class="compras-kpi compras-kpi--purple">
                <span class="compras-kpi-icon"><i class="bi bi-cash-stack" aria-hidden="true"></i></span>
                <div><small>Monto comprado este mes</small><strong>S/ {{ number_format($monthlyPurchaseTotal, 2) }}</strong><span>{{ number_format($monthlyPurchaseCount) }} compras del mes</span></div>
            </article>
        </section>

        <form class="compras-filters" method="GET" action="{{ route('management.purchases') }}">
            <label class="compras-search">
                <i class="bi bi-search" aria-hidden="true"></i>
                <input type="search" name="buscar" value="{{ $filters['buscar'] ?? '' }}" placeholder="Buscar orden, proveedor o producto..." aria-label="Buscar orden, proveedor o producto">
            </label>
            <label>
                <select name="proveedor" aria-label="Filtrar por proveedor">
                    <option value="">Proveedor: todos</option>
                    @foreach ($supplierFilters as $supplier)
                        <option value="{{ $supplier->id }}" @selected((string) ($filters['proveedor'] ?? '') === (string) $supplier->id)>{{ $supplier->nombre }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <select name="estado" aria-label="Filtrar por estado">
                    <option value="">Estado: todos</option>
                    @foreach ($statusSummary as $status)
                        <option value="{{ $status['label'] }}" @selected(($filters['estado'] ?? '') === $status['label'])>{{ $status['label'] }}</option>
                    @endforeach
                </select>
            </label>
            <label class="compras-date-filter">
                <input type="date" name="desde" value="{{ $filters['desde'] ?? '' }}" aria-label="Fecha desde">
            </label>
            <label class="compras-date-filter">
                <input type="date" name="hasta" value="{{ $filters['hasta'] ?? '' }}" aria-label="Fecha hasta">
            </label>
            <button class="compras-filter-button" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Filtrar</button>
            <a class="compras-clear-button" href="{{ route('management.purchases') }}">Limpiar</a>
        </form>

        <section class="compras-table-card" aria-labelledby="compras-list-title">
            <header class="compras-table-header">
                <div><h2 id="compras-list-title">Órdenes de compra</h2><span>{{ number_format($purchases->total()) }} registros</span></div>
            </header>
            <div class="compras-table-scroll">
                <table class="compras-table">
                    <thead>
                        <tr>
                            <th scope="col">Orden</th>
                            <th scope="col">Proveedor</th>
                            <th scope="col">Fecha</th>
                            <th scope="col">Productos</th>
                            <th scope="col">Total</th>
                            <th scope="col">Estado</th>
                            <th scope="col">Recepción</th>
                            <th scope="col"><span class="visually-hidden">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($purchases as $purchase)
                            @php
                                $status = (string) $purchase->estado;
                                $supplierName = $purchase->supplier?->nombre ?? 'Proveedor no disponible';
                            @endphp
                            <tr>
                                <td><strong class="compras-code">OC-{{ $purchase->fecha_compra?->format('y') ?? now()->format('y') }}-{{ str_pad((string) $purchase->id, 5, '0', STR_PAD_LEFT) }}</strong></td>
                                <td><span class="compras-supplier">{{ $supplierName }}</span><small>{{ $purchase->supplier?->ruc ?? '—' }}</small></td>
                                <td>{{ $purchase->fecha_compra?->format('d/m/Y') ?? '—' }}</td>
                                <td>{{ $purchase->details_count }} {{ $purchase->details_count === 1 ? 'producto' : 'productos' }}</td>
                                <td><strong>S/ {{ number_format((float) $purchase->total, 2) }}</strong></td>
                                <td><span class="compras-status compras-status--{{ $statusClass($status) }}"><i class="bi bi-circle-fill" aria-hidden="true"></i>{{ $status }}</span></td>
                                <td><span class="compras-reception compras-reception--{{ $statusClass($status) }}"><i class="bi bi-box-seam" aria-hidden="true"></i>{{ $receptionLabel($status) }}</span></td>
                                <td>
                                    <details class="compras-row-details">
                                        <summary aria-label="Ver detalle de orden {{ $purchase->id }}" title="Ver detalle"><i class="bi bi-eye" aria-hidden="true"></i></summary>
                                        <div class="compras-detail-popover">
                                            <strong>Orden de compra</strong>
                                            <span>{{ $supplierName }} · {{ $purchase->fecha_compra?->format('d/m/Y') ?? 'Fecha no disponible' }}</span>
                                            <ul>
                                                @foreach ($purchase->details as $detail)
                                                    <li><span>{{ $detail->product?->nombre ?? 'Producto no disponible' }} × {{ $detail->cantidad }}</span><b>S/ {{ number_format((float) $detail->costo_unitario * (int) $detail->cantidad, 2) }}</b></li>
                                                @endforeach
                                            </ul>
                                            <p>Total <b>S/ {{ number_format((float) $purchase->total, 2) }}</b></p>
                                        </div>
                                    </details>
                                </td>
                            </tr>
                        @empty
                            <tr><td class="compras-empty" colspan="8"><i class="bi bi-inbox" aria-hidden="true"></i><strong>No hay compras para mostrar</strong><span>Cambia los filtros o registra una nueva compra.</span></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <footer class="compras-table-footer">
                <span>
                    @if ($purchases->total() > 0)
                        Mostrando {{ $purchases->firstItem() }}–{{ $purchases->lastItem() }} de {{ $purchases->total() }} compras
                    @else
                        Sin registros
                    @endif
                </span>
                {{ $purchases->links() }}
            </footer>
        </section>

        <div class="compras-bottom-grid">
            <section class="compras-summary-card">
                <header><span class="compras-summary-icon compras-summary-icon--orange"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i></span><div><h2>Seguimiento de compras</h2><p>Órdenes que requieren recepción</p></div></header>
                @forelse ($pendingPurchaseRecords as $pending)
                    <div class="compras-recent-row">
                        <span class="compras-recent-dot compras-recent-dot--orange"></span>
                        <div><strong>OC-{{ $pending->fecha_compra?->format('y') ?? now()->format('y') }}-{{ str_pad((string) $pending->id, 5, '0', STR_PAD_LEFT) }} · {{ $pending->supplier?->nombre ?? 'Proveedor no disponible' }}</strong><small>{{ $pending->details_count }} {{ $pending->details_count === 1 ? 'producto' : 'productos' }} · S/ {{ number_format((float) $pending->total, 2) }}</small></div>
                        <span class="compras-status compras-status--{{ $statusClass((string) $pending->estado) }}">{{ $pending->estado }}</span>
                    </div>
                @empty
                    <p class="compras-summary-empty">No hay órdenes pendientes de recepción.</p>
                @endforelse
            </section>

            <section class="compras-summary-card">
                <header><span class="compras-summary-icon"><i class="bi bi-box-seam" aria-hidden="true"></i></span><div><h2>Productos más comprados</h2><p>Unidades registradas en recepciones</p></div></header>
                @forelse ($mostPurchasedProducts as $product)
                    <div class="compras-product-row"><span class="compras-product-icon"><i class="bi bi-phone" aria-hidden="true"></i></span><strong>{{ $product->nombre }}</strong><span>{{ number_format((int) $product->cantidad) }} unid.</span></div>
                @empty
                    <p class="compras-summary-empty">Los productos comprados aparecerán aquí al registrar una recepción.</p>
                @endforelse
            </section>

            <section class="compras-summary-card">
                <header><span class="compras-summary-icon compras-summary-icon--blue"><i class="bi bi-activity" aria-hidden="true"></i></span><div><h2>Actividad reciente</h2><p>Últimas órdenes registradas</p></div></header>
                @forelse ($recentPurchases as $recent)
                    <div class="compras-recent-row">
                        <span class="compras-recent-dot"></span>
                        <div><strong>OC-{{ $recent->fecha_compra?->format('y') ?? now()->format('y') }}-{{ str_pad((string) $recent->id, 5, '0', STR_PAD_LEFT) }} · {{ $recent->supplier?->nombre ?? 'Proveedor no disponible' }}</strong><small>{{ $recent->fecha_compra?->format('d/m/Y') ?? 'Fecha no disponible' }} · S/ {{ number_format((float) $recent->total, 2) }}</small></div>
                        <span class="compras-status compras-status--{{ $statusClass((string) $recent->estado) }}">{{ $recent->estado }}</span>
                    </div>
                @empty
                    <p class="compras-summary-empty">Las compras recientes aparecerán aquí.</p>
                @endforelse
            </section>
        </div>

        <dialog class="admin-dialog compras-create-dialog" id="nueva-compra" aria-labelledby="nueva-compra-title">
            <header class="admin-dialog-header">
                <div><h2 id="nueva-compra-title">Registrar compra</h2><p>Registra la compra recibida y actualiza el inventario.</p></div>
                <button class="admin-dialog-close" type="button" data-admin-dialog-close aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
            </header>
            <div class="admin-dialog-body">
                @if ($suppliers->isNotEmpty() && $products->isNotEmpty())
                    @include('gestion.compras.crear', ['suppliers' => $suppliers, 'products' => $products])
                @else
                    <p class="compras-summary-empty">Para registrar una compra, agrega al menos un proveedor activo y un producto disponible.</p>
                @endif
            </div>
        </dialog>
    </section>
@endsection
