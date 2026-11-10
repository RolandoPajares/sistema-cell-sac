<form method="post" action="<?php echo e(route('management.customers.update', $customer)); ?>" class="form-card compact-form">
    <?php echo csrf_field(); ?>
    <?php echo method_field('PUT'); ?>
    <?php echo $__env->make('gestion.comercial.clientes.formulario', ['customer' => $customer], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <button class="btn btn-primary">Guardar cambios</button>
</form>
