<?php $__env->startSection('title', 'Mis notificaciones'); ?>
<?php $__env->startSection('content'); ?>
    <section class="admin-page">
        <header class="admin-page-head"><div><span class="eyebrow">Cuenta personal</span><h1>Mis notificaciones</h1><p>Avisos asociados a tu cuenta.</p></div></header>
        <?php echo $__env->make('cuenta.perfil.navegacion', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php if (! ($available)): ?>
            <div class="admin-alert admin-alert--error" role="status">La tabla de notificaciones no está disponible en la base de datos.</div>
        <?php else: ?>
            <div class="form-card">
                <?php $__empty_1 = true; $__currentLoopData = $notificaciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notificacion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <article class="notification-row">
                        <div>
                            <strong><?php echo e($notificacion->nombreTipo()); ?></strong>
                            <p><?php echo e($notificacion->datosLegibles()); ?></p>
                            <small><?php echo e($notificacion->creado_en?->diffForHumans()); ?></small>
                        </div>
                        <?php if(! $notificacion->leido_en): ?>
                            <form method="post" action="<?php echo e(route('management.account.notifications.read', $notificacion->id)); ?>">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PATCH'); ?>
                                <button class="btn btn-outline-primary btn-sm" type="submit">Marcar como leída</button>
                            </form>
                        <?php else: ?>
                            <span class="badge text-bg-light">Leída</span>
                        <?php endif; ?>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p>No tienes notificaciones registradas.</p>
                <?php endif; ?>
                <?php echo e($notificaciones->links()); ?>

            </div>
        <?php endif; ?>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.panel.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>