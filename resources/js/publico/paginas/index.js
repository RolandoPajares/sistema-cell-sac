/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/publico/paginas/index.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

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
// Flujo asincrono: espera el resultado de una operacion que puede tardar antes de continuar.
const cargarModulosDeVista = async () => {
    const modulos = modulosPorVista
        // Recorrido: usa filter para procesar cada elemento y obtener o actualizar datos.
        .filter(([claseVista]) => document.body.classList.contains(claseVista))
        // Recorrido: usa map para procesar cada elemento y obtener o actualizar datos.
        .map(([, rutaModulo]) => cargarModulo(rutaModulo));

    // Flujo asincrono: espera el resultado de una operacion que puede tardar antes de continuar.
    await Promise.all(modulos);
};

// Funcion: inicializarVistasPublicas concentra una tarea reutilizable del modulo.
const inicializarVistasPublicas = () => {
    cargarModulosDeVista();
};

// Condicional: evalua "document.readyState === 'loading'" para decidir si ejecuta el bloque siguiente.
if (document.readyState === 'loading') {
    // Evento: escucha el evento DOMContentLoaded y ejecuta la funcion asociada cuando ocurre.
    document.addEventListener('DOMContentLoaded', inicializarVistasPublicas, { once: true });
// Caso alternativo: atiende la situacion en la que ninguna condicion anterior resulto verdadera.
} else {
    inicializarVistasPublicas();
}

export {};
