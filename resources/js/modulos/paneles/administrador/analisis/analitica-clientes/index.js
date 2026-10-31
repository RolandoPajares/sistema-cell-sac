export const initializeAnaliticaClientes = (root) => {
    if (root && root.dataset.adminAnalysisReady !== 'true') {
        root.dataset.adminAnalysisReady = 'true';
    }
};
