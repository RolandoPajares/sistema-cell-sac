/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/gestion/finanzas/comprobantes/index.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

/**
 * Ejecuta la operación initializeFacturacion utilizada por el módulo JavaScript.
 *
 * @param {*} root - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: initializeFacturacion concentra una tarea reutilizable del modulo.
export const initializeFacturacion = (root) => {
    // Condicional: evalua "!root || root.dataset.financeFacturacionReady === 'true'" para decidir si ejecuta el bloque siguiente.
    if (!root || root.dataset.financeFacturacionReady === 'true') {
        return;
    }

    root.dataset.financeFacturacionReady = 'true';
    bindFinanceFilterStatus(root, 'facturación');
    initializeInvoiceDemo(root);
};

/**
 * Ejecuta la operación initializeInvoiceDemo utilizada por el módulo JavaScript.
 *
 * @param {*} root - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: initializeInvoiceDemo concentra una tarea reutilizable del modulo.
const initializeInvoiceDemo = (root) => {
    const dialog = root.querySelector('[data-finance-dialog="crear"], [data-pending-modal="crear"]');
    const wizard = dialog?.querySelector('[data-invoice-wizard]');
    // Condicional: evalua "!dialog || !wizard" para decidir si ejecuta el bloque siguiente.
    if (!dialog || !wizard) {
        return;
    }

    const stages = [...wizard.querySelectorAll('[data-invoice-step]')];
    const indicators = [...wizard.querySelectorAll('[data-invoice-indicator]')];
    const previous = dialog.querySelector('[data-invoice-previous]');
    const next = dialog.querySelector('[data-invoice-next]');
    const confirm = dialog.querySelector('[data-invoice-confirm]');
    const error = wizard.querySelector('[data-invoice-error]');
    const feedback = wizard.querySelector('[data-invoice-feedback]');
    const review = wizard.querySelector('[data-invoice-review]');
    const acknowledgement = wizard.querySelector('[data-invoice-ack]');
    let currentStep = 0;

/**
 * Ejecuta la operación render utilizada por el módulo JavaScript.
 *
 * @param {*} step - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
    // Funcion: render concentra una tarea reutilizable del modulo.
    const render = (step) => {
        currentStep = step;
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        stages.forEach((stage) => {
            stage.hidden = Number(stage.dataset.invoiceStep) !== step;
        });
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        indicators.forEach((indicator) => {
            indicator.classList.toggle('is-current', Number(indicator.dataset.invoiceIndicator) === step);
        });
        previous.hidden = step === 0;
        next.hidden = step === stages.length - 1;
        confirm.hidden = step !== stages.length - 1;
        error.hidden = true;
        feedback.hidden = true;
        confirm.disabled = false;
    };

/**
 * Ejecuta la operación populateReview utilizada por el módulo JavaScript.
 *
 * @returns {*} Resultado de la operación.
 */
    // Funcion: populateReview concentra una tarea reutilizable del modulo.
    const populateReview = () => {
        review.replaceChildren();
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        wizard.querySelectorAll('[data-invoice-step="0"] input, [data-invoice-step="0"] select, [data-invoice-step="0"] textarea').forEach((field) => {
            const label = field.closest('label');
            const term = document.createElement('dt');
            const value = document.createElement('dd');
            const selected = field instanceof HTMLSelectElement
                ? field.selectedOptions[0]?.textContent.trim() ?? ''
                : field.value.trim();
            term.textContent = (label?.childNodes[0]?.textContent ?? 'Campo').replace('*', '').trim();
            value.textContent = selected || 'Sin dato';
            review.append(term, value);
        });
    };

    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    next.addEventListener('click', () => {
        const activeStage = stages[currentStep];
        const invalid = [...activeStage.querySelectorAll('input[required], select[required], textarea[required]')]
            .find((field) => !field.checkValidity());
        // Condicional: evalua "invalid" para decidir si ejecuta el bloque siguiente.
        if (invalid) {
            invalid.setAttribute('aria-invalid', 'true');
            error.textContent = 'Completa los campos obligatorios de esta etapa.';
            error.hidden = false;
            invalid.focus();
            return;
        }

        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        activeStage.querySelectorAll('[aria-invalid="true"]').forEach((field) => field.removeAttribute('aria-invalid'));
        // Condicional: evalua "currentStep === 0" para decidir si ejecuta el bloque siguiente.
        if (currentStep === 0) {
            populateReview();
        }
        render(Math.min(currentStep + 1, stages.length - 1));
    });
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    previous.addEventListener('click', () => render(Math.max(0, currentStep - 1)));
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    confirm.addEventListener('click', () => {
        // Condicional: evalua "!acknowledgement.checked" para decidir si ejecuta el bloque siguiente.
        if (!acknowledgement.checked) {
            error.textContent = 'Confirma que revisaste los datos ilustrativos.';
            error.hidden = false;
            return;
        }

        feedback.textContent = 'Integración tributaria pendiente: no se emitió un comprobante ni se generó numeración o XML; tampoco se contactó con SUNAT.';
        feedback.hidden = false;
        confirm.disabled = true;
    });
    // Evento: escucha el evento input y ejecuta la funcion asociada cuando ocurre.
    wizard.addEventListener('input', (event) => event.target.removeAttribute('aria-invalid'));
    // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
    wizard.addEventListener('change', (event) => event.target.removeAttribute('aria-invalid'));
    // Evento: escucha el evento close y ejecuta la funcion asociada cuando ocurre.
    dialog.addEventListener('close', () => {
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        wizard.querySelectorAll('input, select, textarea').forEach((field) => {
            field.removeAttribute('aria-invalid');
            // Condicional: evalua "field instanceof HTMLInputElement || field instanceof HTMLTextAreaElement" para decidir si ejecuta el bloque siguiente.
            if (field instanceof HTMLInputElement || field instanceof HTMLTextAreaElement) {
                field.value = '';
            // Caso alternativo: atiende la situacion en la que ninguna condicion anterior resulto verdadera.
            } else {
                field.selectedIndex = 0;
            }
        });
        acknowledgement.checked = false;
        review.replaceChildren();
        render(0);
    });

    render(0);
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
