/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/layouts/panel/encabezado.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

/**
 * Interacción exclusiva de los selectores de fecha del dashboard administrativo.
 * Las interacciones compartidas de Gestión viven en modulos/gestion.
 */
(() => {
    const selectorFecha = document.querySelector('[data-dashboard-date]');
    const etiquetaFecha = document.querySelector('[data-dashboard-date-label]');

    // Condicional: evalua "selectorFecha" para decidir si ejecuta el bloque siguiente.
    if (selectorFecha) {
/**
 * Ejecuta la operación abrirSelector utilizada por el módulo JavaScript.
 *
 * @returns {*} Resultado de la operación.
 */
        // Funcion: abrirSelector concentra una tarea reutilizable del modulo.
        const abrirSelector = () => {
            // Condicional: evalua "typeof selectorFecha.showPicker === 'function'" para decidir si ejecuta el bloque siguiente.
            if (typeof selectorFecha.showPicker === 'function') {
                selectorFecha.showPicker();
            // Caso alternativo: atiende la situacion en la que ninguna condicion anterior resulto verdadera.
            } else {
                selectorFecha.click();
            }
        };

        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        selectorFecha.closest('.boton-fecha')?.addEventListener('click', (evento) => {
            // Condicional: evalua "evento.target !== selectorFecha" para decidir si ejecuta el bloque siguiente.
            if (evento.target !== selectorFecha) {
                evento.preventDefault();
                abrirSelector();
            }
        });

        // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
        selectorFecha.addEventListener('change', () => {
            // Condicional: evalua "!selectorFecha.value || !etiquetaFecha" para decidir si ejecuta el bloque siguiente.
            if (!selectorFecha.value || !etiquetaFecha) {
                return;
            }

            const fechaObjeto = new Date(`${selectorFecha.value}T12:00:00`);
            etiquetaFecha.textContent = fechaObjeto.toLocaleDateString('es-PE', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
            });
        });
    }

    // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
    document.querySelector('[data-dashboard-period]')?.addEventListener('change', (evento) => {
        const urlActual = new URL(window.location.href);
        urlActual.searchParams.set('months', evento.target.value);
        window.location.href = urlActual.toString();
    });
})();
