/**
 * Gestiona la interacción de la pantalla de usuarios.
 * La lógica empresarial y las validaciones definitivas permanecen en Laravel.
 */
(() => {
    const page = document.querySelector('[data-usuarios-page]');

    if (!page) {
        return;
    }

    // Abre un diálogo modal cuando el usuario inicia una operación de consulta o edición.
    const openDialog = (dialog) => {
        if (dialog && !dialog.open) {
            dialog.showModal();
        }
    };

    page.querySelectorAll('[data-open-user-create]').forEach((button) => {
        button.addEventListener('click', () => openDialog(document.querySelector('#nuevo-usuario')));
    });

    page.querySelectorAll('[data-open-user-view]').forEach((button) => {
        button.addEventListener('click', () => openDialog(document.querySelector(`#ver-usuario-${button.dataset.openUserView}`)));
    });

    page.querySelectorAll('[data-open-user-edit]').forEach((button) => {
        button.addEventListener('click', () => openDialog(document.querySelector(`#editar-usuario-${button.dataset.openUserEdit}`)));
    });

    // Permite cerrar los diálogos mediante sus botones o al pulsar fuera de su contenido.
    document.querySelectorAll('[data-close-user-dialog]').forEach((button) => {
        button.addEventListener('click', () => button.closest('dialog')?.close());
    });

    document.querySelectorAll('.usuarios-dialog').forEach((dialog) => {
        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) {
                dialog.close();
            }
        });
    });

    // Envía automáticamente el formulario cuando cambia el tamaño de página.
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

    const updateCustomerFields = () => {
        const isCustomer = role?.value.startsWith('cliente_');
        const isRuc = documentType?.value === 'ruc';

        if (documentField) {
            documentField.required = Boolean(isCustomer);
            documentField.maxLength = isRuc ? 11 : 8;
            documentField.placeholder = isRuc ? '11 dígitos del RUC' : '8 dígitos del DNI';
            documentField.pattern = isRuc ? '(10|15|17|20)[0-9]{9}' : '[0-9]{8}';
            documentField.inputMode = 'numeric';
        }

        if (roleNote) {
            roleNote.hidden = !isCustomer;
        }

        if (customerSection) {
            customerSection.hidden = !isCustomer;
        }

        customerFields?.forEach((field) => {
            field.hidden = !isCustomer;
        });

        if (customerRequired) {
            customerRequired.hidden = !isCustomer;
        }

        rucFields?.forEach((field) => {
            field.hidden = !isRuc;
            const input = field.querySelector('input');
            if (input) {
                input.required = isRuc;
            }
        });

        if (documentType) {
            documentType.required = isCustomer;
        }

        if (documentLabel) {
            documentLabel.firstChild.textContent = isRuc ? 'RUC ' : 'DNI ';
        }

        const needsSite = role?.selectedOptions[0]?.dataset.scope === 'sede';
        if (siteField) {
            siteField.hidden = !needsSite;
        }

        if (siteSelect) {
            siteSelect.required = needsSite;
        }
    };

    role?.addEventListener('change', updateCustomerFields);
    documentType?.addEventListener('change', updateCustomerFields);
    updateCustomerFields();

    // Reabre el formulario de creación cuando Laravel devolvió errores de validación.
    if (page.dataset.openCreateDialog === 'true') {
        openDialog(document.querySelector('#nuevo-usuario'));
    }
})();
