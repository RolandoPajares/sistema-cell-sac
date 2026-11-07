const modulos = import.meta.glob('../publico/**/*.js');

/**
 * Solicita o carga la información necesaria para la interacción.
 *
 * @param {*} ruta - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
export async function cargarModulo(ruta) {
    const clave = `../publico/${ruta.replace(/^\/+|\/+$/g, '')}.js`;
    const modulo = modulos[clave];

    return modulo ? modulo() : null;
}

/**
 * Solicita o carga la información necesaria para la interacción.
 *
 * @param {*} selector - Parámetro recibido por la función.
 *
 * @param {*} data - Parámetro recibido por la función.
 *
 * @param {*} js - Parámetro recibido por la función.
 *
 * @param {*} modules - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
export async function cargarModulosDesdePagina(selector = '[data-js-modules]') {
    const elemento = document.querySelector(selector);
    const nombres = elemento?.dataset.jsModules?.split(',').map((nombre) => nombre.trim()).filter(Boolean) ?? [];

    return Promise.all(nombres.map((nombre) => cargarModulo(nombre)));
}
