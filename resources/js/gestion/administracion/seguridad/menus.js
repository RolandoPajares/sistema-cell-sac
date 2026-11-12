/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/gestion/administracion/seguridad/menus.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

const page = document.querySelector('[data-menu-page]');

// Condicional: evalua "page" para decidir si ejecuta el bloque siguiente.
if (page) {
    // Funcion: open concentra una tarea reutilizable del modulo.
    const open = (id) => {
        const dialog = document.getElementById(id);
        // Condicional: evalua "dialog && !dialog.open" para decidir si ejecuta el bloque siguiente.
        if (dialog && !dialog.open) {
            dialog.showModal();
        }
    };

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    page.querySelectorAll('[data-open-dialog]').forEach((button) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        button.addEventListener('click', () => open(button.dataset.openDialog));
    });

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    document.querySelectorAll('[data-close-dialog]').forEach((button) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        button.addEventListener('click', () => button.closest('dialog')?.close());
    });

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    document.querySelectorAll('.tc-rbac-dialog').forEach((dialog) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        dialog.addEventListener('click', (event) => {
            // Condicional: evalua "event.target === dialog" para decidir si ejecuta el bloque siguiente.
            if (event.target === dialog) {
                dialog.close();
            }
        });
    });

    // Funcion: descendants concentra una tarea reutilizable del modulo.
    const descendants = (key) => [...page.querySelectorAll(`[data-tree-parent="${key}"]`)];
    // Funcion: setExpanded concentra una tarea reutilizable del modulo.
    const setExpanded = (key, expanded) => {
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        descendants(key).forEach((row) => {
            row.hidden = !expanded;
            // Condicional: evalua "!expanded && row.dataset.treeKey" para decidir si ejecuta el bloque siguiente.
            if (!expanded && row.dataset.treeKey) {
                setExpanded(row.dataset.treeKey, false);
            }
        });
    };

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    page.querySelectorAll('[data-tree-toggle]').forEach((button) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        button.addEventListener('click', () => {
            const expanded = button.getAttribute('aria-expanded') !== 'true';
            button.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            const icon = button.querySelector('i');
            icon?.classList.toggle('bi-chevron-down', expanded);
            icon?.classList.toggle('bi-chevron-right', !expanded);
            setExpanded(button.dataset.treeToggle, expanded);
        });
    });

    const filter = page.querySelector('[data-menu-filter]');
    const status = page.querySelector('[data-menu-status]');
    // Funcion: applyFilters concentra una tarea reutilizable del modulo.
    const applyFilters = () => {
        const value = (filter?.value || '').toLowerCase().trim();
        const state = status?.value || '';
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        page.querySelectorAll('[data-menu-row]').forEach((row) => {
            row.hidden = (value !== '' && !row.dataset.search.includes(value))
                || (state !== '' && row.dataset.status !== state);
        });
    };

    // Evento: escucha el evento input y ejecuta la funcion asociada cuando ocurre.
    filter?.addEventListener('input', applyFilters);
    // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
    status?.addEventListener('change', applyFilters);
}
