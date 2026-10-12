/**
 * Scripts para la Galería de Detalle y el Modal del Catálogo
 * Maneja la interactividad de las imágenes de productos, navegación por miniaturas,
 * zoom, flechas de desplazamiento y la gestión de apertura y cierre del modal.
 */

// Galería del detalle individual; usa únicamente las imágenes registradas en el producto.
(() => {
    const raizGaleria = document.querySelector("[data-product-detail]");
    
    if (!raizGaleria) {
        return;
    }

    const imagenPrincipal = raizGaleria.querySelector("[data-main-product-image]");
    const marcadorPosicion = raizGaleria.querySelector("[data-image-placeholder]");
    const cajaMiniaturas = raizGaleria.querySelector("[data-gallery-thumbs]");
    
    let miniaturasVisibles = [...raizGaleria.querySelectorAll(".gallery-thumb")].filter(
        (boton) => !boton.hidden
    );

    const establecerImagenPrincipal = (fuenteImagen, botonMiniatura) => {
        if (fuenteImagen && imagenPrincipal) {
            imagenPrincipal.src = fuenteImagen;
            imagenPrincipal.hidden = false;
            
            if (marcadorPosicion) {
                marcadorPosicion.hidden = true;
            }
        }

        raizGaleria
            .querySelectorAll(".gallery-thumb")
            .forEach((elementoMiniatura) => elementoMiniatura.classList.remove("active"));
            
        if (botonMiniatura) {
            botonMiniatura.classList.add("active");
        }
    };

    const actualizarFlechasNavegacion = () => {
        const mostrarFlechas = miniaturasVisibles.length > 1;
        const botonAnterior = raizGaleria.querySelector("[data-gallery-prev]");
        const botonSiguiente = raizGaleria.querySelector("[data-gallery-next]");
        
        if (botonAnterior) {
            botonAnterior.hidden = !mostrarFlechas;
        }
        
        if (botonSiguiente) {
            botonSiguiente.hidden = !mostrarFlechas;
        }
    };

    cajaMiniaturas?.addEventListener("click", (evento) => {
        const miniaturaObjetivo = evento.target.closest(".gallery-thumb");
        
        if (miniaturaObjetivo && !miniaturaObjetivo.hidden) {
            evento.preventDefault();
            
            miniaturasVisibles = [...raizGaleria.querySelectorAll(".gallery-thumb")].filter(
                (boton) => !boton.hidden
            );
            
            establecerImagenPrincipal(miniaturaObjetivo.dataset.src, miniaturaObjetivo);
        }
    });

    const avanzarPasoGaleria = (desplazamiento) => {
        if (!miniaturasVisibles.length) {
            return;
        }

        let indiceActual = miniaturasVisibles.findIndex((elemento) => elemento.classList.contains("active"));
        indiceActual = (indiceActual + desplazamiento + miniaturasVisibles.length) % miniaturasVisibles.length;
        
        establecerImagenPrincipal(
            miniaturasVisibles[indiceActual].dataset.src, 
            miniaturasVisibles[indiceActual]
        );
    };

    raizGaleria.querySelector("[data-gallery-prev]")?.addEventListener("click", (evento) => {
        evento.preventDefault();
        avanzarPasoGaleria(-1);
    });

    raizGaleria.querySelector("[data-gallery-next]")?.addEventListener("click", (evento) => {
        evento.preventDefault();
        avanzarPasoGaleria(1);
    });

    const ocultarMiniaturaNoDisponible = (imagenMiniatura) => {
        imagenMiniatura.closest(".gallery-thumb")?.remove();
        
        miniaturasVisibles = [...raizGaleria.querySelectorAll(".gallery-thumb")].filter(
            (boton) => !boton.hidden
        );
        
        actualizarFlechasNavegacion();
    };

    cajaMiniaturas?.querySelectorAll(".gallery-thumb img").forEach((imagenMiniatura) => {
        imagenMiniatura.addEventListener("error", () => ocultarMiniaturaNoDisponible(imagenMiniatura), {
            once: true,
        });
        
        if (imagenMiniatura.complete && imagenMiniatura.naturalWidth === 0) {
            ocultarMiniaturaNoDisponible(imagenMiniatura);
        }
    });

    const mostrarMarcadorPosicion = () => {
        if (imagenPrincipal) {
            imagenPrincipal.hidden = true;
        }
        
        if (marcadorPosicion) {
            marcadorPosicion.hidden = false;
        }
        
        if (cajaMiniaturas) {
            cajaMiniaturas.hidden = true;
        }
    };

    imagenPrincipal?.addEventListener("error", mostrarMarcadorPosicion, { once: true });
    
    if (imagenPrincipal?.getAttribute("src") && imagenPrincipal.complete && imagenPrincipal.naturalWidth === 0) {
        mostrarMarcadorPosicion();
    }

    if (miniaturasVisibles.length && imagenPrincipal) {
        establecerImagenPrincipal(miniaturasVisibles[0].dataset.src, miniaturasVisibles[0]);
    } else if (imagenPrincipal) {
        imagenPrincipal.hidden = true;
    }

    if (!miniaturasVisibles.length && marcadorPosicion) {
        marcadorPosicion.hidden = false;
    }
    
    actualizarFlechasNavegacion();
})();

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
