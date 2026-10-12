/**
 * Scripts de Interfaz y Módulos Administrativos
 * Este script maneja la interactividad del panel administrativo: selectores de
 * fecha, filtros dinámicos en tablas, exportación a CSV y paneles de proveedores.
 */

// Tablero interno: filtros de fecha y periodo de compras.
(() => {
    // Selección de elementos DOM para los filtros de fecha del tablero.
    const selectorFecha = document.querySelector("[data-dashboard-date]");
    const etiquetaFecha = document.querySelector("[data-dashboard-date-label]");

    if (selectorFecha) {
        // Función auxiliar para forzar la apertura del calendario nativo
        const abrirSelector = () => {
            if (typeof selectorFecha.showPicker === "function") {
                selectorFecha.showPicker();
            } else {
                selectorFecha.click();
            }
        };

        // Delegación de eventos para abrir el calendario al hacer clic en el contenedor visual
        const contenedorBotonFecha = selectorFecha.closest(".boton-fecha");
        contenedorBotonFecha?.addEventListener("click", (evento) => {
            if (evento.target !== selectorFecha) {
                evento.preventDefault();
                abrirSelector();
            }
        });

        // Actualización de la etiqueta de texto cuando el usuario selecciona una fecha
        selectorFecha.addEventListener("change", () => {
            if (!selectorFecha.value || !etiquetaFecha) {
                return;
            }

            const fechaObjeto = new Date(selectorFecha.value + "T12:00:00");
            etiquetaFecha.textContent = fechaObjeto.toLocaleDateString("es-PE", {
                day: "2-digit",
                month: "short",
                year: "numeric",
            });
        });
    }

    // Manejo del cambio en el selector de periodo de meses para recargar la vista
    const selectorPeriodo = document.querySelector("[data-dashboard-period]");
    selectorPeriodo?.addEventListener("change", (evento) => {
        const urlActual = new URL(window.location.href);
        urlActual.searchParams.set("months", evento.target.value);
        window.location.href = urlActual.toString();
    });
})();

// Módulos Internos: Calendario, Filtros Dinámicos y Exportación CSV
(() => {
    // Inicialización de múltiples selectores de fecha en los módulos
    const elementosFechaModulo = document.querySelectorAll("[data-module-date]");
    
    elementosFechaModulo.forEach((inputFecha) => {
        const contenedorBoton = inputFecha.closest(".boton-fecha");
        const etiquetaTexto = contenedorBoton?.querySelector("[data-module-date-label]");

        // Apertura interactiva del calendario por contenedor
        contenedorBoton?.addEventListener("click", (evento) => {
            if (evento.target !== inputFecha) {
                evento.preventDefault();
                
                if (typeof inputFecha.showPicker === "function") {
                    inputFecha.showPicker();
                } else {
                    inputFecha.click();
                }
            }
        });

        // Formateo visual de la fecha seleccionada
        inputFecha.addEventListener("change", () => {
            if (!inputFecha.value || !etiquetaTexto) {
                return;
            }

            const fechaObjeto = new Date(inputFecha.value + "T12:00:00");
            etiquetaTexto.textContent = fechaObjeto.toLocaleDateString("es-PE", {
                day: "2-digit",
                month: "short",
                year: "numeric",
            });
        });
    });

    // Referencias principales para la tabla y sus filtros de búsqueda
    const filtrosContenedor = document.querySelector("[data-module-filters]");
    const tablaFiltro = document.querySelector("[data-filter-table]");

    if (!tablaFiltro) {
        return;
    }

    const campoBusqueda = filtrosContenedor?.querySelector("[data-filter-search]");
    const selectorEstado = filtrosContenedor?.querySelector("[data-filter-status]");
    const campoFechaFiltro = filtrosContenedor?.querySelector("[data-filter-date]");
    const etiquetaFechaFiltro = filtrosContenedor?.querySelector("[data-filter-date-label]");

    // Configuración del botón de calendario específico para filtros
    const contenedorFiltroFecha = filtrosContenedor?.querySelector(".module-filter-date");
    contenedorFiltroFecha?.addEventListener("click", (evento) => {
        if (evento.target !== campoFechaFiltro) {
            evento.preventDefault();
            
            if (typeof campoFechaFiltro.showPicker === "function") {
                campoFechaFiltro.showPicker();
            } else {
                campoFechaFiltro.click();
            }
        }
    });

    campoFechaFiltro?.addEventListener("change", () => {
        if (campoFechaFiltro.value && etiquetaFechaFiltro) {
            const fechaObjeto = new Date(campoFechaFiltro.value + "T12:00:00");
            etiquetaFechaFiltro.textContent = fechaObjeto.toLocaleDateString("es-PE", {
                day: "2-digit",
                month: "short",
                year: "numeric",
            });
        }
    });

    // Algoritmo para filtrar las filas de la tabla en tiempo real
    const aplicarFiltros = () => {
        const textoBusqueda = (campoBusqueda?.value || "").trim().toLowerCase();
        const estadoSeleccionado = (selectorEstado?.value || "").toLowerCase();
        let filasVisibles = 0;

        tablaFiltro.querySelectorAll("[data-filter-row]").forEach((fila) => {
            const contenidoFila = fila.textContent.toLowerCase();
            const cumpleBusqueda = !textoBusqueda || contenidoFila.includes(textoBusqueda);
            const cumpleEstado = !estadoSeleccionado || contenidoFila.includes(estadoSeleccionado);
            const filaValida = cumpleBusqueda && cumpleEstado;

            // Oculta o muestra la fila según los criterios de búsqueda
            fila.hidden = !filaValida;
            if (filaValida) {
                filasVisibles++;
            }
        });

        // Control de visualización para el mensaje de "sin resultados"
        const mensajeVacio = tablaFiltro.querySelector("[data-filter-empty]");
        if (mensajeVacio) {
            mensajeVacio.hidden = filasVisibles !== 0;
        }
    };

    // Asociación de eventos para disparar el filtrado
    const botonAplicarFiltros = filtrosContenedor?.querySelector("[data-apply-filters]");
    botonAplicarFiltros?.addEventListener("click", aplicarFiltros);
    
    campoBusqueda?.addEventListener("input", aplicarFiltros);
    selectorEstado?.addEventListener("change", aplicarFiltros);

    // Cierre del detalle flotante de la tabla.
    const botonCerrarDetalle = tablaFiltro.querySelector("[data-close-detail]");
    botonCerrarDetalle?.addEventListener("click", () => {
        const detalleTablaFlotante = tablaFiltro.querySelector(".detalle-mockup");
        if (detalleTablaFlotante) {
            detalleTablaFlotante.hidden = true;
        }
    });

    // Lógica para exportar los datos visibles de la tabla a un archivo CSV
    const botonExportarTabla = tablaFiltro.querySelector("[data-export-table]");
    botonExportarTabla?.addEventListener("click", () => {
        const filasVisiblesCsv = [...tablaFiltro.querySelectorAll("table tr")]
            .filter((fila) => !fila.hidden)
            .map((fila) =>
                [...fila.children]
                    .map((columna) => '"' + columna.textContent.trim().replaceAll('"', '""') + '"')
                    .join(",")
            );

        // Generación y descarga automática del archivo CSV en el navegador
        const archivoCsv = new Blob([filasVisiblesCsv.join("\n")], {
            type: "text/csv;charset=utf-8" 
        });
        
        const enlaceDescarga = document.createElement("a");
        enlaceDescarga.href = URL.createObjectURL(archivoCsv);
        enlaceDescarga.download = "reporte.csv";
        enlaceDescarga.click();
        
        URL.revokeObjectURL(enlaceDescarga.href);
    });
})();

// Alertas de Reposición: Panel de Proveedor Vinculado a Productos Críticos
(() => {
    const raizListado = document.querySelector(".modulo-listado-real");
    const panelProveedor = raizListado?.querySelector("[data-provider-panel]");

    if (!raizListado || !panelProveedor) {
        return;
    }

    // Intercepta los clics en los detalles de proveedor de cada producto en stock crítico
    raizListado.querySelectorAll("[data-provider-detail]").forEach((botonDetalle) => {
        botonDetalle.addEventListener("click", () => {
            const filaRegistro = botonDetalle.closest("[data-record]");
            let datosRegistro = {};

            // Extracción segura de los datos JSON almacenados en el dataset de la fila
            try {
                datosRegistro = JSON.parse(filaRegistro?.dataset.record || "{}");
            } catch (_) {
                datosRegistro = {};
            }

            // Mapeo de los datos del proveedor hacia el panel lateral de visualización
            const elementoProducto = panelProveedor.querySelector("[data-p-product]");
            const elementoNombre = panelProveedor.querySelector("[data-p-name]");
            const elementoCorreo = panelProveedor.querySelector("[data-p-email]");
            const elementoTelefono = panelProveedor.querySelector("[data-p-phone]");

            elementoProducto.textContent = datosRegistro.producto || "—";
            elementoNombre.textContent = datosRegistro.proveedor || "Sin proveedor asignado";
            elementoCorreo.textContent = datosRegistro.correo_proveedor || "—";
            elementoTelefono.textContent = datosRegistro.telefono_proveedor || "—";

            // Muestra el panel y hace scroll automático hacia él con animación suave
            panelProveedor.hidden = false;
            panelProveedor.scrollIntoView({ 
                behavior: "smooth", 
                block: "nearest" 
            });
        });
    });

    // Botón para cerrar u ocultar el panel de información del proveedor
    const botonCerrarPanel = panelProveedor.querySelector("[data-close-provider]");
    botonCerrarPanel?.addEventListener("click", () => {
        panelProveedor.hidden = true;
    });
})();
