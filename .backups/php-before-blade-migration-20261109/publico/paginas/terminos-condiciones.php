<?php $__env->startSection('title', 'Términos y condiciones | MD Technology Cell'); ?>
<?php $__env->startSection('body_class', 'modulo-terminos-condiciones'); ?>

<?php $__env->startSection('content'); ?>
    <section class="container content-page">
        <span class="eyebrow">INFORMACIÓN COMERCIAL</span>
        <h1>Términos y condiciones</h1>
        <p>La consulta del catálogo, el carrito y el checkout se realizan conforme a la disponibilidad, precios y condiciones vigentes mostradas por la plataforma.</p>
        <p>La disponibilidad se valida nuevamente durante la confirmación del pedido. El registro del pedido no constituye por sí mismo la confirmación de un pago.</p>
        <p>Para cualquier consulta sobre una operación, puedes comunicarte mediante el <a href="<?php echo e(route('pages.contact')); ?>">formulario de contacto</a>.</p>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.tienda.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>