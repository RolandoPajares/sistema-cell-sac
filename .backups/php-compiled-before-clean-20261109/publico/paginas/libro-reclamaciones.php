<?php $__env->startSection('title', 'Libro de reclamaciones | MD Technology Cell'); ?>
<?php $__env->startSection('body_class', 'modulo-libro-reclamaciones'); ?>

<?php $__env->startSection('content'); ?>
    <section class="container content-page">
        <span class="eyebrow">ATENCIÓN AL CLIENTE</span>
        <h1>Libro de reclamaciones</h1>
        <p>Si deseas comunicar una consulta, incidencia o reclamo relacionado con tu atención, puedes utilizar nuestro canal de contacto.</p>
        <p>Describe el caso con información clara para que el equipo pueda revisarlo y responderte.</p>
        <a class="btn btn-primary" href="<?php echo e(route('pages.contact')); ?>#formulario">Ir al formulario de contacto</a>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.tienda.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>