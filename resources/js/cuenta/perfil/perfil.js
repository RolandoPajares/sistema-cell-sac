/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/cuenta/perfil/perfil.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

import "./componentes/foto-perfil.js";
import "./componentes/campos-dinamicos.js";

// Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
document.querySelectorAll("[data-profile-page]").forEach((page) => {
    const tabs = page.querySelectorAll("[data-profile-tab]");
    const panels = page.querySelectorAll("[data-profile-panel]");

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    tabs.forEach((tab) => {
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        tab.addEventListener("click", () => {
            const selected = tab.dataset.profileTab;
            // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
            tabs.forEach((item) =>
                item.classList.toggle("is-active", item === tab),
            );
            // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
            panels.forEach((panel) => {
                panel.hidden = panel.dataset.profilePanel !== selected;
                panel.classList.toggle(
                    "is-active",
                    panel.dataset.profilePanel === selected,
                );
            });
        });
    });
});
