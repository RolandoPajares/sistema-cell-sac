<?php $__env->startSection('title', 'Preferencias de visualización'); ?>
<?php $__env->startSection('content'); ?>
    <section class="admin-page">
        <header class="admin-page-head"><div><span class="eyebrow">Cuenta personal</span><h1>Preferencias de visualización</h1><p>Guarda preferencias personales sin cambiar la configuración global del ecommerce.</p></div></header>
        <?php echo $__env->make('cuenta.perfil.navegacion', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php if (! ($preferencesAvailable)) { ?>
            <div class="admin-alert admin-alert--error" role="status">Las preferencias quedan pendientes hasta aplicar la migración de seguridad de cuenta.</div>
        <?php } else { ?>
            <?php ($preferences = (array) auth()->user()->preferencias); ?>
            <form method="post" action="<?php echo e(route('management.account.preferences.update')); ?>" class="form-card">
                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                <input type="hidden" name="menu_compacto" value="0"><label><input type="checkbox" name="menu_compacto" value="1" <?php if ($preferences['menu_compacto'] ?? false) {
                    echo 'checked';
                } ?>> Mantener el menú lateral compacto</label>
                <input type="hidden" name="idioma" value="es">
                <button class="btn btn-primary" type="submit">Guardar preferencias</button>
            </form>
        <?php } ?>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.panel.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>