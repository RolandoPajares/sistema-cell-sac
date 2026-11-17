/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/cuenta/perfil/componentes/campos-dinamicos.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

// Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
document.querySelectorAll('[data-profile-dynamic-fields] input, [data-profile-dynamic-fields] textarea, [data-profile-dynamic-fields] select').forEach((field) => {
    // Evento: escucha el evento input y ejecuta la funcion asociada cuando ocurre.
    field.addEventListener('input', () => field.setCustomValidity(''));
});
