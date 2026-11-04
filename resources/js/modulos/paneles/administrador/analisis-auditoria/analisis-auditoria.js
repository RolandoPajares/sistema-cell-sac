const pendingMessage = 'Funcionalidad visual. Integración pendiente. No se realizaron cambios.';

document.querySelectorAll('[data-analisis-auditoria]').forEach((page) => {
    const config = JSON.parse(page.nextElementSibling?.textContent ?? '{}');
    const rows = [...page.querySelectorAll('[data-aa-row]')];
    const filters = [...page.querySelectorAll('[data-aa-filter]')];
    const dialog = page.querySelector('[data-aa-dialog]');
    const fields = dialog.querySelector('[data-aa-dialog-fields]');
    const title = dialog.querySelector('[data-aa-dialog-title]');
    const description = dialog.querySelector('[data-aa-dialog-description]');
    const note = dialog.querySelector('[data-aa-dialog-note]');
    const error = dialog.querySelector('[data-aa-dialog-error]');
    const feedback = dialog.querySelector('[data-aa-dialog-feedback]');
    const wizard = dialog.querySelector('[data-aa-wizard]');
    const review = dialog.querySelector('[data-aa-review]');
    const comparison = dialog.querySelector('[data-aa-comparison]');
    const exportDecisions = dialog.querySelector('[data-aa-export-decisions]');
    const reviewList = dialog.querySelector('[data-aa-review-list]');
    const reviewWarnings = dialog.querySelector('[data-aa-review-warnings]');
    const closeButtons = [...dialog.querySelectorAll('[data-aa-close]')];
    const previousButton = dialog.querySelector('[data-aa-previous]');
    const nextButton = dialog.querySelector('[data-aa-next]');
    const confirmButton = dialog.querySelector('[data-aa-dialog-confirm]');
    const decisionDialogs = Object.fromEntries([...page.querySelectorAll('[data-aa-decision-dialog]')].map((item) => [item.dataset.aaDecisionDialog, item]));
    let opener;
    let decisionOpener;
    let activeSteps = [];
    let activeStep = 0;
    let confirmAction = null;
    let reviewContext = 'report';
    let generatedField = 0;
    let selectedExportRecord = '';

    const resetFeedback = () => {
        error.hidden = true;
        feedback.hidden = true;
        feedback.textContent = '';
    };

    const showFeedback = (message = pendingMessage) => {
        error.hidden = true;
        feedback.textContent = message;
        feedback.hidden = false;
    };

    const makeField = (spec, container) => {
        const wrapper = document.createElement('label');
        const type = spec.type ?? 'text';
        const control = document.createElement(type === 'textarea' ? 'textarea' : type === 'select' ? 'select' : 'input');
        const labelText = document.createElement('span');
        const fieldName = spec.name ?? `campo_${++generatedField}`;

        wrapper.className = type === 'checkbox' ? 'tc-aa__check-field' : 'tc-aa__dialog-field';
        labelText.textContent = spec.label;
        control.dataset.aaFieldName = fieldName;

        if (type === 'select') {
            const placeholderOption = document.createElement('option');
            placeholderOption.value = '';
            placeholderOption.textContent = spec.placeholder ?? 'Selecciona una opción';
            control.append(placeholderOption);
            (spec.options ?? []).forEach((option) => {
                const normalizedOption = typeof option === 'string' ? { value: option, label: option } : option;
                const optionElement = document.createElement('option');
                optionElement.value = normalizedOption.value;
                optionElement.textContent = normalizedOption.label;
                control.append(optionElement);
            });
            control.value = spec.value ?? '';
        } else if (type === 'checkbox') {
            control.type = 'checkbox';
            control.value = spec.value ?? '1';
            control.checked = Boolean(spec.checked);
            if (spec.requiredGroup) {
                control.dataset.aaRequiredGroup = spec.requiredGroup;
            }
        } else if (type === 'textarea') {
            control.rows = spec.rows ?? 3;
            control.value = spec.value ?? '';
            control.placeholder = spec.placeholder ?? '';
        } else {
            control.type = type;
            control.value = spec.value ?? '';
            control.placeholder = spec.placeholder ?? '';
        }

        control.required = Boolean(spec.required);
        control.readOnly = Boolean(spec.readOnly);

        if (spec.min !== undefined) {
            control.min = String(spec.min);
        }
        if (spec.max !== undefined) {
            control.max = String(spec.max);
        }
        if (spec.minLength !== undefined) {
            control.minLength = Number(spec.minLength);
        }
        if (spec.maxLength !== undefined) {
            control.maxLength = Number(spec.maxLength);
        }
        if (spec.step !== undefined) {
            control.step = String(spec.step);
        }
        if (spec.help) {
            const help = document.createElement('small');
            help.className = 'tc-aa__field-help';
            help.textContent = spec.help;
            wrapper.append(labelText, control, help);
        } else if (type === 'checkbox') {
            wrapper.append(control, labelText);
        } else {
            wrapper.append(labelText, control);
        }

        container.append(wrapper);
        return control;
    };

    const getValues = () => {
        const values = new Map();

        fields.querySelectorAll('[data-aa-field-name]').forEach((control) => {
            const name = control.dataset.aaFieldName;
            if (control.type === 'checkbox') {
                if (control.checked) {
                    values.set(name, [...(values.get(name) ?? []), control.value]);
                }
            } else {
                values.set(name, control.value);
            }
        });

        return values;
    };

    const getDisplayValue = (control) => {
        if (control.type === 'checkbox') {
            return control.checked ? control.value : '';
        }
        if (control.tagName === 'SELECT') {
            return control.selectedOptions[0]?.textContent.trim() ?? '';
        }

        return control.value.trim();
    };

    const renderReview = () => {
        reviewList.replaceChildren();
        reviewWarnings.replaceChildren();
        const groupedValues = new Map();

        fields.querySelectorAll('[data-aa-field-name]').forEach((control) => {
            const name = control.dataset.aaFieldName;
            const displayValue = getDisplayValue(control);

            if (!displayValue) {
                return;
            }

            const label = control.closest('label')?.querySelector('span')?.textContent.trim() ?? name;
            groupedValues.set(name, [...(groupedValues.get(name) ?? []), { label, value: displayValue }]);
        });

        groupedValues.forEach((items) => {
            const row = document.createElement('div');
            const rowLabel = document.createElement('dt');
            const rowValue = document.createElement('dd');
            rowLabel.textContent = items[0].label;
            rowValue.textContent = items.map((item) => item.value).join(', ');
            row.append(rowLabel, rowValue);
            reviewList.append(row);
        });

        const values = getValues();
        const warnings = [
            'La autorización real y el alcance por usuario o sede no se verificaron.',
            'No se consultó si existe una solicitud duplicada.',
        ];

        if (reviewContext === 'export') {
            warnings.push('No se generará ningún archivo ni se compartirán datos.');
            if (values.get('alcance') === 'ampliado' || values.get('sede') === 'ampliada') {
                warnings.push('El alcance ampliado requiere autorización efectiva antes de integrarse.');
            }
            if (values.get('volumen') === 'periodo_completo') {
                warnings.push('La selección podría representar una exportación masiva y requiere límites backend.');
            }
            if (['Finanzas', 'Clientes', 'Auditoría', 'Seguridad', 'Roles y permisos'].includes(values.get('modulo'))) {
                warnings.push('El módulo elegido podría contener información sensible.');
            }
        } else if (reviewContext === 'alertas') {
            warnings.push('No se guardará una regla ni se enviarán notificaciones.');
        } else {
            warnings.push('No se ejecutó ninguna consulta ni se generó un reporte.');
        }

        warnings.forEach((message) => {
            const item = document.createElement('li');
            item.textContent = message;
            reviewWarnings.append(item);
        });
    };

    const validatePanel = (panel) => {
        const controls = [...panel.querySelectorAll('[data-aa-field-name]')];
        const invalid = controls.find((control) => !control.checkValidity());

        if (invalid) {
            error.textContent = 'Completa los campos obligatorios y revisa los valores permitidos.';
            error.hidden = false;
            invalid.focus();
            return false;
        }

        const requiredGroups = [...new Set(controls.map((control) => control.dataset.aaRequiredGroup).filter(Boolean))];
        for (const group of requiredGroups) {
            if (!controls.some((control) => control.dataset.aaRequiredGroup === group && control.checked)) {
                error.textContent = 'Selecciona al menos un indicador para continuar.';
                error.hidden = false;
                controls.find((control) => control.dataset.aaRequiredGroup === group)?.focus();
                return false;
            }
        }

        const start = panel.querySelector('[data-aa-field-name="fecha_inicio"]');
        const end = panel.querySelector('[data-aa-field-name="fecha_fin"]');
        if (start?.value && end?.value && start.value > end.value) {
            error.textContent = 'La fecha inicial no puede ser posterior a la fecha final.';
            error.hidden = false;
            end.focus();
            return false;
        }

        error.hidden = true;
        return true;
    };

    const showMainDialog = () => {
        if (!dialog.open) {
            dialog.showModal();
        }
        opener?.setAttribute('aria-expanded', 'true');
    };

    const resetDialog = () => {
        fields.replaceChildren();
        wizard.replaceChildren();
        wizard.hidden = true;
        review.hidden = true;
        comparison.hidden = true;
        exportDecisions.hidden = true;
        note.hidden = false;
        note.textContent = 'Datos de ejemplo. Esta interfaz no consulta ni modifica registros reales.';
        resetFeedback();
        generatedField = 0;
        activeSteps = [];
        activeStep = 0;
        confirmAction = null;
        previousButton.hidden = true;
        nextButton.hidden = true;
        confirmButton.hidden = true;
        confirmButton.textContent = 'Confirmar (demostración)';
    };

    const openSimple = (heading, summary, specifications = [], options = {}) => {
        resetDialog();
        title.textContent = heading;
        description.textContent = summary;
        specifications.forEach((specification) => makeField(specification, fields));
        note.textContent = options.note ?? 'Funcionalidad visual. Integración pendiente. No se realizaron cambios.';
        if (options.confirmLabel) {
            confirmButton.hidden = false;
            confirmButton.textContent = options.confirmLabel;
            confirmAction = options.onConfirm ?? (() => showFeedback(options.successMessage ?? pendingMessage));
        }
        showMainDialog();
    };

    const renderWizardStep = () => {
        const panels = [...fields.querySelectorAll('[data-aa-wizard-panel]')];
        panels.forEach((panel) => { panel.hidden = Number(panel.dataset.aaWizardPanel) !== activeStep; });
        [...wizard.children].forEach((indicator, index) => {
            indicator.classList.toggle('is-current', index === activeStep);
            indicator.classList.toggle('is-complete', index < activeStep);
            if (index === activeStep) {
                indicator.setAttribute('aria-current', 'step');
            } else {
                indicator.removeAttribute('aria-current');
            }
        });

        const step = activeSteps[activeStep];
        review.hidden = !step.review;
        comparison.hidden = !step.comparison;
        if (step.review) {
            renderReview();
        }
        previousButton.hidden = activeStep === 0;
        nextButton.hidden = activeStep === activeSteps.length - 1;
        confirmButton.hidden = activeStep !== activeSteps.length - 1 || !confirmAction;
        error.hidden = true;
        feedback.hidden = true;
    };

    const openWizard = (heading, summary, steps, options = {}) => {
        resetDialog();
        title.textContent = heading;
        description.textContent = summary;
        note.textContent = options.note ?? 'Todos los pasos son demostrativos y no ejecutan operaciones en el backend.';
        activeSteps = steps;
        activeStep = 0;
        reviewContext = options.reviewContext ?? 'report';
        confirmAction = options.confirmable === false ? null : options.onConfirm ?? (() => showFeedback(options.successMessage ?? pendingMessage));
        confirmButton.textContent = options.confirmLabel ?? 'Confirmar (demostración)';
        wizard.hidden = false;

        steps.forEach((step, index) => {
            const indicator = document.createElement('span');
            indicator.textContent = `${index + 1}. ${step.label}`;
            wizard.append(indicator);

            const panel = document.createElement('section');
            panel.className = 'tc-aa__step-panel';
            panel.dataset.aaWizardPanel = String(index);
            if (step.title) {
                const panelTitle = document.createElement('h3');
                panelTitle.textContent = step.title;
                panel.append(panelTitle);
            }
            if (step.description) {
                const panelDescription = document.createElement('p');
                panelDescription.textContent = step.description;
                panel.append(panelDescription);
            }
            (step.fields ?? []).forEach((specification) => makeField(specification, panel));
            fields.append(panel);
        });

        renderWizardStep();
        showMainDialog();
    };

    const openExportRequest = (trigger) => {
        const moduleLabels = {
            globales: 'Reporte global',
            sedes: 'Reporte por sede',
            operaciones: 'Auditoría de operaciones',
            roles: 'Historial de roles y permisos',
            seguridad: 'Eventos de seguridad',
        };
        const currentModule = moduleLabels[config.tipo] ?? 'Seleccionar módulo';
        const modules = config.tipo === 'exportaciones'
            ? ['Ventas', 'Inventario', 'Clientes', 'Auditoría', 'Finanzas']
            : [currentModule];

        opener = trigger ?? opener;
        openWizard('Solicitar exportación', 'Completa la solicitud visual. No se crea un archivo ni se elude la autorización existente.', [
            {
                label: 'Alcance',
                title: 'Módulo y alcance solicitado',
                description: 'La disponibilidad real depende de los permisos y del alcance de usuario o sede.',
                fields: [
                    { label: 'Módulo', name: 'modulo', type: 'select', required: true, options: modules },
                    { label: 'Alcance', name: 'alcance', type: 'select', required: true, options: [{ value: 'propio', label: 'Mi alcance autorizado (sin verificar)' }, { value: 'ampliado', label: 'Solicitar alcance ampliado (requiere autorización)' }] },
                    { label: 'Cantidad solicitada', name: 'volumen', type: 'select', required: true, options: [{ value: 'filtrado', label: 'Registros filtrados' }, { value: 'periodo_completo', label: 'Todo el período (posible exportación masiva)' }] },
                ],
            },
            {
                label: 'Período',
                title: 'Período y formato',
                fields: [
                    { label: 'Fecha inicial', name: 'fecha_inicio', type: 'date', required: true },
                    { label: 'Fecha final', name: 'fecha_fin', type: 'date', required: true },
                    { label: 'Formato', name: 'formato', type: 'select', required: true, options: ['PDF', 'XLSX', 'CSV'] },
                ],
            },
            {
                label: 'Motivo',
                title: 'Justificación de la solicitud',
                fields: [{ label: 'Motivo obligatorio', name: 'motivo', type: 'textarea', required: true, minLength: 10, maxLength: 500, placeholder: 'Explica el uso previsto y la necesidad de estos datos.' }],
            },
            { label: 'Revisión', title: 'Revisión de permisos y condiciones', review: true },
            {
                label: 'Confirmar',
                title: 'Confirmar solicitud visual',
                fields: [{ label: 'Confirmo que entiendo que esto no crea ni envía una solicitud real.', name: 'confirmacion', type: 'checkbox', required: true }],
            },
        ], {
            confirmLabel: 'Solicitar autorización (demostración)',
            reviewContext: 'export',
            successMessage: `${pendingMessage} No se registró una solicitud ni se generó un archivo.`,
        });
    };

    const openReportConfiguration = (trigger) => {
        opener = trigger ?? opener;

        if (config.tipo === 'sedes') {
            openWizard('Configurar reporte por sede', 'Selección visual. La sede y sus datos reales no se consultan desde esta vista.', [
                {
                    label: 'Sede y período',
                    title: 'Sede y período solicitado',
                    fields: [
                        { label: 'Sede', name: 'sede', type: 'select', required: true, options: [{ value: 'autorizada', label: 'Sede dentro de mi alcance autorizado' }, { value: 'ampliada', label: 'Comparación multi-sede (requiere autorización)' }] },
                        { label: 'Fecha inicial', name: 'fecha_inicio', type: 'date', required: true },
                        { label: 'Fecha final', name: 'fecha_fin', type: 'date', required: true },
                    ],
                },
                {
                    label: 'Criterios',
                    title: 'Canal y tipo de reporte',
                    fields: [
                        { label: 'Canal', name: 'canal', type: 'select', required: true, options: ['Minorista', 'Mayorista', 'Tienda virtual'] },
                        { label: 'Tipo de reporte', name: 'tipo_reporte', type: 'select', required: true, options: ['Resumen general', 'Ventas', 'Pedidos', 'Inventario'] },
                    ],
                },
                { label: 'Formato', title: 'Formato de salida', fields: [{ label: 'Formato', name: 'formato', type: 'select', required: true, options: ['PDF', 'XLSX', 'CSV'] }] },
                { label: 'Revisión', title: 'Revisión de alcance', review: true },
                { label: 'Confirmar', title: 'Confirmar configuración', fields: [{ label: 'Entiendo que la sede y los datos no se consultaron ni exportaron.', name: 'confirmacion', type: 'checkbox', required: true }] },
            ], {
                confirmLabel: 'Generar cuando se integre',
                reviewContext: 'report',
                successMessage: `${pendingMessage} No se consultaron otras sedes ni se generó un archivo.`,
            });
            return;
        }

        const indicators = config.filas.map((row) => row[0]);
        const indicatorFields = indicators.map((indicator) => ({ label: indicator, name: 'indicadores', value: indicator, type: 'checkbox', requiredGroup: 'indicadores' }));
        openWizard('Configurar reporte global', 'Configura filtros e indicadores disponibles en esta vista de demostración.', [
            {
                label: 'Filtros',
                title: 'Período, canal y módulo',
                fields: [
                    { label: 'Fecha inicial', name: 'fecha_inicio', type: 'date', required: true },
                    { label: 'Fecha final', name: 'fecha_fin', type: 'date', required: true },
                    { label: 'Canal', name: 'canal', type: 'select', required: true, options: ['Todos los canales', 'Minorista', 'Mayorista', 'Tienda virtual'] },
                    { label: 'Módulo', name: 'modulo', type: 'select', required: true, options: ['Todos los módulos', 'Ventas', 'Pedidos', 'Clientes', 'Catálogo'] },
                ],
            },
            { label: 'Indicadores', title: 'Indicadores de ejemplo', description: 'La disponibilidad real del indicador no se consulta desde esta vista.', fields: indicatorFields },
            { label: 'Formato', title: 'Formato del reporte', fields: [{ label: 'Formato', name: 'formato', type: 'select', required: true, options: ['PDF', 'XLSX', 'CSV'] }] },
            { label: 'Revisión', title: 'Revisar selección', review: true },
            { label: 'Confirmar', title: 'Confirmar generación visual', fields: [{ label: 'Entiendo que no se consultarán métricas ni se generará un archivo.', name: 'confirmacion', type: 'checkbox', required: true }] },
        ], {
            confirmLabel: 'Generar cuando se integre',
            reviewContext: 'report',
            successMessage: `${pendingMessage} No se ejecutó una consulta ni se generó un reporte.`,
        });
    };

    const openAuditFilters = (trigger) => {
        opener = trigger ?? opener;
        openSimple('Filtros avanzados de auditoría', 'La búsqueda es visual y no modifica ni consulta registros de auditoría reales.', [
            { label: 'Usuario responsable', name: 'usuario', type: 'text', placeholder: 'Nombre o identificador' },
            { label: 'Operación', name: 'operacion', type: 'select', options: ['Crear', 'Consultar', 'Actualizar', 'Exportar', 'Aprobar', 'Revocar'] },
            { label: 'Módulo', name: 'modulo', type: 'select', options: ['Ventas', 'Inventario', 'Compras', 'Clientes', 'Seguridad'] },
            { label: 'Sede', name: 'sede', type: 'select', options: [{ value: 'autorizada', label: 'Sede dentro de mi alcance (no verificada)' }, { value: 'ampliada', label: 'Otra sede (requiere autorización)' }] },
            { label: 'Fecha inicial', name: 'fecha_inicio', type: 'date' },
            { label: 'Fecha final', name: 'fecha_fin', type: 'date' },
        ], {
            confirmLabel: 'Aplicar filtros (demostración)',
            successMessage: `${pendingMessage} No se consultaron registros reales.`,
        });
    };

    const openRoleComparison = (trigger) => {
        opener = trigger ?? opener;
        openWizard('Consultar historial y comparar permisos', 'La comparación solo presenta una ilustración; no edita el historial ni concede o revoca permisos.', [
            {
                label: 'Filtros',
                title: 'Filtros del historial',
                fields: [
                    { label: 'Usuario responsable', name: 'usuario', type: 'text', placeholder: 'Usuario o identificador' },
                    { label: 'Rol afectado', name: 'rol', type: 'text', placeholder: 'Rol' },
                    { label: 'Módulo', name: 'modulo', type: 'text', placeholder: 'Módulo' },
                    { label: 'Sede', name: 'sede', type: 'select', options: [{ value: 'autorizada', label: 'Sede dentro de mi alcance (no verificada)' }, { value: 'ampliada', label: 'Otra sede (requiere autorización)' }] },
                    { label: 'Fecha inicial', name: 'fecha_inicio', type: 'date' },
                    { label: 'Fecha final', name: 'fecha_fin', type: 'date' },
                ],
            },
            { label: 'Comparación', title: 'Comparación de ejemplo', comparison: true },
        ], { confirmable: false });
    };

    const openSecurityAlertConfiguration = (trigger) => {
        opener = trigger ?? opener;
        openWizard('Configurar regla visual de alerta', 'Configuración demostrativa. No modifica políticas ni envía notificaciones.', [
            {
                label: 'Evento',
                title: 'Evento y nivel de riesgo',
                fields: [
                    { label: 'Tipo de evento', name: 'evento', type: 'select', required: true, options: ['Inicio de sesión fallido', 'Acceso no autorizado', 'Cambio de privilegios', 'Revocación de sesión'] },
                    { label: 'Nivel de riesgo mínimo', name: 'riesgo', type: 'select', required: true, options: ['Medio', 'Alto', 'Crítico'] },
                ],
            },
            {
                label: 'Condiciones',
                title: 'Umbral y destinatarios',
                fields: [
                    { label: 'Intentos dentro de la ventana', name: 'intentos', type: 'number', required: true, min: 1, max: 100, placeholder: 'Ejemplo: 5' },
                    { label: 'Ventana de tiempo (minutos)', name: 'ventana', type: 'number', required: true, min: 1, max: 1440, placeholder: 'Ejemplo: 15' },
                    { label: 'Destinatarios previstos', name: 'destinatarios', type: 'textarea', required: true, minLength: 5, maxLength: 500, placeholder: 'seguridad@empresa.pe, administrador@empresa.pe' },
                    { label: 'Frecuencia', name: 'frecuencia', type: 'select', required: true, options: ['Inmediata', 'Resumen cada hora', 'Resumen diario'] },
                ],
            },
            { label: 'Revisión', title: 'Revisar configuración sensible', review: true },
            { label: 'Confirmar', title: 'Confirmar regla visual', fields: [{ label: 'Entiendo que no se modificará una política real ni se enviarán alertas.', name: 'confirmacion', type: 'checkbox', required: true }] },
        ], {
            confirmLabel: 'Confirmar configuración (demostración)',
            reviewContext: 'alertas',
            successMessage: `${pendingMessage} No se creó una regla ni se enviaron alertas.`,
        });
    };

    const openExportHistoryFilters = (trigger) => {
        opener = trigger ?? opener;
        openSimple('Consultar historial de exportaciones', 'Registros y estados de esta pantalla son ejemplos; no se generan ni descargan archivos.', [
            { label: 'Módulo', name: 'modulo', type: 'select', options: ['Ventas', 'Inventario', 'Clientes', 'Auditoría', 'Finanzas'] },
            { label: 'Solicitante', name: 'solicitante', type: 'text', placeholder: 'Usuario responsable' },
            { label: 'Formato', name: 'formato', type: 'select', options: ['PDF', 'XLSX', 'CSV'] },
            { label: 'Estado', name: 'estado', type: 'select', options: ['En revisión', 'Autorizada', 'Denegada'] },
            { label: 'Fecha inicial', name: 'fecha_inicio', type: 'date' },
            { label: 'Fecha final', name: 'fecha_fin', type: 'date' },
        ], {
            confirmLabel: 'Aplicar filtros (demostración)',
            successMessage: `${pendingMessage} No se consultó un historial real ni se produjo una descarga.`,
        });
    };

    const openDecisionDialog = (button) => {
        const mode = button.dataset.aaDecisionOpen;
        const decisionDialog = decisionDialogs[mode];
        if (!decisionDialog) {
            return;
        }

        decisionOpener = button;
        button.setAttribute('aria-expanded', 'true');
        button.setAttribute('aria-haspopup', 'dialog');
        const decisionTitle = decisionDialog.querySelector('h2');
        const decisionDescription = decisionDialog.querySelector('header p');
        const reason = decisionDialog.querySelector('[data-aa-decision-reason]');
        const decisionFeedback = decisionDialog.querySelector('[data-aa-decision-feedback]');
        decisionTitle.textContent = `${mode === 'approve' ? 'Aprobar' : 'Rechazar'} solicitud · demostración`;
        decisionDescription.textContent = `Solicitud de ejemplo: ${selectedExportRecord}. La decisión no altera su estado.`;
        reason.value = '';
        decisionFeedback.hidden = true;
        if (!decisionDialog.open) {
            decisionDialog.showModal();
        }
    };

    const openExportDecision = (trigger, item) => {
        opener = trigger;
        selectedExportRecord = String(item[3] ?? 'exportación de ejemplo');
        openSimple('Detalle de solicitud de exportación · ejemplo', 'Registro de solo lectura. El estado y los datos del historial no se modifican.', config.columnas.map((column, index) => ({ label: column, name: `detalle_${index}`, value: item[index] ?? '', readOnly: true })), {
            note: 'La fila es ilustrativa. No se validaron permisos, alcance, duplicidad ni disponibilidad de archivo.',
        });
        exportDecisions.hidden = item[6] !== 'En revisión';
    };

    page.querySelector('[data-aa-apply]')?.addEventListener('click', () => {
        const terms = filters.map((filter) => filter.value.toLocaleLowerCase('es')).filter(Boolean);
        let visible = 0;
        rows.forEach((row) => {
            row.hidden = !terms.every((term) => row.textContent.toLocaleLowerCase('es').includes(term));
            visible += Number(!row.hidden);
        });
        page.querySelector('[data-aa-count]').textContent = String(visible);
        page.querySelector('[data-aa-empty]').hidden = visible > 0;
    });

    page.querySelectorAll('[data-aa-detail]').forEach((button) => {
        button.setAttribute('aria-haspopup', 'dialog');
        button.setAttribute('aria-expanded', 'false');
        button.addEventListener('click', () => {
            const item = config.filas[Number(button.dataset.aaDetail)];
            if (!item) {
                return;
            }
            if (config.tipo === 'exportaciones') {
                openExportDecision(button, item);
                return;
            }

            opener = button;
            const detailFields = config.columnas.map((column, index) => ({ label: column, name: `detalle_${index}`, value: item[index] ?? '', readOnly: true }));
            openSimple('Detalle del registro · ejemplo', 'Consulta de solo lectura. Los valores provienen de la vista ilustrativa, no del historial real.', detailFields, {
                note: config.tipo === 'seguridad'
                    ? 'Evento de ejemplo. No representa un incidente real y no ejecuta acciones sobre cuentas o sesiones.'
                    : 'Detalle de ejemplo en modo de solo lectura. No se permite editar ni eliminar registros históricos.',
            });
            comparison.hidden = config.tipo !== 'roles';
        });
    });

    page.querySelectorAll('[data-aa-action]').forEach((button) => {
        button.setAttribute('aria-haspopup', 'dialog');
        button.setAttribute('aria-expanded', 'false');
        button.addEventListener('click', () => {
            opener = button;
            const action = button.dataset.aaAction;
            const actionTitle = button.textContent.trim();

            if (action === 'actualizar') {
                openSimple('Actualizar reporte por sede', 'La actualización de esta vista no consulta sedes ni registros reales.', [], {
                    confirmLabel: 'Actualizar (demostración)',
                    successMessage: `${pendingMessage} No se actualizó el reporte.`,
                });
            } else if (action === 'solicitar') {
                openExportRequest(button);
            } else if (action === 'generar') {
                openReportConfiguration(button);
            } else if (action === 'alertas') {
                openSecurityAlertConfiguration(button);
            } else if (action === 'comparar') {
                openRoleComparison(button);
            } else if (action === 'historial') {
                openAuditFilters(button);
            } else if (action === 'exportar' && config.tipo === 'exportaciones') {
                openExportHistoryFilters(button);
            } else if (action === 'exportar') {
                openExportRequest(button);
            } else {
                openSimple(actionTitle, 'Formulario visual. No se consultan ni modifican registros reales.', [], {
                    confirmLabel: 'Continuar cuando se integre',
                });
            }
        });
    });

    fields.addEventListener('input', resetFeedback);
    fields.addEventListener('change', resetFeedback);

    previousButton.addEventListener('click', () => {
        activeStep = Math.max(0, activeStep - 1);
        renderWizardStep();
    });
    nextButton.addEventListener('click', () => {
        const panel = fields.querySelector(`[data-aa-wizard-panel="${activeStep}"]`);
        if (!validatePanel(panel)) {
            return;
        }
        activeStep = Math.min(activeSteps.length - 1, activeStep + 1);
        renderWizardStep();
    });
    confirmButton.addEventListener('click', () => {
        const panel = activeSteps.length
            ? fields.querySelector(`[data-aa-wizard-panel="${activeStep}"]`)
            : fields;
        if (!validatePanel(panel)) {
            return;
        }
        confirmAction?.();
    });

    closeButtons.forEach((button) => button.addEventListener('click', () => dialog.close()));
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            dialog.close();
        }
    });
    dialog.addEventListener('close', () => {
        opener?.setAttribute('aria-expanded', 'false');
        opener?.focus();
    });

    Object.values(decisionDialogs).forEach((decisionDialog) => {
        decisionDialog.querySelectorAll('[data-aa-decision-close]').forEach((button) => button.addEventListener('click', () => decisionDialog.close()));
        decisionDialog.addEventListener('click', (event) => {
            if (event.target === decisionDialog) {
                decisionDialog.close();
            }
        });
        decisionDialog.addEventListener('close', () => {
            decisionOpener?.setAttribute('aria-expanded', 'false');
            decisionOpener?.focus();
        });
        decisionDialog.querySelector('[data-aa-decision-confirm]').addEventListener('click', () => {
            const reason = decisionDialog.querySelector('[data-aa-decision-reason]');
            const decisionFeedback = decisionDialog.querySelector('[data-aa-decision-feedback]');
            if (!reason.checkValidity() || !reason.value.trim()) {
                decisionFeedback.textContent = 'Escribe una justificación válida para continuar.';
                decisionFeedback.hidden = false;
                reason.focus();
                return;
            }

            decisionFeedback.textContent = `${pendingMessage} La solicitud de ejemplo conserva su estado y no se generó un archivo.`;
            decisionFeedback.hidden = false;
        });
    });

    dialog.querySelectorAll('[data-aa-decision-open]').forEach((button) => {
        button.setAttribute('aria-expanded', 'false');
        button.setAttribute('aria-haspopup', 'dialog');
        button.addEventListener('click', () => openDecisionDialog(button));
    });
});
