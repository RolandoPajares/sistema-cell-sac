<article class="tc-dg__metric tc-dg__metric--<?php echo e($tono ?? 'azul'); ?>">
    <span class="tc-dg__metric-icon"><i class="bi <?php echo e($icono); ?>" aria-hidden="true"></i></span>
    <div>
        <small><?php echo e($titulo); ?></small>
        <strong><?php echo e($valor ?? '—'); ?></strong>
        <span><?php echo e($detalle ?? 'Pendiente de datos integrados'); ?></span>
    </div>
</article>
