export const initializeRespaldos = (root) => {
    if (!root || root.dataset.adminSystemBackupsReady === 'true') {
        return;
    }

    root.dataset.adminSystemBackupsReady = 'true';
    const status = root.querySelector('[data-admin-system-filter-status]');
    root.querySelectorAll('.admin-system-filters [data-pending-filter]').forEach((filter) => {
        filter.addEventListener('change', () => {
            if (status) {
                status.textContent = `Filtro de respaldos: ${filter.value}. Se aplicará al conectar el módulo.`;
            }
        });
    });
};
