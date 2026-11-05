/**
 * Apertura, cierre y carga de datos de los diálogos administrativos.
 */
document.querySelectorAll("[data-admin-dialog-open]").forEach((boton) => {
    boton.addEventListener("click", () =>
        document.getElementById(boton.dataset.adminDialogOpen)?.showModal()
    );
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

document.querySelectorAll("[data-admin-dialog-close]").forEach((boton) => {
    boton.addEventListener("click", () => boton.closest("dialog")?.close());
});

document.querySelectorAll("dialog[data-auto-open='1']").forEach((dialogo) =>
    dialogo.showModal()
);

document.querySelectorAll("dialog").forEach((dialogo) =>
    dialogo.addEventListener("click", (evento) => {
        if (evento.target === dialogo) {
            dialogo.close();
        }
    })
);
