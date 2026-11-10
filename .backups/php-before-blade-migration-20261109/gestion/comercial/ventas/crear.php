<form class="ventas-modal-form" method="post" action="<?php echo e(route('management.sales.store')); ?>" data-sales-form>
    <?php echo csrf_field(); ?>
    <input type="hidden" name="venta_token" value="<?php echo e(session('venta_token')); ?>">

    <div class="ventas-modal-steps" aria-label="Pasos del registro">
        <span class="is-current"><b>1</b> Información</span>
        <i aria-hidden="true"></i>
        <span><b>2</b> Productos</span>
        <i aria-hidden="true"></i>
        <span><b>3</b> Confirmación</span>
    </div>

    <section class="ventas-modal-section">
        <header class="ventas-modal-section-header"><i class="bi bi-person-vcard" aria-hidden="true"></i><h3>Información de la venta</h3></header>
        <div class="ventas-modal-grid ventas-modal-grid--two">
            <label class="ventas-modal-field">
                <span>Cliente <b>*</b></span>
                <select name="cliente_id" required data-sales-customer>
                    <option value="">Buscar cliente...</option>
                    <?php $__currentLoopData = $customers;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $customer) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($customer->id); ?>" <?php if ((string) old('cliente_id') === (string) $customer->id) {
                            echo 'selected';
                        } ?>><?php echo e($customer->razon_social ?: $customer->nombre_contacto); ?> · <?php echo e($customer->correo); ?></option>
                    <?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?>
                </select>
            </label>
            <label class="ventas-modal-field">
                <span>Tipo de venta <b>*</b></span>
                <select name="tipo_venta" required>
                    <option value="fisica" <?php if (old('tipo_venta', 'fisica') === 'fisica') {
                        echo 'selected';
                    } ?>>Venta física</option>
                </select>
            </label>
            <label class="ventas-modal-field">
                <span>Fecha de venta <b>*</b></span>
                <input type="date" name="fecha_venta" value="<?php echo e(old('fecha_venta', now()->toDateString())); ?>" required>
            </label>
            <label class="ventas-modal-field">
                <span>Vendedor</span>
                <input type="text" value="<?php echo e(auth()->user()->nombre); ?>" readonly>
            </label>
        </div>
        <label class="ventas-modal-field">
            <span>Observaciones</span>
            <textarea name="observaciones" maxlength="500" placeholder="Observaciones adicionales..."><?php echo e(old('observaciones')); ?></textarea>
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
                    <tr data-sales-empty><td colspan="6" class="ventas-modal-empty"><i class="bi bi-bag" aria-hidden="true"></i> Agrega al menos un producto para registrar la venta.</td></tr>
                </tbody>
            </table>
        </div>
        <div class="ventas-modal-summary">
            <span>Subtotal <strong data-sales-subtotal>S/ 0.00</strong></span>
            <span>Descuento <strong>S/ 0.00</strong></span>
            <span class="ventas-modal-total">Total <strong data-sales-total>S/ 0.00</strong></span>
        </div>
    </section>

    <footer class="ventas-modal-footer">
        <button class="ventas-modal-clear" type="button" data-sales-clear><i class="bi bi-trash3" aria-hidden="true"></i> Limpiar</button>
        <span></span>
        <button class="ventas-modal-cancel" type="button" data-close-sales-dialog>Cancelar</button>
        <button class="ventas-primary-button" type="submit"><i class="bi bi-check2" aria-hidden="true"></i> Guardar venta</button>
    </footer>
</form>
