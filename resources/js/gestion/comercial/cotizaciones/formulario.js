/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/gestion/comercial/cotizaciones/formulario.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

/**
 * Gestiona únicamente la interacción visual de los formularios de cotización.
 * La validación definitiva y los importes persistidos siempre pertenecen al servidor.
 */
(() => {
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    document.querySelectorAll("[data-quotation-form]").forEach((form) => {
        // Evento: escucha una interaccion del usuario o del navegador y ejecuta la funcion asociada cuando ocurre.
        form.querySelector('[value="nuevo"]')?.addEventListener(
            "change",
            () => {
                const customerLink = form.querySelector(
                    "[data-new-customer-link]",
                );

                // Condicional: evalua "customerLink" para decidir si ejecuta el bloque siguiente.
                if (customerLink) {
                    window.location.assign(customerLink.href);
                }
            },
        );

        const items = form.querySelector("[data-quotation-items]");
        const addButton = form.querySelector("[data-add-item]");

        // Condicional: evalua "!items || !addButton" para decidir si ejecuta el bloque siguiente.
        if (!items || !addButton) {
            return;
        }

        // Funcion: refreshTotals concentra una tarea reutilizable del modulo.
        const refreshTotals = () => {
            // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
            items.querySelectorAll("[data-quotation-item]").forEach((item) => {
                const product = item.querySelector("[data-product-select]");
                const quantity = item.querySelector("[data-quantity]");
                const output = item.querySelector("[data-line-total]");
                const price = Number(
                    product?.selectedOptions[0]?.dataset.price ?? 0,
                );
                const amount = Number(quantity?.value ?? 0);

                // Condicional: evalua "output" para decidir si ejecuta el bloque siguiente.
                if (output) {
                    output.textContent = `S/ ${(price * amount).toFixed(2)}`;
                }
            });
        };

        // Funcion: renumber concentra una tarea reutilizable del modulo.
        const renumber = () => {
            items
                .querySelectorAll("[data-quotation-item]")
                // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
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

        // Funcion: bindItem concentra una tarea reutilizable del modulo.
        const bindItem = (item) => {
            // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
            item.querySelectorAll("select, input").forEach((control) => {
                // Evento: escucha el evento input y ejecuta la funcion asociada cuando ocurre.
                control.addEventListener("input", refreshTotals);
                // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
                control.addEventListener("change", refreshTotals);
            });
            // Evento: escucha una interaccion del usuario o del navegador y ejecuta la funcion asociada cuando ocurre.
            item.querySelector("[data-remove-item]")?.addEventListener(
                "click",
                () => {
                    // Condicional: evalua "la expresion de control" para decidir si ejecuta el bloque siguiente.
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

        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        items.querySelectorAll("[data-quotation-item]").forEach(bindItem);
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
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
