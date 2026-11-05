export const initializeConfiguracionEmpresa = (root) => {
    initializeSettingsRoot(root);
};

const initializeSettingsRoot = (root) => {
    if (!root || root.dataset.adminSettingsReady === 'true') {
        return;
    }

    root.dataset.adminSettingsReady = 'true';
};
