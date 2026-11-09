<?php $__env->startSection('title', 'Devoluciones y garantías'); ?>
<?php $__env->startSection('body_class', 'modulo-devoluciones-garantias'); ?>

<?php $__env->startSection('content'); ?>

<?php
    $ui = $module['operacion_ui'];
$moduleKey = $module['key'];
$rows = $ui['rows'] ?? [];
$filterValues = $ui['filter_values'] ?? [];
$pagination = $ui['pagination'] ?? null;
?>

<section class="gestion-pendiente-page gestion-operacion-page gestion-operacion-page--<?php echo e($moduleKey); ?>" data-pending-module="<?php echo e($moduleKey); ?>">
    <header class="gestion-operacion-header">
        <nav class="gestion-operacion-breadcrumb" aria-label="Ruta de navegación">
            <?php $__currentLoopData = $ui['breadcrumb'];
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $breadcrumb) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?>
                <?php if (! $loop->first) { ?><i class="bi bi-chevron-right" aria-hidden="true"></i><?php } ?>
                <span class="<?php echo e($loop->last ? 'is-current' : ''); ?>"><?php echo e($breadcrumb); ?></span>
            <?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?>
        </nav>
        <div class="gestion-operacion-heading-row">
            <div class="gestion-operacion-heading">
                <span class="gestion-pendiente-eyebrow"><i class="bi <?php echo e($module['icon']); ?>" aria-hidden="true"></i> <?php echo e($module['area']); ?></span>
                <h1><?php echo e($ui['title']); ?></h1>
                <p><?php echo e($ui['description']); ?></p>
            </div>
            <div class="gestion-operacion-actions">
                <?php $__currentLoopData = $ui['actions'];
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $action) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?>
                    <?php if (isset($action['route']) && isset($action['formats'])) { ?>
                        <details class="gestion-pendiente-export-menu"><summary class="gestion-pendiente-secondary"><i class="bi <?php echo e($action['icon']); ?>" aria-hidden="true"></i> <?php echo e($action['label']); ?> <i class="bi bi-chevron-down" aria-hidden="true"></i></summary><div class="gestion-pendiente-export-options"><?php $__currentLoopData = $action['formats'];
                        $__env->addLoop($__currentLoopData);
                        foreach ($__currentLoopData as $format) {
                            $__env->incrementLoopIndices();
                            $loop = $__env->getLastLoop(); ?><a href="<?php echo e(route($action['route'], array_merge(request()->query(), ['format' => $format]))); ?>"><i class="bi <?php echo e($format === 'excel' ? 'bi-file-earmark-spreadsheet' : ($format === 'pdf' ? 'bi-file-earmark-pdf' : 'bi-filetype-csv')); ?>" aria-hidden="true"></i> <?php echo e(strtoupper($format)); ?> (.<?php echo e($format === 'excel' ? 'xlsx' : $format); ?>)</a><?php } $__env->popLoop();
                        $loop = $__env->getLastLoop(); ?></div></details>
                    <?php } elseif (isset($action['route'])) { ?>
                        <a class="gestion-pendiente-secondary" href="<?php echo e(route($action['route'], request()->query())); ?>"><i class="bi <?php echo e($action['icon']); ?>" aria-hidden="true"></i> <?php echo e($action['label']); ?></a>
                    <?php } elseif (($action['modal'] ?? null) === 'crear') { ?>
                        <button class="gestion-pendiente-primary" type="button" data-pending-open="crear"><i class="bi <?php echo e($action['icon']); ?>" aria-hidden="true"></i> <?php echo e($action['label']); ?></button>
                    <?php } else { ?>
                        <button class="gestion-pendiente-secondary" type="button" data-pending-notice="<?php echo e($action['notice']); ?>"><i class="bi <?php echo e($action['icon']); ?>" aria-hidden="true"></i> <?php echo e($action['label']); ?></button>
                    <?php } ?>
                <?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </header>

    <?php if (session('status')) { ?>
        <div class="gestion-operacion-feedback gestion-operacion-feedback--success" role="status"><i class="bi bi-check-circle" aria-hidden="true"></i><?php echo e(session('status')); ?></div>
    <?php } ?>
    <?php if ($errors->any()) { ?>
        <div class="gestion-operacion-feedback gestion-operacion-feedback--error" role="alert"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i><?php echo e($errors->first()); ?></div>
    <?php } ?>

    <section class="gestion-operacion-kpis" aria-label="Indicadores del módulo">
        <?php $__currentLoopData = $ui['kpis'];
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $kpi) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?>
            <article class="gestion-operacion-kpi gestion-operacion-kpi--<?php echo e($kpi['tone']); ?>">
                <span class="gestion-operacion-kpi-icon"><i class="bi <?php echo e($kpi['icon']); ?>" aria-hidden="true"></i></span>
                <div class="gestion-operacion-kpi-content"><small><?php echo e($kpi['label']); ?></small><strong><?php echo e($kpi['value'] ?? '—'); ?></strong><span><?php echo e($kpi['helper']); ?></span></div>
                <span class="gestion-operacion-sparkline" aria-hidden="true"></span>
            </article>
        <?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?>
    </section>

    <form class="gestion-operacion-filters" method="GET" <?php if (($ui['backend'] ?? false) === true) { ?> action="<?php echo e($ui['filter_action']); ?>" <?php } else { ?> data-pending-form <?php } ?>>
        <label class="gestion-operacion-search"><i class="bi bi-search" aria-hidden="true"></i><span class="visually-hidden"><?php echo e($ui['search_label']); ?></span><input name="buscar" type="search" value="<?php echo e($filterValues['buscar'] ?? ''); ?>" placeholder="<?php echo e($ui['search_placeholder']); ?>" data-pending-search></label>
        <?php $__currentLoopData = $ui['filters'];
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $filter) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?>
            <label><span class="visually-hidden"><?php echo e($filter['label']); ?></span><select name="<?php echo e($filter['name'] ?? 'filtro_'.$loop->index); ?>" data-pending-filter><option value=""><?php echo e($filter['placeholder']); ?></option><?php $__currentLoopData = $filter['options'];
    $__env->addLoop($__currentLoopData);
    foreach ($__currentLoopData as $option) {
        $__env->incrementLoopIndices();
        $loop = $__env->getLastLoop(); ?><option value="<?php echo e($option); ?>" <?php if (($filterValues[$filter['name'] ?? ''] ?? '') === $option) {
            echo 'selected';
        } ?>><?php echo e($option); ?></option><?php } $__env->popLoop();
    $loop = $__env->getLastLoop(); ?></select></label>
        <?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?>
        <button class="gestion-operacion-filter-button" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Filtrar</button>
        <?php if (($ui['backend'] ?? false) === true && ! empty($ui['clear_action'])) { ?>
            <a class="gestion-operacion-clear-button" href="<?php echo e($ui['clear_action']); ?>">Limpiar filtros</a>
        <?php } else { ?>
            <button class="gestion-operacion-clear-button" type="button" data-pending-notice="Los filtros se podrán limpiar cuando exista una fuente de datos integrada.">Limpiar filtros</button>
        <?php } ?>
    </form>

    <section class="gestion-operacion-control-card">
        <header class="gestion-operacion-card-header">
            <div><i class="bi <?php echo e($ui['table_icon']); ?>" aria-hidden="true"></i><h2><?php echo e($ui['table_title']); ?></h2><span><?php echo e($ui['table_count']); ?></span></div>
            <?php if (($ui['backend'] ?? false) === true && ! empty($ui['sort_options'])) { ?>
                <details class="gestion-operacion-sort-menu"><summary class="gestion-operacion-sort">Ordenar por <strong><?php echo e($ui['sort_options'][$filterValues['orden'] ?? 'recientes'] ?? 'Más recientes'); ?></strong><i class="bi bi-chevron-down" aria-hidden="true"></i></summary><div class="gestion-operacion-sort-options"><?php $__currentLoopData = $ui['sort_options'];
                $__env->addLoop($__currentLoopData);
                foreach ($__currentLoopData as $sortKey => $sortLabel) {
                    $__env->incrementLoopIndices();
                    $loop = $__env->getLastLoop(); ?><a href="<?php echo e(route($ui['filter_route'], array_merge(request()->query(), ['orden' => $sortKey, 'page' => 1]))); ?>"><?php echo e($sortLabel); ?></a><?php } $__env->popLoop();
                $loop = $__env->getLastLoop(); ?></div></details>
            <?php } else { ?>
                <button class="gestion-operacion-sort" type="button" data-pending-notice="El ordenamiento quedará habilitado cuando exista una fuente de datos integrada.">Ordenar por <strong>Más recientes</strong><i class="bi bi-chevron-down" aria-hidden="true"></i></button>
            <?php } ?>
        </header>
        <div class="gestion-operacion-table-wrap">
            <table class="gestion-operacion-table">
                <thead><tr><?php $__currentLoopData = $ui['table_columns'];
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $column) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?><th><?php echo e($column); ?></th><?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?></tr></thead>
                <tbody>
                    <?php $__empty_1 = true;
$__currentLoopData = $rows;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $row) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop();
    $__empty_1 = false; ?>
                        <tr>
                            <?php $__currentLoopData = $row['cells'];
    $__env->addLoop($__currentLoopData);
    foreach ($__currentLoopData as $cell) {
        $__env->incrementLoopIndices();
        $loop = $__env->getLastLoop(); ?>
                                <td>
                                    <?php if (is_array($cell)) { ?>
                                        <span class="gestion-operacion-badge gestion-operacion-badge--<?php echo e($cell['tone']); ?>"><?php echo e($cell['value']); ?></span>
                                    <?php } else { ?>
                                        <?php echo e($cell); ?>

                                    <?php } ?>
                                </td>
                            <?php } $__env->popLoop();
    $loop = $__env->getLastLoop(); ?>
                            <td class="gestion-operacion-row-actions">
                                <button type="button" data-pending-open="ver" data-pending-record="<?php echo e(base64_encode(json_encode($row['record'], JSON_UNESCAPED_UNICODE))); ?>" aria-label="Ver solicitud"><i class="bi bi-eye" aria-hidden="true"></i></button>
                                <button type="button" data-pending-open="editar" data-pending-action="<?php echo e($row['edit_url']); ?>" data-pending-record="<?php echo e(base64_encode(json_encode($row['record'], JSON_UNESCAPED_UNICODE))); ?>" aria-label="Editar solicitud"><i class="bi bi-pencil" aria-hidden="true"></i></button>
                                <button type="button" data-pending-open="eliminar" data-pending-action="<?php echo e($row['delete_url']); ?>" data-pending-record="<?php echo e(base64_encode(json_encode($row['record'], JSON_UNESCAPED_UNICODE))); ?>" aria-label="Eliminar solicitud"><i class="bi bi-trash3" aria-hidden="true"></i></button>
                            </td>
                        </tr>
                    <?php } $__env->popLoop();
$loop = $__env->getLastLoop();
if ($__empty_1) { ?>
                        <tr><td colspan="<?php echo e(count($ui['table_columns'])); ?>"><div class="gestion-operacion-empty"><span><i class="bi bi-inbox" aria-hidden="true"></i></span><strong><?php echo e($ui['empty_title']); ?></strong><p><?php echo e($ui['empty_description']); ?></p></div></td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <footer class="gestion-operacion-table-footer">
            <span><?php echo e($ui['table_footer']); ?></span>
            <div class="gestion-operacion-pagination" aria-label="Paginación">
                <?php if (is_array($pagination)) { ?>
                    <a href="<?php echo e($pagination['previous'] ?: '#'); ?>" <?php if (! $pagination['previous']) { ?> aria-disabled="true" <?php } ?> aria-label="Página anterior"><i class="bi bi-chevron-left" aria-hidden="true"></i></a>
                    <?php for ($page = 1; $page <= $pagination['last']; $page++) { ?>
                        <?php if ($page <= 5 || $page === $pagination['last']) { ?>
                            <a href="<?php echo e(route($ui['filter_route'], array_merge(request()->query(), ['page' => $page]))); ?>" class="<?php echo e($page === $pagination['current'] ? 'is-active' : ''); ?>"><?php echo e($page); ?></a>
                        <?php } elseif ($page === 6) { ?>
                            <span class="gestion-operacion-pagination-more">…</span>
                        <?php } ?>
                    <?php } ?>
                    <a href="<?php echo e($pagination['next'] ?: '#'); ?>" <?php if (! $pagination['next']) { ?> aria-disabled="true" <?php } ?> aria-label="Página siguiente"><i class="bi bi-chevron-right" aria-hidden="true"></i></a>
                <?php } else { ?>
                    <button type="button" disabled aria-label="Página anterior"><i class="bi bi-chevron-left" aria-hidden="true"></i></button><button type="button" class="is-active" disabled>1</button><button type="button" disabled aria-label="Página siguiente"><i class="bi bi-chevron-right" aria-hidden="true"></i></button>
                <?php } ?>
            </div>
        </footer>
    </section>

    <section class="gestion-operacion-bottom-grid" aria-label="Resumen del módulo">
        <?php $__currentLoopData = $ui['panels'];
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $panel) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?>
            <article class="gestion-operacion-summary-card<?php echo e(($panel['type'] ?? 'empty') === 'performance' ? ' gestion-operacion-performance-card' : ''); ?>">
                <header><div><span class="gestion-operacion-summary-icon gestion-operacion-summary-icon--<?php echo e($panel['tone']); ?>"><i class="bi <?php echo e($panel['icon']); ?>" aria-hidden="true"></i></span><div><h2><?php echo e($panel['title']); ?></h2><p><?php echo e($panel['subtitle']); ?></p></div></div><button type="button" data-pending-notice="<?php echo e($panel['notice']); ?>"><?php echo e($panel['link']); ?> <i class="bi bi-arrow-right" aria-hidden="true"></i></button></header>
                <?php if (($panel['type'] ?? 'empty') === 'performance') { ?>
                    <div class="gestion-operacion-performance-empty"><strong>—</strong><span><?php echo e($panel['metric']); ?></span><div class="gestion-operacion-performance-bar"><span></span></div><small><?php echo e($panel['empty']); ?></small></div>
                <?php } else { ?>
                    <div class="gestion-operacion-summary-empty"><i class="bi <?php echo e($panel['empty_icon']); ?>" aria-hidden="true"></i><strong><?php echo e($panel['empty_title']); ?></strong><span><?php echo e($panel['empty']); ?></span></div>
                <?php } ?>
            </article>
        <?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?>
    </section>

    <dialog class="gestion-pendiente-modal" data-pending-modal="crear" aria-labelledby="<?php echo e($moduleKey); ?>-crear-title"><?php echo $__env->make('gestion.'.$moduleKey.'.crear', ['module' => $module], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></dialog>
    <dialog class="gestion-pendiente-modal" data-pending-modal="editar" aria-labelledby="<?php echo e($moduleKey); ?>-editar-title"><?php echo $__env->make('gestion.'.$moduleKey.'.editar', ['module' => $module], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></dialog>
    <dialog class="gestion-pendiente-modal" data-pending-modal="ver" aria-labelledby="<?php echo e($moduleKey); ?>-ver-title"><div class="gestion-pendiente-modal-shell">
    <header class="gestion-pendiente-modal-header">
        <div><span class="gestion-pendiente-modal-icon"><i class="bi <?php echo e($module['icon']); ?>" aria-hidden="true"></i></span><div><span class="gestion-pendiente-eyebrow"><?php echo e($module['area']); ?></span><h2 id="<?php echo e($module['key']); ?>-ver-title">Ver <?php echo e($module['record_singular'] ?? 'registro'); ?></h2><p>Detalle del registro seleccionado.</p></div></div>
        <button type="button" class="gestion-pendiente-close" data-pending-close aria-label="Cerrar ventana"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </header>
    <div class="gestion-pendiente-modal-body">
        <div class="gestion-pendiente-modal-notice"><i class="bi bi-info-circle" aria-hidden="true"></i><div><strong>Vista preparada para integración</strong><p>No hay un registro real seleccionado. El detalle se poblará desde el backend cuando se conecte este módulo.</p></div></div>
        <div class="gestion-pendiente-detail-grid">
            <?php $__currentLoopData = ($module['fields'] ?? []);
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $field) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?>
                <div><small><?php echo e($field['label']); ?></small><strong>—</strong></div>
            <?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <footer class="gestion-pendiente-modal-footer"><button type="button" class="gestion-pendiente-secondary" data-pending-close>Cerrar</button></footer>
</div>
</dialog>
    <dialog class="gestion-pendiente-modal gestion-pendiente-modal--danger" data-pending-modal="eliminar" aria-labelledby="<?php echo e($moduleKey); ?>-eliminar-title"><div class="gestion-pendiente-modal-shell">
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