/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/gestion/finanzas/finanzas-facturacion/finanzas-facturacion.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

// Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
document.querySelectorAll('[data-finanzas-diseno]').forEach((page) => {
    const search = page.querySelector('[data-ff-search]');
    const filters = [...page.querySelectorAll('[data-ff-filter]')];
    const rows = [...page.querySelectorAll('[data-ff-row]')];
    const empty = page.querySelector('[data-ff-empty]');
    const dialogs = [...page.querySelectorAll('[data-ff-dialog]')];
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    page.querySelectorAll('[data-account-application]').forEach((form) => {
        const cuenta = form.querySelector('[data-account-select]');
        const pago = form.querySelector('[data-registered-payment-select]');
/**
 * Actualiza la información visible o el estado del componente.
 *
 * @returns {*} Resultado de la operación.
 */
        // Funcion: actualizarPagosDisponibles concentra una tarea reutilizable del modulo.
        const actualizarPagosDisponibles = () => {
            // Condicional: evalua "!cuenta || !pago" para decidir si ejecuta el bloque siguiente.
            if (!cuenta || !pago) return;
            // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
            [...pago.options].forEach((opcion) => {
                // Condicional: evalua "opcion.value" para decidir si ejecuta el bloque siguiente.
                if (opcion.value) opcion.hidden = opcion.dataset.cuenta !== cuenta.value;
            });
            // Condicional: evalua "pago.selectedOptions[0]?.hidden" para decidir si ejecuta el bloque siguiente.
            if (pago.selectedOptions[0]?.hidden) pago.value = '';
        };
        // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
        cuenta?.addEventListener('change', actualizarPagosDisponibles);
        actualizarPagosDisponibles();
    });

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
            const textMatches = row.textContent.toLocaleLowerCase('es').includes(query);
            // Funcion: filtersMatch concentra una tarea reutilizable del modulo.
            const filtersMatch = filters.every((filter) => {
                const selected = filter.value;
                const field = filter.dataset.ffFilter;
                const key = `ff${field.charAt(0).toUpperCase()}${field.slice(1)}`;

                return !selected || row.dataset[key] === selected;
            });

            row.hidden = !(textMatches && filtersMatch);
            visible += Number(!row.hidden);
        });

        // Condicional: evalua "empty" para decidir si ejecuta el bloque siguiente.
        if (empty) empty.hidden = visible > 0;
    };

/**
 * Ejecuta la operación getDialog utilizada por el módulo JavaScript.
 *
 * @param {*} name - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
    // Funcion: getDialog concentra una tarea reutilizable del modulo.
    const getDialog = (name) => dialogs.find((dialog) => dialog.dataset.ffDialog === name);
/**
 * Gestiona la interacción y actualiza el estado visual correspondiente.
 *
 * @param {*} dialog - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
    // Funcion: closeDialog concentra una tarea reutilizable del modulo.
    const closeDialog = (dialog) => {
        // Condicional: evalua "dialog?.open) dialog.close(" para decidir si ejecuta el bloque siguiente.
        if (dialog?.open) dialog.close();
    };

    // Evento: escucha el evento input y ejecuta la funcion asociada cuando ocurre.
    search?.addEventListener('input', applyFilters);
    // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
    filters.forEach((filter) => filter.addEventListener('change', applyFilters));
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    page.querySelector('[data-ff-apply]')?.addEventListener('click', applyFilters);

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    page.querySelectorAll('[data-ff-navigate]').forEach((button) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        button.addEventListener('click', () => window.location.assign(button.dataset.ffNavigate));
    });
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    page.querySelectorAll('[data-ff-focus]').forEach((button) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        button.addEventListener('click', () => {
            const target = page.querySelector(`#${CSS.escape(button.dataset.ffFocus)}`) ?? page.querySelector('#cuentas-financieras-aside');
            target?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            target?.querySelector('select:not(:disabled), input:not(:disabled), textarea:not(:disabled)')?.focus({ preventScroll: true });
        });
    });
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    page.querySelectorAll('[data-ff-open]').forEach((button) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        button.addEventListener('click', () => openDialog(page, button.dataset.ffOpen, null, getDialog));
    });
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    page.querySelectorAll('[data-ff-detail]').forEach((button) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        button.addEventListener('click', () => renderDetails(getDialog('detalle'), button.closest('[data-ff-row]')));
    });
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    page.querySelectorAll('button[data-ff-row-action]').forEach((button) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        button.addEventListener('click', () => openDialog(page, button.closest('[data-ff-row]')?.dataset.ffRowAction, button.closest('[data-ff-row]'), getDialog));
    });

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    dialogs.forEach((dialog) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        dialog.querySelectorAll('[data-ff-close]').forEach((button) => button.addEventListener('click', (event) => {
            event.preventDefault();
            closeDialog(dialog);
        }));
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        dialog.addEventListener('click', (event) => {
            // Condicional: evalua "event.target === dialog) closeDialog(dialog" para decidir si ejecuta el bloque siguiente.
            if (event.target === dialog) closeDialog(dialog);
        });
        // Condicional: evalua "dialog.querySelector('[data-ff-stepper]')) initializeStepper(dialog" para decidir si ejecuta el bloque siguiente.
        if (dialog.querySelector('[data-ff-stepper]')) initializeStepper(dialog);
    });

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    page.querySelectorAll('[data-ff-simple-confirm]').forEach((button) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        button.addEventListener('click', () => confirmSimpleDialog(button.closest('dialog')));
    });
    initializeOperationDialog(page, getDialog('operation'));
    initializeSunatDialogs(getDialog('sunat-reenviar'), getDialog('sunat-cdr'));
    initializeAccountAmount(getDialog('abono'));
    initializeNoteEvidence(getDialog('nota'));
});

// Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
document.querySelectorAll('[data-finance-module]').forEach((page) => {
    const dialog = page.querySelector('[data-finance-dialog="crear"]');
    // Condicional: evalua "!dialog" para decidir si ejecuta el bloque siguiente.
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
    // Funcion: syncMethodFields concentra una tarea reutilizable del modulo.
    const syncMethodFields = () => {
        // Condicional: evalua "!method" para decidir si ejecuta el bloque siguiente.
        if (!method) return;
        const selected = method.selectedOptions[0];
        const requiresCash = selected?.dataset.requiresCash === '1';
        const requiresBank = selected?.dataset.requiresBank === '1';
        // Condicional: evalua "cashField" para decidir si ejecuta el bloque siguiente.
        if (cashField) {
            cashField.hidden = !requiresCash;
            cashField.querySelector('select').required = requiresCash;
        }
        // Condicional: evalua "bankField" para decidir si ejecuta el bloque siguiente.
        if (bankField) {
            bankField.hidden = !requiresBank;
            bankField.querySelector('select').required = requiresBank;
        }
        // Condicional: evalua "reference" para decidir si ejecuta el bloque siguiente.
        if (reference) reference.required = requiresBank;
    };
    // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
    method?.addEventListener('change', () => {
        // Condicional: evalua "cashField) cashField.querySelector('select'" para decidir si ejecuta el bloque siguiente.
        if (cashField) cashField.querySelector('select').value = '';
        // Condicional: evalua "bankField) bankField.querySelector('select'" para decidir si ejecuta el bloque siguiente.
        if (bankField) bankField.querySelector('select').value = '';
        syncMethodFields();
    });
    syncMethodFields();

    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    page.querySelector('[data-finance-open="crear"]')?.addEventListener('click', () => dialog.showModal());
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    dialog.querySelectorAll('[data-finance-close]').forEach((button) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        button.addEventListener('click', () => dialog.close());
    });
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    dialog.addEventListener('click', (event) => {
        // Condicional: evalua "event.target === dialog) dialog.close(" para decidir si ejecuta el bloque siguiente.
        if (event.target === dialog) dialog.close();
    });
    // Condicional: evalua "page.dataset.financeReopen === 'crear') dialog.showModal(" para decidir si ejecuta el bloque siguiente.
    if (page.dataset.financeReopen === 'crear') dialog.showModal();
});

// Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
document.querySelectorAll('[data-cajas-operativas]').forEach((page) => {
    const createDialog = page.querySelector('[data-box-dialog="crear"]');
    const editDialog = page.querySelector('[data-box-dialog="editar"]');

    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    page.querySelector('[data-box-open="crear"]')?.addEventListener('click', () => createDialog?.showModal());
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    page.querySelectorAll('[data-box-edit]').forEach((button) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        button.addEventListener('click', () => {
            const form = editDialog?.querySelector('[data-box-edit-form]');
            // Condicional: evalua "!form" para decidir si ejecuta el bloque siguiente.
            if (!form) return;

            form.action = button.dataset.boxEditUrl;
            form.querySelector('[data-box-field="sede_id"]').value = button.dataset.boxSiteId;
            form.querySelector('[data-box-field="sede_nombre"]').value = button.dataset.boxSiteName;
            form.querySelector('[data-box-field="codigo"]').value = button.dataset.boxCode;
            form.querySelector('[data-box-field="nombre"]').value = button.dataset.boxName;
            editDialog.showModal();
        });
    });

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    page.querySelectorAll('[data-box-dialog]').forEach((dialog) => {
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        dialog.querySelectorAll('[data-box-close]').forEach((button) => {
            // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
            button.addEventListener('click', () => dialog.close());
        });
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        dialog.addEventListener('click', (event) => {
            // Condicional: evalua "event.target === dialog) dialog.close(" para decidir si ejecuta el bloque siguiente.
            if (event.target === dialog) dialog.close();
        });
    });
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    page.querySelectorAll('[data-box-status]').forEach((form) => {
        // Evento: escucha el evento submit y ejecuta la funcion asociada cuando ocurre.
        form.addEventListener('submit', (event) => {
            const action = form.querySelector('[data-box-status-label]').dataset.boxStatusLabel;
            // Condicional: evalua "!window.confirm(`¿Confirmas que deseas ${action} esta caja?`)) event.preventDefault(" para decidir si ejecuta el bloque siguiente.
            if (!window.confirm(`¿Confirmas que deseas ${action} esta caja?`)) event.preventDefault();
        });
    });
    // Condicional: evalua "page.dataset.cajasReopen === 'crear') createDialog?.showModal(" para decidir si ejecuta el bloque siguiente.
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
// Funcion: openDialog concentra una tarea reutilizable del modulo.
function openDialog(page, name, row, getDialog) {
    // Condicional: evalua "!name" para decidir si ejecuta el bloque siguiente.
    if (!name) return;
    // Condicional: evalua "['validacion-documento', 'cambio-serie', 'revisar-reembolso', 'ejecutar-reembolso', 'sunat-reenviar-row'].includes(name)" para decidir si ejecuta el bloque siguiente.
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
    // Condicional: evalua "!dialog" para decidir si ejecuta el bloque siguiente.
    if (!dialog) return;

    // Condicional: evalua "dialogName === 'caja') prepareBoxDialog(dialog, row" para decidir si ejecuta el bloque siguiente.
    if (dialogName === 'caja') prepareBoxDialog(dialog, row);
    // Condicional: evalua "dialogName === 'abono') prepareAccountPayment(dialog, row" para decidir si ejecuta el bloque siguiente.
    if (dialogName === 'abono') prepareAccountPayment(dialog, row);
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    dialog.querySelectorAll('[data-ff-simple-error], [data-ff-simple-feedback]').forEach((message) => { message.hidden = true; });
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
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
// Funcion: renderDetails concentra una tarea reutilizable del modulo.
function renderDetails(dialog, row) {
    const details = dialog?.querySelector('[data-ff-detail-fields]');
    // Condicional: evalua "!dialog || !row || !details" para decidir si ejecuta el bloque siguiente.
    if (!dialog || !row || !details) return;

    const headings = [...row.closest('table').querySelectorAll('thead th')];
    const cells = [...row.querySelectorAll('td')];
    details.replaceChildren();
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
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
// Funcion: prepareBoxDialog concentra una tarea reutilizable del modulo.
function prepareBoxDialog(dialog, row) {
    const title = dialog.querySelector('[data-ff-box-title]');
    const fields = [...dialog.querySelectorAll('.tc-ff__dialog-body input, .tc-ff__dialog-body select')];
    const confirm = dialog.querySelector('[data-ff-simple-confirm]');
    const notice = dialog.querySelector('[data-ff-box-notice]');
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    fields.forEach((field) => {
        // Condicional: evalua "field instanceof HTMLSelectElement" para decidir si ejecuta el bloque siguiente.
        if (field instanceof HTMLSelectElement) {
            field.selectedIndex = 0;
        // Caso alternativo: atiende la situacion en la que ninguna condicion anterior resulto verdadera.
        } else {
            field.value = '';
        }
    });
    // Condicional: evalua "!row" para decidir si ejecuta el bloque siguiente.
    if (!row) {
        // Condicional: evalua "title" para decidir si ejecuta el bloque siguiente.
        if (title) title.textContent = 'Nueva caja';
        // Condicional: evalua "confirm) confirm.textContent = 'Guardar (demostración" para decidir si ejecuta el bloque siguiente.
        if (confirm) confirm.textContent = 'Guardar (demostración)';
        // Condicional: evalua "notice" para decidir si ejecuta el bloque siguiente.
        if (notice) notice.textContent = 'No se valida apertura simultánea ni se actualiza una caja real.';
        return;
    }

    // Condicional: evalua "title" para decidir si ejecuta el bloque siguiente.
    if (title) title.textContent = 'Edición visual de datos administrativos';
    // Condicional: evalua "confirm) confirm.textContent = 'Guardar cambios (demostración" para decidir si ejecuta el bloque siguiente.
    if (confirm) confirm.textContent = 'Guardar cambios (demostración)';
    // Condicional: evalua "notice" para decidir si ejecuta el bloque siguiente.
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
// Funcion: prepareAccountPayment concentra una tarea reutilizable del modulo.
function prepareAccountPayment(dialog, row) {
    const documentField = dialog.querySelector('[data-ff-account-document]');
    const balance = dialog.querySelector('[data-ff-account-balance]');
    const kind = dialog.querySelector('[data-ff-account-kind]');
    const amount = dialog.querySelector('[data-ff-account-amount]');
    const warning = dialog.querySelector('[data-ff-account-warning]');
    // Condicional: evalua "documentField" para decidir si ejecuta el bloque siguiente.
    if (documentField) documentField.value = '';
    // Condicional: evalua "kind" para decidir si ejecuta el bloque siguiente.
    if (kind) kind.value = '';
    // Condicional: evalua "balance" para decidir si ejecuta el bloque siguiente.
    if (balance) balance.value = 'Saldo no consultado';
    // Condicional: evalua "warning" para decidir si ejecuta el bloque siguiente.
    if (warning) warning.textContent = row
        ? 'La fila seleccionada es ilustrativa y no se trasladó al formulario. No se consultó el documento ni el saldo real.'
        : 'El saldo real no está consultado. Un monto superior debe bloquearse en la integración backend.';
    // Condicional: evalua "amount" para decidir si ejecuta el bloque siguiente.
    if (amount) amount.value = '';
}

/**
 * Ejecuta la operación initializeAccountAmount utilizada por el módulo JavaScript.
 *
 * @param {*} dialog - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: initializeAccountAmount concentra una tarea reutilizable del modulo.
function initializeAccountAmount(dialog) {
    const amount = dialog?.querySelector('[data-ff-account-amount]');
    const balance = dialog?.querySelector('[data-ff-account-balance]');
    const warning = dialog?.querySelector('[data-ff-account-warning]');
    // Evento: escucha el evento input y ejecuta la funcion asociada cuando ocurre.
    amount?.addEventListener('input', () => {
        const reference = parseMoney(balance?.value ?? '');
        const value = Number(amount.value);
        // Condicional: evalua "warning && reference > 0 && value > reference" para decidir si ejecuta el bloque siguiente.
        if (warning && reference > 0 && value > reference) {
            warning.textContent = 'El monto supera el saldo ilustrativo de la fila. No se consultó saldo real; esta alerta solo es visual.';
            warning.classList.add('is-alert');
        // Condicional alternativo: este bloque se ejecuta cuando la comprobacion anterior no se cumple.
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
// Funcion: parseMoney concentra una tarea reutilizable del modulo.
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
// Funcion: initializeStepper concentra una tarea reutilizable del modulo.
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
    // Funcion: render concentra una tarea reutilizable del modulo.
    const render = (step) => {
        current = step;
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        stages.forEach((stage) => { stage.hidden = Number(stage.dataset.ffStep) !== step; });
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        indicators.forEach((indicator) => indicator.classList.toggle('is-current', Number(indicator.dataset.ffStepIndicator) === step));
        previous.hidden = step === 0;
        next.hidden = step === stages.length - 1;
        confirm.hidden = step !== stages.length - 1;
        confirm.disabled = false;
        error.hidden = true;
        feedback.hidden = true;
    };

    // Evento: escucha el evento close y ejecuta la funcion asociada cuando ocurre.
    dialog.addEventListener('close', () => render(0));
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    previous.addEventListener('click', () => render(Math.max(0, current - 1)));
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    next.addEventListener('click', () => {
        const active = stages[current];
        // Funcion: invalid concentra una tarea reutilizable del modulo.
        const invalid = [...active.querySelectorAll('input[required], select[required], textarea[required]')].find((field) => !field.checkValidity());
        // Condicional: evalua "invalid" para decidir si ejecuta el bloque siguiente.
        if (invalid) {
            invalid.setAttribute('aria-invalid', 'true');
            invalid.focus();
            error.textContent = 'Completa los campos obligatorios de esta etapa.';
            error.hidden = false;
            return;
        }
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        active.querySelectorAll('[aria-invalid="true"]').forEach((field) => field.removeAttribute('aria-invalid'));
        render(Math.min(stages.length - 1, current + 1));
    });
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    confirm.addEventListener('click', () => {
        const acknowledgement = body.querySelector('[data-ff-step-ack]');
        // Condicional: evalua "acknowledgement && !acknowledgement.checked" para decidir si ejecuta el bloque siguiente.
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
// Funcion: confirmSimpleDialog concentra una tarea reutilizable del modulo.
function confirmSimpleDialog(dialog) {
    const fields = [...dialog.querySelectorAll('.tc-ff__dialog-body input[required], .tc-ff__dialog-body select[required], .tc-ff__dialog-body textarea[required]')];
    // Funcion: invalid concentra una tarea reutilizable del modulo.
    const invalid = fields.find((field) => !field.checkValidity());
    const error = dialog.querySelector('[data-ff-simple-error]');
    const feedback = dialog.querySelector('[data-ff-simple-feedback]');
    // Condicional: evalua "invalid" para decidir si ejecuta el bloque siguiente.
    if (invalid) {
        invalid.setAttribute('aria-invalid', 'true');
        error.textContent = 'Completa los campos obligatorios y revisa los valores.';
        error.hidden = false;
        invalid.focus();
        return;
    }
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
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
// Funcion: showOperation concentra una tarea reutilizable del modulo.
function showOperation(page, name, row) {
    const dialog = page.querySelector('[data-ff-dialog="operation"]');
    // Condicional: evalua "!dialog" para decidir si ejecuta el bloque siguiente.
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
// Funcion: initializeOperationDialog concentra una tarea reutilizable del modulo.
function initializeOperationDialog(page, dialog) {
    // Condicional: evalua "!dialog" para decidir si ejecuta el bloque siguiente.
    if (!dialog) return;
    const choice = dialog.querySelector('[data-ff-operation-choice]');
    const reasonGroup = dialog.querySelector('[data-ff-operation-reason-group]');
    // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
    choice.addEventListener('change', () => { reasonGroup.hidden = choice.value !== 'rechazar'; });
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    dialog.querySelector('[data-ff-operation-confirm]').addEventListener('click', (event) => {
        const confirm = event.currentTarget;
        const name = confirm.dataset.ffOperation;
        const choiceInput = dialog.querySelector('[data-ff-operation-choice]');
        const reason = dialog.querySelector('[data-ff-operation-reason]');
        const ack = dialog.querySelector('[data-ff-operation-ack]');
        const error = dialog.querySelector('[data-ff-operation-error]');
        const feedback = dialog.querySelector('[data-ff-operation-feedback]');
        // Condicional: evalua "name === 'revisar-reembolso' && (!choiceInput.value || (choiceInput.value === 'rechazar' && !reason.value.trim()))" para decidir si ejecuta el bloque siguiente.
        if (name === 'revisar-reembolso' && (!choiceInput.value || (choiceInput.value === 'rechazar' && !reason.value.trim()))) {
            error.textContent = 'Selecciona aprobar o rechazar e indica un motivo si rechazas.';
            error.hidden = false;
            return;
        }
        // Condicional: evalua "!ack.checked" para decidir si ejecuta el bloque siguiente.
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
// Funcion: initializeSunatDialogs concentra una tarea reutilizable del modulo.
function initializeSunatDialogs(retryDialog, cdrDialog) {
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    retryDialog?.querySelector('[data-ff-sunat-confirm]')?.addEventListener('click', (event) => {
        const feedback = retryDialog.querySelector('[data-ff-sunat-feedback]');
        feedback.textContent = 'Integración SUNAT pendiente: no se reenvió el comprobante ni se obtuvo una respuesta.';
        feedback.hidden = false;
        event.currentTarget.disabled = true;
    });
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    cdrDialog?.querySelector('[data-ff-sunat-cdr-confirm]')?.addEventListener('click', (event) => {
        const field = cdrDialog.querySelector('.tc-ff__dialog-body input[required]');
        const error = cdrDialog.querySelector('[data-ff-sunat-error]');
        const feedback = cdrDialog.querySelector('[data-ff-sunat-cdr-feedback]');
        // Condicional: evalua "!field.checkValidity()" para decidir si ejecuta el bloque siguiente.
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
// Funcion: initializeNoteEvidence concentra una tarea reutilizable del modulo.
function initializeNoteEvidence(dialog) {
    const input = dialog?.querySelector('[data-ff-note-evidence]');
    const name = dialog?.querySelector('[data-ff-note-evidence-name]');
    // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
    input?.addEventListener('change', () => {
        const file = input.files?.[0] ?? null;
        name.textContent = file ? `Seleccionado: ${file.name}. No se leerá ni transmitirá.` : 'No se ha seleccionado sustento. El archivo no se leerá ni transmitirá.';
    });
}
