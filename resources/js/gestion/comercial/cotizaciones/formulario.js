/**
 * Gestiona únicamente la interacción visual de los formularios de cotización.
 * La validación definitiva y los importes persistidos siempre pertenecen al servidor.
 */
(() => {
    document.querySelectorAll("[data-quotation-form]").forEach((form) => {
        form.querySelector('[value="nuevo"]')?.addEventListener(
            "change",
            () => {
                const customerLink = form.querySelector(
                    "[data-new-customer-link]",
                );

                if (customerLink) {
                    window.location.assign(customerLink.href);
                }
            },
        );

        const items = form.querySelector("[data-quotation-items]");
        const addButton = form.querySelector("[data-add-item]");

        if (!items || !addButton) {
            return;
        }

        const refreshTotals = () => {
            items.querySelectorAll("[data-quotation-item]").forEach((item) => {
                const product = item.querySelector("[data-product-select]");
                const quantity = item.querySelector("[data-quantity]");
                const output = item.querySelector("[data-line-total]");
                const price = Number(
                    product?.selectedOptions[0]?.dataset.price ?? 0,
                );
                const amount = Number(quantity?.value ?? 0);

                if (output) {
                    output.textContent = `S/ ${(price * amount).toFixed(2)}`;
                }
            });
        };

        const renumber = () => {
            items
                .querySelectorAll("[data-quotation-item]")
                .forEach((item, index) => {
                    item.querySelector("[data-product-select]")?.setAttribute(
                        "name",
                        `items[${index}][producto_id]`,
                    );
                    item.querySelector("[data-quantity]")?.setAttribute(
                        "name",
                        `items[${index}][cantidad]`,
                    );
                });
        };

        const bindItem = (item) => {
            item.querySelectorAll("select, input").forEach((control) => {
                control.addEventListener("input", refreshTotals);
                control.addEventListener("change", refreshTotals);
            });
            item.querySelector("[data-remove-item]")?.addEventListener(
                "click",
                () => {
                    if (
                        items.querySelectorAll("[data-quotation-item]")
                            .length <= 1
                    ) {
                        return;
                    }

                    item.remove();
                    renumber();
                    refreshTotals();
                },
            );
        };

        items.querySelectorAll("[data-quotation-item]").forEach(bindItem);
        addButton.addEventListener("click", () => {
            const source = items.querySelector("[data-quotation-item]");
            const clone = source.cloneNode(true);
            clone.querySelector("[data-product-select]").value = "";
            clone.querySelector("[data-quantity]").value = "1";
            items.appendChild(clone);
            bindItem(clone);
            renumber();
            refreshTotals();
        });

        renumber();
        refreshTotals();
    });
})();
