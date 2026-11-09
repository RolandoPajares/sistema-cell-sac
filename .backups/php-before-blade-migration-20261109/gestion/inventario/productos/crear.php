<form class="productos-create-form" method="post" action="<?php echo e(route('management.products.store')); ?>" enctype="multipart/form-data">
    <?php echo csrf_field(); ?>

    <div class="productos-create-layout">
        <section class="productos-create-main">
            <div class="productos-form-section">
                <header class="productos-form-section-header">
                    <i class="bi bi-file-earmark-text" aria-hidden="true"></i>
                    <h3>Información básica</h3>
                </header>

                <div class="productos-create-fields">
                    <label class="productos-create-field productos-create-field--wide">
                        <span>Nombre del producto <b>*</b></span>
                        <input class="productos-create-input" name="nombre" value="<?php echo e(old('nombre')); ?>" placeholder="Ej. iPhone 15 Pro 128GB" maxlength="160" required>
                        <small>Máximo 160 caracteres</small>
                    </label>

                    <label class="productos-create-field productos-create-field--sku">
                        <span>Etiqueta del producto</span>
                        <input class="productos-create-input" name="etiqueta" value="<?php echo e(old('etiqueta')); ?>" maxlength="80">
                    </label>

                    <label class="productos-create-field">
                        <span>Categoría <b>*</b></span>
                        <select class="productos-create-input" name="categoria_id" required>
                            <option value="">Selecciona una categoría</option>
                            <?php $__currentLoopData = $categories;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $category) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($category->id); ?>" <?php if ((string) old('categoria_id') === (string) $category->id) {
                                    echo 'selected';
                                } ?>><?php echo e($category->nombre); ?></option>
                            <?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?>
                        </select>
                    </label>

                    <label class="productos-create-field">
                        <span>Marca <b>*</b></span>
                        <select class="productos-create-input" name="marca" required>
                            <option value="">Selecciona una marca</option>
                            <?php $__currentLoopData = ($brands ?? collect());
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $brand) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($brand); ?>" <?php if (old('marca') === $brand) {
                                    echo 'selected';
                                } ?>><?php echo e($brand); ?></option>
                            <?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?>
                            <?php if (old('marca') && ! ($brands ?? collect())->contains(old('marca'))) { ?>
                                <option value="<?php echo e(old('marca')); ?>" selected><?php echo e(old('marca')); ?></option>
                            <?php } ?>
                        </select>
                    </label>

                    <label class="productos-create-field productos-create-field--compact">
                        <span>Precio de venta <b>*</b></span>
                        <span class="productos-money-input"><b>S/</b><input class="productos-create-input" name="precio" type="number" min="0" step="0.01" value="<?php echo e(old('precio')); ?>" placeholder="0.00" required></span>
                    </label>

                    <label class="productos-create-field productos-create-field--compact">
                        <span>Precio de costo</span>
                        <span class="productos-money-input"><b>S/</b><input class="productos-create-input" name="precio_original" type="number" min="0" step="0.01" value="<?php echo e(old('precio_original')); ?>" placeholder="0.00"></span>
                    </label>

                    <label class="productos-create-field productos-create-field--compact">
                        <span>Existencias iniciales (se registran en Inventario)</span>
                        <input class="productos-create-input" name="existencias" type="number" min="0" step="1" value="0" readonly aria-label="Las existencias iniciales se registran desde el módulo Inventario">
                    </label>

                    <label class="productos-create-field productos-create-field--status">
                        <span>Estado <b>*</b></span>
                        <span class="productos-status-select"><i class="bi bi-circle-fill" aria-hidden="true"></i><select class="productos-create-input" aria-label="Estado del producto" disabled><option>Activo</option></select></span>
                    </label>

                    <label class="productos-create-field productos-create-field--provider">
                        <span>Proveedor</span>
                        <select class="productos-create-input" name="proveedor_id">
                            <option value="">Selecciona un proveedor</option>
                            <?php $__currentLoopData = ($suppliers ?? collect());
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $supplier) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($supplier->id); ?>" <?php if ((string) old('proveedor_id') === (string) $supplier->id) {
                                    echo 'selected';
                                } ?>><?php echo e($supplier->nombre); ?></option>
                            <?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?>
                        </select>
                    </label>
                </div>
            </div>

            <div class="productos-form-section">
                <header class="productos-form-section-header">
                    <i class="bi bi-card-text" aria-hidden="true"></i>
                    <h3>Descripción</h3>
                </header>
                <label class="productos-create-field">
                    <span class="visually-hidden">Descripción del producto</span>
                    <textarea class="productos-create-input productos-create-textarea" name="descripcion" maxlength="1000" placeholder="Describe las características del producto, especificaciones, contenido de la caja, etc."><?php echo e(old('descripcion')); ?></textarea>
                    <small>Máximo 1000 caracteres</small>
                </label>
            </div>
        </section>

        <aside class="productos-create-media">
            <div class="productos-form-section productos-form-section--media">
                <header class="productos-form-section-header">
                    <i class="bi bi-image" aria-hidden="true"></i>
                    <h3>Imagen del producto</h3>
                </header>

                <label class="productos-image-uploader">
                    <input type="file" name="imagen" accept="image/jpeg,image/png,image/webp" data-product-image-input>
                    <i class="bi bi-cloud-arrow-up" aria-hidden="true"></i>
                    <strong>Subir imagen</strong>
                    <small>Arrastra una imagen aquí o haz clic para seleccionar</small>
                    <small>Formatos: JPG, PNG, WEBP (máx. 5 MB)</small>
                    <span class="productos-image-select"><i class="bi bi-image" aria-hidden="true"></i> Seleccionar imagen</span>
                </label>

                <div class="productos-image-preview" data-product-image-preview hidden>
                    <img src="" alt="Vista previa de la imagen del producto" data-product-image-preview-image>
                    <button type="button" data-product-image-clear aria-label="Quitar imagen"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
                </div>

                <label class="productos-create-field productos-url-image-field">
                    <span>O registra una URL de imagen</span>
                    <input class="productos-create-input" name="url_imagen" value="<?php echo e(old('url_imagen')); ?>" maxlength="255" placeholder="https://...">
                </label>
            </div>
        </aside>
    </div>


    <footer class="productos-create-footer">
        <button class="productos-create-cancel" type="button" data-close-product-create>Cancelar</button>
        <button class="productos-create-secondary" type="submit" name="continue" value="1"><i class="bi bi-plus-circle" aria-hidden="true"></i> Guardar y registrar otro</button>
        <button class="productos-create-submit" type="submit"><i class="bi bi-save" aria-hidden="true"></i> Guardar producto</button>
    </footer>
</form>
