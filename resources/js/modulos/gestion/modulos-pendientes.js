/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/modulos/gestion/modulos-pendientes.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

/**
 * Ejecuta la operación decodeRecord utilizada por el módulo JavaScript.
 *
 * @param {*} encodedRecord - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: decodeRecord concentra una tarea reutilizable del modulo.
const decodeRecord = (encodedRecord) => {
    // Condicional: evalua "!encodedRecord" para decidir si ejecuta el bloque siguiente.
    if (!encodedRecord) {
        return {};
    }

    try {
        // Funcion: bytes concentra una tarea reutilizable del modulo.
        const bytes = Uint8Array.from(window.atob(encodedRecord), (character) => character.charCodeAt(0));

        return JSON.parse(new TextDecoder().decode(bytes));
    } catch {
        return {};
    }
};

/**
 * Ejecuta la operación populateModal utilizada por el módulo JavaScript.
 *
 * @param {*} modal - Parámetro recibido por la función.
 *
 * @param {*} trigger - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: populateModal concentra una tarea reutilizable del modulo.
const populateModal = (modal, trigger) => {
    const record = decodeRecord(trigger?.dataset.pendingRecord);
    const form = modal.querySelector('[data-pending-backend-form]');

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    modal.querySelectorAll('[data-pending-detail]').forEach((element) => {
        const key = element.dataset.pendingDetail;
        element.textContent = record[key] ?? '—';
    });

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    modal.querySelectorAll('[data-pending-field]').forEach((field) => {
        const key = field.dataset.pendingField;

        // Condicional: evalua "record[key] === undefined || record[key] === null" para decidir si ejecuta el bloque siguiente.
        if (record[key] === undefined || record[key] === null) {
            return;
        }

        field.value = record[key];
    });

    // Condicional: evalua "!form" para decidir si ejecuta el bloque siguiente.
    if (!form) {
        return;
    }

    form.action = trigger?.dataset.pendingAction || form.dataset.pendingDefaultAction || form.action;
    const methodOverride = form.querySelector('input[name="_method"]');

    // Condicional: evalua "methodOverride" para decidir si ejecuta el bloque siguiente.
    if (methodOverride) {
        methodOverride.value = trigger?.dataset.pendingMethod || form.dataset.pendingDefaultMethod || 'PUT';
    }
};

/**
 * Ejecuta la operación initializePendingModule utilizada por el módulo JavaScript.
 *
 * @param {*} root - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: initializePendingModule concentra una tarea reutilizable del modulo.
const initializePendingModule = (root) => {
    // Condicional: evalua "!root || root.dataset.pendingModuleReady === 'true'" para decidir si ejecuta el bloque siguiente.
    if (!root || root.dataset.pendingModuleReady === 'true') {
        return;
    }

    root.dataset.pendingModuleReady = 'true';

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    root.querySelectorAll('[data-pending-backend-form]').forEach((form) => {
        form.dataset.pendingDefaultAction = form.action;
        form.dataset.pendingDefaultMethod = form.querySelector('input[name="_method"]')?.value || 'POST';
    });

/**
 * Recupera y presenta el detalle de la información solicitada.
 *
 * @param {*} message - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
    // Funcion: showFeedback concentra una tarea reutilizable del modulo.
    const showFeedback = (message) => {
        const feedback = root.querySelector('[data-pending-feedback]');

        // Condicional: evalua "!feedback" para decidir si ejecuta el bloque siguiente.
        if (!feedback) {
            return;
        }

        feedback.textContent = message;
        feedback.hidden = false;

        window.setTimeout(() => {
            feedback.hidden = true;
        }, 4200);
    };

/**
 * Gestiona la interacción y actualiza el estado visual correspondiente.
 *
 * @param {*} modal - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
    // Funcion: closeModal concentra una tarea reutilizable del modulo.
    const closeModal = (modal) => {
        // Condicional: evalua "modal?.open" para decidir si ejecuta el bloque siguiente.
        if (modal?.open) {
            modal.close();
        }
    };

/**
 * Gestiona la interacción y actualiza el estado visual correspondiente.
 *
 * @param {*} mode - Parámetro recibido por la función.
 *
 * @param {*} trigger - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
    // Funcion: openModal concentra una tarea reutilizable del modulo.
    const openModal = (mode, trigger) => {
        const modal = root.querySelector(`[data-pending-modal="${mode}"]`);

        // Condicional: evalua "!modal" para decidir si ejecuta el bloque siguiente.
        if (!modal) {
            return;
        }

        populateModal(modal, trigger);

        // Condicional: evalua "typeof modal.showModal === 'function'" para decidir si ejecuta el bloque siguiente.
        if (typeof modal.showModal === 'function') {
            modal.showModal();
        // Caso alternativo: atiende la situacion en la que ninguna condicion anterior resulto verdadera.
        } else {
            modal.setAttribute('open', '');
        }

        modal.querySelector('[autofocus], input, select, textarea, [data-pending-close]')?.focus();
    };

    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    root.addEventListener('click', (event) => {
        const openButton = event.target.closest('[data-pending-open]');
        const closeButton = event.target.closest('[data-pending-close]');
        const noticeButton = event.target.closest('[data-pending-notice]');

        // Condicional: evalua "openButton && root.contains(openButton)" para decidir si ejecuta el bloque siguiente.
        if (openButton && root.contains(openButton)) {
            openModal(openButton.dataset.pendingOpen, openButton);
            return;
        }

        // Condicional: evalua "closeButton && root.contains(closeButton)" para decidir si ejecuta el bloque siguiente.
        if (closeButton && root.contains(closeButton)) {
            closeModal(closeButton.closest('dialog'));
            return;
        }

        // Condicional: evalua "noticeButton && root.contains(noticeButton)" para decidir si ejecuta el bloque siguiente.
        if (noticeButton && root.contains(noticeButton)) {
            showFeedback(noticeButton.dataset.pendingNotice || 'Esta acción queda pendiente de integración.');
            return;
        }

        const dialog = event.target.closest('dialog[data-pending-modal]');

        // Condicional: evalua "dialog && event.target === dialog" para decidir si ejecuta el bloque siguiente.
        if (dialog && event.target === dialog) {
            closeModal(dialog);
        }
    });

    // Evento: escucha el evento submit y ejecuta la funcion asociada cuando ocurre.
    root.addEventListener('submit', (event) => {
        // Condicional: evalua "!event.target.matches('[data-pending-form]:not([data-pending-backend-form])')" para decidir si ejecuta el bloque siguiente.
        if (!event.target.matches('[data-pending-form]:not([data-pending-backend-form])')) {
            return;
        }

        event.preventDefault();
        showFeedback('No se guardaron datos: la integración backend de este módulo está pendiente.');
    });
};

// Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
document.querySelectorAll('[data-pending-module]').forEach(initializePendingModule);