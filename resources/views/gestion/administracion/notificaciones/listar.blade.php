@extends('layouts.panel.principal')

@section('title', 'Notificaciones')
@section('body_class', 'modulo-notificaciones')

@section('content')
{{-- Presentación del módulo usando la configuración preparada fuera de Blade. --}}
@php($module = config('gestion.administracion.notificaciones'))

{{-- Este permiso controla la visibilidad; la autorización real permanece en el backend. --}}
@can('gestion.seguridad.consultar')
<section class="admin-system-module admin-system-module--{{ $module['key'] }}" data-pending-module="{{ $module['key'] }}" data-admin-system-module="{{ $module['key'] }}">
    <header class="admin-system-header">
        <nav class="admin-system-breadcrumb" aria-label="Ruta de navegaciÃ³n"><span>AdministraciÃ³n</span><i class="bi bi-chevron-right" aria-hidden="true"></i><span>AdministraciÃ³n del sistema</span><i class="bi bi-chevron-right" aria-hidden="true"></i><strong>{{ $module['title'] }}</strong></nav>
        <div class="admin-system-heading-row">
            <div><span class="admin-system-eyebrow"><i class="bi {{ $module['icon'] }}" aria-hidden="true"></i> AdministraciÃ³n del sistema</span>
                <h1>{{ $module['content']['title'] }}</h1>
                <p>{{ $module['content']['description'] }}</p>
            </div>
            <div class="admin-system-actions"><button class="admin-system-button admin-system-button--secondary" type="button" data-pending-notice="La exportaciÃ³n estarÃ¡ disponible cuando exista una fuente de datos integrada."><i class="bi bi-download" aria-hidden="true"></i> Exportar</button><button class="admin-system-button admin-system-button--secondary" type="button" data-pending-notice="{{ $module['content']['action'] }} quedarÃ¡ disponible con la integraciÃ³n del mÃ³dulo."><i class="bi bi-bar-chart-line" aria-hidden="true"></i> {{ $module['content']['action'] }}</button>@if (($module['mode'] ?? 'consult') === 'crud')<button class="admin-system-button admin-system-button--primary" type="button" data-pending-open="crear"><i class="bi bi-plus-lg" aria-hidden="true"></i> {{ $module['content']['primary'] }}</button>@endif</div>
        </div>
    </header>

    {{-- Indicadores resumidos preparados por la capa de datos del módulo. --}}
    <section class="admin-system-kpis" aria-label="Indicadores de {{ $module['content']['title'] }}">
        @foreach ($module['kpis'] ?? [] as $kpi)
        <article class="admin-system-kpi admin-system-kpi--{{ $kpi['tone'] ?? 'blue' }}"><span class="admin-system-kpi-icon"><i class="bi {{ $kpi['icon'] }}" aria-hidden="true"></i></span>
            <div><small>{{ $kpi['label'] }}</small><strong>{{ $kpi['value'] ?? 'â€”' }}</strong><span>{{ $kpi['helper'] ?? 'Sin datos integrados' }}</span></div><i class="admin-system-kpi-spark" aria-hidden="true"></i>
        </article>
        @endforeach
    </section>

    {{-- Filtros visuales para buscar y clasificar registros reales. --}}
    <form class="admin-system-filters" data-pending-form><label class="admin-system-search"><i class="bi bi-search" aria-hidden="true"></i><span class="visually-hidden">Buscar {{ $module['record_plural'] ?? 'registros' }}</span><input type="search" placeholder="Buscar {{ $module['record_singular'] ?? 'registro' }}, mÃ³dulo o permiso..." data-pending-search></label><label><span class="visually-hidden">Tipo</span><select data-pending-filter>
                <option>Tipo: Todos</option>
                <option>Interno</option>
                <option>Cliente</option>
                <option>Operativo</option>
            </select></label><label><span class="visually-hidden">Estado</span><select data-pending-filter>
                <option>Estado: Todos</option>
                <option>Activo</option>
                <option>Pendiente</option>
                <option>Revisar</option>
            </select></label><label><span class="visually-hidden">Fecha</span><select data-pending-filter>
                <option>Registro: Este mes</option>
                <option>Ãšltimos 30 dÃ­as</option>
                <option>HistÃ³rico</option>
            </select></label><button class="admin-system-filter-button" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Filtrar</button><button class="admin-system-clear-button" type="button" data-pending-notice="Los filtros se limpiarÃ¡n cuando se conecte este mÃ³dulo.">Limpiar</button></form>
    <span class="admin-system-filter-status" data-admin-system-filter-status role="status" aria-live="polite"></span>

    {{-- Tabla principal con columnas, estado vacío y paginación visual. --}}
    <section class="admin-system-table-card">
        <header class="admin-system-card-header">
            <div><span class="admin-system-card-icon"><i class="bi {{ $module['icon'] }}" aria-hidden="true"></i></span>
                <div>
                    <h2>Listado de {{ $module['record_plural'] ?? 'registros' }}</h2>
                    <p>La informaciÃ³n aparecerÃ¡ cuando se conecte este mÃ³dulo al backend.</p>
                </div>
            </div><span class="admin-system-count">Sin registros</span>
        </header>
        <div class="admin-system-table-wrap">
            <table class="admin-system-table">
                <thead>
                    <tr>@foreach ($module['columns'] ?? [] as $column)<th>{{ $column }}</th>@endforeach @if (($module['mode'] ?? 'consult') === 'crud')<th>Acciones</th>@endif</tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="{{ max(1, count($module['columns'] ?? []) + (($module['mode'] ?? 'consult') === 'crud' ? 1 : 0)) }}">
                            <div class="admin-system-empty"><span><i class="bi bi-inbox" aria-hidden="true"></i></span><strong>No hay {{ $module['record_plural'] ?? 'registros' }} para mostrar</strong>
                                <p>El diseÃ±o estÃ¡ listo; los datos aparecerÃ¡n cuando exista la integraciÃ³n autorizada del sistema.</p>@if (($module['mode'] ?? 'consult') === 'crud')<button class="admin-system-empty-button" type="button" data-pending-open="crear"><i class="bi bi-plus-lg" aria-hidden="true"></i> {{ $module['content']['primary'] }}</button>@endif
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <footer class="admin-system-table-footer"><span>Sin resultados para mostrar</span>
            <nav class="admin-system-pagination" aria-label="PaginaciÃ³n deshabilitada"><button type="button" disabled aria-label="Anterior"><i class="bi bi-chevron-left" aria-hidden="true"></i></button><button type="button" class="is-active" disabled>1</button><button type="button" disabled aria-label="Siguiente"><i class="bi bi-chevron-right" aria-hidden="true"></i></button></nav>
        </footer>
    </section>

    {{-- Resúmenes secundarios del módulo, sin cálculos ni persistencia en la vista. --}}
    <section class="admin-system-summary-grid" aria-label="Resumen de {{ $module['content']['title'] }}">@foreach ($module['content']['summary'] as $summary)<article class="admin-system-summary-card">
            <header>
                <div><span class="admin-system-summary-icon"><i class="bi bi-stars" aria-hidden="true"></i></span>
                    <h2>{{ $summary }}</h2>
                </div><button type="button" aria-label="Ver resumen: {{ $summary }}" data-pending-notice="Este resumen se habilitarÃ¡ al integrar datos reales."><i class="bi bi-arrow-right" aria-hidden="true"></i></button>
            </header>
            <div class="admin-system-summary-empty"><i class="bi bi-bar-chart-line" aria-hidden="true"></i><strong>Sin datos integrados</strong><span>La informaciÃ³n aparecerÃ¡ aquÃ­.</span></div>
        </article>@endforeach</section>

    @if (($module['mode'] ?? 'consult') === 'crud')
    <dialog class="admin-system-dialog gestion-pendiente-modal" data-pending-modal="crear" aria-labelledby="{{ $module['key'] }}-crear-title">@include('gestion.administracion.' . $module['key'] . '.crear', ['module' => $module])</dialog>
    <dialog class="admin-system-dialog gestion-pendiente-modal" data-pending-modal="editar" aria-labelledby="{{ $module['key'] }}-editar-title">@include('gestion.administracion.' . $module['key'] . '.editar', ['module' => $module])</dialog>
    <dialog class="admin-system-dialog gestion-pendiente-modal" data-pending-modal="ver" aria-labelledby="{{ $module['key'] }}-ver-title">
        <div class="gestion-pendiente-modal-shell">
            <header class="gestion-pendiente-modal-header">
                <div><span class="gestion-pendiente-modal-icon"><i class="bi {{ $module['icon'] }}" aria-hidden="true"></i></span>
                    <div><span class="gestion-pendiente-eyebrow">{{ $module['area'] }}</span>
                        <h2 id="{{ $module['key'] }}-ver-title">Ver {{ $module['record_singular'] ?? 'registro' }}</h2>
                        <p>Detalle del registro seleccionado.</p>
                    </div>
                </div>
                <button type="button" class="gestion-pendiente-close" data-pending-close aria-label="Cerrar ventana"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
            </header>
            <div class="gestion-pendiente-modal-body">
                <div class="gestion-pendiente-modal-notice"><i class="bi bi-info-circle" aria-hidden="true"></i>
                    <div><strong>Vista preparada para integraciÃ³n</strong>
                        <p>No hay un registro real seleccionado. El detalle se poblarÃ¡ desde el backend cuando se conecte este mÃ³dulo.</p>
                    </div>
                </div>
                <div class="gestion-pendiente-detail-grid">
                    @foreach (($module['fields'] ?? []) as $field)
                    <div><small>{{ $field['label'] }}</small><strong>â€”</strong></div>
                    @endforeach
                </div>
            </div>
            <footer class="gestion-pendiente-modal-footer"><button type="button" class="gestion-pendiente-secondary" data-pending-close>Cerrar</button></footer>
        </div>
    </dialog>
    <dialog class="admin-system-dialog gestion-pendiente-modal gestion-pendiente-modal--danger" data-pending-modal="eliminar" aria-labelledby="{{ $module['key'] }}-eliminar-title">
        <div class="gestion-pendiente-modal-shell">
            <header class="gestion-pendiente-modal-header">
                <div><span class="gestion-pendiente-modal-icon gestion-pendiente-modal-icon--danger"><i class="bi bi-trash3" aria-hidden="true"></i></span>
                    <div><span class="gestion-pendiente-eyebrow">{{ $module['area'] }}</span>
                        <h2 id="{{ $module['key'] }}-eliminar-title">Eliminar {{ $module['record_singular'] ?? 'registro' }}</h2>
                        <p>Confirma la acciÃ³n antes de continuar.</p>
                    </div>
                </div>
                <button type="button" class="gestion-pendiente-close" data-pending-close aria-label="Cerrar ventana"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
            </header>
            <div class="gestion-pendiente-modal-body">
                <div class="gestion-pendiente-modal-warning"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                    <div><strong>AcciÃ³n no disponible todavÃ­a</strong>
                        <p>No se eliminÃ³ ningÃºn registro. Esta confirmaciÃ³n es visual y quedarÃ¡ conectada cuando exista una operaciÃ³n autorizada del backend.</p>
                    </div>
                </div>
            </div>
            <footer class="gestion-pendiente-modal-footer"><button type="button" class="gestion-pendiente-secondary" data-pending-close>Cancelar</button><button type="button" class="gestion-pendiente-danger" data-pending-notice="La eliminaciÃ³n permanece pendiente de integraciÃ³n y no modificÃ³ ningÃºn dato.">Eliminar cuando se integre</button></footer>
        </div>
    </dialog>
    @endif
    <div class="gestion-pendiente-feedback" data-pending-feedback role="status" aria-live="polite" hidden></div>
</section>

@else
{{-- Estado alternativo para módulos pendientes de integración con datos reales. --}}
<section class="gestion-pendiente-page gestion-pendiente-page--{{ $module['key'] }}" data-pending-module="{{ $module['key'] }}">
    <header class="gestion-pendiente-hero">
        <div class="gestion-pendiente-heading">
            <span class="gestion-pendiente-eyebrow"><i class="bi {{ $module['icon'] }}" aria-hidden="true"></i>{{ $module['area'] }}</span>
            <h1>{{ $module['title'] }}</h1>
            <p>{{ $module['description'] }}</p>
        </div>
        <div class="gestion-pendiente-hero-actions">
            <span class="gestion-pendiente-status"><i class="bi bi-link-45deg" aria-hidden="true"></i> Pendiente de integraciÃ³n</span>
            @if (($module['mode'] ?? 'consult') === 'crud')
            <button class="gestion-pendiente-primary" type="button" data-pending-open="crear">
                <i class="bi bi-plus-lg" aria-hidden="true"></i>
                Nuevo {{ $module['record_singular'] ?? 'registro' }}
            </button>
            @else
            <button class="gestion-pendiente-secondary" type="button" data-pending-notice="La exportaciÃ³n quedarÃ¡ habilitada cuando este mÃ³dulo tenga una fuente de datos integrada.">
                <i class="bi bi-download" aria-hidden="true"></i>
                Exportar consulta
            </button>
            @endif
        </div>
    </header>

    <div class="gestion-pendiente-kpis">
        @foreach ($module['kpis'] ?? [] as $kpi)
        <article class="gestion-pendiente-kpi gestion-pendiente-kpi--{{ $kpi['tone'] ?? 'blue' }}">
            <span class="gestion-pendiente-kpi-icon"><i class="bi {{ $kpi['icon'] }}" aria-hidden="true"></i></span>
            <div>
                <small>{{ $kpi['label'] }}</small>
                <strong>{{ $kpi['value'] ?? 'â€”' }}</strong>
                <span>{{ $kpi['helper'] ?? 'Sin datos integrados' }}</span>
            </div>
        </article>
        @endforeach
    </div>

    {{-- Bloque analítico únicamente visual; no consulta ni modifica datos. --}}
    @if ($module['analytics'] ?? [] !== [])
    <section class="gestion-pendiente-analytics" aria-label="AnÃ¡lisis visual de {{ $module['title'] }}">
        @foreach ($module['analytics'] ?? [] as $module['analytics'] ?? []Card)
        <article class="gestion-pendiente-analytics-card">
            <header class="gestion-pendiente-analytics-header">
                <div>
                    <span class="gestion-pendiente-analytics-icon"><i class="bi {{ $module['analytics'] ?? []Card['icon'] ?? 'bi-bar-chart-line' }}" aria-hidden="true"></i></span>
                    <div>
                        <h2>{{ $module['analytics'] ?? []Card['title'] }}</h2>
                        <p>{{ $module['analytics'] ?? []Card['description'] }}</p>
                    </div>
                </div>
                <span class="gestion-pendiente-chart-status">Sin datos</span>
            </header>

            @if (($module['analytics'] ?? []Card['type'] ?? 'line') === 'donut')
            <div class="gestion-pendiente-donut-layout">
                <div class="gestion-pendiente-donut" role="img" aria-label="GrÃ¡fica pendiente de {{ $module['analytics'] ?? []Card['title'] }}">
                    <div><strong>â€”</strong><span>Sin datos</span></div>
                </div>
                <div class="gestion-pendiente-chart-legend">
                    @foreach (($module['analytics'] ?? []Card['legend'] ?? []) as $legendItem)
                    <div><span class="gestion-pendiente-legend-dot gestion-pendiente-legend-dot--{{ $loop->index % 4 }}"></span><span>{{ $legendItem }}</span><strong>â€”</strong></div>
                    @endforeach
                </div>
            </div>
            @else
            <div class="gestion-pendiente-line-chart" role="img" aria-label="GrÃ¡fica pendiente de {{ $module['analytics'] ?? []Card['title'] }}">
                <div class="gestion-pendiente-chart-y-axis"><span>â€”</span><span>â€”</span><span>â€”</span><span>â€”</span></div>
                <div class="gestion-pendiente-chart-plot">
                    <svg viewBox="0 0 720 220" preserveAspectRatio="none" aria-hidden="true">
                        <path d="M0 168 C75 158 93 174 150 138 S260 162 316 104 S420 132 478 88 S600 126 720 54" class="gestion-pendiente-placeholder-line"></path>
                    </svg>
                    <div class="gestion-pendiente-chart-empty"><i class="bi bi-bar-chart-line" aria-hidden="true"></i><strong>Sin datos integrados</strong><span>La tendencia aparecerÃ¡ aquÃ­.</span></div>
                </div>
                <div class="gestion-pendiente-chart-x-axis">
                    @foreach (($module['analytics'] ?? []Card['labels'] ?? ['Periodo 1', 'Periodo 2', 'Periodo 3', 'Periodo 4', 'Periodo 5']) as $label)
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
            <span class="visually-hidden">Buscar {{ $module['record_plural'] ?? 'registros' }}</span>
            <input type="search" placeholder="Buscar {{ $module['record_plural'] ?? 'registros' }}..." data-pending-search>
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
        <button class="gestion-pendiente-secondary" type="button" data-pending-notice="Los filtros quedarÃ¡n disponibles cuando exista una fuente de datos para este mÃ³dulo.">
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
                        <h2>Listado de {{ $module['record_plural'] ?? 'registros' }}</h2>
                        <p>La informaciÃ³n aparecerÃ¡ cuando se conecte este mÃ³dulo al backend.</p>
                    </div>
                </div>
                <span class="gestion-pendiente-count">Sin registros</span>
            </header>

            <div class="gestion-pendiente-table-wrap">
                <table class="gestion-pendiente-table">
                    <thead>
                        <tr>
                            @foreach ($module['columns'] ?? [] as $column)
                            <th>{{ $column }}</th>
                            @endforeach
                            @if (($module['mode'] ?? 'consult') === 'crud')
                            <th>Acciones</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="{{ max(1, count($module['columns'] ?? []) + (($module['mode'] ?? 'consult') === 'crud' ? 1 : 0)) }}">
                                <div class="gestion-pendiente-empty">
                                    <span><i class="bi bi-inbox" aria-hidden="true"></i></span>
                                    <strong>No hay datos disponibles</strong>
                                    <p>Este mÃ³dulo estÃ¡ preparado visualmente y quedarÃ¡ disponible cuando se integre con datos reales.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <footer class="gestion-pendiente-card-footer">
                <span>Sin resultados para mostrar</span>
                <div class="gestion-pendiente-pagination" aria-label="PaginaciÃ³n deshabilitada">
                    <button type="button" disabled aria-label="PÃ¡gina anterior"><i class="bi bi-chevron-left" aria-hidden="true"></i></button>
                    <button type="button" class="is-active" disabled>1</button>
                    <button type="button" disabled aria-label="PÃ¡gina siguiente"><i class="bi bi-chevron-right" aria-hidden="true"></i></button>
                </div>
            </footer>
        </section>

        <aside class="gestion-pendiente-card gestion-pendiente-side-card">
            <header class="gestion-pendiente-card-header">
                <div>
                    <span class="gestion-pendiente-card-icon gestion-pendiente-card-icon--purple"><i class="bi bi-stars" aria-hidden="true"></i></span>
                    <div>
                        <h2>Estado del mÃ³dulo</h2>
                        <p>PreparaciÃ³n frontend</p>
                    </div>
                </div>
            </header>
            <div class="gestion-pendiente-integration">
                <div class="gestion-pendiente-integration-row"><i class="bi bi-check2-circle" aria-hidden="true"></i><span>Interfaz administrativa creada</span></div>
                <div class="gestion-pendiente-integration-row"><i class="bi bi-check2-circle" aria-hidden="true"></i><span>DiseÃ±o responsive preparado</span></div>
                <div class="gestion-pendiente-integration-row"><i class="bi bi-hourglass-split" aria-hidden="true"></i><span>ConexiÃ³n de datos pendiente</span></div>
            </div>
            @if (($module['mode'] ?? 'consult') === 'crud')
            <div class="gestion-pendiente-capabilities">
                <h3>Interfaces preparadas</h3>
                <button type="button" data-pending-open="crear"><i class="bi bi-plus-circle" aria-hidden="true"></i> Crear {{ $module['record_singular'] ?? 'registro' }}</button>
                <button type="button" data-pending-open="ver"><i class="bi bi-eye" aria-hidden="true"></i> Ver detalle</button>
                <button type="button" data-pending-open="editar"><i class="bi bi-pencil-square" aria-hidden="true"></i> Editar registro</button>
                <button type="button" data-pending-open="eliminar"><i class="bi bi-trash3" aria-hidden="true"></i> Confirmar eliminaciÃ³n</button>
            </div>
            @else
            <button class="gestion-pendiente-secondary gestion-pendiente-secondary--full" type="button" data-pending-notice="La consulta quedarÃ¡ habilitada cuando exista el origen de datos correspondiente.">
                <i class="bi bi-bar-chart-line" aria-hidden="true"></i>
                Preparar consulta
            </button>
            @endif
        </aside>
    </div>

    @if (($module['mode'] ?? 'consult') === 'crud')
    <dialog class="gestion-pendiente-modal" data-pending-modal="crear" aria-labelledby="{{ $module['key'] }}-crear-title">
        @include('gestion.' . $module['key'] . '.crear', ['module' => $module])
    </dialog>
    <dialog class="gestion-pendiente-modal" data-pending-modal="editar" aria-labelledby="{{ $module['key'] }}-editar-title">
        @include('gestion.' . $module['key'] . '.editar', ['module' => $module])
    </dialog>
    <dialog class="gestion-pendiente-modal" data-pending-modal="ver" aria-labelledby="{{ $module['key'] }}-ver-title">
        <div class="gestion-pendiente-modal-shell">
            <header class="gestion-pendiente-modal-header">
                <div><span class="gestion-pendiente-modal-icon"><i class="bi {{ $module['icon'] }}" aria-hidden="true"></i></span>
                    <div><span class="gestion-pendiente-eyebrow">{{ $module['area'] }}</span>
                        <h2 id="{{ $module['key'] }}-ver-title">Ver {{ $module['record_singular'] ?? 'registro' }}</h2>
                        <p>Detalle del registro seleccionado.</p>
                    </div>
                </div>
                <button type="button" class="gestion-pendiente-close" data-pending-close aria-label="Cerrar ventana"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
            </header>
            <div class="gestion-pendiente-modal-body">
                <div class="gestion-pendiente-modal-notice"><i class="bi bi-info-circle" aria-hidden="true"></i>
                    <div><strong>Vista preparada para integraciÃ³n</strong>
                        <p>No hay un registro real seleccionado. El detalle se poblarÃ¡ desde el backend cuando se conecte este mÃ³dulo.</p>
                    </div>
                </div>
                <div class="gestion-pendiente-detail-grid">
                    @foreach (($module['fields'] ?? []) as $field)
                    <div><small>{{ $field['label'] }}</small><strong>â€”</strong></div>
                    @endforeach
                </div>
            </div>
            <footer class="gestion-pendiente-modal-footer"><button type="button" class="gestion-pendiente-secondary" data-pending-close>Cerrar</button></footer>
        </div>

    </dialog>
    <dialog class="gestion-pendiente-modal gestion-pendiente-modal--danger" data-pending-modal="eliminar" aria-labelledby="{{ $module['key'] }}-eliminar-title">
        <div class="gestion-pendiente-modal-shell">
            <header class="gestion-pendiente-modal-header">
                <div><span class="gestion-pendiente-modal-icon gestion-pendiente-modal-icon--danger"><i class="bi bi-trash3" aria-hidden="true"></i></span>
                    <div><span class="gestion-pendiente-eyebrow">{{ $module['area'] }}</span>
                        <h2 id="{{ $module['key'] }}-eliminar-title">Eliminar {{ $module['record_singular'] ?? 'registro' }}</h2>
                        <p>Confirma la acciÃ³n antes de continuar.</p>
                    </div>
                </div>
                <button type="button" class="gestion-pendiente-close" data-pending-close aria-label="Cerrar ventana"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
            </header>
            <div class="gestion-pendiente-modal-body">
                <div class="gestion-pendiente-modal-warning"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                    <div><strong>AcciÃ³n no disponible todavÃ­a</strong>
                        <p>No se eliminÃ³ ningÃºn registro. Esta confirmaciÃ³n es visual y quedarÃ¡ conectada cuando exista una operaciÃ³n autorizada del backend.</p>
                    </div>
                </div>
            </div>
            <footer class="gestion-pendiente-modal-footer"><button type="button" class="gestion-pendiente-secondary" data-pending-close>Cancelar</button><button type="button" class="gestion-pendiente-danger" data-pending-notice="La eliminaciÃ³n permanece pendiente de integraciÃ³n y no modificÃ³ ningÃºn dato.">Eliminar cuando se integre</button></footer>
        </div>

    </dialog>
    @endif

    <div class="gestion-pendiente-feedback" data-pending-feedback role="status" aria-live="polite" hidden></div>
</section>

@endif
@endsection