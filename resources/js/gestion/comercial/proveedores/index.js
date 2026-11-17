/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/gestion/comercial/proveedores/index.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

/**
 * Muestra el detalle del proveedor asociado a una alerta de reposición.
 */
const raizListado = document.querySelector(".modulo-listado-real");
const panelProveedor = raizListado?.querySelector("[data-provider-panel]");

// Condicional: evalua "raizListado && panelProveedor" para decidir si ejecuta el bloque siguiente.
if (raizListado && panelProveedor) {
    raizListado
        .querySelectorAll("[data-provider-detail]")
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        .forEach((botonDetalle) => {
            // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
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
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        ?.addEventListener("click", () => {
            panelProveedor.hidden = true;
        });
}

/**
 * Gestiona los diálogos del listado principal de proveedores.
 */
const paginaProveedores = document.querySelector("[data-proveedores-page]");

// Condicional: evalua "paginaProveedores" para decidir si ejecuta el bloque siguiente.
if (paginaProveedores) {
    // Funcion: actualizarCamposIdentificacion concentra una tarea reutilizable del modulo.
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

        // Condicional: evalua "naturalFields" para decidir si ejecuta el bloque siguiente.
        if (naturalFields) naturalFields.hidden = juridica;
        // Condicional: evalua "juridicaFields" para decidir si ejecuta el bloque siguiente.
        if (juridicaFields) juridicaFields.hidden = !juridica;
        // Condicional: evalua "nombres" para decidir si ejecuta el bloque siguiente.
        if (nombres) nombres.required = !juridica;
        // Condicional: evalua "apellidos" para decidir si ejecuta el bloque siguiente.
        if (apellidos) apellidos.required = !juridica;
        // Condicional: evalua "razonSocial" para decidir si ejecuta el bloque siguiente.
        if (razonSocial) razonSocial.required = juridica;

        // Funcion: sincronizarNombre concentra una tarea reutilizable del modulo.
        const sincronizarNombre = () => {
            // Condicional: evalua "!nombres && !apellidos && !razonSocial" para decidir si ejecuta el bloque siguiente.
            if (!nombres && !apellidos && !razonSocial) return;
                const nombreDerivado = juridica ? (razonSocial?.value || '') : `${nombres?.value || ''} ${apellidos?.value || ''}`.trim();
                // Condicional: evalua "nombre && nombreDerivado" para decidir si ejecuta el bloque siguiente.
                if (nombre && nombreDerivado) nombre.value = nombreDerivado;
        };

        // Condicional: evalua "tipoDocumento" para decidir si ejecuta el bloque siguiente.
        if (tipoDocumento) {
            tipoDocumento.value = juridica ? 'ruc' : 'dni';
            // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
            [...tipoDocumento.options].forEach((option) => {
                option.hidden = juridica ? option.value !== 'ruc' : option.value !== 'dni';
                option.disabled = option.hidden;
            });
        }
        // Condicional: evalua "documentoLabel" para decidir si ejecuta el bloque siguiente.
        if (documentoLabel) documentoLabel.textContent = juridica ? 'Número de identificación fiscal' : 'Número de documento';
        // Condicional: evalua "documento" para decidir si ejecuta el bloque siguiente.
        if (documento) {
            documento.placeholder = juridica ? 'Ingresar RUC' : 'Ingresar DNI';
            documento.maxLength = juridica ? 11 : 8;
        }
        // Recorrido: usa filter para procesar cada elemento y obtener o actualizar datos.
        [nombres, apellidos, razonSocial].filter(Boolean).forEach((campo) => { campo.oninput = sincronizarNombre; });
        sincronizarNombre();
    };

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    paginaProveedores.querySelectorAll('.proveedores-form').forEach((formulario) => {
        const tipoPersona = formulario.querySelector('[data-provider-person-type]');
        // Condicional: evalua "formulario.closest('#nuevo-proveedor') && tipoPersona" para decidir si ejecuta el bloque siguiente.
        if (formulario.closest('#nuevo-proveedor') && tipoPersona) {
            tipoPersona.value = 'natural';
        }
        actualizarCamposIdentificacion(formulario);
        // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
        tipoPersona?.addEventListener('change', () => actualizarCamposIdentificacion(formulario));
        // Evento: escucha el evento submit y ejecuta la funcion asociada cuando ocurre.
        formulario.addEventListener('submit', () => actualizarCamposIdentificacion(formulario));
    });

    // Funcion: abrirDialogo concentra una tarea reutilizable del modulo.
    const abrirDialogo = (dialogo) => {
        // Condicional: evalua "la expresion de control" para decidir si ejecuta el bloque siguiente.
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
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        .forEach((boton) => {
            // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
            boton.addEventListener("click", () =>
                abrirDialogo(document.querySelector("#nuevo-proveedor")),
            );
        });

    paginaProveedores
        .querySelectorAll("[data-open-supplier-edit]")
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        .forEach((boton) => {
            // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
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
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        .forEach((boton) => {
            // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
            boton.addEventListener("click", () =>
                boton.closest("dialog")?.close(),
            );
        });

    paginaProveedores
        .querySelectorAll(".proveedores-dialog")
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        .forEach((dialogo) => {
            // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
            dialogo.addEventListener("click", (event) => {
                // Condicional: evalua "event.target === dialogo" para decidir si ejecuta el bloque siguiente.
                if (event.target === dialogo) {
                    dialogo.close();
                }
            });
        });

    const dialogoInicial = paginaProveedores.dataset.openDialogOnLoad;

    // Condicional: evalua "dialogoInicial" para decidir si ejecuta el bloque siguiente.
    if (dialogoInicial) {
        abrirDialogo(document.querySelector(`#${dialogoInicial}`));
    }
}
