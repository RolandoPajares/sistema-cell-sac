/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/gestion/ecommerce-marketing/previsualizacion-campanas.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

/**
 * Actualiza la vista previa de una campaña desde sus datos HTML.
 */
// Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
document.querySelectorAll("[data-campaign-preview-select]").forEach((boton) =>
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    boton.addEventListener("click", () => {
        const objetivosTexto = [
            ["[data-campaign-preview-name]", "previewName"],
            ["[data-campaign-preview-description]", "previewDescription"],
            ["[data-campaign-preview-location]", "previewLocation"],
            ["[data-campaign-preview-status]", "previewStatus"],
        ];

        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        objetivosTexto.forEach(([selector, clave]) => {
            const objetivo = document.querySelector(selector);

            // Condicional: evalua "objetivo" para decidir si ejecuta el bloque siguiente.
            if (objetivo) {
                objetivo.textContent = boton.dataset[clave] || "—";
            }
        });

        const fechaInicioCampania = document.querySelector("[data-campaign-preview-start]");
        const fechaFinCampania = document.querySelector("[data-campaign-preview-end]");

        // Condicional: evalua "fechaInicioCampania" para decidir si ejecuta el bloque siguiente.
        if (fechaInicioCampania) {
            fechaInicioCampania.textContent = boton.dataset.previewStart || "—";
            fechaInicioCampania.dateTime = boton.dataset.previewStartIso || "";
        }

        // Condicional: evalua "fechaFinCampania" para decidir si ejecuta el bloque siguiente.
        if (fechaFinCampania) {
            fechaFinCampania.textContent = boton.dataset.previewEnd || "—";
            fechaFinCampania.dateTime = boton.dataset.previewEndIso || "";
        }

        const elementoEstado = document.querySelector("[data-campaign-preview-status]");

        // Condicional: evalua "elementoEstado" para decidir si ejecuta el bloque siguiente.
        if (elementoEstado) {
            // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
            ["active", "scheduled", "finished", "inactive"].forEach((estado) =>
                elementoEstado.classList.remove(`admin-campaign-status--${estado}`)
            );

            elementoEstado.classList.add(
                `admin-campaign-status--${boton.dataset.previewStatusKey || "inactive"}`
            );
        }

        const imagen = document.querySelector("[data-campaign-preview-image]");
        const marcador = document.querySelector("[data-campaign-image-placeholder]");

        // Condicional: evalua "imagen" para decidir si ejecuta el bloque siguiente.
        if (imagen) {
            const fuente = boton.dataset.previewImage || "";
            imagen.hidden = fuente === "";

            // Condicional: evalua "fuente !== """ para decidir si ejecuta el bloque siguiente.
            if (fuente !== "") {
                imagen.src = fuente;
                imagen.alt = `Imagen de ${boton.dataset.previewTitle || boton.dataset.previewName || "la campaña"}`;
            // Caso alternativo: atiende la situacion en la que ninguna condicion anterior resulto verdadera.
            } else {
                imagen.removeAttribute("src");
            }

            // Condicional: evalua "marcador" para decidir si ejecuta el bloque siguiente.
            if (marcador) {
                marcador.hidden = fuente !== "";
            }
        }

        const enlaceEdicion = document.querySelector("[data-campaign-edit-link]");

        // Condicional: evalua "enlaceEdicion && boton.dataset.previewEdit" para decidir si ejecuta el bloque siguiente.
        if (enlaceEdicion && boton.dataset.previewEdit) {
            enlaceEdicion.href = boton.dataset.previewEdit;
        }
    })
);