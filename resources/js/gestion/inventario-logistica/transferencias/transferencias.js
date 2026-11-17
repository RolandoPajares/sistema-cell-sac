/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/gestion/inventario-logistica/transferencias/transferencias.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

// Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
document.querySelectorAll('[data-il-transfer-manager]').forEach((page) => {
    const dialog = page.querySelector('[data-transfer-dialog]');
    const form = page.querySelector('[data-transfer-create-form]');
    const lines = page.querySelector('[data-transfer-lines]');
    const template = page.querySelector('[data-transfer-line-template]');

    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    page.querySelector('[data-transfer-open]')?.addEventListener('click', () => dialog?.showModal());
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    page.querySelectorAll('[data-transfer-close]').forEach((button) => button.addEventListener('click', () => dialog?.close()));

/**
 * Actualiza la información visible o el estado del componente.
 *
 * @returns {*} Resultado de la operación.
 */
    // Funcion: updateLineNames concentra una tarea reutilizable del modulo.
    const updateLineNames = () => {
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        lines?.querySelectorAll('[data-transfer-line]').forEach((line, index) => {
            const select = line.querySelector('[data-transfer-product]');
            const quantity = line.querySelector('[data-transfer-quantity]');
            const productId = line.querySelector('[data-transfer-product-id]');
            const variantId = line.querySelector('[data-transfer-variant-id]');

            select.name = `items[${index}][producto-variante]`;
            quantity.name = `items[${index}][cantidad]`;
            productId.name = `items[${index}][producto_id]`;
            variantId.name = `items[${index}][variante_id]`;

            const [selectedProductId, selectedVariantId] = (select.value || ':').split(':');
            productId.value = selectedProductId || '';
            variantId.value = selectedVariantId === 'null' ? '' : (selectedVariantId || '');
        });
    };

/**
 * Ejecuta la operación syncSelection utilizada por el módulo JavaScript.
 *
 * @param {*} event - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
    // Funcion: syncSelection concentra una tarea reutilizable del modulo.
    const syncSelection = (event) => {
        // Condicional: evalua "event.target.matches('[data-transfer-product]')" para decidir si ejecuta el bloque siguiente.
        if (event.target.matches('[data-transfer-product]')) {
            updateLineNames();
        }
    };

    // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
    lines?.addEventListener('change', syncSelection);
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    lines?.addEventListener('click', (event) => {
        const removeButton = event.target.closest('[data-transfer-remove]');
        // Condicional: evalua "removeButton" para decidir si ejecuta el bloque siguiente.
        if (removeButton) {
            removeButton.closest('[data-transfer-line]')?.remove();
            updateLineNames();
        }
    });

    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    page.querySelector('[data-transfer-add]')?.addEventListener('click', () => {
        const fragment = template?.content.cloneNode(true);
        // Condicional: evalua "fragment" para decidir si ejecuta el bloque siguiente.
        if (fragment) {
            lines?.append(fragment);
            updateLineNames();
        }
    });

    // Evento: escucha el evento submit y ejecuta la funcion asociada cuando ocurre.
    form?.addEventListener('submit', (event) => {
        updateLineNames();
        const origin = form.querySelector('[name="almacen_origen_id"]')?.value;
        const destination = form.querySelector('[name="almacen_destino_id"]')?.value;
        // Condicional: evalua "origin && destination && origin === destination" para decidir si ejecuta el bloque siguiente.
        if (origin && destination && origin === destination) {
            event.preventDefault();
            form.querySelector('[name="almacen_destino_id"]')?.setCustomValidity('El almacén de destino debe ser diferente al de origen.');
            form.querySelector('[name="almacen_destino_id"]')?.reportValidity();
        }
    });

    // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
    form?.querySelector('[name="almacen_destino_id"]')?.addEventListener('change', (event) => event.target.setCustomValidity(''));
    updateLineNames();
});