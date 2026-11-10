<?php $__env->startSection('title', 'Mi actividad reciente'); ?>
<?php $__env->startSection('content'); ?>
    <section class="admin-page">
        <header class="admin-page-head"><div><span class="eyebrow">Cuenta personal</span><h1>Mi actividad reciente</h1><p>Movimientos que ya han sido registrados por el sistema para tu usuario.</p></div></header>
        <?php echo $__env->make('cuenta.perfil.navegacion', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php if (! ($available)) { ?>
            <div class="admin-alert admin-alert--error" role="status">El registro de auditoría no está disponible en este esquema.</div>
        <?php } else { ?>
            <div class="form-card"><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Fecha</th><th>Acción</th><th>Entidad</th><th>Registro</th><th>IP</th></tr></thead><tbody>
                <?php $__empty_1 = true;
            $__currentLoopData = $activity;
            $__env->addLoop($__currentLoopData);
            foreach ($__currentLoopData as $entry) {
                $__env->incrementLoopIndices();
                $loop = $__env->getLastLoop();
                $__empty_1 = false; ?>
                    <tr><td><?php echo e($entry->creado_en?->format('d/m/Y H:i:s')); ?></td><td><?php echo e($entry->accion); ?></td><td><?php echo e($entry->entidad); ?></td><td><?php echo e($entry->entidad_id ?? '—'); ?></td><td><?php echo e($entry->direccion_ip ?? '—'); ?></td></tr>
                <?php } $__env->popLoop();
            $loop = $__env->getLastLoop();
            if ($__empty_1) { ?>
                    <tr><td colspan="5">No hay actividad registrada para esta cuenta.</td></tr>
                <?php } ?>
            </tbody></table></div><?php echo e($activity->links()); ?></div>
        <?php } ?>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.panel.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>