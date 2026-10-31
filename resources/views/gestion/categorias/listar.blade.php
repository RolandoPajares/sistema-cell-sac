@extends('layouts.administracion')

@section('title', 'Categorías')
@section('body_class', 'modulo-categorias')

@php
    $categoryIcon = static function (string $name): string {
        $normalized = mb_strtolower($name);

        return match (true) {
            str_contains($normalized, 'audio') => 'bi-headphones',
            str_contains($normalized, 'celular') || str_contains($normalized, 'smart') => 'bi-phone',
            str_contains($normalized, 'comput') || str_contains($normalized, 'laptop') => 'bi-laptop',
            str_contains($normalized, 'video') || str_contains($normalized, 'juego') => 'bi-controller',
            str_contains($normalized, 'wear') || str_contains($normalized, 'reloj') => 'bi-smartwatch',
            str_contains($normalized, 'hogar') => 'bi-house-heart',
            default => 'bi-box-seam',
        };
    };
    $categoryQuery = request()->query();
@endphp

@section('content')
    <section class="categorias-page" data-categorias-page>
        <header class="categorias-hero">
            <div>
                <span class="categorias-eyebrow">Catálogo</span>
                <h1>Categorías <i class="bi bi-diagram-3" aria-hidden="true"></i></h1>
                <p>Organiza tus productos en categorías para una mejor gestión.</p>
            </div>
            <div class="categorias-actions">
                <a class="categorias-export-button" href="{{ route('management.categories.export', $categoryQuery) }}"><i class="bi bi-download" aria-hidden="true"></i> Exportar</a>
                <button class="categorias-primary-button" type="button" data-open-category-create><i class="bi bi-plus-lg" aria-hidden="true"></i> Nueva categoría</button>
            </div>
        </header>

        <div class="categorias-kpi-grid">
            <article class="categorias-kpi categorias-kpi--blue">
                <span class="categorias-kpi-icon"><i class="bi bi-layers" aria-hidden="true"></i></span>
                <div><small>Total de categorías</small><strong>{{ number_format($totalCategories) }}</strong><span>Activas en el sistema</span></div>
            </article>
            <article class="categorias-kpi categorias-kpi--green">
                <span class="categorias-kpi-icon"><i class="bi bi-check-circle" aria-hidden="true"></i></span>
                <div><small>Categorías activas</small><strong>{{ number_format($activeCategories) }}</strong><span>Disponibles para productos</span></div>
            </article>
            <article class="categorias-kpi categorias-kpi--purple">
                <span class="categorias-kpi-icon"><i class="bi bi-eye-slash" aria-hidden="true"></i></span>
                <div><small>Categorías inactivas</small><strong>{{ number_format($inactiveCategories) }}</strong><span>No visibles en catálogo</span></div>
            </article>
            <article class="categorias-kpi categorias-kpi--violet">
                <span class="categorias-kpi-icon"><i class="bi bi-box-seam" aria-hidden="true"></i></span>
                <div><small>Productos asignados</small><strong>{{ number_format($assignedProducts) }}</strong><span>En todas las categorías</span></div>
            </article>
        </div>

        <form class="categorias-filters" method="get" action="{{ route('management.categories') }}">
            <label class="categorias-search"><i class="bi bi-search" aria-hidden="true"></i><span class="visually-hidden">Buscar categorías</span><input type="search" name="buscar" value="{{ $filters['buscar'] ?? '' }}" placeholder="Buscar categorías..."></label>
            <label><span>Estado</span><select name="estado"><option value="">Todos</option><option value="activa" @selected(($filters['estado'] ?? null) === 'activa')>Activas</option><option value="inactiva" @selected(($filters['estado'] ?? null) === 'inactiva')>Inactivas</option></select></label>
            <label><span>Ordenar por</span><select name="orden"><option value="nombre_asc" @selected(($filters['orden'] ?? 'nombre_asc') === 'nombre_asc')>Nombre (A-Z)</option><option value="nombre_desc" @selected(($filters['orden'] ?? '') === 'nombre_desc')>Nombre (Z-A)</option><option value="reciente" @selected(($filters['orden'] ?? '') === 'reciente')>Más recientes</option></select></label>
            <a class="categorias-clear-button" href="{{ route('management.categories') }}"><i class="bi bi-arrow-repeat" aria-hidden="true"></i> Limpiar filtros</a>
        </form>

        <section class="categorias-table-card">
            <div class="categorias-table-wrap">
                <table class="categorias-table">
                    <thead>
                        <tr><th>#</th><th>Imagen</th><th>Nombre</th><th>Descripción</th><th>Productos</th><th>Estado</th><th>Fecha de creación</th><th>Acciones</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $category)
                            <tr>
                                <td>{{ (($categories->currentPage() - 1) * $categories->perPage()) + $loop->iteration }}</td>
                                <td><span class="categorias-image"><i class="bi {{ $categoryIcon($category->nombre) }}" aria-hidden="true"></i></span></td>
                                <td class="categorias-name"><strong>{{ $category->nombre }}</strong></td>
                                <td class="categorias-description">{{ $category->descripcion ?: 'Sin descripción registrada.' }}</td>
                                <td><span class="categorias-products-count">{{ number_format($category->products_count) }}</span></td>
                                <td><span class="categorias-status categorias-status--{{ $category->activo ? 'active' : 'inactive' }}"><i class="bi bi-circle-fill" aria-hidden="true"></i>{{ $category->activo ? 'Activa' : 'Inactiva' }}</span></td>
                                <td class="categorias-date">{{ $category->creado_en?->format('d/m/Y') ?: '—' }}<small>{{ $category->creado_en?->format('H:i') }}</small></td>
                                <td class="categorias-row-actions">
                                    <details class="categorias-edit-details" data-category-dialog="editar-categoria-{{ $category->id }}">
                                        <summary class="categorias-icon-button" title="Editar categoría" aria-label="Editar {{ $category->nombre }}"><i class="bi bi-pencil" aria-hidden="true"></i></summary>
                                        <div class="categorias-edit-popover">@include('gestion.categorias.editar', ['category' => $category])</div>
                                    </details>
                                    <button class="categorias-icon-button" type="button" title="Ver información" aria-label="Ver información de {{ $category->nombre }}" data-open-category-view data-category-view-dialog="ver-categoria-{{ $category->id }}"><i class="bi bi-eye" aria-hidden="true"></i></button>
                                    @if ($category->activo)
                                        <form method="post" action="{{ route('management.categories.deactivate', $category) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="categorias-icon-button categorias-icon-button--danger" type="submit" title="Desactivar categoría" aria-label="Desactivar {{ $category->nombre }}" data-confirm="¿Desactivar esta categoría?"><i class="bi bi-trash3" aria-hidden="true"></i></button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td class="categorias-empty" colspan="8"><i class="bi bi-inbox" aria-hidden="true"></i><strong>No hay categorías que coincidan con los filtros.</strong><span>Prueba con otros criterios de búsqueda.</span></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <footer class="categorias-pagination">
                <span>Mostrando {{ $categories->firstItem() ?? 0 }} a {{ $categories->lastItem() ?? 0 }} de {{ $categories->total() }} categorías</span>
                <div class="categorias-pagination-controls">{{ $categories->onEachSide(1)->links() }}<span class="categorias-per-page">7 por página <i class="bi bi-chevron-down" aria-hidden="true"></i></span></div>
            </footer>
        </section>
    </section>

    @foreach ($categories as $category)
        <dialog class="categorias-dialog categorias-edit-dialog" id="editar-categoria-{{ $category->id }}">
            <div class="categorias-dialog-card">
                <header class="categorias-dialog-header"><div><span class="categorias-eyebrow">Catálogo</span><h2>Editar categoría</h2><p>Actualiza la información de {{ $category->nombre }}.</p></div><button class="categorias-dialog-close" type="button" data-close-category-edit aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header>
                @include('gestion.categorias.editar', ['category' => $category])
            </div>
        </dialog>
    @endforeach

    @foreach ($categories as $category)
        <dialog class="categorias-dialog categorias-view-dialog" id="ver-categoria-{{ $category->id }}">
            <div class="categorias-dialog-card">
                <header class="categorias-dialog-header"><div><span class="categorias-eyebrow">Catálogo</span><h2>Información de categoría</h2><p>Resumen de {{ $category->nombre }}.</p></div><button class="categorias-dialog-close" type="button" data-close-category-view aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header>
                <div class="categorias-view-body">
                    <div class="categorias-view-heading"><span class="categorias-view-icon"><i class="bi {{ $categoryIcon($category->nombre) }}" aria-hidden="true"></i></span><div><h3>{{ $category->nombre }}</h3><span class="categorias-status categorias-status--{{ $category->activo ? 'active' : 'inactive' }}"><i class="bi bi-circle-fill" aria-hidden="true"></i>{{ $category->activo ? 'Activa' : 'Inactiva' }}</span></div></div>
                    <div class="categorias-view-grid"><div><small>Productos asignados</small><strong>{{ number_format($category->products_count) }}</strong></div><div><small>Fecha de creación</small><strong>{{ $category->creado_en?->format('d/m/Y H:i') ?: 'Sin fecha' }}</strong></div></div>
                    <section class="categorias-view-description"><small>Descripción</small><p>{{ $category->descripcion ?: 'Sin descripción registrada.' }}</p></section>
                </div>
                <footer class="categorias-form-actions categorias-view-actions"><button class="categorias-button categorias-button--secondary" type="button" data-close-category-view>Cerrar</button><a class="categorias-button categorias-button--primary" href="{{ route('management.products', ['categoria' => $category->id]) }}"><i class="bi bi-box-seam" aria-hidden="true"></i> Ver productos</a></footer>
            </div>
        </dialog>
    @endforeach

    <dialog class="categorias-dialog" id="nueva-categoria">
        <div class="categorias-dialog-card">
            <header class="categorias-dialog-header"><div><span class="categorias-eyebrow">Catálogo</span><h2>Nueva categoría</h2><p>Registra una categoría para organizar tus productos.</p></div><button class="categorias-dialog-close" type="button" data-close-category-create aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header>
            @include('gestion.categorias.crear')
        </div>
    </dialog>
@endsection

@push('scripts')
    <script>
        (() => {
            const page = document.querySelector('[data-categorias-page]');
            const dialog = document.querySelector('#nueva-categoria');

            if (!page || !dialog) {
                return;
            }

            page.querySelectorAll('[data-open-category-create]').forEach((button) => {
                button.addEventListener('click', () => dialog.showModal());
            });
            dialog.querySelector('[data-close-category-create]')?.addEventListener('click', () => dialog.close());
            dialog.addEventListener('click', (event) => {
                if (event.target === dialog) {
                    dialog.close();
                }
            });

            page.querySelectorAll('[data-category-dialog]').forEach((trigger) => {
                const editDialog = document.getElementById(trigger.dataset.categoryDialog);

                trigger.querySelector('summary')?.addEventListener('click', (event) => {
                    event.preventDefault();
                    editDialog?.showModal();
                });
            });
            document.querySelectorAll('.categorias-edit-dialog').forEach((editDialog) => {
                editDialog.querySelector('[data-close-category-edit]')?.addEventListener('click', () => editDialog.close());
                editDialog.addEventListener('click', (event) => {
                    if (event.target === editDialog) {
                        editDialog.close();
                    }
                });
            });
            page.querySelectorAll('[data-open-category-view]').forEach((button) => {
                const viewDialog = document.getElementById(button.dataset.categoryViewDialog);

                button.addEventListener('click', () => viewDialog?.showModal());
            });
            document.querySelectorAll('.categorias-view-dialog').forEach((viewDialog) => {
                viewDialog.querySelectorAll('[data-close-category-view]').forEach((button) => button.addEventListener('click', () => viewDialog.close()));
                viewDialog.addEventListener('click', (event) => {
                    if (event.target === viewDialog) {
                        viewDialog.close();
                    }
                });
            });

            @if ($errors->any())
                dialog.showModal();
            @endif
        })();
    </script>
@endpush
