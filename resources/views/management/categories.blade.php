@extends('layouts.admin')
@push('styles')<link rel="stylesheet" href="{{ asset('assets/cell-legacy/css/modulos/comunes/panel.css') }}">@endpush
@section('title', 'Categorías')
@section('content')
<section class="admin-page" data-admin-table-container>
    <div class="admin-page-head"><div><span class="product-category">Catálogo</span><h1>Categorías</h1><p>Clasifica el catálogo sin eliminar productos históricos.</p></div></div>
    <div class="two-column">
        <form method="post" action="{{ route('management.categories.store') }}" class="form-card">@csrf<h2>Nueva categoría</h2><label>Nombre<input name="nombre" required maxlength="120"></label><label>Descripción<textarea name="descripcion" maxlength="500"></textarea></label><button class="btn btn-primary">Registrar</button></form>
        <div class="form-card"><h2>Listado</h2>@forelse($categories as $category)<article class="data-row" data-data-row><div><b>{{ $category->nombre }}</b><small>{{ $category->products_count }} producto(s) · {{ $category->activo ? 'Activa' : 'Inactiva' }}</small><details><summary>Editar</summary><form method="post" action="{{ route('management.categories.update', $category) }}" class="form-card compact-form">@csrf @method('PUT')<label>Nombre<input name="nombre" value="{{ $category->nombre }}" required maxlength="120"></label><label>Descripción<textarea name="descripcion" maxlength="500">{{ $category->descripcion }}</textarea></label><button class="btn btn-primary">Guardar cambios</button></form></details></div>@if($category->activo)<form method="post" action="{{ route('management.categories.deactivate', $category) }}">@csrf @method('DELETE')<button class="btn btn-secondary" data-confirm="¿Desactivar esta categoría?">Desactivar</button></form>@endif</article>@empty<p>No hay categorías registradas.</p>@endforelse{{ $categories->links() }}</div>
    </div>
</section>
@endsection
