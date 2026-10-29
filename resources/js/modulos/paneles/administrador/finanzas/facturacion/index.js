export const initializeFacturacion = (root) => {
    if (!root || root.dataset.financeFacturacionReady === 'true') {
        return;
    }

    root.dataset.financeFacturacionReady = 'true';
    bindFinanceFilterStatus(root, 'facturación');
};

const bindFinanceFilterStatus = (root, moduleName) => {
    const status = root.querySelector('[data-finance-filter-status]');

    root.querySelectorAll('.admin-finance-filters [data-pending-filter]').forEach((filter) => {
        filter.addEventListener('change', () => {
            if (status) {
                status.textContent = `Filtro de ${moduleName}: ${filter.value}. La consulta se aplicará cuando exista integración financiera.`;
            }
        });
    });
};
