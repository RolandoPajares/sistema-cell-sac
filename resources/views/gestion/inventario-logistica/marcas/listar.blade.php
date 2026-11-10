@extends('layouts.panel.principal')

@section('title', 'Marcas')
@section('body_class', 'modulo-marcas')

@section('content')
<section class="marcas-page" data-marcas-page>
    <header class="marcas-hero">
        <div>
            <span class="marcas-breadcrumb"><i class="bi bi-house" aria-hidden="true"></i> / Inventarios / <strong>Marcas</strong></span>
            <h1>Gestión de marcas <i class="bi bi-tags" aria-hidden="true"></i></h1>
            <p>Administra las marcas de tus productos: crea, edita, activa o desactiva.</p>
        </div>
        <button class="marcas-primary-button" type="button" data-open-brand-create><i class="bi bi-plus-lg" aria-hidden="true"></i> Nueva marca</button>
    </header>

    <div class="marcas-kpi-grid">
        <article><span class="marcas-kpi-icon is-blue"><i class="bi bi-tags" aria-hidden="true"></i></span><div><small>Total de marcas</small><strong>{{ number_format($total) }}</strong></div></article>
        <article><span class="marcas-kpi-icon is-green"><i class="bi bi-check-circle" aria-hidden="true"></i></span><div><small>Marcas activas</small><strong>{{ number_format($activas) }}</strong></div></article>
        <article><span class="marcas-kpi-icon is-red"><i class="bi bi-slash-circle" aria-hidden="true"></i></span><div><small>Marcas inactivas</small><strong>{{ number_format($inactivas) }}</strong></div></article>
        <article><span class="marcas-kpi-icon is-violet"><i class="bi bi-box-seam" aria-hidden="true"></i></span><div><small>Productos registrados</small><strong>{{ number_format($productos) }}</strong></div></article>
    </div>

    <form class="marcas-filters" method="get" action="{{ route('management.brands') }}">
        <label><i class="bi bi-search" aria-hidden="true"></i><span class="visually-hidden">Buscar marca</span><input type="search" name="buscar" value="{{ $buscar }}" placeholder="Buscar marca..."></label>
        <label><span>Estado</span><select name="estado"><option value="">Todos</option><option value="activa" @selected($estado === 'activa')>Activas</option><option value="inactiva" @selected($estado === 'inactiva')>Inactivas</option></select></label>
        <a href="{{ route('management.brands') }}"><i class="bi bi-arrow-repeat" aria-hidden="true"></i> Limpiar</a>
    </form>

    <section class="marcas-table-card">
        <div class="marcas-table-wrap"><table class="marcas-table"><thead><tr><th>#</th><th>Logo</th><th>Marca</th><th>Descripción</th><th>Estado</th><th>Productos</th><th>Acciones</th></tr></thead><tbody>
            @forelse ($marcas as $marca)
                <tr><td>{{ (($marcas->currentPage() - 1) * $marcas->perPage()) + $loop->iteration }}</td><td><span class="marcas-logo"><i class="bi bi-tags" aria-hidden="true"></i></span></td><td><strong>{{ $marca->nombre }}</strong></td><td>{{ $marca->descripcion ?: 'Sin descripción registrada.' }}</td><td><span class="marcas-status {{ $marca->activo ? 'is-active' : 'is-inactive' }}"><i class="bi bi-circle-fill" aria-hidden="true"></i>{{ $marca->activo ? 'Activa' : 'Inactiva' }}</span></td><td>{{ number_format($marca->productos_count) }}</td><td class="marcas-actions"><button type="button" title="Editar marca" data-open-brand-edit="{{ $marca->id }}"><i class="bi bi-pencil" aria-hidden="true"></i></button>@if ($marca->activo)<form method="post" action="{{ route('management.brands.deactivate', $marca) }}">@csrf @method('DELETE')<button type="submit" class="is-danger" title="Desactivar marca" data-confirm="¿Desactivar esta marca?"><i class="bi bi-trash3" aria-hidden="true"></i></button></form>@endif</td></tr>
            @empty
                <tr><td colspan="7" class="marcas-empty"><i class="bi bi-tags" aria-hidden="true"></i><strong>No hay marcas registradas.</strong><span>Registra la primera marca para usarla en productos.</span></td></tr>
            @endforelse
        </tbody></table></div>
        <footer class="marcas-pagination"><span>Mostrando {{ $marcas->firstItem() ?? 0 }} a {{ $marcas->lastItem() ?? 0 }} de {{ $marcas->total() }} marcas</span>{{ $marcas->onEachSide(1)->links() }}</footer>
    </section>
</section>

<dialog class="marcas-dialog" id="nueva-marca" aria-labelledby="marca-crear-titulo"><div class="marcas-dialog-card"><header><div><span>Inventarios</span><h2 id="marca-crear-titulo">Registrar marca</h2><p>La marca quedará disponible en el formulario de productos.</p></div><button type="button" data-close-brand-dialog aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header><form method="post" action="{{ route('management.brands.store') }}" class="marcas-form">@csrf @include('gestion.inventario-logistica.marcas.formulario', ['marca' => null])<footer><button type="button" data-close-brand-dialog>Cancelar</button><button type="submit"><i class="bi bi-save" aria-hidden="true"></i> Guardar marca</button></footer></form></div></dialog>

@foreach ($marcas as $marca)
<dialog class="marcas-dialog" id="editar-marca-{{ $marca->id }}" aria-labelledby="marca-editar-titulo-{{ $marca->id }}"><div class="marcas-dialog-card"><header><div><span>Inventarios</span><h2 id="marca-editar-titulo-{{ $marca->id }}">Editar marca</h2><p>Actualiza la información de {{ $marca->nombre }}.</p></div><button type="button" data-close-brand-dialog aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header><form method="post" action="{{ route('management.brands.update', $marca) }}" class="marcas-form">@csrf @method('PUT') @include('gestion.inventario-logistica.marcas.formulario', ['marca' => $marca])<footer><button type="button" data-close-brand-dialog>Cancelar</button><button type="submit"><i class="bi bi-save" aria-hidden="true"></i> Guardar cambios</button></footer></form></div></dialog>
@endforeach
@endsection

@vite(['resources/css/gestion/inventario-logistica/marcas.css', 'resources/js/gestion/inventario-logistica/marcas/index.js'])
