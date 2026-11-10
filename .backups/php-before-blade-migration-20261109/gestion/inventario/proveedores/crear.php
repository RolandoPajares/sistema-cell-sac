<form method="post" class="form-card" action="<?php echo e(route('management.suppliers.store')); ?>">
    <?php echo csrf_field(); ?>
    <h2>Registrar proveedor</h2>
    <?php echo $__env->make('gestion.inventario.proveedores.formulario', ['supplier' => null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <button class="btn btn-primary">Guardar proveedor</button>
</form>
