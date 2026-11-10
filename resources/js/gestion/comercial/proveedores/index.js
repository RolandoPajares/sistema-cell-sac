/**
 * Muestra el detalle del proveedor asociado a una alerta de reposición.
 */
const raizListado = document.querySelector(".modulo-listado-real");
const panelProveedor = raizListado?.querySelector("[data-provider-panel]");

if (raizListado && panelProveedor) {
    raizListado
        .querySelectorAll("[data-provider-detail]")
        .forEach((botonDetalle) => {
            botonDetalle.addEventListener("click", () => {
                const filaRegistro = botonDetalle.closest("[data-record]");
                let datosRegistro = {};

                try {
                    datosRegistro = JSON.parse(
                        filaRegistro?.dataset.record || "{}",
                    );
                } catch (_) {
                    datosRegistro = {};
                }

                panelProveedor.querySelector("[data-p-product]").textContent =
                    datosRegistro.producto || "—";
                panelProveedor.querySelector("[data-p-name]").textContent =
                    datosRegistro.proveedor || "Sin proveedor asignado";
                panelProveedor.querySelector("[data-p-email]").textContent =
                    datosRegistro.correo_proveedor || "—";
                panelProveedor.querySelector("[data-p-phone]").textContent =
                    datosRegistro.telefono_proveedor || "—";
                panelProveedor.hidden = false;
                panelProveedor.scrollIntoView({
                    behavior: "smooth",
                    block: "nearest",
                });
            });
        });

    panelProveedor
        .querySelector("[data-close-provider]")
        ?.addEventListener("click", () => {
            panelProveedor.hidden = true;
        });
}

/**
 * Gestiona los diálogos del listado principal de proveedores.
 */
const paginaProveedores = document.querySelector("[data-proveedores-page]");

if (paginaProveedores) {
    const abrirDialogo = (dialogo) => {
        if (
            dialogo &&
            !dialogo.open &&
            typeof dialogo.showModal === "function"
        ) {
            dialogo.showModal();
        }
    };

    paginaProveedores
        .querySelectorAll("[data-open-supplier-create]")
        .forEach((boton) => {
            boton.addEventListener("click", () =>
                abrirDialogo(document.querySelector("#nuevo-proveedor")),
            );
        });

    paginaProveedores
        .querySelectorAll("[data-open-supplier-edit]")
        .forEach((boton) => {
            boton.addEventListener("click", () =>
                abrirDialogo(
                    document.querySelector(
                        `#editar-proveedor-${boton.dataset.openSupplierEdit}`,
                    ),
                ),
            );
        });

    paginaProveedores
        .querySelectorAll("[data-close-supplier-dialog]")
        .forEach((boton) => {
            boton.addEventListener("click", () =>
                boton.closest("dialog")?.close(),
            );
        });

    paginaProveedores
        .querySelectorAll(".proveedores-dialog")
        .forEach((dialogo) => {
            dialogo.addEventListener("click", (event) => {
                if (event.target === dialogo) {
                    dialogo.close();
                }
            });
        });

    const dialogoInicial = paginaProveedores.dataset.openDialogOnLoad;

    if (dialogoInicial) {
        abrirDialogo(document.querySelector(`#${dialogoInicial}`));
    }
}
