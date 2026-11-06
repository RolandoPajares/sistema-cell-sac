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
export function formatoFecha(value, options = {}) {
    if (!value) {
        return '';
    }

    return new Intl.DateTimeFormat('es-PE', options).format(new Date(value));
}