document.querySelectorAll('[data-finanzas-diseno]').forEach((page) => {
    const search = page.querySelector('[data-ff-search]');
    const filters = [...page.querySelectorAll('[data-ff-filter]')];
    const rows = [...page.querySelectorAll('[data-ff-row]')];
    const empty = page.querySelector('[data-ff-empty]');
    const dialogs = [...page.querySelectorAll('[data-ff-dialog]')];
    page.querySelectorAll('[data-account-application]').forEach((form) => {
        const cuenta = form.querySelector('[data-account-select]');
        const pago = form.querySelector('[data-registered-payment-select]');
/**
 * Actualiza la información visible o el estado del componente.
 *
 * @returns {*} Resultado de la operación.
 */
        const actualizarPagosDisponibles = () => {
            if (!cuenta || !pago) return;
            [...pago.options].forEach((opcion) => {
                if (opcion.value) opcion.hidden = opcion.dataset.cuenta !== cuenta.value;
            });
            if (pago.selectedOptions[0]?.hidden) pago.value = '';
        };
        cuenta?.addEventListener('change', actualizarPagosDisponibles);
        actualizarPagosDisponibles();
    });

/**
 * Gestiona la interacción y actualiza el estado visual correspondiente.
 *
 * @returns {*} Resultado de la operación.
 */
    const applyFilters = () => {
        const query = search?.value.trim().toLocaleLowerCase('es') ?? '';
        let visible = 0;

        rows.forEach((row) => {
            const textMatches = row.textContent.toLocaleLowerCase('es').includes(query);
            const filtersMatch = filters.every((filter) => {
                const selected = filter.value;
                const field = filter.dataset.ffFilter;
                const key = `ff${field.charAt(0).toUpperCase()}${field.slice(1)}`;

                return !selected || row.dataset[key] === selected;
            });

            row.hidden = !(textMatches && filtersMatch);
            visible += Number(!row.hidden);
        });

        if (empty) empty.hidden = visible > 0;
    };

/**
 * Ejecuta la operación getDialog utilizada por el módulo JavaScript.
 *
 * @param {*} name - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
    const getDialog = (name) => dialogs.find((dialog) => dialog.dataset.ffDialog === name);
/**
 * Gestiona la interacción y actualiza el estado visual correspondiente.
 *
 * @param {*} dialog - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
    const closeDialog = (dialog) => {
        if (dialog?.open) dialog.close();
    };

    search?.addEventListener('input', applyFilters);
    filters.forEach((filter) => filter.addEventListener('change', applyFilters));
    page.querySelector('[data-ff-apply]')?.addEventListener('click', applyFilters);

    page.querySelectorAll('[data-ff-navigate]').forEach((button) => {
        button.addEventListener('click', () => window.location.assign(button.dataset.ffNavigate));
    });
    page.querySelectorAll('[data-ff-focus]').forEach((button) => {
        button.addEventListener('click', () => {
            const target = page.querySelector(`#${CSS.escape(button.dataset.ffFocus)}`) ?? page.querySelector('#cuentas-financieras-aside');
            target?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            target?.querySelector('select:not(:disabled), input:not(:disabled), textarea:not(:disabled)')?.focus({ preventScroll: true });
        });
    });
    page.querySelectorAll('[data-ff-open]').forEach((button) => {
        button.addEventListener('click', () => openDialog(page, button.dataset.ffOpen, null, getDialog));
    });
    page.querySelectorAll('[data-ff-detail]').forEach((button) => {
        button.addEventListener('click', () => renderDetails(getDialog('detalle'), button.closest('[data-ff-row]')));
    });
    page.querySelectorAll('button[data-ff-row-action]').forEach((button) => {
        button.addEventListener('click', () => openDialog(page, button.closest('[data-ff-row]')?.dataset.ffRowAction, button.closest('[data-ff-row]'), getDialog));
    });

    dialogs.forEach((dialog) => {
        dialog.querySelectorAll('[data-ff-close]').forEach((button) => button.addEventListener('click', (event) => {
            event.preventDefault();
            closeDialog(dialog);
        }));
        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) closeDialog(dialog);
        });
        if (dialog.querySelector('[data-ff-stepper]')) initializeStepper(dialog);
    });

    page.querySelectorAll('[data-ff-simple-confirm]').forEach((button) => {
        button.addEventListener('click', () => confirmSimpleDialog(button.closest('dialog')));
    });
    initializeOperationDialog(page, getDialog('operation'));
    initializeSunatDialogs(getDialog('sunat-reenviar'), getDialog('sunat-cdr'));
    initializeAccountAmount(getDialog('abono'));
    initializeNoteEvidence(getDialog('nota'));
});

document.querySelectorAll('[data-finance-module]').forEach((page) => {
    const dialog = page.querySelector('[data-finance-dialog="crear"]');
    if (!dialog) return;

    const method = dialog.querySelector('[data-finance-method]');
    const cashField = dialog.querySelector('[data-finance-cash-field]');
    const bankField = dialog.querySelector('[data-finance-bank-field]');
    const reference = dialog.querySelector('[data-finance-reference]');
/**
 * Ejecuta la operación syncMethodFields utilizada por el módulo JavaScript.
 *
 * @returns {*} Resultado de la operación.
 */
    const syncMethodFields = () => {
        if (!method) return;
        const selected = method.selectedOptions[0];
        const requiresCash = selected?.dataset.requiresCash === '1';
        const requiresBank = selected?.dataset.requiresBank === '1';
        if (cashField) {
            cashField.hidden = !requiresCash;
            cashField.querySelector('select').required = requiresCash;
        }
        if (bankField) {
            bankField.hidden = !requiresBank;
            bankField.querySelector('select').required = requiresBank;
        }
        if (reference) reference.required = requiresBank;
    };
    method?.addEventListener('change', () => {
        if (cashField) cashField.querySelector('select').value = '';
        if (bankField) bankField.querySelector('select').value = '';
        syncMethodFields();
    });
    syncMethodFields();

    page.querySelector('[data-finance-open="crear"]')?.addEventListener('click', () => dialog.showModal());
    dialog.querySelectorAll('[data-finance-close]').forEach((button) => {
        button.addEventListener('click', () => dialog.close());
    });
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) dialog.close();
    });
    if (page.dataset.financeReopen === 'crear') dialog.showModal();
});

document.querySelectorAll('[data-cajas-operativas]').forEach((page) => {
    const createDialog = page.querySelector('[data-box-dialog="crear"]');
    const editDialog = page.querySelector('[data-box-dialog="editar"]');

    page.querySelector('[data-box-open="crear"]')?.addEventListener('click', () => createDialog?.showModal());
    page.querySelectorAll('[data-box-edit]').forEach((button) => {
        button.addEventListener('click', () => {
            const form = editDialog?.querySelector('[data-box-edit-form]');
            if (!form) return;

            form.action = button.dataset.boxEditUrl;
            form.querySelector('[data-box-field="sede_id"]').value = button.dataset.boxSiteId;
            form.querySelector('[data-box-field="sede_nombre"]').value = button.dataset.boxSiteName;
            form.querySelector('[data-box-field="codigo"]').value = button.dataset.boxCode;
            form.querySelector('[data-box-field="nombre"]').value = button.dataset.boxName;
            editDialog.showModal();
        });
    });

    page.querySelectorAll('[data-box-dialog]').forEach((dialog) => {
        dialog.querySelectorAll('[data-box-close]').forEach((button) => {
            button.addEventListener('click', () => dialog.close());
        });
        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) dialog.close();
        });
    });
    page.querySelectorAll('[data-box-status]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const action = form.querySelector('[data-box-status-label]').dataset.boxStatusLabel;
            if (!window.confirm(`¿Confirmas que deseas ${action} esta caja?`)) event.preventDefault();
        });
    });
    if (page.dataset.cajasReopen === 'crear') createDialog?.showModal();
});

/**
 * Gestiona la interacción y actualiza el estado visual correspondiente.
 *
 * @param {*} page - Parámetro recibido por la función.
 *
 * @param {*} name - Parámetro recibido por la función.
 *
 * @param {*} row - Parámetro recibido por la función.
 *
 * @param {*} getDialog - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
function openDialog(page, name, row, getDialog) {
    if (!name) return;
    if (['validacion-documento', 'cambio-serie', 'revisar-reembolso', 'ejecutar-reembolso', 'sunat-reenviar-row'].includes(name)) {
        showOperation(page, name, row);
        return;
    }

    const dialogName = name === 'nueva-caja' || name === 'editar-caja'
        ? 'caja'
        : name === 'cierre-caja'
            ? 'cierre'
            : name;
    const dialog = getDialog(dialogName);
    if (!dialog) return;

    if (dialogName === 'caja') prepareBoxDialog(dialog, row);
    if (dialogName === 'abono') prepareAccountPayment(dialog, row);
    dialog.querySelectorAll('[data-ff-simple-error], [data-ff-simple-feedback]').forEach((message) => { message.hidden = true; });
    dialog.querySelectorAll('[data-ff-simple-confirm]').forEach((button) => { button.disabled = false; });
    dialog.showModal();
}

/**
 * Ejecuta la operación renderDetails utilizada por el módulo JavaScript.
 *
 * @param {*} dialog - Parámetro recibido por la función.
 *
 * @param {*} row - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
function renderDetails(dialog, row) {
    const details = dialog?.querySelector('[data-ff-detail-fields]');
    if (!dialog || !row || !details) return;

    const headings = [...row.closest('table').querySelectorAll('thead th')];
    const cells = [...row.querySelectorAll('td')];
    details.replaceChildren();
    cells.slice(0, headings.length - 1).forEach((cell, index) => {
        const wrapper = document.createElement('div');
        const term = document.createElement('dt');
        const value = document.createElement('dd');
        term.textContent = headings[index]?.textContent.trim() ?? `Dato ${index + 1}`;
        value.textContent = cell.textContent.trim();
        wrapper.append(term, value);
        details.append(wrapper);
    });
    dialog.showModal();
}

/**
 * Ejecuta la operación prepareBoxDialog utilizada por el módulo JavaScript.
 *
 * @param {*} dialog - Parámetro recibido por la función.
 *
 * @param {*} row - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
function prepareBoxDialog(dialog, row) {
    const title = dialog.querySelector('[data-ff-box-title]');
    const fields = [...dialog.querySelectorAll('.tc-ff__dialog-body input, .tc-ff__dialog-body select')];
    const confirm = dialog.querySelector('[data-ff-simple-confirm]');
    const notice = dialog.querySelector('[data-ff-box-notice]');
    fields.forEach((field) => {
        if (field instanceof HTMLSelectElement) {
            field.selectedIndex = 0;
        } else {
            field.value = '';
        }
    });
    if (!row) {
        if (title) title.textContent = 'Nueva caja';
        if (confirm) confirm.textContent = 'Guardar (demostración)';
        if (notice) notice.textContent = 'No se valida apertura simultánea ni se actualiza una caja real.';
        return;
    }

    if (title) title.textContent = 'Edición visual de datos administrativos';
    if (confirm) confirm.textContent = 'Guardar cambios (demostración)';
    if (notice) notice.textContent = 'La fila seleccionada es ilustrativa y sus valores no se cargaron. La edición real requiere consultar un registro autorizado.';
}

/**
 * Ejecuta la operación prepareAccountPayment utilizada por el módulo JavaScript.
 *
 * @param {*} dialog - Parámetro recibido por la función.
 *
 * @param {*} row - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
function prepareAccountPayment(dialog, row) {
    const documentField = dialog.querySelector('[data-ff-account-document]');
    const balance = dialog.querySelector('[data-ff-account-balance]');
    const kind = dialog.querySelector('[data-ff-account-kind]');
    const amount = dialog.querySelector('[data-ff-account-amount]');
    const warning = dialog.querySelector('[data-ff-account-warning]');
    if (documentField) documentField.value = '';
    if (kind) kind.value = '';
    if (balance) balance.value = 'Saldo no consultado';
    if (warning) warning.textContent = row
        ? 'La fila seleccionada es ilustrativa y no se trasladó al formulario. No se consultó el documento ni el saldo real.'
        : 'El saldo real no está consultado. Un monto superior debe bloquearse en la integración backend.';
    if (amount) amount.value = '';
}

/**
 * Ejecuta la operación initializeAccountAmount utilizada por el módulo JavaScript.
 *
 * @param {*} dialog - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
function initializeAccountAmount(dialog) {
    const amount = dialog?.querySelector('[data-ff-account-amount]');
    const balance = dialog?.querySelector('[data-ff-account-balance]');
    const warning = dialog?.querySelector('[data-ff-account-warning]');
    amount?.addEventListener('input', () => {
        const reference = parseMoney(balance?.value ?? '');
        const value = Number(amount.value);
        if (warning && reference > 0 && value > reference) {
            warning.textContent = 'El monto supera el saldo ilustrativo de la fila. No se consultó saldo real; esta alerta solo es visual.';
            warning.classList.add('is-alert');
        } else if (warning) {
            warning.textContent = 'No se consultó el saldo real. Si el importe supera la cifra ilustrativa de la fila, la integración debe bloquearlo antes de registrar un abono.';
            warning.classList.remove('is-alert');
        }
    });
}

/**
 * Ejecuta la operación parseMoney utilizada por el módulo JavaScript.
 *
 * @param {*} value - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
function parseMoney(value) {
    const normalized = value.replace(/[^0-9,.-]/g, '').replace(/\.(?=.*\.)/g, '').replace(',', '.');
    const amount = Number(normalized);
    return Number.isFinite(amount) ? amount : 0;
}

/**
 * Ejecuta la operación initializeStepper utilizada por el módulo JavaScript.
 *
 * @param {*} dialog - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
function initializeStepper(dialog) {
    const body = dialog.querySelector('[data-ff-stepper]');
    const stages = [...body.querySelectorAll('[data-ff-step]')];
    const indicators = [...body.querySelectorAll('[data-ff-step-indicator]')];
    const previous = dialog.querySelector('[data-ff-step-previous]');
    const next = dialog.querySelector('[data-ff-step-next]');
    const confirm = dialog.querySelector('[data-ff-step-confirm]');
    const error = body.querySelector('[data-ff-step-error]');
    const feedback = body.querySelector('[data-ff-step-feedback]');
    let current = 0;

/**
 * Ejecuta la operación render utilizada por el módulo JavaScript.
 *
 * @param {*} step - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
    const render = (step) => {
        current = step;
        stages.forEach((stage) => { stage.hidden = Number(stage.dataset.ffStep) !== step; });
        indicators.forEach((indicator) => indicator.classList.toggle('is-current', Number(indicator.dataset.ffStepIndicator) === step));
        previous.hidden = step === 0;
        next.hidden = step === stages.length - 1;
        confirm.hidden = step !== stages.length - 1;
        confirm.disabled = false;
        error.hidden = true;
        feedback.hidden = true;
    };

    dialog.addEventListener('close', () => render(0));
    previous.addEventListener('click', () => render(Math.max(0, current - 1)));
    next.addEventListener('click', () => {
        const active = stages[current];
        const invalid = [...active.querySelectorAll('input[required], select[required], textarea[required]')].find((field) => !field.checkValidity());
        if (invalid) {
            invalid.setAttribute('aria-invalid', 'true');
            invalid.focus();
            error.textContent = 'Completa los campos obligatorios de esta etapa.';
            error.hidden = false;
            return;
        }
        active.querySelectorAll('[aria-invalid="true"]').forEach((field) => field.removeAttribute('aria-invalid'));
        render(Math.min(stages.length - 1, current + 1));
    });
    confirm.addEventListener('click', () => {
        const acknowledgement = body.querySelector('[data-ff-step-ack]');
        if (acknowledgement && !acknowledgement.checked) {
            error.textContent = 'Marca la confirmación visual para continuar.';
            error.hidden = false;
            return;
        }
        const messages = {
            apertura: 'Integración pendiente: no se abrió una caja ni se registró saldo inicial.',
            cierre: 'Integración pendiente: no se cerró la caja ni se conciliaron importes.',
            nota: 'Integración pendiente: no se emitió una nota ni se consultó SUNAT.'
        };
        feedback.textContent = messages[dialog.dataset.ffDialog] ?? 'Integración pendiente: no se guardaron datos ni se ejecutó la operación.';
        feedback.hidden = false;
        confirm.disabled = true;
        error.hidden = true;
    });
    render(0);
}

/**
 * Ejecuta la operación confirmSimpleDialog utilizada por el módulo JavaScript.
 *
 * @param {*} dialog - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
function confirmSimpleDialog(dialog) {
    const fields = [...dialog.querySelectorAll('.tc-ff__dialog-body input[required], .tc-ff__dialog-body select[required], .tc-ff__dialog-body textarea[required]')];
    const invalid = fields.find((field) => !field.checkValidity());
    const error = dialog.querySelector('[data-ff-simple-error]');
    const feedback = dialog.querySelector('[data-ff-simple-feedback]');
    if (invalid) {
        invalid.setAttribute('aria-invalid', 'true');
        error.textContent = 'Completa los campos obligatorios y revisa los valores.';
        error.hidden = false;
        invalid.focus();
        return;
    }
    fields.forEach((field) => field.removeAttribute('aria-invalid'));
    error.hidden = true;
    feedback.textContent = dialog.dataset.ffDialog === 'abono'
        ? 'Integración pendiente: no se registró un abono ni se cambió el saldo.'
        : dialog.dataset.ffDialog === 'nueva-serie'
            ? 'Integración pendiente: no se creó una serie ni se reservó numeración fiscal.'
            : dialog.dataset.ffDialog === 'correlativo'
                ? 'Integración pendiente: no se modificó ni incrementó ningún correlativo.'
                : dialog.dataset.ffDialog === 'obligacion'
                    ? 'Integración pendiente: no se creó una cuenta por cobrar o pagar.'
                    : 'Integración pendiente: no se guardaron cambios ni se modificó una caja real.';
    feedback.hidden = false;
    dialog.querySelector('[data-ff-simple-confirm]').disabled = true;
}

/**
 * Recupera y presenta el detalle de la información solicitada.
 *
 * @param {*} page - Parámetro recibido por la función.
 *
 * @param {*} name - Parámetro recibido por la función.
 *
 * @param {*} row - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
function showOperation(page, name, row) {
    const dialog = page.querySelector('[data-ff-dialog="operation"]');
    if (!dialog) return;
    const title = dialog.querySelector('[data-ff-operation-title]');
    const warning = dialog.querySelector('[data-ff-operation-warning]');
    const choiceGroup = dialog.querySelector('[data-ff-operation-choice-group]');
    const reasonGroup = dialog.querySelector('[data-ff-operation-reason-group]');
    const choice = dialog.querySelector('[data-ff-operation-choice]');
    const reason = dialog.querySelector('[data-ff-operation-reason]');
    const ack = dialog.querySelector('[data-ff-operation-ack]');
    const feedback = dialog.querySelector('[data-ff-operation-feedback]');
    const error = dialog.querySelector('[data-ff-operation-error]');
    const confirm = dialog.querySelector('[data-ff-operation-confirm]');
    const code = row?.dataset.ffCode ?? '';
    const messages = {
        'revisar-reembolso': ['Revisar solicitud de reembolso', `La solicitud ${code} es un ejemplo. Aprobar o rechazar requiere revisión autorizada; no se cambiará su estado.`],
        'ejecutar-reembolso': ['Ejecutar reembolso aprobado', `La solicitud ${code} aparece aprobada solo en la maqueta. La aprobación no equivale a devolver el dinero y no se hará un desembolso.`],
        'validacion-documento': ['Validar documento relacionado', `La validación del documento ${code} es ilustrativa. No se consultó una factura ni se contactó con SUNAT.`],
        'cambio-serie': ['Cambiar estado de serie activa', `La serie ${code} está activa en un ejemplo. Un cambio puede afectar numeración; no se modificará estado ni correlativo.`],
        'sunat-reenviar-row': ['Reenvío de comprobante', `El registro ${code} es ilustrativo. Integración SUNAT pendiente: no se enviará ninguna comunicación.`]
    };
    const [heading, message] = messages[name] ?? ['Confirmar acción financiera', 'La operación es solo demostrativa.'];
    title.textContent = heading;
    warning.textContent = message;
    choiceGroup.hidden = name !== 'revisar-reembolso';
    choice.value = '';
    reasonGroup.hidden = name !== 'revisar-reembolso';
    reason.value = '';
    ack.checked = false;
    feedback.hidden = true;
    error.hidden = true;
    confirm.disabled = false;
    confirm.dataset.ffOperation = name;
    dialog.showModal();
}

/**
 * Ejecuta la operación initializeOperationDialog utilizada por el módulo JavaScript.
 *
 * @param {*} page - Parámetro recibido por la función.
 *
 * @param {*} dialog - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
function initializeOperationDialog(page, dialog) {
    if (!dialog) return;
    const choice = dialog.querySelector('[data-ff-operation-choice]');
    const reasonGroup = dialog.querySelector('[data-ff-operation-reason-group]');
    choice.addEventListener('change', () => { reasonGroup.hidden = choice.value !== 'rechazar'; });
    dialog.querySelector('[data-ff-operation-confirm]').addEventListener('click', (event) => {
        const confirm = event.currentTarget;
        const name = confirm.dataset.ffOperation;
        const choiceInput = dialog.querySelector('[data-ff-operation-choice]');
        const reason = dialog.querySelector('[data-ff-operation-reason]');
        const ack = dialog.querySelector('[data-ff-operation-ack]');
        const error = dialog.querySelector('[data-ff-operation-error]');
        const feedback = dialog.querySelector('[data-ff-operation-feedback]');
        if (name === 'revisar-reembolso' && (!choiceInput.value || (choiceInput.value === 'rechazar' && !reason.value.trim()))) {
            error.textContent = 'Selecciona aprobar o rechazar e indica un motivo si rechazas.';
            error.hidden = false;
            return;
        }
        if (!ack.checked) {
            error.textContent = 'Confirma que entiendes que esta acción no modificará datos.';
            error.hidden = false;
            return;
        }
        const messages = {
            'revisar-reembolso': choiceInput.value === 'aprobar' ? 'Integración pendiente: no se aprobó la solicitud ni cambió su estado.' : 'Integración pendiente: no se rechazó la solicitud ni cambió su estado.',
            'ejecutar-reembolso': 'Integración pendiente: no se devolvió dinero ni se registró un movimiento.',
            'validacion-documento': 'Integración pendiente: el documento no se verificó ni se contactó con SUNAT.',
            'cambio-serie': 'Integración pendiente: la serie y su correlativo permanecen sin cambios.',
            'sunat-reenviar-row': 'Integración SUNAT pendiente: no se envió ningún comprobante.'
        };
        feedback.textContent = messages[name] ?? 'Integración pendiente: no se ejecutó ninguna operación real.';
        feedback.hidden = false;
        confirm.disabled = true;
        error.hidden = true;
    });
}

/**
 * Ejecuta la operación initializeSunatDialogs utilizada por el módulo JavaScript.
 *
 * @param {*} retryDialog - Parámetro recibido por la función.
 *
 * @param {*} cdrDialog - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
function initializeSunatDialogs(retryDialog, cdrDialog) {
    retryDialog?.querySelector('[data-ff-sunat-confirm]')?.addEventListener('click', (event) => {
        const feedback = retryDialog.querySelector('[data-ff-sunat-feedback]');
        feedback.textContent = 'Integración SUNAT pendiente: no se reenvió el comprobante ni se obtuvo una respuesta.';
        feedback.hidden = false;
        event.currentTarget.disabled = true;
    });
    cdrDialog?.querySelector('[data-ff-sunat-cdr-confirm]')?.addEventListener('click', (event) => {
        const field = cdrDialog.querySelector('.tc-ff__dialog-body input[required]');
        const error = cdrDialog.querySelector('[data-ff-sunat-error]');
        const feedback = cdrDialog.querySelector('[data-ff-sunat-cdr-feedback]');
        if (!field.checkValidity()) {
            field.setAttribute('aria-invalid', 'true');
            error.textContent = 'Ingresa una referencia ilustrativa del comprobante.';
            error.hidden = false;
            field.focus();
            return;
        }
        error.hidden = true;
        feedback.textContent = 'Integración SUNAT pendiente: no se consultó estado ni se descargó un CDR.';
        feedback.hidden = false;
        event.currentTarget.disabled = true;
    });
}

/**
 * Ejecuta la operación initializeNoteEvidence utilizada por el módulo JavaScript.
 *
 * @param {*} dialog - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
function initializeNoteEvidence(dialog) {
    const input = dialog?.querySelector('[data-ff-note-evidence]');
    const name = dialog?.querySelector('[data-ff-note-evidence-name]');
    input?.addEventListener('change', () => {
        const file = input.files?.[0] ?? null;
        name.textContent = file ? `Seleccionado: ${file.name}. No se leerá ni transmitirá.` : 'No se ha seleccionado sustento. El archivo no se leerá ni transmitirá.';
    });
}
