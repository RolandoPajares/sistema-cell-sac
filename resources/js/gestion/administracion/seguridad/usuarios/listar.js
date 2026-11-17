/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/gestion/administracion/seguridad/usuarios/listar.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

/**
 * Gestiona la interacción de la pantalla de usuarios.
 * La lógica empresarial y las validaciones definitivas permanecen en Laravel.
 */
(() => {
    const page = document.querySelector('[data-usuarios-page]');

    // Condicional: evalua "!page" para decidir si ejecuta el bloque siguiente.
    if (!page) {
        return;
    }

    // Abre un diálogo modal cuando el usuario inicia una operación de consulta o edición.
    // Funcion: openDialog concentra una tarea reutilizable del modulo.
    const openDialog = (dialog) => {
        // Condicional: evalua "dialog && !dialog.open" para decidir si ejecuta el bloque siguiente.
        if (dialog && !dialog.open) {
            dialog.showModal();
        }
    };

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    page.querySelectorAll('[data-open-user-create]').forEach((button) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        button.addEventListener('click', () => openDialog(document.querySelector('#nuevo-usuario')));
    });

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    page.querySelectorAll('[data-open-user-view]').forEach((button) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        button.addEventListener('click', () => openDialog(document.querySelector(`#ver-usuario-${button.dataset.openUserView}`)));
    });

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    page.querySelectorAll('[data-open-user-edit]').forEach((button) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        button.addEventListener('click', () => openDialog(document.querySelector(`#editar-usuario-${button.dataset.openUserEdit}`)));
    });

    // Permite cerrar los diálogos mediante sus botones o al pulsar fuera de su contenido.
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    document.querySelectorAll('[data-close-user-dialog]').forEach((button) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        button.addEventListener('click', () => button.closest('dialog')?.close());
    });

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    document.querySelectorAll('.usuarios-dialog').forEach((dialog) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        dialog.addEventListener('click', (event) => {
            // Condicional: evalua "event.target === dialog" para decidir si ejecuta el bloque siguiente.
            if (event.target === dialog) {
                dialog.close();
            }
        });
    });

    // Envía automáticamente el formulario cuando cambia el tamaño de página.
    // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
    document.querySelector('[data-users-per-page]')?.addEventListener('change', (event) => {
        event.currentTarget.form.submit();
    });

    // Controla la visibilidad de los datos adicionales según el rol seleccionado.
    const form = document.querySelector('[data-user-form]');
    const role = form?.querySelector('[data-user-role]');
    const documentField = form?.querySelector('[data-user-document]');
    const documentType = form?.querySelector('[data-user-document-type]');
    const documentLabel = form?.querySelector('[data-user-document-label]');
    const rucFields = form?.querySelectorAll('[data-user-ruc-field]');
    const roleNote = form?.querySelector('[data-user-role-note]');
    const customerSection = form?.querySelector('[data-user-customer-section]');
    const customerFields = form?.querySelectorAll('[data-user-customer-field]');
    const customerRequired = form?.querySelector('[data-user-customer-required]');
    const siteField = form?.querySelector('[data-user-site-field]');
    const siteSelect = form?.querySelector('[data-user-site]');

    // Funcion: updateCustomerFields concentra una tarea reutilizable del modulo.
    const updateCustomerFields = () => {
        const isCustomer = role?.value.startsWith('cliente_');
        const isRuc = documentType?.value === 'ruc';

        // Condicional: evalua "documentField" para decidir si ejecuta el bloque siguiente.
        if (documentField) {
            documentField.required = Boolean(isCustomer);
            documentField.maxLength = isRuc ? 11 : 8;
            documentField.placeholder = isRuc ? '11 dígitos del RUC' : '8 dígitos del DNI';
            documentField.pattern = isRuc ? '(10|15|17|20)[0-9]{9}' : '[0-9]{8}';
            documentField.inputMode = 'numeric';
        }

        // Condicional: evalua "roleNote" para decidir si ejecuta el bloque siguiente.
        if (roleNote) {
            roleNote.hidden = !isCustomer;
        }

        // Condicional: evalua "customerSection" para decidir si ejecuta el bloque siguiente.
        if (customerSection) {
            customerSection.hidden = !isCustomer;
        }

        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        customerFields?.forEach((field) => {
            field.hidden = !isCustomer;
        });

        // Condicional: evalua "customerRequired" para decidir si ejecuta el bloque siguiente.
        if (customerRequired) {
            customerRequired.hidden = !isCustomer;
        }

        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        rucFields?.forEach((field) => {
            field.hidden = !isRuc;
            const input = field.querySelector('input');
            // Condicional: evalua "input" para decidir si ejecuta el bloque siguiente.
            if (input) {
                input.required = isRuc;
            }
        });

        // Condicional: evalua "documentType" para decidir si ejecuta el bloque siguiente.
        if (documentType) {
            documentType.required = isCustomer;
        }

        // Condicional: evalua "documentLabel" para decidir si ejecuta el bloque siguiente.
        if (documentLabel) {
            documentLabel.firstChild.textContent = isRuc ? 'RUC ' : 'DNI ';
        }

        const needsSite = role?.selectedOptions[0]?.dataset.scope === 'sede';
        // Condicional: evalua "siteField" para decidir si ejecuta el bloque siguiente.
        if (siteField) {
            siteField.hidden = !needsSite;
        }

        // Condicional: evalua "siteSelect" para decidir si ejecuta el bloque siguiente.
        if (siteSelect) {
            siteSelect.required = needsSite;
        }
    };

    // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
    role?.addEventListener('change', updateCustomerFields);
    // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
    documentType?.addEventListener('change', updateCustomerFields);
    updateCustomerFields();

    // Reabre el formulario de creación cuando Laravel devolvió errores de validación.
    // Condicional: evalua "page.dataset.openCreateDialog === 'true'" para decidir si ejecuta el bloque siguiente.
    if (page.dataset.openCreateDialog === 'true') {
        openDialog(document.querySelector('#nuevo-usuario'));
    }
})();
