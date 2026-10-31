export const initializeReportesFinancieros = (root) => {
    if (root && root.dataset.adminAnalysisReady !== 'true') {
        root.dataset.adminAnalysisReady = 'true';
    }
};
