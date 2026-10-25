@extends('layouts.administracion')

@section('title', 'Productos')
@section('body_class', 'modulo-productos')

@section('content')
    <section class="admin-page" data-admin-table-container>
        <header class="admin-page-head">
            <div>
                <span class="eyebrow">Catálogo</span>
                <h1>Productos <i class="bi bi-phone"></i></h1>
                <p>Administra precios, categorías y disponibilidad del catálogo.</p>
            </div>
            <div class="admin-page-actions">
                <a class="admin-primary-button" href="#nuevo-producto">
                    <i class="bi bi-plus-lg"></i> Nuevo producto
                </a>
            </div>
        </header>

        <div class="admin-grid-main">
            <section class="admin-panel">
                <header class="admin-panel-header">
                    <div class="admin-panel-title">
                        <i class="bi bi-phone"></i>
                        <div>
                            <h2>Catálogo registrado</h2>
                            <p>{{ $products->total() }} productos disponibles</p>
                        </div>
                    </div>
                </header>
                <div class="admin-table-wrap">
                    <table class="admin-table" data-admin-table>
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th>Stock</th>
                                <th>Precio</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($products as $product)
                                <tr data-data-row>
                                    <td class="admin-product-cell">
                                        <span class="admin-product-image-placeholder"><i class="bi bi-phone"></i></span>
                                        <span>
                                            <b>{{ $product->marca }} {{ $product->nombre }}</b>
                                            <small>{{ $product->activo ? 'Activo' : 'Inactivo' }}</small>
                                        </span>
                                    </td>
                                    <td>{{ $product->category?->nombre ?: 'Sin categoría' }}</td>
                                    <td><b>{{ $product->existencias }}</b></td>
                                    <td><b>S/ {{ number_format((float) $product->effective_price, 2) }}</b></td>
                                    <td class="admin-table-actions">
                                        <details>
                                            <summary class="admin-action-icon" aria-label="Editar producto"><i class="bi bi-pencil"></i></summary>
                                            @include('gestion.productos.editar', ['product' => $product, 'categories' => $categories])
                                        </details>
                                        @if ($product->activo)
                                            <form method="post" action="{{ route('management.products.deactivate', $product) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button class="admin-action-icon" type="submit" title="Desactivar">
                                                    <i class="bi bi-eye-slash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="admin-table-empty">No hay productos registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="admin-pagination">{{ $products->links() }}</div>
            </section>

            <aside class="admin-panel" id="nuevo-producto">
                <header class="admin-panel-header">
                    <div class="admin-panel-title">
                        <i class="bi bi-plus-circle"></i>
                        <div>
                            <h2>Nuevo producto</h2>
                            <p>Registra el siguiente equipo del catálogo.</p>
                        </div>
                    </div>
                </header>
                @include('gestion.productos.crear', ['categories' => $categories])
            </aside>
        </div>
    </section>
@endsection

