/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/componentes/comunes/bloqueo-envio.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

/** Evita dobles envíos y deja visible el estado de guardado en formularios administrativos. */
// Evento: escucha el evento submit y ejecuta la funcion asociada cuando ocurre.
document.addEventListener('submit', (event) => {
    const form = event.target;
    // Condicional: evalua "!(form instanceof HTMLFormElement) || form.dataset.submitLocked === '1' || form.dataset.ajaxSubmit === '1'" para decidir si ejecuta el bloque siguiente.
    if (!(form instanceof HTMLFormElement) || form.dataset.submitLocked === '1' || form.dataset.ajaxSubmit === '1') {
        return;
    }

    // Condicional: evalua "!form.closest('.modulo-seguridad-rbac, .modulo-usuarios')" para decidir si ejecuta el bloque siguiente.
    if (!form.closest('.modulo-seguridad-rbac, .modulo-usuarios')) {
        return;
    }

    form.dataset.submitLocked = '1';
    form.setAttribute('aria-busy', 'true');
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    form.querySelectorAll('button[type="submit"]').forEach((button) => {
        button.disabled = true;
        button.dataset.originalLabel = button.innerHTML;
        button.innerHTML = '<i class="bi bi-arrow-repeat" aria-hidden="true"></i> Guardando…';
    });
});