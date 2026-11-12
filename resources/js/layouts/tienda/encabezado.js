/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/layouts/tienda/encabezado.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

/**
 * Ejecuta la operación inicializarEncabezado utilizada por el módulo JavaScript.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: inicializarEncabezado concentra una tarea reutilizable del modulo.
const inicializarEncabezado = () => {
    const botonMenu = document.querySelector('.menu-toggle');
    const navegacion = document.querySelector('.navlinks');

    // Condicional: evalua "!botonMenu || !navegacion" para decidir si ejecuta el bloque siguiente.
    if (!botonMenu || !navegacion) {
        return;
    }

    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    botonMenu.addEventListener('click', (evento) => {
        const abierta = navegacion.classList.toggle('open');

        evento.currentTarget.setAttribute('aria-expanded', abierta ? 'true' : 'false');
    });
};

// Condicional: evalua "document.readyState === 'loading'" para decidir si ejecuta el bloque siguiente.
if (document.readyState === 'loading') {
    // Evento: escucha el evento DOMContentLoaded y ejecuta la funcion asociada cuando ocurre.
    document.addEventListener('DOMContentLoaded', inicializarEncabezado, { once: true });
// Caso alternativo: atiende la situacion en la que ninguna condicion anterior resulto verdadera.
} else {
    inicializarEncabezado();
}