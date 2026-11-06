/**
 * Retrasa la ejecución de una función hasta que finaliza la espera configurada.
 *
 * @param {*} callback - Parámetro recibido por la función.
 *
 * @param {*} delay - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
export function debounce(callback, delay = 250) {
    let timeoutId;

    return (...args) => {
        window.clearTimeout(timeoutId);
        timeoutId = window.setTimeout(() => callback(...args), delay);
    };
}