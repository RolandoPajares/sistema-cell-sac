/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/gestion/comercial/ventas/index.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

const salesPage = document.querySelector("[data-sales-page]");

// Condicional: evalua "salesPage && salesPage.dataset.salesInitialized !== "true"" para decidir si ejecuta el bloque siguiente.
if (salesPage && salesPage.dataset.salesInitialized !== "true") {
    salesPage.dataset.salesInitialized = "true";

    /**
     * Ejecuta la operación currency utilizada por el módulo JavaScript.
     *
     * @param {*} value - Parámetro recibido por la función.
     *
     * @returns {*} Resultado de la operación.
     */
    // Funcion: currency concentra una tarea reutilizable del modulo.
    const currency = (value) =>
        `S/ ${Number(value || 0).toLocaleString("es-PE", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        })}`;

    /**
     * Ejecuta la operación escapeHtml utilizada por el módulo JavaScript.
     *
     * @param {*} value - Parámetro recibido por la función.
     *
     * @returns {*} Resultado de la operación.
     */
    // Funcion: escapeHtml concentra una tarea reutilizable del modulo.
    const escapeHtml = (value) =>
        String(value)
            .replaceAll("&", "&amp;")
            .replaceAll("<", "&lt;")
            .replaceAll(">", "&gt;")
            .replaceAll('"', "&quot;")
            .replaceAll("'", "&#039;");

    /**
     * Ejecuta la operación linesFor utilizada por el módulo JavaScript.
     *
     * @param {*} form - Parámetro recibido por la función.
     *
     * @returns {*} Resultado de la operación.
     */
    // Funcion: linesFor concentra una tarea reutilizable del modulo.
    const linesFor = (form) => [...form.querySelectorAll("[data-sales-line]")];

    /**
     * Ejecuta la operación renderTotals utilizada por el módulo JavaScript.
     *
     * @param {*} form - Parámetro recibido por la función.
     *
     * @returns {*} Resultado de la operación.
     */
    // Funcion: renderTotals concentra una tarea reutilizable del modulo.
    const renderTotals = (form) => {
        let subtotal = 0;

        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        linesFor(form).forEach((line, index) => {
            const price = Number(line.dataset.price ?? 0);
            const quantityInput = line.querySelector(
                "[data-sales-quantity-input]",
            );
            const quantity = Math.max(1, Number(quantityInput?.value ?? 1));
            const lineTotal = price * quantity;
            const lineSubtotal = line.querySelector(
                "[data-sales-line-subtotal]",
            );

            // Condicional: evalua "quantityInput" para decidir si ejecuta el bloque siguiente.
            if (quantityInput) {
                quantityInput.value = String(quantity);
            }

            // Condicional: evalua "lineSubtotal" para decidir si ejecuta el bloque siguiente.
            if (lineSubtotal) {
                lineSubtotal.textContent = currency(lineTotal);
            }

            const lineNumber = line.querySelector(".ventas-modal-line-number");
            // Condicional: evalua "lineNumber" para decidir si ejecuta el bloque siguiente.
            if (lineNumber) {
                lineNumber.textContent = String(index + 1);
            }

            subtotal += lineTotal;
        });

        const tax = Math.round(subtotal * 0.18 * 100) / 100;
        const total = subtotal + tax;

        form.querySelector("[data-sales-subtotal]")?.replaceChildren(
            document.createTextNode(currency(subtotal)),
        );
        form.querySelector("[data-sales-tax]")?.replaceChildren(
            document.createTextNode(currency(tax)),
        );
        form.querySelector("[data-sales-total]")?.replaceChildren(
            document.createTextNode(currency(total)),
        );
        updateCashPayment(form, total);
    };

    // Funcion: updateCashPayment concentra una tarea reutilizable del modulo.
    const updateCashPayment = (form, total = null) => {
        const payment = form.querySelector('[name="medio_pago_id"]');
        const cashField = form.querySelector("[data-cash-payment]");
        const amountInput = form.querySelector("[data-cash-amount]");
        const change = form.querySelector("[data-cash-change]");
        const isCash = payment?.selectedOptions?.[0]?.dataset.paymentCode === "efectivo";
        // Condicional: evalua "!cashField || !amountInput" para decidir si ejecuta el bloque siguiente.
        if (!cashField || !amountInput) {
            return;
        }
        cashField.hidden = !isCash;
        amountInput.required = isCash;
        // Condicional: evalua "!isCash" para decidir si ejecuta el bloque siguiente.
        if (!isCash) {
            amountInput.value = "";
            // Condicional: evalua "change" para decidir si ejecuta el bloque siguiente.
            if (change) change.textContent = "Vuelto: S/ 0.00";
            return;
        }
        const saleTotal = total ?? Number(form.querySelector("[data-sales-total]")?.textContent.replace(/[^0-9,.-]/g, "").replace(",", ".") || 0);
        const received = Number(amountInput.value || 0);
        // Condicional: evalua "change) change.textContent = `Vuelto: ${currency(Math.max(0, received - saleTotal)" para decidir si ejecuta el bloque siguiente.
        if (change) change.textContent = `Vuelto: ${currency(Math.max(0, received - saleTotal))}`;
    };

    /**
     * Recupera y presenta el detalle de la información solicitada.
     *
     * @param {*} form - Parámetro recibido por la función.
     *
     * @returns {*} Resultado de la operación.
     */
    // Funcion: showEmptyLine concentra una tarea reutilizable del modulo.
    const showEmptyLine = (form) => {
        const lines = form.querySelector("[data-sales-lines]");
        // Condicional: evalua "la expresion de control" para decidir si ejecuta el bloque siguiente.
        if (
            !lines ||
            linesFor(form).length > 0 ||
            lines.querySelector("[data-sales-empty]")
        ) {
            return;
        }

        const empty = document.createElement("tr");
        empty.dataset.salesEmpty = "true";
        empty.innerHTML =
            '<td colspan="6" class="ventas-modal-empty"><i class="bi bi-bag" aria-hidden="true"></i> Agrega al menos un producto para registrar la venta.</td>';
        lines.appendChild(empty);
    };

    /**
     * Ejecuta la operación addProductLine utilizada por el módulo JavaScript.
     *
     * @param {*} form - Parámetro recibido por la función.
     *
     * @returns {*} Resultado de la operación.
     */
    // Funcion: addProductLine concentra una tarea reutilizable del modulo.
    const addProductLine = (form) => {
        const picker = form.querySelector("[data-sales-product-picker]");
        const option = picker?.selectedOptions?.[0];
        const productId = option?.value;
        const stock = Number(option?.dataset.stock ?? 0);

        // Condicional: evalua "!productId" para decidir si ejecuta el bloque siguiente.
        if (!productId) {
            return;
        }

        // Condicional: evalua "stock < 1" para decidir si ejecuta el bloque siguiente.
        if (stock < 1) {
            window.alert("Este producto no tiene stock disponible.");
            return;
        }

        const existingLine = form.querySelector(
            `[data-sales-line][data-product-id="${productId}"]`,
        );
        // Condicional: evalua "existingLine" para decidir si ejecuta el bloque siguiente.
        if (existingLine) {
            const quantityInput = existingLine.querySelector(
                "[data-sales-quantity-input]",
            );
            quantityInput.value = String(
                Math.min(stock, Number(quantityInput.value || 0) + 1),
            );
            renderTotals(form);
            picker.value = "";
            return;
        }

        const lines = form.querySelector("[data-sales-lines]");
        const emptyLine = lines?.querySelector("[data-sales-empty]");
        const itemIndex = Number(
            form.dataset.nextItemIndex ?? linesFor(form).length,
        );
        const productName = option.textContent.split(" · ")[0];
        const price = Number(option.dataset.price ?? 0);
        const row = document.createElement("tr");

        emptyLine?.remove();
        row.className = "ventas-modal-line";
        row.dataset.salesLine = "true";
        row.dataset.productId = productId;
        row.dataset.price = String(price);
        row.innerHTML = `
            <td class="ventas-modal-line-number"></td>
            <td><span class="ventas-modal-product"><span class="ventas-modal-product-image"><i class="bi bi-box-seam" aria-hidden="true"></i></span><span><strong>${escapeHtml(productName)}</strong><small>Stock disponible: ${stock}</small></span></span><input type="hidden" name="items[${itemIndex}][producto_id]" value="${productId}"></td>
            <td><strong>${currency(price).replace("S/ ", "")}</strong></td>
            <td><span class="ventas-quantity"><button type="button" data-sales-quantity="down" aria-label="Disminuir cantidad">−</button><input class="ventas-modal-quantity" type="number" name="items[${itemIndex}][cantidad]" value="1" min="1" max="${stock}" data-sales-quantity-input><button type="button" data-sales-quantity="up" aria-label="Aumentar cantidad">+</button></span></td>
            <td><strong class="ventas-line-subtotal" data-sales-line-subtotal>${currency(price)}</strong></td>
            <td><button class="ventas-line-remove" type="button" data-sales-remove aria-label="Quitar producto"><i class="bi bi-trash3" aria-hidden="true"></i></button></td>`;
        lines?.appendChild(row);
        form.dataset.nextItemIndex = String(itemIndex + 1);
        picker.value = "";
        renderTotals(form);
    };

    /**
     * Ejecuta la operación initializeForm utilizada por el módulo JavaScript.
     *
     * @param {*} form - Parámetro recibido por la función.
     *
     * @returns {*} Resultado de la operación.
     */
    // Funcion: initializeForm concentra una tarea reutilizable del modulo.
    const initializeForm = (form) => {
        // Condicional: evalua "form.dataset.salesFormInitialized === "true"" para decidir si ejecuta el bloque siguiente.
        if (form.dataset.salesFormInitialized === "true") {
            return;
        }

        form.dataset.salesFormInitialized = "true";
        form.dataset.nextItemIndex = String(linesFor(form).length);
        renderTotals(form);
        showEmptyLine(form);

        // Evento: escucha una interaccion del usuario o del navegador y ejecuta la funcion asociada cuando ocurre.
        form.querySelector("[data-sales-add-product]")?.addEventListener(
            "click",
            () => addProductLine(form),
        );
        // Evento: escucha una interaccion del usuario o del navegador y ejecuta la funcion asociada cuando ocurre.
        form.querySelector("[data-sales-product-search]")?.addEventListener(
            "input",
            (event) => {
                const term = event.target.value.toLowerCase().trim();
                form.querySelectorAll(
                    "[data-sales-product-picker] option",
                // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
                ).forEach((option, index) => {
                    option.hidden =
                        index > 0 &&
                        !option.textContent.toLowerCase().includes(term);
                });
            },
        );
        // Evento: escucha una interaccion del usuario o del navegador y ejecuta la funcion asociada cuando ocurre.
        form.querySelector("[data-sales-clear]")?.addEventListener(
            "click",
            () => {
                // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
                form.querySelectorAll("[data-sales-line]").forEach((line) =>
                    line.remove(),
                );
                showEmptyLine(form);
                renderTotals(form);
            },
        );
        // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
        form.querySelector('[name="medio_pago_id"]')?.addEventListener("change", () => updateCashPayment(form));
        // Evento: escucha el evento input y ejecuta la funcion asociada cuando ocurre.
        form.querySelector('[name="importe_pago"]')?.addEventListener("input", () => updateCashPayment(form));
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        form.addEventListener("click", (event) => {
            const quantityButton = event.target.closest(
                "[data-sales-quantity]",
            );
            const removeButton = event.target.closest("[data-sales-remove]");

            // Condicional: evalua "quantityButton" para decidir si ejecuta el bloque siguiente.
            if (quantityButton) {
                const line = quantityButton.closest("[data-sales-line]");
                const input = line?.querySelector(
                    "[data-sales-quantity-input]",
                );
                const step =
                    quantityButton.dataset.salesQuantity === "up" ? 1 : -1;

                // Condicional: evalua "input" para decidir si ejecuta el bloque siguiente.
                if (input) {
                    const min = Number(input.min || 1);
                    const max = Number(input.max || 1000);
                    input.value = String(
                        Math.max(
                            min,
                            Math.min(max, Number(input.value || 1) + step),
                        ),
                    );
                    renderTotals(form);
                }
            }

            // Condicional: evalua "removeButton" para decidir si ejecuta el bloque siguiente.
            if (removeButton) {
                removeButton.closest("[data-sales-line]")?.remove();
                showEmptyLine(form);
                renderTotals(form);
            }
        });
        // Evento: escucha el evento input y ejecuta la funcion asociada cuando ocurre.
        form.addEventListener("input", (event) => {
            // Condicional: evalua "event.target.matches("[data-sales-quantity-input]")" para decidir si ejecuta el bloque siguiente.
            if (event.target.matches("[data-sales-quantity-input]")) {
                renderTotals(form);
            }
        });
        // Evento: escucha el evento submit y ejecuta la funcion asociada cuando ocurre.
        form.addEventListener("submit", (event) => {
            // Condicional: evalua "linesFor(form).length === 0" para decidir si ejecuta el bloque siguiente.
            if (linesFor(form).length === 0) {
                event.preventDefault();
                window.alert(
                    "Agrega al menos un producto antes de guardar la venta.",
                );
            }
        });
    };

    /**
     * Gestiona la interacción y actualiza el estado visual correspondiente.
     *
     * @param {*} dialog - Parámetro recibido por la función.
     *
     * @returns {*} Resultado de la operación.
     */
    // Funcion: openDialog concentra una tarea reutilizable del modulo.
    const openDialog = (dialog) => {
        // Condicional: evalua "dialog && !dialog.open" para decidir si ejecuta el bloque siguiente.
        if (dialog && !dialog.open) {
            dialog.showModal();
        }
    };

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    salesPage.querySelectorAll("[data-sales-form]").forEach(initializeForm);
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    salesPage.querySelectorAll("[data-sales-cancel]").forEach((form) => {
        // Evento: escucha el evento submit y ejecuta la funcion asociada cuando ocurre.
        form.addEventListener("submit", (event) => {
            const reason = window.prompt(
                "Indica el motivo de la anulación (mínimo 5 caracteres):",
            );
            // Condicional: evalua "la expresion de control" para decidir si ejecuta el bloque siguiente.
            if (
                reason === null ||
                reason.trim().length < 5 ||
                reason.trim().length > 500
            ) {
                event.preventDefault();
                // Condicional: evalua "reason !== null" para decidir si ejecuta el bloque siguiente.
                if (reason !== null)
                    window.alert(
                        "El motivo debe tener entre 5 y 500 caracteres.",
                    );
                return;
            }
            form.querySelector('[name="motivo"]').value = reason.trim();
        });
    });
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    salesPage.querySelectorAll("[data-sales-confirm]").forEach((form) => {
        // Evento: escucha el evento submit y ejecuta la funcion asociada cuando ocurre.
        form.addEventListener("submit", (event) => {
            // Condicional: evalua "la expresion de control" para decidir si ejecuta el bloque siguiente.
            if (
                !window.confirm(
                    "La confirmacion descontara existencias del almacen asociado. ¿Deseas continuar?",
                )
            ) {
                event.preventDefault();
            }
        });
    });
    // Delegación de eventos: funciona también si los controles se renderizan
    // después de cargar el módulo y centraliza la apertura de todos los diálogos.
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    salesPage.addEventListener("click", (event) => {
        const createButton = event.target.closest("[data-open-sales-create]");
        const confirmButton = event.target.closest("[data-open-sales-confirm]");
        const detailsButton = event.target.closest("[data-sales-details]");
        const closeButton = event.target.closest("[data-close-sales-dialog]");

        // Condicional: evalua "createButton && !createButton.disabled" para decidir si ejecuta el bloque siguiente.
        if (createButton && !createButton.disabled) {
            event.preventDefault();
            openDialog(salesPage.querySelector("#nueva-venta"));
        }

        // Condicional: evalua "confirmButton && !confirmButton.disabled" para decidir si ejecuta el bloque siguiente.
        if (confirmButton && !confirmButton.disabled) {
            event.preventDefault();
            openDialog(
                document.getElementById(confirmButton.dataset.openSalesConfirm),
            );
        }

        // Condicional: evalua "detailsButton && !detailsButton.disabled" para decidir si ejecuta el bloque siguiente.
        if (detailsButton && !detailsButton.disabled) {
            event.preventDefault();
            openDialog(
                document.getElementById(
                    `detalle-venta-${detailsButton.dataset.salesDetails}`,
                ),
            );
        }

        // Condicional: evalua "closeButton" para decidir si ejecuta el bloque siguiente.
        if (closeButton) {
            closeButton.closest("dialog")?.close();
        }
    });
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    salesPage.querySelectorAll("dialog").forEach((dialog) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        dialog.addEventListener("click", (event) => {
            // Condicional: evalua "event.target === dialog" para decidir si ejecuta el bloque siguiente.
            if (event.target === dialog) {
                dialog.close();
            }
        });
        // Condicional: evalua "dialog.dataset.salesAutoOpen === "1"" para decidir si ejecuta el bloque siguiente.
        if (dialog.dataset.salesAutoOpen === "1") {
            openDialog(dialog);
        }
    });

    salesPage
        .querySelector("[data-sales-refresh]")
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        ?.addEventListener("click", () => {
            window.location.reload();
        });

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    salesPage.querySelectorAll("[data-sales-bar]").forEach((bar) => {
        bar.style.height = `${bar.dataset.salesHeight ?? 0}%`;
        // Evento: escucha el evento mouseenter y ejecuta la funcion asociada cuando ocurre.
        bar.addEventListener("mouseenter", () => {
            salesPage
                .querySelector("[data-sales-chart-value]")
                ?.replaceChildren(
                    document.createTextNode(
                        bar.dataset.salesValue ?? "S/ 0.00",
                    ),
                );
        });
    });
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    salesPage.querySelectorAll("[data-orders-bar]").forEach((bar) => {
        bar.style.height = `${bar.dataset.ordersHeight ?? 0}%`;
    });
    const donut = salesPage.querySelector("[data-sales-donut]");
    // Condicional: evalua "donut" para decidir si ejecuta el bloque siguiente.
    if (donut) {
        donut.style.cssText = donut.dataset.salesDonut ?? "";
    }
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    salesPage.querySelectorAll("[data-channel-color]").forEach((dot) => {
        dot.style.background = dot.dataset.channelColor ?? "";
    });
}
