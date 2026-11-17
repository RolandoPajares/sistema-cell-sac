@extends('layouts.panel.principal')


{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('title', 'Envíos y entregas')
{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('body_class', 'modulo-envios-entregas')

{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('content')
<section class="gestion-pendiente-page gestion-operacion-page gestion-operacion-page--{{ $module['key'] }}" data-pending-module="{{ $module['key'] }}">
    <header class="gestion-operacion-header">
        <nav class="gestion-operacion-breadcrumb" aria-label="Ruta de navegación">
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
            @foreach ($module['operacion_ui']['breadcrumb'] as $breadcrumb)
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
                @if (!$loop->first)<i class="bi bi-chevron-right" aria-hidden="true"></i>@endif
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
                    @if (isset($action['route']) && isset($action['formats']))
                        <details class="gestion-pendiente-export-menu"><summary class="gestion-pendiente-secondary"><i class="bi {{ $action['icon'] }}" aria-hidden="true"></i> {{ $action['label'] }} <i class="bi bi-chevron-down" aria-hidden="true"></i></summary><div class="gestion-pendiente-export-options">@foreach ($action['formats'] as $format)<a href="{{ route($action['route'], array_merge(request()->query(), ['format' => $format])) }}"><i class="bi {{ $format === 'excel' ? 'bi-file-earmark-spreadsheet' : ($format === 'pdf' ? 'bi-file-earmark-pdf' : 'bi-filetype-csv') }}" aria-hidden="true"></i> {{ strtoupper($format) }} (.{{ $format === 'excel' ? 'xlsx' : $format }})</a>@endforeach</div></details>
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

{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
    @if (session('status'))
        <div class="gestion-operacion-feedback gestion-operacion-feedback--success" role="status"><i class="bi bi-check-circle" aria-hidden="true"></i>{{ session('status') }}</div>
    @endif
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
    @if ($errors->any())
        <div class="gestion-operacion-feedback gestion-operacion-feedback--error" role="alert"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i>{{ $errors->first() }}</div>
    @endif

    <section class="gestion-operacion-kpis" aria-label="Indicadores del módulo">
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
        @foreach ($module['operacion_ui']['kpis'] as $kpi)
            <article class="gestion-operacion-kpi gestion-operacion-kpi--{{ $kpi['tone'] }}">
                <span class="gestion-operacion-kpi-icon"><i class="bi {{ $kpi['icon'] }}" aria-hidden="true"></i></span>
                <div class="gestion-operacion-kpi-content"><small>{{ $kpi['label'] }}</small><strong>{{ $kpi['value'] ?? '—' }}</strong><span>{{ $kpi['helper'] }}</span></div>
                <span class="gestion-operacion-sparkline" aria-hidden="true"></span>
            </article>
        @endforeach
    </section>

{{-- formulario que recopila datos y los envia a la ruta indicada. --}}
    <form class="gestion-operacion-filters" method="GET" @if (($module['operacion_ui']['backend'] ?? false) === true) action="{{ $module['operacion_ui']['filter_action'] }}" @else data-pending-form @endif>
        <label class="gestion-operacion-search"><i class="bi bi-search" aria-hidden="true"></i><span class="visually-hidden">{{ $module['operacion_ui']['search_label'] }}</span><input name="buscar" type="search" value="{{ $module['operacion_ui']['filter_values']['buscar'] ?? '' }}" placeholder="{{ $module['operacion_ui']['search_placeholder'] }}" data-pending-search></label>
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
        @foreach ($module['operacion_ui']['filters'] as $filter)
            <label><span class="visually-hidden">{{ $filter['label'] }}</span><select name="{{ $filter['name'] ?? 'filtro_'.$loop->index }}" data-pending-filter><option value="">{{ $filter['placeholder'] }}</option>@foreach ($filter['options'] as $option)<option value="{{ $option }}" @selected(($module['operacion_ui']['filter_values'][$filter['name'] ?? ''] ?? '') === $option)>{{ $option }}</option>@endforeach</select></label>
        @endforeach
        <button class="gestion-operacion-filter-button" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Filtrar</button>
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
        @if (($module['operacion_ui']['backend'] ?? false) === true && !empty($module['operacion_ui']['clear_action']))
            <a class="gestion-operacion-clear-button" href="{{ $module['operacion_ui']['clear_action'] }}">Limpiar filtros</a>
{{-- alternativa del condicional cuando la condicion anterior no se cumple. --}}
        @else
            <button class="gestion-operacion-clear-button" type="button" data-pending-notice="Los filtros se podrán limpiar cuando exista una fuente de datos integrada.">Limpiar filtros</button>
        @endif
    </form>

    <section class="gestion-operacion-control-card">
        <header class="gestion-operacion-card-header">
            <div><i class="bi {{ $module['operacion_ui']['table_icon'] }}" aria-hidden="true"></i><h2>{{ $module['operacion_ui']['table_title'] }}</h2><span>{{ $module['operacion_ui']['table_count'] }}</span></div>
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
            @if (($module['operacion_ui']['backend'] ?? false) === true && !empty($module['operacion_ui']['sort_options']))
                <details class="gestion-operacion-sort-menu"><summary class="gestion-operacion-sort">Ordenar por <strong>{{ $module['operacion_ui']['sort_options'][$module['operacion_ui']['filter_values']['orden'] ?? 'recientes'] ?? 'Más recientes' }}</strong><i class="bi bi-chevron-down" aria-hidden="true"></i></summary><div class="gestion-operacion-sort-options">@foreach ($module['operacion_ui']['sort_options'] as $sortKey => $sortLabel)<a href="{{ route($module['operacion_ui']['filter_route'], array_merge(request()->query(), ['orden' => $sortKey, 'page' => 1])) }}">{{ $sortLabel }}</a>@endforeach</div></details>
{{-- alternativa del condicional cuando la condicion anterior no se cumple. --}}
            @else
                <button class="gestion-operacion-sort" type="button" data-pending-notice="El ordenamiento quedará habilitado cuando exista una fuente de datos integrada.">Ordenar por <strong>Más recientes</strong><i class="bi bi-chevron-down" aria-hidden="true"></i></button>
            @endif
        </header>
        <div class="gestion-operacion-table-wrap">
            <table class="gestion-operacion-table">
                <thead><tr>@foreach ($module['operacion_ui']['table_columns'] as $column)<th>{{ $column }}</th>@endforeach</tr></thead>
                <tbody>
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
                    @forelse ($module['operacion_ui']['rows'] as $row)
                        <tr>
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
                            @foreach ($row['cells'] as $cell)
                                <td>
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
                                    @if (is_array($cell))
                                        <span class="gestion-operacion-badge gestion-operacion-badge--{{ $cell['tone'] }}">{{ $cell['value'] }}</span>
{{-- alternativa del condicional cuando la condicion anterior no se cumple. --}}
                                    @else
                                        {{ $cell }}
                                    @endif
                                </td>
                            @endforeach
                            <td class="gestion-operacion-row-actions">
                                <button type="button" data-pending-open="ver" data-pending-record="{{ base64_encode(json_encode($row['record'], JSON_UNESCAPED_UNICODE)) }}" aria-label="Ver solicitud"><i class="bi bi-eye" aria-hidden="true"></i></button>
                                <button type="button" data-pending-open="editar" data-pending-action="{{ $row['edit_url'] }}" data-pending-record="{{ base64_encode(json_encode($row['record'], JSON_UNESCAPED_UNICODE)) }}" aria-label="Editar solicitud"><i class="bi bi-pencil" aria-hidden="true"></i></button>
                                <button type="button" data-pending-open="eliminar" data-pending-action="{{ $row['delete_url'] }}" data-pending-record="{{ base64_encode(json_encode($row['record'], JSON_UNESCAPED_UNICODE)) }}" aria-label="Eliminar solicitud"><i class="bi bi-trash3" aria-hidden="true"></i></button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($module['operacion_ui']['table_columns']) }}"><div class="gestion-operacion-empty"><span><i class="bi bi-inbox" aria-hidden="true"></i></span><strong>{{ $module['operacion_ui']['empty_title'] }}</strong><p>{{ $module['operacion_ui']['empty_description'] }}</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <footer class="gestion-operacion-table-footer">
            <span>{{ $module['operacion_ui']['table_footer'] }}</span>
            <div class="gestion-operacion-pagination" aria-label="Paginación">
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
                @if (is_array($module['operacion_ui']['pagination']))
                    <a href="{{ $module['operacion_ui']['pagination']['previous'] ?: '#' }}" @if (!$module['operacion_ui']['pagination']['previous']) aria-disabled="true" @endif aria-label="Página anterior"><i class="bi bi-chevron-left" aria-hidden="true"></i></a>
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
                    @for ($page = 1; $page <= $module['operacion_ui']['pagination']['last']; $page++)
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
                        @if ($page <= 5 || $page === $module['operacion_ui']['pagination']['last'])
                            <a href="{{ route($module['operacion_ui']['filter_route'], array_merge(request()->query(), ['page' => $page])) }}" class="{{ $page === $module['operacion_ui']['pagination']['current'] ? 'is-active' : '' }}">{{ $page }}</a>
{{-- alternativa del condicional cuando la condicion anterior no se cumple. --}}
                        @elseif ($page === 6)
                            <span class="gestion-operacion-pagination-more">…</span>
                        @endif
                    @endfor
                    <a href="{{ $module['operacion_ui']['pagination']['next'] ?: '#' }}" @if (!$module['operacion_ui']['pagination']['next']) aria-disabled="true" @endif aria-label="Página siguiente"><i class="bi bi-chevron-right" aria-hidden="true"></i></a>
{{-- alternativa del condicional cuando la condicion anterior no se cumple. --}}
                @else
                    <button type="button" disabled aria-label="Página anterior"><i class="bi bi-chevron-left" aria-hidden="true"></i></button><button type="button" class="is-active" disabled>1</button><button type="button" disabled aria-label="Página siguiente"><i class="bi bi-chevron-right" aria-hidden="true"></i></button>
                @endif
            </div>
        </footer>
    </section>

    <section class="gestion-operacion-bottom-grid" aria-label="Resumen del módulo">
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
        @foreach ($module['operacion_ui']['panels'] as $panel)
            <article class="gestion-operacion-summary-card{{ ($panel['type'] ?? 'empty') === 'performance' ? ' gestion-operacion-performance-card' : '' }}">
                <header><div><span class="gestion-operacion-summary-icon gestion-operacion-summary-icon--{{ $panel['tone'] }}"><i class="bi {{ $panel['icon'] }}" aria-hidden="true"></i></span><div><h2>{{ $panel['title'] }}</h2><p>{{ $panel['subtitle'] }}</p></div></div><button type="button" data-pending-notice="{{ $panel['notice'] }}">{{ $panel['link'] }} <i class="bi bi-arrow-right" aria-hidden="true"></i></button></header>
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
                @if (($panel['type'] ?? 'empty') === 'performance')
                    <div class="gestion-operacion-performance-empty"><strong>—</strong><span>{{ $panel['metric'] }}</span><div class="gestion-operacion-performance-bar"><span></span></div><small>{{ $panel['empty'] }}</small></div>
{{-- alternativa del condicional cuando la condicion anterior no se cumple. --}}
                @else
                    <div class="gestion-operacion-summary-empty"><i class="bi {{ $panel['empty_icon'] ?? 'bi-inbox' }}" aria-hidden="true"></i><strong>{{ $panel['empty_title'] }}</strong><span>{{ $panel['empty'] }}</span></div>
                @endif
            </article>
        @endforeach
    </section>

    <dialog class="gestion-pendiente-modal" data-pending-modal="crear" aria-labelledby="{{ $module['key'] }}-crear-title">@include('gestion.' . $module['key'] . '.crear', ['module' => $module])</dialog>
    <dialog class="gestion-pendiente-modal" data-pending-modal="editar" aria-labelledby="{{ $module['key'] }}-editar-title">@include('gestion.' . $module['key'] . '.editar', ['module' => $module])</dialog>
    <dialog class="gestion-pendiente-modal" data-pending-modal="ver" aria-labelledby="{{ $module['key'] }}-ver-title"><div class="gestion-pendiente-modal-shell">
    <header class="gestion-pendiente-modal-header">
        <div><span class="gestion-pendiente-modal-icon"><i class="bi {{ $module['icon'] }}" aria-hidden="true"></i></span><div><span class="gestion-pendiente-eyebrow">{{ $module['area'] }}</span><h2 id="{{ $module['key'] }}-ver-title">Ver {{ $module['record_singular'] ?? 'registro' }}</h2><p>Detalle del registro seleccionado.</p></div></div>
        <button type="button" class="gestion-pendiente-close" data-pending-close aria-label="Cerrar ventana"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </header>
    <div class="gestion-pendiente-modal-body">
        <div class="gestion-pendiente-modal-notice"><i class="bi bi-info-circle" aria-hidden="true"></i><div><strong>Vista preparada para integración</strong><p>No hay un registro real seleccionado. El detalle se poblará desde el backend cuando se conecte este módulo.</p></div></div>
        <div class="gestion-pendiente-detail-grid">
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
            @foreach (($module['fields'] ?? []) as $field)
                <div><small>{{ $field['label'] }}</small><strong>—</strong></div>
            @endforeach
        </div>
    </div>
    <footer class="gestion-pendiente-modal-footer"><button type="button" class="gestion-pendiente-secondary" data-pending-close>Cerrar</button></footer>
</div>
</dialog>
    <dialog class="gestion-pendiente-modal gestion-pendiente-modal--danger" data-pending-modal="eliminar" aria-labelledby="{{ $module['key'] }}-eliminar-title"><div class="gestion-pendiente-modal-shell">
    <header class="gestion-pendiente-modal-header">
        <div><span class="gestion-pendiente-modal-icon gestion-pendiente-modal-icon--danger"><i class="bi bi-trash3" aria-hidden="true"></i></span><div><span class="gestion-pendiente-eyebrow">{{ $module['area'] }}</span><h2 id="{{ $module['key'] }}-eliminar-title">Eliminar {{ $module['record_singular'] ?? 'registro' }}</h2><p>Confirma la acción antes de continuar.</p></div></div>
        <button type="button" class="gestion-pendiente-close" data-pending-close aria-label="Cerrar ventana"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </header>
    <div class="gestion-pendiente-modal-body">
        <div class="gestion-pendiente-modal-warning"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i><div><strong>Acción no disponible todavía</strong><p>No se eliminó ningún registro. Esta confirmación es visual y quedará conectada cuando exista una operación autorizada del backend.</p></div></div>
    </div>
    <footer class="gestion-pendiente-modal-footer"><button type="button" class="gestion-pendiente-secondary" data-pending-close>Cancelar</button><button type="button" class="gestion-pendiente-danger" data-pending-notice="La eliminación permanece pendiente de integración y no modificó ningún dato.">Eliminar cuando se integre</button></footer>
</div>
</dialog>
    <div class="gestion-pendiente-feedback" data-pending-feedback role="status" aria-live="polite" hidden></div>
</section>
@endsection
