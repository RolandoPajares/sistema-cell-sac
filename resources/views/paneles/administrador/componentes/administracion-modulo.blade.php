@php
    $moduleKey = $module['key'];
    $isCrud = ($module['mode'] ?? 'consult') === 'crud';
    $columns = $module['columns'] ?? [];
    $kpis = $module['kpis'] ?? [];
    $recordSingular = $module['record_singular'] ?? 'registro';
    $recordPlural = $module['record_plural'] ?? 'registros';

    $content = [
        'roles-permisos' => [
            'title' => 'Gestión de roles y permisos',
            'description' => 'Consulta los accesos asignados y supervisa las autorizaciones del sistema.',
            'action' => 'Revisar permisos',
            'primary' => 'Nuevo rol',
            'summary' => ['Distribución de cuentas', 'Vista de permisos por módulo', 'Control de cambios'],
        ],
        'notificaciones' => [
            'title' => 'Gestión de notificaciones',
            'description' => 'Organiza avisos, plantillas y comunicaciones del sistema desde un solo panel.',
            'action' => 'Gestionar plantillas',
            'primary' => 'Nueva notificación',
            'summary' => ['Canales de comunicación', 'Alertas de entrega', 'Actividad reciente'],
        ],
        'integraciones' => [
            'title' => 'Gestión de integraciones',
            'description' => 'Organiza servicios externos y prepara conexiones seguras para el sistema.',
            'action' => 'Ver documentación',
            'primary' => 'Nueva integración',
            'summary' => ['Seguridad de integraciones', 'Pendientes de configuración', 'Historial de conexiones'],
        ],
        'respaldos-mantenimiento' => [
            'title' => 'Respaldos y mantenimiento',
            'description' => 'Prepara copias de seguridad, controla tareas preventivas y revisa la recuperación del sistema.',
            'action' => 'Ver bitácora',
            'primary' => 'Crear plan de respaldo',
            'summary' => ['Planes de respaldo', 'Tareas de mantenimiento', 'Seguridad y recuperación'],
        ],
    ][$moduleKey] ?? [
        'title' => $module['title'],
        'description' => $module['description'],
        'action' => 'Ver actividad',
        'primary' => 'Nuevo registro',
        'summary' => [],
    ];
@endphp

<section class="admin-system-module admin-system-module--{{ $moduleKey }}" data-pending-module="{{ $moduleKey }}" data-admin-system-module="{{ $moduleKey }}">
    <header class="admin-system-header">
        <nav class="admin-system-breadcrumb" aria-label="Ruta de navegación"><span>Administración</span><i class="bi bi-chevron-right" aria-hidden="true"></i><span>Administración del sistema</span><i class="bi bi-chevron-right" aria-hidden="true"></i><strong>{{ $module['title'] }}</strong></nav>
        <div class="admin-system-heading-row">
            <div><span class="admin-system-eyebrow"><i class="bi {{ $module['icon'] }}" aria-hidden="true"></i> Administración del sistema</span><h1>{{ $content['title'] }}</h1><p>{{ $content['description'] }}</p></div>
            <div class="admin-system-actions"><button class="admin-system-button admin-system-button--secondary" type="button" data-pending-notice="La exportación estará disponible cuando exista una fuente de datos integrada."><i class="bi bi-download" aria-hidden="true"></i> Exportar</button><button class="admin-system-button admin-system-button--secondary" type="button" data-pending-notice="{{ $content['action'] }} quedará disponible con la integración del módulo."><i class="bi bi-bar-chart-line" aria-hidden="true"></i> {{ $content['action'] }}</button>@if ($isCrud)<button class="admin-system-button admin-system-button--primary" type="button" data-pending-open="crear"><i class="bi bi-plus-lg" aria-hidden="true"></i> {{ $content['primary'] }}</button>@endif</div>
        </div>
    </header>

    <section class="admin-system-kpis" aria-label="Indicadores de {{ $content['title'] }}">
        @foreach ($kpis as $kpi)
            <article class="admin-system-kpi admin-system-kpi--{{ $kpi['tone'] ?? 'blue' }}"><span class="admin-system-kpi-icon"><i class="bi {{ $kpi['icon'] }}" aria-hidden="true"></i></span><div><small>{{ $kpi['label'] }}</small><strong>{{ $kpi['value'] ?? '—' }}</strong><span>{{ $kpi['helper'] ?? 'Sin datos integrados' }}</span></div><i class="admin-system-kpi-spark" aria-hidden="true"></i></article>
        @endforeach
    </section>

    <form class="admin-system-filters" data-pending-form><label class="admin-system-search"><i class="bi bi-search" aria-hidden="true"></i><span class="visually-hidden">Buscar {{ $recordPlural }}</span><input type="search" placeholder="Buscar {{ $recordSingular }}, módulo o permiso..." data-pending-search></label><label><span class="visually-hidden">Tipo</span><select data-pending-filter><option>Tipo: Todos</option><option>Interno</option><option>Cliente</option><option>Operativo</option></select></label><label><span class="visually-hidden">Estado</span><select data-pending-filter><option>Estado: Todos</option><option>Activo</option><option>Pendiente</option><option>Revisar</option></select></label><label><span class="visually-hidden">Fecha</span><select data-pending-filter><option>Registro: Este mes</option><option>Últimos 30 días</option><option>Histórico</option></select></label><button class="admin-system-filter-button" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Filtrar</button><button class="admin-system-clear-button" type="button" data-pending-notice="Los filtros se limpiarán cuando se conecte este módulo.">Limpiar</button></form>
    <span class="admin-system-filter-status" data-admin-system-filter-status role="status" aria-live="polite"></span>

    <section class="admin-system-table-card"><header class="admin-system-card-header"><div><span class="admin-system-card-icon"><i class="bi {{ $module['icon'] }}" aria-hidden="true"></i></span><div><h2>Listado de {{ $recordPlural }}</h2><p>La información aparecerá cuando se conecte este módulo al backend.</p></div></div><span class="admin-system-count">Sin registros</span></header><div class="admin-system-table-wrap"><table class="admin-system-table"><thead><tr>@foreach ($columns as $column)<th>{{ $column }}</th>@endforeach @if ($isCrud)<th>Acciones</th>@endif</tr></thead><tbody><tr><td colspan="{{ max(1, count($columns) + ($isCrud ? 1 : 0)) }}"><div class="admin-system-empty"><span><i class="bi bi-inbox" aria-hidden="true"></i></span><strong>No hay {{ $recordPlural }} para mostrar</strong><p>El diseño está listo; los datos aparecerán cuando exista la integración autorizada del sistema.</p>@if ($isCrud)<button class="admin-system-empty-button" type="button" data-pending-open="crear"><i class="bi bi-plus-lg" aria-hidden="true"></i> {{ $content['primary'] }}</button>@endif</div></td></tr></tbody></table></div><footer class="admin-system-table-footer"><span>Sin resultados para mostrar</span><nav class="admin-system-pagination" aria-label="Paginación deshabilitada"><button type="button" disabled aria-label="Anterior"><i class="bi bi-chevron-left" aria-hidden="true"></i></button><button type="button" class="is-active" disabled>1</button><button type="button" disabled aria-label="Siguiente"><i class="bi bi-chevron-right" aria-hidden="true"></i></button></nav></footer></section>

    <section class="admin-system-summary-grid" aria-label="Resumen de {{ $content['title'] }}">@foreach ($content['summary'] as $summary)<article class="admin-system-summary-card"><header><div><span class="admin-system-summary-icon"><i class="bi bi-stars" aria-hidden="true"></i></span><h2>{{ $summary }}</h2></div><button type="button" data-pending-notice="Este resumen se habilitará al integrar datos reales."><i class="bi bi-arrow-right" aria-hidden="true"></i></button></header><div class="admin-system-summary-empty"><i class="bi bi-bar-chart-line" aria-hidden="true"></i><strong>Sin datos integrados</strong><span>La información aparecerá aquí.</span></div></article>@endforeach</section>

    @if ($isCrud)
        <dialog class="admin-system-dialog gestion-pendiente-modal" data-pending-modal="crear" aria-labelledby="{{ $moduleKey }}-crear-title">@include('paneles.administrador.administracion.' . $moduleKey . '.crear', ['module' => $module])</dialog>
        <dialog class="admin-system-dialog gestion-pendiente-modal" data-pending-modal="editar" aria-labelledby="{{ $moduleKey }}-editar-title">@include('paneles.administrador.administracion.' . $moduleKey . '.editar', ['module' => $module])</dialog>
        <dialog class="admin-system-dialog gestion-pendiente-modal" data-pending-modal="ver" aria-labelledby="{{ $moduleKey }}-ver-title">@include('paneles.administrador.administracion.' . $moduleKey . '.ver', ['module' => $module])</dialog>
        <dialog class="admin-system-dialog gestion-pendiente-modal gestion-pendiente-modal--danger" data-pending-modal="eliminar" aria-labelledby="{{ $moduleKey }}-eliminar-title">@include('paneles.administrador.administracion.' . $moduleKey . '.eliminar', ['module' => $module])</dialog>
    @endif
    <div class="gestion-pendiente-feedback" data-pending-feedback role="status" aria-live="polite" hidden></div>
</section>
