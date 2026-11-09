<div class="tc-org__filters" data-org-filter-root>
    <div class="tc-org__filter-top">
        <label class="tc-org__search"><i class="bi bi-search" aria-hidden="true"></i><input type="search" placeholder="<?php echo e($buscar); ?>" aria-label="<?php echo e($buscar); ?>" data-org-search></label>
        <button class="tc-org__button" type="button" disabled title="Más filtros disponibles con datos reales"><i class="bi bi-funnel" aria-hidden="true"></i> Filtros</button>
        <button class="tc-org__button" type="button" disabled title="Exportación pendiente de integración"><i class="bi bi-download" aria-hidden="true"></i> Exportar</button>
    </div>
    <div class="tc-org__filter-fields">
        <?php $__currentLoopData = $filtros; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $filtro): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <label><span><?php echo e($filtro['titulo']); ?></span><select data-org-select="<?php echo e($filtro['campo']); ?>"><option value=""><?php echo e($filtro['todos']); ?></option><?php $__currentLoopData = $filtro['opciones']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opcion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($opcion); ?>"><?php echo e($opcion); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <button class="tc-org__button tc-org__button--apply" type="button" data-org-apply><i class="bi bi-arrow-clockwise" aria-hidden="true"></i> Aplicar filtros</button>
    </div>
</div>
