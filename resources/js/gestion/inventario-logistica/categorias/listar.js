/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/gestion/inventario-logistica/categorias/listar.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

// Evento: escucha el evento DOMContentLoaded y ejecuta la funcion asociada cuando ocurre.
document.addEventListener('DOMContentLoaded', () => {
    const page = document.querySelector('[data-categorias-page]');
    const createDialog = document.querySelector('#nueva-categoria');

    // Condicional: evalua "!page || !createDialog" para decidir si ejecuta el bloque siguiente.
    if (!page || !createDialog) {
        return;
    }

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    page.querySelectorAll('[data-open-category-create]').forEach((button) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        button.addEventListener('click', () => createDialog.showModal());
    });

    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    createDialog.querySelector('[data-close-category-create]')?.addEventListener('click', () => createDialog.close());
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    createDialog.addEventListener('click', (event) => {
        // Condicional: evalua "event.target === createDialog" para decidir si ejecuta el bloque siguiente.
        if (event.target === createDialog) {
            createDialog.close();
        }
    });

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    page.querySelectorAll('[data-category-dialog]').forEach((trigger) => {
        const editDialog = document.getElementById(trigger.dataset.categoryDialog);

        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        trigger.querySelector('summary')?.addEventListener('click', (event) => {
            event.preventDefault();
            editDialog?.showModal();
        });
    });

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    document.querySelectorAll('.categorias-edit-dialog').forEach((editDialog) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        editDialog.querySelector('[data-close-category-edit]')?.addEventListener('click', () => editDialog.close());
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        editDialog.addEventListener('click', (event) => {
            // Condicional: evalua "event.target === editDialog" para decidir si ejecuta el bloque siguiente.
            if (event.target === editDialog) {
                editDialog.close();
            }
        });
    });

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    page.querySelectorAll('[data-open-category-view]').forEach((button) => {
        const viewDialog = document.getElementById(button.dataset.categoryViewDialog);

        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        button.addEventListener('click', () => viewDialog?.showModal());
    });

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    document.querySelectorAll('.categorias-view-dialog').forEach((viewDialog) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        viewDialog.querySelectorAll('[data-close-category-view]').forEach((button) => button.addEventListener('click', () => viewDialog.close()));
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        viewDialog.addEventListener('click', (event) => {
            // Condicional: evalua "event.target === viewDialog" para decidir si ejecuta el bloque siguiente.
            if (event.target === viewDialog) {
                viewDialog.close();
            }
        });
    });
});
