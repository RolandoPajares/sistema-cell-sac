<form method="POST" action="<?php echo e(route('management.users.store')); ?>" class="form-card usuarios-form" data-user-form>
    <?php echo csrf_field(); ?>

    <?php if ($errors->any()) { ?>
        <div class="admin-alert admin-alert--error usuarios-form-errors" role="alert">
            <strong>No se pudo registrar la cuenta.</strong>
            <ul>
                <?php $__currentLoopData = $errors->all();
        $__env->addLoop($__currentLoopData);
        foreach ($__currentLoopData as $error) {
            $__env->incrementLoopIndices();
            $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php } $__env->popLoop();
        $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php } ?>

    <section class="usuarios-form-section">
        <header class="usuarios-form-section-header"><i class="bi bi-person-lock" aria-hidden="true"></i><h3>Datos de acceso</h3><small>Los campos con <b>*</b> son obligatorios</small></header>
        <div class="usuarios-form-grid">
            <label><span>Nombre completo <b>*</b></span><input type="text" name="nombre" required maxlength="120" value="<?php echo e(old('nombre')); ?>" placeholder="Ej. Juan Pérez García" autocomplete="name" <?php $__errorArgs = ['nombre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) {
    if (isset($message)) {
        $__messageOriginal = $message;
    }
    $message = $__bag->first($__errorArgs[0]); ?> aria-invalid="true" <?php unset($message);
    if (isset($__messageOriginal)) {
        $message = $__messageOriginal;
    }
}
unset($__errorArgs, $__bag); ?>><?php $__errorArgs = ['nombre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) {
    if (isset($message)) {
        $__messageOriginal = $message;
    }
    $message = $__bag->first($__errorArgs[0]); ?><small class="usuarios-field-error"><?php echo e($message); ?></small><?php unset($message);
    if (isset($__messageOriginal)) {
        $message = $__messageOriginal;
    }
}
unset($__errorArgs, $__bag); ?></label>
            <label><span>Correo electrónico <b>*</b></span><input type="email" name="correo" required maxlength="160" value="<?php echo e(old('correo')); ?>" placeholder="usuario@empresa.com" autocomplete="email" <?php $__errorArgs = ['correo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) {
    if (isset($message)) {
        $__messageOriginal = $message;
    }
    $message = $__bag->first($__errorArgs[0]); ?> aria-invalid="true" <?php unset($message);
    if (isset($__messageOriginal)) {
        $message = $__messageOriginal;
    }
}
unset($__errorArgs, $__bag); ?>><?php $__errorArgs = ['correo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) {
    if (isset($message)) {
        $__messageOriginal = $message;
    }
    $message = $__bag->first($__errorArgs[0]); ?><small class="usuarios-field-error"><?php echo e($message); ?></small><?php unset($message);
    if (isset($__messageOriginal)) {
        $message = $__messageOriginal;
    }
}
unset($__errorArgs, $__bag); ?></label>
            <label><span>Rol del usuario <b>*</b></span><select name="rol" required data-user-role <?php $__errorArgs = ['rol'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) {
    if (isset($message)) {
        $__messageOriginal = $message;
    }
    $message = $__bag->first($__errorArgs[0]); ?> aria-invalid="true" <?php unset($message);
    if (isset($__messageOriginal)) {
        $message = $__messageOriginal;
    }
}
unset($__errorArgs, $__bag); ?>><option value="">Seleccionar rol configurable</option><?php $__currentLoopData = $roles;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $role) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?><option value="<?php echo e($role); ?>" data-scope="<?php echo e($roleScopes[$role] ?? ''); ?>" <?php if (old('rol') === $role) {
        echo 'selected';
    } ?>><?php echo e($roleLabels[$role] ?? str_replace('_', ' ', $role)); ?></option><?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?></select><?php $__errorArgs = ['rol'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) {
    if (isset($message)) {
        $__messageOriginal = $message;
    }
    $message = $__bag->first($__errorArgs[0]); ?><small class="usuarios-field-error"><?php echo e($message); ?></small><?php unset($message);
    if (isset($__messageOriginal)) {
        $message = $__messageOriginal;
    }
}
unset($__errorArgs, $__bag); ?></label>
            <div class="usuarios-form-note" data-user-site-field hidden><i class="bi bi-geo-alt" aria-hidden="true"></i> La sede interna autorizada se asigna automáticamente según el contexto de la empresa.</div>
            <label><span>Estado de la cuenta <b>*</b></span><select disabled><option>● Activo</option></select></label>
            <label><span>Contraseña temporal <b>*</b></span><input type="password" name="password" minlength="12" required autocomplete="new-password" placeholder="Mínimo 12 caracteres" <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) {
    if (isset($message)) {
        $__messageOriginal = $message;
    }
    $message = $__bag->first($__errorArgs[0]); ?> aria-invalid="true" <?php unset($message);
    if (isset($__messageOriginal)) {
        $message = $__messageOriginal;
    }
}
unset($__errorArgs, $__bag); ?>><small>Debe contener letras y números.</small><?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) {
    if (isset($message)) {
        $__messageOriginal = $message;
    }
    $message = $__bag->first($__errorArgs[0]); ?><small class="usuarios-field-error"><?php echo e($message); ?></small><?php unset($message);
    if (isset($__messageOriginal)) {
        $message = $__messageOriginal;
    }
}
unset($__errorArgs, $__bag); ?></label>
            <label><span>Confirmar contraseña <b>*</b></span><input type="password" name="password_confirmation" minlength="12" required autocomplete="new-password" placeholder="Repite la contraseña" <?php $__errorArgs = ['password_confirmation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) {
    if (isset($message)) {
        $__messageOriginal = $message;
    }
    $message = $__bag->first($__errorArgs[0]); ?> aria-invalid="true" <?php unset($message);
    if (isset($__messageOriginal)) {
        $message = $__messageOriginal;
    }
}
unset($__errorArgs, $__bag); ?>><small>Las contraseñas deben coincidir.</small><?php $__errorArgs = ['password_confirmation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) {
    if (isset($message)) {
        $__messageOriginal = $message;
    }
    $message = $__bag->first($__errorArgs[0]); ?><small class="usuarios-field-error"><?php echo e($message); ?></small><?php unset($message);
    if (isset($__messageOriginal)) {
        $message = $__messageOriginal;
    }
}
unset($__errorArgs, $__bag); ?></label>
        </div>
    </section>

    <section class="usuarios-form-section" data-user-customer-section hidden>
        <header class="usuarios-form-section-header"><i class="bi bi-person-vcard" aria-hidden="true"></i><h3>Información adicional</h3></header>
        <div class="usuarios-form-note" data-user-role-note hidden><i class="bi bi-info-circle" aria-hidden="true"></i> Estos datos solo aplican a los roles de cliente.</div>
        <div class="usuarios-form-grid usuarios-form-grid--additional">
            <label data-user-customer-field><span>Tipo de documento <b>*</b></span><select name="tipo_documento" data-user-document-type required><option value="">Seleccionar tipo</option><option value="dni" <?php if (old('tipo_documento') === 'dni') {
                echo 'selected';
            } ?>>DNI — persona natural</option><option value="ruc" <?php if (old('tipo_documento') === 'ruc') {
                echo 'selected';
            } ?>>RUC — empresa o negocio</option></select><?php $__errorArgs = ['tipo_documento'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) {
    if (isset($message)) {
        $__messageOriginal = $message;
    }
    $message = $__bag->first($__errorArgs[0]); ?><small class="usuarios-field-error"><?php echo e($message); ?></small><?php unset($message);
    if (isset($__messageOriginal)) {
        $message = $__messageOriginal;
    }
}
unset($__errorArgs, $__bag); ?></label>
            <label data-user-customer-field><span data-user-document-label>Documento <b data-user-customer-required>*</b></span><input type="text" name="documento" maxlength="11" value="<?php echo e(old('documento')); ?>" placeholder="Selecciona DNI o RUC" inputmode="numeric" data-user-document <?php $__errorArgs = ['documento'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) {
    if (isset($message)) {
        $__messageOriginal = $message;
    }
    $message = $__bag->first($__errorArgs[0]); ?> aria-invalid="true" <?php unset($message);
    if (isset($__messageOriginal)) {
        $message = $__messageOriginal;
    }
}
unset($__errorArgs, $__bag); ?>><?php $__errorArgs = ['documento'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) {
    if (isset($message)) {
        $__messageOriginal = $message;
    }
    $message = $__bag->first($__errorArgs[0]); ?><small class="usuarios-field-error"><?php echo e($message); ?></small><?php unset($message);
    if (isset($__messageOriginal)) {
        $message = $__messageOriginal;
    }
}
unset($__errorArgs, $__bag); ?></label>
            <label data-user-customer-field data-user-ruc-field><span>Razón social <b>*</b></span><input type="text" name="razon_social" maxlength="160" value="<?php echo e(old('razon_social')); ?>" placeholder="Ej. Empresa S.A.C." <?php $__errorArgs = ['razon_social'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) {
    if (isset($message)) {
        $__messageOriginal = $message;
    }
    $message = $__bag->first($__errorArgs[0]); ?> aria-invalid="true" <?php unset($message);
    if (isset($__messageOriginal)) {
        $message = $__messageOriginal;
    }
}
unset($__errorArgs, $__bag); ?>><?php $__errorArgs = ['razon_social'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) {
    if (isset($message)) {
        $__messageOriginal = $message;
    }
    $message = $__bag->first($__errorArgs[0]); ?><small class="usuarios-field-error"><?php echo e($message); ?></small><?php unset($message);
    if (isset($__messageOriginal)) {
        $message = $__messageOriginal;
    }
}
unset($__errorArgs, $__bag); ?></label>
            <label data-user-customer-field><span>Teléfono</span><input type="text" name="telefono" maxlength="30" value="<?php echo e(old('telefono')); ?>" placeholder="+51 987 654 321" autocomplete="tel" <?php $__errorArgs = ['telefono'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) {
    if (isset($message)) {
        $__messageOriginal = $message;
    }
    $message = $__bag->first($__errorArgs[0]); ?> aria-invalid="true" <?php unset($message);
    if (isset($__messageOriginal)) {
        $message = $__messageOriginal;
    }
}
unset($__errorArgs, $__bag); ?>><?php $__errorArgs = ['telefono'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) {
    if (isset($message)) {
        $__messageOriginal = $message;
    }
    $message = $__bag->first($__errorArgs[0]); ?><small class="usuarios-field-error"><?php echo e($message); ?></small><?php unset($message);
    if (isset($__messageOriginal)) {
        $message = $__messageOriginal;
    }
}
unset($__errorArgs, $__bag); ?></label>
            <label data-user-customer-field><span>Ciudad</span><input type="text" name="ciudad" maxlength="80" value="<?php echo e(old('ciudad')); ?>" placeholder="Lima" autocomplete="address-level2" <?php $__errorArgs = ['ciudad'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) {
    if (isset($message)) {
        $__messageOriginal = $message;
    }
    $message = $__bag->first($__errorArgs[0]); ?> aria-invalid="true" <?php unset($message);
    if (isset($__messageOriginal)) {
        $message = $__messageOriginal;
    }
}
unset($__errorArgs, $__bag); ?>><?php $__errorArgs = ['ciudad'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) {
    if (isset($message)) {
        $__messageOriginal = $message;
    }
    $message = $__bag->first($__errorArgs[0]); ?><small class="usuarios-field-error"><?php echo e($message); ?></small><?php unset($message);
    if (isset($__messageOriginal)) {
        $message = $__messageOriginal;
    }
}
unset($__errorArgs, $__bag); ?></label>
        </div>
    </section>

    <section class="usuarios-form-section usuarios-roles-section">
        <header class="usuarios-form-section-header"><i class="bi bi-shield-check" aria-hidden="true"></i><h3>Roles disponibles</h3></header>
        <div class="usuarios-role-chips"><?php $__currentLoopData = $roles;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $role) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?><span><?php echo e($roleLabels[$role] ?? str_replace('_', ' ', $role)); ?></span><?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?></div>
        <label class="usuarios-notification"><input type="checkbox" name="notificar" value="1" checked><span>Notificar al usuario por correo cuando se habilite el envío seguro de invitaciones.</span></label>
    </section>

    <footer class="usuarios-dialog-footer usuarios-form-footer"><span><i class="bi bi-lock-fill" aria-hidden="true"></i> Los permisos dependen del rol asignado.</span><div><button class="usuarios-cancel-button" type="button" data-close-user-dialog>Cancelar</button><button class="usuarios-submit-button" type="submit"><i class="bi bi-plus-lg" aria-hidden="true"></i> Crear usuario</button></div></footer>
</form>
