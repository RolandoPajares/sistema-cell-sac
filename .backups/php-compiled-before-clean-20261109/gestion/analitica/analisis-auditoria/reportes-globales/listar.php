<?php $__env->startSection('title', 'Reportes globales'); ?>
<?php $__env->startSection('body_class', 'modulo-analisis-auditoria'); ?>
<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('gestion.analitica.analisis-auditoria.componentes.pagina', ['tipo' => 'globales'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.panel.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>