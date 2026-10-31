export const initializeRolesPermisos = (root) => {
    initializeSystemFilterStatus(root, 'roles y permisos');
};

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
