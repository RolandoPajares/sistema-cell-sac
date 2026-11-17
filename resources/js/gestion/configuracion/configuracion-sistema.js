/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/gestion/configuracion/configuracion-sistema.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

// Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
document.querySelectorAll('[data-configuracion-sistema] button[disabled]').forEach((boton) => {
    boton.setAttribute('aria-disabled', 'true');
});

// Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
document.querySelectorAll('[data-cs-open-integration]').forEach((boton) => {
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    boton.addEventListener('click', () => {
        const dialogo = document.querySelector('#nueva-integracion');
        // Condicional: evalua "dialogo && !dialogo.open" para decidir si ejecuta el bloque siguiente.
        if (dialogo && !dialogo.open) {
            dialogo.showModal();
        }
    });
});

// Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
document.querySelectorAll('#nueva-integracion').forEach((dialogo) => {
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    dialogo.addEventListener('click', (evento) => {
        // Condicional: evalua "evento.target === dialogo" para decidir si ejecuta el bloque siguiente.
        if (evento.target === dialogo) {
            dialogo.close();
        }
    });
});