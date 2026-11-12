/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/gestion/administracion/organizacion/organizacion.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

// Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
document.querySelectorAll('[data-organizacion]').forEach((page) => {
    const filterRoot = page.querySelector('[data-org-filter-root]');
    const rows = [...page.querySelectorAll('[data-org-row]')];
    const search = filterRoot?.querySelector('[data-org-search]');
    const selects = [...(filterRoot?.querySelectorAll('[data-org-select]') ?? [])];
    const empty = page.querySelector('[data-org-empty]');

/**
 * Gestiona la interacción y actualiza el estado visual correspondiente.
 *
 * @returns {*} Resultado de la operación.
 */
    // Funcion: applyFilters concentra una tarea reutilizable del modulo.
    const applyFilters = () => {
        const query = search?.value.trim().toLocaleLowerCase('es') ?? '';
        let visible = 0;

        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        rows.forEach((row) => {
            const matchesText = row.textContent.toLocaleLowerCase('es').includes(query);
            // Funcion: matchesFilters concentra una tarea reutilizable del modulo.
            const matchesFilters = selects.every((select) => {
                const selected = select.value;
                const actual = row.dataset[`org${select.dataset.orgSelect.charAt(0).toUpperCase()}${select.dataset.orgSelect.slice(1)}`];

                return !selected || actual === selected;
            });

            row.hidden = !(matchesText && matchesFilters);
            visible += Number(!row.hidden);
        });

        // Condicional: evalua "empty" para decidir si ejecuta el bloque siguiente.
        if (empty) {
            empty.hidden = visible > 0;
        }
    };

    // Evento: escucha el evento input y ejecuta la funcion asociada cuando ocurre.
    search?.addEventListener('input', applyFilters);
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    filterRoot?.querySelector('[data-org-apply]')?.addEventListener('click', applyFilters);
    // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
    selects.forEach((select) => select.addEventListener('change', applyFilters));

    const tabs = [...page.querySelectorAll('[data-org-tab]')];
    const panels = [...page.querySelectorAll('[data-org-panel]')];

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    tabs.forEach((tab) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        tab.addEventListener('click', () => {
            // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
            tabs.forEach((item) => item.classList.toggle('is-active', item === tab));
            // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
            panels.forEach((panel) => {
                panel.hidden = panel.dataset.orgPanel !== tab.dataset.orgTab;
            });
        });
    });

    const dialog = page.querySelector('[data-org-dialog]');
    const detailDialog = page.querySelector('[data-org-detail-dialog]');
    const confirmDialog = page.querySelector('[data-org-confirm-dialog]');
    const form = dialog?.querySelector('[data-org-form]');
    const steps = [...(dialog?.querySelectorAll('[data-org-step]') ?? [])];
    const feedback = dialog?.querySelector('[data-org-feedback]');
    let currentStep = 0;
    let activeSteps = steps;
    let lastTrigger = null;

/**
 * Gestiona la interacción y actualiza el estado visual correspondiente.
 *
 * @param {*} name - Parámetro recibido por la función.
 *
 * @param {*} value - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
    // Funcion: setField concentra una tarea reutilizable del modulo.
    const setField = (name, value) => {
        const field = form?.elements.namedItem(name);
        // Condicional: evalua "!field || !value || value === '—'" para decidir si ejecuta el bloque siguiente.
        if (!field || !value || value === '—') return;
        // Condicional: evalua "field instanceof HTMLSelectElement && ![...field.options].some((option) => option.value === value)" para decidir si ejecuta el bloque siguiente.
        if (field instanceof HTMLSelectElement && ![...field.options].some((option) => option.value === value)) {
            field.add(new Option(value, value));
        }
        field.value = value;
    };

/**
 * Recupera y presenta el detalle de la información solicitada.
 *
 * @param {*} index - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
    // Funcion: showStep concentra una tarea reutilizable del modulo.
    const showStep = (index) => {
        currentStep = index;
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        steps.forEach((step) => { step.hidden = !activeSteps.includes(step) || step !== activeSteps[index]; });
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        dialog.querySelectorAll('[data-org-step-indicator]').forEach((indicator, position) => {
            indicator.hidden = position >= activeSteps.length;
            indicator.classList.toggle('is-active', position === index);
            // Condicional: evalua "position === index) indicator.setAttribute('aria-current', 'step'" para decidir si ejecuta el bloque siguiente.
            if (position === index) indicator.setAttribute('aria-current', 'step');
            // Caso alternativo: atiende la situacion en la que ninguna condicion anterior resulto verdadera.
            else indicator.removeAttribute('aria-current');
        });
        dialog.querySelector('[data-org-steps]').hidden = activeSteps.length < 2;
        dialog.querySelector('[data-org-prev]').hidden = index === 0;
        dialog.querySelector('[data-org-next]').hidden = index === activeSteps.length - 1;
        dialog.querySelector('[data-org-save]').hidden = index !== activeSteps.length - 1;
        feedback.textContent = '';
    };

/**
 * Ejecuta la operación validateStep utilizada por el módulo JavaScript.
 *
 * @param {*} step - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
    // Funcion: validateStep concentra una tarea reutilizable del modulo.
    const validateStep = (step) => {
        for (const field of step.querySelectorAll('input, select, textarea')) {
            // Condicional: evalua "field.disabled || field.closest('[hidden]')" para decidir si ejecuta el bloque siguiente.
            if (field.disabled || field.closest('[hidden]')) continue;
            // Condicional: evalua "!field.checkValidity()" para decidir si ejecuta el bloque siguiente.
            if (!field.checkValidity()) {
                field.reportValidity();
                feedback.textContent = 'Revisa los campos obligatorios o el formato indicado.';
                return false;
            }
        }
        const start = form.elements.namedItem('inicio')?.value;
        const end = form.elements.namedItem('fin')?.value;
        // Condicional: evalua "start && end && end < start" para decidir si ejecuta el bloque siguiente.
        if (start && end && end < start) {
            feedback.textContent = 'La fecha de fin debe ser posterior a la fecha de inicio.';
            form.elements.namedItem('fin')?.focus();
            return false;
        }
        for (const fileInput of step.querySelectorAll('input[type="file"]')) {
            const limit = fileInput.name === 'logo' ? 5 : 10;
            // Condicional: evalua "fileInput.files?.[0]?.size > limit * 1024 * 1024" para decidir si ejecuta el bloque siguiente.
            if (fileInput.files?.[0]?.size > limit * 1024 * 1024) {
                feedback.textContent = `El archivo ${fileInput.name} supera el máximo de ${limit} MB.`;
                fileInput.focus();
                return false;
            }
        }
        return true;
    };

/**
 * Ejecuta la operación rowValues utilizada por el módulo JavaScript.
 *
 * @param {*} row - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
    // Recorrido: usa map para procesar cada elemento y obtener o actualizar datos.
    const rowValues = (row) => [...row.querySelectorAll('td')].slice(1, -1).map((cell) => cell.textContent.trim());

/**
 * Ejecuta la operación prefill utilizada por el módulo JavaScript.
 *
 * @param {*} row - Parámetro recibido por la función.
 *
 * @param {*} values - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
    // Funcion: prefill concentra una tarea reutilizable del modulo.
    const prefill = (row, values) => {
        switch (page.dataset.organizacion) {
            case 'sedes':
                setField('nombre', row.dataset.orgNombre);
                setField('codigo', row.dataset.orgCodigo);
                setField('direccion', row.dataset.orgDireccion);
                setField('telefono', row.dataset.orgTelefono);
                setField('zona_horaria', row.dataset.orgZonaHoraria);
                break;
            case 'almacenes':
                setField('nombre', row.dataset.orgNombre);
                setField('codigo', row.dataset.orgCodigo);
                setField('sede_id', row.dataset.orgSedeId);
                setField('tipo', row.dataset.orgTipo);
                form.querySelector('input[type="checkbox"][name="permite_venta"]').checked = row.dataset.orgPermiteVenta === '1';
                break;
            case 'personal':
                setField('colaborador', values[0]); setField('cargo', values[1]); setField('sede', values[2]); setField('area', values[3]);
                break;
            case 'responsables':
                setField('recurso', values[0]); setField('tipo_recurso', values[1]); setField('sede', values[2]); setField('anterior', values[3]); setField('cargo', values[4]);
                break;
        }
    };

/**
 * Gestiona la interacción y actualiza el estado visual correspondiente.
 *
 * @param {*} action - Parámetro recibido por la función.
 *
 * @param {*} row - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
    // Funcion: openForm concentra una tarea reutilizable del modulo.
    const openForm = (action, row) => {
        // Condicional: evalua "!dialog || !form" para decidir si ejecuta el bloque siguiente.
        if (!dialog || !form) return;
        form.reset();
        feedback.textContent = '';
        const pageName = page.dataset.organizacion;
        const values = row ? rowValues(row) : [];
        const formularioReal = ['empresa', 'almacenes', 'sedes', 'responsables'].includes(pageName) && form.hasAttribute('data-org-real-form');
        // Condicional: evalua "formularioReal" para decidir si ejecuta el bloque siguiente.
        if (formularioReal) {
            form.action = pageName === 'empresa'
                ? page.dataset.orgUpdateRoute
                : action === 'editar' && row ? row.dataset.orgUpdateUrl : page.dataset.orgCreateRoute;
            form.elements.namedItem('_method').value = pageName === 'empresa' || (action === 'editar' && row) ? 'PUT' : 'POST';
            const selectorSede = form.elements.namedItem('sede_id');
            // Condicional: evalua "selectorSede" para decidir si ejecuta el bloque siguiente.
            if (selectorSede) selectorSede.disabled = action === 'editar';
        }
        const titles = {
            empresa: { editar: 'Editar datos de la empresa', logo: 'Cambiar logotipo' },
            sedes: { nuevo: 'Nueva sede', editar: 'Editar sede' },
            almacenes: { nuevo: 'Nuevo almacén', editar: 'Editar almacén', capacidad: 'Ajustar capacidad' },
            personal: { asignar: 'Asignar personal a una sede', editar: 'Editar asignación de personal' },
            responsables: { nuevo: 'Nueva asignación de responsable', reasignar: 'Reasignar responsable' },
        };
        dialog.querySelector('[data-org-modal-title]').textContent = titles[pageName]?.[action] ?? 'Formulario';
        dialog.querySelector('[data-org-modal-description]').textContent = formularioReal && pageName === 'almacenes'
            ? 'Completa los datos del almacén. Se validarán el código, la sede y el permiso antes de guardar.'
            : formularioReal && pageName === 'sedes'
                ? 'Completa los datos de la sede. El código será único dentro de la empresa activa.'
            : formularioReal && pageName === 'responsables'
                ? 'Selecciona un recurso y un usuario existente. La vigencia anterior se cerrará de forma transaccional si corresponde.'
            : formularioReal
                    ? 'Actualiza los campos disponibles en la ficha empresarial. El cambio quedará auditado.'
                    : 'Completa los campos y revisa la información. La integración con Laravel y MySQL está pendiente.';
        activeSteps = steps;
        const capacityTarget = dialog.querySelector('[data-org-capacity-target]');
        // Condicional: evalua "capacityTarget" para decidir si ejecuta el bloque siguiente.
        if (capacityTarget) capacityTarget.hidden = action !== 'capacidad';
        // Condicional: evalua "action === 'capacidad'" para decidir si ejecuta el bloque siguiente.
        if (action === 'capacidad') {
            activeSteps = [steps[1]];
            const warehouseSelect = form.elements.namedItem('almacen_objetivo');
            warehouseSelect.required = true;
            warehouseSelect.length = 1;
            // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
            rows.forEach((item) => warehouseSelect.add(new Option(rowValues(item)[0], rowValues(item)[0])));
            for (const field of form.querySelectorAll('[data-org-step="2"] input, [data-org-step="2"] select, [data-org-step="2"] textarea')) {
                // Condicional: evalua "field !== warehouseSelect && field.name !== 'capacidad') field.closest('label'" para decidir si ejecuta el bloque siguiente.
                if (field !== warehouseSelect && field.name !== 'capacidad') field.closest('label').hidden = true;
            }
        // Caso alternativo: atiende la situacion en la que ninguna condicion anterior resulto verdadera.
        } else {
            const warehouseSelect = form.elements.namedItem('almacen_objetivo');
            // Condicional: evalua "warehouseSelect" para decidir si ejecuta el bloque siguiente.
            if (warehouseSelect) warehouseSelect.required = false;
            // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
            form.querySelectorAll('[data-org-step="2"] label').forEach((label) => {
                // Condicional: evalua "label !== capacityTarget" para decidir si ejecuta el bloque siguiente.
                if (label !== capacityTarget) label.hidden = false;
            });
        }
        // Condicional: evalua "action === 'logo'" para decidir si ejecuta el bloque siguiente.
        if (action === 'logo') activeSteps = [steps[2]];
        // Condicional: evalua "pageName === 'responsables'" para decidir si ejecuta el bloque siguiente.
        if (pageName === 'responsables') {
            const tipoRecurso = form.elements.namedItem('tipo_recurso');
            const selectorSede = form.elements.namedItem('sede_id');
            const selectorAlmacen = form.elements.namedItem('almacen_id');
            const selectorResponsable = form.elements.namedItem('responsable_usuario_id');
/**
 * Actualiza la información visible o el estado del componente.
 *
 * @returns {*} Resultado de la operación.
 */
            // Funcion: actualizarRecurso concentra una tarea reutilizable del modulo.
            const actualizarRecurso = () => {
                const tipo = tipoRecurso.value;
                form.querySelector('[data-org-resource-field="almacen_id"]').hidden = tipo !== 'almacen';
                selectorAlmacen.required = tipo === 'almacen';
                // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
                [...selectorAlmacen.options].forEach((option) => { if (option.value) option.hidden = option.dataset.sedeId !== selectorSede.value; });
                // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
                [...selectorResponsable.options].forEach((option) => { if (option.value) option.hidden = option.dataset.sedeId !== selectorSede.value; });
            };
            tipoRecurso.onchange = actualizarRecurso;
            selectorSede.onchange = actualizarRecurso;
            actualizarRecurso();
        }
        // Condicional: evalua "row) prefill(row, values" para decidir si ejecuta el bloque siguiente.
        if (row) prefill(row, values);
        showStep(0);
        dialog.showModal();
        dialog.querySelector('[data-org-step]:not([hidden]) input, [data-org-step]:not([hidden]) select, [data-org-step]:not([hidden]) textarea')?.focus();
    };

    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    page.addEventListener('click', (event) => {
        const link = event.target.closest('[data-org-link]');
        // Condicional: evalua "link) { window.location.assign(link.dataset.orgLink" para decidir si ejecuta el bloque siguiente.
        if (link) { window.location.assign(link.dataset.orgLink); return; }
        const trigger = event.target.closest('[data-org-open]');
        // Condicional: evalua "!trigger" para decidir si ejecuta el bloque siguiente.
        if (!trigger) return;
        lastTrigger = trigger;
        const action = trigger.dataset.orgOpen;
        const row = trigger.closest('[data-org-row]');
        // Condicional: evalua "action === 'detalle' && row && detailDialog" para decidir si ejecuta el bloque siguiente.
        if (action === 'detalle' && row && detailDialog) {
            // Recorrido: usa map para procesar cada elemento y obtener o actualizar datos.
            const labels = [...row.closest('table').querySelectorAll('thead th')].slice(1, -1).map((cell) => cell.textContent.trim());
            const list = detailDialog.querySelector('[data-org-detail-list]');
            list.replaceChildren();
            // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
            rowValues(row).forEach((value, index) => {
                const item = document.createElement('div');
                const term = document.createElement('dt');
                const definition = document.createElement('dd');
                term.textContent = labels[index] ?? 'Dato';
                definition.textContent = value;
                item.append(term, definition);
                list.append(item);
            });
            detailDialog.showModal();
        // Condicional alternativo: este bloque se ejecuta cuando la comprobacion anterior no se cumple.
        } else if (action === 'estado' && row && confirmDialog) {
            // Condicional: evalua "confirmDialog.querySelector('[data-org-real-status-form]')" para decidir si ejecuta el bloque siguiente.
            if (confirmDialog.querySelector('[data-org-real-status-form]')) {
                const formularioEstado = confirmDialog.querySelector('[data-org-status-form]');
                const estadoActual = page.dataset.organizacion === 'sedes' ? row.dataset.orgActiva : row.dataset.orgActivo;
                const nuevoEstado = estadoActual !== '1';
                formularioEstado.action = row.dataset.orgStatusUrl;
                formularioEstado.querySelector('[data-org-status-value]').value = nuevoEstado ? '1' : '0';
                formularioEstado.querySelector('[name="motivo"]').value = '';
                confirmDialog.querySelector('[data-org-confirm-description]').textContent = `${nuevoEstado ? 'Activar' : 'Desactivar'} ${row.dataset.orgNombre}. El cambio quedará registrado en auditoría.`;
            // Caso alternativo: atiende la situacion en la que ninguna condicion anterior resulto verdadera.
            } else {
                confirmDialog.querySelector('[data-org-confirm-description]').textContent = `Cambio de estado de ${rowValues(row)[0]}. No se modificará ningún dato.`;
                confirmDialog.querySelector('[data-org-confirm-reason]').value = '';
                confirmDialog.querySelector('[data-org-confirm-feedback]').textContent = '';
            }
            confirmDialog.showModal();
            confirmDialog.querySelector('[name="motivo"], [data-org-confirm-reason]')?.focus();
        // Caso alternativo: atiende la situacion en la que ninguna condicion anterior resulto verdadera.
        } else {
            openForm(action, row);
        }
    });

    // Recorrido: usa filter para procesar cada elemento y obtener o actualizar datos.
    for (const modal of [dialog, detailDialog, confirmDialog].filter(Boolean)) {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        modal.querySelectorAll('[data-org-close]').forEach((button) => button.addEventListener('click', () => modal.close()));
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        modal.addEventListener('click', (event) => { if (event.target === modal) modal.close(); });
        // Evento: escucha el evento close y ejecuta la funcion asociada cuando ocurre.
        modal.addEventListener('close', () => lastTrigger?.focus());
    }
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    dialog?.querySelector('[data-org-prev]')?.addEventListener('click', () => showStep(currentStep - 1));
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    dialog?.querySelector('[data-org-next]')?.addEventListener('click', () => {
        // Condicional: evalua "validateStep(activeSteps[currentStep])) showStep(currentStep + 1" para decidir si ejecuta el bloque siguiente.
        if (validateStep(activeSteps[currentStep])) showStep(currentStep + 1);
    });
    // Condicional: evalua "!form?.hasAttribute('data-org-real-form')" para decidir si ejecuta el bloque siguiente.
    if (!form?.hasAttribute('data-org-real-form')) {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        dialog?.querySelector('[data-org-save]')?.addEventListener('click', () => {
            for (let index = 0; index < activeSteps.length; index++) {
                showStep(index);
                // Condicional: evalua "!validateStep(activeSteps[index])" para decidir si ejecuta el bloque siguiente.
                if (!validateStep(activeSteps[index])) return;
            }
            showStep(activeSteps.length - 1);
            feedback.textContent = 'Vista de diseño: la integración con Laravel y MySQL está pendiente. No se guardó ninguna información.';
        });
        // Evento: escucha el evento submit y ejecuta la funcion asociada cuando ocurre.
        form?.addEventListener('submit', (event) => event.preventDefault());
    }
    // Condicional: evalua "form?.hasAttribute('data-org-real-form') && page.dataset.organizacion === 'responsables'" para decidir si ejecuta el bloque siguiente.
    if (form?.hasAttribute('data-org-real-form') && page.dataset.organizacion === 'responsables') {
        // Evento: escucha el evento submit y ejecuta la funcion asociada cuando ocurre.
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            for (let index = 0; index < activeSteps.length; index++) {
                showStep(index);
                // Condicional: evalua "!validateStep(activeSteps[index])" para decidir si ejecuta el bloque siguiente.
                if (!validateStep(activeSteps[index])) return;
            }
            showStep(activeSteps.length - 1);
            HTMLFormElement.prototype.submit.call(form);
        });
    }
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    confirmDialog?.querySelector('[data-org-confirm]')?.addEventListener('click', () => {
        const reason = confirmDialog.querySelector('[data-org-confirm-reason]');
        // Condicional: evalua "!reason.checkValidity()) { reason.reportValidity(" para decidir si ejecuta el bloque siguiente.
        if (!reason.checkValidity()) { reason.reportValidity(); return; }
        confirmDialog.querySelector('[data-org-confirm-feedback]').textContent = 'Vista de diseño: el estado no se modificó. La integración está pendiente.';
    });
});