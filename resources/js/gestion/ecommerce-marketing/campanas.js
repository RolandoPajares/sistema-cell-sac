/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/gestion/ecommerce-marketing/campanas.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

// Funcion: openCampaignDialog concentra una tarea reutilizable del modulo.
const openCampaignDialog = (dialog) => {
    // Condicional: evalua "dialog && !dialog.open" para decidir si ejecuta el bloque siguiente.
    if (dialog && !dialog.open) {
        dialog.showModal();
    }
};

// Funcion: closeCampaignDialog concentra una tarea reutilizable del modulo.
const closeCampaignDialog = (dialog) => {
    // Condicional: evalua "dialog instanceof HTMLDialogElement && dialog.open" para decidir si ejecuta el bloque siguiente.
    if (dialog instanceof HTMLDialogElement && dialog.open) {
        dialog.close();
    }
};

// Funcion: initializeCampaignForms concentra una tarea reutilizable del modulo.
const initializeCampaignForms = (page) => {
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    page.querySelectorAll('[data-campaign-form]').forEach((form) => {
        const fileInput = form.querySelector('[data-campaign-image-input]');
        const urlInput = form.querySelector('[data-campaign-image-url]');
        const preview = form.querySelector('[data-campaign-preview]');
        const previewImage = form.querySelector('[data-campaign-preview-image]');
        const destination = form.querySelector('[data-campaign-destination]');
        const allSegments = form.querySelector('[data-campaign-all]');
        const segments = [...form.querySelectorAll('input[name="segmentos[]"]')];

        // Funcion: showPreview concentra una tarea reutilizable del modulo.
        const showPreview = (source) => {
            // Condicional: evalua "!source || !previewImage || !preview" para decidir si ejecuta el bloque siguiente.
            if (!source || !previewImage || !preview) {
                return;
            }

            previewImage.src = source;
            preview.hidden = false;
        };

        // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
        fileInput?.addEventListener('change', () => {
            const file = fileInput.files?.[0];

            // Condicional: evalua "file" para decidir si ejecuta el bloque siguiente.
            if (file) {
                showPreview(URL.createObjectURL(file));
            }
        });
        // Evento: escucha el evento input y ejecuta la funcion asociada cuando ocurre.
        urlInput?.addEventListener('input', () => showPreview(urlInput.value.trim()));
        // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
        destination?.addEventListener('change', () => {
            // Condicional: evalua "urlInput && destination.value" para decidir si ejecuta el bloque siguiente.
            if (urlInput && destination.value) {
                urlInput.value = destination.value;
            }
        });
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        form.querySelector('[data-clear-campaign-image]')?.addEventListener('click', () => {
            // Condicional: evalua "fileInput" para decidir si ejecuta el bloque siguiente.
            if (fileInput) {
                fileInput.value = '';
            }

            // Condicional: evalua "urlInput" para decidir si ejecuta el bloque siguiente.
            if (urlInput) {
                urlInput.value = '';
            }

            // Condicional: evalua "preview" para decidir si ejecuta el bloque siguiente.
            if (preview) {
                preview.hidden = true;
            }
        });
        // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
        allSegments?.addEventListener('change', () => {
            // Condicional: evalua "allSegments.checked" para decidir si ejecuta el bloque siguiente.
            if (allSegments.checked) {
                // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
                segments.forEach((checkbox) => { checkbox.checked = false; });
            }
        });
        // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
        segments.forEach((checkbox) => checkbox.addEventListener('change', () => {
            // Condicional: evalua "checkbox.checked && allSegments" para decidir si ejecuta el bloque siguiente.
            if (checkbox.checked && allSegments) {
                allSegments.checked = false;
            }

            // Condicional: evalua "allSegments && !segments.some((item) => item.checked)" para decidir si ejecuta el bloque siguiente.
            if (allSegments && !segments.some((item) => item.checked)) {
                allSegments.checked = true;
            }
        }));
    });
};

// Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
document.querySelectorAll('[data-campanias-page]').forEach((page) => {
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    page.querySelectorAll('[data-open-campaign-create]').forEach((button) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        button.addEventListener('click', () => openCampaignDialog(document.querySelector('#nueva-campania')));
    });
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    page.querySelector('[data-open-campaign-import]')?.addEventListener('click', () => {
        openCampaignDialog(document.querySelector('#importar-campanias'));
    });
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    page.querySelectorAll('[data-open-campaign-edit]').forEach((button) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        button.addEventListener('click', () => openCampaignDialog(document.querySelector(`#editar-campania-${button.dataset.openCampaignEdit}`)));
    });
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    page.querySelectorAll('[data-open-campaign-view]').forEach((button) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        button.addEventListener('click', (event) => {
            event.preventDefault();
            openCampaignDialog(document.querySelector(`#campania-detalle-${button.dataset.openCampaignView}`));
        });
    });
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    page.querySelectorAll('[data-close-campaign-dialog]').forEach((button) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        button.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            closeCampaignDialog(button.closest('dialog'));
        });
    });
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    page.querySelectorAll('.campanias-dialog').forEach((dialog) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        dialog.addEventListener('click', (event) => {
            // Condicional: evalua "event.target === dialog" para decidir si ejecuta el bloque siguiente.
            if (event.target === dialog) {
                closeCampaignDialog(dialog);
            }
        });
    });
    // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
    page.querySelector('[data-campaign-per-page]')?.addEventListener('change', (event) => {
        event.currentTarget.form?.submit();
    });
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    page.querySelectorAll('[data-campanias-angle]').forEach((element) => {
        element.style.setProperty('--campanias-angle', `${element.dataset.campaniasAngle}deg`);
    });
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    page.querySelectorAll('[data-campanias-width]').forEach((element) => {
        element.style.width = `${element.dataset.campaniasWidth}%`;
    });

    initializeCampaignForms(page);

    // Condicional: evalua "page.dataset.campaniasReopen === 'crear'" para decidir si ejecuta el bloque siguiente.
    if (page.dataset.campaniasReopen === 'crear') {
        openCampaignDialog(document.querySelector('#nueva-campania'));
    }
});
