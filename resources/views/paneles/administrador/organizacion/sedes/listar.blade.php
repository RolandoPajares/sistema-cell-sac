@extends('layouts.administracion')

@section('title', 'Administración de sedes')
@section('body_class', 'modulo-organizacion')

@section('content')
<section class="tc-org" data-organizacion="sedes" data-org-create-route="{{ route('management.company_settings.sites.store') }}">
    @include('paneles.administrador.organizacion.componentes.cabecera', [
        'titulo' => 'Administración de sedes',
        'icono' => 'bi-shop',
        'descripcion' => 'Consulta y administra las sedes registradas para '.($empresa?->nombre_comercial ?: $empresa?->razon_social ?: 'la empresa activa').'.',
        'acciones' => $puedeEditar && $empresa !== null
            ? [['texto' => 'Nueva sede', 'icono' => 'bi-person-plus', 'primaria' => true, 'modal' => 'nuevo']]
            : [],
    ])

    @if (session('status'))
        <div class="tc-org__alert tc-org__alert--success" role="status">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="tc-org__alert tc-org__alert--error" role="alert">
            <strong>No se pudo guardar la sede.</strong>
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    @if ($empresa === null)
        <div class="tc-org__alert" role="status">No hay una única empresa activa autorizada. Verifica la empresa antes de registrar sedes.</div>
    @endif

    <div class="tc-org__metrics">
        @include('paneles.administrador.organizacion.componentes.metrica', ['titulo' => 'Total de sedes', 'valor' => $resumen['total'], 'icono' => 'bi-shop', 'detalle' => 'Empresa activa autorizada'])
        @include('paneles.administrador.organizacion.componentes.metrica', ['titulo' => 'Sedes activas', 'valor' => $resumen['activas'], 'icono' => 'bi-shield-check', 'tono' => 'verde', 'detalle' => 'Disponibles para operaciones'])
        @include('paneles.administrador.organizacion.componentes.metrica', ['titulo' => 'Sedes inactivas', 'valor' => $resumen['inactivas'], 'icono' => 'bi-shop-window', 'tono' => 'violeta', 'detalle' => 'Conservadas en MariaDB'])
        @include('paneles.administrador.organizacion.componentes.metrica', ['titulo' => 'Almacenes asociados', 'valor' => $totalAlmacenes, 'icono' => 'bi-box-seam', 'detalle' => 'Conteo en las sedes de la empresa'])
    </div>

    <form class="tc-org__filters" method="get" action="{{ route('management.company_settings.sites') }}">
        <div class="tc-org__filter-top">
            <label class="tc-org__search"><i class="bi bi-search" aria-hidden="true"></i><input type="search" name="buscar" value="{{ $filtros['buscar'] ?? '' }}" placeholder="Buscar por código, sede o dirección" aria-label="Buscar sedes"></label>
            <button class="tc-org__button tc-org__button--apply" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Aplicar filtros</button>
            <a class="tc-org__button" href="{{ route('management.company_settings.sites') }}"><i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i> Limpiar</a>
        </div>
        <div class="tc-org__filter-fields">
            <label><span>Estado</span><select name="estado"><option value="">Todos los estados</option><option value="activa" @selected(($filtros['estado'] ?? '') === 'activa')>Activa</option><option value="inactiva" @selected(($filtros['estado'] ?? '') === 'inactiva')>Inactiva</option></select></label>
        </div>
    </form>

    <article class="tc-org__card tc-org__table-card">
        <div class="tc-org__card-title"><div><h2>Sedes registradas ({{ $sedes->total() }})</h2><p>Mostrando {{ $sedes->count() }} registros consultados en MariaDB.</p></div></div>
        <div class="tc-org__table-wrap"><table><thead><tr><th>#</th><th>Sede</th><th>Código</th><th>Dirección</th><th>Teléfono</th><th>Zona horaria</th><th>Almacenes</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
                @forelse ($sedes as $sede)
                    <tr data-org-row
                        data-org-nombre="{{ $sede->nombre }}"
                        data-org-codigo="{{ $sede->codigo }}"
                        data-org-direccion="{{ $sede->direccion }}"
                        data-org-telefono="{{ $sede->telefono }}"
                        data-org-zona-horaria="{{ $sede->zona_horaria }}"
                        data-org-activa="{{ $sede->activa ? '1' : '0' }}"
                        data-org-update-url="{{ route('management.company_settings.sites.update', $sede) }}"
                        data-org-status-url="{{ route('management.company_settings.sites.status', $sede) }}">
                        <td>{{ $sedes->firstItem() + $loop->index }}</td>
                        <td>{{ $sede->nombre }}</td>
                        <td>{{ $sede->codigo }}</td>
                        <td>{{ $sede->direccion ?: '—' }}</td>
                        <td>{{ $sede->telefono ?: '—' }}</td>
                        <td>{{ $sede->zona_horaria }}</td>
                        <td>{{ (int) $sede->almacenes_count }}</td>
                        <td>{{ $sede->activa ? 'Activa' : 'Inactiva' }}</td>
                        <td class="tc-org__row-actions">
                            <button type="button" data-org-open="detalle" aria-label="Ver {{ $sede->nombre }}"><i class="bi bi-eye" aria-hidden="true"></i></button>
                            @if ($puedeEditar)
                                <button type="button" data-org-open="editar" aria-label="Editar {{ $sede->nombre }}"><i class="bi bi-pencil" aria-hidden="true"></i></button>
                                <button type="button" data-org-open="estado" aria-label="Cambiar estado de {{ $sede->nombre }}"><i class="bi bi-three-dots-vertical" aria-hidden="true"></i></button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9"><p class="tc-org__no-results">No hay sedes registradas que coincidan con los filtros.</p></td></tr>
                @endforelse
            </tbody>
        </table></div>
        @if ($sedes->hasPages())
            <nav class="pagination" aria-label="Paginación de sedes">{{ $sedes->links() }}</nav>
        @endif
    </article>

    <div class="tc-org__grid tc-org__grid--halves">
        <article class="tc-org__card"><h2 class="tc-org__section-heading"><i class="bi bi-box-seam" aria-hidden="true"></i> Almacenes por sede</h2><p class="tc-org__section-copy">Conteos obtenidos de los almacenes asociados a cada sede.</p>
            @forelse ($almacenesPorSede as $resumenSede)
                <div class="tc-org__key-list"><div><span>{{ $resumenSede->nombre }}</span><strong>{{ (int) $resumenSede->almacenes_count }}</strong></div></div>
            @empty
                <p class="tc-org__no-results">No hay sedes registradas para resumir.</p>
            @endforelse
        </article>
        <article class="tc-org__card"><h2 class="tc-org__section-heading"><i class="bi bi-info-circle" aria-hidden="true"></i> Datos disponibles</h2><p class="tc-org__section-copy">Cada sede utiliza los campos definidos por el esquema vigente.</p><p>Código, nombre, dirección, teléfono, zona horaria y estado operativo.</p></article>
    </div>

    @include('paneles.administrador.organizacion.componentes.modales', ['tipo' => 'sedes'])
</section>
@endsection
