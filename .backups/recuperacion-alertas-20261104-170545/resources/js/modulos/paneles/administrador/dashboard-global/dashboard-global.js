const alertTabs = document.querySelector('[data-alert-tabs]');

if (alertTabs) {
    const page = alertTabs.closest('[data-dashboard-global="alertas"]');
    const tabs = [...alertTabs.querySelectorAll('[data-alert-tab]')];
    const alerts = [...page.querySelectorAll('[data-alert-kind]')];
    const emptyNotice = page.querySelector('[data-alert-empty]');
    const detailTitle = page.querySelector('[data-alert-detail-title]');
    const detailDescription = page.querySelector('[data-alert-detail-description]');
    const detailGrid = page.querySelector('.tc-dg__detail-grid');
    const recommendation = page.querySelector('.tc-dg__recommendation');
    const actionButtons = [...page.querySelectorAll('.tc-dg__alert-actions button')];
    const actionNames = ['read', 'assign', 'resolve'];
    let activeAlert = null;

    actionButtons.forEach((button, index) => {
        if (actionNames[index]) {
            button.dataset.alertAction = actionNames[index];
        }
    });

    const alertDialog = document.createElement('dialog');
    alertDialog.className = 'tc-sec-modal';
    alertDialog.dataset.alertDialog = 'true';
    alertDialog.setAttribute('aria-labelledby', 'tc-dg-alert-modal-title');
    alertDialog.innerHTML = `
        <div class="tc-sec-modal__card">
            <header class="tc-sec-modal__header">
                <div>
                    <span class="tc-sec-modal__icon" data-alert-modal-icon><i class="bi bi-bell" aria-hidden="true"></i></span>
                    <span class="tc-sec-modal__heading">
                        <small>Dashboard Global · Vista demostrativa</small>
                        <h2 id="tc-dg-alert-modal-title" data-alert-modal-title>Acción de alerta</h2>
                        <p data-alert-modal-description>La acción no se aplicará.</p>
                    </span>
                </div>
                <button class="tc-sec-modal__close" type="button" data-alert-modal-close aria-label="Cerrar formulario"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
            </header>
            <form class="tc-sec-modal__form" data-alert-demo-form novalidate>
                <div class="tc-sec-modal__demo-note"><i class="bi bi-info-circle" aria-hidden="true"></i><span>Funcionalidad visual. No se guardan datos ni se modifica el estado de la alerta.</span></div>
                <div class="tc-sec-modal__warning" data-alert-modal-warning role="alert"></div>
                <div class="tc-sec-modal__fields" data-alert-modal-fields></div>
                <div class="tc-sec-modal__errors" data-alert-modal-errors role="alert" hidden></div>
                <footer class="tc-sec-modal__footer">
                    <button class="tc-sec__button" type="button" data-alert-modal-close>Cancelar</button>
                    <span class="tc-sec-modal__footer-spacer"></span>
                    <button class="tc-sec__button tc-sec__button--primary" type="button" data-alert-modal-confirm>Confirmar (demostración)</button>
                </footer>
            </form>
        </div>`;
    page.append(alertDialog);

    const alertForm = alertDialog.querySelector('[data-alert-demo-form]');
    const modalTitle = alertDialog.querySelector('[data-alert-modal-title]');
    const modalDescription = alertDialog.querySelector('[data-alert-modal-description]');
    const modalWarning = alertDialog.querySelector('[data-alert-modal-warning]');
    const modalFields = alertDialog.querySelector('[data-alert-modal-fields]');
    const modalErrors = alertDialog.querySelector('[data-alert-modal-errors]');
    const feedback = document.createElement('div');
    feedback.className = 'tc-dg__design-notice';
    feedback.dataset.alertFeedback = 'true';
    feedback.setAttribute('role', 'status');
    feedback.setAttribute('aria-live', 'polite');
    feedback.hidden = true;
    page.append(feedback);

    const addAlertField = ({ id, labelText, type, required = false, hint = '' }) => {
        const wrapper = document.createElement('div');
        wrapper.className = 'tc-sec-modal__field tc-sec-modal__field--wide';
        const label = document.createElement('label');
        label.className = 'tc-sec-modal__label';
        label.htmlFor = id;
        label.textContent = labelText;
        const control = document.createElement(type === 'textarea' ? 'textarea' : 'input');
        control.id = id;
        control.name = id;
        control.dataset.alertRequired = String(required);
        control.setAttribute('aria-describedby', `${id}-error`);
        if (type === 'textarea') {
            control.rows = 4;
        } else {
            control.type = type;
        }
        wrapper.append(label, control);

        if (hint) {
            const help = document.createElement('small');
            help.className = 'tc-sec-modal__hint';
            help.textContent = hint;
            wrapper.append(help);
        }

        const error = document.createElement('small');
        error.className = 'tc-sec-modal__field-error';
        error.id = `${id}-error`;
        error.setAttribute('role', 'alert');
        error.textContent = 'Completa este campo para continuar.';
        error.hidden = true;
        wrapper.append(error);
        modalFields.append(wrapper);
        return control;
    };

    const addAlertConfirmation = () => {
        const wrapper = document.createElement('div');
        wrapper.className = 'tc-sec-modal__field tc-sec-modal__field--wide';
        const choice = document.createElement('label');
        choice.className = 'tc-sec-modal__choice';
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.id = 'tc-dg-alert-confirmation';
        checkbox.dataset.alertRequired = 'true';
        checkbox.setAttribute('aria-describedby', 'tc-dg-alert-confirmation-error');
        choice.append(checkbox, document.createTextNode('Confirmo que esta acción es solo visual y no cambiará la alerta.'));
        const error = document.createElement('small');
        error.className = 'tc-sec-modal__field-error';
        error.id = 'tc-dg-alert-confirmation-error';
        error.setAttribute('role', 'alert');
        error.textContent = 'Confirma que comprendes el alcance demostrativo.';
        error.hidden = true;
        wrapper.append(choice, error);
        modalFields.append(wrapper);
        return checkbox;
    };

    const openAlertAction = (action) => {
        activeAlert = alerts.find((alert) => alert.classList.contains('is-selected') && !alert.hidden) ?? null;
        if (!activeAlert) {
            return;
        }

        feedback.hidden = true;
        feedback.textContent = '';
        modalFields.replaceChildren();
        modalErrors.hidden = true;
        modalErrors.textContent = '';
        const context = document.createElement('p');
        context.className = 'tc-sec-modal__hint tc-sec-modal__field--wide';
        const category = activeAlert.dataset.alertKind ?? 'sin categoría';
        context.textContent = `Alerta de ejemplo: ${activeAlert.dataset.alertTitle ?? 'Sin título'} · ${category}`;
        modalFields.append(context);

        if (action === 'read') {
            modalTitle.textContent = 'Marcar como leída';
            modalDescription.textContent = 'Confirma la acción visual para la alerta seleccionada.';
            modalWarning.textContent = 'La alerta permanecerá sin cambios; no existe una operación de lectura conectada aquí.';
            addAlertConfirmation();
            alertDialog.querySelector('[data-alert-modal-confirm]').textContent = 'Confirmar (demostración)';
        } else if (action === 'assign') {
            modalTitle.textContent = 'Asignar alerta';
            modalDescription.textContent = 'Indica una referencia ilustrativa de destinatario.';
            modalWarning.textContent = 'No se cargarán usuarios o grupos reales ni se guardará una asignación.';
            addAlertField({
                id: 'tc-dg-alert-recipient',
                labelText: 'Destinatario o equipo (referencia de ejemplo)',
                type: 'text',
                required: true,
                hint: 'No hay un directorio de destinatarios conectado. Escribe solo un ejemplo.',
            });
            addAlertField({ id: 'tc-dg-alert-assignment-note', labelText: 'Observación (opcional)', type: 'textarea' });
            alertDialog.querySelector('[data-alert-modal-confirm]').textContent = 'Confirmar asignación (demostración)';
        } else if (action === 'resolve') {
            modalTitle.textContent = 'Resolver alerta';
            modalDescription.textContent = 'Registra un motivo ilustrativo para revisar la resolución.';
            modalWarning.textContent = 'No se cambiará el estado de la alerta ni se cerrará una incidencia real.';
            addAlertField({
                id: 'tc-dg-alert-resolution-reason',
                labelText: 'Motivo u observación',
                type: 'textarea',
                required: true,
                hint: 'El motivo no se guardará ni se asociará a un evento real.',
            });
            alertDialog.querySelector('[data-alert-modal-confirm]').textContent = 'Confirmar resolución (demostración)';
        } else {
            return;
        }

        if (!alertDialog.open) {
            alertDialog.showModal();
        }
        modalFields.querySelector('input, textarea')?.focus();
    };

    const selectAlert = (selectedAlert) => {
        alerts.forEach((alert) => alert.classList.toggle('is-selected', alert === selectedAlert));

        actionButtons.forEach((button) => {
            button.disabled = !selectedAlert;
            if (selectedAlert) {
                button.style.opacity = '1';
                button.style.cursor = 'pointer';
            } else {
                button.style.removeProperty('opacity');
                button.style.removeProperty('cursor');
            }
        });

        if (!selectedAlert) {
            return;
        }

        if (detailTitle) {
            detailTitle.textContent = selectedAlert.dataset.alertTitle ?? '';
        }

        if (detailDescription) {
            detailDescription.textContent = selectedAlert.dataset.alertDescription ?? '';
        }

        const showsStockDetails = selectedAlert.dataset.alertTitle === 'Stock crítico: iPhone 15 (128 GB)';

        if (detailGrid) {
            detailGrid.hidden = !showsStockDetails;
        }

        if (recommendation) {
            recommendation.hidden = !showsStockDetails;
        }

    };

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            tabs.forEach((item) => {
                item.setAttribute('aria-selected', String(item === tab));
            });

            const type = tab.dataset.alertTab;
            const visibleAlerts = alerts.filter((alert) => type === 'todas' || alert.dataset.alertKind === type);

            alerts.forEach((alert) => {
                alert.hidden = !visibleAlerts.includes(alert);
            });

            if (emptyNotice) {
                emptyNotice.hidden = visibleAlerts.length > 0;
            }

            selectAlert(visibleAlerts[0] ?? null);
        });
    });

    alerts.forEach((alert) => alert.addEventListener('click', () => selectAlert(alert)));

    selectAlert(alerts.find((alert) => alert.classList.contains('is-selected') && !alert.hidden) ?? alerts[0] ?? null);

    page.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-alert-action]');
        if (trigger) {
            openAlertAction(trigger.dataset.alertAction);
        }
    });

    alertForm.addEventListener('submit', (event) => event.preventDefault());
    alertDialog.querySelectorAll('[data-alert-modal-close]').forEach((button) => {
        button.addEventListener('click', () => alertDialog.close());
    });
    alertDialog.addEventListener('click', (event) => {
        if (event.target === alertDialog) {
            alertDialog.close();
        }
    });
    modalFields.addEventListener('input', (event) => {
        const control = event.target.closest('[data-alert-required]');
        const wrapper = control?.closest('.tc-sec-modal__field');
        if (wrapper) {
            wrapper.classList.remove('tc-sec-modal__field--invalid');
            control.removeAttribute('aria-invalid');
            wrapper.querySelector('.tc-sec-modal__field-error').hidden = true;
        }
        modalErrors.hidden = true;
    });
    alertDialog.querySelector('[data-alert-modal-confirm]').addEventListener('click', () => {
        const invalid = [...modalFields.querySelectorAll('[data-alert-required="true"]')].find((control) => (
            control.type === 'checkbox' ? !control.checked : !control.value.trim()
        ));

        if (invalid) {
            const wrapper = invalid.closest('.tc-sec-modal__field');
            wrapper.classList.add('tc-sec-modal__field--invalid');
            invalid.setAttribute('aria-invalid', 'true');
            wrapper.querySelector('.tc-sec-modal__field-error').hidden = false;
            modalErrors.textContent = 'Revisa el campo obligatorio antes de continuar.';
            modalErrors.hidden = false;
            invalid.focus();
            return;
        }

        feedback.textContent = 'Funcionalidad visual. Integración pendiente. No se realizaron cambios.';
        feedback.hidden = false;
        alertDialog.close();
        feedback.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });

    alertDialog.addEventListener('close', () => {
        activeAlert = null;
        modalFields.replaceChildren();
        modalErrors.hidden = true;
    });
}
