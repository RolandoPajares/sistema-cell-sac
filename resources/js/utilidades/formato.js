/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/utilidades/formato.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

/**
 * Ejecuta la operación formatoMoneda utilizada por el módulo JavaScript.
 *
 * @param {*} value - Parámetro recibido por la función.
 *
 * @param {*} currency - Parámetro recibido por la función.
 *
 * @param {*} PEN - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: formatoMoneda concentra una tarea reutilizable del modulo.
export function formatoMoneda(value, currency = 'PEN') {
    return new Intl.NumberFormat('es-PE', { style: 'currency', currency }).format(Number(value) || 0);
}

/**
 * Ejecuta la operación formatoFecha utilizada por el módulo JavaScript.
 *
 * @param {*} value - Parámetro recibido por la función.
 *
 * @param {*} options - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: formatoFecha concentra una tarea reutilizable del modulo.
export function formatoFecha(value, options = {}) {
    // Condicional: evalua "!value" para decidir si ejecuta el bloque siguiente.
    if (!value) {
        return '';
    }

    return new Intl.DateTimeFormat('es-PE', options).format(new Date(value));
}