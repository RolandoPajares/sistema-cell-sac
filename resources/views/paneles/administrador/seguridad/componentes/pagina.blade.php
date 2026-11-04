@php
    $accionesConModal = [
        'usuarios' => [0 => 'usuario-crear'],
        'roles' => [0 => 'rol-crear'],
        'asignaciones' => [0 => 'asignacion-usuario', 1 => 'asignacion-masiva'],
        'asignaciones-sede' => [0 => 'asignacion-sede', 1 => 'asignacion-masiva-sede'],
        'modulos' => [0 => 'modulo-crear', 1 => 'pantalla-crear'],
        'politicas' => [0 => 'politica-crear', 1 => 'delegacion-crear'],
        'sesiones' => [0 => 'sesiones-revocar-todas'],
        'bloqueos' => [0 => 'cuenta-bloquear'],
    ];
@endphp
<section class="tc-sec" data-seguridad-diseno data-sec-module="{{ $pagina['modulo'] }}">
    <header class="tc-sec__header">
        <div>
            <span class="tc-sec__breadcrumb"><i class="bi bi-house" aria-hidden="true"></i> Seguridad y Delegación <i class="bi bi-chevron-right" aria-hidden="true"></i> {{ $pagina['titulo'] }}</span>
            <h1><i class="bi {{ $pagina['icono'] }}" aria-hidden="true"></i> {{ $pagina['titulo'] }}</h1>
            <p>{{ $pagina['descripcion'] }}</p>
        </div>
        <div class="tc-sec__header-actions">
            @foreach (($pagina['acciones'] ?? []) as $indiceAccion => $accion)
                @php($modalAccion = $accionesConModal[$pagina['modulo']][$indiceAccion] ?? null)
                <button type="button" class="tc-sec__button {{ ($accion['primaria'] ?? false) ? 'tc-sec__button--primary' : '' }}" @if ($modalAccion) data-sec-modal-open="{{ $modalAccion }}" @else disabled title="Acción disponible en la gestión conectada" @endif><i class="bi {{ $accion['icono'] }}" aria-hidden="true"></i>{{ $accion['texto'] }}</button>
            @endforeach
        </div>
    </header>
    <div class="tc-sec__notice" role="status"><i class="bi bi-eye" aria-hidden="true"></i><strong>Vista de diseño</strong><span>Cifras y registros de ejemplo. Las operaciones nuevas no guardan datos.</span>@if (isset($pagina['rutaGestion']))<a href="{{ route($pagina['rutaGestion']) }}">Abrir gestión actual <i class="bi bi-arrow-right"></i></a>@endif</div>
    <div class="tc-sec__feedback" data-sec-feedback role="status" aria-live="polite" hidden></div>
    <div class="tc-sec__metrics">
        @foreach ($pagina['metricas'] as $metrica)
            <article class="tc-sec__metric tc-sec__metric--{{ $metrica['tono'] ?? 'azul' }}"><span class="tc-sec__metric-icon"><i class="bi {{ $metrica['icono'] }}" aria-hidden="true"></i></span><div><small>{{ $metrica['titulo'] }}</small><strong>{{ $metrica['valor'] }}</strong></div><span class="tc-sec__metric-trend">{{ $metrica['detalle'] ?? 'Ejemplo' }}</span></article>
        @endforeach
    </div>
    <div class="tc-sec__body-grid {{ isset($pagina['panelLateral']) ? 'tc-sec__body-grid--aside' : '' }}">
        <div class="tc-sec__body-main">
            @if (isset($pagina['buscar']))
                <div class="tc-sec__filters"><label class="tc-sec__search"><i class="bi bi-search" aria-hidden="true"></i><input type="search" data-sec-search placeholder="{{ $pagina['buscar'] }}" aria-label="{{ $pagina['buscar'] }}"></label>
                    <div class="tc-sec__filter-fields">@foreach ($pagina['filtros'] ?? [] as $filtro)<label><span>{{ $filtro['titulo'] }}</span><select data-sec-filter="{{ $filtro['campo'] }}"><option value="">{{ $filtro['todos'] }}</option>@foreach ($filtro['opciones'] as $opcion)<option value="{{ $opcion }}">{{ $opcion }}</option>@endforeach</select></label>@endforeach<button type="button" class="tc-sec__button tc-sec__button--primary" data-sec-apply><i class="bi bi-funnel"></i> Aplicar filtros</button></div>
                </div>
            @endif
            <article class="tc-sec__card tc-sec__table-card"><div class="tc-sec__card-heading"><h2><i class="bi {{ $pagina['tablaIcono'] ?? 'bi-list-ul' }}" aria-hidden="true"></i>{{ $pagina['tablaTitulo'] }}</h2><small>Mostrando {{ count($pagina['filas']) }} registros de ejemplo</small></div><div class="tc-sec__table-wrap"><table><thead><tr>@foreach ($pagina['columnas'] as $columna)<th>{{ $columna }}</th>@endforeach<th>Acciones</th></tr></thead><tbody>@foreach ($pagina['filas'] as $fila)<tr data-sec-row @foreach ($fila['filtros'] ?? [] as $campo => $valor) data-sec-{{ $campo }}="{{ $valor }}" @endforeach>@foreach ($fila['celdas'] as $celda)<td>@if (is_array($celda))<span class="tc-sec__pill tc-sec__pill--{{ $celda['tono'] ?? 'azul' }}">{{ $celda['texto'] }}</span>@else{{ $celda }}@endif</td>@endforeach<td><div class="tc-sec__row-actions"><button type="button" data-sec-row-action="detail" aria-label="Ver registro de ejemplo"><i class="bi bi-eye" aria-hidden="true"></i> Ver</button><button type="button" data-sec-row-action="edit" aria-label="Abrir edición demostrativa"><i class="bi bi-pencil" aria-hidden="true"></i> Editar</button><button type="button" data-sec-row-action="danger" aria-label="Abrir confirmación demostrativa"><i class="bi bi-three-dots" aria-hidden="true"></i></button></div></td></tr>@endforeach</tbody></table></div><p class="tc-sec__empty" data-sec-empty hidden>No hay ejemplos que coincidan con los filtros.</p></article>
            @if (isset($pagina['paneles']))@include('paneles.administrador.seguridad.componentes.paneles', ['paneles' => $pagina['paneles']])@endif
        </div>
        @if (isset($pagina['panelLateral']))@include($pagina['panelLateral'])@endif
    </div>
    @include('paneles.administrador.seguridad.componentes.modales')
</section>
