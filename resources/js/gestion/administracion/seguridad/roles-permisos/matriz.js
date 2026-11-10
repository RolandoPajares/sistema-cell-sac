/**
 * Interacciones del módulo de roles y permisos: matriz.
 */

(() => {
    /**
     * Gestiona la matriz de permisos sin persistir cambios hasta confirmar el formulario.
     * @@returns {void} Configura los eventos y el estado visual de la matriz.
     */
    const page = document.querySelector('[data-matrix-page]');
    if (!page) return;
    /** Mantiene protegidas las asignaciones del módulo de Seguridad y Delegación. */
    const form = page.querySelector('[data-permission-form]');
    const checkboxes = () => [...(form?.querySelectorAll('input[data-permission-id]:not(:disabled)') || [])];
    const ids = () => checkboxes().filter((input) => input.checked).map((input) => Number(input.dataset.permissionId));
    let original = new Set(JSON.parse(page.dataset.currentPermissions || '[]').map(Number));
    const catalog = JSON.parse(page.dataset.previewItems || '[]');
    const roleSelector = page.querySelector('[data-role-selector]');
    const permissionDialog = page.querySelector('[data-permission-dialog]');
    const permissionList = page.querySelector('[data-permission-list]');
    const toastHost = page.querySelector('[data-matrix-toast-host]');
    let activeScreen = null;
    let activePermissionInputs = [];
    let skipConfirmation = false;

    const showToast = (message, type = 'error') => {
        const activeToastHost = permissionDialog?.open
            ? page.querySelector('[data-matrix-toast-dialog]')
            : toastHost;
        if (!activeToastHost) return;
        const toast = document.createElement('div');
        toast.className = `tc-matrix-toast tc-matrix-toast--${type}`;
        toast.setAttribute('role', type === 'error' ? 'alert' : 'status');

        const icon = document.createElement('i');
        icon.className = type === 'error' ? 'bi bi-exclamation-circle-fill' : 'bi bi-check-circle-fill';
        icon.setAttribute('aria-hidden', 'true');

        const text = document.createElement('span');
        text.textContent = message;

        const close = document.createElement('button');
        close.type = 'button';
        close.className = 'tc-matrix-toast-close';
        close.setAttribute('aria-label', 'Cerrar notificación');
        close.textContent = '×';

        const dismiss = () => toast.remove();
        close.addEventListener('click', dismiss);
        toast.append(icon, text, close);
        activeToastHost.append(toast);
        window.setTimeout(dismiss, 7000);
    };

    /** @@returns {void} Actualiza el contador y el estado pendiente de la matriz. */
    const refreshState = () => {
        const selected = new Set(ids());
        const changed = selected.size !== original.size || [...selected].some((id) => !original.has(id));
        const state = page.querySelector('[data-saved-state]');
        if (state) state.textContent = changed ? 'Hay cambios pendientes de guardar.' : `Rol editable: ${roleSelector?.selectedOptions[0]?.text || ''}. Sin cambios pendientes.`;
    };

    /** @@returns {void} Aplica la búsqueda y el filtro de módulo sobre filas del catálogo. */
    const moduleSelector = page.querySelector('[data-module-selector]');
    const setModuleExpanded = (panel, expanded) => {
        const body = panel?.querySelector('.tc-matrix-module-body');
        const button = panel?.querySelector('[data-toggle-module]');
        if (!body || !button) return;
        body.hidden = !expanded;
        button.setAttribute('aria-expanded', String(expanded));
        button.querySelector('i')?.classList.toggle('is-rotated', expanded);
    };
    const filterRows = () => {
        const text = (page.querySelector('[data-matrix-filter]')?.value || '').toLowerCase().trim();
        const selectedGroup = moduleSelector?.value || '';
        page.querySelectorAll('[data-module-panel]').forEach((panel) => {
            const matchesChild = [...panel.querySelectorAll('[data-matrix-row]')].some((row) => row.dataset.search.includes(text));
            const matchesText = text === '' || panel.dataset.search.includes(text) || matchesChild;
            const matchesGroup = selectedGroup === '' || panel.dataset.moduleCode === selectedGroup;
            panel.hidden = !matchesGroup || !matchesText;
            if (matchesText && matchesGroup && (text !== '' || selectedGroup !== '')) setModuleExpanded(panel, true);
        });

        page.querySelectorAll('[data-matrix-row]').forEach((row) => {
            row.hidden = text !== '' && !row.dataset.search.includes(text);
        });
    };

    /**
     * Compara la selección actual con una propuesta de permisos.
     * @@param {Set<number>} proposal Permisos propuestos.
     * @@returns Objeto con permisos a agregar, retirar y conservar.
     */
    const diff = (proposal) => { const current = new Set(ids()); return { agregar: [...proposal].filter((id) => !current.has(id)), retirar: [...current].filter((id) => !proposal.has(id)), conservar: [...current].filter((id) => proposal.has(id)) }; };

    const updatePermissionSummary = () => {
        const summary = page.querySelector('[data-permission-summary]');
        if (!summary || !permissionList) return;
        const inputs = [...permissionList.querySelectorAll('input[data-permission-id]')];
        const selected = inputs.filter((input) => input.checked);
        summary.replaceChildren();

        const count = document.createElement('p');
        count.className = 'tc-permission-summary-count';
        count.textContent = `${selected.length} de ${inputs.length} acciones seleccionadas para este m\u00f3dulo.`;
        summary.append(count);

        if (selected.length === 0) {
            const empty = document.createElement('p');
            empty.className = 'tc-rbac-muted';
            empty.textContent = 'Este rol no tiene acciones seleccionadas para este m\u00f3dulo.';
            summary.append(empty);
            return;
        }

        const list = document.createElement('ul');
        selected.forEach((input) => {
            const item = document.createElement('li');
            item.textContent = input.dataset.permissionLabel || input.value;
            list.append(item);
        });
        summary.append(list);
    };

    const formatAction = (action) => (action || '').replaceAll('_', ' ').replace(/^./, (letter) => letter.toLocaleUpperCase());

    const createPermissionCheckbox = (input) => {
        const label = document.createElement('label');
        label.className = 'tc-permission-matrix-choice';
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.value = input.value;
        checkbox.checked = input.checked;
        checkbox.dataset.permissionId = input.dataset.permissionId;
        checkbox.dataset.permissionLabel = input.dataset.permissionLabel || '';
        checkbox.dataset.permissionAction = input.dataset.permissionAction || '';
        checkbox.setAttribute('aria-label', `${input.dataset.permissionScreenName || 'Módulo'}: ${input.dataset.permissionLabel || formatAction(input.dataset.permissionAction)}`);
        label.append(checkbox);
        return label;
    };

    const renderPermissionOptions = (inputs, screenRecords, selectedScreenId) => {
        if (!permissionList) return;
        permissionList.replaceChildren();

        if (inputs.length === 0 && screenRecords.length === 0) {
            const empty = document.createElement('p');
            empty.className = 'tc-matrix-empty-line';
            empty.textContent = 'No hay pantallas ni acciones registradas para este m\u00f3dulo.';
            permissionList.append(empty);
            updatePermissionSummary();
            return;
        }

        const actions = [...new Set(inputs.map((input) => input.dataset.permissionAction).filter(Boolean))]
            .sort((first, second) => first.localeCompare(second, 'es'));
        const screenRows = screenRecords.map((record) => ({
            id: record.dataset.permissionScreen,
            name: record.dataset.permissionScreenName,
            isModule: false,
        }));
        const modulePermissions = inputs.filter((input) => input.dataset.permissionScreen === '');
        if (modulePermissions.length > 0) {
            screenRows.unshift({ id: '', name: 'Permisos generales del módulo', isModule: true });
        }

        const table = document.createElement('table');
        table.className = 'tc-permission-matrix-table';
        const head = document.createElement('thead');
        const headerRow = document.createElement('tr');
        const screenHeading = document.createElement('th');
        screenHeading.scope = 'col';
        screenHeading.textContent = 'Pantallas (obtenidas de la base de datos)';
        headerRow.append(screenHeading);
        actions.forEach((action) => {
            const heading = document.createElement('th');
            heading.scope = 'col';
            heading.textContent = formatAction(action);
            headerRow.append(heading);
        });
        head.append(headerRow);
        table.append(head);

        const body = document.createElement('tbody');
        screenRows.forEach((screen) => {
            const row = document.createElement('tr');
            if (screen.id === selectedScreenId) row.classList.add('is-current-screen');

            const name = document.createElement('th');
            name.scope = 'row';
            name.textContent = screen.name;
            row.append(name);

            actions.forEach((action) => {
                const cell = document.createElement('td');
                const permissions = inputs.filter((input) => input.dataset.permissionAction === action
                    && (screen.isModule
                        ? input.dataset.permissionScreen === ''
                        : input.dataset.permissionScreen === screen.id));
                if (permissions.length === 0) {
                    cell.textContent = '—';
                    cell.className = 'tc-permission-matrix-unavailable';
                } else {
                    permissions.forEach((input) => cell.append(createPermissionCheckbox(input)));
                }
                row.append(cell);
            });
            body.append(row);
        });
        table.append(body);
        permissionList.append(table);
        if (actions.length === 0) {
            const empty = document.createElement('p');
            empty.className = 'tc-matrix-empty-line';
            empty.textContent = 'No hay acciones activas registradas para estas pantallas.';
            permissionList.append(empty);
        }
        updatePermissionSummary();
    };

    const openPermissionDialog = (button) => {
        const screen = button.closest('[data-screen-id]');
        if (!screen || !permissionDialog || !permissionList) return;
        activeScreen = screen;
        page.querySelector('[data-permission-module-name]').textContent = screen.dataset.moduleName;
        page.querySelector('[data-permission-screen-name]').textContent = screen.dataset.screenName;
        activePermissionInputs = checkboxes().filter((input) => input.dataset.permissionModule === screen.dataset.permissionModule);
        const moduleScreens = [...page.querySelectorAll('[data-permission-screen-record]')]
            .filter((record) => record.dataset.permissionModule === screen.dataset.permissionModule);
        renderPermissionOptions(activePermissionInputs, moduleScreens, screen.dataset.screenId);
        page.querySelectorAll('[data-permission-tab]').forEach((tab) => tab.setAttribute('aria-selected', String(tab.dataset.permissionTab === 'permissions')));
        page.querySelectorAll('[data-permission-pane]').forEach((pane) => { pane.hidden = pane.dataset.permissionPane !== 'permissions'; });
        if (typeof permissionDialog.showModal === 'function') {
            permissionDialog.showModal();
        } else {
            permissionDialog.setAttribute('open', '');
        }
        permissionDialog.classList.add('is-open');
    };

    const closePermissionDialog = () => {
        if (!permissionDialog) return;
        if (typeof permissionDialog.close === 'function' && permissionDialog.open) {
            permissionDialog.close();
        } else {
            permissionDialog.removeAttribute('open');
        }
        permissionDialog.classList.remove('is-open');
    };

    permissionDialog?.addEventListener('close', () => permissionDialog.classList.remove('is-open'));
    page.querySelectorAll('[data-permission-tab]').forEach((tab) => tab.addEventListener('click', () => {
        page.querySelectorAll('[data-permission-tab]').forEach((item) => item.setAttribute('aria-selected', String(item === tab)));
        page.querySelectorAll('[data-permission-pane]').forEach((pane) => { pane.hidden = pane.dataset.permissionPane !== tab.dataset.permissionTab; });
        updatePermissionSummary();
    }));
    permissionList?.addEventListener('change', updatePermissionSummary);

    page.querySelector('[data-matrix-filter]')?.addEventListener('input', filterRows);
    moduleSelector?.addEventListener('change', () => {
        filterRows();
    });
    page.querySelectorAll('[data-toggle-module]').forEach((button) => button.addEventListener('click', () => {
        const panel = button.closest('[data-module-panel]');
        setModuleExpanded(panel, button.getAttribute('aria-expanded') !== 'true');
    }));
    page.querySelectorAll('[data-module-header]').forEach((header) => header.addEventListener('click', (event) => {
        if (event.target.closest('button')) return;
        const panel = header.closest('[data-module-panel]');
        const toggle = panel?.querySelector('[data-toggle-module]');
        setModuleExpanded(panel, toggle?.getAttribute('aria-expanded') !== 'true');
    }));
    page.querySelector('[data-expand-all]')?.addEventListener('click', () => page.querySelectorAll('[data-module-panel]').forEach((panel) => setModuleExpanded(panel, true)));
    page.querySelector('[data-collapse-all]')?.addEventListener('click', () => page.querySelectorAll('[data-module-panel]').forEach((panel) => setModuleExpanded(panel, false)));
    checkboxes().forEach((input) => input.addEventListener('change', refreshState));
    page.addEventListener('click', (event) => {
        const button = event.target.closest('[data-open-permission-modal]');
        if (button && page.contains(button)) {
            event.preventDefault();
            openPermissionDialog(button);
        }
    });
    page.querySelectorAll('[data-close-permission-dialog]').forEach((button) => button.addEventListener('click', closePermissionDialog));
    const saveButton = page.querySelector('[data-save-screen-permissions]');
    const savePermissions = async () => {
        if (!form) return;
        form.setAttribute('aria-busy', 'true');
        if (saveButton) saveButton.disabled = true;

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const payload = await response.json();

            if (!response.ok) {
                const message = response.status < 500 && payload.message
                    ? payload.message
                    : 'No se pudieron guardar los privilegios. Inténtalo nuevamente.';
                showToast(message);
                if (response.status >= 500) console.error('Error del servidor al actualizar los privilegios del rol.', response.status);
                return;
            }

            original = new Set(ids());
            page.dataset.currentPermissions = JSON.stringify([...original]);
            refreshState();
            renderPreview();
            closePermissionDialog();
            showToast(payload.message || 'Los privilegios del rol fueron actualizados.', 'success');
        } catch (error) {
            console.error('No se pudo completar la solicitud para actualizar los privilegios del rol.', error);
            showToast('No se pudo conectar con el servidor. Revisa tu conexión e inténtalo nuevamente.');
        } finally {
            form.removeAttribute('aria-busy');
            if (saveButton) saveButton.disabled = false;
        }
    };

    saveButton?.addEventListener('click', () => {
        if (!activeScreen || !form || !permissionList) return;
        const selected = new Set([...permissionList.querySelectorAll('input[data-permission-id]:checked')]
            .map((input) => Number(input.dataset.permissionId)));
        activePermissionInputs.forEach((input) => {
            input.checked = selected.has(Number(input.dataset.permissionId));
        });
        refreshState();
        skipConfirmation = true;
        form.requestSubmit();
    });
    page.querySelector('[data-discard-changes]')?.addEventListener('click', () => { checkboxes().forEach((input) => { input.checked = original.has(Number(input.dataset.permissionId)); }); refreshState(); });
    form?.addEventListener('submit', (event) => {
        if (skipConfirmation) {
            skipConfirmation = false;
        } else {
            const changes = diff(new Set(ids()));
            if ((changes.agregar.length || changes.retirar.length) && !window.confirm(`Se agregarán ${changes.agregar.length} permisos y se retirarán ${changes.retirar.length}. ¿Confirmas guardar la transacción?`)) {
                event.preventDefault();
                return;
            }
        }
        event.preventDefault();
        void savePermissions();
    });

    /** @@returns {void} Renderiza el menú real filtrado por la selección actual. */
    const renderPreview = () => {
        const allowed = new Set(ids());
        const visible = catalog.filter((item) => item.tipo !== 'grupo' && item.permiso_id && allowed.has(Number(item.permiso_id)));
        const content = page.querySelector('[data-preview-content]');
        if (!content) return;
        content.replaceChildren();
        if (visible.length === 0) {
            const empty = document.createElement('p');
            empty.className = 'tc-rbac-muted';
            empty.textContent = 'No hay pantallas autorizadas con la selección actual.';
            content.append(empty);
            return;
        }

        const list = document.createElement('ul');
        visible.forEach((item) => {
            const entry = document.createElement('li');
            const label = document.createElement('strong');
            label.textContent = item.etiqueta;
            entry.append(label);
            if (item.ruta) {
                const path = document.createElement('small');
                path.textContent = ` · ${item.ruta}`;
                entry.append(path);
            }
            list.append(entry);
        });
        content.append(list);
    };
    page.querySelector('[data-preview-menu]')?.addEventListener('click', () => {
        renderPreview();
        const previewDialog = page.querySelector('[data-preview-dialog]');
        if (!previewDialog) return;
        if (typeof previewDialog.showModal === 'function') previewDialog.showModal();
        else previewDialog.setAttribute('open', '');
    });
    page.querySelectorAll('[data-close-preview]').forEach((button) => button.addEventListener('click', () => {
        const dialog = page.querySelector('[data-preview-dialog]');
        if (dialog?.open && typeof dialog.close === 'function') dialog.close();
        else dialog?.removeAttribute('open');
    }));
    roleSelector?.addEventListener('change', (event) => { const selected = new Set(ids()); if (selected.size !== original.size || [...selected].some((id) => !original.has(id))) { if (!window.confirm('Hay cambios pendientes. ¿Deseas descartarlos y cambiar de rol?')) { event.preventDefault(); roleSelector.value = page.dataset.roleId; return; } } roleSelector.form.submit(); });
    refreshState();
    filterRows();
})();
