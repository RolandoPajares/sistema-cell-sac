(() => {
    /** Configura los formularios de alta y edición de compras. */
    document.querySelectorAll('[data-purchase-form]').forEach((form) => {
        const items = form.querySelector('[data-purchase-items]');
        const template = form.querySelector('[data-purchase-template]');
        const currency = form.querySelector('[name="moneda"]')?.value || 'PEN';

        const amount = (selector, item) => Number.parseFloat(item.querySelector(selector)?.value || '0') || 0;
        const money = (value) => `${currency === 'USD' ? '$' : 'S/'} ${value.toFixed(2)}`;

        const refresh = () => {
            let subtotal = 0;
            let discounts = 0;
            let taxes = 0;
            [...items.querySelectorAll('[data-purchase-item]')].forEach((item, index) => {
                item.querySelector('[data-purchase-item-number]').textContent = String(index + 1);
                const quantity = amount('[data-purchase-quantity]', item);
                const cost = amount('[data-purchase-cost]', item);
                const discount = amount('[data-purchase-discount]', item);
                const tax = amount('[data-purchase-tax]', item);
                const lineSubtotal = quantity * cost;
                subtotal += lineSubtotal;
                discounts += discount;
                taxes += tax;
                item.querySelector('[data-purchase-line-total]').textContent = money(lineSubtotal - discount + tax);
            });
            form.querySelector('[data-purchase-subtotal]').textContent = money(subtotal);
            form.querySelector('[data-purchase-discounts]').textContent = money(discounts);
            form.querySelector('[data-purchase-taxes]').textContent = money(taxes);
            form.querySelector('[data-purchase-total]').textContent = money(subtotal - discounts + taxes);
        };

        const filterVariants = (item) => {
            const product = item.querySelector('[data-purchase-product]')?.value;
            const variants = item.querySelector('[data-purchase-variant]');
            if (!variants) return;
            [...variants.options].forEach((option) => {
                const belongsToProduct = !option.dataset.productId || option.dataset.productId === product;
                option.hidden = !belongsToProduct;
            });
            if (variants.selectedOptions[0]?.hidden) variants.value = '';
        };

        const bindItem = (item) => {
            item.querySelectorAll('input, select').forEach((input) => input.addEventListener('input', refresh));
            item.querySelector('[data-purchase-product]')?.addEventListener('change', () => {
                filterVariants(item);
                refresh();
            });
            item.querySelector('[data-purchase-remove]')?.addEventListener('click', () => {
                if (items.querySelectorAll('[data-purchase-item]').length > 1) {
                    item.remove();
                    refresh();
                }
            });
            filterVariants(item);
        };

        form.querySelector('[name="moneda"]')?.addEventListener('change', refresh);
        form.querySelector('[data-purchase-add]')?.addEventListener('click', () => {
            const index = items.querySelectorAll('[data-purchase-item]').length;
            const item = document.createElement('div');
            item.innerHTML = template.innerHTML.replaceAll('__INDEX__', String(index)).replaceAll('__INDEX_NUMBER__', String(index + 1));
            const newItem = item.firstElementChild;
            items.append(newItem);
            bindItem(newItem);
            refresh();
        });
        items.querySelectorAll('[data-purchase-item]').forEach(bindItem);
        refresh();
    });
})();
