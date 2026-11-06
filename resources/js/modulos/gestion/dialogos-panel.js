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
const abrirDialogo = (id) => {
    const dialogo = typeof id === "string" ? document.getElementById(id) : null;

    if (dialogo && !dialogo.open && typeof dialogo.showModal === "function") {
        dialogo.showModal();
    }
};

// Delegación para soportar vistas cargadas dinámicamente y evitar que un
// botón quede sin evento cuando el módulo se incorpora después del HTML.
document.addEventListener("click", (evento) => {
    const abrir = evento.target.closest("[data-admin-dialog-open]");
    const cerrar = evento.target.closest("[data-admin-dialog-close]");

    if (abrir && !abrir.disabled) {
        evento.preventDefault();
        abrirDialogo(abrir.dataset.adminDialogOpen);
    }

    if (cerrar) {
        cerrar.closest("dialog")?.close();
    }
});

document.querySelectorAll("[data-audit-id]").forEach((boton) =>
    boton.addEventListener("click", () => {
        const valores = {
            id: `#${boton.dataset.auditId || "—"}`,
            date: boton.dataset.auditDate || "—",
            user: boton.dataset.auditUser || "—",
            action: boton.dataset.auditAction || "—",
            entity: boton.dataset.auditEntity || "—",
            ip: boton.dataset.auditIp || "—",
        };

        Object.entries(valores).forEach(([clave, valor]) => {
            const objetivo = document.querySelector(`[data-audit-detail="${clave}"]`);

            if (objetivo) {
                objetivo.textContent = valor;
            }
        });

        ["old", "new"].forEach((clave) => {
            const objetivo = document.querySelector(`[data-audit-detail="${clave}"]`);

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

document.querySelectorAll("dialog[data-auto-open='1']").forEach((dialogo) => {
    abrirDialogo(dialogo.id);
});

document.querySelectorAll("dialog").forEach((dialogo) =>
    dialogo.addEventListener("click", (evento) => {
        if (evento.target === dialogo) {
            dialogo.close();
        }
    })
);