document.addEventListener('DOMContentLoaded', () => {
    const dialog = document.querySelector('#nuevo-producto');
    const importDialog = document.querySelector('#importar-productos');
    const page = document.querySelector('[data-product-page]');

    if (!dialog || !page) {
        return;
    }

    page.querySelectorAll('[data-open-product-create]').forEach((button) => {
        button.addEventListener('click', () => dialog.showModal());
    });

    page.querySelectorAll('[data-open-product-import]').forEach((button) => {
        button.addEventListener('click', () => importDialog?.showModal());
    });

    importDialog?.querySelectorAll('[data-close-product-import]').forEach((button) => {
        button.addEventListener('click', () => importDialog.close());
    });

    importDialog?.querySelector('input[type="file"]')?.addEventListener('change', (event) => {
        const selected = event.currentTarget.files?.[0];
        const label = importDialog.querySelector('[data-product-import-file-name]');
        if (label) label.textContent = selected?.name || 'Ningún archivo seleccionado';
    });

    importDialog?.addEventListener('click', (event) => {
        if (event.target === importDialog) {
            importDialog.close();
        }
    });

    dialog.querySelector('[data-close-product-create]')?.addEventListener('click', () => dialog.close());
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            dialog.close();
        }
    });

    const imageInput = dialog.querySelector('[data-product-image-input]');
    const imagePreview = dialog.querySelector('[data-product-image-preview]');
    const imagePreviewImage = dialog.querySelector('[data-product-image-preview-image]');
    const clearImage = dialog.querySelector('[data-product-image-clear]');

    imageInput?.addEventListener('change', () => {
        const [file] = imageInput.files ?? [];

        if (!file || !imagePreview || !imagePreviewImage) {
            return;
        }

        imagePreviewImage.src = URL.createObjectURL(file);
        imagePreview.hidden = false;
    });

    clearImage?.addEventListener('click', () => {
        if (imageInput) {
            imageInput.value = '';
        }
        if (imagePreviewImage) {
            imagePreviewImage.src = '';
        }
        if (imagePreview) {
            imagePreview.hidden = true;
        }
    });

    document.querySelectorAll('[data-open-product-edit]').forEach((button) => {
        const editDialog = document.getElementById(button.dataset.productDialog);

        button.addEventListener('click', () => editDialog?.showModal());
    });

    document.querySelectorAll('.productos-edit-dialog').forEach((editDialog) => {
        editDialog.querySelector('[data-close-product-edit]')?.addEventListener('click', () => editDialog.close());
        editDialog.addEventListener('click', (event) => {
            if (event.target === editDialog) {
                editDialog.close();
            }
        });
    });

    const productEditId = Number(page.dataset.productEditId || 0);
    if (productEditId > 0) {
        document.getElementById(`editar-producto-${productEditId}`)?.showModal();
    }

    page.querySelectorAll('[data-open-product-view]').forEach((button) => {
        const viewDialog = document.getElementById(button.dataset.productViewDialog);
        const editDialog = document.getElementById(button.dataset.productDialog);

        button.addEventListener('click', () => {
            if (viewDialog) {
                viewDialog.showModal();
            } else if (editDialog) {
                editDialog.showModal();
            }
        });
    });

    document.querySelectorAll('.productos-view-dialog').forEach((viewDialog) => {
        viewDialog.querySelectorAll('[data-close-product-view]').forEach((button) => button.addEventListener('click', () => viewDialog.close()));
        viewDialog.addEventListener('click', (event) => {
            if (event.target === viewDialog) {
                viewDialog.close();
            }
        });
    });

    document.querySelectorAll('[data-open-product-variants]').forEach((button) => {
        const variantsDialog = document.getElementById(button.dataset.productVariantsDialog);

        button.addEventListener('click', () => {
            button.closest('.productos-view-dialog')?.close();
            variantsDialog?.showModal();
        });
    });

    document.querySelectorAll('.productos-variants-dialog').forEach((variantsDialog) => {
        variantsDialog.querySelector('[data-close-product-variants]')?.addEventListener('click', () => variantsDialog.close());
        variantsDialog.querySelectorAll('[data-open-variant-edit]').forEach((button) => {
            const editDialog = document.getElementById(button.dataset.openVariantEdit);

            button.addEventListener('click', () => editDialog?.showModal());
        });
        variantsDialog.addEventListener('click', (event) => {
            if (event.target === variantsDialog) {
                variantsDialog.close();
            }
        });
    });

    document.querySelectorAll('.productos-variant-edit-dialog').forEach((editDialog) => {
        editDialog.querySelectorAll('[data-close-variant-edit]').forEach((button) => button.addEventListener('click', () => editDialog.close()));
        editDialog.addEventListener('click', (event) => {
            if (event.target === editDialog) {
                editDialog.close();
            }
        });
    });

    const variantManagerProductId = Number(page.dataset.variantManagerProductId || 0);
    const variantEditorId = Number(page.dataset.variantEditorId || 0);
    if (variantManagerProductId > 0) {
        document.getElementById(`variantes-producto-${variantManagerProductId}`)?.showModal();

        if (variantEditorId > 0) {
            document.getElementById(`editar-variante-${variantEditorId}`)?.showModal();
        }
    }

    const hasErrors = page.dataset.hasErrors === '1';
    if (hasErrors) {
        const productErrorModalId = Number(page.dataset.productErrorModalId || 0);
        const variantErrorModalId = Number(page.dataset.variantErrorModalId || 0);

        if (productErrorModalId > 0) {
            document.getElementById(`variantes-producto-${productErrorModalId}`)?.showModal();

            if (variantErrorModalId > 0) {
                document.getElementById(`editar-variante-${variantErrorModalId}`)?.showModal();
            }
        } else {
            dialog.showModal();
        }
    }

    if (page.dataset.openProductModal === '1') {
        dialog.showModal();
    }
});
