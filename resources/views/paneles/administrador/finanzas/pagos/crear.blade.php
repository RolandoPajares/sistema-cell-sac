<div class="gestion-pendiente-modal-shell tc-finance-demo" data-finance-payment-form>
    <header class="gestion-pendiente-modal-header">
        <div><span class="gestion-pendiente-modal-icon"><i class="bi bi-credit-card" aria-hidden="true"></i></span><div><span class="gestion-pendiente-eyebrow">Finanzas · Pago y cobro</span><h2 id="pagos-crear-title" data-finance-payment-title>Registrar pago o cobro</h2><p>Formulario visual reutilizado por las dos acciones del módulo.</p></div></div>
        <button type="button" class="gestion-pendiente-close" data-pending-close aria-label="Cerrar ventana"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </header>
    <div class="gestion-pendiente-modal-body tc-finance-demo__body">
        <div class="gestion-pendiente-modal-notice"><i class="bi bi-database-exclamation" aria-hidden="true"></i><div><strong>Integración financiera pendiente</strong><p>No se registrará un movimiento ni se confirmará un pago o cobro.</p></div></div>
        <div class="tc-finance-form-grid">
            <label class="tc-finance-field">Tipo de operación *<select required data-finance-payment-operation><option value="pago">Pago a proveedor</option><option value="cobro">Cobro recibido de cliente</option></select></label>
            <label class="tc-finance-field">Tipo de tercero *<select required data-finance-payment-party-type><option value="proveedor">Proveedor</option><option value="cliente">Cliente</option></select></label>
            <label class="tc-finance-field">Cliente / proveedor *<input type="text" required maxlength="150" placeholder="Nombre o razón social"></label>
            <label class="tc-finance-field">Documento relacionado *<input type="text" required maxlength="80" placeholder="Factura, boleta u obligación"></label>
            <label class="tc-finance-field">Fecha *<input type="date" required></label>
            <label class="tc-finance-field">Método *<select required><option value="">Selecciona método</option><option>Transferencia</option><option>Tarjeta</option><option>Efectivo</option><option>QR</option></select></label>
            <label class="tc-finance-field">Monto *<input type="number" min="0.01" step="0.01" required placeholder="0.00"></label>
            <label class="tc-finance-field">Moneda *<select required><option>PEN</option><option>USD</option></select></label>
            <label class="tc-finance-field">Referencia de operación<input type="text" maxlength="100" placeholder="Código bancario o voucher"></label>
            <label class="tc-finance-field tc-finance-field--wide">Observaciones<textarea rows="2" maxlength="300" placeholder="Detalle adicional"></textarea></label>
        </div>
        <div class="tc-finance-warning-list" role="note"><strong>Advertencias visuales; no son controles efectivos:</strong><ul><li>Podría existir un pago duplicado.</li><li>El monto podría superar el saldo pendiente.</li><li>El documento podría no existir o no corresponder al tercero.</li></ul><span>No se consultaron documentos, terceros ni saldos reales.</span></div>
        <p class="tc-finance-demo__error" data-finance-payment-error role="alert" hidden></p>
        <p class="tc-finance-demo__feedback" data-finance-payment-feedback role="status" aria-live="polite" hidden></p>
    </div>
    <footer class="gestion-pendiente-modal-footer"><button type="button" class="gestion-pendiente-secondary" data-pending-close>Cancelar</button><button type="button" class="gestion-pendiente-primary" data-finance-payment-confirm><i class="bi bi-check2" aria-hidden="true"></i> Confirmar (demostración)</button></footer>
</div>
