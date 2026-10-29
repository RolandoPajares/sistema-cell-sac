export const initializeIntegraciones = (root) => {
    if (!root || root.dataset.adminSystemIntegrationsReady === 'true') {
        return;
    }

    root.dataset.adminSystemIntegrationsReady = 'true';
    const status = root.querySelector('[data-admin-system-filter-status]');
    root.querySelectorAll('.admin-system-filters [data-pending-filter]').forEach((filter) => {
        filter.addEventListener('change', () => {
            if (status) {
                status.textContent = `Filtro de integraciones: ${filter.value}. Se aplicará al conectar el módulo.`;
            }
        });
    });
};
