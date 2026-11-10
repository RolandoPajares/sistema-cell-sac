document.querySelectorAll('[data-il-transfer-manager]').forEach((page) => {
    const dialog = page.querySelector('[data-transfer-dialog]');
    const form = page.querySelector('[data-transfer-create-form]');
    const lines = page.querySelector('[data-transfer-lines]');
    const template = page.querySelector('[data-transfer-line-template]');

    page.querySelector('[data-transfer-open]')?.addEventListener('click', () => dialog?.showModal());
    page.querySelectorAll('[data-transfer-close]').forEach((button) => button.addEventListener('click', () => dialog?.close()));

/**
 * Actualiza la información visible o el estado del componente.
 *
 * @returns {*} Resultado de la operación.
 */
    const updateLineNames = () => {
        lines?.querySelectorAll('[data-transfer-line]').forEach((line, index) => {
            const select = line.querySelector('[data-transfer-product]');
            const quantity = line.querySelector('[data-transfer-quantity]');
            const productId = line.querySelector('[data-transfer-product-id]');
            const variantId = line.querySelector('[data-transfer-variant-id]');

            select.name = `items[${index}][producto-variante]`;
            quantity.name = `items[${index}][cantidad]`;
            productId.name = `items[${index}][producto_id]`;
            variantId.name = `items[${index}][variante_id]`;

            const [selectedProductId, selectedVariantId] = (select.value || ':').split(':');
            productId.value = selectedProductId || '';
            variantId.value = selectedVariantId === 'null' ? '' : (selectedVariantId || '');
        });
    };

/**
 * Ejecuta la operación syncSelection utilizada por el módulo JavaScript.
 *
 * @param {*} event - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
    const syncSelection = (event) => {
        if (event.target.matches('[data-transfer-product]')) {
            updateLineNames();
        }
    };

    lines?.addEventListener('change', syncSelection);
    lines?.addEventListener('click', (event) => {
        const removeButton = event.target.closest('[data-transfer-remove]');
        if (removeButton) {
            removeButton.closest('[data-transfer-line]')?.remove();
            updateLineNames();
        }
    });

    page.querySelector('[data-transfer-add]')?.addEventListener('click', () => {
        const fragment = template?.content.cloneNode(true);
        if (fragment) {
            lines?.append(fragment);
            updateLineNames();
        }
    });

    form?.addEventListener('submit', (event) => {
        updateLineNames();
        const origin = form.querySelector('[name="almacen_origen_id"]')?.value;
        const destination = form.querySelector('[name="almacen_destino_id"]')?.value;
        if (origin && destination && origin === destination) {
            event.preventDefault();
            form.querySelector('[name="almacen_destino_id"]')?.setCustomValidity('El almacén de destino debe ser diferente al de origen.');
            form.querySelector('[name="almacen_destino_id"]')?.reportValidity();
        }
    });

    form?.querySelector('[name="almacen_destino_id"]')?.addEventListener('change', (event) => event.target.setCustomValidity(''));
    updateLineNames();
});