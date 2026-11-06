/**
 * Ejecuta la operación initializeReembolsos utilizada por el módulo JavaScript.
 *
 * @param {*} root - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
export const initializeReembolsos = (root) => {
    if (!root || root.dataset.financeReembolsosReady === 'true') {
        return;
    }

    root.dataset.financeReembolsosReady = 'true';
    bindFinanceFilterStatus(root, 'reembolsos');
    initializeRefundDemo(root);
};

/**
 * Ejecuta la operación initializeRefundDemo utilizada por el módulo JavaScript.
 *
 * @param {*} root - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
const initializeRefundDemo = (root) => {
    const form = root.querySelector('[data-finance-refund-form]');
    const dialog = root.querySelector('[data-pending-modal="crear"]');
    const confirm = form?.querySelector('[data-finance-refund-confirm]');
    const error = form?.querySelector('[data-finance-refund-error]');
    const feedback = form?.querySelector('[data-finance-refund-feedback]');
    const evidence = form?.querySelector('[data-finance-refund-evidence]');
    const evidenceName = form?.querySelector('[data-finance-refund-evidence-name]');

    if (!form || !dialog || !confirm) {
        return;
    }

    if (new URLSearchParams(window.location.search).get('financeAction') === 'reembolso') {
        dialog.showModal();
    }

    evidence?.addEventListener('change', () => {
        const file = evidence.files?.[0] ?? null;
        if (evidenceName) {
            evidenceName.textContent = file
                ? `Seleccionado: ${file.name}. No se leerá ni transmitirá.`
                : 'No se ha seleccionado evidencia. El archivo no se leerá ni transmitirá.';
        }
    });

    dialog.addEventListener('close', () => {
        form.querySelectorAll('input, select, textarea').forEach((field) => {
            field.removeAttribute('aria-invalid');
            if (field.matches('input[type="file"]')) {
                field.value = '';
            } else if (field.matches('input, textarea')) {
                field.value = '';
            } else {
                field.selectedIndex = 0;
            }
        });
        if (error) error.hidden = true;
        if (feedback) feedback.hidden = true;
        if (evidenceName) {
            evidenceName.textContent = 'No se ha seleccionado evidencia. El archivo no se leerá ni transmitirá.';
        }
        confirm.disabled = false;
    });

    confirm.addEventListener('click', () => {
        const fields = [...form.querySelectorAll('input[required], select[required], textarea[required]')];
        const invalid = fields.find((field) => !field.checkValidity());
        if (invalid) {
            invalid.setAttribute('aria-invalid', 'true');
            if (error) {
                error.textContent = 'Completa los campos obligatorios y revisa el importe.';
                error.hidden = false;
            }
            invalid.focus();
            return;
        }

        fields.forEach((field) => field.removeAttribute('aria-invalid'));
        if (error) error.hidden = true;
        if (feedback) {
            feedback.textContent = 'Integración pendiente: la solicitud no se guardó, no se aprobó y no se realizó un desembolso.';
            feedback.hidden = false;
        }
        confirm.disabled = true;
    });
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