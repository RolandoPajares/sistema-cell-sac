import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import './encabezado.js';
import './pie-pagina.js';
import '../../componentes/publico/publicidad.js';
import '../../componentes/comunes/visibilidad-contrasenas.js';
import '../../inteligente/asistente/flotante.js';
import '../../publico/paginas/index.js';

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
 * Ejecuta la operación inicializarTienda utilizada por el módulo JavaScript.
 *
 * @returns {*} Resultado de la operación.
 */
const inicializarTienda = () => {
    inicializarBotonSubir();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', inicializarTienda, { once: true });
} else {
    inicializarTienda();
}

export {};
