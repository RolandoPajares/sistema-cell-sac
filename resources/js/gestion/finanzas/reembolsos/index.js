/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/gestion/finanzas/reembolsos/index.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

/**
 * Ejecuta la operación initializeReembolsos utilizada por el módulo JavaScript.
 *
 * @param {*} root - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: initializeReembolsos concentra una tarea reutilizable del modulo.
export const initializeReembolsos = (root) => {
    // Condicional: evalua "!root || root.dataset.financeReembolsosReady === 'true'" para decidir si ejecuta el bloque siguiente.
    if (!root || root.dataset.financeReembolsosReady === 'true') {
        return;
    }

    root.dataset.financeReembolsosReady = 'true';
    bindFinanceFilterStatus(root, 'reembolsos');
    initializeRefundDemo(root);
};

/**
 * Ejecuta la operación initializeRefundDemo utilizada por el módulo JavaScript.
 *
 * @param {*} root - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: initializeRefundDemo concentra una tarea reutilizable del modulo.
const initializeRefundDemo = (root) => {
    const form = root.querySelector('[data-finance-refund-form]');
    const dialog = root.querySelector('[data-finance-dialog="crear"], [data-pending-modal="crear"]');
    const confirm = form?.querySelector('[data-finance-refund-confirm]');
    const error = form?.querySelector('[data-finance-refund-error]');
    const feedback = form?.querySelector('[data-finance-refund-feedback]');
    const evidence = form?.querySelector('[data-finance-refund-evidence]');
    const evidenceName = form?.querySelector('[data-finance-refund-evidence-name]');

    // Condicional: evalua "!form || !dialog || !confirm" para decidir si ejecuta el bloque siguiente.
    if (!form || !dialog || !confirm) {
        return;
    }

    // Condicional: evalua "new URLSearchParams(window.location.search).get('financeAction') === 'reembolso'" para decidir si ejecuta el bloque siguiente.
    if (new URLSearchParams(window.location.search).get('financeAction') === 'reembolso') {
        dialog.showModal();
    }

    // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
    evidence?.addEventListener('change', () => {
        const file = evidence.files?.[0] ?? null;
        // Condicional: evalua "evidenceName" para decidir si ejecuta el bloque siguiente.
        if (evidenceName) {
            evidenceName.textContent = file
                ? `Seleccionado: ${file.name}. No se leerá ni transmitirá.`
                : 'No se ha seleccionado evidencia. El archivo no se leerá ni transmitirá.';
        }
    });

    // Evento: escucha el evento close y ejecuta la funcion asociada cuando ocurre.
    dialog.addEventListener('close', () => {
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        form.querySelectorAll('input, select, textarea').forEach((field) => {
            field.removeAttribute('aria-invalid');
            // Condicional: evalua "field.matches('input[type="file"]')" para decidir si ejecuta el bloque siguiente.
            if (field.matches('input[type="file"]')) {
                field.value = '';
            // Condicional alternativo: este bloque se ejecuta cuando la comprobacion anterior no se cumple.
            } else if (field.matches('input, textarea')) {
                field.value = '';
            // Caso alternativo: atiende la situacion en la que ninguna condicion anterior resulto verdadera.
            } else {
                field.selectedIndex = 0;
            }
        });
        // Condicional: evalua "error" para decidir si ejecuta el bloque siguiente.
        if (error) error.hidden = true;
        // Condicional: evalua "feedback" para decidir si ejecuta el bloque siguiente.
        if (feedback) feedback.hidden = true;
        // Condicional: evalua "evidenceName" para decidir si ejecuta el bloque siguiente.
        if (evidenceName) {
            evidenceName.textContent = 'No se ha seleccionado evidencia. El archivo no se leerá ni transmitirá.';
        }
        confirm.disabled = false;
    });

    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    confirm.addEventListener('click', () => {
        const fields = [...form.querySelectorAll('input[required], select[required], textarea[required]')];
        // Funcion: invalid concentra una tarea reutilizable del modulo.
        const invalid = fields.find((field) => !field.checkValidity());
        // Condicional: evalua "invalid" para decidir si ejecuta el bloque siguiente.
        if (invalid) {
            invalid.setAttribute('aria-invalid', 'true');
            // Condicional: evalua "error" para decidir si ejecuta el bloque siguiente.
            if (error) {
                error.textContent = 'Completa los campos obligatorios y revisa el importe.';
                error.hidden = false;
            }
            invalid.focus();
            return;
        }

        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        fields.forEach((field) => field.removeAttribute('aria-invalid'));
        // Condicional: evalua "error" para decidir si ejecuta el bloque siguiente.
        if (error) error.hidden = true;
        // Condicional: evalua "feedback" para decidir si ejecuta el bloque siguiente.
        if (feedback) {
            feedback.textContent = 'Integración pendiente: la solicitud no se guardó, no se aprobó y no se realizó un desembolso.';
            feedback.hidden = false;
        }
        confirm.disabled = true;
    });
};

/**
 * Ejecuta la operación bindFinanceFilterStatus utilizada por el módulo JavaScript.
 *
 * @param {*} root - Parámetro recibido por la función.
 *
 * @param {*} moduleName - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: bindFinanceFilterStatus concentra una tarea reutilizable del modulo.
const bindFinanceFilterStatus = (root, moduleName) => {
    const status = root.querySelector('[data-finance-filter-status]');

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    root.querySelectorAll('.admin-finance-filters [data-pending-filter]').forEach((filter) => {
        // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
        filter.addEventListener('change', () => {
            // Condicional: evalua "status" para decidir si ejecuta el bloque siguiente.
            if (status) {
                status.textContent = `Filtro de ${moduleName}: ${filter.value}. La consulta se aplicará cuando exista integración financiera.`;
            }
        });
    });
};
