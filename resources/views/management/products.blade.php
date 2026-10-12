@extends('layouts.admin')

@section('title', 'Productos')
@section('body_class', 'modulo-productos')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/cell-legacy/css/modulos/productos/productos.css') }}">
@endpush

@section('content')
    <section class="admin-page" data-admin-table-container>
        <header class="admin-page-head">
            <div><span class="eyebrow">Catálogo</span><h1>Productos <i class="bi bi-phone"></i></h1><p>Administra precios, categorías y disponibilidad del catálogo.</p></div>
            <div class="admin-page-actions"><a class="admin-primary-button" href="#nuevo-producto"><i class="bi bi-plus-lg"></i> Nuevo producto</a></div>
        </header>

        <div class="admin-grid-main">
            <section class="admin-panel">
                <header class="admin-panel-header">
                    <div class="admin-panel-title"><i class="bi bi-phone"></i><div><h2>Catálogo registrado</h2><p>{{ $products->total() }} productos disponibles</p></div></div>
                </header>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead><tr><th>Producto</th><th>Categoría</th><th>Stock</th><th>Precio</th><th>Acciones</th></tr></thead>
                        <tbody>
                            @forelse ($products as $product)
                                <tr data-data-row>
                                    <td class="admin-product-cell"><span class="admin-product-image-placeholder"><i class="bi bi-phone"></i></span><span><b>{{ $product->marca }} {{ $product->nombre }}</b><small>{{ $product->activo ? 'Activo' : 'Inactivo' }}</small></span></td>
                                    <td>{{ $product->category?->nombre ?: 'Sin categoría' }}</td>
                                    <td><b>{{ $product->existencias }}</b></td>
                                    <td><b>S/ {{ number_format((float) $product->effective_price, 2) }}</b></td>
                                    <td class="admin-table-actions">
                                        <details>
                                            <summary class="admin-action-icon" aria-label="Editar producto"><i class="bi bi-pencil"></i></summary>
                                            <form method="post" class="admin-edit-popover" action="{{ route('management.products.update', $product) }}">
                                                @csrf @method('PUT')
                                                <div class="admin-form-grid">
                                                    <label class="admin-form-group"><span>Marca</span><input name="marca" value="{{ $product->marca }}" required></label>
                                                    <label class="admin-form-group"><span>Nombre</span><input name="nombre" value="{{ $product->nombre }}" required></label>
                                                    <label class="admin-form-group"><span>Categoría</span><select name="categoria_id" required>@foreach($categories as $category)<option value="{{ $category->id }}" @selected($category->id === $product->categoria_id)>{{ $category->nombre }}</option>@endforeach</select></label>
                                                    <label class="admin-form-group"><span>Precio</span><input name="precio" type="number" min="0" step="0.01" value="{{ $product->precio }}" required></label>
                                                    <label class="admin-form-group"><span>Precio oferta</span><input name="precio_oferta" type="number" min="0" step="0.01" value="{{ $product->precio_oferta }}"></label>
                                                    <label class="admin-form-group"><span>Imagen</span><input name="url_imagen" value="{{ $product->url_imagen }}"></label>
                                                    <label class="admin-form-group is-full"><span>Descripción</span><textarea name="descripcion">{{ $product->descripcion }}</textarea></label>
                                                </div>
                                                <div class="admin-form-actions"><button class="admin-primary-button" type="submit">Guardar cambios</button></div>
                                            </form>
                                        </details>
                                        @if ($product->activo)<form method="post" action="{{ route('management.products.deactivate', $product) }}">@csrf @method('DELETE')<button class="admin-action-icon" type="submit" title="Desactivar"><i class="bi bi-eye-slash"></i></button></form>@endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="admin-table-empty">No hay productos registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="admin-pagination">{{ $products->links() }}</div>
            </section>

            <aside class="admin-panel" id="nuevo-producto">
                <header class="admin-panel-header"><div class="admin-panel-title"><i class="bi bi-plus-circle"></i><div><h2>Nuevo producto</h2><p>Registra el siguiente equipo del catálogo.</p></div></div></header>
                <form method="post" action="{{ route('management.products.store') }}">
                    @csrf
                    <div class="admin-form-grid">
                        <label class="admin-form-group"><span>Marca *</span><input name="marca" required maxlength="80"></label>
                        <label class="admin-form-group"><span>Nombre *</span><input name="nombre" required maxlength="160"></label>
                        <label class="admin-form-group is-full"><span>Categoría *</span><select name="categoria_id" required>@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->nombre }}</option>@endforeach</select></label>
                        <label class="admin-form-group"><span>Precio *</span><input name="precio" type="number" min="0" step="0.01" required></label>
                        <label class="admin-form-group"><span>Precio oferta</span><input name="precio_oferta" type="number" min="0" step="0.01"></label>
                        <label class="admin-form-group is-full"><span>Ruta de imagen</span><input name="url_imagen" maxlength="255"></label>
                        <label class="admin-form-group is-full"><span>Descripción</span><textarea name="descripcion" rows="4"></textarea></label>
                    </div>
                    <div class="admin-form-actions"><button class="admin-primary-button" type="submit"><i class="bi bi-check2-circle"></i> Crear producto</button></div>
                </form>
            </aside>
        </div>
    </section>
@endsection
