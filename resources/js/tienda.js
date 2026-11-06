import './layouts/publico/encabezado.js';
import './componentes/publico/publicidad.js';
import './componentes/comunes/visibilidad-contrasenas.js';
import './componentes/inteligencia/asistente/asistente-panel.js';
import { cargarModulo } from './nucleo/cargador-modulos.js';

const modulosPorPagina = [
    ['modulo-catalog', 'publico/catalogo/index'],
    ['modulo-producto', 'publico/catalogo/detalle'],
    ['modulo-comparador', 'publico/catalogo/comparar'],
    ['modulo-asistente', 'publico/asistente/index'],
];

/**
 * Ejecuta la operación inicializarBotonSubir utilizada por el módulo JavaScript.
 *
 * @returns {*} Resultado de la operación.
 */
const inicializarBotonSubir = () => {
    const botonSubir = document.getElementById('toTop');

    if (!botonSubir) {
        return;
    }

    window.addEventListener('scroll', () => {
        botonSubir.classList.toggle('show', window.scrollY > 500);
    });
    botonSubir.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
};

/**
 * Solicita o carga la información necesaria para la interacción.
 *
 * @returns {*} Resultado de la operación.
 */
const cargarModulosDePagina = async () => {
    const modulos = modulosPorPagina
        .filter(([clase]) => document.body.classList.contains(clase))
        .map(([, ruta]) => cargarModulo(ruta));

    await Promise.all(modulos);
};

/**
 * Ejecuta la operación inicializarTienda utilizada por el módulo JavaScript.
 *
 * @returns {*} Resultado de la operación.
 */
const inicializarTienda = () => {
    inicializarBotonSubir();
    cargarModulosDePagina();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', inicializarTienda, { once: true });
} else {
    inicializarTienda();
}

export {};