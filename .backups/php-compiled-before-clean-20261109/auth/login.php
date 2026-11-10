<?php $__env->startSection('body_class', 'modulo-autenticacion'); ?>
<?php $__env->startSection('title', 'Iniciar sesión'); ?>

<?php $__env->startPush('styles'); ?>
    <?php echo app('Illuminate\Foundation\Vite')('resources/css/auth/login.css'); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
    <?php echo app('Illuminate\Foundation\Vite')('resources/js/auth/login.js'); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="auth-reference">
 <div class="auth-reference__inner container">
  <div class="auth-intro">
    <span class="auth-reference__badge"><i class="bi bi-stars"></i> MD SmartCommerce</span>
    <h1>Bienvenido a<br>Technology <span>CELL</span></h1>
 </div>
 <div class="auth-wrap panel">
    <div class="auth-brand"><img src="<?php echo e(asset('assets/cell-legacy/img/marca/logo.jpeg')); ?>" alt="Technology Cell"></div>
    <h2>Iniciar sesión</h2>
    <?php if($errors->any()): ?><div class="alert alert-error"><?php echo e($errors->first()); ?></div><?php endif; ?>
    <form method="post" action="<?php echo e(route('login.store')); ?>">
        <?php echo csrf_field(); ?>
        <div class="form-group">
            <label>Correo</label>
            <input
                class="input"
                type="email"
                name="correo"
                value="<?php echo e(old('correo')); ?>"
                maxlength="160"
                required
                autocomplete="email"
            >
        </div>
        <div class="form-group">
            <label>Contraseña</label>
            <div class="admin-password-input auth-password-field">
                <input
                    class="input"
                    type="password"
                    name="password"
                    maxlength="4096"
                    required
                    autocomplete="current-password"
                >
                <button type="button" data-password-toggle aria-label="Mostrar contraseña">
                    <i class="bi bi-eye" aria-hidden="true"></i>
                </button>
            </div>
        </div>
        <div class="auth-reference__options">
            <label><input type="checkbox" name="remember" value="1"> <span>Recordarme</span></label>
            <a href="<?php echo e(route('pages.contact')); ?>#contact-name">¿Olvidaste tu contraseña?</a>
        </div>
        <button class="btn btn-primary" style="width:100%"><i class="bi bi-box-arrow-in-right"></i> Ingresar</button>
    </form>
    <a class="auth-reference__register" href="<?php echo e(route('register')); ?>"><i class="bi bi-person-plus"></i> Registrarse aquí</a>
    <div class="auth-reference__secure"><span><i class="bi bi-shield-check"></i><b>Acceso seguro y confiable</b></span><div><small><i class="bi bi-lock"></i> Tus datos<br>protegidos</small><small><i class="bi bi-patch-check"></i> Cuenta<br>verificada</small><small><i class="bi bi-fingerprint"></i> Experiencia<br>personalizada</small></div></div>
 </div>
 </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>