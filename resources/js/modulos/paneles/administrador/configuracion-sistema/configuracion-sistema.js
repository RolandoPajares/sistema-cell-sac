document.querySelectorAll('[data-configuracion-sistema] button[disabled]').forEach((boton) => {
    boton.setAttribute('aria-disabled', 'true');
});
