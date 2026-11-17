/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/gestion/comercial/compras/index.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

(() => {
    /** Configura los formularios de alta y edición de compras. */
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    document.querySelectorAll("[data-purchase-form]").forEach((form) => {
        const items = form.querySelector("[data-purchase-items]");
        const template = form.querySelector("[data-purchase-template]");
        const currency = form.querySelector('[name="moneda"]')?.value || "PEN";

        // Funcion: amount concentra una tarea reutilizable del modulo.
        const amount = (selector, item) =>
            Number.parseFloat(item.querySelector(selector)?.value || "0") || 0;
        // Funcion: money concentra una tarea reutilizable del modulo.
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

        // Funcion: filtrarVariantesDialogo concentra una tarea reutilizable del modulo.
        const filtrarVariantesDialogo = () => {
            const producto = selectorProducto?.value || "";
            selectorVariante
                ?.querySelectorAll("option[data-product-id]")
                // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
                .forEach((option) => {
                    option.hidden = option.dataset.productId !== producto;
                });
            // Condicional: evalua "selectorVariante?.selectedOptions[0]?.hidden" para decidir si ejecuta el bloque siguiente.
            if (selectorVariante?.selectedOptions[0]?.hidden) {
                selectorVariante.value = "";
            }
        };

        // Funcion: cerrarDialogoProducto concentra una tarea reutilizable del modulo.
        const cerrarDialogoProducto = () => {
            dialogoProducto?.close();
            // Condicional: evalua "selectorProducto" para decidir si ejecuta el bloque siguiente.
            if (selectorProducto) selectorProducto.value = "";
            // Condicional: evalua "selectorVariante" para decidir si ejecuta el bloque siguiente.
            if (selectorVariante) selectorVariante.value = "";
            // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
            dialogoProducto?.querySelectorAll("input").forEach((input) => {
                // Condicional: evalua "input.dataset.purchasePickerQuantity" para decidir si ejecuta el bloque siguiente.
                if (input.dataset.purchasePickerQuantity) input.value = "1";
                // Condicional: evalua "la expresion de control" para decidir si ejecuta el bloque siguiente.
                if (
                    input.dataset.purchasePickerDiscount ||
                    input.dataset.purchasePickerTax
                )
                    input.value = "0";
                // Condicional: evalua "input.dataset.purchasePickerCost" para decidir si ejecuta el bloque siguiente.
                if (input.dataset.purchasePickerCost) input.value = "";
            });
            filtrarVariantesDialogo();
        };

        // Funcion: agregarFilaDesdeDialogo concentra una tarea reutilizable del modulo.
        const agregarFilaDesdeDialogo = () => {
            // Condicional: evalua "!dialogoProducto || !template" para decidir si ejecuta el bloque siguiente.
            if (!dialogoProducto || !template) return;
            const producto = selectorProducto?.value || "";
            const cantidad =
                dialogoProducto.querySelector("[data-purchase-picker-quantity]")
                    ?.value || "1";
            const costo =
                dialogoProducto.querySelector("[data-purchase-picker-cost]")
                    ?.value || "";
            // Condicional: evalua "!producto || !costo || Number(cantidad) <= 0" para decidir si ejecuta el bloque siguiente.
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

        // Funcion: refresh concentra una tarea reutilizable del modulo.
        const refresh = () => {
            let subtotal = 0;
            let discounts = 0;
            let taxes = 0;
            // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
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

        // Funcion: filterVariants concentra una tarea reutilizable del modulo.
        const filterVariants = (item) => {
            const product = item.querySelector(
                "[data-purchase-product]",
            )?.value;
            const variants = item.querySelector("[data-purchase-variant]");
            // Condicional: evalua "!variants" para decidir si ejecuta el bloque siguiente.
            if (!variants) return;
            // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
            [...variants.options].forEach((option) => {
                const belongsToProduct =
                    !option.dataset.productId ||
                    option.dataset.productId === product;
                option.hidden = !belongsToProduct;
            });
            // Condicional: evalua "variants.selectedOptions[0]?.hidden" para decidir si ejecuta el bloque siguiente.
            if (variants.selectedOptions[0]?.hidden) variants.value = "";
        };

        // Funcion: bindItem concentra una tarea reutilizable del modulo.
        const bindItem = (item) => {
            // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
            item.querySelectorAll("input, select").forEach((input) =>
                // Evento: escucha el evento input y ejecuta la funcion asociada cuando ocurre.
                input.addEventListener("input", refresh),
            );
            // Evento: escucha una interaccion del usuario o del navegador y ejecuta la funcion asociada cuando ocurre.
            item.querySelector("[data-purchase-product]")?.addEventListener(
                "change",
                () => {
                    filterVariants(item);
                    refresh();
                },
            );
            // Evento: escucha una interaccion del usuario o del navegador y ejecuta la funcion asociada cuando ocurre.
            item.querySelector("[data-purchase-remove]")?.addEventListener(
                "click",
                () => {
                    // Condicional: evalua "la expresion de control" para decidir si ejecuta el bloque siguiente.
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

        // Evento: escucha una interaccion del usuario o del navegador y ejecuta la funcion asociada cuando ocurre.
        form.querySelector('[name="moneda"]')?.addEventListener(
            "change",
            refresh,
        );
        // Evento: escucha una interaccion del usuario o del navegador y ejecuta la funcion asociada cuando ocurre.
        form.querySelector("[data-purchase-add]")?.addEventListener(
            "click",
            () => {
                // Condicional: evalua "la expresion de control" para decidir si ejecuta el bloque siguiente.
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

        // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
        selectorProducto?.addEventListener("change", filtrarVariantesDialogo);
        dialogoProducto
            ?.querySelector("[data-purchase-product-add]")
            // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
            ?.addEventListener("click", agregarFilaDesdeDialogo);
        dialogoProducto
            ?.querySelector("[data-purchase-product-close]")
            // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
            ?.addEventListener("click", cerrarDialogoProducto);
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        dialogoProducto?.addEventListener("click", (event) => {
            // Condicional: evalua "event.target === dialogoProducto) cerrarDialogoProducto(" para decidir si ejecuta el bloque siguiente.
            if (event.target === dialogoProducto) cerrarDialogoProducto();
        });
        dialogoProducto
            ?.querySelectorAll("[data-purchase-product-tab]")
            // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
            .forEach((tab) => {
                // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
                tab.addEventListener("click", () => {
                    const selected = tab.dataset.purchaseProductTab;
                    dialogoProducto
                        .querySelectorAll("[data-purchase-product-tab]")
                        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
                        .forEach((item) =>
                            item.classList.toggle("is-active", item === tab),
                        );
                    dialogoProducto
                        .querySelectorAll("[data-purchase-product-panel]")
                        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
                        .forEach((panel) => {
                            panel.hidden =
                                panel.dataset.purchaseProductPanel !== selected;
                        });
                });
            });
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        items.querySelectorAll("[data-purchase-item]").forEach(bindItem);
        refresh();
    });
})();

document
    .querySelectorAll('.compras-import-file input[type="file"]')
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    .forEach((input) => {
        // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
        input.addEventListener("change", () => {
            const fileName = input
                .closest(".compras-import-file")
                ?.querySelector("strong");
            const selected = input.files?.[0];
            // Condicional: evalua "fileName" para decidir si ejecuta el bloque siguiente.
            if (fileName)
                fileName.textContent =
                    selected?.name || "Ningún archivo seleccionado";
        });
    });
