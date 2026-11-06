/**
 * Ejecuta la operación csrfToken utilizada por el módulo JavaScript.
 *
 * @returns {*} Resultado de la operación.
 */
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
export async function request(url, options = {}) {
    const headers = new Headers(options.headers ?? {});

    headers.set('Accept', 'application/json');

    if (!headers.has('X-CSRF-TOKEN')) {
        headers.set('X-CSRF-TOKEN', csrfToken());
    }

    const response = await fetch(url, { ...options, headers });
    const contentType = response.headers.get('content-type') ?? '';
    const body = contentType.includes('application/json') ? await response.json() : await response.text();

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
export const post = (url, body, options = {}) => request(url, {
    ...options,
    method: 'POST',
    body: body instanceof FormData ? body : JSON.stringify(body),
    headers: {
        'Content-Type': 'application/json',
        ...(options.headers ?? {}),
    },
});