@extends('layouts.administracion')

@section('title', 'Proveedores')

@section('content')
<section class="admin-page" data-admin-table-container>
    <div class="admin-page-head">
        <div>
            <span class="product-category">Compras y logística</span>
            <h1>Proveedores</h1>
            <p>Administra los proveedores vinculados al catálogo y a las compras.</p>
        </div>
    </div>

    <div class="two-column">
        @include('gestion.proveedores.crear')

        <div class="form-card">
            <h2>Proveedores registrados</h2>
            @forelse($suppliers as $supplier)
                <article class="data-row" data-data-row>
                    <div>
                        <b>{{ $supplier->nombre }}</b>
                        <small>RUC {{ $supplier->ruc }} · {{ $supplier->correo }} · {{ $supplier->activo ? 'Activo' : 'Inactivo' }}</small>
                    </div>
                    <div class="inline-actions">
                        <details>
                            <summary class="btn btn-secondary">Editar</summary>
                            @include('gestion.proveedores.editar', ['supplier' => $supplier])
                        </details>
                        @if($supplier->activo)
                            <form method="post" action="{{ route('management.suppliers.deactivate', $supplier) }}">
                                @csrf
                                @method('DELETE')
                                <button
                                    class="btn btn-secondary"
                                    data-confirm="¿Desactivar este proveedor?"
                                >
                                    Desactivar
                                </button>
                            </form>
                        @endif
                    </div>
                </article>
            @empty
                <p>No hay proveedores registrados.</p>
            @endforelse
            {{ $suppliers->links() }}
        </div>
    </div>
</section>
@endsection

