
@extends('layouts.admin')

@section('title', 'Compras')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/cell-legacy/css/roles/internos/compras-logistica.css') }}">
@endpush

@section('content')
<section class="container content-page purchases-page">

    {{-- ENCABEZADO --}}
    <header class="purchases-page-header">
        <span class="purchases-eyebrow">
            Compras y logística
        </span>

        <h1>
            Compras a proveedores
            <i class="bi bi-cart-check" aria-hidden="true"></i>
        </h1>

        <p>
            Registra la recepción de productos y consulta
            el historial de compras realizadas.
        </p>
    </header>

    {{-- FORMULARIO DE RECEPCIÓN --}}
    <section class="purchases-form-panel">

        <div class="purchases-section-header">
            <div class="purchases-section-icon">
                <i class="bi bi-box-seam"></i>
            </div>

            <div>
                <h2>Registrar recepción</h2>
                <p>Completa los datos de la compra al proveedor.</p>
            </div>
        </div>

        <form method="post"
              class="purchases-form"
              action="{{ route('management.purchases.store') }}">

            @csrf

            <div class="purchases-form-grid">

                {{-- PROVEEDOR --}}
                <label class="purchases-field">
                    <span>Proveedor</span>

                    <select name="proveedor_id" required>
                        <option value="">Seleccionar proveedor</option>

                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}"
                                @selected(old('proveedor_id') == $supplier->id)>
                                {{ $supplier->nombre }}
                            </option>
                        @endforeach
                    </select>
                </label>

                {{-- FECHA --}}
                <label class="purchases-field">
                    <span>Fecha de compra</span>

                    <input type="date"
                           name="fecha_compra"
                           required
                           value="{{ old('fecha_compra', now()->toDateString()) }}">
                </label>

                {{-- PRODUCTO --}}
                <label class="purchases-field">
                    <span>Producto</span>

                    <select name="items[0][producto_id]" required>
                        <option value="">Seleccionar producto</option>

                        @foreach($products as $product)
                            <option value="{{ $product->id }}"
                                @selected(old('items.0.producto_id') == $product->id)>
                                {{ $product->nombre }}
                            </option>
                        @endforeach
                    </select>
                </label>

                {{-- CANTIDAD --}}
                <label class="purchases-field">
                    <span>Cantidad recibida</span>

                    <input type="number"
                           name="items[0][cantidad]"
                           min="1"
                           step="1"
                           required
                           value="{{ old('items.0.cantidad') }}"
                           placeholder="Ej. 20">
                </label>

                {{-- COSTO UNITARIO --}}
                <label class="purchases-field purchases-field-full">
                    <span>Costo unitario (S/)</span>

                    <input type="number"
                           name="items[0][costo_unitario]"
                           min="0"
                           step="0.01"
                           required
                           value="{{ old('items.0.costo_unitario') }}"
                           placeholder="Ej. 150.00">
                </label>

            </div>

            {{-- ERRORES DE VALIDACIÓN --}}
            @if($errors->any())
                <div class="purchases-form-errors" role="alert">
                    <strong>Revisa los datos ingresados:</strong>

                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="purchases-form-footer">
                <button type="submit"
                        class="purchases-submit-button">
                    <i class="bi bi-check-circle"></i>
                    Registrar compra y entrada
                </button>
            </div>

        </form>
    </section>

    {{-- HISTORIAL DE COMPRAS --}}
    <section class="purchases-history-panel">

        <div class="purchases-section-header">
            <div class="purchases-section-icon">
                <i class="bi bi-clock-history"></i>
            </div>

            <div>
                <h2>Historial de compras</h2>
                <p>Consulta las recepciones registradas.</p>
            </div>
        </div>

        <div class="purchases-history-list">

            @forelse($purchases as $purchase)

                <article class="purchase-history-card">

                    <div class="purchase-history-icon">
                        <i class="bi bi-receipt"></i>
                    </div>

                    <div class="purchase-history-info">

                        <h3>
                            Compra #{{ $purchase->id }}
                        </h3>

                        <p class="purchase-history-supplier">
                            {{ $purchase->supplier?->nombre ?? 'Proveedor no disponible' }}
                        </p>

                        <div class="purchase-history-meta">
                            <span>
                                <i class="bi bi-calendar3"></i>
                                {{ $purchase->fecha_compra }}
                            </span>

                            <span class="purchase-history-status">
                                {{ $purchase->estado }}
                            </span>
                        </div>

                    </div>

                    <div class="purchase-history-total">
                        <small>Total</small>

                        <strong>
                            S/ {{ number_format((float) $purchase->total, 2) }}
                        </strong>
                    </div>

                </article>

            @empty

                <div class="purchases-history-empty">
                    <i class="bi bi-inbox"></i>
                    <h3>No hay compras registradas</h3>
                    <p>Las compras realizadas aparecerán aquí.</p>
                </div>

            @endforelse

        </div>

        {{-- PAGINACIÓN --}}
        @if($purchases->hasPages())

            <nav class="purchases-pagination"
                 aria-label="Paginación de compras">

                @if($purchases->onFirstPage())
                    <span class="purchases-page-arrow is-disabled"
                          aria-hidden="true">
                        <i class="bi bi-chevron-left"></i>
                    </span>
                @else
                    <a class="purchases-page-arrow"
                       href="{{ $purchases->previousPageUrl() }}"
                       aria-label="Página anterior">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                @endif

                <div class="purchases-page-numbers">

                    @foreach($purchases->getUrlRange(1, $purchases->lastPage()) as $page => $url)

                        @if($page == $purchases->currentPage())
                            <span class="purchases-page-number is-current"
                                  aria-current="page">
                                {{ $page }}
                            </span>
                        @else
                            <a class="purchases-page-number"
                               href="{{ $url }}"
                               aria-label="Ir a la página {{ $page }}">
                                {{ $page }}
                            </a>
                        @endif

                    @endforeach

                </div>

                @if($purchases->hasMorePages())
                    <a class="purchases-page-arrow"
                       href="{{ $purchases->nextPageUrl() }}"
                       aria-label="Página siguiente">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                @else
                    <span class="purchases-page-arrow is-disabled"
                          aria-hidden="true">
                        <i class="bi bi-chevron-right"></i>
                    </span>
                @endif

            </nav>

            <p class="purchases-pagination-info">
                Página {{ $purchases->currentPage() }}
                de {{ $purchases->lastPage() }}
            </p>

        @endif

    </section>

</section>
@endsection
