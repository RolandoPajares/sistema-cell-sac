/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/gestion/administracion/seguridad/roles-permisos/matriz.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

/**
 * Interacciones del módulo de roles y permisos: matriz.
 */

(() => {
    /**
     * Gestiona la matriz de permisos sin persistir cambios hasta confirmar el formulario.
     * @@returns {void} Configura los eventos y el estado visual de la matriz.
     */
    const page = document.querySelector('[data-matrix-page]');
    // Condicional: evalua "!page" para decidir si ejecuta el bloque siguiente.
    if (!page) return;
    /** Mantiene protegidas las asignaciones del módulo de Seguridad y Delegación. */
    const form = page.querySelector('[data-permission-form]');
    // Funcion: checkboxes concentra una tarea reutilizable del modulo.
    const checkboxes = () => [...(form?.querySelectorAll('input[data-permission-id]:not(:disabled)') || [])];
    // Recorrido: usa filter para procesar cada elemento y obtener o actualizar datos.
    const ids = () => checkboxes().filter((input) => input.checked).map((input) => Number(input.dataset.permissionId));
    // Recorrido: usa map para procesar cada elemento y obtener o actualizar datos.
    let original = new Set(JSON.parse(page.dataset.currentPermissions || '[]').map(Number));
    const catalog = JSON.parse(page.dataset.previewItems || '[]');
    const roleSelector = page.querySelector('[data-role-selector]');
    const permissionDialog = page.querySelector('[data-permission-dialog]');
    const permissionList = page.querySelector('[data-permission-list]');
    const toastHost = page.querySelector('[data-matrix-toast-host]');
    let activeScreen = null;
    let activePermissionInputs = [];
    let skipConfirmation = false;

    // Funcion: showToast concentra una tarea reutilizable del modulo.
    const showToast = (message, type = 'error') => {
        const activeToastHost = permissionDialog?.open
            ? page.querySelector('[data-matrix-toast-dialog]')
            : toastHost;
        // Condicional: evalua "!activeToastHost" para decidir si ejecuta el bloque siguiente.
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

        // Funcion: dismiss concentra una tarea reutilizable del modulo.
        const dismiss = () => toast.remove();
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        close.addEventListener('click', dismiss);
        toast.append(icon, text, close);
        activeToastHost.append(toast);
        window.setTimeout(dismiss, 7000);
    };

    /** @@returns {void} Actualiza el contador y el estado pendiente de la matriz. */
    // Funcion: refreshState concentra una tarea reutilizable del modulo.
    const refreshState = () => {
        const selected = new Set(ids());
        // Funcion: changed concentra una tarea reutilizable del modulo.
        const changed = selected.size !== original.size || [...selected].some((id) => !original.has(id));
        const state = page.querySelector('[data-saved-state]');
        // Condicional: evalua "state" para decidir si ejecuta el bloque siguiente.
        if (state) state.textContent = changed ? 'Hay cambios pendientes de guardar.' : `Rol editable: ${roleSelector?.selectedOptions[0]?.text || ''}. Sin cambios pendientes.`;
    };

    /** @@returns {void} Aplica la búsqueda y el filtro de módulo sobre filas del catálogo. */
    const moduleSelector = page.querySelector('[data-module-selector]');
    // Funcion: setModuleExpanded concentra una tarea reutilizable del modulo.
    const setModuleExpanded = (panel, expanded) => {
        const body = panel?.querySelector('.tc-matrix-module-body');
        const button = panel?.querySelector('[data-toggle-module]');
        // Condicional: evalua "!body || !button" para decidir si ejecuta el bloque siguiente.
        if (!body || !button) return;
        body.hidden = !expanded;
        button.setAttribute('aria-expanded', String(expanded));
        button.querySelector('i')?.classList.toggle('is-rotated', expanded);
    };
    // Funcion: filterRows concentra una tarea reutilizable del modulo.
    const filterRows = () => {
        const text = (page.querySelector('[data-matrix-filter]')?.value || '').toLowerCase().trim();
        const selectedGroup = moduleSelector?.value || '';
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        page.querySelectorAll('[data-module-panel]').forEach((panel) => {
            // Funcion: matchesChild concentra una tarea reutilizable del modulo.
            const matchesChild = [...panel.querySelectorAll('[data-matrix-row]')].some((row) => row.dataset.search.includes(text));
            const matchesText = text === '' || panel.dataset.search.includes(text) || matchesChild;
            const matchesGroup = selectedGroup === '' || panel.dataset.moduleCode === selectedGroup;
            panel.hidden = !matchesGroup || !matchesText;
            // Condicional: evalua "matchesText && matchesGroup && (text !== '' || selectedGroup !== '')) setModuleExpanded(panel, true" para decidir si ejecuta el bloque siguiente.
            if (matchesText && matchesGroup && (text !== '' || selectedGroup !== '')) setModuleExpanded(panel, true);
        });

        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        page.querySelectorAll('[data-matrix-row]').forEach((row) => {
            row.hidden = text !== '' && !row.dataset.search.includes(text);
        });
    };

    /**
     * Compara la selección actual con una propuesta de permisos.
     * @@param {Set<number>} proposal Permisos propuestos.
     * @@returns Objeto con permisos a agregar, retirar y conservar.
     */
    // Recorrido: usa filter para procesar cada elemento y obtener o actualizar datos.
    const diff = (proposal) => { const current = new Set(ids()); return { agregar: [...proposal].filter((id) => !current.has(id)), retirar: [...current].filter((id) => !proposal.has(id)), conservar: [...current].filter((id) => proposal.has(id)) }; };

    // Funcion: updatePermissionSummary concentra una tarea reutilizable del modulo.
    const updatePermissionSummary = () => {
        const summary = page.querySelector('[data-permission-summary]');
        // Condicional: evalua "!summary || !permissionList" para decidir si ejecuta el bloque siguiente.
        if (!summary || !permissionList) return;
        const inputs = [...permissionList.querySelectorAll('input[data-permission-id]')];
        // Recorrido: usa filter para procesar cada elemento y obtener o actualizar datos.
        const selected = inputs.filter((input) => input.checked);
        summary.replaceChildren();

        const count = document.createElement('p');
        count.className = 'tc-permission-summary-count';
        count.textContent = `${selected.length} de ${inputs.length} acciones seleccionadas para este m\u00f3dulo.`;
        summary.append(count);

        // Condicional: evalua "selected.length === 0" para decidir si ejecuta el bloque siguiente.
        if (selected.length === 0) {
            const empty = document.createElement('p');
            empty.className = 'tc-rbac-muted';
            empty.textContent = 'Este rol no tiene acciones seleccionadas para este m\u00f3dulo.';
            summary.append(empty);
            return;
        }

        const list = document.createElement('ul');
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        selected.forEach((input) => {
            const item = document.createElement('li');
            item.textContent = input.dataset.permissionLabel || input.value;
            list.append(item);
        });
        summary.append(list);
    };

    // Funcion: formatAction concentra una tarea reutilizable del modulo.
    const formatAction = (action) => (action || '').replaceAll('_', ' ').replace(/^./, (letter) => letter.toLocaleUpperCase());

    // Funcion: createPermissionCheckbox concentra una tarea reutilizable del modulo.
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

    // Funcion: renderPermissionOptions concentra una tarea reutilizable del modulo.
    const renderPermissionOptions = (inputs, screenRecords, selectedScreenId) => {
        // Condicional: evalua "!permissionList" para decidir si ejecuta el bloque siguiente.
        if (!permissionList) return;
        permissionList.replaceChildren();

        // Condicional: evalua "inputs.length === 0 && screenRecords.length === 0" para decidir si ejecuta el bloque siguiente.
        if (inputs.length === 0 && screenRecords.length === 0) {
            const empty = document.createElement('p');
            empty.className = 'tc-matrix-empty-line';
            empty.textContent = 'No hay pantallas ni acciones registradas para este m\u00f3dulo.';
            permissionList.append(empty);
            updatePermissionSummary();
            return;
        }

        // Recorrido: usa map para procesar cada elemento y obtener o actualizar datos.
        const actions = [...new Set(inputs.map((input) => input.dataset.permissionAction).filter(Boolean))]
            .sort((first, second) => first.localeCompare(second, 'es'));
        // Recorrido: usa map para procesar cada elemento y obtener o actualizar datos.
        const screenRows = screenRecords.map((record) => ({
            id: record.dataset.permissionScreen,
            name: record.dataset.permissionScreenName,
            isModule: false,
        }));
        // Recorrido: usa filter para procesar cada elemento y obtener o actualizar datos.
        const modulePermissions = inputs.filter((input) => input.dataset.permissionScreen === '');
        // Condicional: evalua "modulePermissions.length > 0" para decidir si ejecuta el bloque siguiente.
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
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        actions.forEach((action) => {
            const heading = document.createElement('th');
            heading.scope = 'col';
            heading.textContent = formatAction(action);
            headerRow.append(heading);
        });
        head.append(headerRow);
        table.append(head);

        const body = document.createElement('tbody');
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        screenRows.forEach((screen) => {
            const row = document.createElement('tr');
            // Condicional: evalua "screen.id === selectedScreenId) row.classList.add('is-current-screen'" para decidir si ejecuta el bloque siguiente.
            if (screen.id === selectedScreenId) row.classList.add('is-current-screen');

            const name = document.createElement('th');
            name.scope = 'row';
            name.textContent = screen.name;
            row.append(name);

            // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
            actions.forEach((action) => {
                const cell = document.createElement('td');
                // Recorrido: usa filter para procesar cada elemento y obtener o actualizar datos.
                const permissions = inputs.filter((input) => input.dataset.permissionAction === action
                    && (screen.isModule
                        ? input.dataset.permissionScreen === ''
                        : input.dataset.permissionScreen === screen.id));
                // Condicional: evalua "permissions.length === 0" para decidir si ejecuta el bloque siguiente.
                if (permissions.length === 0) {
                    cell.textContent = '—';
                    cell.className = 'tc-permission-matrix-unavailable';
                // Caso alternativo: atiende la situacion en la que ninguna condicion anterior resulto verdadera.
                } else {
                    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
                    permissions.forEach((input) => cell.append(createPermissionCheckbox(input)));
                }
                row.append(cell);
            });
            body.append(row);
        });
        table.append(body);
        permissionList.append(table);
        // Condicional: evalua "actions.length === 0" para decidir si ejecuta el bloque siguiente.
        if (actions.length === 0) {
            const empty = document.createElement('p');
            empty.className = 'tc-matrix-empty-line';
            empty.textContent = 'No hay acciones activas registradas para estas pantallas.';
            permissionList.append(empty);
        }
        updatePermissionSummary();
    };

    // Funcion: openPermissionDialog concentra una tarea reutilizable del modulo.
    const openPermissionDialog = (button) => {
        const screen = button.closest('[data-screen-id]');
        // Condicional: evalua "!screen || !permissionDialog || !permissionList" para decidir si ejecuta el bloque siguiente.
        if (!screen || !permissionDialog || !permissionList) return;
        activeScreen = screen;
        page.querySelector('[data-permission-module-name]').textContent = screen.dataset.moduleName;
        page.querySelector('[data-permission-screen-name]').textContent = screen.dataset.screenName;
        // Recorrido: usa filter para procesar cada elemento y obtener o actualizar datos.
        activePermissionInputs = checkboxes().filter((input) => input.dataset.permissionModule === screen.dataset.permissionModule);
        const moduleScreens = [...page.querySelectorAll('[data-permission-screen-record]')]
            // Recorrido: usa filter para procesar cada elemento y obtener o actualizar datos.
            .filter((record) => record.dataset.permissionModule === screen.dataset.permissionModule);
        renderPermissionOptions(activePermissionInputs, moduleScreens, screen.dataset.screenId);
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        page.querySelectorAll('[data-permission-tab]').forEach((tab) => tab.setAttribute('aria-selected', String(tab.dataset.permissionTab === 'permissions')));
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        page.querySelectorAll('[data-permission-pane]').forEach((pane) => { pane.hidden = pane.dataset.permissionPane !== 'permissions'; });
        // Condicional: evalua "typeof permissionDialog.showModal === 'function'" para decidir si ejecuta el bloque siguiente.
        if (typeof permissionDialog.showModal === 'function') {
            permissionDialog.showModal();
        // Caso alternativo: atiende la situacion en la que ninguna condicion anterior resulto verdadera.
        } else {
            permissionDialog.setAttribute('open', '');
        }
        permissionDialog.classList.add('is-open');
    };

    // Funcion: closePermissionDialog concentra una tarea reutilizable del modulo.
    const closePermissionDialog = () => {
        // Condicional: evalua "!permissionDialog" para decidir si ejecuta el bloque siguiente.
        if (!permissionDialog) return;
        // Condicional: evalua "typeof permissionDialog.close === 'function' && permissionDialog.open" para decidir si ejecuta el bloque siguiente.
        if (typeof permissionDialog.close === 'function' && permissionDialog.open) {
            permissionDialog.close();
        // Caso alternativo: atiende la situacion en la que ninguna condicion anterior resulto verdadera.
        } else {
            permissionDialog.removeAttribute('open');
        }
        permissionDialog.classList.remove('is-open');
    };

    // Evento: escucha el evento close y ejecuta la funcion asociada cuando ocurre.
    permissionDialog?.addEventListener('close', () => permissionDialog.classList.remove('is-open'));
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    page.querySelectorAll('[data-permission-tab]').forEach((tab) => tab.addEventListener('click', () => {
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        page.querySelectorAll('[data-permission-tab]').forEach((item) => item.setAttribute('aria-selected', String(item === tab)));
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        page.querySelectorAll('[data-permission-pane]').forEach((pane) => { pane.hidden = pane.dataset.permissionPane !== tab.dataset.permissionTab; });
        updatePermissionSummary();
    }));
    // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
    permissionList?.addEventListener('change', updatePermissionSummary);

    // Evento: escucha el evento input y ejecuta la funcion asociada cuando ocurre.
    page.querySelector('[data-matrix-filter]')?.addEventListener('input', filterRows);
    // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
    moduleSelector?.addEventListener('change', () => {
        filterRows();
    });
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    page.querySelectorAll('[data-toggle-module]').forEach((button) => button.addEventListener('click', () => {
        const panel = button.closest('[data-module-panel]');
        setModuleExpanded(panel, button.getAttribute('aria-expanded') !== 'true');
    }));
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    page.querySelectorAll('[data-module-header]').forEach((header) => header.addEventListener('click', (event) => {
        // Condicional: evalua "event.target.closest('button')" para decidir si ejecuta el bloque siguiente.
        if (event.target.closest('button')) return;
        const panel = header.closest('[data-module-panel]');
        const toggle = panel?.querySelector('[data-toggle-module]');
        setModuleExpanded(panel, toggle?.getAttribute('aria-expanded') !== 'true');
    }));
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    page.querySelector('[data-expand-all]')?.addEventListener('click', () => page.querySelectorAll('[data-module-panel]').forEach((panel) => setModuleExpanded(panel, true)));
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    page.querySelector('[data-collapse-all]')?.addEventListener('click', () => page.querySelectorAll('[data-module-panel]').forEach((panel) => setModuleExpanded(panel, false)));
    // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
    checkboxes().forEach((input) => input.addEventListener('change', refreshState));
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    page.addEventListener('click', (event) => {
        const button = event.target.closest('[data-open-permission-modal]');
        // Condicional: evalua "button && page.contains(button)" para decidir si ejecuta el bloque siguiente.
        if (button && page.contains(button)) {
            event.preventDefault();
            openPermissionDialog(button);
        }
    });
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    page.querySelectorAll('[data-close-permission-dialog]').forEach((button) => button.addEventListener('click', closePermissionDialog));
    const saveButton = page.querySelector('[data-save-screen-permissions]');
    // Flujo asincrono: espera el resultado de una operacion que puede tardar antes de continuar.
    const savePermissions = async () => {
        // Condicional: evalua "!form" para decidir si ejecuta el bloque siguiente.
        if (!form) return;
        form.setAttribute('aria-busy', 'true');
        // Condicional: evalua "saveButton" para decidir si ejecuta el bloque siguiente.
        if (saveButton) saveButton.disabled = true;

        try {
            // Peticion asincrona: envia datos al servidor con fetch y espera su respuesta sin recargar la pagina.
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            // Flujo asincrono: espera el resultado de una operacion que puede tardar antes de continuar.
            const payload = await response.json();

            // Condicional: evalua "!response.ok" para decidir si ejecuta el bloque siguiente.
            if (!response.ok) {
                const message = response.status < 500 && payload.message
                    ? payload.message
                    : 'No se pudieron guardar los privilegios. Inténtalo nuevamente.';
                showToast(message);
                // Condicional: evalua "response.status >= 500) console.error('Error del servidor al actualizar los privilegios del rol.', response.status" para decidir si ejecuta el bloque siguiente.
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
            // Condicional: evalua "saveButton" para decidir si ejecuta el bloque siguiente.
            if (saveButton) saveButton.disabled = false;
        }
    };

    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    saveButton?.addEventListener('click', () => {
        // Condicional: evalua "!activeScreen || !form || !permissionList" para decidir si ejecuta el bloque siguiente.
        if (!activeScreen || !form || !permissionList) return;
        const selected = new Set([...permissionList.querySelectorAll('input[data-permission-id]:checked')]
            // Recorrido: usa map para procesar cada elemento y obtener o actualizar datos.
            .map((input) => Number(input.dataset.permissionId)));
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        activePermissionInputs.forEach((input) => {
            input.checked = selected.has(Number(input.dataset.permissionId));
        });
        refreshState();
        skipConfirmation = true;
        form.requestSubmit();
    });
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    page.querySelector('[data-discard-changes]')?.addEventListener('click', () => { checkboxes().forEach((input) => { input.checked = original.has(Number(input.dataset.permissionId)); }); refreshState(); });
    // Evento: escucha el evento submit y ejecuta la funcion asociada cuando ocurre.
    form?.addEventListener('submit', (event) => {
        // Condicional: evalua "skipConfirmation" para decidir si ejecuta el bloque siguiente.
        if (skipConfirmation) {
            skipConfirmation = false;
        // Caso alternativo: atiende la situacion en la que ninguna condicion anterior resulto verdadera.
        } else {
            const changes = diff(new Set(ids()));
            // Condicional: evalua "(changes.agregar.length || changes.retirar.length) && !window.confirm(`Se agregarán ${changes.agregar.length} permisos y se retirarán ${changes.retirar.length}. ¿Confirmas guardar la transacción?`)" para decidir si ejecuta el bloque siguiente.
            if ((changes.agregar.length || changes.retirar.length) && !window.confirm(`Se agregarán ${changes.agregar.length} permisos y se retirarán ${changes.retirar.length}. ¿Confirmas guardar la transacción?`)) {
                event.preventDefault();
                return;
            }
        }
        event.preventDefault();
        void savePermissions();
    });

    /** @@returns {void} Renderiza el menú real filtrado por la selección actual. */
    // Funcion: renderPreview concentra una tarea reutilizable del modulo.
    const renderPreview = () => {
        const allowed = new Set(ids());
        // Recorrido: usa filter para procesar cada elemento y obtener o actualizar datos.
        const visible = catalog.filter((item) => item.tipo !== 'grupo' && item.permiso_id && allowed.has(Number(item.permiso_id)));
        const content = page.querySelector('[data-preview-content]');
        // Condicional: evalua "!content" para decidir si ejecuta el bloque siguiente.
        if (!content) return;
        content.replaceChildren();
        // Condicional: evalua "visible.length === 0" para decidir si ejecuta el bloque siguiente.
        if (visible.length === 0) {
            const empty = document.createElement('p');
            empty.className = 'tc-rbac-muted';
            empty.textContent = 'No hay pantallas autorizadas con la selección actual.';
            content.append(empty);
            return;
        }

        const list = document.createElement('ul');
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        visible.forEach((item) => {
            const entry = document.createElement('li');
            const label = document.createElement('strong');
            label.textContent = item.etiqueta;
            entry.append(label);
            // Condicional: evalua "item.ruta" para decidir si ejecuta el bloque siguiente.
            if (item.ruta) {
                const path = document.createElement('small');
                path.textContent = ` · ${item.ruta}`;
                entry.append(path);
            }
            list.append(entry);
        });
        content.append(list);
    };
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    page.querySelector('[data-preview-menu]')?.addEventListener('click', () => {
        renderPreview();
        const previewDialog = page.querySelector('[data-preview-dialog]');
        // Condicional: evalua "!previewDialog" para decidir si ejecuta el bloque siguiente.
        if (!previewDialog) return;
        // Condicional: evalua "typeof previewDialog.showModal === 'function') previewDialog.showModal(" para decidir si ejecuta el bloque siguiente.
        if (typeof previewDialog.showModal === 'function') previewDialog.showModal();
        // Caso alternativo: atiende la situacion en la que ninguna condicion anterior resulto verdadera.
        else previewDialog.setAttribute('open', '');
    });
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    page.querySelectorAll('[data-close-preview]').forEach((button) => button.addEventListener('click', () => {
        const dialog = page.querySelector('[data-preview-dialog]');
        // Condicional: evalua "dialog?.open && typeof dialog.close === 'function') dialog.close(" para decidir si ejecuta el bloque siguiente.
        if (dialog?.open && typeof dialog.close === 'function') dialog.close();
        // Caso alternativo: atiende la situacion en la que ninguna condicion anterior resulto verdadera.
        else dialog?.removeAttribute('open');
    }));
    // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
    roleSelector?.addEventListener('change', (event) => { const selected = new Set(ids()); if (selected.size !== original.size || [...selected].some((id) => !original.has(id))) { if (!window.confirm('Hay cambios pendientes. ¿Deseas descartarlos y cambiar de rol?')) { event.preventDefault(); roleSelector.value = page.dataset.roleId; return; } } roleSelector.form.submit(); });
    refreshState();
    filterRows();
})();
