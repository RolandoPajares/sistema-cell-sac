/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/gestion/administracion/integraciones/index.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

/**
 * Ejecuta la operación initializeIntegraciones utilizada por el módulo JavaScript.
 *
 * @param {*} root - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: initializeIntegraciones concentra una tarea reutilizable del modulo.
export const initializeIntegraciones = (root) => {
    // Condicional: evalua "!root || root.dataset.adminSystemIntegrationsReady === 'true'" para decidir si ejecuta el bloque siguiente.
    if (!root || root.dataset.adminSystemIntegrationsReady === 'true') {
        return;
    }

    root.dataset.adminSystemIntegrationsReady = 'true';
    const status = root.querySelector('[data-admin-system-filter-status]');
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    root.querySelectorAll('.admin-system-filters [data-pending-filter]').forEach((filter) => {
        // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
        filter.addEventListener('change', () => {
            // Condicional: evalua "status" para decidir si ejecuta el bloque siguiente.
            if (status) {
                status.textContent = `Filtro de integraciones: ${filter.value}. Se aplicará al conectar el módulo.`;
            }
        });
    });
};