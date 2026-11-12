/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/modulos/gestion/filtros-tablas.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

/**
 * Filtros, ordenamiento y paginación de las tablas administrativas.
 */
/**
 * Gestiona la interacción y actualiza el estado visual correspondiente.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: filtrarFilas concentra una tarea reutilizable del modulo.
const filtrarFilas = () => {
    const terminoGlobal = (
        document.querySelector("[data-admin-global-search]")?.value || ""
    )
        .trim()
        .toLocaleLowerCase("es");

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    document.querySelectorAll("[data-admin-table] tbody tr[data-data-row]").forEach((fila) => {
        const contenedor = fila.closest("[data-admin-table-container]");
        const campoBusquedaLocal = contenedor?.querySelector("[data-table-search]");
        const filtroCategoria = contenedor?.querySelector("[data-product-category-filter]");
        const filtroEstado = contenedor?.querySelector("[data-product-state-filter]");
        const filtroEstadoCategoria = contenedor?.querySelector("[data-category-status-filter]");
        const filtroCategoriaInventario = contenedor?.querySelector("[data-inventory-category-filter]");
        const filtroEstadoInventario = contenedor?.querySelector("[data-inventory-state-filter]");
        const filtroPeriodoInventario = contenedor?.querySelector("[data-inventory-period-filter]");
        const filtroEstadoPedido = contenedor?.querySelector("[data-order-status-filter]");
        const filtroPeriodoPedido = contenedor?.querySelector("[data-order-period-filter]");
        const filtroTipoCliente = contenedor?.querySelector("[data-client-type-filter]");
        const filtroEstadoCliente = contenedor?.querySelector("[data-client-state-filter]");
        const filtroEstadoProveedor = contenedor?.querySelector("[data-supplier-state-filter]");
        const filtroCiudadProveedor = contenedor?.querySelector("[data-supplier-city-filter]");
        const filtroEstadoCampania = contenedor?.querySelector("[data-campaign-status-filter]");
        const filtroUbicacionCampania = contenedor?.querySelector("[data-campaign-location-filter]");
        const filtroPeriodoCampania = contenedor?.querySelector("[data-campaign-period-filter]");
        const textoLocal = (campoBusquedaLocal?.value || "").trim().toLocaleLowerCase("es");
        const textoFila = fila.textContent.toLocaleLowerCase("es");
        const categoriaProducto = (filtroCategoria?.value || "").toLocaleLowerCase("es");
        const estadoProducto = filtroEstado?.value || "";
        const estadoCategoria = filtroEstadoCategoria?.value || "";
        const categoriaInventario = (filtroCategoriaInventario?.value || "").toLocaleLowerCase("es");
        const estadoInventario = filtroEstadoInventario?.value || "";
        const periodoInventario = filtroPeriodoInventario?.value || "all";
        const antiguedadMovimiento = Math.floor(Date.now() / 1000) - Number(fila.dataset.inventoryTime || 0);
        const periodoPedido = filtroPeriodoPedido?.value || "all";
        const antiguedadPedido = Math.floor(Date.now() / 1000) - Number(fila.dataset.orderTime || 0);
        const tipoCliente = filtroTipoCliente?.value || "";
        const estadoCliente = filtroEstadoCliente?.value || "";
        const estadoProveedor = filtroEstadoProveedor?.value || "";
        const ciudadProveedor = filtroCiudadProveedor?.value || "";
        const estadoCampania = filtroEstadoCampania?.value || "";
        const ubicacionCampania = filtroUbicacionCampania?.value || "";
        const periodoCampania = filtroPeriodoCampania?.value || "all";
        const antiguedadCampania = Math.floor(Date.now() / 1000) - Number(fila.dataset.campaignTime || 0);

        fila.hidden =
            (terminoGlobal !== "" && !textoFila.includes(terminoGlobal)) ||
            (textoLocal !== "" && !textoFila.includes(textoLocal)) ||
            (categoriaProducto !== "" && (fila.dataset.productCategory || "").toLocaleLowerCase("es") !== categoriaProducto) ||
            (estadoProducto !== "" && fila.dataset.productState !== estadoProducto) ||
            (estadoCategoria !== "" && fila.dataset.categoryStatus !== estadoCategoria) ||
            (categoriaInventario !== "" && (fila.dataset.inventoryCategory || "").toLocaleLowerCase("es") !== categoriaInventario) ||
            (estadoInventario !== "" && fila.dataset.inventoryState !== estadoInventario) ||
            (filtroPeriodoInventario && periodoInventario !== "all" && (antiguedadMovimiento < 0 || antiguedadMovimiento > Number(periodoInventario) * 86400)) ||
            (filtroEstadoPedido && filtroEstadoPedido.value !== "" && fila.dataset.orderStatus !== filtroEstadoPedido.value) ||
            (filtroPeriodoPedido && periodoPedido !== "all" && (antiguedadPedido < 0 || antiguedadPedido > Number(periodoPedido) * 86400)) ||
            (tipoCliente !== "" && fila.dataset.clientType !== tipoCliente) ||
            (estadoCliente !== "" && fila.dataset.clientState !== estadoCliente) ||
            (estadoProveedor !== "" && fila.dataset.supplierState !== estadoProveedor) ||
            (ciudadProveedor !== "" && fila.dataset.supplierCity !== ciudadProveedor) ||
            (estadoCampania !== "" && fila.dataset.campaignStatus !== estadoCampania) ||
            (ubicacionCampania !== "" && fila.dataset.campaignLocation !== ubicacionCampania) ||
            (filtroPeriodoCampania && periodoCampania !== "all" && (antiguedadCampania < 0 || antiguedadCampania > Number(periodoCampania) * 86400));
    });

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    document.querySelectorAll("[data-admin-table-container]").forEach((contenedor) => {
        const estadoVacio = contenedor.querySelector(
            "[data-product-filter-empty], [data-category-filter-empty], [data-inventory-filter-empty], [data-orders-filter-empty], [data-client-filter-empty], [data-supplier-filter-empty], [data-campaign-filter-empty]"
        );

        // Condicional: evalua "!estadoVacio" para decidir si ejecuta el bloque siguiente.
        if (!estadoVacio) {
            return;
        }

        const filasTabla = [...contenedor.querySelectorAll("[data-admin-table] tbody tr[data-data-row]")];
        estadoVacio.hidden = filasTabla.length === 0 || filasTabla.some((fila) => !fila.hidden);
        const criterioOrden = contenedor.querySelector("[data-category-order-filter]")?.value;

        // Condicional: evalua "criterioOrden" para decidir si ejecuta el bloque siguiente.
        if (criterioOrden) {
            const cuerpoTabla = contenedor.querySelector("[data-admin-table] tbody");
            const filasOrdenables = [...(cuerpoTabla?.querySelectorAll("tr[data-data-row]") || [])];

            filasOrdenables.sort((a, b) =>
                criterioOrden === "name-asc"
                    ? (a.dataset.categoryName || "").localeCompare(b.dataset.categoryName || "", "es", { sensitivity: "base" })
                    : (b.dataset.categoryCreated || "").localeCompare(a.dataset.categoryCreated || "")
            );

            // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
            filasOrdenables.forEach((fila) => cuerpoTabla.appendChild(fila));
        }
    });

    document.dispatchEvent(new CustomEvent("admin:table-filtered"));
};

// Evento: escucha el evento input y ejecuta la funcion asociada cuando ocurre.
document.querySelector("[data-admin-global-search]")?.addEventListener("input", filtrarFilas);
// Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
document.querySelectorAll("[data-table-search]").forEach((campo) =>
    // Evento: escucha el evento input y ejecuta la funcion asociada cuando ocurre.
    campo.addEventListener("input", filtrarFilas)
);
// Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
document.querySelectorAll("[data-product-category-filter], [data-product-state-filter]").forEach((filtro) =>
    // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
    filtro.addEventListener("change", filtrarFilas)
);
document.querySelectorAll(
    "[data-category-status-filter], [data-category-order-filter], [data-inventory-category-filter], [data-inventory-state-filter], [data-inventory-period-filter], [data-order-status-filter], [data-order-period-filter], [data-client-type-filter], [data-client-state-filter], [data-supplier-state-filter], [data-supplier-city-filter], [data-campaign-status-filter], [data-campaign-location-filter], [data-campaign-period-filter]"
// Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
).forEach((filtro) => filtro.addEventListener("change", filtrarFilas));

// Condicional: evalua "document.querySelector("[data-category-order-filter], [data-inventory-period-filter], [data-campaign-period-filter]")" para decidir si ejecuta el bloque siguiente.
if (document.querySelector("[data-category-order-filter], [data-inventory-period-filter], [data-campaign-period-filter]")) {
    filtrarFilas();
}

// Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
document.querySelectorAll("[data-admin-table]").forEach((tabla) => {
    const filas = [...tabla.querySelectorAll("tbody tr[data-data-row]")];
    const contenedor = tabla.closest("[data-admin-table-container]");
    const paginador = contenedor?.querySelector("[data-admin-pagination]");

    // Condicional: evalua "!paginador || filas.length <= 10" para decidir si ejecuta el bloque siguiente.
    if (!paginador || filas.length <= 10) {
        return;
    }

    let paginaActual = 1;
    const porPagina = 10;

/**
 * Ejecuta la operación filasOrdenadas utilizada por el módulo JavaScript.
 *
 * @returns {*} Resultado de la operación.
 */
    // Funcion: filasOrdenadas concentra una tarea reutilizable del modulo.
    const filasOrdenadas = () => {
        const orden = contenedor?.querySelector("[data-category-order-filter]")?.value;

        // Condicional: evalua "!orden" para decidir si ejecuta el bloque siguiente.
        if (!orden) {
            return filas;
        }

        return [...filas].sort((a, b) =>
            orden === "name-asc"
                ? (a.dataset.categoryName || "").localeCompare(b.dataset.categoryName || "", "es", { sensitivity: "base" })
                : (b.dataset.categoryCreated || "").localeCompare(a.dataset.categoryCreated || "")
        );
    };

/**
 * Ejecuta la operación renderizarPaginacion utilizada por el módulo JavaScript.
 *
 * @returns {*} Resultado de la operación.
 */
    // Funcion: renderizarPaginacion concentra una tarea reutilizable del modulo.
    const renderizarPaginacion = () => {
        // Recorrido: usa filter para procesar cada elemento y obtener o actualizar datos.
        const filasVisibles = filasOrdenadas().filter((fila) => !fila.hidden);
        const totalPaginas = Math.max(1, Math.ceil(filasVisibles.length / porPagina));

        paginaActual = Math.min(paginaActual, totalPaginas);

        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        filas.forEach((fila) => {
            // Condicional: evalua "!fila.hidden" para decidir si ejecuta el bloque siguiente.
            if (!fila.hidden) {
                fila.style.display = "none";
            }
        });

        filasVisibles
            .slice((paginaActual - 1) * porPagina, paginaActual * porPagina)
            // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
            .forEach((fila) => {
                fila.style.display = "";
            });

        paginador.innerHTML = `
            <span>Mostrando ${filasVisibles.length ? (paginaActual - 1) * porPagina + 1 : 0}-${Math.min(paginaActual * porPagina, filasVisibles.length)} de ${filasVisibles.length}</span>
            <div>
                <button type="button" data-prev aria-label="Página anterior">‹</button>
                <b>${paginaActual} / ${totalPaginas}</b>
                <button type="button" data-next aria-label="Página siguiente">›</button>
            </div>
        `;

        const botonAnterior = paginador.querySelector("[data-prev]");
        const botonSiguiente = paginador.querySelector("[data-next]");

        // Condicional: evalua "botonAnterior" para decidir si ejecuta el bloque siguiente.
        if (botonAnterior) {
            botonAnterior.disabled = paginaActual === 1;
            botonAnterior.onclick = () => {
                paginaActual -= 1;
                renderizarPaginacion();
            };
        }

        // Condicional: evalua "botonSiguiente" para decidir si ejecuta el bloque siguiente.
        if (botonSiguiente) {
            botonSiguiente.disabled = paginaActual === totalPaginas;
            botonSiguiente.onclick = () => {
                paginaActual += 1;
                renderizarPaginacion();
            };
        }
    };

    // Evento: escucha el evento admin:table-filtered y ejecuta la funcion asociada cuando ocurre.
    document.addEventListener("admin:table-filtered", () => {
        paginaActual = 1;
        renderizarPaginacion();
    });

    renderizarPaginacion();
});