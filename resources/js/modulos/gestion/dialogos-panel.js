/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/modulos/gestion/dialogos-panel.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

/**
 * Apertura, cierre y carga de datos de los diálogos administrativos.
 */
/**
 * Ejecuta la operación abrirDialogo utilizada por el módulo JavaScript.
 *
 * @param {*} id - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: abrirDialogo concentra una tarea reutilizable del modulo.
const abrirDialogo = (id) => {
    const dialogo = typeof id === "string" ? document.getElementById(id) : null;

    // Condicional: evalua "dialogo && !dialogo.open && typeof dialogo.showModal === "function"" para decidir si ejecuta el bloque siguiente.
    if (dialogo && !dialogo.open && typeof dialogo.showModal === "function") {
        dialogo.showModal();
    }
};

// Delegación para soportar vistas cargadas dinámicamente y evitar que un
// botón quede sin evento cuando el módulo se incorpora después del HTML.
// Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
document.addEventListener("click", (evento) => {
    const abrir = evento.target.closest("[data-admin-dialog-open]");
    const cerrar = evento.target.closest("[data-admin-dialog-close]");
    const recepcionCompra = evento.target.closest("[data-purchase-reception-open]");
    const abrirProveedor = evento.target.closest("[data-open-supplier-create]");
    const editarProveedor = evento.target.closest("[data-open-supplier-edit]");
    const cerrarProveedor = evento.target.closest("[data-close-supplier-dialog]");

    // Condicional: evalua "recepcionCompra" para decidir si ejecuta el bloque siguiente.
    if (recepcionCompra) {
        const compraId = recepcionCompra.dataset.purchaseReceptionOpen;
        document.getElementById(`detalle-compra-${compraId}`)?.setAttribute("open", "");
        document.getElementById(`recepcion-parcial-${compraId}`)?.setAttribute("open", "");
    }

    // Condicional: evalua "abrir && !abrir.disabled" para decidir si ejecuta el bloque siguiente.
    if (abrir && !abrir.disabled) {
        evento.preventDefault();
        abrirDialogo(abrir.dataset.adminDialogOpen);
    }

    // Condicional: evalua "abrirProveedor && !abrirProveedor.disabled" para decidir si ejecuta el bloque siguiente.
    if (abrirProveedor && !abrirProveedor.disabled) {
        evento.preventDefault();
        abrirDialogo("nuevo-proveedor");
    }

    // Condicional: evalua "editarProveedor && !editarProveedor.disabled" para decidir si ejecuta el bloque siguiente.
    if (editarProveedor && !editarProveedor.disabled) {
        evento.preventDefault();
        abrirDialogo(`editar-proveedor-${editarProveedor.dataset.openSupplierEdit}`);
    }

    // Condicional: evalua "cerrar" para decidir si ejecuta el bloque siguiente.
    if (cerrar) {
        cerrar.closest("dialog")?.close();
    }

    // Condicional: evalua "cerrarProveedor" para decidir si ejecuta el bloque siguiente.
    if (cerrarProveedor) {
        cerrarProveedor.closest("dialog")?.close();
    }
});

// Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
document.querySelectorAll("[data-audit-id]").forEach((boton) =>
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    boton.addEventListener("click", () => {
        const valores = {
            id: `#${boton.dataset.auditId || "—"}`,
            date: boton.dataset.auditDate || "—",
            user: boton.dataset.auditUser || "—",
            action: boton.dataset.auditAction || "—",
            entity: boton.dataset.auditEntity || "—",
            ip: boton.dataset.auditIp || "—",
        };

        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        Object.entries(valores).forEach(([clave, valor]) => {
            const objetivo = document.querySelector(`[data-audit-detail="${clave}"]`);

            // Condicional: evalua "objetivo" para decidir si ejecuta el bloque siguiente.
            if (objetivo) {
                objetivo.textContent = valor;
            }
        });

        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        ["old", "new"].forEach((clave) => {
            const objetivo = document.querySelector(`[data-audit-detail="${clave}"]`);

            // Condicional: evalua "!objetivo" para decidir si ejecuta el bloque siguiente.
            if (!objetivo) {
                return;
            }

            try {
                const nombreDato = `audit${clave[0].toUpperCase()}${clave.slice(1)}`;
                const parseado = JSON.parse(boton.dataset[nombreDato] || "{}");

                objetivo.textContent = parseado && Object.keys(parseado).length
                    ? JSON.stringify(parseado, null, 2)
                    : "Sin datos";
            } catch (_) {
                objetivo.textContent = "Sin datos disponibles";
            }
        });
    })
);

// Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
document.querySelectorAll("dialog[data-auto-open='1']").forEach((dialogo) => {
    abrirDialogo(dialogo.id);
});

// Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
document.querySelectorAll("dialog").forEach((dialogo) =>
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    dialogo.addEventListener("click", (evento) => {
        // Condicional: evalua "evento.target === dialogo" para decidir si ejecuta el bloque siguiente.
        if (evento.target === dialogo) {
            dialogo.close();
        }
    })
);
