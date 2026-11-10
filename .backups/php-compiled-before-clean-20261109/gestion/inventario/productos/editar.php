<form class="productos-create-form productos-edit-form" method="post" action="<?php echo e(route('management.products.update', $product)); ?>" enctype="multipart/form-data">
    <?php echo csrf_field(); ?>
    <?php echo method_field('PUT'); ?>

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
                        <input class="productos-create-input" name="nombre" value="<?php echo e(old('nombre', $product->nombre)); ?>" maxlength="160" required>
                    </label>

                    <label class="productos-create-field productos-create-field--sku">
                        <span>Etiqueta del producto</span>
                        <input class="productos-create-input" name="etiqueta" value="<?php echo e(old('etiqueta', $product->etiqueta)); ?>" maxlength="80">
                    </label>

                    <label class="productos-create-field">
                        <span>Categoría <b>*</b></span>
                        <select class="productos-create-input" name="categoria_id" required>
                            <option value="">Selecciona una categoría</option>
                            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($category->id); ?>" <?php if((string) old('categoria_id', $product->categoria_id) === (string) $category->id): echo 'selected'; endif; ?>><?php echo e($category->nombre); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </label>

                    <label class="productos-create-field">
                        <span>Marca <b>*</b></span>
                        <select class="productos-create-input" name="marca" required>
                            <option value="">Selecciona una marca</option>
                            <?php $__currentLoopData = ($brands ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $brand): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($brand); ?>" <?php if(old('marca', $product->marca) === $brand): echo 'selected'; endif; ?>><?php echo e($brand); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php if($product->marca && !($brands ?? collect())->contains($product->marca)): ?>
                                <option value="<?php echo e($product->marca); ?>" selected><?php echo e($product->marca); ?></option>
                            <?php endif; ?>
                        </select>
                    </label>

                    <label class="productos-create-field productos-create-field--compact">
                        <span>Precio de venta <b>*</b></span>
                        <span class="productos-money-input"><b>S/</b><input class="productos-create-input" name="precio" type="number" min="0" step="0.01" value="<?php echo e(old('precio', $product->precio)); ?>" required></span>
                    </label>

                    <label class="productos-create-field productos-create-field--compact">
                        <span>Precio de costo</span>
                        <span class="productos-money-input"><b>S/</b><input class="productos-create-input" name="precio_original" type="number" min="0" step="0.01" value="<?php echo e(old('precio_original', $product->precio_original)); ?>"></span>
                    </label>

                    <label class="productos-create-field productos-create-field--compact">
                        <span>Existencias físicas en este alcance</span>
                        <input class="productos-create-input" type="number" value="<?php echo e(number_format($product->existencias_alcance)); ?>" readonly>
                        <small>Para ajustar el stock, selecciona el almacén desde <a href="<?php echo e(route('management.inventory')); ?>">Inventario</a>.</small>
                    </label>

                    <label class="productos-create-field productos-create-field--status">
                        <span>Estado <b>*</b></span>
                        <span class="productos-status-select">
                            <i class="bi bi-circle-fill" aria-hidden="true"></i>
                            <select class="productos-create-input" name="activo">
                                <option value="1" <?php if((string) old('activo', (int) $product->activo) === '1'): echo 'selected'; endif; ?>>Activo</option>
                                <option value="0" <?php if((string) old('activo', (int) $product->activo) === '0'): echo 'selected'; endif; ?>>Inactivo</option>
                            </select>
                        </span>
                    </label>

                    <label class="productos-create-field productos-create-field--provider">
                        <span>Proveedor</span>
                        <select class="productos-create-input" name="proveedor_id">
                            <option value="">Selecciona un proveedor</option>
                            <?php $__currentLoopData = ($suppliers ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($supplier->id); ?>" <?php if((string) old('proveedor_id', $product->suppliers->first()?->id) === (string) $supplier->id): echo 'selected'; endif; ?>><?php echo e($supplier->nombre); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                    <textarea class="productos-create-input productos-create-textarea" name="descripcion" maxlength="1000"><?php echo e(old('descripcion', $product->descripcion)); ?></textarea>
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
                    <input type="file" name="imagen" accept="image/jpeg,image/png,image/webp">
                    <i class="bi bi-cloud-arrow-up" aria-hidden="true"></i>
                    <strong>Actualizar imagen</strong>
                    <small>JPG, PNG o WEBP (máx. 5 MB)</small>
                    <span class="productos-image-select"><i class="bi bi-image" aria-hidden="true"></i> Seleccionar imagen</span>
                </label>
                <label class="productos-create-field productos-url-image-field">
                    <span>URL de imagen</span>
                    <input class="productos-create-input" name="url_imagen" value="<?php echo e(old('url_imagen', $product->url_imagen)); ?>" maxlength="255" placeholder="https://...">
                </label>
            </div>
        </aside>
    </div>

    <footer class="productos-create-footer">
        <button class="productos-create-cancel" type="button" data-close-product-edit>Cancelar</button>
        <button class="productos-create-submit" type="submit"><i class="bi bi-save" aria-hidden="true"></i> Guardar cambios</button>
    </footer>
</form>
