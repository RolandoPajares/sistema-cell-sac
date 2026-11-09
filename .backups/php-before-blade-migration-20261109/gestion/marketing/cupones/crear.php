<form method="post" class="form-card compact-form" action="<?php echo e(route('management.coupons.store')); ?>">
    <?php echo csrf_field(); ?>

    <label>
        Código
        <input name="codigo" value="<?php echo e(old('codigo')); ?>" maxlength="50" required>
        <?php $__errorArgs = ['codigo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) {
    if (isset($message)) {
        $__messageOriginal = $message;
    }
    $message = $__bag->first($__errorArgs[0]); ?><small role="alert"><?php echo e($message); ?></small><?php unset($message);
    if (isset($__messageOriginal)) {
        $message = $__messageOriginal;
    }
}
unset($__errorArgs, $__bag); ?>
    </label>

    <label>
        Tipo
        <select name="tipo_descuento" required>
            <option value="porcentaje">Porcentaje</option>
            <option value="fijo">Monto fijo</option>
        </select>
    </label>

    <label>
        Valor
        <input
            type="number"
            name="valor"
            step="0.01"
            min="0.01"
            required
            max="99999999.99"
        >
        <?php $__errorArgs = ['valor'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) {
    if (isset($message)) {
        $__messageOriginal = $message;
    }
    $message = $__bag->first($__errorArgs[0]); ?><small role="alert"><?php echo e($message); ?></small><?php unset($message);
    if (isset($__messageOriginal)) {
        $message = $__messageOriginal;
    }
}
unset($__errorArgs, $__bag); ?>
    </label>

    <label>
        Compra mínima
        <input
            type="number"
            name="minimo_compra"
            step="0.01"
            min="0"
            value="0"
            required
            max="99999999.99"
        >
        <?php $__errorArgs = ['minimo_compra'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) {
    if (isset($message)) {
        $__messageOriginal = $message;
    }
    $message = $__bag->first($__errorArgs[0]); ?><small role="alert"><?php echo e($message); ?></small><?php unset($message);
    if (isset($__messageOriginal)) {
        $message = $__messageOriginal;
    }
}
unset($__errorArgs, $__bag); ?>
    </label>

    <label>
        Límite usos
        <input type="number" name="limite_usos" min="1" max="4294967295">
        <?php $__errorArgs = ['limite_usos'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) {
    if (isset($message)) {
        $__messageOriginal = $message;
    }
    $message = $__bag->first($__errorArgs[0]); ?><small role="alert"><?php echo e($message); ?></small><?php unset($message);
    if (isset($__messageOriginal)) {
        $message = $__messageOriginal;
    }
}
unset($__errorArgs, $__bag); ?>
    </label>

    <label>
        Inicio
        <input type="datetime-local" name="inicia_en" value="<?php echo e(old('inicia_en')); ?>" required>
        <?php $__errorArgs = ['inicia_en'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) {
    if (isset($message)) {
        $__messageOriginal = $message;
    }
    $message = $__bag->first($__errorArgs[0]); ?><small role="alert"><?php echo e($message); ?></small><?php unset($message);
    if (isset($__messageOriginal)) {
        $message = $__messageOriginal;
    }
}
unset($__errorArgs, $__bag); ?>
    </label>

    <label>
        Fin
        <input type="datetime-local" name="finaliza_en" value="<?php echo e(old('finaliza_en')); ?>" required>
        <?php $__errorArgs = ['finaliza_en'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) {
    if (isset($message)) {
        $__messageOriginal = $message;
    }
    $message = $__bag->first($__errorArgs[0]); ?><small role="alert"><?php echo e($message); ?></small><?php unset($message);
    if (isset($__messageOriginal)) {
        $message = $__messageOriginal;
    }
}
unset($__errorArgs, $__bag); ?>
    </label>

    <button class="btn btn-primary" <?php if (! $empresaCuponDisponible) {
        echo 'disabled';
    } ?>>Guardar cupón</button>
</form>
