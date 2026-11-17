/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/nucleo/cargador-modulos.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

const modulosPublicos = import.meta.glob('../publico/catalogo/*.js');
const modulosInteligencia = import.meta.glob('../inteligente/**/*.js');

/**
 * Solicita o carga la información necesaria para la interacción.
 *
 * @param {*} ruta - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
// Flujo asincrono: espera el resultado de una operacion que puede tardar antes de continuar.
export async function cargarModulo(ruta) {
    const rutaNormalizada = ruta.replace(/^\/+|\/+$/g, '');
    const esInteligencia = rutaNormalizada.startsWith('inteligente/');
    const clave = `../${rutaNormalizada}.js`;
    const modulo = (esInteligencia ? modulosInteligencia : modulosPublicos)[clave];

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
// Flujo asincrono: espera el resultado de una operacion que puede tardar antes de continuar.
export async function cargarModulosDesdePagina(selector = '[data-js-modules]') {
    const elemento = document.querySelector(selector);
    // Recorrido: usa map para procesar cada elemento y obtener o actualizar datos.
    const nombres = elemento?.dataset.jsModules?.split(',').map((nombre) => nombre.trim()).filter(Boolean) ?? [];

    // Recorrido: usa map para procesar cada elemento y obtener o actualizar datos.
    return Promise.all(nombres.map((nombre) => cargarModulo(nombre)));
}
