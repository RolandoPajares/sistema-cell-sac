/**
 * Alterna la visibilidad de campos de contraseña reutilizables.
 */
document.querySelectorAll("[data-password-toggle]").forEach((boton) => {
    const campoContrasena = boton.closest(".admin-password-input")?.querySelector("input");

    if (!campoContrasena) {
        return;
    }

    boton.addEventListener("click", () => {
        const mostrarTexto = campoContrasena.type === "password";

        campoContrasena.type = mostrarTexto ? "text" : "password";
        boton.setAttribute(
            "aria-label",
            mostrarTexto ? "Ocultar contraseña" : "Mostrar contraseña"
        );
        boton.innerHTML = `<i class="bi ${mostrarTexto ? "bi-eye-slash" : "bi-eye"}" aria-hidden="true"></i>`;
    });
});
