import { initializeConfiguracionComercial } from './configuracion-comercial/index.js';
import { initializeConfiguracionEmpresa } from './configuracion-empresa/index.js';
import { initializeConfiguracionGeneral } from './configuracion-general/index.js';
import { initializeConfiguracionSeguridad } from './configuracion-seguridad/index.js';

const initializers = {
    'configuracion-empresa': initializeConfiguracionEmpresa,
    'configuracion-comercial': initializeConfiguracionComercial,
    'configuracion-seguridad': initializeConfiguracionSeguridad,
    'configuracion-general': initializeConfiguracionGeneral,
};

document.querySelectorAll('[data-admin-settings]').forEach((root) => {
    const initialize = initializers[root.dataset.adminSettings];

    if (initialize) {
        initialize(root);
    }

    const form = root.querySelector('[data-settings-form]');
    const feedback = root.querySelector('[data-settings-feedback]');
    const fileInput = root.querySelector('[data-settings-file]');
    const fileTrigger = root.querySelector('[data-settings-file-trigger]');
    const fileName = root.querySelector('[data-settings-file-name]');

    if (!form || root.dataset.adminSettingsBehaviorReady === 'true') {
        return;
    }

    root.dataset.adminSettingsBehaviorReady = 'true';

    const showFeedback = (message) => {
        if (!feedback) {
            return;
        }

        feedback.textContent = message;
        feedback.hidden = false;

        window.setTimeout(() => {
            feedback.hidden = true;
        }, 3800);
    };

    root.querySelector('[data-settings-reset]')?.addEventListener('click', () => {
        form.reset();
        showFeedback('Cambios descartados en esta vista. No se modificó información del sistema.');
    });

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        showFeedback('La configuración está preparada visualmente; el guardado requiere integración backend.');
    });

    root.querySelectorAll('[data-settings-notice]').forEach((button) => {
        button.addEventListener('click', () => showFeedback(button.dataset.settingsNotice));
    });

    fileTrigger?.addEventListener('click', () => fileInput?.click());
    fileInput?.addEventListener('change', () => {
        if (fileName) {
            fileName.textContent = fileInput.files?.[0]?.name ?? 'Sin archivo seleccionado';
        }
    });
});
