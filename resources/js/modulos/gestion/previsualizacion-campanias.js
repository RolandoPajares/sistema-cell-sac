/**
 * Actualiza la vista previa de una campaña desde sus datos HTML.
 */
document.querySelectorAll("[data-campaign-preview-select]").forEach((boton) =>
    boton.addEventListener("click", () => {
        const objetivosTexto = [
            ["[data-campaign-preview-name]", "previewName"],
            ["[data-campaign-preview-description]", "previewDescription"],
            ["[data-campaign-preview-location]", "previewLocation"],
            ["[data-campaign-preview-status]", "previewStatus"],
        ];

        objetivosTexto.forEach(([selector, clave]) => {
            const objetivo = document.querySelector(selector);

            if (objetivo) {
                objetivo.textContent = boton.dataset[clave] || "—";
            }
        });

        const fechaInicioCampania = document.querySelector("[data-campaign-preview-start]");
        const fechaFinCampania = document.querySelector("[data-campaign-preview-end]");

        if (fechaInicioCampania) {
            fechaInicioCampania.textContent = boton.dataset.previewStart || "—";
            fechaInicioCampania.dateTime = boton.dataset.previewStartIso || "";
        }

        if (fechaFinCampania) {
            fechaFinCampania.textContent = boton.dataset.previewEnd || "—";
            fechaFinCampania.dateTime = boton.dataset.previewEndIso || "";
        }

        const elementoEstado = document.querySelector("[data-campaign-preview-status]");

        if (elementoEstado) {
            ["active", "scheduled", "finished", "inactive"].forEach((estado) =>
                elementoEstado.classList.remove(`admin-campaign-status--${estado}`)
            );

            elementoEstado.classList.add(
                `admin-campaign-status--${boton.dataset.previewStatusKey || "inactive"}`
            );
        }

        const imagen = document.querySelector("[data-campaign-preview-image]");
        const marcador = document.querySelector("[data-campaign-image-placeholder]");

        if (imagen) {
            const fuente = boton.dataset.previewImage || "";
            imagen.hidden = fuente === "";

            if (fuente !== "") {
                imagen.src = fuente;
                imagen.alt = `Imagen de ${boton.dataset.previewTitle || boton.dataset.previewName || "la campaña"}`;
            } else {
                imagen.removeAttribute("src");
            }

            if (marcador) {
                marcador.hidden = fuente !== "";
            }
        }

        const enlaceEdicion = document.querySelector("[data-campaign-edit-link]");

        if (enlaceEdicion && boton.dataset.previewEdit) {
            enlaceEdicion.href = boton.dataset.previewEdit;
        }
    })
);
