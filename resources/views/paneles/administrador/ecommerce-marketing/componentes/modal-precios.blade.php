<dialog class="tc-em__dialog" data-em-dialog data-em-prices-dialog aria-labelledby="tc-em-price-dialog-title">
    <div class="tc-em__dialog-card">
        <header>
            <span class="tc-em__dialog-icon"><i class="bi bi-tags"></i></span>
            <div><h2 id="tc-em-price-dialog-title" data-em-dialog-title>Configuración de precios</h2><p>Vista de diseño. La gestión real permanece en Productos.</p></div>
            <button type="button" class="tc-em__close" data-em-close aria-label="Cerrar"><i class="bi bi-x-lg"></i></button>
        </header>
        <div class="tc-em__dialog-body">
            <div class="tc-em__modal-notice" data-em-price-notice><i class="bi bi-info-circle"></i> Los precios de esta pantalla son ejemplos; esta vista no modifica precios ni el checkout.</div>
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
                    <p>Descarga una plantilla CSV UTF-8 compatible con Excel. Sus columnas son ilustrativas; el modelo actual no define precios separados por canal ni una importación de vigencias.</p>
                    <button type="button" class="tc-em__button" data-em-template-download><i class="bi bi-download" aria-hidden="true"></i> Descargar plantilla CSV</button>
                </section>
                <section class="tc-em__bulk-panel" data-em-bulk-step="1" hidden>
                    <h3>Seleccionar archivo</h3>
                    <label>Archivo CSV o XLSX<input type="file" accept=".csv,.xlsx,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" data-em-bulk-file></label>
                    <small data-em-bulk-file-status>No se leerá, transmitirá ni almacenará el archivo.</small>
                </section>
                <section class="tc-em__bulk-panel" data-em-bulk-step="2" hidden>
                    <h3>Vista previa ilustrativa</h3>
                    <p>Esta fila es un ejemplo fijo; no se obtiene del archivo seleccionado ni del catálogo.</p>
                    <div class="tc-em__table-wrap"><table><thead><tr><th>SKU</th><th>Canal</th><th>Precio</th><th>Moneda</th><th>Vigencia desde</th><th>Vigencia hasta</th><th>Motivo</th></tr></thead><tbody><tr><td>SKU-DEMO-001</td><td>minorista</td><td>S/ 100,00</td><td>PEN</td><td>2026-01-01</td><td>2026-12-31</td><td>Ejemplo ilustrativo</td></tr></tbody></table></div>
                </section>
                <section class="tc-em__bulk-panel" data-em-bulk-step="3" hidden>
                    <h3>Validaciones previstas</h3>
                    <ul><li>SKU vacío o no reconocido.</li><li>Canal inválido o precio cero/negativo.</li><li>Vigencia final anterior al inicio.</li><li>Posible SKU repetido en el archivo.</li></ul>
                    <p>Son validaciones previstas únicamente para ilustrar el flujo. No se inspeccionó el archivo ni se buscaron SKU duplicados. La moneda por fila y la vigencia todavía requieren mapeo de negocio y persistencia.</p>
                </section>
                <section class="tc-em__bulk-panel" data-em-bulk-step="4" hidden>
                    <h3>Confirmar actualización</h3>
                    <p>No se importarán filas ni se actualizarán precios del catálogo o checkout.</p>
                    <label class="tc-em__bulk-check"><input type="checkbox" data-em-bulk-ack> Entiendo que la integración de importación está pendiente.</label>
                </section>
                <p class="tc-em__bulk-error" data-em-bulk-error role="alert" hidden></p>
                <p class="tc-em__bulk-feedback" data-em-bulk-feedback role="status" aria-live="polite" hidden></p>
            </div>
            <div class="tc-em__form-grid" data-em-dialog-fields="editar" hidden>
                <label class="tc-em__form-full">Producto<input type="text" data-em-dialog-product readonly></label>
                <label>Canal<select required data-em-edit-channel><option value="minorista">Minorista</option><option value="mayorista">Mayorista</option></select></label>
                <label>Nuevo precio (S/)<input type="number" min="0.01" step="0.01" required data-em-edit-price placeholder="0,00"></label>
                <label>Vigente desde<input type="date" required data-em-edit-start></label>
                <label>Vigente hasta (opcional)<input type="date" data-em-edit-end></label>
                <label class="tc-em__form-full">Motivo<textarea required maxlength="300" data-em-edit-reason placeholder="Describe el cambio de precio…"></textarea></label>
                <p class="tc-em__bulk-error tc-em__form-full" data-em-edit-error role="alert" hidden></p>
            </div>
            <div class="tc-em__form-grid" data-em-dialog-fields="detalle" hidden>
                <label class="tc-em__form-full">Producto de ejemplo<input type="text" data-em-dialog-product readonly></label>
                <label>Precio minorista (ejemplo)<input type="text" data-em-detail-minorista readonly></label>
                <label>Precio mayorista (ejemplo)<input type="text" data-em-detail-mayorista readonly></label>
                <label>Precio base (ejemplo)<input type="text" data-em-detail-base readonly></label>
                <label>Stock de ejemplo<input type="text" data-em-detail-stock readonly></label>
            </div>
        </div>
        <footer>
            <button type="button" class="tc-em__button" data-em-close>Cancelar</button>
            <button type="button" class="tc-em__button" data-em-bulk-previous hidden>Anterior</button>
            <button type="button" class="tc-em__button" data-em-bulk-next hidden>Siguiente</button>
            <button type="button" class="tc-em__button tc-em__button--primary" data-em-bulk-confirm hidden>Confirmar (demostración)</button>
            <button type="button" class="tc-em__button tc-em__button--primary" data-em-demo-save>Guardar cuando se integre</button>
        </footer>
    </div>
</dialog>
