<form class="categorias-modal-form" method="post" action="<?php echo e(route('management.categories.update', $category)); ?>">
    <?php echo csrf_field(); ?>
    <?php echo method_field('PUT'); ?>
    <?php echo $__env->make('gestion.inventario.categorias.formulario-modal', ['category' => $category], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <footer class="categorias-form-actions">
        <button class="categorias-button categorias-button--secondary" type="button" data-close-category-edit>Cancelar</button>
        <button class="categorias-button categorias-button--primary" type="submit"><i class="bi bi-save" aria-hidden="true"></i> Guardar cambios</button>
    </footer>
</form>
