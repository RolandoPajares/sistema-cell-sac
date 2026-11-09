<?php $__env->startSection('title', 'Soporte y reclamos'); ?>
<?php $__env->startSection('body_class', 'modulo-soporte-reclamos'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $kpis = $module['kpis'];
    $tableColumns = ['Ticket', 'Cliente', 'Asunto', 'Prioridad', 'Estado', 'Actualización', 'Acciones'];
?>

<section class="gestion-soporte-page" data-pending-module="soporte-reclamos" data-backend="true">
    <header class="gestion-soporte-header">
        <nav class="gestion-soporte-breadcrumb" aria-label="Ruta de navegación">
            <span>Administración</span><i class="bi bi-chevron-right" aria-hidden="true"></i>
            <span>Relaciones comerciales</span><i class="bi bi-chevron-right" aria-hidden="true"></i>
            <strong>Soporte y reclamos</strong>
        </nav>
        <div class="gestion-soporte-heading-row">
            <div class="gestion-soporte-heading">
                <span class="gestion-soporte-eyebrow"><i class="bi bi-headset" aria-hidden="true"></i><?php echo e($module['area']); ?></span>
                <h1>Gestión de soporte y reclamos</h1>
                <p>Atiende solicitudes e incidencias utilizando casos reales de MariaDB.</p>
            </div>
            <div class="gestion-soporte-actions">
                <details class="gestion-soporte-export-menu"><summary class="gestion-soporte-button gestion-soporte-button--secondary"><i class="bi bi-download" aria-hidden="true"></i> Exportar</summary><div class="gestion-soporte-export-options"><a href="<?php echo e(route('management.support.export', ['format' => 'excel'] + request()->query())); ?>">Excel (.xlsx)</a><a href="<?php echo e(route('management.support.export', ['format' => 'pdf'] + request()->query())); ?>">PDF (.pdf)</a><a href="<?php echo e(route('management.support.export', ['format' => 'csv'] + request()->query())); ?>">CSV (.csv)</a></div></details>
                <button class="gestion-soporte-button gestion-soporte-button--secondary" type="button" data-pending-notice="Los reportes se habilitarán cuando exista una fuente de datos integrada."><i class="bi bi-bar-chart-line" aria-hidden="true"></i> Ver reportes</button>
                <button class="gestion-soporte-button gestion-soporte-button--primary" type="button" data-pending-open="crear"><i class="bi bi-plus-lg" aria-hidden="true"></i> Nuevo ticket</button>
            </div>
        </div>
    </header>

    <section class="gestion-soporte-kpis" aria-label="Indicadores de soporte">
        <?php $__currentLoopData = $kpis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kpi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <article class="gestion-soporte-kpi gestion-soporte-kpi--<?php echo e($kpi['tone']); ?>">
                <span class="gestion-soporte-kpi-icon"><i class="bi <?php echo e($kpi['icon']); ?>" aria-hidden="true"></i></span>
                <div><small><?php echo e($kpi['label']); ?></small><strong><?php echo e($kpi['value']); ?></strong><span><?php echo e($kpi['helper']); ?></span></div>
                <span class="gestion-soporte-kpi-spark" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i><i></i></span>
            </article>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </section>

    <form class="gestion-soporte-filters" method="GET" action="<?php echo e(route('management.support')); ?>">
        <label class="gestion-soporte-search"><i class="bi bi-search" aria-hidden="true"></i><span class="visually-hidden">Buscar tickets</span><input name="buscar" type="search" value="<?php echo e($module['filters']['buscar'] ?? ''); ?>" placeholder="Buscar ticket o asunto..."></label>
        <label><span class="visually-hidden">Filtrar por prioridad</span><select name="prioridad"><option value="">Prioridad: Todas</option><option value="alta" <?php if(($module['filters']['prioridad'] ?? '') === 'alta'): echo 'selected'; endif; ?>>Alta</option><option value="normal" <?php if(($module['filters']['prioridad'] ?? '') === 'normal'): echo 'selected'; endif; ?>>Normal</option><option value="baja" <?php if(($module['filters']['prioridad'] ?? '') === 'baja'): echo 'selected'; endif; ?>>Baja</option><option value="urgente" <?php if(($module['filters']['prioridad'] ?? '') === 'urgente'): echo 'selected'; endif; ?>>Urgente</option></select></label>
        <label><span class="visually-hidden">Filtrar por estado</span><select name="estado"><option value="">Estado: Todos</option><option value="abierto" <?php if(($module['filters']['estado'] ?? '') === 'abierto'): echo 'selected'; endif; ?>>Abierto</option><option value="en_proceso" <?php if(($module['filters']['estado'] ?? '') === 'en_proceso'): echo 'selected'; endif; ?>>En proceso</option><option value="resuelto" <?php if(($module['filters']['estado'] ?? '') === 'resuelto'): echo 'selected'; endif; ?>>Resuelto</option><option value="cerrado" <?php if(($module['filters']['estado'] ?? '') === 'cerrado'): echo 'selected'; endif; ?>>Cerrado</option></select></label>
        <label><span class="visually-hidden">Filtrar por fecha</span><select data-pending-filter><option value="">Fecha: Este mes</option><option>Esta semana</option><option>Últimos 30 días</option></select></label>
        <button class="gestion-soporte-filter-button" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Filtrar</button>
        <a class="gestion-soporte-clear-button" href="<?php echo e(route('management.support')); ?>">Limpiar</a>
    </form>

    <section class="gestion-soporte-table-card">
        <header class="gestion-soporte-card-header">
            <div><span class="gestion-soporte-card-icon"><i class="bi bi-headset" aria-hidden="true"></i></span><div><h2>Centro de soporte y reclamos</h2><p>Gestiona cada solicitud desde una única bandeja de atención.</p></div></div>
                <span class="gestion-soporte-count"><?php echo e(count($module['rows'] ?? [])); ?> en esta página</span>
        </header>
        <div class="gestion-soporte-table-wrap">
            <table class="gestion-soporte-table">
                <thead><tr><?php $__currentLoopData = $tableColumns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><th><?php echo e($column); ?></th><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></tr></thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $module['rows'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr><td><?php echo e($row['codigo']); ?></td><td><?php echo e($row['cliente']); ?></td><td><?php echo e($row['asunto']); ?></td><td><?php echo e($row['prioridad']); ?></td><td><?php echo e($row['estado']); ?></td><td><?php echo e($row['actualizado']); ?></td><td><button type="button" aria-label="Ver caso" data-pending-open="ver" data-pending-record="<?php echo e(base64_encode(json_encode($row['record'], JSON_UNESCAPED_UNICODE))); ?>"><i class="bi bi-eye" aria-hidden="true"></i></button><button type="button" aria-label="Editar caso" data-pending-open="editar" data-pending-action="<?php echo e($row['edit_url']); ?>" data-pending-record="<?php echo e(base64_encode(json_encode($row['record'], JSON_UNESCAPED_UNICODE))); ?>"><i class="bi bi-pencil" aria-hidden="true"></i></button><button type="button" aria-label="Cerrar caso" data-pending-open="eliminar" data-pending-action="<?php echo e($row['delete_url']); ?>" data-pending-method="PATCH" data-pending-record="<?php echo e(base64_encode(json_encode($row['record'], JSON_UNESCAPED_UNICODE))); ?>"><i class="bi bi-check2-circle" aria-hidden="true"></i></button></td></tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="<?php echo e(count($tableColumns)); ?>"><div class="gestion-soporte-empty"><span><i class="bi bi-inbox" aria-hidden="true"></i></span><strong>No hay casos para mostrar</strong><p>No existen casos que coincidan con el alcance y filtros actuales.</p></div></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <footer class="gestion-soporte-table-footer"><span><?php echo e($module['pagination']->total()); ?> registros; mostrando <?php echo e($module['pagination']->firstItem() ?: 0); ?> a <?php echo e($module['pagination']->lastItem() ?: 0); ?></span><div class="gestion-soporte-pagination" aria-label="Paginación"><?php if($module['pagination']->onFirstPage()): ?><button type="button" disabled aria-label="Página anterior"><i class="bi bi-chevron-left" aria-hidden="true"></i></button><?php else: ?><a href="<?php echo e($module['pagination']->previousPageUrl()); ?>" aria-label="Página anterior"><i class="bi bi-chevron-left" aria-hidden="true"></i></a><?php endif; ?> <?php for($page = 1; $page <= $module['pagination']->lastPage(); $page++): ?><a href="<?php echo e($module['pagination']->url($page)); ?>" class="<?php echo e($page === $module['pagination']->currentPage() ? 'is-active' : ''); ?>"><?php echo e($page); ?></a><?php endfor; ?> <?php if($module['pagination']->hasMorePages()): ?><a href="<?php echo e($module['pagination']->nextPageUrl()); ?>" aria-label="Página siguiente"><i class="bi bi-chevron-right" aria-hidden="true"></i></a><?php else: ?><button type="button" disabled aria-label="Página siguiente"><i class="bi bi-chevron-right" aria-hidden="true"></i></button><?php endif; ?></div></footer>
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

    <dialog class="gestion-pendiente-modal" data-pending-modal="crear" aria-labelledby="soporte-reclamos-crear-title"><?php echo $__env->make('gestion.soporte.reclamos.crear', ['module' => $module], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></dialog>
    <dialog class="gestion-pendiente-modal" data-pending-modal="editar" aria-labelledby="soporte-reclamos-editar-title"><?php echo $__env->make('gestion.soporte.reclamos.editar', ['module' => $module], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></dialog>
    <dialog class="gestion-pendiente-modal" data-pending-modal="ver" aria-labelledby="soporte-reclamos-ver-title"><div class="gestion-pendiente-modal-shell">
    <header class="gestion-pendiente-modal-header">
        <div><span class="gestion-pendiente-modal-icon"><i class="bi <?php echo e($module['icon']); ?>" aria-hidden="true"></i></span><div><span class="gestion-pendiente-eyebrow"><?php echo e($module['area']); ?></span><h2 id="<?php echo e($module['key']); ?>-ver-title">Ver <?php echo e($module['record_singular'] ?? 'registro'); ?></h2><p>Detalle del registro seleccionado.</p></div></div>
        <button type="button" class="gestion-pendiente-close" data-pending-close aria-label="Cerrar ventana"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </header>
    <div class="gestion-pendiente-modal-body">
        <div class="gestion-pendiente-modal-notice"><i class="bi bi-info-circle" aria-hidden="true"></i><div><strong>Vista preparada para integración</strong><p>No hay un registro real seleccionado. El detalle se poblará desde el backend cuando se conecte este módulo.</p></div></div>
        <div class="gestion-pendiente-detail-grid">
            <?php $__currentLoopData = ($module['fields'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div><small><?php echo e($field['label']); ?></small><strong>—</strong></div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <footer class="gestion-pendiente-modal-footer"><button type="button" class="gestion-pendiente-secondary" data-pending-close>Cerrar</button></footer>
</div>
</dialog>
    <dialog class="gestion-pendiente-modal gestion-pendiente-modal--danger" data-pending-modal="eliminar" aria-labelledby="soporte-reclamos-eliminar-title"><div class="gestion-pendiente-modal-shell">
    <header class="gestion-pendiente-modal-header">
        <div><span class="gestion-pendiente-modal-icon gestion-pendiente-modal-icon--danger"><i class="bi bi-trash3" aria-hidden="true"></i></span><div><span class="gestion-pendiente-eyebrow"><?php echo e($module['area']); ?></span><h2 id="<?php echo e($module['key']); ?>-eliminar-title">Eliminar <?php echo e($module['record_singular'] ?? 'registro'); ?></h2><p>Confirma la acción antes de continuar.</p></div></div>
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
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.panel.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>