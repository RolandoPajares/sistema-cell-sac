/** Confirmaciones accesibles y reutilizables para acciones administrativas. */
const confirmables = [...document.querySelectorAll('[data-confirm]')];

if (confirmables.length > 0) {
    const dialog = document.createElement('dialog');
    dialog.className = 'tc-confirm-dialog';
    dialog.innerHTML = '<form method="dialog" class="tc-confirm-dialog__card"><header><span class="tc-confirm-dialog__icon"><i class="bi bi-shield-exclamation" aria-hidden="true"></i></span><div><small>Confirmar operación</small><h2>¿Deseas continuar?</h2></div></header><p class="tc-confirm-dialog__message"></p><footer><button value="cancel" class="tc-confirm-dialog__cancel">Cancelar</button><button value="confirm" class="tc-confirm-dialog__accept">Confirmar</button></footer></form>';
    document.body.append(dialog);
    const form = dialog.querySelector('form');
    const message = dialog.querySelector('.tc-confirm-dialog__message');
    let pending = null;

    confirmables.forEach((element) => element.addEventListener('click', (event) => {
        if (element.dataset.confirmed === '1') {
            delete element.dataset.confirmed;
            return;
        }

        event.preventDefault();
        pending = element;
        message.textContent = element.dataset.confirm || '¿Confirmas esta acción?';
        dialog.showModal();
    }));

    form.addEventListener('submit', (event) => {
        if (event.submitter?.value !== 'confirm') {
            pending = null;
            return;
        }

        event.preventDefault();
        const element = pending;
        pending = null;
        dialog.close();
        if (!element) return;
        element.dataset.confirmed = '1';
        if (element.matches('form')) element.requestSubmit();
        else element.click();
    });
}
