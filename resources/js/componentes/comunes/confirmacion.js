/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/componentes/comunes/confirmacion.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

/** Confirmaciones accesibles y reutilizables para acciones administrativas. */
const confirmables = [...document.querySelectorAll('[data-confirm]')];

// Condicional: evalua "confirmables.length > 0" para decidir si ejecuta el bloque siguiente.
if (confirmables.length > 0) {
    const dialog = document.createElement('dialog');
    dialog.className = 'tc-confirm-dialog';
    dialog.innerHTML = '<form method="dialog" class="tc-confirm-dialog__card"><header><span class="tc-confirm-dialog__icon"><i class="bi bi-shield-exclamation" aria-hidden="true"></i></span><div><small>Confirmar operación</small><h2>¿Deseas continuar?</h2></div></header><p class="tc-confirm-dialog__message"></p><footer><button value="cancel" class="tc-confirm-dialog__cancel">Cancelar</button><button value="confirm" class="tc-confirm-dialog__accept">Confirmar</button></footer></form>';
    document.body.append(dialog);
    const form = dialog.querySelector('form');
    const message = dialog.querySelector('.tc-confirm-dialog__message');
    let pending = null;

    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    confirmables.forEach((element) => element.addEventListener('click', (event) => {
        // Condicional: evalua "element.dataset.confirmed === '1'" para decidir si ejecuta el bloque siguiente.
        if (element.dataset.confirmed === '1') {
            delete element.dataset.confirmed;
            return;
        }

        event.preventDefault();
        pending = element;
        message.textContent = element.dataset.confirm || '¿Confirmas esta acción?';
        dialog.showModal();
    }));

    // Evento: escucha el evento submit y ejecuta la funcion asociada cuando ocurre.
    form.addEventListener('submit', (event) => {
        // Condicional: evalua "event.submitter?.value !== 'confirm'" para decidir si ejecuta el bloque siguiente.
        if (event.submitter?.value !== 'confirm') {
            pending = null;
            return;
        }

        event.preventDefault();
        const element = pending;
        pending = null;
        dialog.close();
        // Condicional: evalua "!element" para decidir si ejecuta el bloque siguiente.
        if (!element) return;
        element.dataset.confirmed = '1';
        // Condicional: evalua "element.matches('form')) element.requestSubmit(" para decidir si ejecuta el bloque siguiente.
        if (element.matches('form')) element.requestSubmit();
        // Caso alternativo: atiende la situacion en la que ninguna condicion anterior resulto verdadera.
        else element.click();
    });
}