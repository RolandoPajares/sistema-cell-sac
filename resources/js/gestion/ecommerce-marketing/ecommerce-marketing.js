/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/gestion/ecommerce-marketing/ecommerce-marketing.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

/**
 * Gestiona la interacción y actualiza el estado visual correspondiente.
 *
 * @param {*} dialog - Parámetro recibido por la función.
 *
 * @param {*} trigger - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: openDialog concentra una tarea reutilizable del modulo.
const openDialog = (dialog, trigger) => {
    // Condicional: evalua "!dialog?.open" para decidir si ejecuta el bloque siguiente.
    if (!dialog?.open) {
        dialog?.showModal();
        trigger?.setAttribute('aria-expanded', 'true');
    }
};

/**
 * Ejecuta la operación wireDialogClose utilizada por el módulo JavaScript.
 *
 * @param {*} dialog - Parámetro recibido por la función.
 *
 * @param {*} selector - Parámetro recibido por la función.
 *
 * @param {*} onClose - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: wireDialogClose concentra una tarea reutilizable del modulo.
const wireDialogClose = (dialog, selector, onClose = () => {}) => {
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    dialog.querySelectorAll(selector).forEach((button) => button.addEventListener('click', () => dialog.close()));
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    dialog.addEventListener('click', (event) => {
        // Condicional: evalua "event.target === dialog" para decidir si ejecuta el bloque siguiente.
        if (event.target === dialog) {
            dialog.close();
        }
    });
    // Evento: escucha el evento close y ejecuta la funcion asociada cuando ocurre.
    dialog.addEventListener('close', onClose);
};

const pricesPage = document.querySelector('[data-ecommerce-precios]');

// Condicional: evalua "pricesPage" para decidir si ejecuta el bloque siguiente.
if (pricesPage) {
    const tabs = [...pricesPage.querySelectorAll('[data-em-price-tab]')];
    const rows = [...pricesPage.querySelectorAll('[data-em-price-row]')];
    const search = pricesPage.querySelector('[data-em-price-search]');
    const filters = [...pricesPage.querySelectorAll('[data-em-price-filter]')];
    const dialog = pricesPage.querySelector('[data-em-dialog]');
    const notice = dialog.querySelector('[data-em-price-notice]');
    const saveButton = dialog.querySelector('[data-em-price-submit]');
    const priceForm = dialog.querySelector('[data-em-wholesale-price-form]');
    const bulk = dialog.querySelector('[data-em-price-bulk]');
    const bulkSteps = [...(bulk?.querySelectorAll('[data-em-bulk-step]') ?? [])];
    const bulkIndicators = [...(bulk?.querySelectorAll('[data-em-bulk-indicator]') ?? [])];
    const bulkPrevious = dialog.querySelector('[data-em-bulk-previous]');
    const bulkNext = dialog.querySelector('[data-em-bulk-next]');
    const bulkConfirm = dialog.querySelector('[data-em-bulk-confirm]');
    const bulkFile = dialog.querySelector('[data-em-bulk-file]');
    const bulkFileStatus = dialog.querySelector('[data-em-bulk-file-status]');
    const bulkError = dialog.querySelector('[data-em-bulk-error]');
    const bulkFeedback = dialog.querySelector('[data-em-bulk-feedback]');
    const editError = dialog.querySelector('[data-em-edit-error]');
    let channel = 'minorista';
    let lastTrigger;
    let activeMode = 'configuracion';
    let activeBulkStep = 0;

/**
 * Actualiza la información visible o el estado del componente.
 *
 * @returns {*} Resultado de la operación.
 */
    // Funcion: updateRows concentra una tarea reutilizable del modulo.
    const updateRows = () => {
        const query = search.value.trim().toLocaleLowerCase('es');
        let count = 0;

        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        rows.forEach((row) => {
            const textMatches = row.textContent.toLocaleLowerCase('es').includes(query);
            // Funcion: filtersMatch concentra una tarea reutilizable del modulo.
            const filtersMatch = filters.every((filter) => !filter.value || row.dataset[`em${filter.dataset.emPriceFilter.charAt(0).toUpperCase()}${filter.dataset.emPriceFilter.slice(1)}`] === filter.value);
            row.hidden = !(textMatches && filtersMatch);
            count += Number(!row.hidden);
            row.querySelector('[data-em-price-value]').textContent = row.dataset[`em${channel.charAt(0).toUpperCase()}${channel.slice(1)}`];
        });

        pricesPage.querySelector('[data-em-price-count]').textContent = String(count);
        pricesPage.querySelector('[data-em-price-empty]').hidden = count > 0;
    };

    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    tabs.forEach((tab) => tab.addEventListener('click', () => {
        channel = tab.dataset.emPriceTab;
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        tabs.forEach((candidate) => {
            candidate.classList.toggle('is-active', candidate === tab);
            candidate.setAttribute('aria-selected', String(candidate === tab));
        });
        const label = channel === 'minorista' ? 'Minorista' : 'Mayorista';
        pricesPage.querySelector('[data-em-price-channel]').textContent = label;
        pricesPage.querySelector('[data-em-price-heading]').textContent = channel;
        updateRows();
    }));

    // Evento: escucha el evento input y ejecuta la funcion asociada cuando ocurre.
    search.addEventListener('input', updateRows);
    // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
    filters.forEach((filter) => filter.addEventListener('change', updateRows));
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    pricesPage.querySelector('[data-em-price-apply]').addEventListener('click', updateRows);
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    pricesPage.querySelector('[data-em-price-clear]').addEventListener('click', () => {
        search.value = '';
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        filters.forEach((filter) => { filter.value = ''; });
        updateRows();
    });

    const titles = { configuracion: 'Configuración de precios', masiva: 'Actualización masiva', detalle: 'Detalle de precio', editar: 'Editar precio' };
/**
 * Ejecuta la operación renderBulkStep utilizada por el módulo JavaScript.
 *
 * @param {*} step - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
    // Funcion: renderBulkStep concentra una tarea reutilizable del modulo.
    const renderBulkStep = (step) => {
        activeBulkStep = step;
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        bulkSteps.forEach((stage) => { stage.hidden = Number(stage.dataset.emBulkStep) !== step; });
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        bulkIndicators.forEach((indicator) => indicator.classList.toggle('is-current', Number(indicator.dataset.emBulkIndicator) === step));
        bulkPrevious.hidden = step === 0;
        bulkNext.hidden = step === bulkSteps.length - 1;
        bulkConfirm.hidden = step !== bulkSteps.length - 1;
        bulkError.hidden = true;
        bulkFeedback.hidden = true;
        bulkConfirm.disabled = step !== bulkSteps.length - 1;
    };

/**
 * Ejecuta la operación resetBulkFlow utilizada por el módulo JavaScript.
 *
 * @returns {*} Resultado de la operación.
 */
    // Funcion: resetBulkFlow concentra una tarea reutilizable del modulo.
    const resetBulkFlow = () => {
        renderBulkStep(0);
        bulkFile.value = '';
        bulkFileStatus.textContent = 'Se procesará en el servidor dentro de una transacción.';
        bulk.querySelector('[data-em-bulk-ack]').checked = false;
        bulkError.hidden = true;
        bulkFeedback.hidden = true;
    };

/**
 * Ejecuta la operación validateEditPrice utilizada por el módulo JavaScript.
 *
 * @returns {*} Resultado de la operación.
 */
    // Funcion: validateEditPrice concentra una tarea reutilizable del modulo.
    const validateEditPrice = () => {
        const fields = [...dialog.querySelectorAll('[data-em-dialog-fields="editar"] input[required], [data-em-dialog-fields="editar"] select[required], [data-em-dialog-fields="editar"] textarea[required]')];
        const startsAt = dialog.querySelector('[data-em-edit-start]');
        const endsAt = dialog.querySelector('[data-em-edit-end]');
        const price = dialog.querySelector('[data-em-edit-price]');
        const invalidPeriod = startsAt.value && endsAt.value && endsAt.value < startsAt.value;
        endsAt.setCustomValidity(invalidPeriod ? 'La fecha final debe ser igual o posterior al inicio.' : '');
        // Condicional: evalua "invalidPeriod" para decidir si ejecuta el bloque siguiente.
        if (invalidPeriod) {
            editError.textContent = 'La vigencia final no puede ser anterior al inicio.';
            editError.hidden = false;
            endsAt.focus();
            return false;
        }
        // Funcion: invalid concentra una tarea reutilizable del modulo.
        const invalid = fields.find((field) => !field.checkValidity());
        // Condicional: evalua "invalid" para decidir si ejecuta el bloque siguiente.
        if (invalid) {
            invalid.setAttribute('aria-invalid', 'true');
            editError.textContent = 'Completa los campos obligatorios y usa un precio mayor que cero.';
            editError.hidden = false;
            invalid.focus();
            return false;
        }
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        fields.forEach((field) => field.removeAttribute('aria-invalid'));
        editError.hidden = true;
        return Boolean(price.value);
    };

    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    pricesPage.querySelectorAll('[data-em-open]').forEach((button) => button.addEventListener('click', () => {
        lastTrigger = button;
        const productName = button.dataset.emProduct;
        const mode = button.dataset.emOpen;
        activeMode = mode;
        pricesPage.querySelector('[data-em-dialog-title]').textContent = productName ? `${titles[mode]} · ${productName}` : titles[mode];
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        dialog.querySelectorAll('[data-em-dialog-fields]').forEach((fields) => {
            fields.hidden = fields.dataset.emDialogFields !== mode;
        });
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        dialog.querySelectorAll('[data-em-dialog-product]').forEach((field) => { field.value = productName ?? ''; });
        saveButton.hidden = mode === 'detalle' || mode === 'masiva';
        bulkPrevious.hidden = mode !== 'masiva';
        bulkNext.hidden = mode !== 'masiva';
        bulkConfirm.hidden = mode !== 'masiva';
        // Condicional: evalua "notice" para decidir si ejecuta el bloque siguiente.
        if (notice) {
            notice.textContent = mode === 'detalle'
                ? 'Detalle consultado desde los datos del catálogo. El precio mayorista no está definido en MariaDB.'
                : mode === 'editar'
                    ? 'El nuevo precio se validará y guardará en MariaDB.'
                : mode === 'masiva'
                    ? 'Importación real: se validarán todas las filas antes de confirmar la transacción.'
                    : 'La configuración y los precios de esta pantalla son ejemplos; no se modifica el catálogo ni el checkout.';
        }
        // Condicional: evalua "mode === 'editar'" para decidir si ejecuta el bloque siguiente.
        if (mode === 'editar') {
            priceForm.action = priceForm.dataset.emEditAction;
            dialog.querySelector('[data-em-edit-channel]').value = 'mayorista';
            dialog.querySelector('[data-em-edit-price]').value = '';
            dialog.querySelector('[data-em-edit-start]').value = '';
            dialog.querySelector('[data-em-edit-end]').value = '';
            dialog.querySelector('[data-em-edit-reason]').value = '';
            dialog.querySelector('[data-em-edit-product-id]').value = button.closest('[data-em-price-row]')?.dataset.emProductId ?? '';
            editError.hidden = true;
        }
        // Condicional: evalua "mode === 'masiva'" para decidir si ejecuta el bloque siguiente.
        if (mode === 'masiva') {
            priceForm.action = priceForm.dataset.emImportAction;
        }
        // Condicional: evalua "mode === 'detalle'" para decidir si ejecuta el bloque siguiente.
        if (mode === 'detalle') {
            const row = button.closest('[data-em-price-row]');
            dialog.querySelector('[data-em-detail-minorista]').value = row.dataset.emMinorista;
            dialog.querySelector('[data-em-detail-mayorista]').value = row.dataset.emMayorista;
            dialog.querySelector('[data-em-detail-base]').value = row.cells[4].textContent;
            dialog.querySelector('[data-em-detail-stock]').value = row.cells[6].textContent;
        }
        openDialog(dialog, button);
    }));
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    pricesPage.querySelectorAll('[data-em-price-edit]').forEach((enlace) => enlace.addEventListener('click', (event) => {
        // Condicional: evalua "channel !== 'mayorista'" para decidir si ejecuta el bloque siguiente.
        if (channel !== 'mayorista') {
            return;
        }
        event.preventDefault();
        lastTrigger = enlace;
        activeMode = 'editar';
        pricesPage.querySelector('[data-em-dialog-title]').textContent = `${titles.editar} · ${enlace.dataset.emProductName}`;
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        dialog.querySelectorAll('[data-em-dialog-fields]').forEach((fields) => {
            fields.hidden = fields.dataset.emDialogFields !== 'editar';
        });
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        dialog.querySelectorAll('[data-em-dialog-product]').forEach((field) => { field.value = enlace.dataset.emProductName ?? ''; });
        dialog.querySelector('[data-em-edit-product-id]').value = enlace.dataset.emProductId ?? '';
        dialog.querySelector('[data-em-edit-channel]').value = 'mayorista';
        dialog.querySelector('[data-em-edit-price]').value = '';
        dialog.querySelector('[data-em-edit-start]').value = '';
        dialog.querySelector('[data-em-edit-end]').value = '';
        dialog.querySelector('[data-em-edit-reason]').value = '';
        saveButton.hidden = false;
        editError.hidden = true;
        notice.textContent = 'El nuevo precio se guardará con su vigencia y motivo en MariaDB; no se alterará el precio minorista.';
        priceForm.action = priceForm.dataset.emEditAction;
        openDialog(dialog, enlace);
    }));
    wireDialogClose(dialog, '[data-em-close]', () => {
        lastTrigger?.setAttribute('aria-expanded', 'false');
        lastTrigger?.focus();
        resetBulkFlow();
        saveButton.disabled = saveButton.dataset.emPermission !== 'true';
        activeMode = 'configuracion';
    });
    // Evento: escucha el evento submit y ejecuta la funcion asociada cuando ocurre.
    priceForm.addEventListener('submit', (event) => {
        // Condicional: evalua "activeMode === 'masiva'" para decidir si ejecuta el bloque siguiente.
        if (activeMode === 'masiva') {
            const file = bulkFile.files?.[0] ?? null;
            // Condicional: evalua "!file || bulkFile.dataset.emInvalid === 'true' || !bulk.querySelector('[data-em-bulk-ack]').checked" para decidir si ejecuta el bloque siguiente.
            if (!file || bulkFile.dataset.emInvalid === 'true' || !bulk.querySelector('[data-em-bulk-ack]').checked) {
                event.preventDefault();
                bulkError.textContent = !file || bulkFile.dataset.emInvalid === 'true'
                    ? 'Selecciona un archivo CSV válido.'
                    : 'Confirma que el CSV pertenece a la empresa autorizada.';
                bulkError.hidden = false;
            }
            return;
        }
        // Condicional: evalua "activeMode !== 'editar' || !validateEditPrice()" para decidir si ejecuta el bloque siguiente.
        if (activeMode !== 'editar' || !validateEditPrice()) {
            event.preventDefault();
        }
    });

    // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
    bulkFile.addEventListener('change', () => {
        const file = bulkFile.files?.[0] ?? null;
        const supported = file && /\.csv$/i.test(file.name);
        bulkFileStatus.textContent = !file
            ? 'Se procesará en el servidor dentro de una transacción.'
            : supported
                ? `Seleccionado: ${file.name}. Se validará y procesará en el servidor.`
                : `Archivo no compatible: ${file.name}. Selecciona un CSV.`;
        bulkFile.dataset.emInvalid = file && !supported ? 'true' : 'false';
    });
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    dialog.querySelector('[data-em-template-download]').addEventListener('click', () => {
        const csv = '\uFEFFsku,canal,precio,moneda,vigencia_desde,vigencia_hasta,motivo\r\n';
        const fileUrl = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8' }));
        const link = document.createElement('a');
        link.href = fileUrl;
        link.download = 'plantilla-precios-minorista-mayorista.csv';
        link.click();
        window.setTimeout(() => URL.revokeObjectURL(fileUrl), 0);
    });
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    bulkPrevious.addEventListener('click', () => renderBulkStep(Math.max(0, activeBulkStep - 1)));
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    bulkNext.addEventListener('click', () => {
        const file = bulkFile.files?.[0] ?? null;
        // Condicional: evalua "activeBulkStep === 1 && (!file || bulkFile.dataset.emInvalid === 'true')" para decidir si ejecuta el bloque siguiente.
        if (activeBulkStep === 1 && (!file || bulkFile.dataset.emInvalid === 'true')) {
            bulkError.textContent = file ? 'El nombre del archivo no corresponde a CSV.' : 'Selecciona un archivo CSV para continuar.';
            bulkError.hidden = false;
            bulkFile.focus();
            return;
        }
        renderBulkStep(Math.min(bulkSteps.length - 1, activeBulkStep + 1));
    });
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    bulkConfirm.addEventListener('click', () => {
        // Condicional: evalua "!bulk.querySelector('[data-em-bulk-ack]').checked" para decidir si ejecuta el bloque siguiente.
        if (!bulk.querySelector('[data-em-bulk-ack]').checked) {
            bulkError.textContent = 'Confirma que el CSV pertenece a la empresa autorizada.';
            bulkError.hidden = false;
            return;
        }
        bulkConfirm.disabled = false;
    });
    resetBulkFlow();
}

const storePage = document.querySelector('[data-ecommerce-tienda]');

// Condicional: evalua "storePage" para decidir si ejecuta el bloque siguiente.
if (storePage) {
    const tabs = [...storePage.querySelectorAll('[data-em-store-tab]')];
    const panels = [...storePage.querySelectorAll('[data-em-store-panel]')];
    const dialog = storePage.querySelector('[data-em-store-dialog]');
    const storeForm = dialog.querySelector('[data-em-store-form]');
    const sectionInput = dialog.querySelector('[data-em-store-section-value]');
    const storeSaveButton = dialog.querySelector('[data-em-store-submit]');
    let lastTrigger;
    let selectedSection = 'general';

    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    tabs.forEach((tab) => tab.addEventListener('click', () => {
        selectedSection = tab.dataset.emStoreTab;
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        tabs.forEach((candidate) => {
            candidate.classList.toggle('is-active', candidate === tab);
            candidate.setAttribute('aria-selected', String(candidate === tab));
        });
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        panels.forEach((panel) => { panel.hidden = panel.dataset.emStorePanel !== selectedSection; });
    }));

    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    storePage.querySelectorAll('[data-em-store-open]').forEach((button) => button.addEventListener('click', () => {
        lastTrigger = button;
        selectedSection = button.dataset.emStoreSection ?? selectedSection;
        // Funcion: label concentra una tarea reutilizable del modulo.
        const label = tabs.find((tab) => tab.dataset.emStoreTab === selectedSection)?.textContent.trim() ?? 'tienda';
        storePage.querySelector('#tc-em-store-dialog-title').textContent = `Editar configuración · ${label}`;
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        dialog.querySelectorAll('[data-em-store-fields]').forEach((fields) => {
            fields.hidden = fields.dataset.emStoreFields !== selectedSection;
        });
        sectionInput.value = selectedSection;
        openDialog(dialog, button);
    }));
    wireDialogClose(dialog, '[data-em-store-close]', () => {
        lastTrigger?.setAttribute('aria-expanded', 'false');
        lastTrigger?.focus();
    });
    // Evento: escucha el evento submit y ejecuta la funcion asociada cuando ocurre.
    storeForm.addEventListener('submit', (event) => {
        // Condicional: evalua "selectedSection === 'integraciones' || storeSaveButton.disabled" para decidir si ejecuta el bloque siguiente.
        if (selectedSection === 'integraciones' || storeSaveButton.disabled) {
            event.preventDefault();
            return;
        }
        sectionInput.value = selectedSection;
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        dialog.querySelectorAll('[data-em-store-fields]').forEach((grupo) => {
            // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
            grupo.querySelectorAll('input, select, textarea').forEach((campo) => {
                campo.disabled = grupo.dataset.emStoreFields !== selectedSection;
            });
        });
    });
}

/**
 * Ejecuta la operación mountPreviewForm utilizada por el módulo JavaScript.
 *
 * @param {*} page - Parámetro recibido por la función.
 *
 * @param {*} cardSelector - Parámetro recibido por la función.
 *
 * @param {*} buttonSelector - Parámetro recibido por la función.
 *
 * @param {*} title - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: mountPreviewForm concentra una tarea reutilizable del modulo.
const mountPreviewForm = (page, cardSelector, buttonSelector, title) => {
    const card = page.querySelector(cardSelector);
    const button = page.querySelector(buttonSelector);
    const hasExistingAction = button?.matches('a[href], [data-org-open], [data-org-link], [data-sec-modal-open], [data-sec-header-action], [data-oc-open-modal], [data-il-open], [data-il-history], [data-ff-open], [data-ff-navigate]') ?? false;

    // Condicional: evalua "!card || !button" para decidir si ejecuta el bloque siguiente.
    if (!card || !button) {
        return;
    }

    const dialog = document.createElement('dialog');
    dialog.className = 'tc-preview-dialog';
    dialog.setAttribute('aria-label', title);
    const heading = document.createElement('div');
    heading.className = 'tc-preview-dialog__header';
    heading.innerHTML = `<strong>${title}</strong><button type="button" class="tc-preview-dialog__close" aria-label="Cerrar formulario"><i class="bi bi-x-lg" aria-hidden="true"></i></button>`;
    dialog.append(heading, card);
    page.append(dialog);
    page.classList.add('has-preview-dialog');
    // Condicional: evalua "!hasExistingAction" para decidir si ejecuta el bloque siguiente.
    if (!hasExistingAction) {
        button.disabled = false;
        button.title = 'Abrir formulario de diseño';
        button.setAttribute('aria-haspopup', 'dialog');
        button.setAttribute('aria-expanded', 'false');
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        button.addEventListener('click', () => openDialog(dialog, button));
    }
    wireDialogClose(dialog, '.tc-preview-dialog__close', () => {
        button.setAttribute('aria-expanded', 'false');
        button.focus();
    });
};

// Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
document.querySelectorAll('[data-ventas-diseno]').forEach((page) => mountPreviewForm(page, '.tc-oc__quick-form', '.tc-oc__header-actions .tc-oc__button--primary', 'Formulario de venta · Vista de diseño'));
// Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
document.querySelectorAll('[data-inventario-diseno]').forEach((page) => mountPreviewForm(page, '.tc-il__quick-form', '.tc-il__header-actions .tc-il__button--primary', 'Formulario de inventario · Vista de diseño'));
// Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
document.querySelectorAll('[data-finanzas-diseno]').forEach((page) => {
    // Condicional: evalua "!page.querySelector('.tc-ff__form-grid')" para decidir si ejecuta el bloque siguiente.
    if (!page.querySelector('.tc-ff__form-grid')) {
        return;
    }

    const isClosingPage = page.querySelector('h1')?.textContent.includes('Apertura y cierre');
    const primaryButtons = [...page.querySelectorAll('.tc-ff__header-actions .tc-ff__button--primary')];
    // Condicional: evalua "isClosingPage && primaryButtons.length > 1" para decidir si ejecuta el bloque siguiente.
    if (isClosingPage && primaryButtons.length > 1) {
        primaryButtons[1].dataset.previewFormTrigger = 'true';
        mountPreviewForm(page, '.tc-ff__aside', '[data-preview-form-trigger]', 'Registro de cierre · Vista de diseño');
    // Caso alternativo: atiende la situacion en la que ninguna condicion anterior resulto verdadera.
    } else {
        mountPreviewForm(page, '.tc-ff__aside', '.tc-ff__header-actions .tc-ff__button--primary', 'Formulario financiero · Vista de diseño');
    }
});

// Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
document.querySelectorAll('[data-organizacion="responsables"]').forEach((page) => mountPreviewForm(page, '.tc-org__assignment', '.tc-org__header-actions .tc-org__button--primary', 'Asignación de responsable · Vista de diseño'));
// Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
document.querySelectorAll('[data-seguridad-diseno]').forEach((page) => {
    // Condicional: evalua "!page.querySelector('.tc-sec__side-form')" para decidir si ejecuta el bloque siguiente.
    if (!page.querySelector('.tc-sec__side-form')) {
        return;
    }

    // Condicional: evalua "page.querySelector('.tc-sec__side-form h2')?.textContent.includes('reactivación')" para decidir si ejecuta el bloque siguiente.
    if (page.querySelector('.tc-sec__side-form h2')?.textContent.includes('reactivación')) {
        const actions = page.querySelector('.tc-sec__header-actions');
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'tc-sec__button tc-sec__button--primary';
        button.innerHTML = '<i class="bi bi-person-check" aria-hidden="true"></i> Reactivar cuenta';
        button.dataset.previewFormTrigger = 'true';
        actions?.append(button);
        mountPreviewForm(page, '.tc-sec__side-form', '[data-preview-form-trigger]', 'Reactivación de cuenta · Vista de diseño');
    // Caso alternativo: atiende la situacion en la que ninguna condicion anterior resulto verdadera.
    } else {
        mountPreviewForm(page, '.tc-sec__side-form', '.tc-sec__header-actions .tc-sec__button--primary', 'Asignación de rol · Vista de diseño');
    }
});

const couponPage = document.querySelector('[data-em-coupon-page]');

// Condicional: evalua "couponPage" para decidir si ejecuta el bloque siguiente.
if (couponPage) {
    // Recorrido: usa map para procesar cada elemento y obtener o actualizar datos.
    const dialogs = Object.fromEntries([...couponPage.querySelectorAll('[data-em-coupon-dialog]')].map((dialog) => [dialog.dataset.emCouponDialog, dialog]));
    let lastTrigger;
/**
 * Ejecuta la operación formatPeriod utilizada por el módulo JavaScript.
 *
 * @param {*} value - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
    // Funcion: formatPeriod concentra una tarea reutilizable del modulo.
    const formatPeriod = (value) => value ? value.replace('T', ' ') : 'Sin fecha';

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    Object.values(dialogs).forEach((dialog) => wireDialogClose(dialog, '[data-em-coupon-close]', () => {
        lastTrigger?.setAttribute('aria-expanded', 'false');
        lastTrigger?.focus();
    }));

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    couponPage.querySelectorAll('[data-em-coupon-action]').forEach((button) => {
        button.setAttribute('aria-haspopup', 'dialog');
        button.setAttribute('aria-expanded', 'false');
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        button.addEventListener('click', () => {
            const row = button.closest('[data-em-coupon-row]');

            // Condicional: evalua "!row" para decidir si ejecuta el bloque siguiente.
            if (!row) {
                return;
            }

            const data = row.dataset;
            const mode = button.dataset.emCouponAction;
            const dialog = dialogs[mode];
            lastTrigger = button;

            // Condicional: evalua "mode === 'detail'" para decidir si ejecuta el bloque siguiente.
            if (mode === 'detail') {
                const discount = data.emCouponType === 'porcentaje' ? `${data.emCouponValue}%` : `S/ ${data.emCouponValue}`;
                const values = {
                    code: data.emCouponCode,
                    discount,
                    minimum: `S/ ${data.emCouponMinimum}`,
                    uses: `${data.emCouponUses} / ${data.emCouponLimit || 'sin límite'}`,
                    period: `${formatPeriod(data.emCouponStart)} — ${formatPeriod(data.emCouponEnd)}`,
                    status: data.emCouponStatus,
                };

                // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
                Object.entries(values).forEach(([key, value]) => {
                    dialog.querySelector(`[data-em-coupon-detail="${key}"]`).textContent = value;
                });
            }

            // Condicional: evalua "mode === 'edit'" para decidir si ejecuta el bloque siguiente.
            if (mode === 'edit') {
                dialog.dataset.emCouponCurrentUses = data.emCouponUses;
                dialog.querySelector('[data-em-coupon-edit-form]').action = data.emCouponUpdateUrl;
                dialog.querySelector('[data-em-coupon-edit-code]').value = data.emCouponCode;
                dialog.querySelector('[data-em-coupon-edit-type]').value = data.emCouponType;
                dialog.querySelector('[data-em-coupon-edit-value]').value = data.emCouponValue;
                dialog.querySelector('[data-em-coupon-edit-minimum]').value = data.emCouponMinimum;
                dialog.querySelector('[data-em-coupon-edit-limit]').value = data.emCouponLimit;
                dialog.querySelector('[data-em-coupon-edit-start]').value = data.emCouponStart;
                dialog.querySelector('[data-em-coupon-edit-end]').value = data.emCouponEnd;
                dialog.querySelector('[data-em-coupon-edit-error]').hidden = true;
                dialog.querySelector('[data-em-coupon-edit-feedback]').hidden = true;
            }

            // Condicional: evalua "mode === 'deactivate'" para decidir si ejecuta el bloque siguiente.
            if (mode === 'deactivate') {
                dialog.querySelector('[data-em-coupon-deactivate-form]').action = data.emCouponDeactivateUrl;
                dialog.querySelector('[data-em-coupon-deactivate-code]').textContent = data.emCouponCode;
                dialog.querySelector('[data-em-coupon-deactivate-ack]').checked = false;
                dialog.querySelector('[data-em-coupon-deactivate-feedback]').hidden = true;
            }

            openDialog(dialog, button);
        });
    });

    const editDialog = dialogs.edit;
    const editError = editDialog.querySelector('[data-em-coupon-edit-error]');
    const editFeedback = editDialog.querySelector('[data-em-coupon-edit-feedback]');
    const editFields = [...editDialog.querySelectorAll('[data-em-coupon-edit-type], [data-em-coupon-edit-value], [data-em-coupon-edit-minimum], [data-em-coupon-edit-limit], [data-em-coupon-edit-start], [data-em-coupon-edit-end]')];

    // Evento: escucha el evento input y ejecuta la funcion asociada cuando ocurre.
    editFields.forEach((field) => field.addEventListener('input', () => {
        editError.hidden = true;
        editFeedback.hidden = true;
    }));

    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    editDialog.querySelector('[data-em-coupon-edit-confirm]').addEventListener('click', (event) => {
        const type = editDialog.querySelector('[data-em-coupon-edit-type]');
        const value = editDialog.querySelector('[data-em-coupon-edit-value]');
        const startsAt = editDialog.querySelector('[data-em-coupon-edit-start]');
        const endsAt = editDialog.querySelector('[data-em-coupon-edit-end]');
        const limit = editDialog.querySelector('[data-em-coupon-edit-limit]');
        const invalidPeriod = startsAt.value && endsAt.value && endsAt.value <= startsAt.value;
        const invalidLimit = limit.value && Number(limit.value) < Number(editDialog.dataset.emCouponCurrentUses);
        endsAt.setCustomValidity(invalidPeriod ? 'La fecha final debe ser posterior al inicio.' : '');
        // Funcion: invalid concentra una tarea reutilizable del modulo.
        const invalid = editFields.find((field) => !field.checkValidity());

        // Condicional: evalua "invalid || invalidLimit || (type.value === 'porcentaje' && Number(value.value) > 100)" para decidir si ejecuta el bloque siguiente.
        if (invalid || invalidLimit || (type.value === 'porcentaje' && Number(value.value) > 100)) {
            event.preventDefault();
            // Condicional: evalua "invalidPeriod" para decidir si ejecuta el bloque siguiente.
            if (invalidPeriod) {
                editError.textContent = 'La fecha final debe ser posterior a la fecha inicial.';
            // Condicional alternativo: este bloque se ejecuta cuando la comprobacion anterior no se cumple.
            } else if (invalidLimit) {
                editError.textContent = 'El límite de usos no puede ser inferior a los canjes ya registrados.';
            // Condicional alternativo: este bloque se ejecuta cuando la comprobacion anterior no se cumple.
            } else if (type.value === 'porcentaje' && Number(value.value) > 100) {
                editError.textContent = 'El descuento porcentual no puede superar 100 %.';
            // Caso alternativo: atiende la situacion en la que ninguna condicion anterior resulto verdadera.
            } else {
                editError.textContent = 'Revisa los campos obligatorios, el valor positivo y los límites indicados.';
            }
            editError.hidden = false;
            (invalid ?? (invalidLimit ? limit : value)).focus();
            return;
        }
        editError.hidden = true;
    });

    const deactivateDialog = dialogs.deactivate;
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    deactivateDialog.querySelector('[data-em-coupon-deactivate-confirm]').addEventListener('click', (event) => {
        const feedback = deactivateDialog.querySelector('[data-em-coupon-deactivate-feedback]');
        const acknowledged = deactivateDialog.querySelector('[data-em-coupon-deactivate-ack]').checked;
        // Condicional: evalua "!acknowledged" para decidir si ejecuta el bloque siguiente.
        if (!acknowledged) {
            event.preventDefault();
            feedback.textContent = 'Confirma cancelar el cupón para continuar.';
            feedback.hidden = false;

            return;
        }

        feedback.hidden = true;
    });
}

const segmentPage = document.querySelector('[data-em-segment-page]');

// Condicional: evalua "segmentPage" para decidir si ejecuta el bloque siguiente.
if (segmentPage) {
    // Recorrido: usa map para procesar cada elemento y obtener o actualizar datos.
    const dialogs = Object.fromEntries([...segmentPage.querySelectorAll('[data-em-segment-dialog]')].map((dialog) => [dialog.dataset.emSegmentDialog, dialog]));
    let lastTrigger;
    const editDialog = dialogs.edit;
    const roleCheckboxes = [...editDialog.querySelectorAll('[data-em-segment-role]')];
    const editError = editDialog.querySelector('[data-em-segment-edit-error]');
    const editFeedback = editDialog.querySelector('[data-em-segment-edit-feedback]');
    const nameField = editDialog.querySelector('[data-em-segment-edit-name]');
    const editForm = editDialog.querySelector('[data-em-segment-edit-form]');
    const removeForm = dialogs.remove.querySelector('[data-em-segment-remove-form]');
    const segmentBaseUrl = segmentPage.dataset.emSegmentBaseUrl;
/**
 * Ejecuta la operación roleLabel utilizada por el módulo JavaScript.
 *
 * @param {*} role - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
    // Funcion: roleLabel concentra una tarea reutilizable del modulo.
    const roleLabel = (role) => role.replace(/_/g, ' ');

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    Object.values(dialogs).forEach((dialog) => wireDialogClose(dialog, '[data-em-segment-close]', () => {
        lastTrigger?.setAttribute('aria-expanded', 'false');
        lastTrigger?.focus();
    }));

/**
 * Actualiza la información visible o el estado del componente.
 *
 * @returns {*} Resultado de la operación.
 */
    // Funcion: updateSegmentPreview concentra una tarea reutilizable del modulo.
    const updateSegmentPreview = () => {
        // Recorrido: usa filter para procesar cada elemento y obtener o actualizar datos.
        const roles = roleCheckboxes.filter((checkbox) => checkbox.checked).map((checkbox) => roleLabel(checkbox.value));
        editDialog.querySelector('[data-em-segment-preview]').textContent = roles.length
            ? `Regla ilustrativa por rol: ${roles.join(', ')}. No se consultarán clientes.`
            : 'Selecciona al menos un rol para describir la regla. No se buscarán clientes.';
        editError.hidden = true;
        editFeedback.hidden = true;
    };

    // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
    roleCheckboxes.forEach((checkbox) => checkbox.addEventListener('change', updateSegmentPreview));
    // Evento: escucha el evento input y ejecuta la funcion asociada cuando ocurre.
    nameField.addEventListener('input', () => {
        editError.hidden = true;
        editFeedback.hidden = true;
    });

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    segmentPage.querySelectorAll('[data-em-segment-action]').forEach((button) => {
        button.setAttribute('aria-haspopup', 'dialog');
        button.setAttribute('aria-expanded', 'false');
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        button.addEventListener('click', () => {
            const row = button.closest('[data-em-segment-row]');

            // Condicional: evalua "!row" para decidir si ejecuta el bloque siguiente.
            if (!row) {
                return;
            }

            const data = row.dataset;
            const mode = button.dataset.emSegmentAction;
            const dialog = dialogs[mode];
            lastTrigger = button;

            // Condicional: evalua "mode === 'detail'" para decidir si ejecuta el bloque siguiente.
            if (mode === 'detail') {
                // Recorrido: usa filter para procesar cada elemento y obtener o actualizar datos.
                const roles = data.emSegmentRoles.split('|').filter(Boolean).map(roleLabel);
                const values = {
                    name: data.emSegmentName,
                    type: data.emSegmentType,
                    roles: roles.length ? roles.join(', ') : 'Sin roles definidos',
                    active: data.emSegmentActive,
                };

                // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
                Object.entries(values).forEach(([key, value]) => {
                    dialog.querySelector(`[data-em-segment-detail="${key}"]`).textContent = value;
                });
            }

            // Condicional: evalua "mode === 'edit'" para decidir si ejecuta el bloque siguiente.
            if (mode === 'edit') {
                editForm.action = segmentBaseUrl + '/' + data.emSegmentId;
                nameField.value = data.emSegmentName;
                // Recorrido: usa filter para procesar cada elemento y obtener o actualizar datos.
                const selectedRoles = new Set(data.emSegmentRoles.split('|').filter(Boolean));
                // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
                roleCheckboxes.forEach((checkbox) => { checkbox.checked = selectedRoles.has(checkbox.value); });
                editError.hidden = true;
                editFeedback.hidden = true;
                updateSegmentPreview();
            }

            // Condicional: evalua "mode === 'remove'" para decidir si ejecuta el bloque siguiente.
            if (mode === 'remove') {
                removeForm.action = segmentBaseUrl + '/' + data.emSegmentId;
                dialog.querySelector('[data-em-segment-remove-name]').textContent = data.emSegmentName;
                dialog.querySelector('[data-em-segment-remove-ack]').checked = false;
                dialog.querySelector('[data-em-segment-remove-feedback]').hidden = true;
            }

            openDialog(dialog, button);
        });
    });

    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    editDialog.querySelector('[data-em-segment-edit-confirm]').addEventListener('click', () => {
        // Recorrido: usa filter para procesar cada elemento y obtener o actualizar datos.
        const selectedRoles = roleCheckboxes.filter((checkbox) => checkbox.checked);
        const invalidName = !nameField.value.trim() || !nameField.checkValidity();

        // Condicional: evalua "invalidName || selectedRoles.length === 0" para decidir si ejecuta el bloque siguiente.
        if (invalidName || selectedRoles.length === 0) {
            editError.textContent = invalidName
                ? 'Escribe un nombre de hasta 120 caracteres.'
                : 'Selecciona al menos un rol para definir la regla.';
            editError.hidden = false;
            (invalidName ? nameField : roleCheckboxes[0])?.focus();
            return;
        }
        editError.hidden = true;
        editForm.submit();
    });

    const removeDialog = dialogs.remove;
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    removeDialog.querySelector('[data-em-segment-remove-confirm]').addEventListener('click', () => {
        const feedback = removeDialog.querySelector('[data-em-segment-remove-feedback]');
        const acknowledged = removeDialog.querySelector('[data-em-segment-remove-ack]').checked;
        // Condicional: evalua "acknowledged" para decidir si ejecuta el bloque siguiente.
        if (acknowledged) {
            removeForm.submit();
            return;
        }
        feedback.textContent = 'Confirma la casilla para desactivar el segmento.';
        feedback.hidden = false;
    });
}