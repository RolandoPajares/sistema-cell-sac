<header class="tc-org__header">
    <div>
        <span class="tc-org__eyebrow">Organización y Sedes</span>
        <h1><?php echo e($titulo); ?> <i class="bi <?php echo e($icono); ?>" aria-hidden="true"></i></h1>
        <p><?php echo e($descripcion); ?></p>
    </div>
    <div class="tc-org__header-actions">
        <?php $__currentLoopData = $acciones ?? [];
        $__env->addLoop($__currentLoopData);
        foreach ($__currentLoopData as $accion) {
            $__env->incrementLoopIndices();
            $loop = $__env->getLastLoop(); ?>
            <button class="tc-org__button <?php echo e(($accion['primaria'] ?? false) ? 'tc-org__button--primary' : ''); ?>" type="button" <?php if (isset($accion['modal'])) { ?> data-org-open="<?php echo e($accion['modal']); ?>" <?php } elseif (isset($accion['ruta'])) { ?> data-org-link="<?php echo e($accion['ruta']); ?>" <?php } else { ?> disabled title="Disponible cuando este módulo se conecte a datos reales" <?php } ?>>
                <i class="bi <?php echo e($accion['icono']); ?>" aria-hidden="true"></i><?php echo e($accion['texto']); ?>

            </button>
        <?php } $__env->popLoop();
        $loop = $__env->getLastLoop(); ?>
    </div>
</header>
