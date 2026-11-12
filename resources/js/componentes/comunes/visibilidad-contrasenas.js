/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/componentes/comunes/visibilidad-contrasenas.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

/**
 * Alterna la visibilidad de campos de contraseña reutilizables.
 */
// Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
document.querySelectorAll("[data-password-toggle]").forEach((boton) => {
    const campoContrasena = boton.closest(".admin-password-input")?.querySelector("input");

    // Condicional: evalua "!campoContrasena" para decidir si ejecuta el bloque siguiente.
    if (!campoContrasena) {
        return;
    }

    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
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