<?php ($editing = isset($customer) && $customer !== null); ?>

<label>
    Tipo
    <select name="tipo">
        <option value="minorista" <?php if($editing && $customer->tipo === 'minorista'): echo 'selected'; endif; ?>>Minorista</option>
        <option value="mayorista" <?php if($editing && $customer->tipo === 'mayorista'): echo 'selected'; endif; ?>>Mayorista</option>
    </select>
</label>

<label>
    Documento
    <input
        name="documento"
        <?php if($editing): ?> value="<?php echo e($customer->documento); ?>" <?php endif; ?>
        required
        maxlength="20"
    >
</label>

<label>
    Nombre de contacto
    <input
        name="nombre_contacto"
        <?php if($editing): ?> value="<?php echo e($customer->nombre_contacto); ?>" <?php endif; ?>
        required
        maxlength="160"
    >
</label>

<label>
    Razón social
    <input
        name="razon_social"
        <?php if($editing): ?> value="<?php echo e($customer->razon_social); ?>" <?php endif; ?>
        maxlength="160"
    >
</label>

<label>
    Correo
    <input
        type="email"
        name="correo"
        <?php if($editing): ?> value="<?php echo e($customer->correo); ?>" <?php endif; ?>
        required
        maxlength="160"
    >
</label>

<label>
    Teléfono
    <input
        name="telefono"
        <?php if($editing): ?> value="<?php echo e($customer->telefono); ?>" <?php endif; ?>
        maxlength="30"
    >
</label>

<label>
    Ciudad
    <input
        name="ciudad"
        <?php if($editing): ?> value="<?php echo e($customer->ciudad); ?>" <?php endif; ?>
        maxlength="80"
    >
</label>
