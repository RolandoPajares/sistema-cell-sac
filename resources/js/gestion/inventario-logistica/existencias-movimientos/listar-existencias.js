document.addEventListener('DOMContentLoaded', () => {
    const dialog = document.querySelector('#registrar-movimiento-inventario');
    const page = document.querySelector('[data-inventory-page]');
    const productSelect = dialog?.querySelector('[data-inventory-product-select]');
    const productInput = dialog?.querySelector('[data-inventory-product-input]');
    const warehouseSelect = dialog?.querySelector('[data-inventory-warehouse-select]');
    const variantSelect = dialog?.querySelector('[data-inventory-variant-select]');
    const typeSelect = dialog?.querySelector('[data-inventory-type-select]');
    const quantityInput = dialog?.querySelector('[data-inventory-quantity]');
    const quantityLabel = dialog?.querySelector('[data-inventory-quantity-label]');
    const movementForm = dialog?.querySelector('[data-inventory-movement-form]');
    const saveButton = movementForm?.querySelector('[type="submit"]');
    const detailDialog = document.querySelector('[data-inventory-detail-dialog]');

    if (!dialog || !page || !productSelect || !productInput || !warehouseSelect || !variantSelect || !typeSelect || !quantityInput || !quantityLabel || !movementForm || !saveButton || !detailDialog) {
        return;
    }

    const movementBaseUrl = movementForm.dataset.rutaBase.replace(/\/0$/, '');
    const baseVariantOption = variantSelect.querySelector('option[value=""]');
    const variantOptions = [...variantSelect.querySelectorAll('option[data-producto-id]')];

    const updateMovementUrl = () => {
        const productId = productSelect.value;
        productInput.value = productId;
        movementForm.action = productId ? `${movementBaseUrl}/${encodeURIComponent(productId)}` : `${movementBaseUrl}/0`;
        saveButton.disabled = productId === '' || warehouseSelect.value === '' || movementForm.dataset.hayProductos !== '1' || movementForm.dataset.hayAlmacenes !== '1';
    };

    const updateAvailableVariants = () => {
        const productId = productSelect.value;
        let productVariantCount = 0;

        variantOptions.forEach((option) => {
            const belongsToProduct = option.dataset.productoId === productId;
            option.hidden = !belongsToProduct;
            option.disabled = !belongsToProduct;
            productVariantCount += belongsToProduct ? 1 : 0;
        });

        const requiresVariant = productId !== '' && productVariantCount > 0;
        baseVariantOption.hidden = requiresVariant;
        baseVariantOption.disabled = requiresVariant;
        variantSelect.required = requiresVariant;
        variantSelect.disabled = productId === '' || !requiresVariant;

        if (!requiresVariant || variantSelect.selectedOptions[0]?.dataset.productoId !== productId) {
            variantSelect.value = '';
        }
    };

    const updateQuantityLabel = () => {
        const isAdjustment = typeSelect.value === 'ajuste';
        quantityLabel.textContent = isAdjustment ? 'Existencias contadas (saldo final)' : 'Cantidad del movimiento';
        quantityInput.min = isAdjustment ? '0' : '1';
    };

    productSelect.addEventListener('change', updateMovementUrl);
    productSelect.addEventListener('change', updateAvailableVariants);
    warehouseSelect.addEventListener('change', updateMovementUrl);
    typeSelect.addEventListener('change', updateQuantityLabel);
    updateMovementUrl();
    updateAvailableVariants();
    updateQuantityLabel();

    page.querySelector('[data-inventory-per-page]')?.addEventListener('change', (event) => event.currentTarget.form.submit());

    page.querySelectorAll('[data-open-inventory-movement]').forEach((button) => {
        button.addEventListener('click', () => {
            if (button.dataset.inventoryProduct) {
                productSelect.value = button.dataset.inventoryProduct;
            }
            if (button.dataset.movementType) {
                typeSelect.value = button.dataset.movementType;
            }
            updateAvailableVariants();
            updateMovementUrl();
            updateQuantityLabel();
            dialog.showModal();
        });
    });

    document.querySelectorAll('[data-close-inventory-movement]').forEach((button) => button.addEventListener('click', () => dialog.close()));
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            dialog.close();
        }
    });

    page.querySelectorAll('[data-open-inventory-detail]').forEach((button) => button.addEventListener('click', () => {
        const row = button.closest('tr');
        const details = detailDialog.querySelector('[data-inventory-detail-fields]');
        const headings = [...row.closest('table').querySelectorAll('thead th')];
        const cells = [...row.querySelectorAll('td')];
        details.replaceChildren();
        cells.slice(0, headings.length - 1).forEach((cell, index) => {
            const wrapper = document.createElement('div');
            const term = document.createElement('dt');
            const description = document.createElement('dd');
            term.textContent = headings[index]?.textContent.trim() ?? `Dato ${index + 1}`;
            description.textContent = cell.textContent.trim();
            wrapper.append(term, description);
            details.append(wrapper);
        });
        detailDialog.showModal();
    }));

    page.querySelectorAll('[data-open-movement-detail]').forEach((button) => button.addEventListener('click', () => {
        const details = detailDialog.querySelector('[data-inventory-detail-fields]');
        const fields = [
            ['Tipo', button.dataset.tipo],
            ['Cantidad', button.dataset.cantidad],
            ['Fecha', button.dataset.fecha],
            ['Usuario', button.dataset.usuario],
            ['Producto', button.dataset.producto],
            ['Variante', button.dataset.variante],
            ['Almacén', button.dataset.almacen],
            ['Motivo', button.dataset.notas],
            ['Documento', button.dataset.documento],
        ];
        details.replaceChildren();
        fields.forEach(([label, value]) => {
            const wrapper = document.createElement('div');
            const term = document.createElement('dt');
            const description = document.createElement('dd');
            term.textContent = label;
            description.textContent = value || '—';
            wrapper.append(term, description);
            details.append(wrapper);
        });
        detailDialog.showModal();
    }));

    detailDialog.querySelectorAll('[data-close-inventory-detail]').forEach((button) => button.addEventListener('click', () => detailDialog.close()));
    detailDialog.addEventListener('click', (event) => {
        if (event.target === detailDialog) {
            detailDialog.close();
        }
    });

    if (page.dataset.hasErrors === '1') {
        dialog.showModal();
    }
});
