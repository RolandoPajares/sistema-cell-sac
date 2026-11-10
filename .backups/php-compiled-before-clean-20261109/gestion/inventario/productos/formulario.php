<?php ($editing = isset($product) && $product !== null); ?>

<label class="admin-form-group">
    <span><?php echo e($editing ? 'Marca' : 'Marca *'); ?></span>
    <input
        name="marca"
        <?php if($editing): ?> value="<?php echo e($product->marca); ?>" <?php endif; ?>
        required
    >
</label>

<label class="admin-form-group">
    <span><?php echo e($editing ? 'Nombre' : 'Nombre *'); ?></span>
    <input
        name="nombre"
        <?php if($editing): ?> value="<?php echo e($product->nombre); ?>" <?php endif; ?>
        required
    >
</label>

<label class="admin-form-group is-full">
    <span><?php echo e($editing ? 'Categoría' : 'Categoría *'); ?></span>
    <select name="categoria_id" required>
        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($category->id); ?>" <?php if($editing && $category->id === $product->categoria_id): echo 'selected'; endif; ?>><?php echo e($category->nombre); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
</label>

<label class="admin-form-group">
    <span><?php echo e($editing ? 'Precio' : 'Precio *'); ?></span>
    <input
        name="precio"
        type="number"
        min="0"
        step="0.01"
        <?php if($editing): ?> value="<?php echo e($product->precio); ?>" <?php endif; ?>
        required
    >
</label>

<label class="admin-form-group">
    <span>Precio oferta</span>
    <input
        name="precio_oferta"
        type="number"
        min="0"
        step="0.01"
        <?php if($editing): ?> value="<?php echo e($product->precio_oferta); ?>" <?php endif; ?>
    >
</label>

<label class="admin-form-group is-full">
    <span><?php echo e($editing ? 'Imagen' : 'Ruta de imagen'); ?></span>
    <input
        name="url_imagen"
        <?php if(!$editing): ?> maxlength="255" <?php endif; ?>
        <?php if($editing): ?> value="<?php echo e($product->url_imagen); ?>" <?php endif; ?>
    >
</label>

<label class="admin-form-group is-full">
    <span>Descripción</span>
    <textarea
        name="descripcion"
        <?php if(!$editing): ?> rows="4" <?php endif; ?>
    ><?php echo e($editing ? $product->descripcion : ''); ?></textarea>
</label>
