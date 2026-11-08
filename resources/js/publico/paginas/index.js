import { cargarModulo } from '../../nucleo/cargador-modulos.js';

const modulosPorVista = [
    ['modulo-catalog', 'publico/catalogo/index'],
    ['modulo-producto', 'publico/catalogo/detalle'],
    ['modulo-comparador', 'inteligente/comprador/comparacion'],
    ['modulo-asistente', 'inteligente/asistente/chat'],
];

/**
 * Carga las interacciones JavaScript correspondientes a la vista pública actual.
 *
 * @returns {Promise<void>} Promesa resuelta cuando terminan de cargarse los módulos.
 */
const cargarModulosDeVista = async () => {
    const modulos = modulosPorVista
        .filter(([claseVista]) => document.body.classList.contains(claseVista))
        .map(([, rutaModulo]) => cargarModulo(rutaModulo));

    await Promise.all(modulos);
};

const inicializarVistasPublicas = () => {
    cargarModulosDeVista();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', inicializarVistasPublicas, { once: true });
} else {
    inicializarVistasPublicas();
}

export {};
