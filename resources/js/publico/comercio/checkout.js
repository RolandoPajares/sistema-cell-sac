/**
 * Asistente dinámico de cuatro pasos para finalizar el pedido.
 */

(() => {
    const form = document.querySelector('[data-checkout-wizard]');

    if (!form) {
        return;
    }

    const steps = [...form.querySelectorAll('[data-checkout-step]')];
    const actions = [...form.querySelectorAll('[data-checkout-actions]')];
    const progressItems = [...document.querySelectorAll('[data-checkout-progress] > li')];
    const summary = form.querySelector('[data-checkout-summary]');
    const stepNames = ['identification', 'delivery', 'payment', 'confirmation'];
    let currentStep = 0;

    const controlValue = (name) => form.elements.namedItem(name)?.value?.trim() || '';

    const addSummaryRow = (label, value) => {
        if (!summary || !value) {
            return;
        }

        const row = document.createElement('div');
        const title = document.createElement('span');
        const content = document.createElement('strong');
        title.textContent = label;
        content.textContent = value;
        row.append(title, content);
        summary.append(row);
    };

    const updateSummary = () => {
        if (!summary) {
            return;
        }

        summary.replaceChildren();
        addSummaryRow('Cliente', controlValue('nombre') || document.querySelector('.checkout-identity-summary strong')?.textContent?.trim() || 'Cliente registrado');
        addSummaryRow('Correo', controlValue('correo') || document.querySelector('.checkout-identity-summary span')?.textContent?.trim() || 'Cuenta activa');
        addSummaryRow('Entrega', controlValue('direccion'));
        addSummaryRow('Referencia', controlValue('referencia'));
        addSummaryRow('Medio de pago', form.elements.namedItem('medio_pago_id')?.selectedOptions?.[0]?.textContent?.trim() || 'Pendiente de coordinación');
        addSummaryRow('Total', document.querySelector('.cart-total strong')?.textContent?.trim() || '');
    };

    const validateStep = (step) => {
        const controls = [...step.querySelectorAll('input, select, textarea')];
        const invalid = controls.find((control) => !control.checkValidity());

        if (invalid) {
            invalid.reportValidity();
            return false;
        }

        return true;
    };

    const showStep = (index) => {
        currentStep = Math.min(Math.max(index, 0), steps.length - 1);
        const currentName = stepNames[currentStep];

        steps.forEach((step) => {
            step.hidden = step.dataset.checkoutStep !== currentName;
        });
        actions.forEach((action) => {
            action.hidden = action.dataset.checkoutActions !== currentName;
        });
        progressItems.forEach((item, itemIndex) => {
            item.classList.toggle('is-active', itemIndex <= currentStep);
            item.classList.toggle('is-current', itemIndex === currentStep);
        });

        if (currentName === 'confirmation') {
            updateSummary();
        }
    };

    form.addEventListener('click', (event) => {
        const next = event.target.closest('[data-checkout-next]');
        const previous = event.target.closest('[data-checkout-previous]');

        if (next) {
            const step = steps.find((item) => item.dataset.checkoutStep === stepNames[currentStep]);
            if (!step || !validateStep(step)) {
                return;
            }
            showStep(currentStep + 1);
            return;
        }

        if (previous) {
            showStep(currentStep - 1);
        }
    });

    showStep(0);
})();
