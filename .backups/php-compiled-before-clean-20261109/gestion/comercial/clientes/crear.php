<form method="post" action="<?php echo e(route('management.customers.store')); ?>" class="form-card">
    <?php echo csrf_field(); ?>
    <h2>Nuevo cliente</h2>
    <?php echo $__env->make('gestion.comercial.clientes.formulario', ['customer' => null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <button class="btn btn-primary">Registrar cliente</button>
</form>