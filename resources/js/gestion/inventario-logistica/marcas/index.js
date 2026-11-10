document.addEventListener('DOMContentLoaded', () => {
    const page = document.querySelector('[data-marcas-page]');
    const createDialog = document.querySelector('#nueva-marca');

    if (!page || !createDialog) {
        return;
    }

    page.querySelector('[data-open-brand-create]')?.addEventListener('click', () => createDialog.showModal());
    page.querySelectorAll('[data-open-brand-edit]').forEach((button) => {
        button.addEventListener('click', () => document.getElementById(`editar-marca-${button.dataset.openBrandEdit}`)?.showModal());
    });
    document.querySelectorAll('.marcas-dialog').forEach((dialog) => {
        dialog.querySelectorAll('[data-close-brand-dialog]').forEach((button) => button.addEventListener('click', () => dialog.close()));
        dialog.addEventListener('click', (event) => { if (event.target === dialog) { dialog.close(); } });
    });
});
