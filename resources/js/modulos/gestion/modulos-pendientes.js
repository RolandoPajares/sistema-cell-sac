const decodeRecord = (encodedRecord) => {
    if (!encodedRecord) {
        return {};
    }

    try {
        const bytes = Uint8Array.from(window.atob(encodedRecord), (character) => character.charCodeAt(0));

        return JSON.parse(new TextDecoder().decode(bytes));
    } catch {
        return {};
    }
};

const populateModal = (modal, trigger) => {
    const record = decodeRecord(trigger?.dataset.pendingRecord);
    const form = modal.querySelector('[data-pending-backend-form]');

    modal.querySelectorAll('[data-pending-detail]').forEach((element) => {
        const key = element.dataset.pendingDetail;
        element.textContent = record[key] ?? '—';
    });

    modal.querySelectorAll('[data-pending-field]').forEach((field) => {
        const key = field.dataset.pendingField;

        if (record[key] === undefined || record[key] === null) {
            return;
        }

        field.value = record[key];
    });

    if (!form) {
        return;
    }

    form.action = trigger?.dataset.pendingAction || form.dataset.pendingDefaultAction || form.action;
    const methodOverride = form.querySelector('input[name="_method"]');

    if (methodOverride) {
        methodOverride.value = trigger?.dataset.pendingMethod || form.dataset.pendingDefaultMethod || 'PUT';
    }
};

const initializePendingModule = (root) => {
    if (!root || root.dataset.pendingModuleReady === 'true') {
        return;
    }

    root.dataset.pendingModuleReady = 'true';

    root.querySelectorAll('[data-pending-backend-form]').forEach((form) => {
        form.dataset.pendingDefaultAction = form.action;
        form.dataset.pendingDefaultMethod = form.querySelector('input[name="_method"]')?.value || 'POST';
    });

    const showFeedback = (message) => {
        const feedback = root.querySelector('[data-pending-feedback]');

        if (!feedback) {
            return;
        }

        feedback.textContent = message;
        feedback.hidden = false;

        window.setTimeout(() => {
            feedback.hidden = true;
        }, 4200);
    };

    const closeModal = (modal) => {
        if (modal?.open) {
            modal.close();
        }
    };

    const openModal = (mode, trigger) => {
        const modal = root.querySelector(`[data-pending-modal="${mode}"]`);

        if (!modal) {
            return;
        }

        populateModal(modal, trigger);

        if (typeof modal.showModal === 'function') {
            modal.showModal();
        } else {
            modal.setAttribute('open', '');
        }

        modal.querySelector('[autofocus], input, select, textarea, [data-pending-close]')?.focus();
    };

    root.addEventListener('click', (event) => {
        const openButton = event.target.closest('[data-pending-open]');
        const closeButton = event.target.closest('[data-pending-close]');
        const noticeButton = event.target.closest('[data-pending-notice]');

        if (openButton && root.contains(openButton)) {
            openModal(openButton.dataset.pendingOpen, openButton);
            return;
        }

        if (closeButton && root.contains(closeButton)) {
            closeModal(closeButton.closest('dialog'));
            return;
        }

        if (noticeButton && root.contains(noticeButton)) {
            showFeedback(noticeButton.dataset.pendingNotice || 'Esta acción queda pendiente de integración.');
            return;
        }

        const dialog = event.target.closest('dialog[data-pending-modal]');

        if (dialog && event.target === dialog) {
            closeModal(dialog);
        }
    });

    root.addEventListener('submit', (event) => {
        if (!event.target.matches('[data-pending-form]:not([data-pending-backend-form])')) {
            return;
        }

        event.preventDefault();
        showFeedback('No se guardaron datos: la integración backend de este módulo está pendiente.');
    });
};

document.querySelectorAll('[data-pending-module]').forEach(initializePendingModule);
