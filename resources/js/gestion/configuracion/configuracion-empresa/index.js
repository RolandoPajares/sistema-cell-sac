/**
 * Ejecuta la operación initializeConfiguracionEmpresa utilizada por el módulo JavaScript.
 *
 * @param {*} root - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
export const initializeConfiguracionEmpresa = (root) => {
    initializeSettingsRoot(root);
};

/**
 * Ejecuta la operación initializeSettingsRoot utilizada por el módulo JavaScript.
 *
 * @param {*} root - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
const initializeSettingsRoot = (root) => {
    if (!root || root.dataset.adminSettingsReady === 'true') {
        return;
    }

    root.dataset.adminSettingsReady = 'true';
};