<?php $__env->startSection('title', 'Alertas y notificaciones'); ?>
<?php $__env->startSection('body_class', 'modulo-dashboard-global'); ?>

<?php $__env->startSection('content'); ?>
<section class="tc-dg" data-dashboard-global="alertas">
    <?php echo $__env->make('gestion.analitica.dashboard-global.componentes.cabecera', ['titulo' => 'Alertas y notificaciones', 'icono' => 'bi-bell', 'descripcion' => 'Consulta las notificaciones persistidas para la cuenta administrativa actual.', 'exportRoute' => $disponible ? 'management.reports.dashboard_alerts.export' : null, 'exportParameters' => array_filter(['estado' => $estado, 'buscar' => $busqueda], static fn ($value) => $value !== null && $value !== '')], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php if (! ($disponible)) { ?>
        <div class="tc-dg__empty" role="status">La tabla notificaciones no está disponible en la base de datos.</div>
    <?php } else { ?>
        <form id="filtro-estado-alertas" method="GET" action="<?php echo e(route('management.dashboard.alerts')); ?>">
            <?php if ($busqueda !== null && $busqueda !== '') { ?>
                <input type="hidden" name="buscar" value="<?php echo e($busqueda); ?>">
            <?php } ?>
        </form>

        <div class="tc-dg__tabs" role="tablist" aria-label="Estado de notificaciones">
            <button type="submit" form="filtro-estado-alertas" name="estado" value="todas" role="tab" aria-selected="<?php echo e($estado === 'todas' ? 'true' : 'false'); ?>"><i class="bi bi-bell"></i> Todas <span>(<?php echo e(number_format($resumen['total'])); ?>)</span></button>
            <button type="submit" form="filtro-estado-alertas" name="estado" value="no_leidas" role="tab" aria-selected="<?php echo e($estado === 'no_leidas' ? 'true' : 'false'); ?>"><i class="bi bi-bell-fill"></i> No leídas <span>(<?php echo e(number_format($resumen['no_leidas'])); ?>)</span></button>
            <button type="submit" form="filtro-estado-alertas" name="estado" value="leidas" role="tab" aria-selected="<?php echo e($estado === 'leidas' ? 'true' : 'false'); ?>"><i class="bi bi-envelope-open"></i> Leídas <span>(<?php echo e(number_format($resumen['leidas'])); ?>)</span></button>
        </div>

        <form class="tc-dg__filters" method="GET" action="<?php echo e(route('management.dashboard.alerts')); ?>">
            <label><span><i class="bi bi-search"></i> Buscar notificaciones</span><input type="search" name="buscar" value="<?php echo e($busqueda); ?>" maxlength="120"></label>
            <label><span><i class="bi bi-funnel"></i> Estado</span><select name="estado">
                <option value="todas" <?php if ($estado === 'todas') {
                    echo 'selected';
                } ?>>Todas</option>
                <option value="no_leidas" <?php if ($estado === 'no_leidas') {
                    echo 'selected';
                } ?>>No leídas</option>
                <option value="leidas" <?php if ($estado === 'leidas') {
                    echo 'selected';
                } ?>>Leídas</option>
            </select></label>
            <button type="submit"><i class="bi bi-arrow-repeat"></i> Aplicar filtros</button>
        </form>

        <div class="tc-dg__metrics tc-dg__metrics--four">
            <?php echo $__env->make('gestion.analitica.dashboard-global.componentes.metrica', ['titulo' => 'Notificaciones', 'icono' => 'bi-bell', 'valor' => number_format($resumen['total']), 'detalle' => 'Registros asociados a esta cuenta'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('gestion.analitica.dashboard-global.componentes.metrica', ['titulo' => 'No leídas', 'icono' => 'bi-envelope-exclamation', 'tono' => 'rojo', 'valor' => number_format($resumen['no_leidas']), 'detalle' => 'Según leido_en en MariaDB'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('gestion.analitica.dashboard-global.componentes.metrica', ['titulo' => 'Leídas', 'icono' => 'bi-envelope-open', 'tono' => 'verde', 'valor' => number_format($resumen['leidas']), 'detalle' => 'Según leido_en en MariaDB'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('gestion.analitica.dashboard-global.componentes.metrica', ['titulo' => 'Tipos registrados', 'icono' => 'bi-tags', 'tono' => 'violeta', 'valor' => number_format($resumen['tipos']), 'detalle' => 'Tipos distintos almacenados'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>

        <form id="seleccionar-notificacion" method="GET" action="<?php echo e(route('management.dashboard.alerts')); ?>">
            <input type="hidden" name="estado" value="<?php echo e($estado); ?>">
            <?php if ($busqueda !== null && $busqueda !== '') { ?>
                <input type="hidden" name="buscar" value="<?php echo e($busqueda); ?>">
            <?php } ?>
        </form>

        <div class="tc-dg__grid tc-dg__grid--alerts">
            <article class="tc-dg__card">
                <div class="tc-dg__card-head"><i class="bi bi-bell"></i><div><h2>Notificaciones (<?php echo e(number_format($resumen['total'])); ?>)</h2><p>Registros de la cuenta administrativa autenticada.</p></div><span><?php echo e($estado === 'todas' ? 'Todas' : ($estado === 'no_leidas' ? 'No leídas' : 'Leídas')); ?></span></div>
                <div class="tc-dg__alert-list">
                    <?php $__empty_1 = true;
        $__currentLoopData = $notificaciones;
        $__env->addLoop($__currentLoopData);
        foreach ($__currentLoopData as $notificacion) {
            $__env->incrementLoopIndices();
            $loop = $__env->getLastLoop();
            $__empty_1 = false; ?>
                        <button class="<?php echo e($notificacionSeleccionada?->is($notificacion) ? 'is-selected' : ''); ?>" type="submit" form="seleccionar-notificacion" name="notificacion" value="<?php echo e($notificacion->id); ?>">
                            <i class="bi <?php echo e($notificacion->leido_en ? 'bi-envelope-open' : 'bi-bell-fill'); ?>"></i>
                            <span><strong><?php echo e($notificacion->nombreTipo()); ?></strong><small><?php echo e($notificacion->datosLegibles()); ?></small></span>
                            <b><?php echo e($notificacion->leido_en ? 'Leída' : 'No leída'); ?></b>
                            <time><?php echo e($notificacion->creado_en?->format('d/m/Y H:i') ?? 'Fecha no informada'); ?></time>
                        </button>
                    <?php } $__env->popLoop();
        $loop = $__env->getLastLoop();
        if ($__empty_1) { ?>
                        <p class="tc-dg__alert-empty">
                            <?php if ($resumen['total'] === 0) { ?>
                                No hay notificaciones registradas.
                            <?php } else { ?>
                                No hay notificaciones que coincidan con los filtros aplicados.
                            <?php } ?>
                        </p>
                    <?php } ?>
                </div>
                <?php echo e($notificaciones->links()); ?>

            </article>

            <article class="tc-dg__card tc-dg__alert-detail">
                <?php if ($notificacionSeleccionada) { ?>
                    <div class="tc-dg__card-head"><i class="bi bi-bell"></i><div><h2><?php echo e($notificacionSeleccionada->nombreTipo()); ?></h2><p>Datos persistidos en la notificación seleccionada.</p></div><span><?php echo e($notificacionSeleccionada->leido_en ? 'Leída' : 'No leída'); ?></span></div>
                    <p class="tc-dg__detail-label">Datos de la notificación</p>
                    <p class="tc-dg__detail-copy"><?php echo e($notificacionSeleccionada->datosLegibles()); ?></p>
                    <div class="tc-dg__detail-grid">
                        <div><i class="bi bi-tag"></i><small>Tipo</small><strong><?php echo e($notificacionSeleccionada->tipo); ?></strong></div>
                        <div><i class="bi bi-envelope"></i><small>Lectura</small><strong><?php echo e($notificacionSeleccionada->leido_en ? 'Leída' : 'Pendiente de lectura'); ?></strong></div>
                        <div><i class="bi bi-calendar-event"></i><small>Creada</small><strong><?php echo e($notificacionSeleccionada->creado_en?->format('d/m/Y H:i') ?? 'Fecha no informada'); ?></strong></div>
                        <div><i class="bi bi-fingerprint"></i><small>Identificador</small><strong><?php echo e($notificacionSeleccionada->id); ?></strong></div>
                    </div>
                    <?php if (! ($notificacionSeleccionada->leido_en)) { ?>
                        <div class="tc-dg__alert-actions">
                            <form method="POST" action="<?php echo e(route('management.account.notifications.read', $notificacionSeleccionada->id)); ?>">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PATCH'); ?>
                                <button type="submit">Marcar como leída</button>
                            </form>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="tc-dg__card-head"><i class="bi bi-bell-slash"></i><div><h2>Detalle de notificación</h2><p>Selecciona un registro para consultar sus datos.</p></div></div>
                    <p class="tc-dg__empty">No hay notificaciones para mostrar.</p>
                <?php } ?>
            </article>
        </div>

        <div class="tc-dg__grid tc-dg__grid--thirds">
            <article class="tc-dg__card"><div class="tc-dg__card-head"><i class="bi bi-pie-chart"></i><div><h2>Notificaciones por tipo</h2><p>Distribución calculada desde registros reales.</p></div></div><div class="tc-dg__chart">
                <?php if ($resumen['distribucion'] === []) { ?>
                    <p class="tc-dg__empty">No hay datos para construir la distribución.</p>
                <?php } else { ?>
                    <?php echo $__env->make('gestion.analitica.dashboard-global.componentes.anillo', ['centro' => number_format($resumen['total']), 'etiqueta' => 'Notificaciones', 'gradiente' => $resumen['gradiente'], 'leyenda' => $resumen['distribucion']], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php } ?>
            </div></article>
            <article class="tc-dg__card"><div class="tc-dg__card-head"><i class="bi bi-envelope-check"></i><div><h2>Estado de lectura</h2><p>Conteo actual del destinatario autenticado.</p></div></div><div class="tc-dg__task-list"><span>No leídas <b><?php echo e(number_format($resumen['no_leidas'])); ?></b></span><span>Leídas <b><?php echo e(number_format($resumen['leidas'])); ?></b></span></div></article>
            <article class="tc-dg__card"><div class="tc-dg__card-head"><i class="bi bi-person-check"></i><div><h2>Alcance de consulta</h2><p>Los registros quedan aislados por destinatario.</p></div></div><div class="tc-dg__task-list"><span>Cuenta consultada <b>Autenticada</b></span><a href="<?php echo e(route('management.account.notifications')); ?>">Ver mis notificaciones <i class="bi bi-arrow-right"></i></a></div></article>
        </div>
    <?php } ?>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.panel.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>