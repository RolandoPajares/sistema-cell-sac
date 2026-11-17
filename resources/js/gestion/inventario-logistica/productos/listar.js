/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/gestion/inventario-logistica/productos/listar.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

// Evento: escucha el evento DOMContentLoaded y ejecuta la funcion asociada cuando ocurre.
document.addEventListener("DOMContentLoaded", () => {
    const dialog = document.querySelector("#nuevo-producto");
    const importDialog = document.querySelector("#importar-productos");
    const page = document.querySelector("[data-product-page]");

    // Condicional: evalua "!dialog || !page" para decidir si ejecuta el bloque siguiente.
    if (!dialog || !page) {
        return;
    }

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    page.querySelectorAll("[data-open-product-create]").forEach((button) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        button.addEventListener("click", () => dialog.showModal());
    });

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    page.querySelectorAll("[data-open-product-import]").forEach((button) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        button.addEventListener("click", () => importDialog?.showModal());
    });

    importDialog
        ?.querySelectorAll("[data-close-product-import]")
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        .forEach((button) => {
            // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
            button.addEventListener("click", () => importDialog.close());
        });

    importDialog
        ?.querySelector('input[type="file"]')
        // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
        ?.addEventListener("change", (event) => {
            const selected = event.currentTarget.files?.[0];
            const label = importDialog.querySelector(
                "[data-product-import-file-name]",
            );
            // Condicional: evalua "label" para decidir si ejecuta el bloque siguiente.
            if (label)
                label.textContent =
                    selected?.name || "Ningún archivo seleccionado";
        });

    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    importDialog?.addEventListener("click", (event) => {
        // Condicional: evalua "event.target === importDialog" para decidir si ejecuta el bloque siguiente.
        if (event.target === importDialog) {
            importDialog.close();
        }
    });

    dialog
        .querySelector("[data-close-product-create]")
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        ?.addEventListener("click", () => dialog.close());
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    dialog.addEventListener("click", (event) => {
        // Condicional: evalua "event.target === dialog" para decidir si ejecuta el bloque siguiente.
        if (event.target === dialog) {
            dialog.close();
        }
    });

    const imageInput = dialog.querySelector("[data-product-image-input]");
    const imagePreview = dialog.querySelector("[data-product-image-preview]");
    const imagePreviewImage = dialog.querySelector(
        "[data-product-image-preview-image]",
    );
    const clearImage = dialog.querySelector("[data-product-image-clear]");

    // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
    imageInput?.addEventListener("change", () => {
        const [file] = imageInput.files ?? [];

        // Condicional: evalua "!file || !imagePreview || !imagePreviewImage" para decidir si ejecuta el bloque siguiente.
        if (!file || !imagePreview || !imagePreviewImage) {
            return;
        }

        imagePreviewImage.src = URL.createObjectURL(file);
        imagePreview.hidden = false;
    });

    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    clearImage?.addEventListener("click", () => {
        // Condicional: evalua "imageInput" para decidir si ejecuta el bloque siguiente.
        if (imageInput) {
            imageInput.value = "";
        }
        // Condicional: evalua "imagePreviewImage" para decidir si ejecuta el bloque siguiente.
        if (imagePreviewImage) {
            imagePreviewImage.src = "";
        }
        // Condicional: evalua "imagePreview" para decidir si ejecuta el bloque siguiente.
        if (imagePreview) {
            imagePreview.hidden = true;
        }
    });

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    document.querySelectorAll("[data-open-product-edit]").forEach((button) => {
        const editDialog = document.getElementById(
            button.dataset.productDialog,
        );

        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        button.addEventListener("click", () => editDialog?.showModal());
    });

    document
        .querySelectorAll(".productos-edit-dialog")
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        .forEach((editDialog) => {
            editDialog
                .querySelector("[data-close-product-edit]")
                // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
                ?.addEventListener("click", () => editDialog.close());
            // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
            editDialog.addEventListener("click", (event) => {
                // Condicional: evalua "event.target === editDialog" para decidir si ejecuta el bloque siguiente.
                if (event.target === editDialog) {
                    editDialog.close();
                }
            });
        });

    const productEditId = Number(page.dataset.productEditId || 0);
    // Condicional: evalua "productEditId > 0" para decidir si ejecuta el bloque siguiente.
    if (productEditId > 0) {
        document
            .getElementById(`editar-producto-${productEditId}`)
            ?.showModal();
    }

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    page.querySelectorAll("[data-open-product-view]").forEach((button) => {
        const viewDialog = document.getElementById(
            button.dataset.productViewDialog,
        );
        const editDialog = document.getElementById(
            button.dataset.productDialog,
        );

        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        button.addEventListener("click", () => {
            // Condicional: evalua "viewDialog" para decidir si ejecuta el bloque siguiente.
            if (viewDialog) {
                viewDialog.showModal();
            // Condicional alternativo: este bloque se ejecuta cuando la comprobacion anterior no se cumple.
            } else if (editDialog) {
                editDialog.showModal();
            }
        });
    });

    document
        .querySelectorAll(".productos-view-dialog")
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        .forEach((viewDialog) => {
            viewDialog
                .querySelectorAll("[data-close-product-view]")
                // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
                .forEach((button) =>
                    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
                    button.addEventListener("click", () => viewDialog.close()),
                );
            // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
            viewDialog.addEventListener("click", (event) => {
                // Condicional: evalua "event.target === viewDialog" para decidir si ejecuta el bloque siguiente.
                if (event.target === viewDialog) {
                    viewDialog.close();
                }
            });
        });

    document
        .querySelectorAll("[data-open-product-variants]")
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        .forEach((button) => {
            const variantsDialog = document.getElementById(
                button.dataset.productVariantsDialog,
            );

            // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
            button.addEventListener("click", () => {
                button.closest(".productos-view-dialog")?.close();
                variantsDialog?.showModal();
            });
        });

    document
        .querySelectorAll(".productos-variants-dialog")
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        .forEach((variantsDialog) => {
            variantsDialog
                .querySelector("[data-close-product-variants]")
                // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
                ?.addEventListener("click", () => variantsDialog.close());
            variantsDialog
                .querySelectorAll("[data-open-variant-edit]")
                // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
                .forEach((button) => {
                    const editDialog = document.getElementById(
                        button.dataset.openVariantEdit,
                    );

                    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
                    button.addEventListener("click", () =>
                        editDialog?.showModal(),
                    );
                });
            // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
            variantsDialog.addEventListener("click", (event) => {
                // Condicional: evalua "event.target === variantsDialog" para decidir si ejecuta el bloque siguiente.
                if (event.target === variantsDialog) {
                    variantsDialog.close();
                }
            });
        });

    document
        .querySelectorAll(".productos-variant-edit-dialog")
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        .forEach((editDialog) => {
            editDialog
                .querySelectorAll("[data-close-variant-edit]")
                // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
                .forEach((button) =>
                    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
                    button.addEventListener("click", () => editDialog.close()),
                );
            // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
            editDialog.addEventListener("click", (event) => {
                // Condicional: evalua "event.target === editDialog" para decidir si ejecuta el bloque siguiente.
                if (event.target === editDialog) {
                    editDialog.close();
                }
            });
        });

    const variantManagerProductId = Number(
        page.dataset.variantManagerProductId || 0,
    );
    const variantEditorId = Number(page.dataset.variantEditorId || 0);
    // Condicional: evalua "variantManagerProductId > 0" para decidir si ejecuta el bloque siguiente.
    if (variantManagerProductId > 0) {
        document
            .getElementById(`variantes-producto-${variantManagerProductId}`)
            ?.showModal();

        // Condicional: evalua "variantEditorId > 0" para decidir si ejecuta el bloque siguiente.
        if (variantEditorId > 0) {
            document
                .getElementById(`editar-variante-${variantEditorId}`)
                ?.showModal();
        }
    }

    const hasErrors = page.dataset.hasErrors === "1";
    // Condicional: evalua "hasErrors" para decidir si ejecuta el bloque siguiente.
    if (hasErrors) {
        const productErrorModalId = Number(
            page.dataset.productErrorModalId || 0,
        );
        const variantErrorModalId = Number(
            page.dataset.variantErrorModalId || 0,
        );

        // Condicional: evalua "productErrorModalId > 0" para decidir si ejecuta el bloque siguiente.
        if (productErrorModalId > 0) {
            document
                .getElementById(`variantes-producto-${productErrorModalId}`)
                ?.showModal();

            // Condicional: evalua "variantErrorModalId > 0" para decidir si ejecuta el bloque siguiente.
            if (variantErrorModalId > 0) {
                document
                    .getElementById(`editar-variante-${variantErrorModalId}`)
                    ?.showModal();
            }
        // Caso alternativo: atiende la situacion en la que ninguna condicion anterior resulto verdadera.
        } else {
            dialog.showModal();
        }
    }

    // Condicional: evalua "page.dataset.openProductModal === "1"" para decidir si ejecuta el bloque siguiente.
    if (page.dataset.openProductModal === "1") {
        dialog.showModal();
    }
});
