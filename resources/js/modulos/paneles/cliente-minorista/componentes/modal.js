// Ventanas modales del cliente (abrir, cerrar y abrir solas si hubo error al guardar)
// Uso: <button data-cliente-modal-abrir="id-del-modal">  y  <dialog id="id-del-modal" class="cliente-modal">

document.querySelectorAll('[data-cliente-modal-abrir]').forEach((boton) => {
    boton.addEventListener('click', () => {
        document.getElementById(boton.dataset.clienteModalAbrir)?.showModal();
    });
});

document.querySelectorAll('[data-cliente-modal-cerrar]').forEach((boton) => {
    boton.addEventListener('click', () => boton.closest('dialog')?.close());
});

document.querySelectorAll('dialog.cliente-modal').forEach((modal) => {
    // se pasa al final de la página para que se vea aunque su sección esté oculta
    document.body.appendChild(modal);

    // si se hace clic en el fondo oscuro, se cierra
    modal.addEventListener('click', (evento) => {
        if (evento.target === modal) {
            modal.close();
        }
    });

    // si Laravel devolvió errores de este formulario, el modal se abre solo
    if (modal.dataset.clienteModalAuto === '1') {
        modal.showModal();
    }
});
