<form method="post" class="form-card compact-form" action="<?php echo e(route('quotations.store')); ?>">
    <?php echo csrf_field(); ?>
    <?php if(auth()->user()->can('gestion.seguridad.consultar') || auth()->user()->can('gestion.cotizaciones.consultar')): ?>
        <label>
            Cliente mayorista
            <select name="cliente_id" required>
                <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($customer->id); ?>"><?php echo e($customer->nombre_contacto); ?> — <?php echo e($customer->documento); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </label>
    <?php endif; ?>

    <label>
        Producto
        <select name="items[0][producto_id]" required>
            <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($product->id); ?>"><?php echo e($product->nombre); ?> — S/ <?php echo e($product->effective_price); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </label>

    <label>
        Cantidad
        <input type="number" name="items[0][cantidad]" min="1" value="1" required>
    </label>

    <label>
        Notas
        <textarea name="notas"></textarea>
    </label>

    <button class="btn btn-primary">Guardar borrador</button>
</form>
