<?php $__env->startSection('title', 'Página no encontrada'); ?>
<?php $__env->startSection('content'); ?>
<section class="container form-page">
    <div class="form-card" style="text-align:center">
        <span class="product-category">Error 404</span>
        <h1>No encontramos esta página</h1>
        <p>El enlace puede haber cambiado o el producto ya no está disponible.</p>
        <a class="btn btn-primary" href="<?php echo e(route('home')); ?>">Volver al inicio</a>
    </div>
</section>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.tienda.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>