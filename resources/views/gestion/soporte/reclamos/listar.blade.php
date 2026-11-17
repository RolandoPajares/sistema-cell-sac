@extends('layouts.panel.principal')


{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('title', 'Soporte y reclamos')
{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('body_class', 'modulo-soporte-reclamos')

{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('content')
<section class="gestion-soporte-page" data-pending-module="soporte-reclamos" data-backend="true">
    <header class="gestion-soporte-header">
        <nav class="gestion-soporte-breadcrumb" aria-label="Ruta de navegación">
            <span>Administración</span><i class="bi bi-chevron-right" aria-hidden="true"></i>
            <span>Relaciones comerciales</span><i class="bi bi-chevron-right" aria-hidden="true"></i>
            <strong>Soporte y reclamos</strong>
        </nav>
        <div class="gestion-soporte-heading-row">
            <div class="gestion-soporte-heading">
                <span class="gestion-soporte-eyebrow"><i class="bi bi-headset" aria-hidden="true"></i>{{ $module['area'] }}</span>
                <h1>Gestión de soporte y reclamos</h1>
                <p>Atiende solicitudes e incidencias utilizando casos reales de MariaDB.</p>
            </div>
            <div class="gestion-soporte-actions">
                <details class="gestion-soporte-export-menu"><summary class="gestion-soporte-button gestion-soporte-button--secondary"><i class="bi bi-download" aria-hidden="true"></i> Exportar</summary><div class="gestion-soporte-export-options"><a href="{{ route('management.support.export', ['format' => 'excel'] + request()->query()) }}">Excel (.xlsx)</a><a href="{{ route('management.support.export', ['format' => 'pdf'] + request()->query()) }}">PDF (.pdf)</a><a href="{{ route('management.support.export', ['format' => 'csv'] + request()->query()) }}">CSV (.csv)</a></div></details>
                <button class="gestion-soporte-button gestion-soporte-button--secondary" type="button" data-pending-notice="Los reportes se habilitarán cuando exista una fuente de datos integrada."><i class="bi bi-bar-chart-line" aria-hidden="true"></i> Ver reportes</button>
                <button class="gestion-soporte-button gestion-soporte-button--primary" type="button" data-pending-open="crear"><i class="bi bi-plus-lg" aria-hidden="true"></i> Nuevo ticket</button>
            </div>
        </div>
    </header>

    <section class="gestion-soporte-kpis" aria-label="Indicadores de soporte">
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
        @foreach ($module['kpis'] as $kpi)
            <article class="gestion-soporte-kpi gestion-soporte-kpi--{{ $kpi['tone'] }}">
                <span class="gestion-soporte-kpi-icon"><i class="bi {{ $kpi['icon'] }}" aria-hidden="true"></i></span>
                <div><small>{{ $kpi['label'] }}</small><strong>{{ $kpi['value'] }}</strong><span>{{ $kpi['helper'] }}</span></div>
                <span class="gestion-soporte-kpi-spark" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i><i></i></span>
            </article>
        @endforeach
    </section>

{{-- formulario que recopila datos y los envia a la ruta indicada. --}}
    <form class="gestion-soporte-filters" method="GET" action="{{ route('management.support') }}">
        <label class="gestion-soporte-search"><i class="bi bi-search" aria-hidden="true"></i><span class="visually-hidden">Buscar tickets</span><input name="buscar" type="search" value="{{ $module['filters']['buscar'] ?? '' }}" placeholder="Buscar ticket o asunto..."></label>
        <label><span class="visually-hidden">Filtrar por prioridad</span><select name="prioridad"><option value="">Prioridad: Todas</option><option value="alta" @selected(($module['filters']['prioridad'] ?? '') === 'alta')>Alta</option><option value="normal" @selected(($module['filters']['prioridad'] ?? '') === 'normal')>Normal</option><option value="baja" @selected(($module['filters']['prioridad'] ?? '') === 'baja')>Baja</option><option value="urgente" @selected(($module['filters']['prioridad'] ?? '') === 'urgente')>Urgente</option></select></label>
        <label><span class="visually-hidden">Filtrar por estado</span><select name="estado"><option value="">Estado: Todos</option><option value="abierto" @selected(($module['filters']['estado'] ?? '') === 'abierto')>Abierto</option><option value="en_proceso" @selected(($module['filters']['estado'] ?? '') === 'en_proceso')>En proceso</option><option value="resuelto" @selected(($module['filters']['estado'] ?? '') === 'resuelto')>Resuelto</option><option value="cerrado" @selected(($module['filters']['estado'] ?? '') === 'cerrado')>Cerrado</option></select></label>
        <label><span class="visually-hidden">Filtrar por fecha</span><select data-pending-filter><option value="">Fecha: Este mes</option><option>Esta semana</option><option>Últimos 30 días</option></select></label>
        <button class="gestion-soporte-filter-button" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Filtrar</button>
        <a class="gestion-soporte-clear-button" href="{{ route('management.support') }}">Limpiar</a>
    </form>

    <section class="gestion-soporte-table-card">
        <header class="gestion-soporte-card-header">
            <div><span class="gestion-soporte-card-icon"><i class="bi bi-headset" aria-hidden="true"></i></span><div><h2>Centro de soporte y reclamos</h2><p>Gestiona cada solicitud desde una única bandeja de atención.</p></div></div>
                <span class="gestion-soporte-count">{{ count($module['rows'] ?? []) }} en esta página</span>
        </header>
        <div class="gestion-soporte-table-wrap">
            <table class="gestion-soporte-table">
                <thead><tr>@foreach ($module['tableColumns'] as $column)<th>{{ $column }}</th>@endforeach</tr></thead>
                <tbody>
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
                @forelse ($module['rows'] ?? [] as $row)
                    <tr><td>{{ $row['codigo'] }}</td><td>{{ $row['cliente'] }}</td><td>{{ $row['asunto'] }}</td><td>{{ $row['prioridad'] }}</td><td>{{ $row['estado'] }}</td><td>{{ $row['actualizado'] }}</td><td><button type="button" aria-label="Ver caso" data-pending-open="ver" data-pending-record="{{ base64_encode(json_encode($row['record'], JSON_UNESCAPED_UNICODE)) }}"><i class="bi bi-eye" aria-hidden="true"></i></button><button type="button" aria-label="Editar caso" data-pending-open="editar" data-pending-action="{{ $row['edit_url'] }}" data-pending-record="{{ base64_encode(json_encode($row['record'], JSON_UNESCAPED_UNICODE)) }}"><i class="bi bi-pencil" aria-hidden="true"></i></button><button type="button" aria-label="Cerrar caso" data-pending-open="eliminar" data-pending-action="{{ $row['delete_url'] }}" data-pending-method="PATCH" data-pending-record="{{ base64_encode(json_encode($row['record'], JSON_UNESCAPED_UNICODE)) }}"><i class="bi bi-check2-circle" aria-hidden="true"></i></button></td></tr>
                @empty
                    <tr><td colspan="{{ count($module['tableColumns']) }}"><div class="gestion-soporte-empty"><span><i class="bi bi-inbox" aria-hidden="true"></i></span><strong>No hay casos para mostrar</strong><p>No existen casos que coincidan con el alcance y filtros actuales.</p></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <footer class="gestion-soporte-table-footer"><span>{{ $module['pagination']->total() }} registros; mostrando {{ $module['pagination']->firstItem() ?: 0 }} a {{ $module['pagination']->lastItem() ?: 0 }}</span><div class="gestion-soporte-pagination" aria-label="Paginación">@if ($module['pagination']->onFirstPage())<button type="button" disabled aria-label="Página anterior"><i class="bi bi-chevron-left" aria-hidden="true"></i></button>@else<a href="{{ $module['pagination']->previousPageUrl() }}" aria-label="Página anterior"><i class="bi bi-chevron-left" aria-hidden="true"></i></a>@endif @for ($page = 1; $page <= $module['pagination']->lastPage(); $page++)<a href="{{ $module['pagination']->url($page) }}" class="{{ $page === $module['pagination']->currentPage() ? 'is-active' : '' }}">{{ $page }}</a>@endfor @if ($module['pagination']->hasMorePages())<a href="{{ $module['pagination']->nextPageUrl() }}" aria-label="Página siguiente"><i class="bi bi-chevron-right" aria-hidden="true"></i></a>@else<button type="button" disabled aria-label="Página siguiente"><i class="bi bi-chevron-right" aria-hidden="true"></i></button>@endif</div></footer>
    </section>

    <section class="gestion-soporte-bottom-grid" aria-label="Resumen de soporte">
        <article class="gestion-soporte-summary-card">
            <header><div><span class="gestion-soporte-summary-icon gestion-soporte-summary-icon--red"><i class="bi bi-bell" aria-hidden="true"></i></span><div><h2>Alertas de atención</h2><p>Situaciones que requieren seguimiento.</p></div></div><button type="button" data-pending-notice="Las prioridades se mostrarán cuando existan tickets reales.">Ver prioridades <i class="bi bi-arrow-right" aria-hidden="true"></i></button></header>
            <div class="gestion-soporte-alert-empty"><i class="bi bi-check-circle" aria-hidden="true"></i><strong>Operación al día</strong><span>No hay alertas de atención.</span></div>
        </article>
        <article class="gestion-soporte-summary-card">
            <header><div><span class="gestion-soporte-summary-icon gestion-soporte-summary-icon--blue"><i class="bi bi-clock-history" aria-hidden="true"></i></span><div><h2>Actividad reciente</h2><p>Últimos movimientos del servicio.</p></div></div><button type="button" data-pending-notice="La actividad aparecerá cuando existan tickets reales.">Ver actividad <i class="bi bi-arrow-right" aria-hidden="true"></i></button></header>
            <div class="gestion-soporte-activity-empty"><i class="bi bi-clock" aria-hidden="true"></i><strong>Sin actividad reciente</strong><span>Las acciones auditadas aparecerán aquí.</span></div>
        </article>
        <article class="gestion-soporte-summary-card gestion-soporte-quality-card">
            <header><div><span class="gestion-soporte-summary-icon gestion-soporte-summary-icon--gold"><i class="bi bi-star" aria-hidden="true"></i></span><div><h2>Calidad de atención</h2><p>Indicadores del servicio al cliente.</p></div></div><button type="button" data-pending-notice="Las métricas se habilitarán con datos de atención.">Ver métricas <i class="bi bi-arrow-right" aria-hidden="true"></i></button></header>
            <div class="gestion-soporte-quality-empty"><strong>— / 5</strong><span>Satisfacción del cliente</span><div><i></i></div><small>La calidad aparecerá cuando existan valoraciones.</small></div>
        </article>
    </section>

    <dialog class="gestion-pendiente-modal" data-pending-modal="crear" aria-labelledby="soporte-reclamos-crear-title">@include('gestion.soporte.reclamos.crear', ['module' => $module])</dialog>
    <dialog class="gestion-pendiente-modal" data-pending-modal="editar" aria-labelledby="soporte-reclamos-editar-title">@include('gestion.soporte.reclamos.editar', ['module' => $module])</dialog>
    <dialog class="gestion-pendiente-modal" data-pending-modal="ver" aria-labelledby="soporte-reclamos-ver-title"><div class="gestion-pendiente-modal-shell">
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
    <dialog class="gestion-pendiente-modal gestion-pendiente-modal--danger" data-pending-modal="eliminar" aria-labelledby="soporte-reclamos-eliminar-title"><div class="gestion-pendiente-modal-shell">
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

