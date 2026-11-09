<?php if($paginator->hasPages()): ?>
    <nav class="catalog-pagination" aria-label="PaginaciÃ³n del catÃ¡logo">
        <p class="catalog-pagination__summary">
            Mostrando
            <strong><?php echo e($paginator->firstItem()); ?></strong>
            a <strong><?php echo e($paginator->lastItem()); ?></strong>
            de <strong><?php echo e($paginator->total()); ?></strong> productos
        </p>

        <div class="catalog-pagination__controls">
            <?php if($paginator->onFirstPage()): ?>
                <span class="catalog-pagination__disabled" aria-disabled="true">
                    <i class="bi bi-chevron-left" aria-hidden="true"></i>
                    <span>Anterior</span>
                </span>
            <?php else: ?>
                <a class="catalog-pagination__link" href="<?php echo e($paginator->previousPageUrl()); ?>" rel="prev">
                    <i class="bi bi-chevron-left" aria-hidden="true"></i>
                    <span>Anterior</span>
                </a>
            <?php endif; ?>

            <div class="catalog-pagination__pages">
                <?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(is_string($element)): ?>
                        <span class="catalog-pagination__ellipsis" aria-hidden="true"><?php echo e($element); ?></span>
                    <?php endif; ?>

                    <?php if(is_array($element)): ?>
                        <?php $__currentLoopData = $element; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if($page == $paginator->currentPage()): ?>
                                <span class="catalog-pagination__current" aria-current="page"><?php echo e($page); ?></span>
                            <?php else: ?>
                                <a class="catalog-pagination__link" href="<?php echo e($url); ?>"><?php echo e($page); ?></a>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <?php if($paginator->hasMorePages()): ?>
                <a class="catalog-pagination__link" href="<?php echo e($paginator->nextPageUrl()); ?>" rel="next">
                    <span>Siguiente</span>
                    <i class="bi bi-chevron-right" aria-hidden="true"></i>
                </a>
            <?php else: ?>
                <span class="catalog-pagination__disabled" aria-disabled="true">
                    <span>Siguiente</span>
                    <i class="bi bi-chevron-right" aria-hidden="true"></i>
                </span>
            <?php endif; ?>
        </div>
    </nav>
<?php endif; ?>
