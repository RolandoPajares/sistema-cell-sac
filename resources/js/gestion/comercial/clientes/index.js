/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/gestion/comercial/clientes/index.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

(() => {
    const page = document.querySelector("[data-client-page]");
    const createDialog = document.querySelector("#nuevo-cliente");
    const importDialog = document.querySelector("[data-client-import-dialog]");

    // Funcion: configureClientForms concentra una tarea reutilizable del modulo.
    const configureClientForms = () => {
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        document.querySelectorAll("[data-client-form]").forEach((form) => {
            const options = [...form.querySelectorAll("[data-client-person]")];
            const legalFields = [...form.querySelectorAll("[data-legal-only]")];
            const naturalFields = [...form.querySelectorAll("[data-client-natural-fields]")];
            const juridicaFields = [...form.querySelectorAll("[data-client-juridica-fields]")];
            const naturalLabels = [
                ...form.querySelectorAll("[data-natural-label]"),
            ];
            const legalLabels = [
                ...form.querySelectorAll("[data-legal-label]"),
            ];
            const documentType = form.querySelector(
                "[data-client-document-type]",
            );
            const contactInput = form.querySelector(
                "[data-contact-name] input",
            );
            const hiddenContactInput = form.querySelector("[data-client-contact-input]");
            const firstName = form.querySelector("[data-client-first-name]");
            const lastName = form.querySelector("[data-client-last-name]");
            const legalName = form.querySelector("[data-client-legal-name]");
            const companyDocument = form.querySelector("[data-client-company-document]");
            const documentInput = form.querySelector('input[name="documento"]');
            const hiddenDocumentType = form.querySelector("[data-client-document-hidden]");
            const companyDocumentType = form.querySelector("[data-client-document-type-company]");

            // Funcion: syncContactName concentra una tarea reutilizable del modulo.
            const syncContactName = () => {
                // Condicional: evalua "!hiddenContactInput" para decidir si ejecuta el bloque siguiente.
                if (!hiddenContactInput) return;
                hiddenContactInput.value = personType === "juridica"
                    ? (legalName?.value || "")
                    : `${firstName?.value || ""} ${lastName?.value || ""}`.trim();
            };

            // Funcion: syncDocument concentra una tarea reutilizable del modulo.
            const syncDocument = () => {
                // Condicional: evalua "!documentInput || !companyDocument" para decidir si ejecuta el bloque siguiente.
                if (!documentInput || !companyDocument) return;
                // Condicional: evalua "form.dataset.personType === "juridica"" para decidir si ejecuta el bloque siguiente.
                if (form.dataset.personType === "juridica") documentInput.value = companyDocument.value;
                // Caso alternativo: atiende la situacion en la que ninguna condicion anterior resulto verdadera.
                else companyDocument.value = documentInput.value;
            };

            // Funcion: updateForm concentra una tarea reutilizable del modulo.
            const updateForm = (personType) => {
                form.dataset.personType = personType;
                // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
                options.forEach((option) => {
                    const isActive = option.dataset.clientPerson === personType;
                    option.classList.toggle("is-active", isActive);
                    option.setAttribute(
                        "aria-pressed",
                        isActive ? "true" : "false",
                    );
                });
                // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
                legalFields.forEach((field) => {
                    field.hidden = personType !== "juridica";
                });
                // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
                naturalFields.forEach((field) => { field.hidden = personType !== "natural"; });
                // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
                juridicaFields.forEach((field) => { field.hidden = personType !== "juridica"; });
                // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
                naturalLabels.forEach((label) => {
                    label.hidden = personType !== "natural";
                });
                // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
                legalLabels.forEach((label) => {
                    label.hidden = personType !== "juridica";
                });
                // Condicional: evalua "contactInput" para decidir si ejecuta el bloque siguiente.
                if (contactInput) {
                    contactInput.placeholder =
                        personType === "juridica"
                            ? "Nombre de la persona de contacto"
                            : "Ingresa nombres y apellidos";
                }
                // Condicional: evalua "documentType" para decidir si ejecuta el bloque siguiente.
                if (documentType) {
                    documentType.value =
                        personType === "juridica" ? "ruc" : "dni";
                }
                // Condicional: evalua "companyDocumentType" para decidir si ejecuta el bloque siguiente.
                if (companyDocumentType) companyDocumentType.value = "ruc";
                // Condicional: evalua "companyDocument && documentInput && personType === "juridica" && !companyDocument.value" para decidir si ejecuta el bloque siguiente.
                if (companyDocument && documentInput && personType === "juridica" && !companyDocument.value) companyDocument.value = documentInput.value;
                // Condicional: evalua "hiddenDocumentType" para decidir si ejecuta el bloque siguiente.
                if (hiddenDocumentType) hiddenDocumentType.value = personType === "juridica" ? "ruc" : "dni";
                // Evento: escucha el evento input y ejecuta la funcion asociada cuando ocurre.
                [firstName, lastName, legalName].filter(Boolean).forEach((field) => field.addEventListener("input", syncContactName));
                syncContactName();
                syncDocument();
            };

            // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
            options.forEach((option) =>
                // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
                option.addEventListener("click", () =>
                    updateForm(option.dataset.clientPerson),
                ),
            );
            // Evento: escucha el evento input y ejecuta la funcion asociada cuando ocurre.
            companyDocument?.addEventListener("input", syncDocument);
            updateForm(form.dataset.personType || "natural");
        });
    };

    // Condicional: evalua "!page || !createDialog" para decidir si ejecuta el bloque siguiente.
    if (!page || !createDialog) {
        return;
    }

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    page.querySelectorAll("[data-open-client-create]").forEach((button) =>
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        button.addEventListener("click", () => createDialog.showModal()),
    );
    const importStages = [
        ...(importDialog?.querySelectorAll("[data-client-import-stage]") ?? []),
    ];
    const importIndicators = [
        ...(importDialog?.querySelectorAll("[data-client-import-indicator]") ??
            []),
    ];
    const importFile = importDialog?.querySelector("[data-client-import-file]");
    const importError = importDialog?.querySelector(
        "[data-client-import-error]",
    );
    const importPrevious = importDialog?.querySelector(
        "[data-client-import-previous]",
    );
    const importNext = importDialog?.querySelector("[data-client-import-next]");
    const importConfirm = importDialog?.querySelector(
        "[data-client-import-confirm]",
    );
    const importFileName = importDialog?.querySelector(
        "[data-client-import-file-name]",
    );
    const importFinalFile = importDialog?.querySelector(
        "[data-client-import-final-file]",
    );
    const importAcknowledgement = importDialog?.querySelector(
        "[data-client-import-ack]",
    );
    const importFeedback = importDialog?.querySelector(
        "[data-client-import-feedback]",
    );
    const importForm = importDialog?.querySelector("[data-client-import-form]");
    const importPreview = importDialog?.querySelector(
        "[data-client-import-preview]",
    );
    const importValidations = importDialog?.querySelector(
        "[data-client-import-validations]",
    );
    let importStep = 0;
    let selectedImportFileName = "";
    let importFileReady = false;
    // Funcion: parseCsv concentra una tarea reutilizable del modulo.
    const parseCsv = (text) => {
        const rows = [];
        let row = [],
            cell = "",
            quoted = false;
        for (let index = 0; index < text.length; index += 1) {
            const character = text[index];
            // Condicional: evalua "character === '"' && text[index + 1] === '"' && quoted" para decidir si ejecuta el bloque siguiente.
            if (character === '"' && text[index + 1] === '"' && quoted) {
                cell += '"';
                index += 1;
                continue;
            }
            // Condicional: evalua "character === '"'" para decidir si ejecuta el bloque siguiente.
            if (character === '"') {
                quoted = !quoted;
                continue;
            }
            // Condicional: evalua "character === "," && !quoted" para decidir si ejecuta el bloque siguiente.
            if (character === "," && !quoted) {
                row.push(cell.trim());
                cell = "";
                continue;
            }
            // Condicional: evalua "(character === "\n" || character === "\r") && !quoted" para decidir si ejecuta el bloque siguiente.
            if ((character === "\n" || character === "\r") && !quoted) {
                // Condicional: evalua "character === "\r" && text[index + 1] === "\n"" para decidir si ejecuta el bloque siguiente.
                if (character === "\r" && text[index + 1] === "\n") index += 1;
                row.push(cell.trim());
                cell = "";
                // Condicional: evalua "row.some((value) => value !== "")) rows.push(row" para decidir si ejecuta el bloque siguiente.
                if (row.some((value) => value !== "")) rows.push(row);
                row = [];
                continue;
            }
            cell += character;
        }
        // Condicional: evalua "cell !== "" || row.length" para decidir si ejecuta el bloque siguiente.
        if (cell !== "" || row.length) {
            row.push(cell.trim());
            rows.push(row);
        }
        return rows;
    };
    // Funcion: renderImportPreview concentra una tarea reutilizable del modulo.
    const renderImportPreview = (rows) => {
        // Condicional: evalua "!importPreview" para decidir si ejecuta el bloque siguiente.
        if (!importPreview) return;
        importPreview.innerHTML =
            rows
                .slice(1, 11)
                // Recorrido: usa map para procesar cada elemento y obtener o actualizar datos.
                .map(
                    (row) =>
                        `<tr>${Array.from(
                            { length: 7 },
                            (_, index) =>
                                `<td>${String(row[index] ?? "")
                                    .replaceAll("&", "&amp;")
                                    .replaceAll("<", "&lt;")
                                    .replaceAll(">", "&gt;")}</td>`,
                        ).join("")}</tr>`,
                )
                .join("") ||
            '<tr><td colspan="7">El CSV no contiene filas de datos.</td></tr>';
    };
    // Funcion: renderImportValidations concentra una tarea reutilizable del modulo.
    const renderImportValidations = (rows) => {
        // Condicional: evalua "!importValidations" para decidir si ejecuta el bloque siguiente.
        if (!importValidations) return;
        const expected = [
            "tipo",
            "documento",
            "nombre_contacto",
            "razon_social",
            "correo",
            "telefono",
            "ciudad",
        ];
        // Recorrido: usa map para procesar cada elemento y obtener o actualizar datos.
        const headers = (rows[0] ?? []).map((value) =>
            String(value).replace(/^﻿/, "").toLowerCase(),
        );
        const valid =
            JSON.stringify(headers) === JSON.stringify(expected) &&
            rows.length > 1;
        importFileReady = valid;
        importValidations.innerHTML = valid
            ? '<li class="is-valid"><i class="bi bi-check-circle" aria-hidden="true"></i> Cabecera correcta y hay filas para enviar al servidor.</li><li><i class="bi bi-info-circle" aria-hidden="true"></i> El servidor volverá a validar tipos, correos y documentos duplicados.</li>'
            : '<li class="is-warning"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i> La cabecera o el contenido no coincide con la plantilla CSV.</li>';
    };
    // Funcion: renderImportStep concentra una tarea reutilizable del modulo.
    const renderImportStep = (step) => {
        importStep = step;
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        importStages.forEach((stage) => {
            stage.hidden = Number(stage.dataset.clientImportStage) !== step;
        });
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        importIndicators.forEach((indicator) =>
            indicator.classList.toggle(
                "is-current",
                Number(indicator.dataset.clientImportIndicator) === step,
            ),
        );
        importPrevious.hidden = step === 0;
        importNext.hidden = step === 4;
        importConfirm.hidden = step !== 4;
        importError.hidden = true;
        importFeedback.hidden = true;
        importFinalFile.textContent = selectedImportFileName || "—";
    };
    // Funcion: resetImportWizard concentra una tarea reutilizable del modulo.
    const resetImportWizard = () => {
        selectedImportFileName = "";
        importFile.value = "";
        importFileName.textContent = "No se ha seleccionado un archivo.";
        importAcknowledgement.checked = false;
        importConfirm.disabled = false;
        importFileReady = false;
        // Condicional: evalua "importPreview" para decidir si ejecuta el bloque siguiente.
        if (importPreview)
            importPreview.innerHTML =
                '<tr><td colspan="7">Selecciona un CSV para consultar su contenido.</td></tr>';
        renderImportStep(0);
    };
    // Evento: escucha una interaccion del usuario o del navegador y ejecuta la funcion asociada cuando ocurre.
    page.querySelector("[data-open-client-import]")?.addEventListener(
        "click",
        () => {
            resetImportWizard();
            importDialog.showModal();
        },
    );
    importDialog
        ?.querySelector("[data-client-import-download]")
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        ?.addEventListener("click", () => {
            const csv =
                "\uFEFFtipo,documento,nombre_contacto,razon_social,correo,telefono,ciudad\r\n";
            const url = URL.createObjectURL(
                new Blob([csv], { type: "text/csv;charset=utf-8" }),
            );
            const link = document.createElement("a");
            link.href = url;
            link.download = "plantilla-clientes.csv";
            link.click();
            window.setTimeout(() => URL.revokeObjectURL(url), 1000);
        });
    // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
    importFile?.addEventListener("change", () => {
        const file = importFile.files?.[0] ?? null;
        // Condicional: evalua "file && !/\.(csv|txt)$/i.test(file.name)" para decidir si ejecuta el bloque siguiente.
        if (file && !/\.(csv|txt)$/i.test(file.name)) {
            selectedImportFileName = "";
            importFile.value = "";
            importFileName.textContent = "No se ha seleccionado un archivo.";
            importError.textContent = "Selecciona un archivo CSV UTF-8.";
            importError.hidden = false;
            return;
        }
        selectedImportFileName = file?.name ?? "";
        importFileName.textContent = file
            ? `Seleccionado: ${file.name}`
            : "No se ha seleccionado un archivo.";
        importError.hidden = true;
        importFileReady = false;
        // Condicional: evalua "file" para decidir si ejecuta el bloque siguiente.
        if (file) {
            file.text()
                .then((text) => {
                    const rows = parseCsv(text);
                    renderImportPreview(rows);
                    renderImportValidations(rows);
                })
                .catch(() => {
                    importError.textContent =
                        "No se pudo leer el CSV seleccionado.";
                    importError.hidden = false;
                });
        }
    });
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    importPrevious?.addEventListener("click", () =>
        renderImportStep(Math.max(0, importStep - 1)),
    );
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    importNext?.addEventListener("click", () => {
        // Condicional: evalua "importStep === 1 && (!selectedImportFileName || !importFileReady)" para decidir si ejecuta el bloque siguiente.
        if (importStep === 1 && (!selectedImportFileName || !importFileReady)) {
            importError.textContent =
                "Selecciona un CSV válido para continuar.";
            importError.hidden = false;
            return;
        }
        renderImportStep(Math.min(4, importStep + 1));
    });
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    importConfirm?.addEventListener("click", (event) => {
        // Condicional: evalua "!importAcknowledgement.checked" para decidir si ejecuta el bloque siguiente.
        if (!importAcknowledgement.checked) {
            importError.textContent =
                "Confirma que revisaste la vista previa y sus advertencias.";
            importError.hidden = false;
            event.preventDefault();
            return;
        }
        importError.hidden = true;
    });
    document
        .querySelectorAll("[data-open-client-edit]")
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        .forEach((button) =>
            // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
            button.addEventListener("click", () =>
                document
                    .getElementById(button.dataset.openClientEdit)
                    ?.showModal(),
            ),
        );
    document
        .querySelectorAll("[data-open-client-view]")
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        .forEach((button) =>
            // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
            button.addEventListener("click", () =>
                document
                    .getElementById(button.dataset.openClientView)
                    ?.showModal(),
            ),
        );
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    document.querySelectorAll(".clientes-dialog").forEach((dialog) => {
        dialog
            .querySelectorAll("[data-close-client-dialog]")
            // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
            .forEach((button) =>
                // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
                button.addEventListener("click", () => dialog.close()),
            );
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        dialog.addEventListener("click", (event) => {
            // Condicional: evalua "event.target === dialog" para decidir si ejecuta el bloque siguiente.
            if (event.target === dialog) {
                dialog.close();
            }
        });
    });
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    page.querySelectorAll("[data-refresh-clients]").forEach((button) =>
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        button.addEventListener("click", () => window.location.reload()),
    );

    // Condicional: evalua "page.dataset.openOnLoad === "true"" para decidir si ejecuta el bloque siguiente.
    if (page.dataset.openOnLoad === "true") {
        createDialog.showModal();
    }
    configureClientForms();
})();
