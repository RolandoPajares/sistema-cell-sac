@extends('layouts.administracion')

@section('title', 'Personal por sede')
@section('body_class', 'modulo-organizacion')

@section('content')
<section class="tc-org" data-organizacion="personal">
    @include('paneles.administrador.organizacion.componentes.cabecera', [
        'titulo' => 'Personal por sede',
        'icono' => 'bi-people-fill',
        'descripcion' => 'Consulta las asignaciones vigentes de usuarios a roles y sedes autorizadas.',
        'acciones' => [
            ['texto' => 'Asignar rol a una sede', 'icono' => 'bi-diagram-3', 'ruta' => route('management.user_assignments')],
            ['texto' => 'Nuevo usuario', 'icono' => 'bi-person-plus', 'primaria' => true, 'ruta' => route('management.users')],
        ],
    ])

    @if (session('status'))
        <div class="tc-org__alert tc-org__alert--success" role="status">{{ session('status') }}</div>
    @endif

    <div class="tc-org__metrics">
        @include('paneles.administrador.organizacion.componentes.metrica', ['titulo' => 'Usuarios asignados', 'valor' => $resumen['personal_total'], 'icono' => 'bi-people', 'detalle' => 'Registros reales de usuarios por sede'])
        @include('paneles.administrador.organizacion.componentes.metrica', ['titulo' => 'Asignaciones vigentes', 'valor' => $resumen['asignaciones_vigentes'], 'icono' => 'bi-shield-check', 'tono' => 'verde', 'detalle' => 'Cuenta, rol, sede y vigencia activos'])
        @include('paneles.administrador.organizacion.componentes.metrica', ['titulo' => 'Sedes con personal', 'valor' => $resumen['sedes_con_personal'], 'icono' => 'bi-buildings', 'tono' => 'violeta', 'detalle' => 'Dentro del alcance autorizado'])
        @include('paneles.administrador.organizacion.componentes.metrica', ['titulo' => 'Roles asignados', 'valor' => $resumen['roles_vigentes'], 'icono' => 'bi-person-badge', 'detalle' => 'Roles existentes del catálogo RBAC'])
    </div>

    <form class="tc-org__filters" method="get" action="{{ route('management.company_settings.staff') }}">
        <div class="tc-org__filter-top">
            <label class="tc-org__search">
                <i class="bi bi-search" aria-hidden="true"></i>
                <input type="search" name="buscar" value="{{ $filtros['buscar'] ?? '' }}" placeholder="Buscar usuario, correo, rol o sede" aria-label="Buscar personal por sede">
            </label>
            <button class="tc-org__button tc-org__button--apply" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Aplicar filtros</button>
            <a class="tc-org__button" href="{{ route('management.company_settings.staff') }}"><i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i> Limpiar</a>
        </div>
        <div class="tc-org__filter-fields">
            <label><span>Sede autorizada</span><select name="sede_id"><option value="">Todas las sedes autorizadas</option>@foreach ($sedesAutorizadas as $sede)<option value="{{ $sede->id }}" @selected((string) ($filtros['sede_id'] ?? '') === (string) $sede->id)>{{ $sede->nombre }} · {{ $sede->codigo }}</option>@endforeach</select></label>
            <label><span>Vigencia</span><select name="estado"><option value="">Todas</option><option value="vigente" @selected(($filtros['estado'] ?? '') === 'vigente')>Vigentes</option><option value="no_vigente" @selected(($filtros['estado'] ?? '') === 'no_vigente')>No vigentes</option></select></label>
            <label><span>Filas por página</span><select name="por_pagina"><option value="15" @selected((int) ($filtros['por_pagina'] ?? 15) === 15)>15</option><option value="30" @selected((int) ($filtros['por_pagina'] ?? 15) === 30)>30</option><option value="50" @selected((int) ($filtros['por_pagina'] ?? 15) === 50)>50</option></select></label>
        </div>
    </form>

    <article class="tc-org__card tc-org__table-card">
        <div class="tc-org__card-title"><div><h2>Asignaciones de personal ({{ $asignaciones->total() }})</h2><p>Mostrando {{ $asignaciones->count() }} asignaciones consultadas en MariaDB.</p></div></div>
        <div class="tc-org__table-wrap"><table><thead><tr><th>#</th><th>Usuario</th><th>Correo</th><th>Rol</th><th>Sede</th><th>Asignado desde</th><th>Vigente hasta</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
                @forelse ($asignaciones as $asignacion)
                    <tr>
                        <td>{{ $asignaciones->firstItem() + $loop->index }}</td>
                        <td>{{ $asignacion->usuario_nombre }}</td>
                        <td>{{ $asignacion->usuario_correo }}</td>
                        <td>{{ $asignacion->rol_nombre }}</td>
                        <td>{{ $asignacion->sede_nombre }}</td>
                        <td>{{ $asignacion->asignado_en ? \Illuminate\Support\Carbon::parse($asignacion->asignado_en)->format('d/m/Y') : '—' }}</td>
                        <td>{{ $asignacion->vigente_hasta ? \Illuminate\Support\Carbon::parse($asignacion->vigente_hasta)->format('d/m/Y') : 'Sin vencimiento' }}</td>
                        <td>{{ $asignacion->vigente ? 'Vigente' : 'No vigente' }}</td>
                        <td class="tc-org__row-actions"><button type="button" data-org-link="{{ route('management.user_assignments') }}" aria-label="Administrar asignaciones de {{ $asignacion->usuario_nombre }}"><i class="bi bi-person-gear" aria-hidden="true"></i></button></td>
                    </tr>
                @empty
                    <tr><td colspan="9"><p class="tc-org__no-results">No hay asignaciones de personal que coincidan con los filtros. Puedes crear usuarios y asignarlos desde los módulos de usuarios y asignaciones.</p></td></tr>
                @endforelse
            </tbody>
        </table></div>
        @if ($asignaciones->hasPages())
            <nav class="pagination" aria-label="Paginación de personal por sede">{{ $asignaciones->links() }}</nav>
        @endif
    </article>

    <div class="tc-org__grid tc-org__grid--halves">
        <article class="tc-org__card"><h2 class="tc-org__section-heading"><i class="bi bi-buildings" aria-hidden="true"></i> Personal vigente por sede</h2><p class="tc-org__section-copy">Conteos derivados de las asignaciones activas de usuarios.</p>
            @forelse ($personalPorSede as $resumenSede)
                <div class="tc-org__key-list"><div><span>{{ $resumenSede->nombre }}</span><strong>{{ (int) $resumenSede->total_personal }}</strong></div></div>
            @empty
                <p class="tc-org__no-results">No hay personal asignado en las sedes autorizadas.</p>
            @endforelse
        </article>
        <article class="tc-org__card"><h2 class="tc-org__section-heading"><i class="bi bi-person-badge" aria-hidden="true"></i> Roles vigentes</h2><p class="tc-org__section-copy">Distribución de usuarios por los roles reales del catálogo.</p>
            @forelse ($personalPorRol as $resumenRol)
                <div class="tc-org__key-list"><div><span>{{ $resumenRol->nombre }}</span><strong>{{ (int) $resumenRol->total_personal }}</strong></div></div>
            @empty
                <p class="tc-org__no-results">Todavía no existen asignaciones vigentes de roles por sede.</p>
            @endforelse
        </article>
    </div>
</section>
@endsection
