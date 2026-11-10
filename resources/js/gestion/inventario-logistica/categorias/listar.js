document.addEventListener('DOMContentLoaded', () => {
    const page = document.querySelector('[data-categorias-page]');
    const createDialog = document.querySelector('#nueva-categoria');

    if (!page || !createDialog) {
        return;
    }

    page.querySelectorAll('[data-open-category-create]').forEach((button) => {
        button.addEventListener('click', () => createDialog.showModal());
    });

    createDialog.querySelector('[data-close-category-create]')?.addEventListener('click', () => createDialog.close());
    createDialog.addEventListener('click', (event) => {
        if (event.target === createDialog) {
            createDialog.close();
        }
    });

    page.querySelectorAll('[data-category-dialog]').forEach((trigger) => {
        const editDialog = document.getElementById(trigger.dataset.categoryDialog);

        trigger.querySelector('summary')?.addEventListener('click', (event) => {
            event.preventDefault();
            editDialog?.showModal();
        });
    });

    document.querySelectorAll('.categorias-edit-dialog').forEach((editDialog) => {
        editDialog.querySelector('[data-close-category-edit]')?.addEventListener('click', () => editDialog.close());
        editDialog.addEventListener('click', (event) => {
            if (event.target === editDialog) {
                editDialog.close();
            }
        });
    });

    page.querySelectorAll('[data-open-category-view]').forEach((button) => {
        const viewDialog = document.getElementById(button.dataset.categoryViewDialog);

        button.addEventListener('click', () => viewDialog?.showModal());
    });

    document.querySelectorAll('.categorias-view-dialog').forEach((viewDialog) => {
        viewDialog.querySelectorAll('[data-close-category-view]').forEach((button) => button.addEventListener('click', () => viewDialog.close()));
        viewDialog.addEventListener('click', (event) => {
            if (event.target === viewDialog) {
                viewDialog.close();
            }
        });
    });
});
