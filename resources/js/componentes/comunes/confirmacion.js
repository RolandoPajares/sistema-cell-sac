/**
 * Confirmaciones reutilizables para acciones administrativas.
 */
document.querySelectorAll("[data-confirm]").forEach((elemento) => {
    elemento.addEventListener("click", (evento) => {
        const mensajeConfirmacion = elemento.dataset.confirm || "¿Confirmas esta acción?";

        if (!confirm(mensajeConfirmacion)) {
            evento.preventDefault();
        }
    });
});
