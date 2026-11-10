<?php $__env->startSection('title', 'Preguntas frecuentes | MD Technology Cell'); ?>
<?php $__env->startSection('body_class', 'modulo-preguntas-frecuentes'); ?>

<?php $__env->startSection('content'); ?>
    <section class="container content-page">
        <span class="eyebrow">AYUDA</span>
        <h1>Preguntas frecuentes</h1>
        <h2>¿Cómo consulto un producto?</h2>
        <p>Ingresa al catálogo para revisar los productos, sus características, precios y disponibilidad.</p>
        <h2>¿Qué ocurre al confirmar un pedido?</h2>
        <p>El sistema vuelve a validar la disponibilidad y registra el pedido con la información de la cuenta.</p>
        <h2>¿Cómo consulto mis pedidos?</h2>
        <p>Después de iniciar sesión, puedes revisar el historial y el detalle desde la sección de pedidos.</p>
        <h2>¿Necesitas ayuda adicional?</h2>
        <p>Utiliza nuestro <a href="<?php echo e(route('pages.contact')); ?>">canal de contacto</a> para enviar una consulta.</p>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.tienda.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>