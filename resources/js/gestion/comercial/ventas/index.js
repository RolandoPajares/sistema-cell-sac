const salesPage = document.querySelector("[data-sales-page]");

if (salesPage && salesPage.dataset.salesInitialized !== "true") {
    salesPage.dataset.salesInitialized = "true";

    /**
     * Ejecuta la operación currency utilizada por el módulo JavaScript.
     *
     * @param {*} value - Parámetro recibido por la función.
     *
     * @returns {*} Resultado de la operación.
     */
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
    const linesFor = (form) => [...form.querySelectorAll("[data-sales-line]")];

    /**
     * Ejecuta la operación renderTotals utilizada por el módulo JavaScript.
     *
     * @param {*} form - Parámetro recibido por la función.
     *
     * @returns {*} Resultado de la operación.
     */
    const renderTotals = (form) => {
        let subtotal = 0;

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

            if (quantityInput) {
                quantityInput.value = String(quantity);
            }

            if (lineSubtotal) {
                lineSubtotal.textContent = currency(lineTotal);
            }

            const lineNumber = line.querySelector(".ventas-modal-line-number");
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

    const updateCashPayment = (form, total = null) => {
        const payment = form.querySelector('[name="medio_pago_id"]');
        const cashField = form.querySelector("[data-cash-payment]");
        const amountInput = form.querySelector("[data-cash-amount]");
        const change = form.querySelector("[data-cash-change]");
        const isCash = payment?.selectedOptions?.[0]?.dataset.paymentCode === "efectivo";
        if (!cashField || !amountInput) {
            return;
        }
        cashField.hidden = !isCash;
        amountInput.required = isCash;
        if (!isCash) {
            amountInput.value = "";
            if (change) change.textContent = "Vuelto: S/ 0.00";
            return;
        }
        const saleTotal = total ?? Number(form.querySelector("[data-sales-total]")?.textContent.replace(/[^0-9,.-]/g, "").replace(",", ".") || 0);
        const received = Number(amountInput.value || 0);
        if (change) change.textContent = `Vuelto: ${currency(Math.max(0, received - saleTotal))}`;
    };

    /**
     * Recupera y presenta el detalle de la información solicitada.
     *
     * @param {*} form - Parámetro recibido por la función.
     *
     * @returns {*} Resultado de la operación.
     */
    const showEmptyLine = (form) => {
        const lines = form.querySelector("[data-sales-lines]");
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
    const addProductLine = (form) => {
        const picker = form.querySelector("[data-sales-product-picker]");
        const option = picker?.selectedOptions?.[0];
        const productId = option?.value;
        const stock = Number(option?.dataset.stock ?? 0);

        if (!productId) {
            return;
        }

        if (stock < 1) {
            window.alert("Este producto no tiene stock disponible.");
            return;
        }

        const existingLine = form.querySelector(
            `[data-sales-line][data-product-id="${productId}"]`,
        );
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
    const initializeForm = (form) => {
        if (form.dataset.salesFormInitialized === "true") {
            return;
        }

        form.dataset.salesFormInitialized = "true";
        form.dataset.nextItemIndex = String(linesFor(form).length);
        renderTotals(form);
        showEmptyLine(form);

        form.querySelector("[data-sales-add-product]")?.addEventListener(
            "click",
            () => addProductLine(form),
        );
        form.querySelector("[data-sales-product-search]")?.addEventListener(
            "input",
            (event) => {
                const term = event.target.value.toLowerCase().trim();
                form.querySelectorAll(
                    "[data-sales-product-picker] option",
                ).forEach((option, index) => {
                    option.hidden =
                        index > 0 &&
                        !option.textContent.toLowerCase().includes(term);
                });
            },
        );
        form.querySelector("[data-sales-clear]")?.addEventListener(
            "click",
            () => {
                form.querySelectorAll("[data-sales-line]").forEach((line) =>
                    line.remove(),
                );
                showEmptyLine(form);
                renderTotals(form);
            },
        );
        form.querySelector('[name="medio_pago_id"]')?.addEventListener("change", () => updateCashPayment(form));
        form.querySelector('[name="importe_pago"]')?.addEventListener("input", () => updateCashPayment(form));
        form.addEventListener("click", (event) => {
            const quantityButton = event.target.closest(
                "[data-sales-quantity]",
            );
            const removeButton = event.target.closest("[data-sales-remove]");

            if (quantityButton) {
                const line = quantityButton.closest("[data-sales-line]");
                const input = line?.querySelector(
                    "[data-sales-quantity-input]",
                );
                const step =
                    quantityButton.dataset.salesQuantity === "up" ? 1 : -1;

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

            if (removeButton) {
                removeButton.closest("[data-sales-line]")?.remove();
                showEmptyLine(form);
                renderTotals(form);
            }
        });
        form.addEventListener("input", (event) => {
            if (event.target.matches("[data-sales-quantity-input]")) {
                renderTotals(form);
            }
        });
        form.addEventListener("submit", (event) => {
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
    const openDialog = (dialog) => {
        if (dialog && !dialog.open) {
            dialog.showModal();
        }
    };

    salesPage.querySelectorAll("[data-sales-form]").forEach(initializeForm);
    salesPage.querySelectorAll("[data-sales-cancel]").forEach((form) => {
        form.addEventListener("submit", (event) => {
            const reason = window.prompt(
                "Indica el motivo de la anulación (mínimo 5 caracteres):",
            );
            if (
                reason === null ||
                reason.trim().length < 5 ||
                reason.trim().length > 500
            ) {
                event.preventDefault();
                if (reason !== null)
                    window.alert(
                        "El motivo debe tener entre 5 y 500 caracteres.",
                    );
                return;
            }
            form.querySelector('[name="motivo"]').value = reason.trim();
        });
    });
    salesPage.querySelectorAll("[data-sales-confirm]").forEach((form) => {
        form.addEventListener("submit", (event) => {
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
    salesPage.addEventListener("click", (event) => {
        const createButton = event.target.closest("[data-open-sales-create]");
        const confirmButton = event.target.closest("[data-open-sales-confirm]");
        const detailsButton = event.target.closest("[data-sales-details]");
        const closeButton = event.target.closest("[data-close-sales-dialog]");

        if (createButton && !createButton.disabled) {
            event.preventDefault();
            openDialog(salesPage.querySelector("#nueva-venta"));
        }

        if (confirmButton && !confirmButton.disabled) {
            event.preventDefault();
            openDialog(
                document.getElementById(confirmButton.dataset.openSalesConfirm),
            );
        }

        if (detailsButton && !detailsButton.disabled) {
            event.preventDefault();
            openDialog(
                document.getElementById(
                    `detalle-venta-${detailsButton.dataset.salesDetails}`,
                ),
            );
        }

        if (closeButton) {
            closeButton.closest("dialog")?.close();
        }
    });
    salesPage.querySelectorAll("dialog").forEach((dialog) => {
        dialog.addEventListener("click", (event) => {
            if (event.target === dialog) {
                dialog.close();
            }
        });
        if (dialog.dataset.salesAutoOpen === "1") {
            openDialog(dialog);
        }
    });

    salesPage
        .querySelector("[data-sales-refresh]")
        ?.addEventListener("click", () => {
            window.location.reload();
        });

    salesPage.querySelectorAll("[data-sales-bar]").forEach((bar) => {
        bar.style.height = `${bar.dataset.salesHeight ?? 0}%`;
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
    salesPage.querySelectorAll("[data-orders-bar]").forEach((bar) => {
        bar.style.height = `${bar.dataset.ordersHeight ?? 0}%`;
    });
    const donut = salesPage.querySelector("[data-sales-donut]");
    if (donut) {
        donut.style.cssText = donut.dataset.salesDonut ?? "";
    }
    salesPage.querySelectorAll("[data-channel-color]").forEach((dot) => {
        dot.style.background = dot.dataset.channelColor ?? "";
    });
}
