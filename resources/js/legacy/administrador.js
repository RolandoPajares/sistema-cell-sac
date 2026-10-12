/**
 * Script principal del panel de administración.
 * Controla la barra lateral, los filtros globales y locales de tablas,
 * la paginación, la vista previa de campañas, los diálogos modales, el asistente virtual y la visibilidad de contraseñas.
 */
(() => {
    const aplicacion = document.querySelector("[data-admin-app]");
    const barraLateral = document.getElementById("adminSidebar");
    const fondo = document.querySelector("[data-admin-sidebar-close]");
    const botonAlternar = document.querySelector("[data-admin-sidebar-toggle]");
    
    // Función para determinar si la vista es móvil según el ancho de la pantalla
    const esVistaMovil = () => matchMedia("(max-width: 800px)").matches;

    // Función para cerrar la barra lateral en vista móvil
    const cerrarBarraLateralMovil = () => {
        barraLateral?.classList.remove("is-open");
        fondo?.classList.remove("is-visible");
        botonAlternar?.setAttribute("aria-expanded", "false");
    };
 
    // Evento para alternar la barra lateral
    botonAlternar?.addEventListener("click", () => {
        if (esVistaMovil()) {
            const estaAbierta = barraLateral?.classList.toggle("is-open") ?? false;
            fondo?.classList.toggle("is-visible", estaAbierta);
            botonAlternar.setAttribute("aria-expanded", String(estaAbierta));
            return;
        }
        // Alternar la clase de colapso de la barra lateral en vista de escritorio
        const barraContraida = aplicacion?.classList.toggle("is-sidebar-collapsed") ?? false;
        // Guardar el estado de la barra lateral en localStorage para persistencia
        localStorage.setItem(
            "md-admin-sidebar",
            barraContraida ? "collapsed" : "expanded"
        );
        // Actualizar el atributo aria-expanded del botón de alternar
        botonAlternar.setAttribute("aria-expanded", String(!barraContraida));
    });

    // Evento para cerrar la barra lateral al hacer clic en el fondo
    fondo?.addEventListener("click", cerrarBarraLateralMovil);
    // Cerrar la barra lateral al hacer clic en cualquier enlace dentro de ella
    barraLateral?.querySelectorAll("a").forEach((enlace) => 
        enlace.addEventListener("click", cerrarBarraLateralMovil)
    );

    // Restaurar el estado de la barra lateral desde localStorage al cargar la página
    if (!esVistaMovil() && localStorage.getItem("md-admin-sidebar") === "collapsed") {
        aplicacion?.classList.add("is-sidebar-collapsed");
        botonAlternar?.setAttribute("aria-expanded", "false");
    }

    // Agregar confirmación a elementos con el atributo data-confirm
    document.querySelectorAll("[data-confirm]").forEach((elemento) => {
        elemento.addEventListener("click", (evento) => {
            const mensajeConfirmacion = elemento.dataset.confirm || "¿Confirmas esta acción?";
            
            // Mostrar un cuadro de confirmación y prevenir la acción si el usuario cancela
            if (!confirm(mensajeConfirmacion)) {
                evento.preventDefault();
            }
        });
    });

    // Función para filtrar filas de tablas según criterios globales y locales
    const filtrarFilas = () => {
        const terminoGlobal = (
            document.querySelector("[data-admin-global-search]")?.value || ""
        )
            .trim()
            .toLocaleLowerCase("es");

        // Iterar sobre cada fila de la tabla y aplicar los filtros correspondientes
        document.querySelectorAll("[data-admin-table] tbody tr[data-data-row]").forEach((fila) => {
            const contenedor = fila.closest("[data-admin-table-container]");
            
            // Obtener referencias a los campos de búsqueda y filtros locales
            const campoBusquedaLocal = contenedor?.querySelector("[data-table-search]");
            const filtroCategoria = contenedor?.querySelector("[data-product-category-filter]");
            const filtroEstado = contenedor?.querySelector("[data-product-state-filter]");
            const filtroEstadoCategoria = contenedor?.querySelector("[data-category-status-filter]");
            const filtroCategoriaInventario = contenedor?.querySelector("[data-inventory-category-filter]");
            const filtroEstadoInventario = contenedor?.querySelector("[data-inventory-state-filter]");
            const filtroPeriodoInventario = contenedor?.querySelector("[data-inventory-period-filter]");
            const filtroEstadoPedido = contenedor?.querySelector("[data-order-status-filter]");
            const filtroPeriodoPedido = contenedor?.querySelector("[data-order-period-filter]");
            const filtroTipoCliente = contenedor?.querySelector("[data-client-type-filter]");
            const filtroEstadoCliente = contenedor?.querySelector("[data-client-state-filter]");
            const filtroEstadoProveedor = contenedor?.querySelector("[data-supplier-state-filter]");
            const filtroCiudadProveedor = contenedor?.querySelector("[data-supplier-city-filter]");
            const filtroEstadoCampania = contenedor?.querySelector("[data-campaign-status-filter]");
            const filtroUbicacionCampania = contenedor?.querySelector("[data-campaign-location-filter]");
            const filtroPeriodoCampania = contenedor?.querySelector("[data-campaign-period-filter]");

            // Obtener los valores de los filtros y criterios de búsqueda, normalizados a minúsculas
            const textoLocal = (campoBusquedaLocal?.value || "").trim().toLocaleLowerCase("es");
            const textoFila = fila.textContent.toLocaleLowerCase("es");
            const categoriaProducto = (filtroCategoria?.value || "").toLocaleLowerCase("es");
            const estadoProducto = filtroEstado?.value || "";
            const estadoCategoria = filtroEstadoCategoria?.value || "";
            const categoriaInventario = (filtroCategoriaInventario?.value || "").toLocaleLowerCase("es");
            const estadoInventario = filtroEstadoInventario?.value || "";
            const periodoInventario = filtroPeriodoInventario?.value || "all";
            const antiguedadMovimiento = Math.floor(Date.now() / 1000) - Number(fila.dataset.inventoryTime || 0);
            
            // Obtener los valores de los filtros de pedidos y calcular la antigüedad del pedido
            const periodoPedido = filtroPeriodoPedido?.value || "all";
            const antiguedadPedido = Math.floor(Date.now() / 1000) - Number(fila.dataset.orderTime || 0);
            
            // Obtener los valores de los filtros de clientes, proveedores y campañas
            const tipoCliente = filtroTipoCliente?.value || "";
            const estadoCliente = filtroEstadoCliente?.value || "";
            const estadoProveedor = filtroEstadoProveedor?.value || "";
            const ciudadProveedor = filtroCiudadProveedor?.value || "";
            const estadoCampania = filtroEstadoCampania?.value || "";
            const ubicacionCampania = filtroUbicacionCampania?.value || "";
            const periodoCampania = filtroPeriodoCampania?.value || "all";
            const antiguedadCampania = Math.floor(Date.now() / 1000) - Number(fila.dataset.campaignTime || 0);

            // Determinar si la fila debe estar oculta según los criterios de búsqueda y filtros aplicados
            fila.hidden =
                (terminoGlobal !== "" && !textoFila.includes(terminoGlobal)) ||
                (textoLocal !== "" && !textoFila.includes(textoLocal)) ||
                (categoriaProducto !== "" && (fila.dataset.productCategory || "").toLocaleLowerCase("es") !== categoriaProducto) ||
                (estadoProducto !== "" && fila.dataset.productState !== estadoProducto) ||
                (estadoCategoria !== "" && fila.dataset.categoryStatus !== estadoCategoria) ||
                (categoriaInventario !== "" && (fila.dataset.inventoryCategory || "").toLocaleLowerCase("es") !== categoriaInventario) ||
                (estadoInventario !== "" && fila.dataset.inventoryState !== estadoInventario) ||
                (filtroPeriodoInventario && periodoInventario !== "all" && (antiguedadMovimiento < 0 || antiguedadMovimiento > Number(periodoInventario) * 86400)) ||
                (filtroEstadoPedido && filtroEstadoPedido.value !== "" && fila.dataset.orderStatus !== filtroEstadoPedido.value) ||
                (filtroPeriodoPedido && periodoPedido !== "all" && (antiguedadPedido < 0 || antiguedadPedido > Number(periodoPedido) * 86400)) ||
                (tipoCliente !== "" && fila.dataset.clientType !== tipoCliente) ||
                (estadoCliente !== "" && fila.dataset.clientState !== estadoCliente) ||
                (estadoProveedor !== "" && fila.dataset.supplierState !== estadoProveedor) ||
                (ciudadProveedor !== "" && fila.dataset.supplierCity !== ciudadProveedor) ||
                (estadoCampania !== "" && fila.dataset.campaignStatus !== estadoCampania) ||
                (ubicacionCampania !== "" && fila.dataset.campaignLocation !== ubicacionCampania) ||
                (filtroPeriodoCampania && periodoCampania !== "all" && (antiguedadCampania < 0 || antiguedadCampania > Number(periodoCampania) * 86400));
        });

        // Actualizar la visibilidad de los mensajes de estado vacío y ordenar las filas según el criterio seleccionado
        document.querySelectorAll("[data-admin-table-container]").forEach((contenedor) => {
            const estadoVacio = contenedor.querySelector(
                "[data-product-filter-empty], [data-category-filter-empty], [data-inventory-filter-empty], [data-orders-filter-empty], [data-client-filter-empty], [data-supplier-filter-empty], [data-campaign-filter-empty]"
            );
            
            if (!estadoVacio) return;

            // Obtener todas las filas visibles de la tabla y determinar si el mensaje de estado vacío debe mostrarse
            const filasTabla = [...contenedor.querySelectorAll("[data-admin-table] tbody tr[data-data-row]")];
            estadoVacio.hidden = filasTabla.length === 0 || filasTabla.some((fila) => !fila.hidden);

            const criterioOrden = contenedor.querySelector("[data-category-order-filter]")?.value;
            
            // Ordenar las filas de la tabla según el criterio seleccionado (nombre ascendente o fecha de creación descendente)
            if (criterioOrden) {
                const cuerpoTabla = contenedor.querySelector("[data-admin-table] tbody");
                const filasOrdenables = [...(cuerpoTabla?.querySelectorAll("tr[data-data-row]") || [])];
                
                // Ordenar las filas según el criterio seleccionado
                filasOrdenables.sort((a, b) =>
                    criterioOrden === "name-asc"
                        ? (a.dataset.categoryName || "").localeCompare(b.dataset.categoryName || "", "es", { sensitivity: "base" })
                        : (b.dataset.categoryCreated || "").localeCompare(a.dataset.categoryCreated || "")
                );
                
                filasOrdenables.forEach((fila) => cuerpoTabla.appendChild(fila));
            }
        });

        // Despachar un evento personalizado para indicar que las filas de la tabla han sido filtradas
        document.dispatchEvent(new CustomEvent("admin:table-filtered"));
    };

    // Agregar eventos de entrada y cambio a los campos de búsqueda y filtros para activar la función de filtrado
    document.querySelector("[data-admin-global-search]")?.addEventListener("input", filtrarFilas);
    
    // Agregar eventos de entrada a los campos de búsqueda locales y eventos de cambio a los filtros para activar la función de filtrado
    document.querySelectorAll("[data-table-search]").forEach((campo) => 
        campo.addEventListener("input", filtrarFilas)
    );
    
    // Agregar eventos de cambio a los filtros de productos, categorías, inventario, pedidos, clientes, proveedores y campañas para activar la función de filtrado
    document.querySelectorAll("[data-product-category-filter], [data-product-state-filter]").forEach((filtro) => 
        filtro.addEventListener("change", filtrarFilas)
    );
    
    // Agregar eventos de cambio a los filtros de categorías, inventario, pedidos, clientes, proveedores y campañas para activar la función de filtrado
    document.querySelectorAll(
        "[data-category-status-filter], [data-category-order-filter], [data-inventory-category-filter], [data-inventory-state-filter], [data-inventory-period-filter], [data-order-status-filter], [data-order-period-filter], [data-client-type-filter], [data-client-state-filter], [data-supplier-state-filter], [data-supplier-city-filter], [data-campaign-status-filter], [data-campaign-location-filter], [data-campaign-period-filter]"
    ).forEach((filtro) => filtro.addEventListener("change", filtrarFilas));

    // Ejecutar la función de filtrado al cargar la página si existen filtros aplicables
    if (document.querySelector("[data-category-order-filter], [data-inventory-period-filter], [data-campaign-period-filter]")) {
        filtrarFilas();
    }

    // Agregar eventos de clic a los botones de vista previa de campañas para actualizar la información en el panel de vista previa
    document.querySelectorAll("[data-campaign-preview-select]").forEach((boton) =>
        boton.addEventListener("click", () => {
            const objetivosTexto = [
                ["[data-campaign-preview-name]", "previewName"],
                ["[data-campaign-preview-description]", "previewDescription"],
                ["[data-campaign-preview-location]", "previewLocation"],
                ["[data-campaign-preview-status]", "previewStatus"],
            ];

            // Actualizar el contenido de los elementos de vista previa con los datos del botón seleccionado
            objetivosTexto.forEach(([selector, clave]) => {
                const objetivo = document.querySelector(selector);
                if (objetivo) objetivo.textContent = boton.dataset[clave] || "—";
            });

            // Actualizar las fechas de inicio y fin de la campaña en el panel de vista previa
            const fechaInicioCampania = document.querySelector("[data-campaign-preview-start]");
            const fechaFinCampania = document.querySelector("[data-campaign-preview-end]");
            
            // Actualizar el contenido y el atributo dateTime de los elementos de fecha de inicio y fin
            if (fechaInicioCampania) {
                fechaInicioCampania.textContent = boton.dataset.previewStart || "—";
                fechaInicioCampania.dateTime = boton.dataset.previewStartIso || "";
            }
            // Actualizar el contenido y el atributo dateTime de los elementos de fecha de inicio y fin
            if (fechaFinCampania) {
                fechaFinCampania.textContent = boton.dataset.previewEnd || "—";
                fechaFinCampania.dateTime = boton.dataset.previewEndIso || "";
            }

            // Actualizar el estado de la campaña en el panel de vista previa
            const elementoEstado = document.querySelector("[data-campaign-preview-status]");
            
            // Remover todas las clases de estado existentes y agregar la clase correspondiente al estado actual de la campaña
            if (elementoEstado) {
                ["active", "scheduled", "finished", "inactive"].forEach((estado) =>
                    elementoEstado.classList.remove(`admin-campaign-status--${estado}`)
                );
                
                elementoEstado.classList.add(
                    `admin-campaign-status--${boton.dataset.previewStatusKey || "inactive"}`
                );
            }

            // Actualizar la imagen de vista previa de la campaña y el marcador de posición según la fuente proporcionada
            const imagen = document.querySelector("[data-campaign-preview-image]");
            const marcador = document.querySelector("[data-campaign-image-placeholder]");
            
            // Mostrar u ocultar la imagen de vista previa y el marcador según la fuente proporcionada
            if (imagen) {
                const fuente = boton.dataset.previewImage || "";
                imagen.hidden = fuente === "";
                
                // Actualizar la fuente y el texto alternativo de la imagen de vista previa si se proporciona una fuente válida
                if (fuente !== "") {
                    imagen.src = fuente;
                    imagen.alt = `Imagen de ${boton.dataset.previewTitle || boton.dataset.previewName || "la campaña"}`;
                } else {
                    imagen.removeAttribute("src");
                }
                
                // Mostrar u ocultar el marcador de posición según la fuente proporcionada
                if (marcador) marcador.hidden = fuente !== "";
            }

            // Actualizar el enlace de edición de la campaña en el panel de vista previa si se proporciona un enlace válido
            const enlaceEdicion = document.querySelector("[data-campaign-edit-link]");
            if (enlaceEdicion && boton.dataset.previewEdit) {
                enlaceEdicion.href = boton.dataset.previewEdit;
            }
        })
    );

    // Agregar eventos de clic a los botones que abren diálogos modales para mostrar el diálogo correspondiente
    document.querySelectorAll("[data-admin-dialog-open]").forEach((boton) => {
        boton.addEventListener("click", () =>
            document.getElementById(boton.dataset.adminDialogOpen)?.showModal()
        );
    });

    // Agregar eventos de clic a los botones que muestran detalles de auditoría para actualizar el contenido del panel de detalles
    document.querySelectorAll("[data-audit-id]").forEach((boton) =>
        boton.addEventListener("click", () => {
            const valores = {
                id: `#${boton.dataset.auditId || "—"}`,
                date: boton.dataset.auditDate || "—",
                user: boton.dataset.auditUser || "—",
                action: boton.dataset.auditAction || "—",
                entity: boton.dataset.auditEntity || "—",
                ip: boton.dataset.auditIp || "—",
            };

            // Actualizar el contenido de los elementos de detalles de auditoría con los valores correspondientes
            Object.entries(valores).forEach(([clave, valor]) => {
                const objetivo = document.querySelector(`[data-audit-detail="${clave}"]`);
                if (objetivo) objetivo.textContent = valor;
            });

            // Actualizar el contenido de los elementos de detalles de auditoría con los valores antiguos y nuevos, si están disponibles
            ["old", "new"].forEach((clave) => {
                const objetivo = document.querySelector(`[data-audit-detail="${clave}"]`);
                if (!objetivo) return;
                
                try {
                    const parseado = JSON.parse(
                        boton.dataset[`audit${clave[0].toUpperCase()}${clave.slice(1)}`] || "{}"
                    );
                    
                    objetivo.textContent = parseado && Object.keys(parseado).length
                        ? JSON.stringify(parseado, null, 2)
                        : "Sin datos";
                } catch (_) {
                    objetivo.textContent = "Sin datos disponibles";
                }
            });
        })
    );

    // Agregar eventos de clic a los botones que cierran diálogos modales para cerrar el diálogo correspondiente
    document.querySelectorAll("[data-admin-dialog-close]").forEach((boton) => {
        boton.addEventListener("click", () => boton.closest("dialog")?.close());
    });

    // Abrir automáticamente los diálogos modales que tienen el atributo data-auto-open establecido en '1'
    document.querySelectorAll("dialog[data-auto-open='1']").forEach((dialogo) => 
        dialogo.showModal()
    );

    // Agregar eventos de clic a los diálogos modales para cerrarlos al hacer clic fuera del contenido del diálogo
    document.querySelectorAll("dialog").forEach((dialogo) =>
        dialogo.addEventListener("click", (evento) => {
            if (evento.target === dialogo) dialogo.close();
        })
    );

    // Agregar eventos de clic a los botones de paginación para controlar la visualización de filas en tablas con paginación
    document.querySelectorAll("[data-admin-table]").forEach((tabla) => {
        const filas = [...tabla.querySelectorAll("tbody tr[data-data-row]")];
        const contenedor = tabla.closest("[data-admin-table-container]");
        const paginador = contenedor?.querySelector("[data-admin-pagination]");
        
        if (!paginador || filas.length <= 10) return;
        
        let paginaActual = 1;
        const porPagina = 10;

        // Función para ordenar las filas según el criterio seleccionado en el filtro de orden
        const filasOrdenadas = () => {
            const orden = contenedor?.querySelector("[data-category-order-filter]")?.value;
            if (!orden) return filas;
            
            return [...filas].sort((a, b) =>
                orden === "name-asc"
                    ? (a.dataset.categoryName || "").localeCompare(b.dataset.categoryName || "", "es", { sensitivity: "base" })
                    : (b.dataset.categoryCreated || "").localeCompare(a.dataset.categoryCreated || "")
            );
        };

        // Función para renderizar la paginación y mostrar las filas correspondientes a la página actual
        const renderizarPaginacion = () => {
            const filasVisibles = filasOrdenadas().filter((fila) => !fila.hidden);
            const totalPaginas = Math.max(1, Math.ceil(filasVisibles.length / porPagina));
            
            paginaActual = Math.min(paginaActual, totalPaginas);

            filas.forEach((fila) => {
                if (!fila.hidden) fila.style.display = "none";
            });

            // Mostrar solo las filas correspondientes a la página actual
            filasVisibles
                .slice((paginaActual - 1) * porPagina, paginaActual * porPagina)
                .forEach((fila) => {
                    fila.style.display = "";
                });

                // Actualizar el contenido del paginador con la información de la página actual y los botones de navegación
            paginador.innerHTML = `
                <span>Mostrando ${filasVisibles.length ? (paginaActual - 1) * porPagina + 1 : 0}-${Math.min(paginaActual * porPagina, filasVisibles.length)} de ${filasVisibles.length}</span>
                <div>
                    <button type="button" data-prev aria-label="Página anterior">‹</button>
                    <b>${paginaActual} / ${totalPaginas}</b>
                    <button type="button" data-next aria-label="Página siguiente">›</button>
                </div>
            `;

            const botonAnterior = paginador.querySelector("[data-prev]");
            const botonSiguiente = paginador.querySelector("[data-next]");

            // Configurar los botones de navegación para actualizar la página actual y renderizar la paginación nuevamente
            if (botonAnterior) {
                botonAnterior.disabled = paginaActual === 1;
                botonAnterior.onclick = () => {
                    paginaActual -= 1;
                    renderizarPaginacion();
                };
            }

            // Configurar los botones de navegación para actualizar la página actual y renderizar la paginación nuevamente
            if (botonSiguiente) {
                botonSiguiente.disabled = paginaActual === totalPaginas;
                botonSiguiente.onclick = () => {
                    paginaActual += 1;
                    renderizarPaginacion();
                };
            }
        };

        // Escuchar el evento personalizado "admin:table-filtered" para reiniciar la página actual y renderizar la paginación nuevamente
        document.addEventListener("admin:table-filtered", () => {
            paginaActual = 1;
            renderizarPaginacion();
        });

        renderizarPaginacion();
    });

    const asistente = document.querySelector("[data-admin-assistant]");
    
    if (asistente) {
        const panel = asistente.querySelector("[data-admin-assistant-panel]");
        const mensajes = asistente.querySelector("[data-admin-assistant-messages]");
        const formulario = asistente.querySelector("[data-admin-assistant-form]");
        const campoEntrada = formulario?.querySelector("input");

        const cambiarEstadoPanel = (abierto) => {
            panel.hidden = !abierto;
        };

        asistente.querySelector("[data-admin-assistant-toggle]")?.addEventListener("click", () => 
            cambiarEstadoPanel(panel.hidden)
        );

        asistente.querySelector("[data-admin-assistant-close]")?.addEventListener("click", () => 
            cambiarEstadoPanel(false)
        );

        // Función para agregar un mensaje al panel del asistente virtual
        const agregarMensaje = (textoMensaje, esPropio = false) => {
            const articulo = document.createElement("article");
            articulo.className = esPropio ? "is-user" : "";
            
            const icono = esPropio ? "bi-person" : "bi-robot";
            const parrafoSeguro = document.createElement("p");
            parrafoSeguro.textContent = textoMensaje;
            
            articulo.innerHTML = `<span><i class="bi ${icono}"></i></span>`;
            articulo.appendChild(parrafoSeguro);
            
            mensajes.appendChild(articulo);
            mensajes.scrollTop = mensajes.scrollHeight;
        };

        const consultarAsistente = async (pregunta) => {
            const consultaLimpia = pregunta.trim();
            if (!consultaLimpia) return;

            cambiarEstadoPanel(true);
            agregarMensaje(consultaLimpia, true);
            
            campoEntrada.value = "";
            campoEntrada.disabled = true;

            try {
                const respuestaServidor = await fetch(asistente.dataset.endpoint, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8",
                        Accept: "application/json",
                    },
                    body: new URLSearchParams({
                        consulta: consultaLimpia,
                        _token: document.querySelector('meta[name="csrf-token"]')?.content || "",
                    }),
                });

                const datosRespuesta = await respuestaServidor.json();
                
                agregarMensaje(datosRespuesta.ok ? datosRespuesta.respuesta.mensaje : datosRespuesta.mensaje);
            } catch (_) {
                agregarMensaje("No fue posible consultar la información en este momento.");
            } finally {
                campoEntrada.disabled = false;
                campoEntrada.focus();
            }
        };

        formulario?.addEventListener("submit", (evento) => {
            evento.preventDefault();
            consultarAsistente(campoEntrada.value);
        });

        asistente.querySelectorAll("[data-admin-assistant-prompt]").forEach((boton) =>
            boton.addEventListener("click", () =>
                consultarAsistente(boton.dataset.adminAssistantPrompt || "")
            )
        );
    }

    document.querySelectorAll("[data-password-toggle]").forEach((boton) => {
        const campoContrasena = boton.closest(".admin-password-input")?.querySelector("input");
        
        if (!campoContrasena) return;

        boton.addEventListener("click", () => {
            const mostrarTexto = campoContrasena.type === "password";
            
            campoContrasena.type = mostrarTexto ? "text" : "password";
            
            boton.setAttribute(
                "aria-label",
                mostrarTexto ? "Ocultar contraseña" : "Mostrar contraseña"
            );
            
            boton.innerHTML = `<i class="bi ${mostrarTexto ? "bi-eye-slash" : "bi-eye"}" aria-hidden="true"></i>`;
        });
    });
})();
