/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/gestion/inventario-logistica/marcas/index.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

// Evento: escucha el evento DOMContentLoaded y ejecuta la funcion asociada cuando ocurre.
document.addEventListener('DOMContentLoaded', () => {
    const page = document.querySelector('[data-marcas-page]');
    const createDialog = document.querySelector('#nueva-marca');

    // Condicional: evalua "!page || !createDialog" para decidir si ejecuta el bloque siguiente.
    if (!page || !createDialog) {
        return;
    }

    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    page.querySelector('[data-open-brand-create]')?.addEventListener('click', () => createDialog.showModal());
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    page.querySelectorAll('[data-open-brand-edit]').forEach((button) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        button.addEventListener('click', () => document.getElementById(`editar-marca-${button.dataset.openBrandEdit}`)?.showModal());
    });
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    document.querySelectorAll('.marcas-dialog').forEach((dialog) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        dialog.querySelectorAll('[data-close-brand-dialog]').forEach((button) => button.addEventListener('click', () => dialog.close()));
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        dialog.addEventListener('click', (event) => { if (event.target === dialog) { dialog.close(); } });
    });
});
