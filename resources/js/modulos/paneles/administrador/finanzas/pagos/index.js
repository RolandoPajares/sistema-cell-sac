export const initializePagos = (root) => {
    if (!root || root.dataset.financePagosReady === 'true') {
        return;
    }

    root.dataset.financePagosReady = 'true';
    bindFinanceFilterStatus(root, 'pagos');
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
