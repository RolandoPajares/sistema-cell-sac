/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/gestion/administracion/roles-permisos/index.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

/**
 * Ejecuta la operación initializeRolesPermisos utilizada por el módulo JavaScript.
 *
 * @param {*} root - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: initializeRolesPermisos concentra una tarea reutilizable del modulo.
export const initializeRolesPermisos = (root) => {
    initializeSystemFilterStatus(root, 'roles y permisos');
};

/**
 * Ejecuta la operación initializeSystemFilterStatus utilizada por el módulo JavaScript.
 *
 * @param {*} root - Parámetro recibido por la función.
 *
 * @param {*} moduleName - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: initializeSystemFilterStatus concentra una tarea reutilizable del modulo.
const initializeSystemFilterStatus = (root, moduleName) => {
    // Condicional: evalua "!root || root.dataset.adminSystemRolesReady === 'true'" para decidir si ejecuta el bloque siguiente.
    if (!root || root.dataset.adminSystemRolesReady === 'true') {
        return;
    }

    root.dataset.adminSystemRolesReady = 'true';
    const status = root.querySelector('[data-admin-system-filter-status]');
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    root.querySelectorAll('.admin-system-filters [data-pending-filter]').forEach((filter) => {
        // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
        filter.addEventListener('change', () => {
            // Condicional: evalua "status" para decidir si ejecuta el bloque siguiente.
            if (status) {
                status.textContent = `Filtro de ${moduleName}: ${filter.value}. Se aplicará al conectar el módulo.`;
            }
        });
    });
};