// El catálogo ya recibe del servidor los datos consultados por el controlador.
(() => {
    const formularioOrden = document.querySelector("[data-catalog-sort]");
    formularioOrden?.addEventListener("change", () =>
        formularioOrden.form?.requestSubmit()
    );

    const elementoModal = document.querySelector("[data-catalog-modal]");
    if (!elementoModal || typeof elementoModal.showModal !== "function") {
        return;
    }

    const imagenPrincipalModal = elementoModal.querySelector("[data-modal-main-image]");
    const marcadorImagenModal = elementoModal.querySelector("[data-modal-image-placeholder]");
    const miniaturasModal = [...elementoModal.querySelectorAll("[data-modal-thumb]")];
    const botonZoomModal = elementoModal.querySelector("[data-modal-zoom]");
    
    let indiceImagenSeleccionada = 0;
    const imagenesDisponiblesModal = miniaturasModal
        .map((miniatura) => miniatura.dataset.src || "")
        .filter((rutaImagen) => rutaImagen !== "");

    const mostrarImagenModal = (indice) => {
        if (!imagenesDisponiblesModal.length || !imagenPrincipalModal) {
            return;
        }

        indiceImagenSeleccionada = (indice + imagenesDisponiblesModal.length) % imagenesDisponiblesModal.length;
        imagenPrincipalModal.src = imagenesDisponiblesModal[indiceImagenSeleccionada];
        imagenPrincipalModal.hidden = false;
        
        if (marcadorImagenModal) {
            marcadorImagenModal.hidden = true;
        }
        
        botonZoomModal?.classList.remove("is-zoomed");

        miniaturasModal.forEach((miniatura, indiceMiniatura) => {
            const estaSeleccionada = indiceMiniatura === indiceImagenSeleccionada;
            miniatura.classList.toggle("is-active", estaSeleccionada);
            miniatura.setAttribute(
                "aria-current",
                estaSeleccionada ? "true" : "false"
            );
        });
    };

    miniaturasModal.forEach((miniatura, indice) => {
        miniatura.addEventListener("click", () => mostrarImagenModal(indice));
    });

    imagenPrincipalModal?.addEventListener(
        "error",
        () => {
            imagenPrincipalModal.hidden = true;
            
            if (marcadorImagenModal) {
                marcadorImagenModal.hidden = false;
            }
            
            if (miniaturasModal.length === 0) {
                return;
            }
            
            miniaturasModal[0].hidden = true;
        },
        { once: true }
    );

    botonZoomModal?.addEventListener("click", () => {
        if (imagenPrincipalModal && !imagenPrincipalModal.hidden) {
            botonZoomModal.classList.toggle("is-zoomed");
        }
    });

    // Elimina el parámetro del producto al cerrar para que una recarga no vuelva a abrir el detalle.
    const quitarProductoDeLaDireccion = () => {
        const direccionUrl = new URL(window.location.href);
        
        if (!direccionUrl.searchParams.has("producto")) {
            return;
        }
        
        direccionUrl.searchParams.delete("producto");
        window.history.replaceState(window.history.state, "", direccionUrl);
    };

    elementoModal.querySelectorAll("[data-modal-close]").forEach((botonCerrar) => {
        botonCerrar.addEventListener("click", () => elementoModal.close());
    });
    
    elementoModal.addEventListener("click", (evento) => {
        if (evento.target === elementoModal) {
            elementoModal.close();
        }
    });
    
    elementoModal.addEventListener("close", quitarProductoDeLaDireccion);

    if (imagenesDisponiblesModal.length) {
        mostrarImagenModal(0);
    }

    // El atributo open permite mostrar una alternativa en línea si el navegador bloquea scripts.
    if (elementoModal.hasAttribute("open")) {
        elementoModal.removeAttribute("open");
        elementoModal.showModal();
    }
})();
