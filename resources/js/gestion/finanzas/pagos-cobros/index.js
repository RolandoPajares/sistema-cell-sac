/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/gestion/finanzas/pagos-cobros/index.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

/**
 * Ejecuta la operación initializePagos utilizada por el módulo JavaScript.
 *
 * @param {*} root - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: initializePagos concentra una tarea reutilizable del modulo.
export const initializePagos = (root) => {
    // Condicional: evalua "!root || root.dataset.financePagosReady === 'true'" para decidir si ejecuta el bloque siguiente.
    if (!root || root.dataset.financePagosReady === 'true') {
        return;
    }

    root.dataset.financePagosReady = 'true';
    bindFinanceFilterStatus(root, 'pagos');
    initializePaymentDemo(root);
};

/**
 * Ejecuta la operación initializePaymentDemo utilizada por el módulo JavaScript.
 *
 * @param {*} root - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: initializePaymentDemo concentra una tarea reutilizable del modulo.
const initializePaymentDemo = (root) => {
    const form = root.querySelector('[data-finance-payment-form]');
    const operation = form?.querySelector('[data-finance-payment-operation]');
    const partyType = form?.querySelector('[data-finance-payment-party-type]');
    const title = form?.querySelector('[data-finance-payment-title]');
    const confirm = form?.querySelector('[data-finance-payment-confirm]');
    const dialog = root.querySelector('[data-finance-dialog="crear"], [data-pending-modal="crear"]');
    const error = form?.querySelector('[data-finance-payment-error]');
    const feedback = form?.querySelector('[data-finance-payment-feedback]');

    // Condicional: evalua "!form || !operation || !partyType || !confirm || !dialog" para decidir si ejecuta el bloque siguiente.
    if (!form || !operation || !partyType || !confirm || !dialog) {
        return;
    }

/**
 * Ejecuta la operación syncOperation utilizada por el módulo JavaScript.
 *
 * @returns {*} Resultado de la operación.
 */
    // Funcion: syncOperation concentra una tarea reutilizable del modulo.
    const syncOperation = () => {
        const isPayment = operation.value === 'pago';
        partyType.value = isPayment ? 'proveedor' : 'cliente';
        // Condicional: evalua "title" para decidir si ejecuta el bloque siguiente.
        if (title) {
            title.textContent = isPayment ? 'Registrar pago a proveedor' : 'Registrar cobro de cliente';
        }
        confirm.textContent = isPayment ? 'Confirmar pago (demostración)' : 'Confirmar cobro (demostración)';
    };

    const requestedOperation = new URLSearchParams(window.location.search).get('financeAction');
    // Condicional: evalua "requestedOperation === 'pago' || requestedOperation === 'cobro'" para decidir si ejecuta el bloque siguiente.
    if (requestedOperation === 'pago' || requestedOperation === 'cobro') {
        operation.value = requestedOperation;
        syncOperation();
        dialog.showModal();
    }

    // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
    operation.addEventListener('change', syncOperation);
    // Evento: escucha el evento close y ejecuta la funcion asociada cuando ocurre.
    dialog.addEventListener('close', () => {
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        form.querySelectorAll('input, select, textarea').forEach((field) => {
            field.removeAttribute('aria-invalid');
            // Condicional: evalua "field.matches('input, textarea')" para decidir si ejecuta el bloque siguiente.
            if (field.matches('input, textarea')) {
                field.value = '';
            // Caso alternativo: atiende la situacion en la que ninguna condicion anterior resulto verdadera.
            } else {
                field.selectedIndex = 0;
            }
        });
        // Condicional: evalua "error" para decidir si ejecuta el bloque siguiente.
        if (error) error.hidden = true;
        // Condicional: evalua "feedback" para decidir si ejecuta el bloque siguiente.
        if (feedback) feedback.hidden = true;
        confirm.disabled = false;
        syncOperation();
    });
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    confirm.addEventListener('click', () => {
        const fields = [...form.querySelectorAll('input[required], select[required], textarea[required]')];
        // Funcion: invalid concentra una tarea reutilizable del modulo.
        const invalid = fields.find((field) => !field.checkValidity());
        // Condicional: evalua "invalid" para decidir si ejecuta el bloque siguiente.
        if (invalid) {
            invalid.setAttribute('aria-invalid', 'true');
            // Condicional: evalua "error" para decidir si ejecuta el bloque siguiente.
            if (error) {
                error.textContent = 'Completa los campos obligatorios y revisa el monto.';
                error.hidden = false;
            }
            invalid.focus();
            return;
        }

        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        fields.forEach((field) => field.removeAttribute('aria-invalid'));
        // Condicional: evalua "error" para decidir si ejecuta el bloque siguiente.
        if (error) error.hidden = true;
        // Condicional: evalua "feedback" para decidir si ejecuta el bloque siguiente.
        if (feedback) {
            feedback.textContent = operation.value === 'pago'
                ? 'Integración pendiente: no se registró el pago ni se cambió el saldo del proveedor.'
                : 'Integración pendiente: no se registró el cobro ni se confirmó dinero recibido.';
            feedback.hidden = false;
        }
        confirm.disabled = true;
    });

    syncOperation();
};

/**
 * Ejecuta la operación bindFinanceFilterStatus utilizada por el módulo JavaScript.
 *
 * @param {*} root - Parámetro recibido por la función.
 *
 * @param {*} moduleName - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: bindFinanceFilterStatus concentra una tarea reutilizable del modulo.
const bindFinanceFilterStatus = (root, moduleName) => {
    const status = root.querySelector('[data-finance-filter-status]');

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    root.querySelectorAll('.admin-finance-filters [data-pending-filter]').forEach((filter) => {
        // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
        filter.addEventListener('change', () => {
            // Condicional: evalua "status" para decidir si ejecuta el bloque siguiente.
            if (status) {
                status.textContent = `Filtro de ${moduleName}: ${filter.value}. La consulta se aplicará cuando exista integración financiera.`;
            }
        });
    });
};
