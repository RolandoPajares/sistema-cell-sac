<form class="ventas-modal-form" method="post" action="<?php echo e(route('management.sales.update', $order)); ?>" data-sales-form>
    <?php echo csrf_field(); ?>
    <?php echo method_field('PUT'); ?>

    <div class="ventas-modal-steps" aria-label="Pasos de edición">
        <span class="is-current"><b>1</b> Información</span>
        <i aria-hidden="true"></i>
        <span><b>2</b> Productos</span>
        <i aria-hidden="true"></i>
        <span><b>3</b> Pago y comprobante</span>
    </div>

    <section class="ventas-modal-section">
        <header class="ventas-modal-section-header"><i class="bi bi-pencil-square" aria-hidden="true"></i><h3>Información de la venta</h3></header>
        <div class="ventas-modal-grid ventas-modal-grid--two">
            <label class="ventas-modal-field">
                <span>Cliente <b>*</b></span>
                <select name="cliente_id" required data-sales-customer>
                    <?php $__currentLoopData = $customers;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $customer) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($customer->id); ?>" <?php if ($customer->id === $order->cliente_id) {
                            echo 'selected';
                        } ?>><?php echo e(($customer->razon_social ?: $customer->nombre_contacto)); ?> · <?php echo e($customer->correo); ?></option>
                    <?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?>
                </select>
            </label>
            <label class="ventas-modal-field">
                <span>Tipo de venta <b>*</b></span>
                <select name="tipo_venta" required>
                    <option value="fisica" selected>Venta física</option>
                </select>
            </label>
            <label class="ventas-modal-field">
                <span>Fecha de venta <b>*</b></span>
                <input type="date" name="fecha_venta" value="<?php echo e($order->fecha_venta?->toDateString() ?: now()->toDateString()); ?>" required>
            </label>
            <label class="ventas-modal-field">
                <span>Vendedor</span>
                <input type="text" value="<?php echo e($order->usuario?->nombre ?: 'Sin asesor registrado'); ?>" readonly>
            </label>
        </div>
        <label class="ventas-modal-field">
            <span>Observaciones</span>
            <textarea name="observaciones" maxlength="500" placeholder="Observaciones adicionales..."><?php echo e(old('observaciones', $order->observaciones)); ?></textarea>
        </label>
    </section>

    <section class="ventas-modal-section">
        <header class="ventas-modal-section-header"><i class="bi bi-bag-check" aria-hidden="true"></i><h3>Productos</h3></header>
        <div class="ventas-product-picker">
            <label class="ventas-modal-search"><i class="bi bi-search" aria-hidden="true"></i><span class="visually-hidden">Buscar producto</span><input type="search" placeholder="Buscar producto por nombre, código o categoría..." data-sales-product-search></label>
            <select class="ventas-product-select" aria-label="Producto para agregar" data-sales-product-picker>
                <option value="">Selecciona un producto</option>
                <?php $__currentLoopData = $products;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $product) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($product->id); ?>" data-price="<?php echo e(number_format((float) $product->effective_price, 2, '.', '')); ?>" data-stock="<?php echo e($product->existencias); ?>"><?php echo e($product->nombre); ?> · S/ <?php echo e(number_format((float) $product->effective_price, 2)); ?> · Stock <?php echo e($product->existencias); ?></option>
                <?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?>
            </select>
            <button class="ventas-modal-add" type="button" data-sales-add-product><i class="bi bi-plus-lg" aria-hidden="true"></i> Agregar</button>
        </div>
        <div class="ventas-modal-table-wrap">
            <table class="ventas-modal-table">
                <thead><tr><th>#</th><th>Producto</th><th>Precio (S/)</th><th>Cantidad</th><th>Subtotal (S/)</th><th>Acciones</th></tr></thead>
                <tbody data-sales-lines>
                    <?php $__currentLoopData = $order->detalles;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $index => $detail) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?>
                        <?php ($product = $detail->producto); ?>
                        <tr class="ventas-modal-line" data-sales-line data-product-id="<?php echo e($product?->id); ?>" data-price="<?php echo e(number_format((float) ($detail->precio_unitario ?? $product?->effective_price ?? 0), 2, '.', '')); ?>">
                            <td class="ventas-modal-line-number"><?php echo e($index + 1); ?></td>
                            <td><span class="ventas-modal-product"><span class="ventas-modal-product-image"><?php if ($product?->url_imagen) { ?><img src="<?php echo e(str_starts_with($product->url_imagen, 'http') ? $product->url_imagen : asset(ltrim($product->url_imagen, '/'))); ?>" alt=""><?php } else { ?><i class="bi bi-box-seam" aria-hidden="true"></i><?php } ?></span><span><strong><?php echo e($product?->nombre ?: 'Producto no disponible'); ?></strong><small>Stock actual: <?php echo e($product?->existencias ?? 0); ?></small></span></span><input type="hidden" name="items[<?php echo e($index); ?>][producto_id]" value="<?php echo e($product?->id); ?>"></td>
                            <td><strong><?php echo e(number_format((float) ($detail->precio_unitario ?? $product?->effective_price ?? 0), 2)); ?></strong></td>
                            <td><span class="ventas-quantity"><button type="button" data-sales-quantity="down" aria-label="Disminuir cantidad">−</button><input class="ventas-modal-quantity" type="number" aria-label="Cantidad de <?php echo e($product?->nombre ?: 'Producto no disponible'); ?>" name="items[<?php echo e($index); ?>][cantidad]" value="<?php echo e($detail->cantidad); ?>" min="1" max="<?php echo e(max(1, (int) ($product?->existencias ?? 1))); ?>" data-sales-quantity-input><button type="button" data-sales-quantity="up" aria-label="Aumentar cantidad">+</button></span></td>
                            <td><strong class="ventas-line-subtotal" data-sales-line-subtotal>S/ <?php echo e(number_format((float) ($detail->precio_unitario ?? 0) * (int) $detail->cantidad, 2)); ?></strong></td>
                            <td><button class="ventas-line-remove" type="button" data-sales-remove aria-label="Quitar producto"><i class="bi bi-trash3" aria-hidden="true"></i></button></td>
                        </tr>
                    <?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
        <div class="ventas-modal-summary">
            <span>Subtotal <strong data-sales-subtotal>S/ 0.00</strong></span>
            <span>Descuento <strong>S/ <?php echo e(number_format((float) $order->descuento, 2)); ?></strong></span>
            <span class="ventas-modal-total">Total <strong data-sales-total>S/ <?php echo e(number_format((float) $order->total, 2)); ?></strong></span>
        </div>
    </section>

    <footer class="ventas-modal-footer">
        <button class="ventas-modal-clear" type="button" data-sales-clear><i class="bi bi-trash3" aria-hidden="true"></i> Limpiar</button>
        <span></span>
        <a class="ventas-modal-cancel" href="<?php echo e(route('management.sales')); ?>">Cancelar</a>
        <button class="ventas-primary-button" type="submit"><i class="bi bi-check2" aria-hidden="true"></i> Guardar cambios</button>
    </footer>
</form>
