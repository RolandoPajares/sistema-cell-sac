/**
 * Ejecuta la operación initializeMetricasMarketing utilizada por el módulo JavaScript.
 *
 * @param {*} root - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
export const initializeMetricasMarketing = (root) => {
    if (root && root.dataset.adminAnalysisReady !== 'true') {
        root.dataset.adminAnalysisReady = 'true';
    }
};