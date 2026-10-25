@extends('layouts.administracion')

@section('title', 'Inventario')
@section('body_class', 'modulo-inventario')

@section('content')
    <section class="admin-page" data-admin-table-container>
        <header class="admin-page-head">
            <div><span class="eyebrow">Operaciones</span><h1>Inventario <i class="bi bi-boxes"></i></h1><p>Consulta existencias y registra entradas, salidas o ajustes con trazabilidad.</p></div>
        </header>

        <section class="admin-panel">
            <header class="admin-panel-header"><div class="admin-panel-title"><i class="bi bi-box-seam"></i><div><h2>Lista de inventario</h2><p>{{ $products->total() }} productos activos</p></div></div><label class="admin-toolbar-search"><i class="bi bi-search"></i><input type="search" data-table-search placeholder="Buscar producto..."></label></header>
            <div class="admin-table-wrap">
                <table class="admin-table" data-admin-table>
                    <thead><tr><th>Producto</th><th>Categoría</th><th>Stock actual</th><th>Proveedores</th><th>Acción</th></tr></thead>
                    <tbody>
                        @forelse ($products as $product)
                            <tr data-data-row>
                                <td class="admin-product-cell"><span class="admin-product-image-placeholder"><i class="bi bi-phone"></i></span><span><b>{{ $product->marca }} {{ $product->nombre }}</b><small>{{ $product->activo ? 'Activo' : 'Inactivo' }}</small></span></td>
                                <td>{{ $product->category?->nombre ?: 'Sin categoría' }}</td>
                                <td><span class="admin-status {{ $product->existencias <= 0 ? 'admin-status--danger' : ($product->existencias <= 5 ? 'admin-status--warning' : '') }}">{{ $product->existencias }} unidades</span></td>
                                <td>{{ $product->suppliers->count() }}</td>
                                <td>
                                    <details>
                                        <summary class="admin-secondary-button">Registrar movimiento</summary>
                                        @include('gestion.inventario.crear', ['product' => $product])
                                    </details>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="admin-table-empty">No hay productos activos para consultar.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="admin-pagination">{{ $products->links() }}</div>
        </section>
    </section>
@endsection

