<form method="post" class="form-card compact-form" action="<?php echo e(route('management.purchases.store')); ?>">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="compra_token" value="<?php echo e(session('compra_token')); ?>">
    <h2>Registrar compra pendiente</h2>

    <label>
        Proveedor
        <select name="proveedor_id" required>
            <?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($supplier->id); ?>"><?php echo e($supplier->nombre); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </label>

    <label>
        Fecha
        <input
            type="date"
            name="fecha_compra"
            required
            value="<?php echo e(now()->toDateString()); ?>"
        >
    </label>

    <label>
        Producto
        <select name="items[0][producto_id]" required>
            <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($product->id); ?>"><?php echo e($product->nombre); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </label>

    <label>
        Variante (si corresponde)
        <select name="items[0][variante_id]">
            <option value="">Producto sin variantes</option>
            <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $__currentLoopData = $product->variants->where('estado', 'activo'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($variant->id); ?>"><?php echo e($product->nombre); ?> · <?php echo e($variant->nombre_color); ?><?php if($variant->codigo_sku): ?> · SKU <?php echo e($variant->codigo_sku); ?><?php endif; ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <small>Si el producto tiene variantes, selecciona una que pertenezca al producto indicado.</small>
    </label>

    <label>
        Cantidad
        <input type="number" name="items[0][cantidad]" min="1" required>
    </label>

    <label>
        Costo unitario
        <input
            type="number"
            name="items[0][costo_unitario]"
            step="0.01"
            min="0"
            required
        >
    </label>

    <button class="btn btn-primary">Registrar compra</button>
</form>
