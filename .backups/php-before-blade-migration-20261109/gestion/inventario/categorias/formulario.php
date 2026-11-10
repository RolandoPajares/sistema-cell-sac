<?php ($editing = isset($category) && $category !== null); ?>

<label>
    Nombre
    <input
        name="nombre"
        <?php if ($editing) { ?> value="<?php echo e($category->nombre); ?>" <?php } ?>
        required
        maxlength="120"
    >
</label>

<label>
    Descripción
    <textarea name="descripcion" maxlength="500"><?php echo e($editing ? $category->descripcion : ''); ?></textarea>
</label>
