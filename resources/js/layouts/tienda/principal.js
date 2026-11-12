/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/layouts/tienda/principal.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

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
// Funcion: inicializarBotonSubir concentra una tarea reutilizable del modulo.
const inicializarBotonSubir = () => {
    const botonSubir = document.getElementById('toTop');

    // Condicional: evalua "!botonSubir" para decidir si ejecuta el bloque siguiente.
    if (!botonSubir) {
        return;
    }

    // Evento: escucha el evento scroll y ejecuta la funcion asociada cuando ocurre.
    window.addEventListener('scroll', () => {
        botonSubir.classList.toggle('show', window.scrollY > 500);
    });
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    botonSubir.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
};

/**
 * Ejecuta la operación inicializarTienda utilizada por el módulo JavaScript.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: inicializarTienda concentra una tarea reutilizable del modulo.
const inicializarTienda = () => {
    inicializarBotonSubir();
};

// Condicional: evalua "document.readyState === 'loading'" para decidir si ejecuta el bloque siguiente.
if (document.readyState === 'loading') {
    // Evento: escucha el evento DOMContentLoaded y ejecuta la funcion asociada cuando ocurre.
    document.addEventListener('DOMContentLoaded', inicializarTienda, { once: true });
// Caso alternativo: atiende la situacion en la que ninguna condicion anterior resulto verdadera.
} else {
    inicializarTienda();
}

export {};
