/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/gestion/comercial/ventas/operaciones-ventas.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

// Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
document.querySelectorAll("[data-ventas-diseno]").forEach((page) => {
    const search = page.querySelector("[data-oc-search]");
    const filters = [...page.querySelectorAll("[data-oc-filter]")];
    const rows = [...page.querySelectorAll("[data-oc-row]")];
    const empty = page.querySelector("[data-oc-empty]");

    /**
     * Gestiona la interacción y actualiza el estado visual correspondiente.
     *
     * @returns {*} Resultado de la operación.
     */
    // Funcion: applyFilters concentra una tarea reutilizable del modulo.
    const applyFilters = () => {
        const query = search?.value.trim().toLocaleLowerCase("es") ?? "";
        let visible = 0;

        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        rows.forEach((row) => {
            const textMatches = row.textContent
                .toLocaleLowerCase("es")
                .includes(query);
            // Funcion: filtersMatch concentra una tarea reutilizable del modulo.
            const filtersMatch = filters.every((filter) => {
                const selected = filter.value;
                const field = filter.dataset.ocFilter;
                const key = `oc${field.charAt(0).toUpperCase()}${field.slice(1)}`;

                return !selected || row.dataset[key] === selected;
            });

            row.hidden = !(textMatches && filtersMatch);
            visible += Number(!row.hidden);
        });

        // Condicional: evalua "empty" para decidir si ejecuta el bloque siguiente.
        if (empty) {
            empty.hidden = visible > 0;
        }
    };

    // Evento: escucha el evento input y ejecuta la funcion asociada cuando ocurre.
    search?.addEventListener("input", applyFilters);
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    filters.forEach((filter) =>
        // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
        filter.addEventListener("change", applyFilters),
    );
    // Evento: escucha una interaccion del usuario o del navegador y ejecuta la funcion asociada cuando ocurre.
    page.querySelector("[data-oc-apply]")?.addEventListener(
        "click",
        applyFilters,
    );
});
