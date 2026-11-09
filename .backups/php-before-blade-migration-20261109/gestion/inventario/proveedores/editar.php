<form method="post" class="form-card compact-form" action="<?php echo e(route('management.suppliers.update', $supplier)); ?>">
    <?php echo csrf_field(); ?>
    <?php echo method_field('PUT'); ?>
    <?php echo $__env->make('gestion.inventario.proveedores.formulario', ['supplier' => $supplier], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <button class="btn btn-primary">Actualizar proveedor</button>
</form>
