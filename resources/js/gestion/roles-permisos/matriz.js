/**
 * Interacciones del módulo de roles y permisos: matriz.
 */

(() => {
    /**
     * Gestiona la matriz de permisos sin persistir cambios hasta confirmar el formulario.
     * @@returns {void} Configura los eventos y el estado visual de la matriz.
     */
    const page = document.querySelector('[data-matrix-page]');
    if (!page) return;
    /** Mantiene protegidas las asignaciones del módulo de Seguridad y Delegación. */
    page.querySelectorAll('input[data-module="seguridad"]').forEach((input) => { input.disabled = true; });
    page.querySelector('[data-select-module="seguridad"]')?.setAttribute('disabled', 'disabled');
    const form = page.querySelector('[data-permission-form]');
    const checkboxes = () => [...page.querySelectorAll('input[data-permission-id]:not(:disabled)')];
    const ids = () => checkboxes().filter((input) => input.checked).map((input) => Number(input.dataset.permissionId));
    const original = new Set(JSON.parse(page.dataset.currentPermissions || '[]').map(Number));
    const template = new Set(JSON.parse(page.dataset.templatePermissions || '[]').map(Number));
    const catalog = JSON.parse(page.dataset.previewItems || '[]');
    const roleSelector = page.querySelector('[data-role-selector]');

    /** @@returns {void} Actualiza el contador y el estado pendiente de la matriz. */
    const refreshState = () => {
        const selected = new Set(ids());
        const changed = selected.size !== original.size || [...selected].some((id) => !original.has(id));
        page.querySelector('[data-assigned-count]').textContent = String(selected.size);
        const state = page.querySelector('[data-saved-state]');
        if (state) state.textContent = changed ? 'Hay cambios pendientes de guardar.' : `Rol editable: ${roleSelector?.selectedOptions[0]?.text || ''}. Sin cambios pendientes.`;
    };

    /** @@returns {void} Aplica la búsqueda y el filtro de módulo sobre filas del catálogo. */
    const filterRows = () => {
        const text = (page.querySelector('[data-matrix-filter]')?.value || '').toLowerCase().trim();
        const module = page.querySelector('[data-module-filter]')?.value || '';
        page.querySelectorAll('[data-matrix-row]').forEach((row) => { row.hidden = (text !== '' && !row.dataset.search.includes(text)) || (module !== '' && row.dataset.moduleCode !== module); });
    };

    /**
     * Compara la selección actual con una propuesta de permisos.
     * @@param {Set<number>} proposal Permisos propuestos.
     * @@returns Objeto con permisos a agregar, retirar y conservar.
     */
    const diff = (proposal) => { const current = new Set(ids()); return { agregar: [...proposal].filter((id) => !current.has(id)), retirar: [...current].filter((id) => !proposal.has(id)), conservar: [...current].filter((id) => proposal.has(id)) }; };

    /**
     * Obtiene nombres reales para mostrar una diferencia de permisos.
     * @@param {number[]} permissionIds Identificadores de permisos.
     * @@returns {string} Etiquetas de los permisos.
     */
    const labels = (permissionIds) => permissionIds.map((id) => page.querySelector(`input[value="${id}"]`)?.closest('label')?.title || `Permiso #${id}`).join(', ') || 'ninguno';

    page.querySelector('[data-matrix-filter]')?.addEventListener('input', filterRows);
    page.querySelector('[data-module-filter]')?.addEventListener('change', filterRows);
    checkboxes().forEach((input) => input.addEventListener('change', refreshState));
    page.querySelectorAll('[data-select-module]').forEach((button) => button.addEventListener('click', () => { const inputs = checkboxes().filter((input) => input.dataset.module === button.dataset.selectModule); const shouldCheck = inputs.some((input) => !input.checked); inputs.forEach((input) => { input.checked = shouldCheck; }); refreshState(); }));
    page.querySelector('[data-apply-template]')?.addEventListener('click', () => { const changes = diff(template); if (!window.confirm(`La plantilla propone agregar: ${labels(changes.agregar)}; retirar: ${labels(changes.retirar)}; conservar: ${labels(changes.conservar)}. ¿Deseas cargarla como cambio pendiente?`)) return; checkboxes().forEach((input) => { input.checked = template.has(Number(input.dataset.permissionId)); }); refreshState(); });
    page.querySelector('[data-discard-changes]')?.addEventListener('click', () => { checkboxes().forEach((input) => { input.checked = original.has(Number(input.dataset.permissionId)); }); refreshState(); });
    form?.addEventListener('submit', (event) => { const changes = diff(new Set(ids())); if ((changes.agregar.length || changes.retirar.length) && !window.confirm(`Se agregarán ${changes.agregar.length} permisos y se retirarán ${changes.retirar.length}. ¿Confirmas guardar la transacción?`)) event.preventDefault(); });

    /** @@returns {void} Renderiza el menú real filtrado por la selección actual. */
    const renderPreview = () => { const allowed = new Set(ids()); const visible = catalog.filter((item) => item.tipo !== 'grupo' && item.permiso_id && allowed.has(Number(item.permiso_id))); const content = page.querySelector('[data-preview-content]'); if (!content) return; content.innerHTML = visible.length ? `<ul>${visible.map((item) => `<li><strong>${item.etiqueta}</strong>${item.ruta ? ` <small>· ${item.ruta}</small>` : ''}</li>`).join('')}</ul>` : '<p class="tc-rbac-muted">No hay pantallas autorizadas con la selección actual.</p>'; };
    page.querySelector('[data-preview-menu]')?.addEventListener('click', () => { renderPreview(); page.querySelector('[data-preview-dialog]')?.showModal(); });
    page.querySelectorAll('[data-close-preview]').forEach((button) => button.addEventListener('click', () => page.querySelector('[data-preview-dialog]')?.close()));
    roleSelector?.addEventListener('change', (event) => { const selected = new Set(ids()); if (selected.size !== original.size || [...selected].some((id) => !original.has(id))) { if (!window.confirm('Hay cambios pendientes. ¿Deseas descartarlos y cambiar de rol?')) { event.preventDefault(); roleSelector.value = page.dataset.roleId; return; } } roleSelector.form.submit(); });
    refreshState();
})();

