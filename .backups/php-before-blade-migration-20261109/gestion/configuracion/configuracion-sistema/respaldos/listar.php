<?php $__env->startSection('title', 'Respaldos y recuperación'); ?>
<?php $__env->startSection('body_class', 'modulo-configuracion-sistema'); ?>
<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('gestion.configuracion.configuracion-sistema.componentes.pagina', ['tipo' => 'respaldos'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.panel.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>