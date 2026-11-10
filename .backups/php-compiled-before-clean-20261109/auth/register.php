<?php $__env->startSection('body_class', 'modulo-autenticacion'); ?>
<?php $__env->startSection('title', 'Crear cuenta'); ?>

<?php $__env->startPush('styles'); ?>
    <?php echo app('Illuminate\Foundation\Vite')('resources/css/auth/register.css'); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
    <?php echo app('Illuminate\Foundation\Vite')('resources/js/auth/register.js'); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="auth-register-page">
<div class="auth-wrap panel">
    <h1>Crear cuenta</h1>
    <p>El registro público crea una cuenta de cliente. El acceso administrativo se concede según el rol autorizado.</p>
    <?php if($errors->any()): ?><div class="alert alert-error"><?php echo e($errors->first()); ?></div><?php endif; ?>
    <form method="post" action="<?php echo e(route('register.store')); ?>">
        <?php echo csrf_field(); ?>
        <div class="form-group">
            <label for="tipo_cliente">Tipo de cliente</label>
            <select class="input" id="tipo_cliente" name="tipo_cliente" data-customer-type required>
                <option value="minorista" <?php if(old('tipo_cliente', 'minorista') === 'minorista'): echo 'selected'; endif; ?>>Cliente minorista</option>
                <option value="mayorista" <?php if(old('tipo_cliente') === 'mayorista'): echo 'selected'; endif; ?>>Cliente mayorista</option>
            </select>
        </div>

        <div class="form-group">
            <label for="nombre">Nombre completo</label>
            <input
                class="input"
                id="nombre"
                name="nombre"
                value="<?php echo e(old('nombre')); ?>"
                maxlength="120"
                required
                autocomplete="name"
            >
        </div>

        <div class="form-group">
            <label for="correo">Correo</label>
            <input
                class="input"
                id="correo"
                type="email"
                name="correo"
                value="<?php echo e(old('correo')); ?>"
                maxlength="160"
                required
                autocomplete="email"
            >
        </div>

        <div class="form-group" data-wholesale-field <?php if(old('tipo_cliente', 'minorista') !== 'mayorista'): ?> hidden <?php endif; ?>>
            <label for="ruc">RUC</label>
            <input
                class="input"
                id="ruc"
                name="ruc"
                value="<?php echo e(old('ruc')); ?>"
                maxlength="11"
                inputmode="numeric"
                autocomplete="organization"
                <?php if(old('tipo_cliente') === 'mayorista'): echo 'required'; endif; ?>
            >
        </div>

        <div class="form-group">
            <label for="password">Contraseña</label>
            <div class="admin-password-input auth-password-field">
                <input class="input" id="password" type="password" name="password" minlength="10" maxlength="4096" required autocomplete="new-password">
                <button type="button" data-password-toggle aria-label="Mostrar contraseña"><i class="bi bi-eye" aria-hidden="true"></i></button>
            </div>
        </div>

        <div class="form-group">
            <label for="password_confirmation">Confirmar contraseña</label>
            <div class="admin-password-input auth-password-field">
                <input class="input" id="password_confirmation" type="password" name="password_confirmation" maxlength="4096" required autocomplete="new-password">
                <button type="button" data-password-toggle aria-label="Mostrar contraseña"><i class="bi bi-eye" aria-hidden="true"></i></button>
            </div>
        </div>
        <button class="btn btn-primary" style="width:100%">Registrarme</button>
    </form>
    <p>¿Ya tienes cuenta? <a style="color: var(--primary)" href="<?php echo e(route('login')); ?>">Inicia sesión</a>.</p>
</div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.auth.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>