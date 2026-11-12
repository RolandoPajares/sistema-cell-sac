@extends('layouts.panel.principal')


{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('title', 'Devoluciones y garantías')
{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('body_class', 'modulo-devoluciones-garantias')

{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('content')
{{-- El controlador entrega filas, indicadores, filtros, paginación y permisos ya preparados. --}}
<section class="gestion-pendiente-page gestion-operacion-page gestion-operacion-page--{{ $module['key'] }}" data-pending-module="{{ $module['key'] }}">
    {{-- Encabezado, navegación y acciones principales del módulo. --}}
    <header class="gestion-operacion-header">
        <nav class="gestion-operacion-breadcrumb" aria-label="Ruta de navegación">
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
            @foreach ($module['operacion_ui']['breadcrumb'] as $breadcrumb)
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
                @if (! $loop->first)<i class="bi bi-chevron-right" aria-hidden="true"></i>@endif
                <span class="{{ $loop->last ? 'is-current' : '' }}">{{ $breadcrumb }}</span>
            @endforeach
        </nav>
        <div class="gestion-operacion-heading-row">
            <div class="gestion-operacion-heading">
                <span class="gestion-pendiente-eyebrow"><i class="bi {{ $module['icon'] }}" aria-hidden="true"></i> {{ $module['area'] }}</span>
                <h1>{{ $module['operacion_ui']['title'] }}</h1>
                <p>{{ $module['operacion_ui']['description'] }}</p>
            </div>
            <div class="gestion-operacion-actions">
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
                @foreach ($module['operacion_ui']['actions'] as $action)
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
                    @if (isset($action['route'], $action['formats']))
                        <details class="gestion-pendiente-export-menu">
                            <summary class="gestion-pendiente-secondary"><i class="bi {{ $action['icon'] }}" aria-hidden="true"></i> {{ $action['label'] }} <i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
                            <div class="gestion-pendiente-export-options">
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
                                @foreach ($action['formats'] as $format)
                                    <a href="{{ route($action['route'], array_merge(request()->query(), ['format' => $format])) }}">{{ strtoupper($format) }}</a>
                                @endforeach
                            </div>
                        </details>
{{-- alternativa del condicional cuando la condicion anterior no se cumple. --}}
                    @elseif (isset($action['route']))
                        <a class="gestion-pendiente-secondary" href="{{ route($action['route'], request()->query()) }}"><i class="bi {{ $action['icon'] }}" aria-hidden="true"></i> {{ $action['label'] }}</a>
{{-- alternativa del condicional cuando la condicion anterior no se cumple. --}}
                    @elseif (($action['modal'] ?? null) === 'crear')
                        <button class="gestion-pendiente-primary" type="button" data-pending-open="crear"><i class="bi {{ $action['icon'] }}" aria-hidden="true"></i> {{ $action['label'] }}</button>
{{-- alternativa del condicional cuando la condicion anterior no se cumple. --}}
                    @else
                        <button class="gestion-pendiente-secondary" type="button" data-pending-notice="{{ $action['notice'] }}"><i class="bi {{ $action['icon'] }}" aria-hidden="true"></i> {{ $action['label'] }}</button>
                    @endif
                @endforeach
            </div>
        </div>
    </header>

    {{-- Mensajes del resultado de las operaciones y validaciones del servidor. --}}
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
    @if (session('status'))
        <div class="gestion-operacion-feedback gestion-operacion-feedback--success" role="status">{{ session('status') }}</div>
    @endif
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
    @if ($errors->any())
        <div class="gestion-operacion-feedback gestion-operacion-feedback--error" role="alert">{{ $errors->first() }}</div>
    @endif

    {{-- Indicadores resumidos del estado de las solicitudes. --}}
    <section class="gestion-operacion-kpis" aria-label="Indicadores del módulo">
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
        @foreach ($module['operacion_ui']['kpis'] as $kpi)
            <article class="gestion-operacion-kpi gestion-operacion-kpi--{{ $kpi['tone'] }}">
                <span class="gestion-operacion-kpi-icon"><i class="bi {{ $kpi['icon'] }}" aria-hidden="true"></i></span>
                <div class="gestion-operacion-kpi-content"><small>{{ $kpi['label'] }}</small><strong>{{ $kpi['value'] ?? '—' }}</strong><span>{{ $kpi['helper'] }}</span></div>
            </article>
        @endforeach
    </section>

    {{-- Filtros enviados al controlador para consultar únicamente registros autorizados. --}}
{{-- formulario que recopila datos y los envia a la ruta indicada. --}}
    <form class="gestion-operacion-filters" method="GET" action="{{ $module['operacion_ui']['filter_action'] }}">
        <label class="gestion-operacion-search"><i class="bi bi-search" aria-hidden="true"></i><span class="visually-hidden">{{ $module['operacion_ui']['search_label'] }}</span><input name="buscar" type="search" value="{{ $module['operacion_ui']['filter_values']['buscar'] ?? '' }}" placeholder="{{ $module['operacion_ui']['search_placeholder'] }}"></label>
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
        @foreach ($module['operacion_ui']['filters'] as $filter)
            <label><span class="visually-hidden">{{ $filter['label'] }}</span><select name="{{ $filter['name'] }}"><option value="">{{ $filter['placeholder'] }}</option>@foreach ($filter['options'] as $option)<option value="{{ $option }}" @selected(($module['operacion_ui']['filter_values'][$filter['name']] ?? '') === $option)>{{ $option }}</option>@endforeach</select></label>
        @endforeach
        <button class="gestion-operacion-filter-button" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Filtrar</button>
        <a class="gestion-operacion-clear-button" href="{{ $module['operacion_ui']['clear_action'] }}">Limpiar filtros</a>
    </form>

    {{-- Tabla principal con filas formateadas por el controlador. --}}
    <section class="gestion-operacion-control-card" aria-labelledby="devoluciones-list-title">
        <header class="gestion-operacion-card-header"><div><i class="bi {{ $module['operacion_ui']['table_icon'] }}" aria-hidden="true"></i><h2 id="devoluciones-list-title">{{ $module['operacion_ui']['table_title'] }}</h2><span>{{ $module['operacion_ui']['table_count'] }}</span></div></header>
        <div class="gestion-operacion-table-wrap">
            <table class="gestion-operacion-table">
                <thead><tr>@foreach ($module['operacion_ui']['table_columns'] as $column)<th scope="col">{{ $column }}</th>@endforeach</tr></thead>
                <tbody>
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
                    @forelse ($module['operacion_ui']['rows'] as $row)
                        <tr>
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
                            @foreach ($row['cells'] as $cell)
                                <td>@if (is_array($cell))<span class="gestion-operacion-badge gestion-operacion-badge--{{ $cell['tone'] }}">{{ $cell['value'] }}</span>@else{{ $cell }}@endif</td>
                            @endforeach
                            <td class="gestion-operacion-row-actions"><button type="button" data-pending-open="ver" data-pending-record="{{ base64_encode(json_encode($row['record'], JSON_UNESCAPED_UNICODE)) }}" aria-label="Ver solicitud"><i class="bi bi-eye" aria-hidden="true"></i></button><button type="button" data-pending-open="editar" data-pending-action="{{ $row['edit_url'] }}" data-pending-record="{{ base64_encode(json_encode($row['record'], JSON_UNESCAPED_UNICODE)) }}" aria-label="Editar solicitud"><i class="bi bi-pencil" aria-hidden="true"></i></button><button type="button" data-pending-open="eliminar" data-pending-action="{{ $row['delete_url'] }}" data-pending-record="{{ base64_encode(json_encode($row['record'], JSON_UNESCAPED_UNICODE)) }}" aria-label="Eliminar solicitud"><i class="bi bi-trash3" aria-hidden="true"></i></button></td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($module['operacion_ui']['table_columns']) }}"><div class="gestion-operacion-empty"><span><i class="bi bi-inbox" aria-hidden="true"></i></span><strong>{{ $module['operacion_ui']['empty_title'] }}</strong><p>{{ $module['operacion_ui']['empty_description'] }}</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{-- Paginación generada a partir del resultado paginado del backend. --}}
        <footer class="gestion-operacion-table-footer"><span>{{ $module['operacion_ui']['table_footer'] }}</span><div class="gestion-operacion-pagination" aria-label="Paginación">@if (is_array($module['operacion_ui']['pagination']))<a href="{{ $module['operacion_ui']['pagination']['previous'] ?: '#' }}" aria-label="Página anterior"><i class="bi bi-chevron-left" aria-hidden="true"></i></a>@for ($page = 1; $page <= $module['operacion_ui']['pagination']['last']; $page++)<a href="{{ route($module['operacion_ui']['filter_route'], array_merge(request()->query(), ['page' => $page])) }}" class="{{ $page === $module['operacion_ui']['pagination']['current'] ? 'is-active' : '' }}">{{ $page }}</a>@endfor<a href="{{ $module['operacion_ui']['pagination']['next'] ?: '#' }}" aria-label="Página siguiente"><i class="bi bi-chevron-right" aria-hidden="true"></i></a>@endif</div></footer>
    </section>

    {{-- Resumen de seguimiento preparado por el backend. --}}
    <section class="gestion-operacion-bottom-grid" aria-label="Resumen del módulo">
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
        @foreach ($module['operacion_ui']['panels'] as $panel)
            <article class="gestion-operacion-summary-card"><header><div><span class="gestion-operacion-summary-icon gestion-operacion-summary-icon--{{ $panel['tone'] }}"><i class="bi {{ $panel['icon'] }}" aria-hidden="true"></i></span><div><h2>{{ $panel['title'] }}</h2><p>{{ $panel['subtitle'] }}</p></div></div><button type="button" data-pending-notice="{{ $panel['notice'] }}">{{ $panel['link'] }} <i class="bi bi-arrow-right" aria-hidden="true"></i></button></header><div class="gestion-operacion-summary-empty"><i class="bi {{ $panel['empty_icon'] ?? 'bi-inbox' }}" aria-hidden="true"></i><strong>{{ $panel['empty_title'] }}</strong><span>{{ $panel['empty'] }}</span></div></article>
        @endforeach
    </section>

    {{-- Los formularios completos viven en registrar y editar; aquí solo se abren como diálogos. --}}
    <dialog class="gestion-pendiente-modal" data-pending-modal="crear" aria-labelledby="devoluciones-garantias-crear-title">@include('gestion.comercial.devoluciones-garantias.registrar', ['module' => $module])</dialog>
    <dialog class="gestion-pendiente-modal" data-pending-modal="editar" aria-labelledby="devoluciones-garantias-editar-title">@include('gestion.comercial.devoluciones-garantias.editar', ['module' => $module])</dialog>
    <div class="gestion-pendiente-feedback" data-pending-feedback role="status" aria-live="polite" hidden></div>
</section>
@endsection
