/**
 * Ejecuta la operación initializeFacturacion utilizada por el módulo JavaScript.
 *
 * @param {*} root - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
export const initializeFacturacion = (root) => {
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
const initializeInvoiceDemo = (root) => {
    const dialog = root.querySelector('[data-pending-modal="crear"]');
    const wizard = dialog?.querySelector('[data-invoice-wizard]');
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
    const render = (step) => {
        currentStep = step;
        stages.forEach((stage) => {
            stage.hidden = Number(stage.dataset.invoiceStep) !== step;
        });
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
    const populateReview = () => {
        review.replaceChildren();
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

    next.addEventListener('click', () => {
        const activeStage = stages[currentStep];
        const invalid = [...activeStage.querySelectorAll('input[required], select[required], textarea[required]')]
            .find((field) => !field.checkValidity());
        if (invalid) {
            invalid.setAttribute('aria-invalid', 'true');
            error.textContent = 'Completa los campos obligatorios de esta etapa.';
            error.hidden = false;
            invalid.focus();
            return;
        }

        activeStage.querySelectorAll('[aria-invalid="true"]').forEach((field) => field.removeAttribute('aria-invalid'));
        if (currentStep === 0) {
            populateReview();
        }
        render(Math.min(currentStep + 1, stages.length - 1));
    });
    previous.addEventListener('click', () => render(Math.max(0, currentStep - 1)));
    confirm.addEventListener('click', () => {
        if (!acknowledgement.checked) {
            error.textContent = 'Confirma que revisaste los datos ilustrativos.';
            error.hidden = false;
            return;
        }

        feedback.textContent = 'Integración tributaria pendiente: no se emitió un comprobante ni se generó numeración o XML; tampoco se contactó con SUNAT.';
        feedback.hidden = false;
        confirm.disabled = true;
    });
    wizard.addEventListener('input', (event) => event.target.removeAttribute('aria-invalid'));
    wizard.addEventListener('change', (event) => event.target.removeAttribute('aria-invalid'));
    dialog.addEventListener('close', () => {
        wizard.querySelectorAll('input, select, textarea').forEach((field) => {
            field.removeAttribute('aria-invalid');
            if (field instanceof HTMLInputElement || field instanceof HTMLTextAreaElement) {
                field.value = '';
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
const bindFinanceFilterStatus = (root, moduleName) => {
    const status = root.querySelector('[data-finance-filter-status]');

    root.querySelectorAll('.admin-finance-filters [data-pending-filter]').forEach((filter) => {
        filter.addEventListener('change', () => {
            if (status) {
                status.textContent = `Filtro de ${moduleName}: ${filter.value}. La consulta se aplicará cuando exista integración financiera.`;
            }
        });
    });
};