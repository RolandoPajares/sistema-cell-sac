/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/gestion/configuracion/index.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

import { initializeConfiguracionComercial } from './comercial/index.js';
import { initializeConfiguracionEmpresa } from './empresa/index.js';
import { initializeConfiguracionGeneral } from './general/index.js';
import { initializeConfiguracionSeguridad } from './seguridad/index.js';

const initializers = {
    'configuracion-empresa': initializeConfiguracionEmpresa,
    'configuracion-comercial': initializeConfiguracionComercial,
    'configuracion-seguridad': initializeConfiguracionSeguridad,
    'configuracion-general': initializeConfiguracionGeneral,
};

// Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
document.querySelectorAll('[data-admin-settings]').forEach((root) => {
    const initialize = initializers[root.dataset.adminSettings];

    // Condicional: evalua "initialize" para decidir si ejecuta el bloque siguiente.
    if (initialize) {
        initialize(root);
    }

    const form = root.querySelector('[data-settings-form]');
    const feedback = root.querySelector('[data-settings-feedback]');
    const fileInput = root.querySelector('[data-settings-file]');
    const fileTrigger = root.querySelector('[data-settings-file-trigger]');
    const fileName = root.querySelector('[data-settings-file-name]');

    // Condicional: evalua "!form || root.dataset.adminSettingsBehaviorReady === 'true'" para decidir si ejecuta el bloque siguiente.
    if (!form || root.dataset.adminSettingsBehaviorReady === 'true') {
        return;
    }

    root.dataset.adminSettingsBehaviorReady = 'true';

/**
 * Recupera y presenta el detalle de la información solicitada.
 *
 * @param {*} message - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
    // Funcion: showFeedback concentra una tarea reutilizable del modulo.
    const showFeedback = (message) => {
        // Condicional: evalua "!feedback" para decidir si ejecuta el bloque siguiente.
        if (!feedback) {
            return;
        }

        feedback.textContent = message;
        feedback.hidden = false;

        window.setTimeout(() => {
            feedback.hidden = true;
        }, 3800);
    };

    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    root.querySelector('[data-settings-reset]')?.addEventListener('click', () => {
        form.reset();
        showFeedback('Cambios descartados en esta vista. No se modificó información del sistema.');
    });

    // Evento: escucha el evento submit y ejecuta la funcion asociada cuando ocurre.
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        showFeedback('La configuración está preparada visualmente; el guardado requiere integración backend.');
    });

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    root.querySelectorAll('[data-settings-notice]').forEach((button) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        button.addEventListener('click', () => showFeedback(button.dataset.settingsNotice));
    });

    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    fileTrigger?.addEventListener('click', () => fileInput?.click());
    // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
    fileInput?.addEventListener('change', () => {
        // Condicional: evalua "fileName" para decidir si ejecuta el bloque siguiente.
        if (fileName) {
            fileName.textContent = fileInput.files?.[0]?.name ?? 'Sin archivo seleccionado';
        }
    });
});
