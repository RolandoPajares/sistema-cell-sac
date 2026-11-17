/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/cuenta/perfil/componentes/foto-perfil.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

const dialog = document.querySelector('[data-profile-photo-dialog]');
const openButton = document.querySelector('[data-profile-photo-open]');
const photoInput = document.querySelector('[data-profile-photo-input]');

// Condicional: evalua "dialog && openButton" para decidir si ejecuta el bloque siguiente.
if (dialog && openButton) {
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    openButton.addEventListener('click', () => dialog.showModal());

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    dialog.querySelectorAll('[data-profile-photo-close]').forEach((button) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        button.addEventListener('click', () => dialog.close());
    });

    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    dialog.addEventListener('click', (event) => {
        // Condicional: evalua "event.target === dialog" para decidir si ejecuta el bloque siguiente.
        if (event.target === dialog) {
            dialog.close();
        }
    });
}

// Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
photoInput?.addEventListener('change', () => {
    // Condicional: evalua "photoInput.files?.length > 0" para decidir si ejecuta el bloque siguiente.
    if (photoInput.files?.length > 0) {
        photoInput.form?.requestSubmit();
    }
});
