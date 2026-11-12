/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/gestion/comercial/pedidos/index.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

(() => {
    const page = document.querySelector("[data-admin-orders-page]");

    // Condicional: evalua "!page" para decidir si ejecuta el bloque siguiente.
    if (!page) {
        return;
    }

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    page.querySelectorAll("[data-open-order-dialog]").forEach((button) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        button.addEventListener("click", () =>
            document
                .getElementById(button.dataset.openOrderDialog)
                ?.showModal(),
        );
    });
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    document.querySelectorAll(".pedidos-dialog").forEach((dialog) => {
        dialog
            .querySelectorAll("[data-close-order-dialog]")
            // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
            .forEach((button) =>
                // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
                button.addEventListener("click", () => dialog.close()),
            );
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        dialog.addEventListener("click", (event) => {
            // Condicional: evalua "event.target === dialog" para decidir si ejecuta el bloque siguiente.
            if (event.target === dialog) {
                dialog.close();
            }
        });
    });
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    page.querySelectorAll("[data-refresh-orders]").forEach((button) =>
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        button.addEventListener("click", () => window.location.reload()),
    );
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    page.querySelectorAll("[data-pedidos-per-page]").forEach((select) =>
        // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
        select.addEventListener("change", () => select.form?.submit()),
    );
})();
