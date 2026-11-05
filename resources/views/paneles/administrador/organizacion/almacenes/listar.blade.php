@extends('layouts.administracion')

@section('title', 'Almacenes')
@section('body_class', 'modulo-organizacion')

@section('content')
<section class="tc-org" data-organizacion="almacenes" data-org-create-route="{{ route('management.company_settings.warehouses.store') }}">
    @include('paneles.administrador.organizacion.componentes.cabecera', [
        'titulo' => 'Almacenes',
        'icono' => 'bi-box-seam',
        'descripcion' => 'Consulta y administra almacenes registrados por sede. Las existencias provienen del inventario operativo.',
        'acciones' => $puedeEditar && $sedesActivas->isNotEmpty()
            ? [['texto' => 'Nuevo almacén', 'icono' => 'bi-box-seam', 'primaria' => true, 'modal' => 'nuevo']]
            : [],
    ])

    @if (session('status'))
        <div class="tc-org__alert tc-org__alert--success" role="status">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="tc-org__alert tc-org__alert--error" role="alert">
            <strong>No se pudo guardar el almacén.</strong>
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    @if ($sedesActivas->isEmpty())
        <div class="tc-org__alert" role="status">No hay sedes activas autorizadas. Registra o activa una sede antes de crear un almacén.</div>
    @endif

    <div class="tc-org__metrics">
        @include('paneles.administrador.organizacion.componentes.metrica', ['titulo' => 'Almacenes registrados', 'valor' => $resumen['total'], 'icono' => 'bi-box-seam', 'detalle' => 'Dentro de las sedes autorizadas'])
        @include('paneles.administrador.organizacion.componentes.metrica', ['titulo' => 'Almacenes activos', 'valor' => $resumen['activos'], 'icono' => 'bi-check-circle', 'tono' => 'verde', 'detalle' => $resumen['habilitados_venta'].' habilitan ventas'])
        @include('paneles.administrador.organizacion.componentes.metrica', ['titulo' => 'Existencias físicas', 'valor' => number_format($resumen['unidades_fisicas'], 2, '.', ','), 'icono' => 'bi-boxes', 'tono' => 'violeta', 'detalle' => $resumen['productos_con_existencias'].' productos con saldo'])
        @include('paneles.administrador.organizacion.componentes.metrica', ['titulo' => 'Sedes con almacenes', 'valor' => $resumen['sedes_con_almacenes'], 'icono' => 'bi-buildings', 'detalle' => 'De '.$sedes->count().' sedes autorizadas'])
    </div>

    <form class="tc-org__filters" method="get" action="{{ route('management.company_settings.warehouses') }}">
        <div class="tc-org__filter-top">
            <label class="tc-org__search"><i class="bi bi-search" aria-hidden="true"></i><input type="search" name="buscar" value="{{ $filtros['buscar'] ?? '' }}" placeholder="Buscar por almacén, código o sede" aria-label="Buscar almacenes"></label>
            <button class="tc-org__button tc-org__button--apply" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Aplicar filtros</button>
            <a class="tc-org__button" href="{{ route('management.company_settings.warehouses') }}"><i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i> Limpiar</a>
        </div>
        <div class="tc-org__filter-fields">
            <label><span>Sede</span><select name="sede_id"><option value="">Todas las sedes</option>@foreach ($sedes as $sede)<option value="{{ $sede->id }}" @selected((string) ($filtros['sede_id'] ?? '') === (string) $sede->id)>{{ $sede->nombre }} · {{ $sede->codigo }}</option>@endforeach</select></label>
            <label><span>Tipo</span><select name="tipo"><option value="">Todos los tipos</option>@foreach ($tipos as $tipo)<option value="{{ $tipo }}" @selected(($filtros['tipo'] ?? '') === $tipo)>{{ $tipo }}</option>@endforeach</select></label>
            <label><span>Estado</span><select name="estado"><option value="">Todos</option><option value="activo" @selected(($filtros['estado'] ?? '') === 'activo')>Activo</option><option value="inactivo" @selected(($filtros['estado'] ?? '') === 'inactivo')>Inactivo</option></select></label>
        </div>
    </form>

    <article class="tc-org__card tc-org__table-card">
        <div class="tc-org__card-title"><div><h2>Almacenes registrados ({{ $almacenes->total() }})</h2><p>Mostrando {{ $almacenes->count() }} registros consultados en MariaDB.</p></div></div>
        <div class="tc-org__table-wrap"><table><thead><tr><th>#</th><th>Almacén</th><th>Código</th><th>Sede</th><th>Tipo</th><th>Existencias físicas</th><th>Productos con saldo</th><th>Permite ventas</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
                @forelse ($almacenes as $almacen)
                    <tr data-org-row
                        data-org-nombre="{{ $almacen->nombre }}"
                        data-org-codigo="{{ $almacen->codigo }}"
                        data-org-sede-id="{{ $almacen->sede_id }}"
                        data-org-tipo="{{ $almacen->tipo }}"
                        data-org-permite-venta="{{ $almacen->permite_venta ? '1' : '0' }}"
                        data-org-activo="{{ $almacen->activo ? '1' : '0' }}"
                        data-org-update-url="{{ route('management.company_settings.warehouses.update', $almacen) }}"
                        data-org-status-url="{{ route('management.company_settings.warehouses.status', $almacen) }}">
                        <td>{{ $almacenes->firstItem() + $loop->index }}</td>
                        <td>{{ $almacen->nombre }}</td>
                        <td>{{ $almacen->codigo }}</td>
                        <td>{{ $almacen->sede?->nombre ?? 'Sede no disponible' }}</td>
                        <td>{{ $almacen->tipo }}</td>
                        <td>{{ number_format((float) $almacen->unidades_fisicas, 2, '.', ',') }}</td>
                        <td>{{ (int) $almacen->productos_con_existencias }}</td>
                        <td>{{ $almacen->permite_venta ? 'Sí' : 'No' }}</td>
                        <td>{{ $almacen->activo ? 'Activo' : 'Inactivo' }}</td>
                        <td class="tc-org__row-actions">
                            <button type="button" data-org-open="detalle" aria-label="Ver {{ $almacen->nombre }}"><i class="bi bi-eye" aria-hidden="true"></i></button>
                            @if ($puedeEditar)
                                <button type="button" data-org-open="editar" aria-label="Editar {{ $almacen->nombre }}"><i class="bi bi-pencil" aria-hidden="true"></i></button>
                                <button type="button" data-org-open="estado" aria-label="Cambiar estado de {{ $almacen->nombre }}"><i class="bi bi-three-dots-vertical" aria-hidden="true"></i></button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10"><p class="tc-org__no-results">No hay almacenes registrados que coincidan con los filtros.</p></td></tr>
                @endforelse
            </tbody>
        </table></div>
        @if ($almacenes->hasPages())
            <nav class="pagination" aria-label="Paginación de almacenes">{{ $almacenes->links() }}</nav>
        @endif
    </article>

    <div class="tc-org__grid tc-org__grid--thirds">
        <article class="tc-org__card"><h2 class="tc-org__section-heading"><i class="bi bi-boxes" aria-hidden="true"></i> Existencias físicas por almacén</h2><p class="tc-org__section-copy">Saldo operativo sumado desde existencias_almacen.</p>
            @if ($barrasAlmacenes->isEmpty())
                <p class="tc-org__no-results">Todavía no hay almacenes para resumir.</p>
            @else
                <div class="tc-org__bar-list">@foreach ($barrasAlmacenes as $barra)<div><span>{{ $barra['nombre'] }}</span><i><b style="width: {{ $barra['porcentaje_relativo'] }}%"></b></i><strong>{{ number_format($barra['unidades_fisicas'], 2, '.', ',') }}</strong></div>@endforeach</div>
            @endif
        </article>
        <article class="tc-org__card"><h2 class="tc-org__section-heading"><i class="bi bi-buildings" aria-hidden="true"></i> Almacenes por sede</h2><p class="tc-org__section-copy">Conteo y saldo físico para sedes autorizadas.</p>
            @forelse ($resumenSedes as $sede)
                <div class="tc-org__key-list"><div><span>{{ $sede['nombre'] }} · {{ $sede['almacenes'] }} almacenes</span><strong>{{ number_format($sede['unidades_fisicas'], 2, '.', ',') }} u.</strong></div></div>
            @empty
                <p class="tc-org__no-results">No hay sedes autorizadas para mostrar.</p>
            @endforelse
        </article>
        <article class="tc-org__card"><h2 class="tc-org__section-heading"><i class="bi bi-info-circle" aria-hidden="true"></i> Datos del almacén</h2><p class="tc-org__section-copy">La tabla real no contiene capacidad ni responsable asignado; no se muestran estimaciones para esos campos.</p><p>Para consultar movimientos y el kardex, abre Inventario.</p></article>
    </div>

    @include('paneles.administrador.organizacion.componentes.modales', ['tipo' => 'almacenes'])
</section>
@endsection
