<dialog class="tc-em__dialog" data-em-dialog data-em-prices-dialog aria-labelledby="tc-em-price-dialog-title">
    <div class="tc-em__dialog-card">
        <header>
            <span class="tc-em__dialog-icon"><i class="bi bi-tags"></i></span>
            <div><h2 id="tc-em-price-dialog-title" data-em-dialog-title>Configuración de precios</h2><p>Registra precios mayoristas vigentes para el producto seleccionado.</p></div>
            <button type="button" class="tc-em__close" data-em-close aria-label="Cerrar"><i class="bi bi-x-lg"></i></button>
        </header>
        <form method="POST" action="<?php echo e(route('management.products.wholesale_prices.update')); ?>" enctype="multipart/form-data" data-em-wholesale-price-form data-em-edit-action="<?php echo e(route('management.products.wholesale_prices.update')); ?>" data-em-import-action="<?php echo e(route('management.products.wholesale_prices.import')); ?>">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <input type="hidden" name="producto_id" value="" data-em-edit-product-id>
        <div class="tc-em__dialog-body">
            <div class="tc-em__modal-notice" data-em-price-notice><i class="bi bi-info-circle"></i> El precio mayorista se registra en MariaDB para la empresa activa cuando la estructura está disponible.</div>
            <div class="tc-em__form-grid" data-em-dialog-fields="configuracion">
                <label>Moneda principal<select><option>PEN · Sol peruano (S/)</option></select></label>
                <label>Canal predeterminado<select><option>Minorista</option><option>Mayorista</option></select></label>
                <label>Redondeo de precios<select><option>Dos decimales</option><option>Precio entero</option></select></label>
                <label>Precio mayorista mínimo<input type="number" min="0" step="0.01" placeholder="0,00"></label>
            </div>
            <div class="tc-em__bulk-assistant" data-em-dialog-fields="masiva" data-em-price-bulk hidden>
                <nav class="tc-em__bulk-steps" aria-label="Etapas de actualización masiva">
                    <span data-em-bulk-indicator="0">1. Plantilla</span>
                    <span data-em-bulk-indicator="1">2. Archivo</span>
                    <span data-em-bulk-indicator="2">3. Vista previa</span>
                    <span data-em-bulk-indicator="3">4. Revisión</span>
                    <span data-em-bulk-indicator="4">5. Confirmación</span>
                </nav>
                <section class="tc-em__bulk-panel" data-em-bulk-step="0">
                    <h3>Plantilla CSV</h3>
                    <p>Descarga una plantilla CSV UTF-8 compatible con Excel. Todas las filas se validan antes de guardarse en una única transacción.</p>
                    <button type="button" class="tc-em__button" data-em-template-download><i class="bi bi-download" aria-hidden="true"></i> Descargar plantilla CSV</button>
                </section>
                <section class="tc-em__bulk-panel" data-em-bulk-step="1" hidden>
                    <h3>Seleccionar archivo</h3>
                    <label>Archivo CSV<input type="file" name="archivo" accept=".csv,text/csv" required data-em-bulk-file></label>
                    <small data-em-bulk-file-status>Se procesará en el servidor dentro de una transacción.</small>
                </section>
                <section class="tc-em__bulk-panel" data-em-bulk-step="2" hidden>
                    <h3>Vista previa ilustrativa</h3>
                    <p>Esta fila es un ejemplo fijo; no se obtiene del archivo seleccionado ni del catálogo.</p>
                    <div class="tc-em__table-wrap"><table><thead><tr><th>SKU</th><th>Canal</th><th>Precio</th><th>Moneda</th><th>Vigencia desde</th><th>Vigencia hasta</th><th>Motivo</th></tr></thead><tbody><tr><td colspan="7">Selecciona un archivo para consultar sus datos reales.</td></tr></tbody></table></div>
                </section>
                <section class="tc-em__bulk-panel" data-em-bulk-step="3" hidden>
                    <h3>Validaciones previstas</h3>
                    <ul><li>SKU vacío o no reconocido.</li><li>Canal inválido o precio cero/negativo.</li><li>Vigencia final anterior al inicio.</li><li>Posible SKU repetido en el archivo.</li></ul>
                    <p>Son validaciones previstas únicamente para ilustrar el flujo. No se inspeccionó el archivo ni se buscaron SKU duplicados. La moneda por fila y la vigencia todavía requieren mapeo de negocio y persistencia.</p>
                </section>
                <section class="tc-em__bulk-panel" data-em-bulk-step="4" hidden>
                    <h3>Confirmar actualización</h3>
                    <p>No se importarán filas ni se actualizarán precios del catálogo o checkout.</p>
                    <label class="tc-em__bulk-check"><input type="checkbox" name="confirmar" value="1" data-em-bulk-ack required> Confirmo que el CSV contiene precios mayoristas de la empresa autorizada.</label>
                </section>
                <p class="tc-em__bulk-error" data-em-bulk-error role="alert" hidden></p>
                <p class="tc-em__bulk-feedback" data-em-bulk-feedback role="status" aria-live="polite" hidden></p>
            </div>
            <div class="tc-em__form-grid" data-em-dialog-fields="editar" hidden>
                <label class="tc-em__form-full">Producto<input type="text" data-em-dialog-product readonly></label>
                <label>Canal<select required data-em-edit-channel disabled><option value="mayorista">Mayorista</option></select></label>
                <label>Nuevo precio (S/)<input type="number" name="precio" min="0.01" step="0.01" required data-em-edit-price placeholder="0,00"></label>
                <label>Vigente desde<input type="date" name="vigente_desde" required data-em-edit-start></label>
                <label>Vigente hasta (opcional)<input type="date" name="vigente_hasta" data-em-edit-end></label>
                <label class="tc-em__form-full">Motivo<textarea name="motivo" required maxlength="300" data-em-edit-reason placeholder="Describe el cambio de precio…"></textarea></label>
                <p class="tc-em__bulk-error tc-em__form-full" data-em-edit-error role="alert" hidden></p>
            </div>
            <div class="tc-em__form-grid" data-em-dialog-fields="detalle" hidden>
                <label class="tc-em__form-full">Producto<input type="text" data-em-dialog-product readonly></label>
                <label>Precio registrado<input type="text" data-em-detail-minorista readonly></label>
                <label>Precio mayorista<input type="text" data-em-detail-mayorista readonly></label>
                <label>Precio base<input type="text" data-em-detail-base readonly></label>
                <label>Stock disponible<input type="text" data-em-detail-stock readonly></label>
            </div>
        </div>
        <footer>
            <button type="button" class="tc-em__button" data-em-close>Cancelar</button>
            <button type="button" class="tc-em__button" data-em-bulk-previous hidden>Anterior</button>
            <button type="button" class="tc-em__button" data-em-bulk-next hidden>Siguiente</button>
            <button type="submit" class="tc-em__button tc-em__button--primary" data-em-bulk-confirm hidden disabled>Importar precios</button>
            <button type="submit" class="tc-em__button tc-em__button--primary" data-em-price-submit data-em-permission="<?php echo e($puedeActualizarPrecioMayorista ? 'true' : 'false'); ?>" <?php if (! ($preciosMayoristasDisponibles && $puedeActualizarPrecioMayorista)) {
                echo 'disabled';
            } ?>>Guardar precio mayorista</button>
        </footer>
        </form>
    </div>
</dialog>
