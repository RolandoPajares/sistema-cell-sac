export const initializeReembolsos = (root) => {
    if (!root || root.dataset.financeReembolsosReady === 'true') {
        return;
    }

    root.dataset.financeReembolsosReady = 'true';
    bindFinanceFilterStatus(root, 'reembolsos');
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
