@extends('layouts.panel.principal')

@section('title', 'Facturación')
@section('body_class', 'modulo-facturacion')

@section('content')
{{-- La consulta usa siempre el origen real de documentos_electronicos. No se
     muestra la maqueta anterior a ningún rol autorizado. --}}

<section class="admin-finance-module admin-finance-module--{{ $moduleKey }}" data-finance-module="{{ $moduleKey }}" data-finance-reopen="{{ old('_form') === 'crear' && $errors->any() ? 'crear' : '' }}">
    <header class="admin-finance-header">
        <nav class="admin-finance-breadcrumb" aria-label="Ruta de navegación">
            <span>Administración</span>
            <i class="bi bi-chevron-right" aria-hidden="true"></i>
            <span>{{ $module['area'] }}</span>
            <i class="bi bi-chevron-right" aria-hidden="true"></i>
            <strong>{{ $module['title'] }}</strong>
        </nav>

        <div class="admin-finance-heading-row">
            <div class="admin-finance-heading">
                <span class="admin-finance-eyebrow"><i class="bi {{ $module['icon'] }}" aria-hidden="true"></i> {{ $module['area'] }}</span>
                <h1>{{ $module['title'] }}</h1>
                <p>{{ $module['description'] }}</p>
            </div>
            <div class="admin-finance-actions">
                @if ($module['export_route'] ?? false)
                    <details class="admin-finance-export-menu">
                        <summary class="admin-finance-button admin-finance-button--secondary"><i class="bi bi-download" aria-hidden="true"></i><span>{{ $module['export_label'] ?? 'Exportar' }}</span><i class="bi bi-chevron-down admin-finance-export-chevron" aria-hidden="true"></i></summary>
                        <div class="admin-finance-export-options" role="menu">
                            <a role="menuitem" href="{{ route($module['export_route'], ['format' => 'excel'] + $exportParameters) }}"><i class="bi bi-file-earmark-excel" aria-hidden="true"></i><span><strong>Exportar Excel</strong><small>Archivo compatible .xlsx</small></span></a>
                            <a role="menuitem" href="{{ route($module['export_route'], ['format' => 'pdf'] + $exportParameters) }}"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i><span><strong>Exportar PDF</strong><small>Documento imprimible</small></span></a>
                            <a role="menuitem" href="{{ route($module['export_route'], ['format' => 'csv'] + $exportParameters) }}"><i class="bi bi-filetype-csv" aria-hidden="true"></i><span><strong>Exportar CSV</strong><small>Datos delimitados</small></span></a>
                        </div>
                    </details>
                @endif
                @if ($module['create_route'] ?? false)
                    <button class="admin-finance-button admin-finance-button--primary" type="button" data-finance-open="crear">
                        <i class="bi bi-plus-lg" aria-hidden="true"></i> {{ $moduleKey === 'pagos' ? 'Registrar solicitud' : 'Nueva solicitud' }}
                    </button>
                @endif
            </div>
        </div>
    </header>
    @if (session('status'))
        <p class="alert alert-success" role="status">{{ session('status') }}</p>
    @endif

    <section class="admin-finance-kpis" aria-label="Indicadores de {{ $module['title'] }}">
        @foreach ($module['kpis'] as $kpi)
            <article class="admin-finance-kpi admin-finance-kpi--{{ $kpi['tone'] ?? 'blue' }}">
                <span class="admin-finance-kpi-icon"><i class="bi {{ $kpi['icon'] }}" aria-hidden="true"></i></span>
                <div class="admin-finance-kpi-copy">
                    <small>{{ $kpi['label'] }}</small>
                    <strong>{{ $kpi['value'] }}</strong>
                    <span>{{ $kpi['helper'] }}</span>
                </div>
                <i class="admin-finance-kpi-spark" aria-hidden="true"></i>
            </article>
        @endforeach
    </section>

    <form class="admin-finance-filters" method="GET" action="{{ route($module['route']) }}">
        <label class="admin-finance-search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <span class="visually-hidden">Buscar {{ $module['record_plural'] }}</span>
            <input type="search" name="buscar" value="{{ $filterValues['buscar'] ?? '' }}" maxlength="120" placeholder="Buscar por código, referencia o documento...">
        </label>
        @if (($filterOptions['types'] ?? []) !== [])
            <label>
                <span class="visually-hidden">Tipo de operación</span>
                <select name="tipo">
                    <option value="">Tipo: todos</option>
                    @foreach ($filterOptions['types'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filterValues['tipo'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
        @endif
        <label>
            <span class="visually-hidden">Estado</span>
            <select name="estado">
                <option value="">Estado: todos</option>
                @foreach ($filterOptions['statuses'] as $status)
                    <option value="{{ $status }}" @selected(($filterValues['estado'] ?? '') === $status)>{{ $status }}</option>
                @endforeach
            </select>
        </label>
        <label>
            <span class="visually-hidden">Fecha inicial</span>
            <input type="date" name="desde" value="{{ $filterValues['desde'] ?? '' }}" aria-label="Fecha inicial">
        </label>
        <label>
            <span class="visually-hidden">Fecha final</span>
            <input type="date" name="hasta" value="{{ $filterValues['hasta'] ?? '' }}" aria-label="Fecha final">
        </label>
        <button class="admin-finance-filter-button" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Filtrar</button>
        <a class="admin-finance-clear-button" href="{{ route($module['route']) }}">Limpiar filtros</a>
    </form>
    @foreach ($errors->all() as $error)
        <p class="text-danger" role="alert">{{ $error }}</p>
    @endforeach

    <section class="admin-finance-table-card">
        <header class="admin-finance-card-header">
            <div>
                <span class="admin-finance-card-icon"><i class="bi {{ $module['icon'] }}" aria-hidden="true"></i></span>
                <div>
                    <h2>{{ $module['table_title'] }}</h2>
                    <p>Registros consultados desde las tablas financieras y el alcance autorizado.</p>
                </div>
            </div>
            <span class="admin-finance-count">{{ number_format($module['total_count']) }} registros</span>
        </header>
        <div class="admin-finance-table-wrap">
            <table class="admin-finance-table">
                <thead>
                    <tr>
                        @foreach ($module['columns'] as $column)
                            <th>{{ $column }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($module['records'] as $record)
                        <tr>
                            @foreach ($record['cells'] as $cellIndex => $cell)
                                <td>
                                    @if ($cellIndex === 0 && isset($record['detail_url']))
                                        <a href="{{ $record['detail_url'] }}">{{ $cell }}</a>
                                    @else
                                        {{ $cell }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($module['columns']) }}">
                                <div class="admin-finance-empty">
                                    <span><i class="bi bi-inbox" aria-hidden="true"></i></span>
                                    <strong>No hay {{ $module['record_plural'] }} para mostrar</strong>
                                    <p>No existen registros que coincidan con los filtros y el alcance autorizados.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <footer class="admin-finance-table-footer">
            <span>
                @if ($module['total_count'] > 0)
                    Mostrando {{ $module['pagination']->firstItem() }}–{{ $module['pagination']->lastItem() }} de {{ number_format($module['total_count']) }}
                @else
                    Sin resultados para mostrar
                @endif
            </span>
            @if ($module['pagination']->hasPages())
                <nav class="admin-finance-pagination" aria-label="Paginación">
                    @if ($module['pagination']->previousPageUrl())
                        <a href="{{ $module['pagination']->previousPageUrl() }}" rel="prev" aria-label="Página anterior"><i class="bi bi-chevron-left" aria-hidden="true"></i></a>
                    @else
                        <button type="button" disabled aria-label="Página anterior"><i class="bi bi-chevron-left" aria-hidden="true"></i></button>
                    @endif
                    <span class="is-active" aria-current="page">{{ $module['pagination']->currentPage() }} / {{ $module['pagination']->lastPage() }}</span>
                    @if ($module['pagination']->nextPageUrl())
                        <a href="{{ $module['pagination']->nextPageUrl() }}" rel="next" aria-label="Página siguiente"><i class="bi bi-chevron-right" aria-hidden="true"></i></a>
                    @else
                        <button type="button" disabled aria-label="Página siguiente"><i class="bi bi-chevron-right" aria-hidden="true"></i></button>
                    @endif
                </nav>
            @endif
        </footer>
    </section>

    @if ($module['summary'] !== [])
        <section class="admin-finance-summary-grid" aria-label="Resumen por estado de {{ $module['title'] }}">
            @foreach ($module['summary'] as $summary)
                <article class="admin-finance-summary-card">
                    <header>
                        <div><span class="admin-finance-summary-icon admin-finance-summary-icon--{{ $summary['tone'] }}"><i class="bi {{ $summary['icon'] }}" aria-hidden="true"></i></span><h2>{{ $summary['title'] }}</h2></div>
                    </header>
                    <div class="admin-finance-summary-empty"><i class="bi bi-list-check" aria-hidden="true"></i><strong>{{ $summary['text'] }}</strong><span>Estado leído del registro</span></div>
                </article>
            @endforeach
        </section>
    @endif

</section>


@endsection

