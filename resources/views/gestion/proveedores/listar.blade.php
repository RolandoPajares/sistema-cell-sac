@extends('layouts.administracion')

@section('title', 'Proveedores')
@section('body_class', 'modulo-proveedores')

@section('content')
    @php
        $supplierCategories = static function ($supplier) {
            return $supplier->products
                ->map(static fn ($product) => $product->category?->nombre)
                ->filter()
                ->unique()
                ->values();
        };
        $selectedCategories = $selectedSupplier ? $supplierCategories($selectedSupplier) : collect();
        $purchaseCount = $selectedSupplier?->purchases_count ?? 0;
        $receivedPurchases = (int) ($purchaseStatusCounts->firstWhere('estado', 'Recibida')?->total ?? 0);
        $receivedPercent = $purchaseCount > 0 ? min(100, round(($receivedPurchases / $purchaseCount) * 100)) : 0;
    @endphp

    <section class="proveedores-page" data-proveedores-page>
        <header class="proveedores-hero">
            <div>
                <span class="proveedores-eyebrow">Compras y logística</span>
                <h1>Proveedores <i class="bi bi-truck" aria-hidden="true"></i></h1>
                <p>Administra los proveedores vinculados al catálogo y a las compras.</p>
            </div>
        </header>

        <section class="proveedores-kpi-grid" aria-label="Resumen de proveedores">
            <article class="proveedores-kpi proveedores-kpi--blue">
                <span class="proveedores-kpi-icon"><i class="bi bi-people-fill" aria-hidden="true"></i></span>
                <div><small>Total de proveedores</small><strong>{{ number_format($totalSuppliers) }}</strong><span>Registrados en el sistema</span></div>
            </article>
            <article class="proveedores-kpi proveedores-kpi--green">
                <span class="proveedores-kpi-icon"><i class="bi bi-check-circle-fill" aria-hidden="true"></i></span>
                <div><small>Proveedores activos</small><strong>{{ number_format($activeSuppliers) }}</strong><span>{{ $totalSuppliers > 0 ? number_format(($activeSuppliers / $totalSuppliers) * 100, 0) : 0 }}% del total</span></div>
            </article>
            <article class="proveedores-kpi proveedores-kpi--purple">
                <span class="proveedores-kpi-icon"><i class="bi bi-cart-check-fill" aria-hidden="true"></i></span>
                <div><small>Órdenes vinculadas</small><strong>{{ number_format($linkedOrders) }}</strong><span>Compras registradas</span></div>
            </article>
            <article class="proveedores-kpi proveedores-kpi--orange">
                <span class="proveedores-kpi-icon"><i class="bi bi-cash-stack" aria-hidden="true"></i></span>
                <div><small>Total comprado</small><strong>S/ {{ number_format($purchasedTotal, 2) }}</strong><span>En órdenes activas</span></div>
            </article>
        </section>

        <nav class="proveedores-tabs" aria-label="Secciones de proveedores">
            <span class="proveedores-tab proveedores-tab--active">Lista de proveedores</span>
            <span class="proveedores-tab">Análisis y métricas</span>
        </nav>

        <form class="proveedores-toolbar" method="get" action="{{ route('management.suppliers') }}">
            <label class="proveedores-search">
                <i class="bi bi-search" aria-hidden="true"></i>
                <span class="visually-hidden">Buscar proveedores</span>
                <input type="search" name="buscar" value="{{ $filters['buscar'] ?? '' }}" placeholder="Buscar por nombre, RUC, categoría o ciudad...">
            </label>
            <label>
                <span class="visually-hidden">Filtrar por categoría</span>
                <select name="categoria">
                    <option value="">Todas las categorías</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(($filters['categoria'] ?? null) === $category->id)>{{ $category->nombre }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span class="visually-hidden">Filtrar por estado</span>
                <select name="estado">
                    <option value="">Todos los estados</option>
                    <option value="activo" @selected(($filters['estado'] ?? null) === 'activo')>Activos</option>
                    <option value="inactivo" @selected(($filters['estado'] ?? null) === 'inactivo')>Inactivos</option>
                </select>
            </label>
            <label>
                <span class="visually-hidden">Filtrar por ciudad</span>
                <select name="ciudad">
                    <option value="">Todas las ciudades</option>
                    @foreach ($cities as $city)
                        <option value="{{ $city }}" @selected(($filters['ciudad'] ?? null) === $city)>{{ $city }}</option>
                    @endforeach
                </select>
            </label>
            <button class="proveedores-toolbar-button" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i><span>Filtros</span></button>
            <button class="proveedores-primary-button" type="button" data-open-supplier-create><i class="bi bi-plus-lg" aria-hidden="true"></i> Nuevo proveedor</button>
            <button class="proveedores-toolbar-button" type="button" disabled title="Importación disponible próximamente"><i class="bi bi-upload" aria-hidden="true"></i> Importar</button>
            <a class="proveedores-toolbar-button" href="{{ route('management.suppliers') }}"><i class="bi bi-download" aria-hidden="true"></i> Exportar</a>
            @if (($filters['buscar'] ?? null) || ($filters['categoria'] ?? null) || ($filters['estado'] ?? null) || ($filters['ciudad'] ?? null))
                <a class="proveedores-clear-button" href="{{ route('management.suppliers') }}">Limpiar filtros</a>
            @endif
        </form>

        <div class="proveedores-main-grid">
            <section class="proveedores-list-card">
                <header class="proveedores-card-header">
                    <div>
                        <h2>Proveedores ({{ number_format($suppliers->total()) }}) <i class="bi bi-chevron-down" aria-hidden="true"></i></h2>
                        <p>Mostrando {{ $suppliers->firstItem() ?? 0 }} a {{ $suppliers->lastItem() ?? 0 }} proveedores</p>
                    </div>
                </header>

                <div class="proveedores-table-wrap">
                    <table class="proveedores-table">
                        <thead>
                            <tr><th><span class="proveedores-checkbox" aria-hidden="true"></span></th><th>#</th><th>Proveedor</th><th>RUC</th><th>Categoría</th><th>Ciudad</th><th>Estado</th><th>Acciones</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($suppliers as $supplier)
                                @php
                                    $categoriesForSupplier = $supplierCategories($supplier);
                                    $supplierInitial = mb_strtoupper(mb_substr($supplier->nombre, 0, 1));
                                    $isSelected = $selectedSupplier?->id === $supplier->id;
                                @endphp
                                <tr class="{{ $isSelected ? 'is-selected' : '' }}">
                                    <td><span class="proveedores-checkbox" aria-hidden="true"></span></td>
                                    <td class="proveedores-index">{{ $suppliers->firstItem() + $loop->index }}</td>
                                    <td>
                                        <a class="proveedores-identity" href="{{ request()->fullUrlWithQuery(['proveedor' => $supplier->id]) }}">
                                            <span class="proveedores-avatar">{{ $supplierInitial }}</span>
                                            <span><strong>{{ $supplier->nombre }}</strong><small>{{ $supplier->correo }}</small></span>
                                        </a>
                                    </td>
                                    <td class="proveedores-muted">{{ $supplier->ruc }}</td>
                                    <td>
                                        <span class="proveedores-category-list">
                                            @forelse ($categoriesForSupplier->take(2) as $categoryName)
                                                <b>{{ $categoryName }}</b>
                                            @empty
                                                <span>Sin categoría</span>
                                            @endforelse
                                        </span>
                                    </td>
                                    <td class="proveedores-muted">{{ $supplier->ciudad ?: 'Sin ciudad' }}</td>
                                    <td><span class="proveedores-status proveedores-status--{{ $supplier->activo ? 'active' : 'inactive' }}"><i class="bi bi-circle-fill" aria-hidden="true"></i>{{ $supplier->activo ? 'Activo' : 'Inactivo' }}</span></td>
                                    <td class="proveedores-actions-cell">
                                        <a class="proveedores-action" href="{{ request()->fullUrlWithQuery(['proveedor' => $supplier->id]) }}" title="Ver proveedor"><i class="bi bi-eye" aria-hidden="true"></i></a>
                                        <button class="proveedores-action" type="button" data-open-supplier-edit="{{ $supplier->id }}" title="Editar proveedor"><i class="bi bi-pencil" aria-hidden="true"></i></button>
                                        @if ($supplier->activo)
                                            <form method="post" action="{{ route('management.suppliers.deactivate', $supplier) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button class="proveedores-action proveedores-action--danger" type="submit" data-confirm="¿Desactivar este proveedor?" title="Desactivar proveedor"><i class="bi bi-trash3" aria-hidden="true"></i></button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="proveedores-empty"><i class="bi bi-truck" aria-hidden="true"></i><strong>No hay proveedores para mostrar.</strong><span>Prueba con otros filtros o registra un nuevo proveedor.</span></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <footer class="proveedores-pagination">
                    <span>Mostrando {{ $suppliers->firstItem() ?? 0 }} a {{ $suppliers->lastItem() ?? 0 }} de {{ number_format($suppliers->total()) }} proveedores</span>
                    <div class="proveedores-pagination-controls">
                        {{ $suppliers->onEachSide(1)->links() }}
                        <form method="get" action="{{ route('management.suppliers') }}" class="proveedores-per-page">
                            @foreach (request()->except(['page', 'por_pagina']) as $key => $value)
                                @if (is_scalar($value))<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif
                            @endforeach
                            <label for="proveedores-por-pagina">Proveedores por página</label>
                            <select id="proveedores-por-pagina" name="por_pagina" onchange="this.form.submit()">
                                @foreach ([10, 20, 50] as $option)
                                    <option value="{{ $option }}" @selected(($filters['por_pagina'] ?? 10) === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </footer>
            </section>

            <aside class="proveedores-detail-column">
                <section class="proveedores-detail-card">
                    <header class="proveedores-card-header">
                        <h2>Detalle del proveedor <i class="bi bi-chevron-up" aria-hidden="true"></i></h2>
                    </header>
                    @if ($selectedSupplier)
                        @php($selectedInitial = mb_strtoupper(mb_substr($selectedSupplier->nombre, 0, 1)))
                        <div class="proveedores-detail-identity">
                            <span class="proveedores-detail-avatar">{{ $selectedInitial }}</span>
                            <div><strong>{{ $selectedSupplier->nombre }}</strong><span>RUC: {{ $selectedSupplier->ruc }}</span><small>Proveedor vinculado al catálogo.</small></div>
                            <span class="proveedores-status proveedores-status--{{ $selectedSupplier->activo ? 'active' : 'inactive' }}">{{ $selectedSupplier->activo ? 'Activo' : 'Inactivo' }}</span>
                        </div>
                        <div class="proveedores-detail-actions">
                            <button class="proveedores-toolbar-button" type="button" data-open-supplier-edit="{{ $selectedSupplier->id }}"><i class="bi bi-pencil" aria-hidden="true"></i> Editar</button>
                            <a class="proveedores-more-button" href="#proveedores-info" aria-label="Ver información"><i class="bi bi-three-dots-vertical" aria-hidden="true"></i></a>
                        </div>
                        <nav class="proveedores-detail-tabs" aria-label="Detalle del proveedor"><span class="is-active">Información</span><span>Estadísticas</span><span>Órdenes</span><span>Productos</span></nav>
                        <div class="proveedores-detail-section" id="proveedores-info">
                            <h3>Información general</h3>
                            <dl class="proveedores-info-list">
                                <div><dt><i class="bi bi-person" aria-hidden="true"></i> Contacto</dt><dd>Área de compras</dd></div>
                                <div><dt><i class="bi bi-envelope" aria-hidden="true"></i> Correo</dt><dd>{{ $selectedSupplier->correo }}</dd></div>
                                <div><dt><i class="bi bi-telephone" aria-hidden="true"></i> Teléfono</dt><dd>{{ $selectedSupplier->telefono ?: 'No registrado' }}</dd></div>
                                <div><dt><i class="bi bi-geo-alt" aria-hidden="true"></i> Ciudad</dt><dd>{{ $selectedSupplier->ciudad ?: 'No registrada' }}</dd></div>
                                <div><dt><i class="bi bi-box-seam" aria-hidden="true"></i> Categorías</dt><dd class="proveedores-detail-tags">@forelse ($selectedCategories->take(3) as $categoryName)<span>{{ $categoryName }}</span>@empty<span>Sin categorías</span>@endforelse</dd></div>
                            </dl>
                        </div>
                        <div class="proveedores-performance">
                            <header><h3>Rendimiento de compras</h3><small>Datos registrados</small></header>
                            <div class="proveedores-performance-grid">
                                <div class="proveedores-progress-ring" style="--proveedores-progress: {{ $receivedPercent }}%"><strong>{{ $receivedPercent }}%</strong><span>Recibidas</span></div>
                                <div class="proveedores-performance-stats">
                                    <div><span><i class="bi bi-file-earmark-check-fill is-blue" aria-hidden="true"></i> Órdenes totales</span><b>{{ number_format($purchaseCount) }}</b></div>
                                    <div><span><i class="bi bi-cash-coin is-green" aria-hidden="true"></i> Total comprado</span><b>S/ {{ number_format((float) ($selectedSupplier->purchases_total ?? 0), 2) }}</b></div>
                                    <div><span><i class="bi bi-calendar3 is-purple" aria-hidden="true"></i> Última orden</span><b>{{ $selectedSupplier->latest_purchase_date ? \Illuminate\Support\Carbon::parse($selectedSupplier->latest_purchase_date)->format('d/m/Y') : 'Sin compras' }}</b></div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="proveedores-detail-empty"><i class="bi bi-truck" aria-hidden="true"></i><strong>Selecciona un proveedor</strong><span>Su información aparecerá aquí.</span></div>
                    @endif
                </section>
            </aside>
        </div>

        <div class="proveedores-bottom-grid">
            <section class="proveedores-secondary-card">
                <header class="proveedores-card-header"><div><h2><i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i> Alertas de abastecimiento <span class="proveedores-alert-count">{{ $lowStockProducts->count() }}</span></h2><p>Productos con existencias por debajo del mínimo.</p></div><a href="{{ route('management.inventory', ['estado' => 'critico']) }}">Ver todas</a></header>
                <div class="proveedores-alert-list">
                    @forelse ($lowStockProducts as $product)
                        <div class="proveedores-alert-row"><span class="proveedores-alert-icon"><i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i></span><span><strong>Stock bajo en {{ $product->nombre }}</strong><small>{{ $product->existencias }} unidades · {{ $product->suppliers->first()?->nombre ?: 'Sin proveedor' }}</small></span><time>{{ $product->existencias <= 5 ? 'Crítico' : 'Bajo' }}</time><a href="{{ route('management.inventory', ['buscar' => $product->nombre]) }}">Revisar</a></div>
                    @empty
                        <div class="proveedores-secondary-empty"><i class="bi bi-shield-check" aria-hidden="true"></i><strong>Sin alertas de abastecimiento</strong><span>El inventario de proveedores está al día.</span></div>
                    @endforelse
                </div>
            </section>

            <section class="proveedores-secondary-card">
                <header class="proveedores-card-header"><div><h2><i class="bi bi-clock-history" aria-hidden="true"></i> Actividad reciente</h2><p>Últimos movimientos de proveedores y compras.</p></div><a href="{{ route('management.audit') }}">Ver todas</a></header>
                <div class="proveedores-activity-list">
                    @forelse ($recentActivity as $activity)
                        <div class="proveedores-activity-row"><span class="proveedores-activity-icon proveedores-activity-icon--{{ $activity['color'] }}"><i class="bi bi-{{ $activity['icon'] }}" aria-hidden="true"></i></span><span><strong>{{ $activity['title'] }}</strong><small>{{ $activity['detail'] }}</small></span><time>{{ $activity['date']?->diffForHumans() }}</time></div>
                    @empty
                        <div class="proveedores-secondary-empty"><i class="bi bi-clock-history" aria-hidden="true"></i><strong>Sin actividad reciente</strong><span>Los movimientos aparecerán aquí.</span></div>
                    @endforelse
                </div>
            </section>
        </div>
    </section>

    <dialog class="proveedores-dialog" id="nuevo-proveedor">
        <div class="proveedores-dialog-card">
            <header class="proveedores-dialog-header"><div><span>Compras y logística</span><h2>Registrar proveedor</h2><p>Completa los datos del proveedor para vincularlo a tus compras.</p></div><button type="button" class="proveedores-dialog-close" data-close-supplier-dialog aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header>
            <div class="proveedores-dialog-body">@include('gestion.proveedores.crear')</div>
        </div>
    </dialog>

    @foreach ($suppliers as $supplier)
        <dialog class="proveedores-dialog" id="editar-proveedor-{{ $supplier->id }}">
            <div class="proveedores-dialog-card">
                <header class="proveedores-dialog-header"><div><span>Compras y logística</span><h2>Editar proveedor</h2><p>Actualiza la información de {{ $supplier->nombre }}.</p></div><button type="button" class="proveedores-dialog-close" data-close-supplier-dialog aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header>
                <div class="proveedores-dialog-body">@include('gestion.proveedores.editar', ['supplier' => $supplier])</div>
            </div>
        </dialog>
    @endforeach
@endsection

@push('scripts')
    <script>
        (() => {
            const page = document.querySelector('[data-proveedores-page]');
            if (!page) {
                return;
            }

            const openDialog = (dialog) => {
                if (dialog && !dialog.open) {
                    dialog.showModal();
                }
            };

            page.querySelector('[data-open-supplier-create]')?.addEventListener('click', () => openDialog(document.querySelector('#nuevo-proveedor')));
            page.querySelectorAll('[data-open-supplier-edit]').forEach((button) => {
                button.addEventListener('click', () => openDialog(document.querySelector(`#editar-proveedor-${button.dataset.openSupplierEdit}`)));
            });
            document.querySelectorAll('[data-close-supplier-dialog]').forEach((button) => button.addEventListener('click', () => button.closest('dialog')?.close()));
            document.querySelectorAll('.proveedores-dialog').forEach((dialog) => dialog.addEventListener('click', (event) => {
                if (event.target === dialog) {
                    dialog.close();
                }
            }));

            @if ($errors->any())
                openDialog(document.querySelector('#nuevo-proveedor'));
            @endif
        })();
    </script>
@endpush
