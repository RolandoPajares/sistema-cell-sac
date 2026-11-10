<header class="tc-dg__header">
    <div>
        <span class="tc-dg__eyebrow">Dashboard Global</span>
        <h1><?php echo e($titulo); ?> <i class="bi <?php echo e($icono); ?>" aria-hidden="true"></i></h1>
        <p><?php echo e($descripcion); ?></p>
    </div>
    <?php if(! empty($exportRoute)): ?>
        <details class="tc-dg__export-menu">
            <summary class="tc-dg__export"><i class="bi bi-download" aria-hidden="true"></i> Exportar <i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
            <div class="tc-dg__export-options">
                <a href="<?php echo e(route($exportRoute, array_merge(['format' => 'pdf'], $exportParameters ?? []))); ?>">PDF</a>
                <a href="<?php echo e(route($exportRoute, array_merge(['format' => 'excel'], $exportParameters ?? []))); ?>">Excel</a>
                <a href="<?php echo e(route($exportRoute, array_merge(['format' => 'csv'], $exportParameters ?? []))); ?>">CSV</a>
            </div>
        </details>
    <?php else: ?>
        <button class="tc-dg__export" type="button" disabled title="La exportación estará disponible cuando esta pantalla tenga datos integrados">
            <i class="bi bi-download" aria-hidden="true"></i> Exportar <i class="bi bi-chevron-down" aria-hidden="true"></i>
        </button>
    <?php endif; ?>
</header>
