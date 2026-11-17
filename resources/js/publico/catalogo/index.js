/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/publico/catalogo/index.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

// El catálogo ya recibe del servidor los datos consultados por el controlador.
(() => {
    const formularioOrden = document.querySelector("[data-catalog-sort]");
    // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
    formularioOrden?.addEventListener("change", () =>
        formularioOrden.form?.requestSubmit()
    );

    const elementoModal = document.querySelector("[data-catalog-modal]");
    // Condicional: evalua "!elementoModal || typeof elementoModal.showModal !== "function"" para decidir si ejecuta el bloque siguiente.
    if (!elementoModal || typeof elementoModal.showModal !== "function") {
        return;
    }

    const imagenPrincipalModal = elementoModal.querySelector("[data-modal-main-image]");
    const marcadorImagenModal = elementoModal.querySelector("[data-modal-image-placeholder]");
    const miniaturasModal = [...elementoModal.querySelectorAll("[data-modal-thumb]")];
    const botonZoomModal = elementoModal.querySelector("[data-modal-zoom]");
    
    let indiceImagenSeleccionada = 0;
    const imagenesDisponiblesModal = miniaturasModal
        // Recorrido: usa map para procesar cada elemento y obtener o actualizar datos.
        .map((miniatura) => miniatura.dataset.src || "")
        // Recorrido: usa filter para procesar cada elemento y obtener o actualizar datos.
        .filter((rutaImagen) => rutaImagen !== "");

/**
 * Ejecuta la operación mostrarImagenModal utilizada por el módulo JavaScript.
 *
 * @param {*} indice - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
    // Funcion: mostrarImagenModal concentra una tarea reutilizable del modulo.
    const mostrarImagenModal = (indice) => {
        // Condicional: evalua "!imagenesDisponiblesModal.length || !imagenPrincipalModal" para decidir si ejecuta el bloque siguiente.
        if (!imagenesDisponiblesModal.length || !imagenPrincipalModal) {
            return;
        }

        indiceImagenSeleccionada = (indice + imagenesDisponiblesModal.length) % imagenesDisponiblesModal.length;
        imagenPrincipalModal.src = imagenesDisponiblesModal[indiceImagenSeleccionada];
        imagenPrincipalModal.hidden = false;
        
        // Condicional: evalua "marcadorImagenModal" para decidir si ejecuta el bloque siguiente.
        if (marcadorImagenModal) {
            marcadorImagenModal.hidden = true;
        }
        
        botonZoomModal?.classList.remove("is-zoomed");

        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        miniaturasModal.forEach((miniatura, indiceMiniatura) => {
            const estaSeleccionada = indiceMiniatura === indiceImagenSeleccionada;
            miniatura.classList.toggle("is-active", estaSeleccionada);
            miniatura.setAttribute(
                "aria-current",
                estaSeleccionada ? "true" : "false"
            );
        });
    };

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    miniaturasModal.forEach((miniatura, indice) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        miniatura.addEventListener("click", () => mostrarImagenModal(indice));
    });

    // Evento: escucha una interaccion del usuario o del navegador y ejecuta la funcion asociada cuando ocurre.
    imagenPrincipalModal?.addEventListener(
        "error",
        () => {
            imagenPrincipalModal.hidden = true;
            
            // Condicional: evalua "marcadorImagenModal" para decidir si ejecuta el bloque siguiente.
            if (marcadorImagenModal) {
                marcadorImagenModal.hidden = false;
            }
            
            // Condicional: evalua "miniaturasModal.length === 0" para decidir si ejecuta el bloque siguiente.
            if (miniaturasModal.length === 0) {
                return;
            }
            
            miniaturasModal[0].hidden = true;
        },
        { once: true }
    );

    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    botonZoomModal?.addEventListener("click", () => {
        // Condicional: evalua "imagenPrincipalModal && !imagenPrincipalModal.hidden" para decidir si ejecuta el bloque siguiente.
        if (imagenPrincipalModal && !imagenPrincipalModal.hidden) {
            botonZoomModal.classList.toggle("is-zoomed");
        }
    });

    // Elimina el parámetro del producto al cerrar para que una recarga no vuelva a abrir el detalle.
/**
 * Ejecuta la operación quitarProductoDeLaDireccion utilizada por el módulo JavaScript.
 *
 * @returns {*} Resultado de la operación.
 */
    // Funcion: quitarProductoDeLaDireccion concentra una tarea reutilizable del modulo.
    const quitarProductoDeLaDireccion = () => {
        const direccionUrl = new URL(window.location.href);
        
        // Condicional: evalua "!direccionUrl.searchParams.has("producto")" para decidir si ejecuta el bloque siguiente.
        if (!direccionUrl.searchParams.has("producto")) {
            return;
        }
        
        direccionUrl.searchParams.delete("producto");
        window.history.replaceState(window.history.state, "", direccionUrl);
    };

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    elementoModal.querySelectorAll("[data-modal-close]").forEach((botonCerrar) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        botonCerrar.addEventListener("click", () => elementoModal.close());
    });
    
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    elementoModal.addEventListener("click", (evento) => {
        // Condicional: evalua "evento.target === elementoModal" para decidir si ejecuta el bloque siguiente.
        if (evento.target === elementoModal) {
            elementoModal.close();
        }
    });
    
    // Evento: escucha el evento close y ejecuta la funcion asociada cuando ocurre.
    elementoModal.addEventListener("close", quitarProductoDeLaDireccion);

    // Condicional: evalua "imagenesDisponiblesModal.length" para decidir si ejecuta el bloque siguiente.
    if (imagenesDisponiblesModal.length) {
        mostrarImagenModal(0);
    }

    // El atributo open permite mostrar una alternativa en línea si el navegador bloquea scripts.
    // Condicional: evalua "elementoModal.hasAttribute("open")" para decidir si ejecuta el bloque siguiente.
    if (elementoModal.hasAttribute("open")) {
        elementoModal.removeAttribute("open");
        elementoModal.showModal();
    }
})();