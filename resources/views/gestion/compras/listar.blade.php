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
                <button class="compras-secondary-button" type="button" data-admin-dialog-open="importar-compras"><i class="bi bi-upload" aria-hidden="true"></i> Importar</button>
                <details class="compras-export-menu"><summary class="compras-secondary-button"><i class="bi bi-download" aria-hidden="true"></i> Exportar <i class="bi bi-chevron-down" aria-hidden="true"></i></summary><div class="compras-export-options"><a href="{{ route('management.purchases.export', array_merge(request()->query(), ['format' => 'excel'])) }}"><i class="bi bi-file-earmark-spreadsheet" aria-hidden="true"></i> Excel (.xlsx)</a><a href="{{ route('management.purchases.export', array_merge(request()->query(), ['format' => 'pdf'])) }}"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i> PDF (.pdf)</a><a href="{{ route('management.purchases.export', array_merge(request()->query(), ['format' => 'csv'])) }}"><i class="bi bi-filetype-csv" aria-hidden="true"></i> CSV (.csv)</a></div></details>
                <button class="compras-primary-button" type="button" data-admin-dialog-open="nueva-compra" @disabled(! $canCreate || $suppliers->isEmpty() || $products->isEmpty())>
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
                                $estadoRecepcion = $purchase->estado === 'Aprobada' && (int) $purchase->cantidad_recibida_total > 0 ? 'Parcial' : $status;
                                $supplierName = $purchase->supplier?->nombre ?? 'Proveedor no disponible';
                            @endphp
                            <tr>
                                <td><strong class="compras-code">OC-{{ $purchase->fecha_compra?->format('y') ?? now()->format('y') }}-{{ str_pad((string) $purchase->id, 5, '0', STR_PAD_LEFT) }}</strong></td>
                                <td><span class="compras-supplier">{{ $supplierName }}</span><small>{{ $purchase->supplier?->ruc ?? '—' }}</small></td>
                                <td>{{ $purchase->fecha_compra?->format('d/m/Y') ?? '—' }}</td>
                                <td>{{ $purchase->details_count }} {{ $purchase->details_count === 1 ? 'producto' : 'productos' }}</td>
                                <td><strong>S/ {{ number_format((float) $purchase->total, 2) }}</strong></td>
                                <td><span class="compras-status compras-status--{{ $statusClass($status) }}"><i class="bi bi-circle-fill" aria-hidden="true"></i>{{ $status }}</span></td>
                                <td><span class="compras-reception compras-reception--{{ $statusClass($estadoRecepcion) }}"><i class="bi bi-box-seam" aria-hidden="true"></i>{{ $receptionLabel($estadoRecepcion) }}</span></td>
                                <td>
                                    <details class="compras-row-details" id="detalle-compra-{{ $purchase->id }}">
                                        <summary aria-label="Ver detalle de orden {{ $purchase->id }}" title="Ver detalle"><i class="bi bi-eye" aria-hidden="true"></i></summary>
                                        <div class="compras-detail-popover">
                                            <strong>Orden de compra</strong>
                                            <span>{{ $supplierName }} · {{ $purchase->fecha_compra?->format('d/m/Y') ?? 'Fecha no disponible' }}</span>
                                            <ul>
                                                @foreach ($purchase->details as $detail)
                                                    <li><span>{{ $detail->product?->nombre ?? 'Producto no disponible' }} × {{ $detail->cantidad }}<small>Recibidas: {{ $detail->cantidad_recibida }} · Pendientes: {{ $detail->cantidad_pendiente }}</small></span><b>S/ {{ number_format((float) $detail->costo_unitario * (int) $detail->cantidad, 2) }}</b></li>
                                                @endforeach
                                            </ul>
                                            <p>Total <b>S/ {{ number_format((float) $purchase->total, 2) }}</b></p>
                                            @if ($purchase->estado === 'Aprobada' && in_array($purchase->id, $recibibles, true) && isset($tokensRecepcion[$purchase->id]) && $purchase->details->contains(fn ($detail) => $detail->cantidad_pendiente > 0))
                                                <details class="compras-partial-reception" id="recepcion-parcial-{{ $purchase->id }}">
                                                    <summary>Registrar recepción parcial</summary>
                                                    <form method="post" action="{{ route('management.purchases.receive_partial', $purchase) }}">
                                                        @csrf
                                                        <input type="hidden" name="token_recepcion" value="{{ $tokensRecepcion[$purchase->id] }}">
                                                        @foreach ($purchase->details as $indice => $detail)
                                                            @if ($detail->cantidad_pendiente > 0)
                                                                <label>
                                                                    {{ $detail->product?->nombre ?? 'Producto no disponible' }} · pendiente {{ $detail->cantidad_pendiente }}
                                                                    <input type="hidden" name="items[{{ $indice }}][detalle_compra_id]" value="{{ $detail->id }}">
                                                                    <input type="number" name="items[{{ $indice }}][cantidad]" min="0" max="{{ $detail->cantidad_pendiente }}" value="0" required>
                                                                    @if ($detail->variante?->controla_serie)
                                                                        <textarea name="items[{{ $indice }}][identificadores]" maxlength="30000" rows="3" placeholder="Un equipo por línea: IMEI:15dígitos, SERIE:valor o IMEI:15dígitos|SERIE:valor"></textarea>
                                                                    @endif
                                                                </label>
                                                            @endif
                                                        @endforeach
                                                        <label>Observación de recepción <textarea name="observaciones" maxlength="500" rows="2" placeholder="Obligatoria si quedan unidades pendientes"></textarea></label>
                                                        <button class="compras-reception-open" type="submit">Guardar cantidades recibidas</button>
                                                    </form>
                                                </details>
                                            @endif
                                        </div>
                                    </details>
                                    @if ($purchase->estado === 'Pendiente' && in_array($purchase->id, $aprobables))
                                        <form method="post" action="{{ route('management.purchases.approve', $purchase) }}">@csrf <button class="compras-reception-open" type="submit">Aprobar</button></form>
                                        @if (in_array($purchase->id, $anulables, true))<form method="post" action="{{ route('management.purchases.cancel', $purchase) }}" onsubmit="return confirm('¿Anular esta compra pendiente? Se conservará la auditoría.')">@csrf <button class="compras-reception-open" type="submit">Anular</button></form>@endif
                                    @elseif ($purchase->estado === 'Aprobada' && in_array($purchase->id, $recibibles, true) && $purchase->details->contains(fn ($detail) => $detail->cantidad_pendiente > 0 && $detail->variante?->controla_serie))
                                        <button class="compras-reception-open" type="button" onclick="document.getElementById('detalle-compra-{{ $purchase->id }}').open = true; document.getElementById('recepcion-parcial-{{ $purchase->id }}').open = true;">Recibir</button>
                                    @elseif ($purchase->estado === 'Aprobada' && in_array($purchase->id, $recibibles, true))
                                        <form method="post" action="{{ route('management.purchases.receive', $purchase) }}" onsubmit="return confirm('¿Confirmas la recepción completa de esta compra?')">@csrf <button class="compras-reception-open" type="submit">Recibir</button></form>
                                    @endif
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
                    @php
                        $pendingReference = 'OC-' . ($pending->fecha_compra?->format('y') ?? now()->format('y')) . '-' . str_pad((string) $pending->id, 5, '0', STR_PAD_LEFT);
                        $pendingSupplierName = $pending->supplier?->nombre ?? 'Proveedor no disponible';
                    @endphp
                    <div class="compras-recent-row">
                        <span class="compras-recent-dot compras-recent-dot--orange"></span>
                        <div><strong>{{ $pendingReference }} · {{ $pendingSupplierName }}</strong><small>{{ $pending->details_count }} {{ $pending->details_count === 1 ? 'producto' : 'productos' }} · S/ {{ number_format((float) $pending->total, 2) }}</small></div>
                        <span class="compras-status compras-status--{{ $statusClass((string) $pending->estado) }}">{{ $pending->estado }}</span>
                        @if ($pending->estado === 'Pendiente' && in_array($pending->id, $aprobables))
                            <form method="post" action="{{ route('management.purchases.approve', $pending) }}">@csrf <button class="compras-reception-open" type="submit">Aprobar</button></form>
                            @if (in_array($pending->id, $anulables, true))<form method="post" action="{{ route('management.purchases.cancel', $pending) }}" onsubmit="return confirm('¿Anular esta compra pendiente? Se conservará la auditoría.')">@csrf <button class="compras-reception-open" type="submit">Anular</button></form>@endif
                        @elseif ($pending->estado === 'Aprobada' && in_array($pending->id, $recibibles))
                            <form method="post" action="{{ route('management.purchases.receive', $pending) }}" onsubmit="return confirm('¿Confirmas la recepción completa de esta compra?')">@csrf <button class="compras-reception-open" type="submit">Recibir mercadería</button></form>
                        @endif
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
                <div><h2 id="nueva-compra-title">Registrar compra</h2><p>La compra quedará pendiente. La aprobación y recepción son pasos separados.</p></div>
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

        <dialog class="admin-dialog compras-create-dialog" id="importar-compras" aria-labelledby="importar-compras-title">
            <header class="admin-dialog-header"><div><h2 id="importar-compras-title">Importar compras</h2><p>Importa líneas reales con validación y transacción.</p></div><button class="admin-dialog-close" type="button" data-admin-dialog-close aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header>
            <div class="admin-dialog-body"><form method="post" action="{{ route('management.purchases.import') }}" enctype="multipart/form-data" class="form-card">@csrf<p>Columnas exactas: <code>proveedor_id,fecha_compra,producto_id,variante_id,cantidad,costo_unitario</code>.</p><label>Archivo CSV<input type="file" name="archivo" accept=".csv,text/csv" required></label><footer class="compras-form-actions"><button type="button" class="compras-secondary-button" data-admin-dialog-close>Cancelar</button><button type="submit" class="compras-primary-button"><i class="bi bi-upload" aria-hidden="true"></i> Importar</button></footer></form></div>
        </dialog>

    </section>
@endsection
