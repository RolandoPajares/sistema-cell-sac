<?php $__env->startSection('title', 'Ventas minoristas'); ?>
<?php $__env->startSection('body_class', 'modulo-operaciones-comerciales'); ?>
<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('gestion.comercial.operaciones-comerciales.componentes.pagina-ventas', ['datos' => $datos], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.panel.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>