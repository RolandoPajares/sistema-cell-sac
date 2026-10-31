@php
    $ui = $module['operacion_ui'];
    $moduleKey = $module['key'];
    $rows = $ui['rows'] ?? [];
    $filterValues = $ui['filter_values'] ?? [];
    $pagination = $ui['pagination'] ?? null;
@endphp

<section class="gestion-pendiente-page gestion-operacion-page gestion-operacion-page--{{ $moduleKey }}" data-pending-module="{{ $moduleKey }}">
    <header class="gestion-operacion-header">
        <nav class="gestion-operacion-breadcrumb" aria-label="Ruta de navegación">
            @foreach ($ui['breadcrumb'] as $breadcrumb)
                @if (!$loop->first)<i class="bi bi-chevron-right" aria-hidden="true"></i>@endif
                <span class="{{ $loop->last ? 'is-current' : '' }}">{{ $breadcrumb }}</span>
            @endforeach
        </nav>
        <div class="gestion-operacion-heading-row">
            <div class="gestion-operacion-heading">
                <span class="gestion-pendiente-eyebrow"><i class="bi {{ $module['icon'] }}" aria-hidden="true"></i> {{ $module['area'] }}</span>
                <h1>{{ $ui['title'] }}</h1>
                <p>{{ $ui['description'] }}</p>
            </div>
            <div class="gestion-operacion-actions">
                @foreach ($ui['actions'] as $action)
                    @if (isset($action['route']))
                        <a class="gestion-pendiente-secondary" href="{{ route($action['route'], request()->query()) }}"><i class="bi {{ $action['icon'] }}" aria-hidden="true"></i> {{ $action['label'] }}</a>
                    @elseif (($action['modal'] ?? null) === 'crear')
                        <button class="gestion-pendiente-primary" type="button" data-pending-open="crear"><i class="bi {{ $action['icon'] }}" aria-hidden="true"></i> {{ $action['label'] }}</button>
                    @else
                        <button class="gestion-pendiente-secondary" type="button" data-pending-notice="{{ $action['notice'] }}"><i class="bi {{ $action['icon'] }}" aria-hidden="true"></i> {{ $action['label'] }}</button>
                    @endif
                @endforeach
            </div>
        </div>
    </header>

    @if (session('status'))
        <div class="gestion-operacion-feedback gestion-operacion-feedback--success" role="status"><i class="bi bi-check-circle" aria-hidden="true"></i>{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="gestion-operacion-feedback gestion-operacion-feedback--error" role="alert"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i>{{ $errors->first() }}</div>
    @endif

    <section class="gestion-operacion-kpis" aria-label="Indicadores del módulo">
        @foreach ($ui['kpis'] as $kpi)
            <article class="gestion-operacion-kpi gestion-operacion-kpi--{{ $kpi['tone'] }}">
                <span class="gestion-operacion-kpi-icon"><i class="bi {{ $kpi['icon'] }}" aria-hidden="true"></i></span>
                <div class="gestion-operacion-kpi-content"><small>{{ $kpi['label'] }}</small><strong>{{ $kpi['value'] ?? '—' }}</strong><span>{{ $kpi['helper'] }}</span></div>
                <span class="gestion-operacion-sparkline" aria-hidden="true"></span>
            </article>
        @endforeach
    </section>

    <form class="gestion-operacion-filters" method="GET" @if (($ui['backend'] ?? false) === true) action="{{ $ui['filter_action'] }}" @else data-pending-form @endif>
        <label class="gestion-operacion-search"><i class="bi bi-search" aria-hidden="true"></i><span class="visually-hidden">{{ $ui['search_label'] }}</span><input name="buscar" type="search" value="{{ $filterValues['buscar'] ?? '' }}" placeholder="{{ $ui['search_placeholder'] }}" data-pending-search></label>
        @foreach ($ui['filters'] as $filter)
            <label><span class="visually-hidden">{{ $filter['label'] }}</span><select name="{{ $filter['name'] ?? 'filtro_'.$loop->index }}" data-pending-filter><option value="">{{ $filter['placeholder'] }}</option>@foreach ($filter['options'] as $option)<option value="{{ $option }}" @selected(($filterValues[$filter['name'] ?? ''] ?? '') === $option)>{{ $option }}</option>@endforeach</select></label>
        @endforeach
        <button class="gestion-operacion-filter-button" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Filtrar</button>
        @if (($ui['backend'] ?? false) === true && !empty($ui['clear_action']))
            <a class="gestion-operacion-clear-button" href="{{ $ui['clear_action'] }}">Limpiar filtros</a>
        @else
            <button class="gestion-operacion-clear-button" type="button" data-pending-notice="Los filtros se podrán limpiar cuando exista una fuente de datos integrada.">Limpiar filtros</button>
        @endif
    </form>

    <section class="gestion-operacion-control-card">
        <header class="gestion-operacion-card-header">
            <div><i class="bi {{ $ui['table_icon'] }}" aria-hidden="true"></i><h2>{{ $ui['table_title'] }}</h2><span>{{ $ui['table_count'] }}</span></div>
            <button class="gestion-operacion-sort" type="button" data-pending-notice="El ordenamiento quedará habilitado con los registros reales.">Ordenar por <strong>Más recientes</strong><i class="bi bi-chevron-down" aria-hidden="true"></i></button>
        </header>
        <div class="gestion-operacion-table-wrap">
            <table class="gestion-operacion-table">
                <thead><tr>@foreach ($ui['table_columns'] as $column)<th>{{ $column }}</th>@endforeach</tr></thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            @foreach ($row['cells'] as $cell)
                                <td>
                                    @if (is_array($cell))
                                        <span class="gestion-operacion-badge gestion-operacion-badge--{{ $cell['tone'] }}">{{ $cell['value'] }}</span>
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
                        <tr><td colspan="{{ count($ui['table_columns']) }}"><div class="gestion-operacion-empty"><span><i class="bi bi-inbox" aria-hidden="true"></i></span><strong>{{ $ui['empty_title'] }}</strong><p>{{ $ui['empty_description'] }}</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <footer class="gestion-operacion-table-footer">
            <span>{{ $ui['table_footer'] }}</span>
            <div class="gestion-operacion-pagination" aria-label="Paginación">
                @if (is_array($pagination))
                    <a href="{{ $pagination['previous'] ?: '#' }}" @if (!$pagination['previous']) aria-disabled="true" @endif aria-label="Página anterior"><i class="bi bi-chevron-left" aria-hidden="true"></i></a>
                    @for ($page = 1; $page <= $pagination['last']; $page++)
                        @if ($page <= 5 || $page === $pagination['last'])
                            <a href="{{ route($ui['filter_route'], array_merge(request()->query(), ['page' => $page])) }}" class="{{ $page === $pagination['current'] ? 'is-active' : '' }}">{{ $page }}</a>
                        @elseif ($page === 6)
                            <span class="gestion-operacion-pagination-more">…</span>
                        @endif
                    @endfor
                    <a href="{{ $pagination['next'] ?: '#' }}" @if (!$pagination['next']) aria-disabled="true" @endif aria-label="Página siguiente"><i class="bi bi-chevron-right" aria-hidden="true"></i></a>
                @else
                    <button type="button" disabled aria-label="Página anterior"><i class="bi bi-chevron-left" aria-hidden="true"></i></button><button type="button" class="is-active" disabled>1</button><button type="button" disabled aria-label="Página siguiente"><i class="bi bi-chevron-right" aria-hidden="true"></i></button>
                @endif
            </div>
        </footer>
    </section>

    <section class="gestion-operacion-bottom-grid" aria-label="Resumen del módulo">
        @foreach ($ui['panels'] as $panel)
            <article class="gestion-operacion-summary-card{{ ($panel['type'] ?? 'empty') === 'performance' ? ' gestion-operacion-performance-card' : '' }}">
                <header><div><span class="gestion-operacion-summary-icon gestion-operacion-summary-icon--{{ $panel['tone'] }}"><i class="bi {{ $panel['icon'] }}" aria-hidden="true"></i></span><div><h2>{{ $panel['title'] }}</h2><p>{{ $panel['subtitle'] }}</p></div></div><button type="button" data-pending-notice="{{ $panel['notice'] }}">{{ $panel['link'] }} <i class="bi bi-arrow-right" aria-hidden="true"></i></button></header>
                @if (($panel['type'] ?? 'empty') === 'performance')
                    <div class="gestion-operacion-performance-empty"><strong>—</strong><span>{{ $panel['metric'] }}</span><div class="gestion-operacion-performance-bar"><span></span></div><small>{{ $panel['empty'] }}</small></div>
                @else
                    <div class="gestion-operacion-summary-empty"><i class="bi {{ $panel['empty_icon'] }}" aria-hidden="true"></i><strong>{{ $panel['empty_title'] }}</strong><span>{{ $panel['empty'] }}</span></div>
                @endif
            </article>
        @endforeach
    </section>

    <dialog class="gestion-pendiente-modal" data-pending-modal="crear" aria-labelledby="{{ $moduleKey }}-crear-title">@include('gestion.' . $moduleKey . '.crear', ['module' => $module])</dialog>
    <dialog class="gestion-pendiente-modal" data-pending-modal="editar" aria-labelledby="{{ $moduleKey }}-editar-title">@include('gestion.' . $moduleKey . '.editar', ['module' => $module])</dialog>
    <dialog class="gestion-pendiente-modal" data-pending-modal="ver" aria-labelledby="{{ $moduleKey }}-ver-title">@include('gestion.' . $moduleKey . '.ver', ['module' => $module])</dialog>
    <dialog class="gestion-pendiente-modal gestion-pendiente-modal--danger" data-pending-modal="eliminar" aria-labelledby="{{ $moduleKey }}-eliminar-title">@include('gestion.' . $moduleKey . '.eliminar', ['module' => $module])</dialog>
    <div class="gestion-pendiente-feedback" data-pending-feedback role="status" aria-live="polite" hidden></div>
</section>
