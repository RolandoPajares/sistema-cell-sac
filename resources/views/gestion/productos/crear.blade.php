<form class="productos-create-form" method="post" action="{{ route('management.products.store') }}" enctype="multipart/form-data">
    @csrf

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
                        <input class="productos-create-input" name="nombre" value="{{ old('nombre') }}" placeholder="Ej. iPhone 15 Pro 128GB" maxlength="160" required>
                        <small>Máximo 160 caracteres</small>
                    </label>

                    <label class="productos-create-field productos-create-field--sku">
                        <span>SKU <b>*</b></span>
                        <input class="productos-create-input" name="etiqueta" value="{{ old('etiqueta') }}" placeholder="Ej. IP15P-128-BLK" maxlength="80" required>
                    </label>

                    <label class="productos-create-field">
                        <span>Categoría <b>*</b></span>
                        <select class="productos-create-input" name="categoria_id" required>
                            <option value="">Selecciona una categoría</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) old('categoria_id') === (string) $category->id)>{{ $category->nombre }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="productos-create-field">
                        <span>Marca <b>*</b></span>
                        <select class="productos-create-input" name="marca" required>
                            <option value="">Selecciona una marca</option>
                            @foreach (($brands ?? collect()) as $brand)
                                <option value="{{ $brand }}" @selected(old('marca') === $brand)>{{ $brand }}</option>
                            @endforeach
                            @if (old('marca') && !($brands ?? collect())->contains(old('marca')))
                                <option value="{{ old('marca') }}" selected>{{ old('marca') }}</option>
                            @endif
                        </select>
                    </label>

                    <label class="productos-create-field productos-create-field--compact">
                        <span>Precio de venta <b>*</b></span>
                        <span class="productos-money-input"><b>S/</b><input class="productos-create-input" name="precio" type="number" min="0" step="0.01" value="{{ old('precio') }}" placeholder="0.00" required></span>
                    </label>

                    <label class="productos-create-field productos-create-field--compact">
                        <span>Precio de costo</span>
                        <span class="productos-money-input"><b>S/</b><input class="productos-create-input" name="precio_original" type="number" min="0" step="0.01" value="{{ old('precio_original') }}" placeholder="0.00"></span>
                    </label>

                    <label class="productos-create-field productos-create-field--compact">
                        <span>Stock inicial <b>*</b></span>
                        <input class="productos-create-input" name="existencias" type="number" min="0" step="1" value="{{ old('existencias', 0) }}" required>
                    </label>

                    <label class="productos-create-field productos-create-field--status">
                        <span>Estado <b>*</b></span>
                        <span class="productos-status-select"><i class="bi bi-circle-fill" aria-hidden="true"></i><select class="productos-create-input" aria-label="Estado del producto" disabled><option>Activo</option></select></span>
                    </label>

                    <label class="productos-create-field productos-create-field--provider">
                        <span>Proveedor</span>
                        <select class="productos-create-input" name="proveedor_id">
                            <option value="">Selecciona un proveedor</option>
                            @foreach (($suppliers ?? collect()) as $supplier)
                                <option value="{{ $supplier->id }}" @selected((string) old('proveedor_id') === (string) $supplier->id)>{{ $supplier->nombre }}</option>
                            @endforeach
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
                    <textarea class="productos-create-input productos-create-textarea" name="descripcion" maxlength="1000" placeholder="Describe las características del producto, especificaciones, contenido de la caja, etc.">{{ old('descripcion') }}</textarea>
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
                    <input class="productos-create-input" name="url_imagen" value="{{ old('url_imagen') }}" maxlength="255" placeholder="https://...">
                </label>
            </div>
        </aside>
    </div>

    <section class="productos-excel-callout">
        <div class="productos-excel-icon"><i class="bi bi-file-earmark-spreadsheet" aria-hidden="true"></i></div>
        <div class="productos-excel-copy">
            <h3>Carga masiva por Excel</h3>
            <p>Importa 1000 o más productos con una plantilla estandarizada.</p>
            <ul>
                <li><i class="bi bi-check2" aria-hidden="true"></i> Registra múltiples productos de forma rápida</li>
                <li><i class="bi bi-check2" aria-hidden="true"></i> Valida los datos antes de importar</li>
            </ul>
        </div>
        <div class="productos-excel-actions">
            <a class="productos-excel-template" href="{{ route('management.products.export', ['format' => 'excel']) }}"><i class="bi bi-download" aria-hidden="true"></i><span><b>Descargar plantilla Excel</b><small>Formato .xls compatible</small></span></a>
            <button class="productos-excel-upload" type="button" disabled title="La importación masiva estará disponible próximamente"><i class="bi bi-upload" aria-hidden="true"></i><span><b>Subir archivo Excel</b><small>Disponible próximamente</small></span></button>
        </div>
    </section>

    <footer class="productos-create-footer">
        <button class="productos-create-cancel" type="button" data-close-product-create>Cancelar</button>
        <button class="productos-create-secondary" type="submit" name="continue" value="1"><i class="bi bi-plus-circle" aria-hidden="true"></i> Guardar y registrar otro</button>
        <button class="productos-create-submit" type="submit"><i class="bi bi-save" aria-hidden="true"></i> Guardar producto</button>
    </footer>
</form>
