const salesPage = document.querySelector('[data-sales-page]');

if (salesPage && salesPage.dataset.salesInitialized !== 'true') {
    salesPage.dataset.salesInitialized = 'true';

    const currency = (value) => `S/ ${Number(value || 0).toLocaleString('es-PE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`;

    const escapeHtml = (value) => String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    const linesFor = (form) => [...form.querySelectorAll('[data-sales-line]')];

    const renderTotals = (form) => {
        let total = 0;

        linesFor(form).forEach((line, index) => {
            const price = Number(line.dataset.price ?? 0);
            const quantityInput = line.querySelector('[data-sales-quantity-input]');
            const quantity = Math.max(1, Number(quantityInput?.value ?? 1));
            const lineTotal = price * quantity;
            const lineSubtotal = line.querySelector('[data-sales-line-subtotal]');

            if (quantityInput) {
                quantityInput.value = String(quantity);
            }

            if (lineSubtotal) {
                lineSubtotal.textContent = currency(lineTotal);
            }

            const lineNumber = line.querySelector('.ventas-modal-line-number');
            if (lineNumber) {
                lineNumber.textContent = String(index + 1);
            }

            total += lineTotal;
        });

        form.querySelector('[data-sales-subtotal]')?.replaceChildren(document.createTextNode(currency(total)));
        form.querySelector('[data-sales-total]')?.replaceChildren(document.createTextNode(currency(total)));
    };

    const showEmptyLine = (form) => {
        const lines = form.querySelector('[data-sales-lines]');
        if (!lines || linesFor(form).length > 0 || lines.querySelector('[data-sales-empty]')) {
            return;
        }

        const empty = document.createElement('tr');
        empty.dataset.salesEmpty = 'true';
        empty.innerHTML = '<td colspan="6" class="ventas-modal-empty"><i class="bi bi-bag" aria-hidden="true"></i> Agrega al menos un producto para registrar la venta.</td>';
        lines.appendChild(empty);
    };

    const addProductLine = (form) => {
        const picker = form.querySelector('[data-sales-product-picker]');
        const option = picker?.selectedOptions?.[0];
        const productId = option?.value;
        const stock = Number(option?.dataset.stock ?? 0);

        if (!productId) {
            return;
        }

        if (stock < 1) {
            window.alert('Este producto no tiene stock disponible.');
            return;
        }

        const existingLine = form.querySelector(`[data-sales-line][data-product-id="${productId}"]`);
        if (existingLine) {
            const quantityInput = existingLine.querySelector('[data-sales-quantity-input]');
            quantityInput.value = String(Math.min(stock, Number(quantityInput.value || 0) + 1));
            renderTotals(form);
            picker.value = '';
            return;
        }

        const lines = form.querySelector('[data-sales-lines]');
        const emptyLine = lines?.querySelector('[data-sales-empty]');
        const itemIndex = Number(form.dataset.nextItemIndex ?? linesFor(form).length);
        const productName = option.textContent.split(' · ')[0];
        const price = Number(option.dataset.price ?? 0);
        const row = document.createElement('tr');

        emptyLine?.remove();
        row.className = 'ventas-modal-line';
        row.dataset.salesLine = 'true';
        row.dataset.productId = productId;
        row.dataset.price = String(price);
        row.innerHTML = `
            <td class="ventas-modal-line-number"></td>
            <td><span class="ventas-modal-product"><span class="ventas-modal-product-image"><i class="bi bi-box-seam" aria-hidden="true"></i></span><span><strong>${escapeHtml(productName)}</strong><small>Stock disponible: ${stock}</small></span></span><input type="hidden" name="items[${itemIndex}][producto_id]" value="${productId}"></td>
            <td><strong>${currency(price).replace('S/ ', '')}</strong></td>
            <td><span class="ventas-quantity"><button type="button" data-sales-quantity="down" aria-label="Disminuir cantidad">−</button><input class="ventas-modal-quantity" type="number" name="items[${itemIndex}][cantidad]" value="1" min="1" max="${stock}" data-sales-quantity-input><button type="button" data-sales-quantity="up" aria-label="Aumentar cantidad">+</button></span></td>
            <td><strong class="ventas-line-subtotal" data-sales-line-subtotal>${currency(price)}</strong></td>
            <td><button class="ventas-line-remove" type="button" data-sales-remove aria-label="Quitar producto"><i class="bi bi-trash3" aria-hidden="true"></i></button></td>`;
        lines?.appendChild(row);
        form.dataset.nextItemIndex = String(itemIndex + 1);
        picker.value = '';
        renderTotals(form);
    };

    const initializeForm = (form) => {
        if (form.dataset.salesFormInitialized === 'true') {
            return;
        }

        form.dataset.salesFormInitialized = 'true';
        form.dataset.nextItemIndex = String(linesFor(form).length);
        renderTotals(form);
        showEmptyLine(form);

        form.querySelector('[data-sales-add-product]')?.addEventListener('click', () => addProductLine(form));
        form.querySelector('[data-sales-product-search]')?.addEventListener('input', (event) => {
            const term = event.target.value.toLowerCase().trim();
            form.querySelectorAll('[data-sales-product-picker] option').forEach((option, index) => {
                option.hidden = index > 0 && !option.textContent.toLowerCase().includes(term);
            });
        });
        form.querySelector('[data-sales-clear]')?.addEventListener('click', () => {
            form.querySelectorAll('[data-sales-line]').forEach((line) => line.remove());
            showEmptyLine(form);
            renderTotals(form);
        });
        form.addEventListener('click', (event) => {
            const quantityButton = event.target.closest('[data-sales-quantity]');
            const removeButton = event.target.closest('[data-sales-remove]');

            if (quantityButton) {
                const line = quantityButton.closest('[data-sales-line]');
                const input = line?.querySelector('[data-sales-quantity-input]');
                const step = quantityButton.dataset.salesQuantity === 'up' ? 1 : -1;

                if (input) {
                    const min = Number(input.min || 1);
                    const max = Number(input.max || 1000);
                    input.value = String(Math.max(min, Math.min(max, Number(input.value || 1) + step)));
                    renderTotals(form);
                }
            }

            if (removeButton) {
                removeButton.closest('[data-sales-line]')?.remove();
                showEmptyLine(form);
                renderTotals(form);
            }
        });
        form.addEventListener('input', (event) => {
            if (event.target.matches('[data-sales-quantity-input]')) {
                renderTotals(form);
            }
        });
        form.addEventListener('submit', (event) => {
            if (linesFor(form).length === 0) {
                event.preventDefault();
                window.alert('Agrega al menos un producto antes de guardar la venta.');
            }
        });
    };

    const openDialog = (dialog) => {
        if (dialog && !dialog.open) {
            dialog.showModal();
        }
    };

    salesPage.querySelectorAll('[data-sales-form]').forEach(initializeForm);
    salesPage.querySelectorAll('[data-open-sales-create]').forEach((button) => {
        button.addEventListener('click', () => openDialog(salesPage.querySelector('#nueva-venta')));
    });
    salesPage.querySelectorAll('[data-close-sales-dialog]').forEach((button) => {
        button.addEventListener('click', () => button.closest('dialog')?.close());
    });
    salesPage.querySelectorAll('dialog').forEach((dialog) => {
        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) {
                dialog.close();
            }
        });
        if (dialog.dataset.salesAutoOpen === '1') {
            openDialog(dialog);
        }
    });

    salesPage.querySelector('[data-sales-refresh]')?.addEventListener('click', () => {
        window.location.reload();
    });

    salesPage.querySelectorAll('[data-sales-bar]').forEach((bar) => {
        bar.addEventListener('mouseenter', () => {
            salesPage.querySelector('[data-sales-chart-value]')?.replaceChildren(document.createTextNode(bar.dataset.salesValue ?? 'S/ 0.00'));
        });
    });
}
