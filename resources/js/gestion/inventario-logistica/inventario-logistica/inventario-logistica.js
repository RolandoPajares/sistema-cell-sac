/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/gestion/inventario-logistica/inventario-logistica/inventario-logistica.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

// Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
document.querySelectorAll('[data-inventario-diseno]').forEach((page) => {
    const search = page.querySelector('[data-il-search]');
    const filters = [...page.querySelectorAll('[data-il-filter]')];
    const rows = [...page.querySelectorAll('[data-il-row]')];
    const empty = page.querySelector('[data-il-empty]');
    const mode = page.dataset.ilMode;

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
                const field = filter.dataset.ilFilter;
                const key = `il${field.charAt(0).toUpperCase()}${field.slice(1)}`;

                return !selected || row.dataset[key] === selected;
            });

            row.hidden = !(textMatches && filtersMatch);
            visible += Number(!row.hidden);
        });

        // Condicional: evalua "empty" para decidir si ejecuta el bloque siguiente.
        if (empty) {
            empty.hidden = visible > 0;
        }
    };

    // Evento: escucha el evento input y ejecuta la funcion asociada cuando ocurre.
    search?.addEventListener('input', applyFilters);
    // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
    filters.forEach((filter) => filter.addEventListener('change', applyFilters));
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    page.querySelector('[data-il-apply]')?.addEventListener('click', applyFilters);
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    page.querySelector('[data-il-history]')?.addEventListener('click', () => {
        page.querySelector('.tc-il__table-card')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    const dialogs = [...page.querySelectorAll('[data-il-dialog]')];
/**
 * Ejecuta la operación getDialog utilizada por el módulo JavaScript.
 *
 * @param {*} name - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
    // Funcion: getDialog concentra una tarea reutilizable del modulo.
    const getDialog = (name) => dialogs.find((dialog) => dialog.dataset.ilDialog === name);
/**
 * Gestiona la interacción y actualiza el estado visual correspondiente.
 *
 * @param {*} dialog - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
    // Funcion: closeDialog concentra una tarea reutilizable del modulo.
    const closeDialog = (dialog) => {
        // Condicional: evalua "dialog?.open" para decidir si ejecuta el bloque siguiente.
        if (dialog?.open) {
            dialog.close();
        }
    };

/**
 * Ejecuta la operación renderDetails utilizada por el módulo JavaScript.
 *
 * @param {*} row - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
    // Funcion: renderDetails concentra una tarea reutilizable del modulo.
    const renderDetails = (row) => {
        const dialog = getDialog('detalle');
        const details = dialog?.querySelector('[data-il-detail-fields]');
        const headings = [...row.closest('table').querySelectorAll('thead th')];
        const cells = [...row.querySelectorAll('td')];

        // Condicional: evalua "!dialog || !details" para decidir si ejecuta el bloque siguiente.
        if (!dialog || !details) {
            return;
        }

        const timeline = dialog.querySelector('[data-il-shipment-timeline]');
        // Condicional: evalua "timeline" para decidir si ejecuta el bloque siguiente.
        if (timeline) timeline.hidden = mode !== 'envios';
        details.replaceChildren();
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        cells.slice(0, headings.length - 1).forEach((cell, index) => {
            const wrapper = document.createElement('div');
            const term = document.createElement('dt');
            const description = document.createElement('dd');
            term.textContent = headings[index]?.textContent.trim() ?? `Dato ${index + 1}`;
            description.textContent = cell.textContent.trim();
            wrapper.append(term, description);
            details.append(wrapper);
        });
        dialog.showModal();
    };

/**
 * Recupera y presenta el detalle de la información solicitada.
 *
 * @param {*} row - Parámetro recibido por la función.
 *
 * @param {*} operation - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
    // Funcion: showOperation concentra una tarea reutilizable del modulo.
    const showOperation = (row, operation) => {
        const dialog = getDialog('operacion');
        const warning = dialog?.querySelector('[data-il-operation-warning]');
        const feedback = dialog?.querySelector('[data-il-operation-feedback]');
        const confirm = dialog?.querySelector('[data-il-operation-confirm]');
        const evidenceField = dialog?.querySelector('[data-il-evidence-field]');
        const evidenceName = dialog?.querySelector('[data-il-evidence-name]');
        const recordCode = row.dataset.ilCode || 'registro de ejemplo';
        const operationMessages = {
            despacho: `Confirma visualmente el despacho de ${recordCode}. No se reserva ni descuenta stock y el estado del registro no cambiará.`,
            recepcion: `Confirma visualmente la recepción de ${recordCode}. No se incrementa stock ni se modifica el estado del registro.`,
            entrega: `Confirma visualmente la entrega de ${recordCode}. No se actualiza el pedido ni el estado del envío. La evidencia es opcional y no será procesada.`,
            duplicidad: `Este registro de ejemplo está marcado como posible duplicado. No se consultó la base real y no se confirma que exista una serie repetida.`
        };

        // Condicional: evalua "!dialog || !warning || !confirm" para decidir si ejecuta el bloque siguiente.
        if (!dialog || !warning || !confirm) {
            return;
        }

        warning.textContent = operationMessages[operation] ?? 'Esta acción solo muestra una confirmación visual.';
        feedback.hidden = true;
        feedback.textContent = '';
        confirm.disabled = false;
        confirm.dataset.operation = operation;
        evidenceField.hidden = operation !== 'entrega';
        // Condicional: evalua "evidenceName" para decidir si ejecuta el bloque siguiente.
        if (evidenceName) {
            evidenceName.textContent = 'No se ha seleccionado evidencia. No se leerá ni transmitirá el archivo.';
        }
        const evidenceInput = dialog.querySelector('[data-il-evidence-file]');
        // Condicional: evalua "evidenceInput" para decidir si ejecuta el bloque siguiente.
        if (evidenceInput) {
            evidenceInput.value = '';
        }
        dialog.showModal();
    };

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    page.querySelectorAll('[data-il-open]').forEach((button) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        button.addEventListener('click', () => {
            const requested = button.dataset.ilOpen;
            const dialogName = requested === 'ruta' ? 'envio' : requested;
            const dialog = getDialog(dialogName);

            // Condicional: evalua "!dialog" para decidir si ejecuta el bloque siguiente.
            if (!dialog) {
                return;
            }

            // Condicional: evalua "dialogName === 'envio'" para decidir si ejecuta el bloque siguiente.
            if (dialogName === 'envio') {
                resetStepper(dialog, requested === 'ruta' ? 1 : 0);
            // Condicional alternativo: este bloque se ejecuta cuando la comprobacion anterior no se cumple.
            } else if (dialogName === 'transferencia') {
                resetStepper(dialog, 0);
            // Condicional alternativo: este bloque se ejecuta cuando la comprobacion anterior no se cumple.
            } else if (dialogName === 'serie') {
                const feedback = dialog.querySelector('[data-il-series-feedback]');
                const error = dialog.querySelector('[data-il-series-error]');
                const confirm = dialog.querySelector('[data-il-series-confirm]');
                // Condicional: evalua "feedback" para decidir si ejecuta el bloque siguiente.
                if (feedback) feedback.hidden = true;
                // Condicional: evalua "error" para decidir si ejecuta el bloque siguiente.
                if (error) error.hidden = true;
                // Condicional: evalua "confirm" para decidir si ejecuta el bloque siguiente.
                if (confirm) confirm.disabled = false;
            // Condicional alternativo: este bloque se ejecuta cuando la comprobacion anterior no se cumple.
            } else if (dialogName === 'importar-series') {
                resetImportWizard(dialog);
            }

            dialog.showModal();
        });
    });

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    page.querySelectorAll('[data-il-detail]').forEach((button) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        button.addEventListener('click', () => renderDetails(button.closest('[data-il-row]')));
    });

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    page.querySelectorAll('[data-il-row-action-button]').forEach((button) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        button.addEventListener('click', () => {
            const row = button.closest('[data-il-row]');
            showOperation(row, row?.dataset.ilRowAction);
        });
    });

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    dialogs.forEach((dialog) => {
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        dialog.querySelectorAll('[data-il-close]').forEach((button) => {
            // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
            button.addEventListener('click', () => closeDialog(dialog));
        });
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        dialog.addEventListener('click', (event) => {
            // Condicional: evalua "event.target === dialog" para decidir si ejecuta el bloque siguiente.
            if (event.target === dialog) {
                closeDialog(dialog);
            }
        });
    });

    const operationDialog = getDialog('operacion');
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    operationDialog?.querySelector('[data-il-operation-confirm]')?.addEventListener('click', (event) => {
        const confirm = event.currentTarget;
        const feedback = operationDialog.querySelector('[data-il-operation-feedback]');
        const operation = confirm.dataset.operation;
        const messages = {
            despacho: 'Integración pendiente: no se despachó ni cambió el estado del registro.',
            recepcion: 'Integración pendiente: no se recibió mercancía ni cambió el estado del registro.',
            entrega: 'Integración pendiente: no se confirmó una entrega real ni se modificó el pedido.',
            duplicidad: 'Aviso ilustrativo: no se buscó ni se modificó ningún IMEI o número de serie.'
        };
        feedback.textContent = messages[operation] ?? 'Integración pendiente: no se ejecutó ninguna operación real.';
        feedback.hidden = false;
        confirm.disabled = true;
    });

    // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
    operationDialog?.querySelector('[data-il-evidence-file]')?.addEventListener('change', (event) => {
        const file = event.currentTarget.files?.[0] ?? null;
        const name = operationDialog.querySelector('[data-il-evidence-name]');
        // Condicional: evalua "name" para decidir si ejecuta el bloque siguiente.
        if (name) {
            name.textContent = file ? `Seleccionado: ${file.name}. No se leerá ni transmitirá.` : 'No se ha seleccionado evidencia. No se leerá ni transmitirá el archivo.';
        }
    });

    const transferDialog = getDialog('transferencia');
    // Condicional: evalua "transferDialog" para decidir si ejecuta el bloque siguiente.
    if (transferDialog) {
        const products = transferDialog.querySelector('[data-il-transfer-products]');
        const template = transferDialog.querySelector('[data-il-transfer-template]');
        const sourceSite = transferDialog.querySelector('[data-il-transfer-source-site]');
        const destinationSite = transferDialog.querySelector('[data-il-transfer-destination-site]');
        const sourceWarehouse = transferDialog.querySelector('[data-il-transfer-source-warehouse]');
        const destinationWarehouse = transferDialog.querySelector('[data-il-transfer-destination-warehouse]');
        const transferError = transferDialog.querySelector('[data-il-transfer-error]');

        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        transferDialog.querySelector('[data-il-transfer-add]')?.addEventListener('click', () => {
            // Condicional: evalua "template && products" para decidir si ejecuta el bloque siguiente.
            if (template && products) {
                products.append(template.content.cloneNode(true));
                updateTransferRemoveButtons(products);
            }
        });
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        products?.addEventListener('click', (event) => {
            const remove = event.target.closest('[data-il-transfer-remove]');
            // Condicional: evalua "remove && !remove.disabled" para decidir si ejecuta el bloque siguiente.
            if (remove && !remove.disabled) {
                remove.closest('[data-il-transfer-product-row]')?.remove();
                updateTransferRemoveButtons(products);
            }
        });
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        transferDialog.querySelector('[data-il-next]')?.addEventListener('click', () => {
            const step = currentStep(transferDialog);
            // Condicional: evalua "step === 3" para decidir si ejecuta el bloque siguiente.
            if (step === 3) {
                return;
            }
            // Condicional: evalua "!validateVisibleStep(transferDialog)" para decidir si ejecuta el bloque siguiente.
            if (!validateVisibleStep(transferDialog)) {
                return;
            }
            // Condicional: evalua "step === 1 && sourceSite.value === destinationSite.value" para decidir si ejecuta el bloque siguiente.
            if (step === 1 && sourceSite.value === destinationSite.value) {
                transferError.textContent = 'La sede de origen y la sede de destino deben ser distintas.';
                transferError.hidden = false;
                return;
            }
            transferError.hidden = true;
            // Condicional: evalua "step === 2" para decidir si ejecuta el bloque siguiente.
            if (step === 2) {
                const rowsInTransfer = [...products.querySelectorAll('[data-il-transfer-product-row]')];
                // Condicional: evalua "rowsInTransfer.some((row) => Number(row.querySelector('input[type="number"]')?.value) <= 0)" para decidir si ejecuta el bloque siguiente.
                if (rowsInTransfer.some((row) => Number(row.querySelector('input[type="number"]')?.value) <= 0)) {
                    showStepError(transferDialog, 'Cada cantidad debe ser mayor que cero.');
                    return;
                }
            }
            const next = step + 1;
            // Condicional: evalua "next === 3" para decidir si ejecuta el bloque siguiente.
            if (next === 3) {
                renderTransferReview(transferDialog);
            }
            renderStepper(transferDialog, next);
        });
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        transferDialog.querySelector('[data-il-previous]')?.addEventListener('click', () => renderStepper(transferDialog, Math.max(0, currentStep(transferDialog) - 1)));
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        transferDialog.querySelector('[data-il-confirm]')?.addEventListener('click', () => {
            const acknowledgement = transferDialog.querySelector('[data-il-transfer-ack]');
            const feedback = transferDialog.querySelector('[data-il-transfer-feedback]');
            // Condicional: evalua "!acknowledgement.checked" para decidir si ejecuta el bloque siguiente.
            if (!acknowledgement.checked) {
                showStepError(transferDialog, 'Marca la confirmación para continuar.');
                return;
            }
            feedback.textContent = 'Integración pendiente: no se creó, despachó ni recibió una transferencia y no se modificó stock.';
            feedback.hidden = false;
            transferDialog.querySelector('[data-il-confirm]').disabled = true;
            showStepError(transferDialog, '');
        });
    }

    const shipmentDialog = getDialog('envio');
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    shipmentDialog?.querySelector('[data-il-next]')?.addEventListener('click', () => {
        const step = currentStep(shipmentDialog);
        // Condicional: evalua "step === 2" para decidir si ejecuta el bloque siguiente.
        if (step === 2) {
            return;
        }
        // Condicional: evalua "!validateVisibleStep(shipmentDialog)" para decidir si ejecuta el bloque siguiente.
        if (!validateVisibleStep(shipmentDialog)) {
            return;
        }
        renderStepper(shipmentDialog, step + 1);
    });
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    shipmentDialog?.querySelector('[data-il-previous]')?.addEventListener('click', () => renderStepper(shipmentDialog, Math.max(0, currentStep(shipmentDialog) - 1)));
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    shipmentDialog?.querySelector('[data-il-confirm]')?.addEventListener('click', () => {
        const acknowledgement = shipmentDialog.querySelector('[data-il-shipment-ack]');
        const feedback = shipmentDialog.querySelector('[data-il-shipment-feedback]');
        // Condicional: evalua "!acknowledgement.checked" para decidir si ejecuta el bloque siguiente.
        if (!acknowledgement.checked) {
            showStepError(shipmentDialog, 'Marca la confirmación para continuar.');
            return;
        }
        feedback.textContent = 'Integración pendiente: no se creó un envío ni se actualizó un pedido o estado.';
        feedback.hidden = false;
        shipmentDialog.querySelector('[data-il-confirm]').disabled = true;
        showStepError(shipmentDialog, '');
    });

    const seriesDialog = getDialog('serie');
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    seriesDialog?.querySelector('[data-il-series-confirm]')?.addEventListener('click', () => {
        const fields = [...seriesDialog.querySelectorAll('.tc-il__dialog-body input[required], .tc-il__dialog-body select[required]')];
        // Funcion: invalid concentra una tarea reutilizable del modulo.
        const invalid = fields.find((field) => !field.checkValidity());
        const error = seriesDialog.querySelector('[data-il-series-error]');
        const feedback = seriesDialog.querySelector('[data-il-series-feedback]');
        // Condicional: evalua "invalid" para decidir si ejecuta el bloque siguiente.
        if (invalid) {
            invalid.setAttribute('aria-invalid', 'true');
            error.textContent = 'Completa los campos requeridos y revisa el formato ilustrativo.';
            error.hidden = false;
            invalid.focus();
            return;
        }
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        fields.forEach((field) => field.removeAttribute('aria-invalid'));
        error.hidden = true;
        feedback.textContent = 'Integración pendiente: no se registró la serie, no se consultaron duplicados y no se creó stock.';
        feedback.hidden = false;
        seriesDialog.querySelector('[data-il-series-confirm]').disabled = true;
    });

    const importDialog = getDialog('importar-series');
    // Condicional: evalua "importDialog" para decidir si ejecuta el bloque siguiente.
    if (importDialog) {
        const wizard = importDialog.querySelector('[data-il-import-wizard]');
        const fileInput = importDialog.querySelector('[data-il-series-file]');
        const fileName = importDialog.querySelector('[data-il-series-file-name]');
        const error = importDialog.querySelector('[data-il-import-error]');
        const feedback = importDialog.querySelector('[data-il-series-import-feedback]');
        const previous = importDialog.querySelector('[data-il-import-previous]');
        const next = importDialog.querySelector('[data-il-import-next]');
        const confirm = importDialog.querySelector('[data-il-import-confirm]');
        const acknowledgement = importDialog.querySelector('[data-il-series-import-ack]');
/**
 * Ejecuta la operación currentImportStep utilizada por el módulo JavaScript.
 *
 * @returns {*} Resultado de la operación.
 */
        // Funcion: currentImportStep concentra una tarea reutilizable del modulo.
        const currentImportStep = () => Number(wizard.dataset.ilImportStep ?? 0);

        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        importDialog.querySelector('[data-il-series-template]')?.addEventListener('click', () => downloadCsv('plantilla-imei-series.csv', 'imei_o_serie,producto,almacen,condicion'));
        // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
        fileInput.addEventListener('change', () => {
            const file = fileInput.files?.[0] ?? null;
            // Condicional: evalua "file && !/\.(csv|xlsx)$/i.test(file.name)" para decidir si ejecuta el bloque siguiente.
            if (file && !/\.(csv|xlsx)$/i.test(file.name)) {
                wizard.dataset.ilSelectedFile = '';
                fileInput.value = '';
                fileName.textContent = 'No se ha seleccionado un archivo.';
                error.textContent = 'Selecciona un archivo CSV o XLSX.';
                error.hidden = false;
                return;
            }
            wizard.dataset.ilSelectedFile = file?.name ?? '';
            fileName.textContent = file ? `Seleccionado: ${file.name}` : 'No se ha seleccionado un archivo.';
            error.hidden = true;
        });
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        previous.addEventListener('click', () => renderImportStep(importDialog, Math.max(0, currentImportStep() - 1)));
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        next.addEventListener('click', () => {
            const step = currentImportStep();
            // Condicional: evalua "step === 1 && !wizard.dataset.ilSelectedFile" para decidir si ejecuta el bloque siguiente.
            if (step === 1 && !wizard.dataset.ilSelectedFile) {
                error.textContent = 'Selecciona un archivo para continuar con la vista previa ilustrativa.';
                error.hidden = false;
                return;
            }
            renderImportStep(importDialog, Math.min(4, step + 1));
        });
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        confirm.addEventListener('click', () => {
            // Condicional: evalua "!acknowledgement.checked" para decidir si ejecuta el bloque siguiente.
            if (!acknowledgement.checked) {
                error.textContent = 'Confirma que revisaste los ejemplos y las validaciones ilustrativas.';
                error.hidden = false;
                return;
            }
            feedback.textContent = 'Funcionalidad visual. Integración pendiente. No se realizaron cambios. No se importaron series, no se consultó duplicidad y no se modificó stock.';
            feedback.hidden = false;
            confirm.disabled = true;
            error.hidden = true;
        });
    }
});

/**
 * Ejecuta la operación currentStep utilizada por el módulo JavaScript.
 *
 * @param {*} dialog - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: currentStep concentra una tarea reutilizable del modulo.
function currentStep(dialog) {
    return Number(dialog.querySelector('[data-il-step]:not([hidden])')?.dataset.ilStep ?? 0);
}

/**
 * Ejecuta la operación resetStepper utilizada por el módulo JavaScript.
 *
 * @param {*} dialog - Parámetro recibido por la función.
 *
 * @param {*} initialStep - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: resetStepper concentra una tarea reutilizable del modulo.
function resetStepper(dialog, initialStep = 0) {
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    dialog.querySelectorAll('[data-il-step]').forEach((step) => {
        step.hidden = Number(step.dataset.ilStep) !== initialStep;
    });
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    dialog.querySelectorAll('[data-il-step-indicator]').forEach((indicator) => {
        indicator.classList.toggle('is-current', Number(indicator.dataset.ilStepIndicator) === initialStep);
    });
    const previous = dialog.querySelector('[data-il-previous]');
    const next = dialog.querySelector('[data-il-next]');
    const confirm = dialog.querySelector('[data-il-confirm]');
    const error = dialog.querySelector('[data-il-step-error]');
    // Condicional: evalua "previous" para decidir si ejecuta el bloque siguiente.
    if (previous) previous.hidden = initialStep === 0;
    // Condicional: evalua "next) next.hidden = initialStep === dialog.querySelectorAll('[data-il-step]'" para decidir si ejecuta el bloque siguiente.
    if (next) next.hidden = initialStep === dialog.querySelectorAll('[data-il-step]').length - 1;
    // Condicional: evalua "confirm" para decidir si ejecuta el bloque siguiente.
    if (confirm) {
        confirm.hidden = initialStep !== dialog.querySelectorAll('[data-il-step]').length - 1;
        confirm.disabled = false;
    }
    // Condicional: evalua "error" para decidir si ejecuta el bloque siguiente.
    if (error) error.hidden = true;
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    dialog.querySelectorAll('[data-il-transfer-feedback], [data-il-shipment-feedback]').forEach((feedback) => { feedback.hidden = true; });
    const acknowledgement = dialog.querySelector('[data-il-transfer-ack], [data-il-shipment-ack]');
    // Condicional: evalua "acknowledgement" para decidir si ejecuta el bloque siguiente.
    if (acknowledgement) acknowledgement.checked = false;
}

/**
 * Ejecuta la operación renderStepper utilizada por el módulo JavaScript.
 *
 * @param {*} dialog - Parámetro recibido por la función.
 *
 * @param {*} step - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: renderStepper concentra una tarea reutilizable del modulo.
function renderStepper(dialog, step) {
    resetStepper(dialog, step);
}

/**
 * Ejecuta la operación validateVisibleStep utilizada por el módulo JavaScript.
 *
 * @param {*} dialog - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: validateVisibleStep concentra una tarea reutilizable del modulo.
function validateVisibleStep(dialog) {
    const active = dialog.querySelector('[data-il-step]:not([hidden])');
    // Funcion: invalid concentra una tarea reutilizable del modulo.
    const invalid = [...(active?.querySelectorAll('input[required], select[required], textarea[required]') ?? [])].find((field) => !field.checkValidity());
    // Condicional: evalua "invalid" para decidir si ejecuta el bloque siguiente.
    if (invalid) {
        invalid.setAttribute('aria-invalid', 'true');
        invalid.focus();
        showStepError(dialog, 'Completa los campos obligatorios para continuar.');
        return false;
    }
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    active?.querySelectorAll('[aria-invalid="true"]').forEach((field) => field.removeAttribute('aria-invalid'));
    showStepError(dialog, '');
    return true;
}

/**
 * Recupera y presenta el detalle de la información solicitada.
 *
 * @param {*} dialog - Parámetro recibido por la función.
 *
 * @param {*} message - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: showStepError concentra una tarea reutilizable del modulo.
function showStepError(dialog, message) {
    const error = dialog.querySelector('[data-il-step-error]');
    // Condicional: evalua "error" para decidir si ejecuta el bloque siguiente.
    if (error) {
        error.textContent = message;
        error.hidden = message === '';
    }
}

/**
 * Actualiza la información visible o el estado del componente.
 *
 * @param {*} container - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: updateTransferRemoveButtons concentra una tarea reutilizable del modulo.
function updateTransferRemoveButtons(container) {
    const rows = [...container.querySelectorAll('[data-il-transfer-product-row]')];
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    rows.forEach((row) => {
        const remove = row.querySelector('[data-il-transfer-remove]');
        // Condicional: evalua "remove" para decidir si ejecuta el bloque siguiente.
        if (remove) remove.disabled = rows.length === 1;
    });
}

/**
 * Ejecuta la operación renderTransferReview utilizada por el módulo JavaScript.
 *
 * @param {*} dialog - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: renderTransferReview concentra una tarea reutilizable del modulo.
function renderTransferReview(dialog) {
    const list = dialog.querySelector('[data-il-transfer-review]');
    const rows = [...dialog.querySelectorAll('[data-il-transfer-product-row]')];
    const sourceSite = dialog.querySelector('[data-il-transfer-source-site]');
    const sourceWarehouse = dialog.querySelector('[data-il-transfer-source-warehouse]');
    const destinationSite = dialog.querySelector('[data-il-transfer-destination-site]');
    const destinationWarehouse = dialog.querySelector('[data-il-transfer-destination-warehouse]');
    const values = [
        ['Origen', `${sourceSite.value} · ${sourceWarehouse.value}`],
        ['Destino', `${destinationSite.value} · ${destinationWarehouse.value}`],
        // Recorrido: usa map para procesar cada elemento y obtener o actualizar datos.
        ['Productos ilustrativos', rows.map((row) => `${row.querySelector('select').value} × ${row.querySelector('input').value}`).join('; ')]
    ];
    list.replaceChildren();
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    values.forEach(([label, value]) => {
        const wrapper = document.createElement('div');
        const term = document.createElement('dt');
        const description = document.createElement('dd');
        term.textContent = label;
        description.textContent = value;
        wrapper.append(term, description);
        list.append(wrapper);
    });
}

/**
 * Ejecuta la operación resetImportWizard utilizada por el módulo JavaScript.
 *
 * @param {*} dialog - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: resetImportWizard concentra una tarea reutilizable del modulo.
function resetImportWizard(dialog) {
    const wizard = dialog.querySelector('[data-il-import-wizard]');
    const fileInput = dialog.querySelector('[data-il-series-file]');
    const fileName = dialog.querySelector('[data-il-series-file-name]');
    const acknowledgement = dialog.querySelector('[data-il-series-import-ack]');
    const confirm = dialog.querySelector('[data-il-import-confirm]');
    // Condicional: evalua "fileInput" para decidir si ejecuta el bloque siguiente.
    if (fileInput) fileInput.value = '';
    // Condicional: evalua "fileName" para decidir si ejecuta el bloque siguiente.
    if (fileName) fileName.textContent = 'No se ha seleccionado un archivo.';
    // Condicional: evalua "acknowledgement" para decidir si ejecuta el bloque siguiente.
    if (acknowledgement) acknowledgement.checked = false;
    // Condicional: evalua "confirm" para decidir si ejecuta el bloque siguiente.
    if (confirm) confirm.disabled = false;
    // Condicional: evalua "wizard" para decidir si ejecuta el bloque siguiente.
    if (wizard) wizard.dataset.ilSelectedFile = '';
    renderImportStep(dialog, 0);
}

/**
 * Ejecuta la operación renderImportStep utilizada por el módulo JavaScript.
 *
 * @param {*} dialog - Parámetro recibido por la función.
 *
 * @param {*} step - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: renderImportStep concentra una tarea reutilizable del modulo.
function renderImportStep(dialog, step) {
    dialog.querySelector('[data-il-import-wizard]').dataset.ilImportStep = String(step);
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    dialog.querySelectorAll('[data-il-import-stage]').forEach((stage) => {
        stage.hidden = Number(stage.dataset.ilImportStage) !== step;
    });
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    dialog.querySelectorAll('[data-il-import-indicator]').forEach((indicator) => {
        indicator.classList.toggle('is-current', Number(indicator.dataset.ilImportIndicator) === step);
    });
    const previous = dialog.querySelector('[data-il-import-previous]');
    const next = dialog.querySelector('[data-il-import-next]');
    const confirm = dialog.querySelector('[data-il-import-confirm]');
    const error = dialog.querySelector('[data-il-import-error]');
    const feedback = dialog.querySelector('[data-il-series-import-feedback]');
    // Condicional: evalua "previous" para decidir si ejecuta el bloque siguiente.
    if (previous) previous.hidden = step === 0;
    // Condicional: evalua "next" para decidir si ejecuta el bloque siguiente.
    if (next) next.hidden = step === 4;
    // Condicional: evalua "confirm" para decidir si ejecuta el bloque siguiente.
    if (confirm) confirm.hidden = step !== 4;
    // Condicional: evalua "error" para decidir si ejecuta el bloque siguiente.
    if (error) error.hidden = true;
    // Condicional: evalua "feedback" para decidir si ejecuta el bloque siguiente.
    if (feedback) feedback.hidden = true;
}

/**
 * Ejecuta la operación downloadCsv utilizada por el módulo JavaScript.
 *
 * @param {*} filename - Parámetro recibido por la función.
 *
 * @param {*} header - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: downloadCsv concentra una tarea reutilizable del modulo.
function downloadCsv(filename, header) {
    const csv = `\uFEFF${header}\r\n`;
    const url = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8' }));
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    link.click();
    window.setTimeout(() => URL.revokeObjectURL(url), 1000);
}