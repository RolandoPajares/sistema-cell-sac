@extends('layouts.panel.principal')

@section('title', 'Configuración general')
@section('body_class', 'modulo-configuracion-general')

@section('content')
@php
    $module = require config_path('gestion/configuracion/general.php');
@endphp

@if (auth()->user()?->can('gestion.seguridad.consultar'))
    @php
    $moduleKey = $module['key'];
    $pageTitles = [
        'configuracion-empresa' => 'Datos de empresa',
        'configuracion-comercial' => 'Configuración comercial',
        'configuracion-seguridad' => 'Seguridad del sistema',
        'configuracion-general' => 'Configuración general',
    ];
    $pageTitle = $pageTitles[$moduleKey] ?? $module['title'];
@endphp

<section class="admin-settings-page admin-settings-page--{{ $moduleKey }}" data-admin-settings="{{ $moduleKey }}">
    <header class="admin-settings-header">
        <nav class="admin-settings-breadcrumb" aria-label="Ruta de navegación">
            <span>Administración</span>
            <i class="bi bi-chevron-right" aria-hidden="true"></i>
            <span>Configuración</span>
            <i class="bi bi-chevron-right" aria-hidden="true"></i>
            <strong>{{ $pageTitle }}</strong>
        </nav>
        <div class="admin-settings-heading-row">
            <div>
                <span class="admin-settings-eyebrow"><i class="bi {{ $module['icon'] }}" aria-hidden="true"></i> Configuración</span>
                <h1>{{ $pageTitle }}</h1>
                <p>{{ $module['description'] }}</p>
            </div>
            <div class="admin-settings-actions">
                <button class="admin-settings-button admin-settings-button--secondary" type="button" data-settings-reset>
                    <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
                    Descartar cambios
                </button>
                <button class="admin-settings-button admin-settings-button--primary" type="submit" form="admin-settings-form-{{ $moduleKey }}">
                    <i class="bi bi-save" aria-hidden="true"></i>
                    Guardar cambios
                </button>
            </div>
        </div>
    </header>

    <div class="admin-settings-notice" role="note">
        <i class="bi bi-info-circle" aria-hidden="true"></i>
        <span>Vista administrativa preparada para CELL SAC. Los cambios quedarán disponibles cuando este módulo se conecte al backend.</span>
    </div>

    <form class="admin-settings-form" id="admin-settings-form-{{ $moduleKey }}" data-settings-form>
        @include('gestion.configuracion.' . $moduleKey . '.editar', ['module' => $module])
    </form>

    <div class="admin-settings-feedback" data-settings-feedback role="status" aria-live="polite" hidden></div>
</section>

@else
    @php
    $moduleKey = $module['key'];
    $isCrud = ($module['mode'] ?? 'consult') === 'crud';
    $columns = $module['columns'] ?? [];
    $kpis = $module['kpis'] ?? [];
    $analytics = $module['analytics'] ?? [];
    $fields = $module['fields'] ?? [];
    $recordSingular = $module['record_singular'] ?? 'registro';
    $recordPlural = $module['record_plural'] ?? 'registros';
@endphp

<section class="gestion-pendiente-page gestion-pendiente-page--{{ $moduleKey }}" data-pending-module="{{ $moduleKey }}">
    <header class="gestion-pendiente-hero">
        <div class="gestion-pendiente-heading">
            <span class="gestion-pendiente-eyebrow"><i class="bi {{ $module['icon'] }}" aria-hidden="true"></i>{{ $module['area'] }}</span>
            <h1>{{ $module['title'] }}</h1>
            <p>{{ $module['description'] }}</p>
        </div>
        <div class="gestion-pendiente-hero-actions">
            <span class="gestion-pendiente-status"><i class="bi bi-link-45deg" aria-hidden="true"></i> Pendiente de integración</span>
            @if ($isCrud)
                <button class="gestion-pendiente-primary" type="button" data-pending-open="crear">
                    <i class="bi bi-plus-lg" aria-hidden="true"></i>
                    Nuevo {{ $recordSingular }}
                </button>
            @else
                <button class="gestion-pendiente-secondary" type="button" data-pending-notice="La exportación quedará habilitada cuando este módulo tenga una fuente de datos integrada.">
                    <i class="bi bi-download" aria-hidden="true"></i>
                    Exportar consulta
                </button>
            @endif
        </div>
    </header>

    <div class="gestion-pendiente-kpis">
        @foreach ($kpis as $kpi)
            <article class="gestion-pendiente-kpi gestion-pendiente-kpi--{{ $kpi['tone'] ?? 'blue' }}">
                <span class="gestion-pendiente-kpi-icon"><i class="bi {{ $kpi['icon'] }}" aria-hidden="true"></i></span>
                <div>
                    <small>{{ $kpi['label'] }}</small>
                    <strong>{{ $kpi['value'] ?? '—' }}</strong>
                    <span>{{ $kpi['helper'] ?? 'Sin datos integrados' }}</span>
                </div>
            </article>
        @endforeach
    </div>

    @if ($analytics !== [])
        <section class="gestion-pendiente-analytics" aria-label="Análisis visual de {{ $module['title'] }}">
            @foreach ($analytics as $analyticsCard)
                <article class="gestion-pendiente-analytics-card">
                    <header class="gestion-pendiente-analytics-header">
                        <div>
                            <span class="gestion-pendiente-analytics-icon"><i class="bi {{ $analyticsCard['icon'] ?? 'bi-bar-chart-line' }}" aria-hidden="true"></i></span>
                            <div>
                                <h2>{{ $analyticsCard['title'] }}</h2>
                                <p>{{ $analyticsCard['description'] }}</p>
                            </div>
                        </div>
                        <span class="gestion-pendiente-chart-status">Sin datos</span>
                    </header>

                    @if (($analyticsCard['type'] ?? 'line') === 'donut')
                        <div class="gestion-pendiente-donut-layout">
                            <div class="gestion-pendiente-donut" role="img" aria-label="Gráfica pendiente de {{ $analyticsCard['title'] }}">
                                <div><strong>—</strong><span>Sin datos</span></div>
                            </div>
                            <div class="gestion-pendiente-chart-legend">
                                @foreach (($analyticsCard['legend'] ?? []) as $legendItem)
                                    <div><span class="gestion-pendiente-legend-dot gestion-pendiente-legend-dot--{{ $loop->index % 4 }}"></span><span>{{ $legendItem }}</span><strong>—</strong></div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="gestion-pendiente-line-chart" role="img" aria-label="Gráfica pendiente de {{ $analyticsCard['title'] }}">
                            <div class="gestion-pendiente-chart-y-axis"><span>—</span><span>—</span><span>—</span><span>—</span></div>
                            <div class="gestion-pendiente-chart-plot">
                                <svg viewBox="0 0 720 220" preserveAspectRatio="none" aria-hidden="true">
                                    <path d="M0 168 C75 158 93 174 150 138 S260 162 316 104 S420 132 478 88 S600 126 720 54" class="gestion-pendiente-placeholder-line"></path>
                                </svg>
                                <div class="gestion-pendiente-chart-empty"><i class="bi bi-bar-chart-line" aria-hidden="true"></i><strong>Sin datos integrados</strong><span>La tendencia aparecerá aquí.</span></div>
                            </div>
                            <div class="gestion-pendiente-chart-x-axis">
                                @foreach (($analyticsCard['labels'] ?? ['Periodo 1', 'Periodo 2', 'Periodo 3', 'Periodo 4', 'Periodo 5']) as $label)
                                    <span>{{ $label }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </article>
            @endforeach
        </section>
    @endif

    <div class="gestion-pendiente-toolbar">
        <label class="gestion-pendiente-search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <span class="visually-hidden">Buscar {{ $recordPlural }}</span>
            <input type="search" placeholder="Buscar {{ $recordPlural }}..." data-pending-search>
        </label>
        <label>
            <span class="visually-hidden">Filtrar por estado</span>
            <select data-pending-filter>
                <option value="">Todos los estados</option>
                <option value="activo">Activos</option>
                <option value="pendiente">Pendientes</option>
                <option value="cerrado">Cerrados</option>
            </select>
        </label>
        <button class="gestion-pendiente-secondary" type="button" data-pending-notice="Los filtros quedarán disponibles cuando exista una fuente de datos para este módulo.">
            <i class="bi bi-arrow-repeat" aria-hidden="true"></i>
            Aplicar filtros
        </button>
    </div>

    <div class="gestion-pendiente-layout">
        <section class="gestion-pendiente-card gestion-pendiente-list-card">
            <header class="gestion-pendiente-card-header">
                <div>
                    <span class="gestion-pendiente-card-icon"><i class="bi {{ $module['icon'] }}" aria-hidden="true"></i></span>
                    <div>
                        <h2>Listado de {{ $recordPlural }}</h2>
                        <p>La información aparecerá cuando se conecte este módulo al backend.</p>
                    </div>
                </div>
                <span class="gestion-pendiente-count">Sin registros</span>
            </header>

            <div class="gestion-pendiente-table-wrap">
                <table class="gestion-pendiente-table">
                    <thead>
                        <tr>
                            @foreach ($columns as $column)
                                <th>{{ $column }}</th>
                            @endforeach
                            @if ($isCrud)
                                <th>Acciones</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="{{ max(1, count($columns) + ($isCrud ? 1 : 0)) }}">
                                <div class="gestion-pendiente-empty">
                                    <span><i class="bi bi-inbox" aria-hidden="true"></i></span>
                                    <strong>No hay datos disponibles</strong>
                                    <p>Este módulo está preparado visualmente y quedará disponible cuando se integre con datos reales.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <footer class="gestion-pendiente-card-footer">
                <span>Sin resultados para mostrar</span>
                <div class="gestion-pendiente-pagination" aria-label="Paginación deshabilitada">
                    <button type="button" disabled aria-label="Página anterior"><i class="bi bi-chevron-left" aria-hidden="true"></i></button>
                    <button type="button" class="is-active" disabled>1</button>
                    <button type="button" disabled aria-label="Página siguiente"><i class="bi bi-chevron-right" aria-hidden="true"></i></button>
                </div>
            </footer>
        </section>

        <aside class="gestion-pendiente-card gestion-pendiente-side-card">
            <header class="gestion-pendiente-card-header">
                <div>
                    <span class="gestion-pendiente-card-icon gestion-pendiente-card-icon--purple"><i class="bi bi-stars" aria-hidden="true"></i></span>
                    <div>
                        <h2>Estado del módulo</h2>
                        <p>Preparación frontend</p>
                    </div>
                </div>
            </header>
            <div class="gestion-pendiente-integration">
                <div class="gestion-pendiente-integration-row"><i class="bi bi-check2-circle" aria-hidden="true"></i><span>Interfaz administrativa creada</span></div>
                <div class="gestion-pendiente-integration-row"><i class="bi bi-check2-circle" aria-hidden="true"></i><span>Diseño responsive preparado</span></div>
                <div class="gestion-pendiente-integration-row"><i class="bi bi-hourglass-split" aria-hidden="true"></i><span>Conexión de datos pendiente</span></div>
            </div>
            @if ($isCrud)
                <div class="gestion-pendiente-capabilities">
                    <h3>Interfaces preparadas</h3>
                    <button type="button" data-pending-open="crear"><i class="bi bi-plus-circle" aria-hidden="true"></i> Crear {{ $recordSingular }}</button>
                    <button type="button" data-pending-open="ver"><i class="bi bi-eye" aria-hidden="true"></i> Ver detalle</button>
                    <button type="button" data-pending-open="editar"><i class="bi bi-pencil-square" aria-hidden="true"></i> Editar registro</button>
                    <button type="button" data-pending-open="eliminar"><i class="bi bi-trash3" aria-hidden="true"></i> Confirmar eliminación</button>
                </div>
            @else
                <button class="gestion-pendiente-secondary gestion-pendiente-secondary--full" type="button" data-pending-notice="La consulta quedará habilitada cuando exista el origen de datos correspondiente.">
                    <i class="bi bi-bar-chart-line" aria-hidden="true"></i>
                    Preparar consulta
                </button>
            @endif
        </aside>
    </div>

    @if ($isCrud)
        <dialog class="gestion-pendiente-modal" data-pending-modal="crear" aria-labelledby="{{ $moduleKey }}-crear-title">
            @include('gestion.' . $moduleKey . '.crear', ['module' => $module])
        </dialog>
        <dialog class="gestion-pendiente-modal" data-pending-modal="editar" aria-labelledby="{{ $moduleKey }}-editar-title">
            @include('gestion.' . $moduleKey . '.editar', ['module' => $module])
        </dialog>
        <dialog class="gestion-pendiente-modal" data-pending-modal="ver" aria-labelledby="{{ $moduleKey }}-ver-title">
            <div class="gestion-pendiente-modal-shell">
    <header class="gestion-pendiente-modal-header">
        <div><span class="gestion-pendiente-modal-icon"><i class="bi {{ $module['icon'] }}" aria-hidden="true"></i></span><div><span class="gestion-pendiente-eyebrow">{{ $module['area'] }}</span><h2 id="{{ $module['key'] }}-ver-title">Ver {{ $module['record_singular'] ?? 'registro' }}</h2><p>Detalle del registro seleccionado.</p></div></div>
        <button type="button" class="gestion-pendiente-close" data-pending-close aria-label="Cerrar ventana"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </header>
    <div class="gestion-pendiente-modal-body">
        <div class="gestion-pendiente-modal-notice"><i class="bi bi-info-circle" aria-hidden="true"></i><div><strong>Vista preparada para integración</strong><p>No hay un registro real seleccionado. El detalle se poblará desde el backend cuando se conecte este módulo.</p></div></div>
        <div class="gestion-pendiente-detail-grid">
            @foreach (($module['fields'] ?? []) as $field)
                <div><small>{{ $field['label'] }}</small><strong>—</strong></div>
            @endforeach
        </div>
    </div>
    <footer class="gestion-pendiente-modal-footer"><button type="button" class="gestion-pendiente-secondary" data-pending-close>Cerrar</button></footer>
</div>

        </dialog>
        <dialog class="gestion-pendiente-modal gestion-pendiente-modal--danger" data-pending-modal="eliminar" aria-labelledby="{{ $moduleKey }}-eliminar-title">
            <div class="gestion-pendiente-modal-shell">
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
    @endif

    <div class="gestion-pendiente-feedback" data-pending-feedback role="status" aria-live="polite" hidden></div>
</section>

@endif
@endsection

