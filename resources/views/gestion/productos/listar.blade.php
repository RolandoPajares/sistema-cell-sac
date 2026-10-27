@extends('layouts.administracion')

@section('title', 'Gestión de productos')
@section('body_class', 'modulo-productos')

@php
    $exportParameters = request()->query();
    $productImage = static fn (?string $path): ?string => $path
        ? (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://']) ? $path : asset($path))
        : null;
@endphp

@section('content')
    <section class="productos-page" data-product-page>
        <header class="productos-hero">
            <div>
                <span class="productos-eyebrow">Administración</span>
                <h1>Gestión de productos <span aria-hidden="true">📦</span></h1>
                <p>Administra el catálogo, stock, precios y estado de tus productos.</p>
            </div>
            <div class="productos-actions">
                <button class="productos-primary-button" type="button" data-open-product-create>
                    <i class="bi bi-plus-lg" aria-hidden="true"></i>
                    Registrar nuevo producto
                </button>
                <details class="productos-export-menu">
                    <summary><i class="bi bi-upload" aria-hidden="true"></i><span>Exportar</span><i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
                    <div class="productos-export-options">
                        <a href="{{ route('management.products.export', array_merge(['format' => 'pdf'], $exportParameters)) }}"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i><span>Exportar PDF<small>Documento imprimible</small></span></a>
                        <a href="{{ route('management.products.export', array_merge(['format' => 'excel'], $exportParameters)) }}"><i class="bi bi-file-earmark-spreadsheet" aria-hidden="true"></i><span>Exportar Excel<small>Archivo compatible .xls</small></span></a>
                        <a href="{{ route('management.products.export', array_merge(['format' => 'csv'], $exportParameters)) }}"><i class="bi bi-filetype-csv" aria-hidden="true"></i><span>Exportar CSV<small>Datos delimitados</small></span></a>
                    </div>
                </details>
            </div>
        </header>

        <div class="productos-kpi-grid">
            <article class="productos-kpi productos-kpi--green">
                <span class="productos-kpi-icon"><i class="bi bi-box-seam" aria-hidden="true"></i></span>
                <div><small>Productos activos</small><strong>{{ number_format($activeProducts) }}</strong>@if ($productsTrendPercent !== null)<span class="productos-kpi-trend {{ $productsTrendPercent >= 0 ? 'is-positive' : 'is-negative' }}"><i class="bi bi-arrow-{{ $productsTrendPercent >= 0 ? 'up' : 'down' }}" aria-hidden="true"></i> {{ number_format(abs($productsTrendPercent), 1) }}% <em>vs. mes anterior</em></span>@else<span class="productos-kpi-trend">Sin periodo anterior</span>@endif</div>
            </article>
            <article class="productos-kpi productos-kpi--red">
                <span class="productos-kpi-icon"><i class="bi bi-box" aria-hidden="true"></i></span>
                <div><small>Sin stock</small><strong>{{ number_format($outOfStock) }}</strong><span class="productos-kpi-trend {{ $outOfStock > 0 ? 'is-negative' : 'is-positive' }}"><i class="bi bi-{{ $outOfStock > 0 ? 'arrow-up' : 'check-circle' }}" aria-hidden="true"></i> {{ $outOfStock > 0 ? 'Reponer inventario' : 'Inventario saludable' }}</span></div>
            </article>
            <article class="productos-kpi productos-kpi--purple">
                <span class="productos-kpi-icon"><i class="bi bi-diagram-3" aria-hidden="true"></i></span>
                <div><small>Categorías</small><strong>{{ number_format($categoryCount) }}</strong><span class="productos-kpi-trend">Sin cambios</span></div>
            </article>
            <article class="productos-kpi productos-kpi--blue">
                <span class="productos-kpi-icon"><i class="bi bi-coin" aria-hidden="true"></i></span>
                <div><small>Valor inventario</small><strong>S/ {{ number_format($inventoryValue, 2) }}</strong><span class="productos-kpi-trend is-positive"><i class="bi bi-arrow-up" aria-hidden="true"></i> Valor actual</span></div>
            </article>
        </div>

        <form class="productos-filters" method="get" action="{{ route('management.products') }}">
            <label class="productos-search"><i class="bi bi-search" aria-hidden="true"></i><span class="visually-hidden">Buscar producto</span><input type="search" name="buscar" value="{{ $filters['buscar'] ?? '' }}" placeholder="Buscar producto, SKU o marca..."></label>
            <label><span class="visually-hidden">Filtrar por categoría</span><select name="categoria"><option value="">Todas las categorías</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected(($filters['categoria'] ?? null) === $category->id)>{{ $category->nombre }}</option>@endforeach</select></label>
            <label><span class="visually-hidden">Filtrar por stock</span><select name="stock"><option value="">Todo el stock</option><option value="disponible" @selected(($filters['stock'] ?? null) === 'disponible')>Stock disponible</option><option value="bajo" @selected(($filters['stock'] ?? null) === 'bajo')>Stock bajo</option><option value="sin_stock" @selected(($filters['stock'] ?? null) === 'sin_stock')>Sin stock</option></select></label>
            <label><span class="visually-hidden">Filtrar por estado</span><select name="estado"><option value="">Todos los estados</option><option value="activo" @selected(($filters['estado'] ?? null) === 'activo')>Activos</option><option value="inactivo" @selected(($filters['estado'] ?? null) === 'inactivo')>Inactivos</option></select></label>
            <button class="productos-filter-reset" type="submit"><i class="bi bi-arrow-repeat" aria-hidden="true"></i> Aplicar filtros</button>
            @if (array_filter($filters))
                <a class="productos-filter-clear" href="{{ route('management.products') }}">Limpiar filtros</a>
            @endif
        </form>

        <div class="productos-layout">
            <section class="productos-list-card">
                <header class="productos-card-header">
                    <div class="productos-card-title"><i class="bi bi-box-seam" aria-hidden="true"></i><div><h2>Listado de productos</h2><p>Mostrando {{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }} de {{ $products->total() }} productos</p></div></div>
                    <label class="productos-sort"><span>Ordenar por:</span><select aria-label="Orden de productos"><option>Más recientes</option></select></label>
                </header>
                <div class="productos-table-wrap">
                    <table class="productos-table">
                        <thead><tr><th><span class="productos-checkbox" aria-hidden="true"></span></th><th>Imagen</th><th>Producto</th><th>SKU</th><th>Categoría</th><th>Precio</th><th>Stock</th><th>Estado</th><th>Acciones</th></tr></thead>
                        <tbody>
                            @forelse ($products as $product)
                                @php
                                    $image = $productImage($product->url_imagen);
                                    $productStatus = !$product->activo ? 'Inactivo' : ($product->existencias === 0 ? 'Sin stock' : ($product->precio_oferta ? 'Oferta' : 'Activo'));
                                    $statusClass = match ($productStatus) { 'Sin stock' => 'danger', 'Oferta' => 'warning', 'Inactivo' => 'muted', default => 'success' };
                                    $stockClass = $product->existencias === 0 || $product->existencias <= 5 ? 'danger' : 'success';
                                @endphp
                                <tr>
                                    <td><span class="productos-checkbox" aria-hidden="true"></span></td>
                                    <td>
                                        @if ($image)<img class="productos-image" src="{{ $image }}" alt="{{ $product->nombre }}">@else<span class="productos-image productos-image--placeholder"><i class="bi bi-phone" aria-hidden="true"></i></span>@endif
                                    </td>
                                    <td class="productos-name"><strong>{{ $product->nombre }}</strong><small>{{ $product->marca }} {{ $product->nombre }} · {{ $product->categoria ?: 'Sin categoría' }}</small></td>
                                    <td class="productos-sku">{{ $product->etiqueta ?: 'SKU-'.str_pad((string) $product->id, 4, '0', STR_PAD_LEFT) }}</td>
                                    <td><span class="productos-category">{{ $product->category?->nombre ?: 'Sin categoría' }}</span></td>
                                    <td><strong class="productos-price">S/ {{ number_format((float) $product->effective_price, 2) }}</strong></td>
                                    <td><span class="productos-stock productos-stock--{{ $stockClass }}">{{ number_format($product->existencias) }}</span></td>
                                    <td><span class="productos-status productos-status--{{ $statusClass }}">{{ $productStatus }}</span></td>
                                    <td class="productos-actions-cell">
                                        <button class="productos-action" type="button" title="Ver producto" aria-label="Ver {{ $product->nombre }}" data-open-product-view data-product-view-dialog="ver-producto-{{ $product->id }}"><i class="bi bi-eye" aria-hidden="true"></i></button>
                                        <button class="productos-action" type="button" title="Editar producto" aria-label="Editar {{ $product->nombre }}" data-open-product-edit data-product-dialog="editar-producto-{{ $product->id }}"><i class="bi bi-pencil" aria-hidden="true"></i></button>
                                        @if ($product->activo)<form method="post" action="{{ route('management.products.deactivate', $product) }}">@csrf @method('DELETE')<button class="productos-action productos-action--danger" type="submit" title="Desactivar producto" aria-label="Desactivar {{ $product->nombre }}"><i class="bi bi-trash3" aria-hidden="true"></i></button></form>@endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="productos-empty"><i class="bi bi-inbox" aria-hidden="true"></i><strong>No hay productos que coincidan con los filtros.</strong><span>Prueba con otros criterios de búsqueda.</span></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <footer class="productos-pagination">
                    <form class="productos-per-page-form" method="get" action="{{ route('management.products') }}">
                        @foreach (request()->except(['page', 'por_pagina']) as $key => $value)
                            @if (is_scalar($value))
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        <label for="productos-por-pagina">Mostrar</label>
                        <select id="productos-por-pagina" name="por_pagina" onchange="this.form.submit()" aria-label="Productos por página">
                            @foreach ([8, 10, 20, 50] as $option)
                                <option value="{{ $option }}" @selected(($filters['por_pagina'] ?? 8) === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                        <span>productos por página</span>
                    </form>
                    {{ $products->onEachSide(1)->links() }}
                </footer>
            </section>

            <aside class="productos-sidebar">
                <section class="productos-side-card">
                    <header class="productos-card-header"><div class="productos-card-title"><i class="bi bi-grid-3x3-gap-fill" aria-hidden="true"></i><div><h2>Accesos rápidos</h2></div></div></header>
                    <div class="productos-quick-grid">
                        <button type="button" data-open-product-create><span><i class="bi bi-plus-circle" aria-hidden="true"></i></span><b>Registrar<br>producto</b></button>
                        <button type="button" class="is-disabled" disabled><span><i class="bi bi-upload" aria-hidden="true"></i></span><b>Importar<br>productos</b></button>
                        <a href="{{ route('management.categories') }}"><span><i class="bi bi-diagram-3" aria-hidden="true"></i></span><b>Gestionar<br>categorías</b></a>
                        <a href="{{ route('management.inventory') }}"><span><i class="bi bi-box-seam" aria-hidden="true"></i></span><b>Ajuste de<br>inventario</b></a>
                        <button type="button" class="is-disabled" disabled><span><i class="bi bi-tag" aria-hidden="true"></i></span><b>Etiquetas<br>masivas</b></button>
                        <a href="{{ route('management.products.export', array_merge(['format' => 'csv'], $exportParameters)) }}"><span><i class="bi bi-file-earmark-arrow-down" aria-hidden="true"></i></span><b>Exportar<br>catálogo</b></a>
                    </div>
                </section>

                <section class="productos-side-card productos-advice-card">
                    <header class="productos-card-header"><div class="productos-card-title"><i class="bi bi-lightbulb-fill" aria-hidden="true"></i><div><h2>Consejos de inventario</h2></div></div><a href="{{ route('management.inventory') }}">Ver todos</a></header>
                    <div class="productos-advice-list">
                        <a href="{{ route('management.inventory') }}"><span class="productos-advice-icon is-danger"><i class="bi bi-box" aria-hidden="true"></i></span><span><b>{{ number_format($outOfStock) }} productos sin stock</b><small>Revisa y repone el inventario.</small></span><i class="bi bi-chevron-right" aria-hidden="true"></i></a>
                        <a href="{{ route('management.inventory') }}"><span class="productos-advice-icon is-warning"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i></span><span><b>{{ number_format($lowStock) }} productos con stock bajo</b><small>Considera realizar un nuevo pedido.</small></span><i class="bi bi-chevron-right" aria-hidden="true"></i></a>
                        <a href="{{ route('management.reports') }}"><span class="productos-advice-icon is-success"><i class="bi bi-currency-dollar" aria-hidden="true"></i></span><span><b>Valor total del inventario</b><small>S/ {{ number_format($inventoryValue, 2) }} en productos activos.</small></span><i class="bi bi-chevron-right" aria-hidden="true"></i></a>
                        <a href="{{ route('management.products', ['stock' => 'disponible']) }}"><span class="productos-advice-icon is-purple"><i class="bi bi-percent" aria-hidden="true"></i></span><span><b>{{ number_format($offerCount) }} productos en oferta</b><small>Aprovecha para impulsar tus ventas.</small></span><i class="bi bi-chevron-right" aria-hidden="true"></i></a>
                    </div>
                </section>
            </aside>
        </div>
    </section>

    <dialog class="productos-dialog" id="nuevo-producto">
        <div class="productos-dialog-card">
            <header class="productos-dialog-header">
                <div class="productos-dialog-title">
                    <span class="productos-dialog-icon"><i class="bi bi-box-seam" aria-hidden="true"></i></span>
                    <div>
                        <h2>Registrar nuevo producto</h2>
                        <p>Completa la información del producto o importa una plantilla Excel.</p>
                    </div>
                </div>
                <button type="button" class="productos-dialog-close" data-close-product-create aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
            </header>
            @include('gestion.productos.crear', ['categories' => $categories, 'brands' => $brands, 'suppliers' => $suppliers])
        </div>
    </dialog>

    @foreach ($products as $product)
        <dialog class="productos-dialog productos-edit-dialog" id="editar-producto-{{ $product->id }}">
            <div class="productos-dialog-card">
                <header class="productos-dialog-header">
                    <div class="productos-dialog-title">
                        <span class="productos-dialog-icon"><i class="bi bi-pencil-square" aria-hidden="true"></i></span>
                        <div><h2>Editar producto</h2><p>Actualiza la información de {{ $product->nombre }}.</p></div>
                    </div>
                    <button type="button" class="productos-dialog-close" data-close-product-edit aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
                </header>
                @include('gestion.productos.editar', ['product' => $product, 'categories' => $categories, 'brands' => $brands, 'suppliers' => $suppliers])
            </div>
        </dialog>
    @endforeach

    @foreach ($products as $product)
        @php
            $viewImage = $productImage($product->url_imagen);
            $viewStatus = !$product->activo ? 'Inactivo' : ($product->existencias === 0 ? 'Sin stock' : ($product->precio_oferta ? 'Oferta' : 'Activo'));
            $viewStatusClass = match ($viewStatus) { 'Sin stock' => 'danger', 'Oferta' => 'warning', 'Inactivo' => 'muted', default => 'success' };
        @endphp
        <dialog class="productos-dialog productos-view-dialog" id="ver-producto-{{ $product->id }}">
            <div class="productos-dialog-card">
                <header class="productos-dialog-header">
                    <div class="productos-dialog-title"><span class="productos-dialog-icon"><i class="bi bi-eye" aria-hidden="true"></i></span><div><h2>Información del producto</h2><p>Consulta el detalle registrado en Administración.</p></div></div>
                    <button type="button" class="productos-dialog-close" data-close-product-view aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
                </header>
                <div class="productos-view-body">
                    <div class="productos-view-identity">
                        @if ($viewImage)<img class="productos-view-image" src="{{ $viewImage }}" alt="{{ $product->nombre }}">@else<span class="productos-view-image productos-image--placeholder"><i class="bi bi-box-seam" aria-hidden="true"></i></span>@endif
                        <div><span class="productos-eyebrow">{{ $product->marca }}</span><h3>{{ $product->nombre }}</h3><p>{{ $product->category?->nombre ?: 'Sin categoría' }} · {{ $product->etiqueta ?: 'SKU-'.str_pad((string) $product->id, 4, '0', STR_PAD_LEFT) }}</p></div>
                    </div>
                    <div class="productos-view-stats"><div><small>Precio de venta</small><strong>S/ {{ number_format((float) $product->effective_price, 2) }}</strong></div><div><small>Stock actual</small><strong>{{ number_format($product->existencias) }} unidades</strong></div><div><small>Estado</small><span class="productos-status productos-status--{{ $viewStatusClass }}">{{ $viewStatus }}</span></div><div><small>Proveedor</small><strong>{{ $product->suppliers->first()?->nombre ?: 'Sin proveedor' }}</strong></div></div>
                    <section class="productos-view-description"><small>Descripción</small><p>{{ $product->descripcion ?: 'Sin descripción registrada.' }}</p></section>
                </div>
                <footer class="productos-create-footer productos-view-footer"><button class="productos-create-cancel" type="button" data-close-product-view>Cerrar</button><button class="productos-create-submit" type="button" data-open-product-edit data-product-dialog="editar-producto-{{ $product->id }}"><i class="bi bi-pencil" aria-hidden="true"></i> Editar producto</button></footer>
            </div>
        </dialog>
    @endforeach
@endsection

@push('scripts')
    <script>
        (() => {
            const dialog = document.querySelector('#nuevo-producto');
            const page = document.querySelector('[data-product-page]');

            if (!dialog || !page) {
                return;
            }

            page.querySelectorAll('[data-open-product-create]').forEach((button) => {
                button.addEventListener('click', () => dialog.showModal());
            });
            dialog.querySelector('[data-close-product-create]')?.addEventListener('click', () => dialog.close());
            dialog.addEventListener('click', (event) => {
                if (event.target === dialog) {
                    dialog.close();
                }
            });

            const imageInput = dialog.querySelector('[data-product-image-input]');
            const imagePreview = dialog.querySelector('[data-product-image-preview]');
            const imagePreviewImage = dialog.querySelector('[data-product-image-preview-image]');
            const clearImage = dialog.querySelector('[data-product-image-clear]');

            imageInput?.addEventListener('change', () => {
                const [file] = imageInput.files ?? [];

                if (!file || !imagePreview || !imagePreviewImage) {
                    return;
                }

                imagePreviewImage.src = URL.createObjectURL(file);
                imagePreview.hidden = false;
            });
            clearImage?.addEventListener('click', () => {
                if (imageInput) {
                    imageInput.value = '';
                }
                if (imagePreviewImage) {
                    imagePreviewImage.src = '';
                }
                if (imagePreview) {
                    imagePreview.hidden = true;
                }
            });

            document.querySelectorAll('[data-open-product-edit]').forEach((button) => {
                const editDialog = document.getElementById(button.dataset.productDialog);

                button.addEventListener('click', () => editDialog?.showModal());
            });
            document.querySelectorAll('.productos-edit-dialog').forEach((editDialog) => {
                editDialog.querySelector('[data-close-product-edit]')?.addEventListener('click', () => editDialog.close());
                editDialog.addEventListener('click', (event) => {
                    if (event.target === editDialog) {
                        editDialog.close();
                    }
                });
            });
            page.querySelectorAll('[data-open-product-view]').forEach((button) => {
                const viewDialog = document.getElementById(button.dataset.productViewDialog);
                const editDialog = document.getElementById(button.dataset.productDialog);

                button.addEventListener('click', () => {
                    if (viewDialog) {
                        viewDialog.showModal();
                    } else if (editDialog) {
                        editDialog.showModal();
                    }
                });
            });
            document.querySelectorAll('.productos-view-dialog').forEach((viewDialog) => {
                viewDialog.querySelectorAll('[data-close-product-view]').forEach((button) => button.addEventListener('click', () => viewDialog.close()));
                viewDialog.addEventListener('click', (event) => {
                    if (event.target === viewDialog) {
                        viewDialog.close();
                    }
                });
            });

            @if (session('open_product_modal') || $errors->any())
                dialog.showModal();
            @endif
        })();
    </script>
@endpush
