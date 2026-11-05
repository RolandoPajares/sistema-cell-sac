<div class="gestion-pendiente-modal-shell tc-finance-demo tc-invoice-demo" data-finance-invoice-form>
    <header class="gestion-pendiente-modal-header">
        <div><span class="gestion-pendiente-modal-icon"><i class="bi bi-receipt" aria-hidden="true"></i></span>
            <div><span class="gestion-pendiente-eyebrow">Finanzas · Facturación</span>
                <h2 id="facturacion-crear-title">Revisar comprobante electrónico</h2>
                <p>Flujo visual para factura o boleta; no genera comprobantes fiscales.</p>
            </div>
        </div><button type="button" class="gestion-pendiente-close" data-pending-close aria-label="Cerrar ventana"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </header>
    <div class="gestion-pendiente-modal-body tc-finance-demo__body" data-invoice-wizard>
        <div class="gestion-pendiente-modal-notice"><i class="bi bi-database-exclamation" aria-hidden="true"></i>
            <div><strong>Integración tributaria pendiente</strong>
                <p>No se asigna serie o correlativo real, no se crea XML y no se envía información a SUNAT.</p>
            </div>
        </div>
        <nav class="tc-finance-steps" aria-label="Etapas del comprobante"><span data-invoice-indicator="0">1. Datos</span><span data-invoice-indicator="1">2. Revisión</span><span data-invoice-indicator="2">3. Confirmación</span></nav>
        <section data-invoice-step="0">
            <div class="tc-finance-form-grid"><label class="tc-finance-field">Tipo de comprobante *<select required data-invoice-type>
                        <option value="">Selecciona tipo</option>
                        <option>Factura</option>
                        <option>Boleta</option>
                    </select></label><label class="tc-finance-field">Referencia de venta *<input type="text" required maxlength="80" placeholder="Código interno de venta"></label><label class="tc-finance-field">Cliente / razón social *<input type="text" required maxlength="150" placeholder="Nombre o razón social"></label><label class="tc-finance-field">Tipo de identificación *<select required>
                        <option value="">Selecciona tipo</option>
                        <option>DNI</option>
                        <option>RUC</option>
                        <option>CE</option>
                    </select></label><label class="tc-finance-field">Número de identificación *<input type="text" required maxlength="20" placeholder="Documento del cliente"></label><label class="tc-finance-field">Fecha de emisión referencial *<input type="date" required></label><label class="tc-finance-field">Moneda *<select required>
                        <option>PEN</option>
                        <option>USD</option>
                    </select></label><label class="tc-finance-field">Importe de referencia *<input type="number" min="0.01" step="0.01" required placeholder="0.00"></label><label class="tc-finance-field tc-finance-field--wide">Observaciones<textarea rows="2" maxlength="300" placeholder="Notas internas"></textarea></label></div>
            <p class="tc-finance-warning">Los campos no ejecutan validación tributaria ni determinan IGV. Si la venta ya tiene un comprobante emitido, consulta el estado antes de iniciar otra emisión; aquí no se verificó.</p>
        </section>
        <section data-invoice-step="1" hidden>
            <h3>Revisión de datos</h3>
            <dl class="tc-finance-review-list" data-invoice-review></dl>
            <p>La vista previa es interna. No representa un comprobante emitido ni válido ante SUNAT.</p>
        </section>
        <section data-invoice-step="2" hidden>
            <h3>Confirmación visual</h3><label class="tc-finance-check"><input type="checkbox" data-invoice-ack> Confirmo que revisé los datos de ejemplo y entiendo que la emisión está pendiente.</label>
            <p class="tc-finance-demo__feedback" data-invoice-feedback role="status" aria-live="polite" hidden></p>
        </section>
        <p class="tc-finance-demo__error" data-invoice-error role="alert" hidden></p>
    </div>
    <footer class="gestion-pendiente-modal-footer"><button type="button" class="gestion-pendiente-secondary" data-pending-close>Cancelar</button><button type="button" class="gestion-pendiente-secondary" data-invoice-previous hidden>Anterior</button><button type="button" class="gestion-pendiente-primary" data-invoice-next>Siguiente</button><button type="button" class="gestion-pendiente-primary" data-invoice-confirm hidden>Confirmar emisión (demostración)</button></footer>
</div>