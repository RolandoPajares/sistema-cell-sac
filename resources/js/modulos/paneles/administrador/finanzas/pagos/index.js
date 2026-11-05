export const initializePagos = (root) => {
    if (!root || root.dataset.financePagosReady === 'true') {
        return;
    }

    root.dataset.financePagosReady = 'true';
    bindFinanceFilterStatus(root, 'pagos');
    initializePaymentDemo(root);
};

const initializePaymentDemo = (root) => {
    const form = root.querySelector('[data-finance-payment-form]');
    const operation = form?.querySelector('[data-finance-payment-operation]');
    const partyType = form?.querySelector('[data-finance-payment-party-type]');
    const title = form?.querySelector('[data-finance-payment-title]');
    const confirm = form?.querySelector('[data-finance-payment-confirm]');
    const dialog = root.querySelector('[data-pending-modal="crear"]');
    const error = form?.querySelector('[data-finance-payment-error]');
    const feedback = form?.querySelector('[data-finance-payment-feedback]');

    if (!form || !operation || !partyType || !confirm || !dialog) {
        return;
    }

    const syncOperation = () => {
        const isPayment = operation.value === 'pago';
        partyType.value = isPayment ? 'proveedor' : 'cliente';
        if (title) {
            title.textContent = isPayment ? 'Registrar pago a proveedor' : 'Registrar cobro de cliente';
        }
        confirm.textContent = isPayment ? 'Confirmar pago (demostración)' : 'Confirmar cobro (demostración)';
    };

    const requestedOperation = new URLSearchParams(window.location.search).get('financeAction');
    if (requestedOperation === 'pago' || requestedOperation === 'cobro') {
        operation.value = requestedOperation;
        syncOperation();
        dialog.showModal();
    }

    operation.addEventListener('change', syncOperation);
    dialog.addEventListener('close', () => {
        form.querySelectorAll('input, select, textarea').forEach((field) => {
            field.removeAttribute('aria-invalid');
            if (field.matches('input, textarea')) {
                field.value = '';
            } else {
                field.selectedIndex = 0;
            }
        });
        if (error) error.hidden = true;
        if (feedback) feedback.hidden = true;
        confirm.disabled = false;
        syncOperation();
    });
    confirm.addEventListener('click', () => {
        const fields = [...form.querySelectorAll('input[required], select[required], textarea[required]')];
        const invalid = fields.find((field) => !field.checkValidity());
        if (invalid) {
            invalid.setAttribute('aria-invalid', 'true');
            if (error) {
                error.textContent = 'Completa los campos obligatorios y revisa el monto.';
                error.hidden = false;
            }
            invalid.focus();
            return;
        }

        fields.forEach((field) => field.removeAttribute('aria-invalid'));
        if (error) error.hidden = true;
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
