(() => {
    /** Configura los formularios de alta y edición de compras. */
    document.querySelectorAll("[data-purchase-form]").forEach((form) => {
        const items = form.querySelector("[data-purchase-items]");
        const template = form.querySelector("[data-purchase-template]");
        const currency = form.querySelector('[name="moneda"]')?.value || "PEN";

        const amount = (selector, item) =>
            Number.parseFloat(item.querySelector(selector)?.value || "0") || 0;
        const money = (value) =>
            `${currency === "USD" ? "$" : "S/"} ${value.toFixed(2)}`;

        const dialogoProducto = form
            .closest("dialog")
            ?.querySelector("[data-purchase-product-dialog]");
        const selectorProducto = dialogoProducto?.querySelector(
            "[data-purchase-picker-product]",
        );
        const selectorVariante = dialogoProducto?.querySelector(
            "[data-purchase-picker-variant]",
        );

        const filtrarVariantesDialogo = () => {
            const producto = selectorProducto?.value || "";
            selectorVariante
                ?.querySelectorAll("option[data-product-id]")
                .forEach((option) => {
                    option.hidden = option.dataset.productId !== producto;
                });
            if (selectorVariante?.selectedOptions[0]?.hidden) {
                selectorVariante.value = "";
            }
        };

        const cerrarDialogoProducto = () => {
            dialogoProducto?.close();
            if (selectorProducto) selectorProducto.value = "";
            if (selectorVariante) selectorVariante.value = "";
            dialogoProducto?.querySelectorAll("input").forEach((input) => {
                if (input.dataset.purchasePickerQuantity) input.value = "1";
                if (
                    input.dataset.purchasePickerDiscount ||
                    input.dataset.purchasePickerTax
                )
                    input.value = "0";
                if (input.dataset.purchasePickerCost) input.value = "";
            });
            filtrarVariantesDialogo();
        };

        const agregarFilaDesdeDialogo = () => {
            if (!dialogoProducto || !template) return;
            const producto = selectorProducto?.value || "";
            const cantidad =
                dialogoProducto.querySelector("[data-purchase-picker-quantity]")
                    ?.value || "1";
            const costo =
                dialogoProducto.querySelector("[data-purchase-picker-cost]")
                    ?.value || "";
            if (!producto || !costo || Number(cantidad) <= 0) {
                selectorProducto?.focus();
                return;
            }
            const index = items.querySelectorAll("[data-purchase-item]").length;
            const wrapper = document.createElement("div");
            wrapper.innerHTML = template.innerHTML
                .replaceAll("__INDEX__", String(index))
                .replaceAll("__INDEX_NUMBER__", String(index + 1));
            const nuevaFila = wrapper.firstElementChild;
            items.append(nuevaFila);
            bindItem(nuevaFila);
            nuevaFila.querySelector("[data-purchase-product]").value = producto;
            nuevaFila.querySelector("[data-purchase-variant]").value =
                selectorVariante?.value || "";
            nuevaFila.querySelector("[data-purchase-quantity]").value =
                cantidad;
            nuevaFila.querySelector("[data-purchase-cost]").value = costo;
            nuevaFila.querySelector("[data-purchase-discount]").value =
                dialogoProducto.querySelector("[data-purchase-picker-discount]")
                    ?.value || "0";
            nuevaFila.querySelector("[data-purchase-tax]").value =
                dialogoProducto.querySelector("[data-purchase-picker-tax]")
                    ?.value || "0";
            filterVariants(nuevaFila);
            refresh();
            cerrarDialogoProducto();
        };

        const refresh = () => {
            let subtotal = 0;
            let discounts = 0;
            let taxes = 0;
            [...items.querySelectorAll("[data-purchase-item]")].forEach(
                (item, index) => {
                    item.querySelector(
                        "[data-purchase-item-number]",
                    ).textContent = String(index + 1);
                    const quantity = amount("[data-purchase-quantity]", item);
                    const cost = amount("[data-purchase-cost]", item);
                    const discount = amount("[data-purchase-discount]", item);
                    const tax = amount("[data-purchase-tax]", item);
                    const lineSubtotal = quantity * cost;
                    subtotal += lineSubtotal;
                    discounts += discount;
                    taxes += tax;
                    item.querySelector(
                        "[data-purchase-line-total]",
                    ).textContent = money(lineSubtotal - discount + tax);
                },
            );
            form.querySelector("[data-purchase-subtotal]").textContent =
                money(subtotal);
            form.querySelector("[data-purchase-discounts]").textContent =
                money(discounts);
            form.querySelector("[data-purchase-taxes]").textContent =
                money(taxes);
            form.querySelector("[data-purchase-total]").textContent = money(
                subtotal - discounts + taxes,
            );
        };

        const filterVariants = (item) => {
            const product = item.querySelector(
                "[data-purchase-product]",
            )?.value;
            const variants = item.querySelector("[data-purchase-variant]");
            if (!variants) return;
            [...variants.options].forEach((option) => {
                const belongsToProduct =
                    !option.dataset.productId ||
                    option.dataset.productId === product;
                option.hidden = !belongsToProduct;
            });
            if (variants.selectedOptions[0]?.hidden) variants.value = "";
        };

        const bindItem = (item) => {
            item.querySelectorAll("input, select").forEach((input) =>
                input.addEventListener("input", refresh),
            );
            item.querySelector("[data-purchase-product]")?.addEventListener(
                "change",
                () => {
                    filterVariants(item);
                    refresh();
                },
            );
            item.querySelector("[data-purchase-remove]")?.addEventListener(
                "click",
                () => {
                    if (
                        items.querySelectorAll("[data-purchase-item]").length >
                        1
                    ) {
                        item.remove();
                        refresh();
                    }
                },
            );
            filterVariants(item);
        };

        form.querySelector('[name="moneda"]')?.addEventListener(
            "change",
            refresh,
        );
        form.querySelector("[data-purchase-add]")?.addEventListener(
            "click",
            () => {
                if (
                    dialogoProducto &&
                    typeof dialogoProducto.showModal === "function"
                ) {
                    dialogoProducto.showModal();
                    filtrarVariantesDialogo();
                    return;
                }
                const index = items.querySelectorAll(
                    "[data-purchase-item]",
                ).length;
                const item = document.createElement("div");
                item.innerHTML = template.innerHTML
                    .replaceAll("__INDEX__", String(index))
                    .replaceAll("__INDEX_NUMBER__", String(index + 1));
                const newItem = item.firstElementChild;
                items.append(newItem);
                bindItem(newItem);
                refresh();
            },
        );

        selectorProducto?.addEventListener("change", filtrarVariantesDialogo);
        dialogoProducto
            ?.querySelector("[data-purchase-product-add]")
            ?.addEventListener("click", agregarFilaDesdeDialogo);
        dialogoProducto
            ?.querySelector("[data-purchase-product-close]")
            ?.addEventListener("click", cerrarDialogoProducto);
        dialogoProducto?.addEventListener("click", (event) => {
            if (event.target === dialogoProducto) cerrarDialogoProducto();
        });
        dialogoProducto
            ?.querySelectorAll("[data-purchase-product-tab]")
            .forEach((tab) => {
                tab.addEventListener("click", () => {
                    const selected = tab.dataset.purchaseProductTab;
                    dialogoProducto
                        .querySelectorAll("[data-purchase-product-tab]")
                        .forEach((item) =>
                            item.classList.toggle("is-active", item === tab),
                        );
                    dialogoProducto
                        .querySelectorAll("[data-purchase-product-panel]")
                        .forEach((panel) => {
                            panel.hidden =
                                panel.dataset.purchaseProductPanel !== selected;
                        });
                });
            });
        items.querySelectorAll("[data-purchase-item]").forEach(bindItem);
        refresh();
    });
})();

document
    .querySelectorAll('.compras-import-file input[type="file"]')
    .forEach((input) => {
        input.addEventListener("change", () => {
            const fileName = input
                .closest(".compras-import-file")
                ?.querySelector("strong");
            const selected = input.files?.[0];
            if (fileName)
                fileName.textContent =
                    selected?.name || "Ningún archivo seleccionado";
        });
    });
