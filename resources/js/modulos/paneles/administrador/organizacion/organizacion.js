document.querySelectorAll('[data-organizacion]').forEach((page) => {
    const filterRoot = page.querySelector('[data-org-filter-root]');
    const rows = [...page.querySelectorAll('[data-org-row]')];
    const search = filterRoot?.querySelector('[data-org-search]');
    const selects = [...(filterRoot?.querySelectorAll('[data-org-select]') ?? [])];
    const empty = page.querySelector('[data-org-empty]');

    const applyFilters = () => {
        const query = search?.value.trim().toLocaleLowerCase('es') ?? '';
        let visible = 0;

        rows.forEach((row) => {
            const matchesText = row.textContent.toLocaleLowerCase('es').includes(query);
            const matchesFilters = selects.every((select) => {
                const selected = select.value;
                const actual = row.dataset[`org${select.dataset.orgSelect.charAt(0).toUpperCase()}${select.dataset.orgSelect.slice(1)}`];

                return !selected || actual === selected;
            });

            row.hidden = !(matchesText && matchesFilters);
            visible += Number(!row.hidden);
        });

        if (empty) {
            empty.hidden = visible > 0;
        }
    };

    search?.addEventListener('input', applyFilters);
    filterRoot?.querySelector('[data-org-apply]')?.addEventListener('click', applyFilters);
    selects.forEach((select) => select.addEventListener('change', applyFilters));

    const tabs = [...page.querySelectorAll('[data-org-tab]')];
    const panels = [...page.querySelectorAll('[data-org-panel]')];

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            tabs.forEach((item) => item.classList.toggle('is-active', item === tab));
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

    const setField = (name, value) => {
        const field = form?.elements.namedItem(name);
        if (!field || !value || value === '—') return;
        if (field instanceof HTMLSelectElement && ![...field.options].some((option) => option.value === value)) {
            field.add(new Option(value, value));
        }
        field.value = value;
    };

    const showStep = (index) => {
        currentStep = index;
        steps.forEach((step) => { step.hidden = !activeSteps.includes(step) || step !== activeSteps[index]; });
        dialog.querySelectorAll('[data-org-step-indicator]').forEach((indicator, position) => {
            indicator.hidden = position >= activeSteps.length;
            indicator.classList.toggle('is-active', position === index);
            if (position === index) indicator.setAttribute('aria-current', 'step');
            else indicator.removeAttribute('aria-current');
        });
        dialog.querySelector('[data-org-steps]').hidden = activeSteps.length < 2;
        dialog.querySelector('[data-org-prev]').hidden = index === 0;
        dialog.querySelector('[data-org-next]').hidden = index === activeSteps.length - 1;
        dialog.querySelector('[data-org-save]').hidden = index !== activeSteps.length - 1;
        feedback.textContent = '';
    };

    const validateStep = (step) => {
        for (const field of step.querySelectorAll('input, select, textarea')) {
            if (field.disabled || field.closest('[hidden]')) continue;
            if (!field.checkValidity()) {
                field.reportValidity();
                feedback.textContent = 'Revisa los campos obligatorios o el formato indicado.';
                return false;
            }
        }
        const start = form.elements.namedItem('inicio')?.value;
        const end = form.elements.namedItem('fin')?.value;
        if (start && end && end < start) {
            feedback.textContent = 'La fecha de fin debe ser posterior a la fecha de inicio.';
            form.elements.namedItem('fin')?.focus();
            return false;
        }
        for (const fileInput of step.querySelectorAll('input[type="file"]')) {
            const limit = fileInput.name === 'logo' ? 5 : 10;
            if (fileInput.files?.[0]?.size > limit * 1024 * 1024) {
                feedback.textContent = `El archivo ${fileInput.name} supera el máximo de ${limit} MB.`;
                fileInput.focus();
                return false;
            }
        }
        return true;
    };

    const rowValues = (row) => [...row.querySelectorAll('td')].slice(1, -1).map((cell) => cell.textContent.trim());

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

    const openForm = (action, row) => {
        if (!dialog || !form) return;
        form.reset();
        feedback.textContent = '';
        const pageName = page.dataset.organizacion;
        const values = row ? rowValues(row) : [];
        const formularioReal = ['empresa', 'almacenes', 'sedes'].includes(pageName) && form.hasAttribute('data-org-real-form');
        if (formularioReal) {
            form.action = pageName === 'empresa'
                ? page.dataset.orgUpdateRoute
                : action === 'editar' && row ? row.dataset.orgUpdateUrl : page.dataset.orgCreateRoute;
            form.elements.namedItem('_method').value = pageName === 'empresa' || (action === 'editar' && row) ? 'PUT' : 'POST';
            const selectorSede = form.elements.namedItem('sede_id');
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
                : formularioReal
                    ? 'Actualiza los campos disponibles en la ficha empresarial. El cambio quedará auditado.'
                    : 'Completa los campos y revisa la información. La integración con Laravel y MySQL está pendiente.';
        activeSteps = steps;
        const capacityTarget = dialog.querySelector('[data-org-capacity-target]');
        if (capacityTarget) capacityTarget.hidden = action !== 'capacidad';
        if (action === 'capacidad') {
            activeSteps = [steps[1]];
            const warehouseSelect = form.elements.namedItem('almacen_objetivo');
            warehouseSelect.required = true;
            warehouseSelect.length = 1;
            rows.forEach((item) => warehouseSelect.add(new Option(rowValues(item)[0], rowValues(item)[0])));
            for (const field of form.querySelectorAll('[data-org-step="2"] input, [data-org-step="2"] select, [data-org-step="2"] textarea')) {
                if (field !== warehouseSelect && field.name !== 'capacidad') field.closest('label').hidden = true;
            }
        } else {
            const warehouseSelect = form.elements.namedItem('almacen_objetivo');
            if (warehouseSelect) warehouseSelect.required = false;
            form.querySelectorAll('[data-org-step="2"] label').forEach((label) => {
                if (label !== capacityTarget) label.hidden = false;
            });
        }
        if (action === 'logo') activeSteps = [steps[2]];
        if (row) prefill(row, values);
        showStep(0);
        dialog.showModal();
        dialog.querySelector('[data-org-step]:not([hidden]) input, [data-org-step]:not([hidden]) select, [data-org-step]:not([hidden]) textarea')?.focus();
    };

    page.addEventListener('click', (event) => {
        const link = event.target.closest('[data-org-link]');
        if (link) { window.location.assign(link.dataset.orgLink); return; }
        const trigger = event.target.closest('[data-org-open]');
        if (!trigger) return;
        lastTrigger = trigger;
        const action = trigger.dataset.orgOpen;
        const row = trigger.closest('[data-org-row]');
        if (action === 'detalle' && row && detailDialog) {
            const labels = [...row.closest('table').querySelectorAll('thead th')].slice(1, -1).map((cell) => cell.textContent.trim());
            const list = detailDialog.querySelector('[data-org-detail-list]');
            list.replaceChildren();
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
        } else if (action === 'estado' && row && confirmDialog) {
            if (confirmDialog.querySelector('[data-org-real-status-form]')) {
                const formularioEstado = confirmDialog.querySelector('[data-org-status-form]');
                const estadoActual = page.dataset.organizacion === 'sedes' ? row.dataset.orgActiva : row.dataset.orgActivo;
                const nuevoEstado = estadoActual !== '1';
                formularioEstado.action = row.dataset.orgStatusUrl;
                formularioEstado.querySelector('[data-org-status-value]').value = nuevoEstado ? '1' : '0';
                formularioEstado.querySelector('[name="motivo"]').value = '';
                confirmDialog.querySelector('[data-org-confirm-description]').textContent = `${nuevoEstado ? 'Activar' : 'Desactivar'} ${row.dataset.orgNombre}. El cambio quedará registrado en auditoría.`;
            } else {
                confirmDialog.querySelector('[data-org-confirm-description]').textContent = `Cambio de estado de ${rowValues(row)[0]}. No se modificará ningún dato.`;
                confirmDialog.querySelector('[data-org-confirm-reason]').value = '';
                confirmDialog.querySelector('[data-org-confirm-feedback]').textContent = '';
            }
            confirmDialog.showModal();
            confirmDialog.querySelector('[name="motivo"], [data-org-confirm-reason]')?.focus();
        } else {
            openForm(action, row);
        }
    });

    for (const modal of [dialog, detailDialog, confirmDialog].filter(Boolean)) {
        modal.querySelectorAll('[data-org-close]').forEach((button) => button.addEventListener('click', () => modal.close()));
        modal.addEventListener('click', (event) => { if (event.target === modal) modal.close(); });
        modal.addEventListener('close', () => lastTrigger?.focus());
    }
    dialog?.querySelector('[data-org-prev]')?.addEventListener('click', () => showStep(currentStep - 1));
    dialog?.querySelector('[data-org-next]')?.addEventListener('click', () => {
        if (validateStep(activeSteps[currentStep])) showStep(currentStep + 1);
    });
    if (!form?.hasAttribute('data-org-real-form')) {
        dialog?.querySelector('[data-org-save]')?.addEventListener('click', () => {
            for (let index = 0; index < activeSteps.length; index++) {
                showStep(index);
                if (!validateStep(activeSteps[index])) return;
            }
            showStep(activeSteps.length - 1);
            feedback.textContent = 'Vista de diseño: la integración con Laravel y MySQL está pendiente. No se guardó ninguna información.';
        });
        form?.addEventListener('submit', (event) => event.preventDefault());
    }
    confirmDialog?.querySelector('[data-org-confirm]')?.addEventListener('click', () => {
        const reason = confirmDialog.querySelector('[data-org-confirm-reason]');
        if (!reason.checkValidity()) { reason.reportValidity(); return; }
        confirmDialog.querySelector('[data-org-confirm-feedback]').textContent = 'Vista de diseño: el estado no se modificó. La integración está pendiente.';
    });
});
