<form class="categorias-modal-form" method="post" action="<?php echo e(route('management.categories.store')); ?>">
    <?php echo csrf_field(); ?>
    <?php echo $__env->make('gestion.inventario.categorias.formulario-modal', ['category' => null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <footer class="categorias-form-actions">
        <button class="categorias-button categorias-button--secondary" type="button" data-close-category-create>Cancelar</button>
        <button class="categorias-button categorias-button--primary" type="submit"><i class="bi bi-plus-lg" aria-hidden="true"></i> Registrar categoría</button>
    </footer>
</form>
