/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/utilidades/debounce.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

/**
 * Retrasa la ejecución de una función hasta que finaliza la espera configurada.
 *
 * @param {*} callback - Parámetro recibido por la función.
 *
 * @param {*} delay - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: debounce concentra una tarea reutilizable del modulo.
export function debounce(callback, delay = 250) {
    let timeoutId;

    return (...args) => {
        window.clearTimeout(timeoutId);
        timeoutId = window.setTimeout(() => callback(...args), delay);
    };
}