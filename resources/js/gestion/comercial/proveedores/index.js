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
    const actualizarCamposIdentificacion = (formulario) => {
        const tipoPersona = formulario.querySelector('[data-provider-person-type]')?.value;
        const juridica = tipoPersona !== 'natural';
        const tipoDocumento = formulario.querySelector('[data-provider-document-type]');
        const documentoLabel = formulario.querySelector('[data-provider-document-type-label]');
        const documento = formulario.querySelector('[data-provider-document-input]');
        const naturalFields = formulario.querySelector('[data-provider-natural-fields]');
        const juridicaFields = formulario.querySelector('[data-provider-juridica-fields]');
        const nombre = formulario.querySelector('[data-provider-name-input]');
        const nombres = formulario.querySelector('[data-provider-first-name]');
        const apellidos = formulario.querySelector('[data-provider-last-name]');
        const razonSocial = formulario.querySelector('[data-provider-legal-name]');

        if (naturalFields) naturalFields.hidden = juridica;
        if (juridicaFields) juridicaFields.hidden = !juridica;
        if (nombres) nombres.required = !juridica;
        if (apellidos) apellidos.required = !juridica;
        if (razonSocial) razonSocial.required = juridica;

        const sincronizarNombre = () => {
            if (!nombres && !apellidos && !razonSocial) return;
                const nombreDerivado = juridica ? (razonSocial?.value || '') : `${nombres?.value || ''} ${apellidos?.value || ''}`.trim();
                if (nombre && nombreDerivado) nombre.value = nombreDerivado;
        };

        if (tipoDocumento) {
            tipoDocumento.value = juridica ? 'ruc' : 'dni';
            [...tipoDocumento.options].forEach((option) => {
                option.hidden = juridica ? option.value !== 'ruc' : option.value !== 'dni';
                option.disabled = option.hidden;
            });
        }
        if (documentoLabel) documentoLabel.textContent = juridica ? 'Número de identificación fiscal' : 'Número de documento';
        if (documento) {
            documento.placeholder = juridica ? 'Ingresar RUC' : 'Ingresar DNI';
            documento.maxLength = juridica ? 11 : 8;
        }
        [nombres, apellidos, razonSocial].filter(Boolean).forEach((campo) => { campo.oninput = sincronizarNombre; });
        sincronizarNombre();
    };

    paginaProveedores.querySelectorAll('.proveedores-form').forEach((formulario) => {
        const tipoPersona = formulario.querySelector('[data-provider-person-type]');
        if (formulario.closest('#nuevo-proveedor') && tipoPersona) {
            tipoPersona.value = 'natural';
        }
        actualizarCamposIdentificacion(formulario);
        tipoPersona?.addEventListener('change', () => actualizarCamposIdentificacion(formulario));
        formulario.addEventListener('submit', () => actualizarCamposIdentificacion(formulario));
    });

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
