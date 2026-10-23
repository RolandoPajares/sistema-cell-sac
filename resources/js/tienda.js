import './layouts/publico/encabezado.js';
import './componentes/publico/publicidad.js';
import { cargarModulo } from './nucleo/cargador-modulos.js';

const modulosPorPagina = [
    ['modulo-catalog', 'publico/catalogo/index'],
    ['modulo-producto', 'publico/catalogo/detalle'],
    ['modulo-comparador', 'publico/catalogo/comparar'],
    ['modulo-recomendador', 'publico/catalogo/recomendaciones'],
    ['modulo-contacto', 'publico/paginas/contacto'],
    ['modulo-carrito', 'publico/comercio/carrito'],
    ['modulo-checkout', 'publico/comercio/finalizar-compra'],
    ['modulo-pedidos', 'publico/comercio/pedidos'],
    ['modulo-asistente', 'publico/asistente/index'],
    ['modulo-mayorista', 'publico/inteligente/mayorista'],
    ['modulo-optimizador', 'publico/inteligente/optimizador'],
];

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

const cargarModulosDePagina = async () => {
    const modulos = modulosPorPagina
        .filter(([clase]) => document.body.classList.contains(clase))
        .map(([, ruta]) => cargarModulo(ruta));

    await Promise.all(modulos);
};

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
