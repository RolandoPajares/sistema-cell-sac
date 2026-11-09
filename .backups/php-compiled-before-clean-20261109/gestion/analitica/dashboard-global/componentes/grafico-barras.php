<div class="tc-dg__bars" role="img" aria-label="<?php echo e($descripcion); ?>">
    <?php $__currentLoopData = $barras; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $barra): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="tc-dg__bar-column">
            <strong><?php echo e($barra['valor']); ?></strong>
            <span class="tc-dg__bar tc-dg__bar--<?php echo e($barra['tono'] ?? 'azul'); ?>" style="height: <?php echo e($barra['altura']); ?>%"></span>
            <small><?php echo e($barra['etiqueta']); ?></small>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
