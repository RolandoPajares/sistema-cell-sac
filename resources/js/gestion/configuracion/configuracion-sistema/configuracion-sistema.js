document.querySelectorAll('[data-configuracion-sistema] button[disabled]').forEach((boton) => {
    boton.setAttribute('aria-disabled', 'true');
});

document.querySelectorAll('[data-cs-open-integration]').forEach((boton) => {
    boton.addEventListener('click', () => {
        const dialogo = document.querySelector('#nueva-integracion');
        if (dialogo && !dialogo.open) {
            dialogo.showModal();
        }
    });
});

document.querySelectorAll('#nueva-integracion').forEach((dialogo) => {
    dialogo.addEventListener('click', (evento) => {
        if (evento.target === dialogo) {
            dialogo.close();
        }
    });
});