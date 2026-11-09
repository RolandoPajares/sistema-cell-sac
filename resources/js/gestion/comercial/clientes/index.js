(() => {
            const page = document.querySelector('[data-client-page]');
            const createDialog = document.querySelector('#nuevo-cliente');
            const importDialog = document.querySelector('[data-client-import-dialog]');

            const configureClientForms = () => {
                document.querySelectorAll('[data-client-form]').forEach((form) => {
                    const options = [...form.querySelectorAll('[data-client-person]')];
                    const legalFields = [...form.querySelectorAll('[data-legal-only]')];
                    const naturalLabels = [...form.querySelectorAll('[data-natural-label]')];
                    const legalLabels = [...form.querySelectorAll('[data-legal-label]')];
                    const documentType = form.querySelector('[data-client-document-type]');
                    const contactInput = form.querySelector('[data-contact-name] input');

                    const updateForm = (personType) => {
                        form.dataset.personType = personType;
                        options.forEach((option) => {
                            const isActive = option.dataset.clientPerson === personType;
                            option.classList.toggle('is-active', isActive);
                            option.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                        });
                        legalFields.forEach((field) => { field.hidden = personType !== 'juridica'; });
                        naturalLabels.forEach((label) => { label.hidden = personType !== 'natural'; });
                        legalLabels.forEach((label) => { label.hidden = personType !== 'juridica'; });
                        if (contactInput) {
                            contactInput.placeholder = personType === 'juridica'
                                ? 'Nombre de la persona de contacto'
                                : 'Ingresa nombres y apellidos';
                        }
                        if (documentType) {
                            documentType.value = personType === 'juridica' ? 'ruc' : 'dni';
                        }
                    };

                    options.forEach((option) => option.addEventListener('click', () => updateForm(option.dataset.clientPerson)));
                    updateForm(form.dataset.personType || 'natural');
                });
            };

            if (!page || !createDialog) {
                return;
            }

            page.querySelectorAll('[data-open-client-create]').forEach((button) => button.addEventListener('click', () => createDialog.showModal()));
            const importStages = [...(importDialog?.querySelectorAll('[data-client-import-stage]') ?? [])];
            const importIndicators = [...(importDialog?.querySelectorAll('[data-client-import-indicator]') ?? [])];
            const importFile = importDialog?.querySelector('[data-client-import-file]');
            const importError = importDialog?.querySelector('[data-client-import-error]');
            const importPrevious = importDialog?.querySelector('[data-client-import-previous]');
            const importNext = importDialog?.querySelector('[data-client-import-next]');
            const importConfirm = importDialog?.querySelector('[data-client-import-confirm]');
            const importFileName = importDialog?.querySelector('[data-client-import-file-name]');
            const importFinalFile = importDialog?.querySelector('[data-client-import-final-file]');
            const importAcknowledgement = importDialog?.querySelector('[data-client-import-ack]');
            const importFeedback = importDialog?.querySelector('[data-client-import-feedback]');
            const importForm = importDialog?.querySelector('[data-client-import-form]');
            const importPreview = importDialog?.querySelector('[data-client-import-preview]');
            const importValidations = importDialog?.querySelector('[data-client-import-validations]');
            let importStep = 0;
            let selectedImportFileName = '';
            let importFileReady = false;
            const parseCsv = (text) => {
                const rows = [];
                let row = [], cell = '', quoted = false;
                for (let index = 0; index < text.length; index += 1) {
                    const character = text[index];
                    if (character === '"' && text[index + 1] === '"' && quoted) { cell += '"'; index += 1; continue; }
                    if (character === '"') { quoted = !quoted; continue; }
                    if (character === ',' && !quoted) { row.push(cell.trim()); cell = ''; continue; }
                    if ((character === '\n' || character === '\r') && !quoted) {
                        if (character === '\r' && text[index + 1] === '\n') index += 1;
                        row.push(cell.trim()); cell = '';
                        if (row.some((value) => value !== '')) rows.push(row);
                        row = []; continue;
                    }
                    cell += character;
                }
                if (cell !== '' || row.length) { row.push(cell.trim()); rows.push(row); }
                return rows;
            };
            const renderImportPreview = (rows) => {
                if (!importPreview) return;
                importPreview.innerHTML = rows.slice(1, 11).map((row) => `<tr>${Array.from({ length: 7 }, (_, index) => `<td>${String(row[index] ?? '').replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;')}</td>`).join('')}</tr>`).join('') || '<tr><td colspan="7">El CSV no contiene filas de datos.</td></tr>';
            };
            const renderImportValidations = (rows) => {
                if (!importValidations) return;
                const expected = ['tipo', 'documento', 'nombre_contacto', 'razon_social', 'correo', 'telefono', 'ciudad'];
                const headers = (rows[0] ?? []).map((value) => String(value).replace(/^﻿/, '').toLowerCase());
                const valid = JSON.stringify(headers) === JSON.stringify(expected) && rows.length > 1;
                importFileReady = valid;
                importValidations.innerHTML = valid
                    ? '<li class="is-valid"><i class="bi bi-check-circle" aria-hidden="true"></i> Cabecera correcta y hay filas para enviar al servidor.</li><li><i class="bi bi-info-circle" aria-hidden="true"></i> El servidor volverá a validar tipos, correos y documentos duplicados.</li>'
                    : '<li class="is-warning"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i> La cabecera o el contenido no coincide con la plantilla CSV.</li>';
            };
            const renderImportStep = (step) => {
                importStep = step;
                importStages.forEach((stage) => { stage.hidden = Number(stage.dataset.clientImportStage) !== step; });
                importIndicators.forEach((indicator) => indicator.classList.toggle('is-current', Number(indicator.dataset.clientImportIndicator) === step));
                importPrevious.hidden = step === 0;
                importNext.hidden = step === 4;
                importConfirm.hidden = step !== 4;
                importError.hidden = true;
                importFeedback.hidden = true;
                importFinalFile.textContent = selectedImportFileName || '—';
            };
            const resetImportWizard = () => {
                selectedImportFileName = '';
                importFile.value = '';
                importFileName.textContent = 'No se ha seleccionado un archivo.';
                importAcknowledgement.checked = false;
                importConfirm.disabled = false;
                importFileReady = false;
                if (importPreview) importPreview.innerHTML = '<tr><td colspan="7">Selecciona un CSV para consultar su contenido.</td></tr>';
                renderImportStep(0);
            };
            page.querySelector('[data-open-client-import]')?.addEventListener('click', () => {
                resetImportWizard();
                importDialog.showModal();
            });
            importDialog?.querySelector('[data-client-import-download]')?.addEventListener('click', () => {
                const csv = '\uFEFFtipo,documento,nombre_contacto,razon_social,correo,telefono,ciudad\r\n';
                const url = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8' }));
                const link = document.createElement('a');
                link.href = url;
                link.download = 'plantilla-clientes.csv';
                link.click();
                window.setTimeout(() => URL.revokeObjectURL(url), 1000);
            });
            importFile?.addEventListener('change', () => {
                const file = importFile.files?.[0] ?? null;
                if (file && !/\.(csv|txt)$/i.test(file.name)) {
                    selectedImportFileName = '';
                    importFile.value = '';
                    importFileName.textContent = 'No se ha seleccionado un archivo.';
                    importError.textContent = 'Selecciona un archivo CSV UTF-8.';
                    importError.hidden = false;
                    return;
                }
                selectedImportFileName = file?.name ?? '';
                importFileName.textContent = file ? `Seleccionado: ${file.name}` : 'No se ha seleccionado un archivo.';
                importError.hidden = true;
                importFileReady = false;
                if (file) {
                    file.text().then((text) => {
                        const rows = parseCsv(text);
                        renderImportPreview(rows);
                        renderImportValidations(rows);
                    }).catch(() => {
                        importError.textContent = 'No se pudo leer el CSV seleccionado.';
                        importError.hidden = false;
                    });
                }
            });
            importPrevious?.addEventListener('click', () => renderImportStep(Math.max(0, importStep - 1)));
            importNext?.addEventListener('click', () => {
                if (importStep === 1 && (!selectedImportFileName || !importFileReady)) {
                    importError.textContent = 'Selecciona un CSV válido para continuar.';
                    importError.hidden = false;
                    return;
                }
                renderImportStep(Math.min(4, importStep + 1));
            });
            importConfirm?.addEventListener('click', (event) => {
                if (!importAcknowledgement.checked) {
                    importError.textContent = 'Confirma que revisaste la vista previa y sus advertencias.';
                    importError.hidden = false;
                    event.preventDefault();
                    return;
                }
                importError.hidden = true;
            });
            document.querySelectorAll('[data-open-client-edit]').forEach((button) => button.addEventListener('click', () => document.getElementById(button.dataset.openClientEdit)?.showModal()));
            document.querySelectorAll('[data-open-client-view]').forEach((button) => button.addEventListener('click', () => document.getElementById(button.dataset.openClientView)?.showModal()));
            document.querySelectorAll('.clientes-dialog').forEach((dialog) => {
                dialog.querySelectorAll('[data-close-client-dialog]').forEach((button) => button.addEventListener('click', () => dialog.close()));
                dialog.addEventListener('click', (event) => {
                    if (event.target === dialog) {
                        dialog.close();
                    }
                });
            });
            page.querySelectorAll('[data-refresh-clients]').forEach((button) => button.addEventListener('click', () => window.location.reload()));

            if (page.dataset.openOnLoad === 'true') { createDialog.showModal(); }
            configureClientForms();
        })();
