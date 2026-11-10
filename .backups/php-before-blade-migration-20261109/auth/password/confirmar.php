<?php $__env->startSection('title', 'Confirmar contraseña'); ?>

<?php $__env->startPush('styles'); ?>
    <?php echo app('Illuminate\Foundation\Vite')('resources/css/auth/password/confirmar.css'); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
    <?php echo app('Illuminate\Foundation\Vite')('resources/js/auth/password/confirmar.js'); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>