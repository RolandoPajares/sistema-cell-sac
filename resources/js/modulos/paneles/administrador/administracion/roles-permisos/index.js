/**
 * Ejecuta la operación initializeRolesPermisos utilizada por el módulo JavaScript.
 *
 * @param {*} root - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
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
const initializeSystemFilterStatus = (root, moduleName) => {
    if (!root || root.dataset.adminSystemRolesReady === 'true') {
        return;
    }

    root.dataset.adminSystemRolesReady = 'true';
    const status = root.querySelector('[data-admin-system-filter-status]');
    root.querySelectorAll('.admin-system-filters [data-pending-filter]').forEach((filter) => {
        filter.addEventListener('change', () => {
            if (status) {
                status.textContent = `Filtro de ${moduleName}: ${filter.value}. Se aplicará al conectar el módulo.`;
            }
        });
    });
};