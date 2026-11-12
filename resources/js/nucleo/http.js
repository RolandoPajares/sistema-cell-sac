/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/nucleo/http.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

/**
 * Ejecuta la operación csrfToken utilizada por el módulo JavaScript.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: csrfToken concentra una tarea reutilizable del modulo.
const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

/**
 * Solicita o carga la información necesaria para la interacción.
 *
 * @param {*} url - Parámetro recibido por la función.
 *
 * @param {*} options - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
// Flujo asincrono: espera el resultado de una operacion que puede tardar antes de continuar.
export async function request(url, options = {}) {
    const headers = new Headers(options.headers ?? {});

    headers.set('Accept', 'application/json');

    // Condicional: evalua "!headers.has('X-CSRF-TOKEN')" para decidir si ejecuta el bloque siguiente.
    if (!headers.has('X-CSRF-TOKEN')) {
        headers.set('X-CSRF-TOKEN', csrfToken());
    }

    // Peticion asincrona: envia datos al servidor con fetch y espera su respuesta sin recargar la pagina.
    const response = await fetch(url, { ...options, headers });
    const contentType = response.headers.get('content-type') ?? '';
    // Flujo asincrono: espera el resultado de una operacion que puede tardar antes de continuar.
    const body = contentType.includes('application/json') ? await response.json() : await response.text();

    // Condicional: evalua "!response.ok" para decidir si ejecuta el bloque siguiente.
    if (!response.ok) {
        const message = typeof body === 'object' && body?.message ? body.message : `HTTP ${response.status}`;
        throw new Error(message);
    }

    return body;
}

/**
 * Ejecuta la operación get utilizada por el módulo JavaScript.
 *
 * @param {*} url - Parámetro recibido por la función.
 *
 * @param {*} options - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: get concentra una tarea reutilizable del modulo.
export const get = (url, options = {}) => request(url, { ...options, method: 'GET' });

/**
 * Solicita o carga la información necesaria para la interacción.
 *
 * @param {*} url - Parámetro recibido por la función.
 *
 * @param {*} body - Parámetro recibido por la función.
 *
 * @param {*} options - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: post concentra una tarea reutilizable del modulo.
export const post = (url, body, options = {}) => request(url, {
    ...options,
    method: 'POST',
    body: body instanceof FormData ? body : JSON.stringify(body),
    headers: {
        'Content-Type': 'application/json',
        ...(options.headers ?? {}),
    },
});