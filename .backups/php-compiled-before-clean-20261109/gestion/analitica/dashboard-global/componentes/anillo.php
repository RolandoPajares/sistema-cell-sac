<div class="tc-dg__donut-layout">
    <div class="tc-dg__donut" style="--tc-dg-donut: <?php echo e($gradiente); ?>"><span><strong><?php echo e($centro); ?></strong><small><?php echo e($etiqueta); ?></small></span></div>
    <ul class="tc-dg__legend">
        <?php $__currentLoopData = $leyenda; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $elemento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li><i style="--tc-dg-legend-color: <?php echo e($elemento['color']); ?>"></i><span><?php echo e($elemento['nombre']); ?></span><strong><?php echo e($elemento['valor']); ?></strong></li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>
</div>
