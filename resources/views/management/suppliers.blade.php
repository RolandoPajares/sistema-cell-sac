@extends('layouts.admin')
@push('styles')<link rel="stylesheet" href="{{ asset('assets/cell-legacy/css/roles/internos/compras-logistica.css') }}">@endpush

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
        <form method="post" class="form-card" action="{{ route('management.suppliers.store') }}">
            @csrf
            <h2>Registrar proveedor</h2>
            <label>Nombre<input name="nombre" required maxlength="160" value="{{ old('nombre') }}"></label>
            <label>RUC<input name="ruc" required maxlength="20" value="{{ old('ruc') }}"></label>
            <label>Correo<input type="email" name="correo" required maxlength="160" value="{{ old('correo') }}"></label>
            <label>Teléfono<input name="telefono" maxlength="30" value="{{ old('telefono') }}"></label>
            <label>Ciudad<input name="ciudad" maxlength="80" value="{{ old('ciudad') }}"></label>
            <button class="btn btn-primary">Guardar proveedor</button>
        </form>

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
                            <form method="post" class="form-card compact-form" action="{{ route('management.suppliers.update', $supplier) }}">
                                @csrf
                                @method('PUT')
                                <label>Nombre<input name="nombre" required maxlength="160" value="{{ $supplier->nombre }}"></label>
                                <label>RUC<input name="ruc" required maxlength="20" value="{{ $supplier->ruc }}"></label>
                                <label>Correo<input type="email" name="correo" required maxlength="160" value="{{ $supplier->correo }}"></label>
                                <label>Teléfono<input name="telefono" maxlength="30" value="{{ $supplier->telefono }}"></label>
                                <label>Ciudad<input name="ciudad" maxlength="80" value="{{ $supplier->ciudad }}"></label>
                                <button class="btn btn-primary">Actualizar proveedor</button>
                            </form>
                        </details>
                        @if($supplier->activo)
                            <form method="post" action="{{ route('management.suppliers.deactivate', $supplier) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-secondary" data-confirm="¿Desactivar este proveedor?">Desactivar</button>
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
