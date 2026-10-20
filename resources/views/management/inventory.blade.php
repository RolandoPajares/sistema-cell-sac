
@extends('layouts.admin')

@section('title', 'Inventario')
@section('body_class', 'modulo-inventario')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/cell-legacy/css/roles/internos/compras-logistica.css') }}">
@endpush

@section('content')
<section class="admin-page" data-admin-table-container>

    <header class="admin-page-head">
        <div>
            <span class="eyebrow">Operaciones</span>
            <h1>Inventario <i class="bi bi-boxes"></i></h1>
            <p>Consulta existencias y registra entradas, salidas o ajustes con trazabilidad.</p>
        </div>
    </header>

    <section class="admin-panel">

        <header class="admin-panel-header">
            <div class="admin-panel-title">
                <i class="bi bi-box-seam"></i>
                <div>
                    <h2>Lista de inventario</h2>
                    <p>{{ $products->total() }} productos activos</p>
                </div>
            </div>

            
<form class="inventory-search-form"
      method="GET"
      action="{{ route('management.inventory') }}"
      role="search">

    <div class="inventory-search-field">
        <i class="bi bi-search" aria-hidden="true"></i>

        <input
            type="search"
            name="q"
            value="{{ $search }}"
            placeholder="Buscar por nombre o marca..."
            aria-label="Buscar productos">
    </div>

    <button type="submit" class="inventory-search-button">
        <i class="bi bi-search" aria-hidden="true"></i>
        Buscar
    </button>

    @if($search !== '')
        <a href="{{ route('management.inventory') }}"
           class="inventory-search-clear">
            Limpiar
        </a>
    @endif

</form>

        </header>

        <div class="admin-table-wrap">
            <table class="admin-table">

                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Stock actual</th>
                        <th>Proveedores</th>
                        <th>Acción</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($products as $product)
                        <tr data-data-row>

                            <td class="admin-product-cell">
                                <span class="admin-product-image-placeholder">
                                    <i class="bi bi-phone"></i>
                                </span>

                                <span>
                                    <b>{{ $product->marca }} {{ $product->nombre }}</b>
                                    <small>
                                        {{ $product->activo ? 'Activo' : 'Inactivo' }}
                                    </small>
                                </span>
                            </td>

                            <td>
                                {{ $product->category?->nombre ?: 'Sin categoría' }}
                            </td>

                            <td>
                                <span class="admin-status {{ $product->existencias <= 0 ? 'admin-status--danger' : ($product->existencias <= 5 ? 'admin-status--warning' : '') }}">
                                    {{ $product->existencias }} unidades
                                </span>
                            </td>

                            <td>
                                {{ $product->suppliers->count() }}
                            </td>

                            <td>
                                <button
                                    type="button"
                                    class="admin-secondary-button inventory-open-modal"
                                    data-inventory-modal-open
                                    data-product-name="{{ $product->marca }} {{ $product->nombre }}"
                                    data-product-action="{{ route('management.inventory.adjust', $product) }}">
                                    Registrar movimiento
                                </button>
                            </td>

                        </tr>
                    @empty
                        <tr>
    <td colspan="5" class="admin-table-empty">
        @if($search !== '')
            No se encontraron productos para "{{ $search }}".
        @else
            No hay productos para consultar.
        @endif
    </td>
</tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        {{-- PAGINACIÓN PERSONALIZADA --}}
        @if($products->hasPages())
            <nav class="inventory-pagination"
                 aria-label="Paginación del inventario">

                @if($products->onFirstPage())
                    <span class="inventory-page-arrow is-disabled"
                          aria-hidden="true">
                        <i class="bi bi-chevron-left"></i>
                    </span>
                @else
                    <a class="inventory-page-arrow"
                       href="{{ $products->previousPageUrl() }}"
                       aria-label="Página anterior">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                @endif

                <div class="inventory-page-numbers">
                    @for($page = 1; $page <= $products->lastPage(); $page++)

                        @if($page == $products->currentPage())
                            <span class="inventory-page-dot is-current"
                                  aria-current="page"
                                  aria-label="Página {{ $page }}, actual">
                                {{ $page }}
                            </span>
                        @else
                            <a class="inventory-page-dot"
                               href="{{ $products->url($page) }}"
                               aria-label="Ir a la página {{ $page }}">
                                <span class="inventory-dot-square"
                                      aria-hidden="true"></span>
                            </a>
                        @endif

                    @endfor
                </div>

                @if($products->hasMorePages())
                    <a class="inventory-page-arrow"
                       href="{{ $products->nextPageUrl() }}"
                       aria-label="Página siguiente">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                @else
                    <span class="inventory-page-arrow is-disabled"
                          aria-hidden="true">
                        <i class="bi bi-chevron-right"></i>
                    </span>
                @endif

            </nav>

            <p class="inventory-pagination-info">
                Página {{ $products->currentPage() }}
                de {{ $products->lastPage() }}
            </p>
        @endif

    </section>
</section>

{{-- MODAL DE MOVIMIENTO DE INVENTARIO --}}
<dialog class="inventory-movement-modal"
        id="inventoryMovementModal"
        aria-labelledby="inventoryModalTitle">

    <div class="inventory-modal-header">
        <div>
            <span class="inventory-modal-eyebrow">
                Gestión de inventario
            </span>

            <h2 id="inventoryModalTitle">
                Registrar movimiento
            </h2>

            <p id="inventoryModalProduct"></p>
        </div>

        <button type="button"
                class="inventory-modal-close"
                data-inventory-modal-close
                aria-label="Cerrar ventana">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <form id="inventoryMovementForm"
          method="post"
          action="">

        @csrf

        <div class="inventory-modal-body">

            <label class="inventory-modal-field">
                <span>Tipo de movimiento</span>

                <select name="tipo_movimiento" required>
                    <option value="entrada">Entrada</option>
                    <option value="salida">Salida</option>
                    <option value="ajuste">Ajuste final</option>
                </select>
            </label>

            <label class="inventory-modal-field">
                <span>Cantidad</span>

                <input type="number"
                       name="cantidad"
                       min="1"
                       step="1"
                       placeholder="Ingresa la cantidad"
                       required>
            </label>

            <label class="inventory-modal-field">
                <span>Motivo u observación</span>

                <textarea name="notas"
                          rows="3"
                          maxlength="500"
                          placeholder="Describe el motivo del movimiento..."
                          required></textarea>
            </label>

        </div>

        <div class="inventory-modal-footer">

            <button type="button"
                    class="inventory-modal-cancel"
                    data-inventory-modal-close>
                Cancelar
            </button>

            <button type="submit"
                    class="inventory-modal-submit">
                <i class="bi bi-check-circle"></i>
                Guardar movimiento
            </button>

        </div>
    </form>

</dialog>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const modal = document.getElementById('inventoryMovementModal');
    const form = document.getElementById('inventoryMovementForm');
    const productLabel = document.getElementById('inventoryModalProduct');

    if (!modal || !form || !productLabel) return;

    document.querySelectorAll('[data-inventory-modal-open]')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                const productName = button.dataset.productName;
                const productAction = button.dataset.productAction;

                productLabel.textContent = productName;
                form.action = productAction;
                form.reset();

                modal.showModal();
            });
        });

    document.querySelectorAll('[data-inventory-modal-close]')
        .forEach(function (button) {

            button.addEventListener('click', function () {
                modal.close();
            });
        });

    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            modal.close();
        }
    });

    form.addEventListener('submit', function () {
        const submitButton = form.querySelector('[type="submit"]');

        if (submitButton) {
            submitButton.disabled = true;
            submitButton.textContent = 'Guardando...';
        }
    });

});
</script>
@endsection
