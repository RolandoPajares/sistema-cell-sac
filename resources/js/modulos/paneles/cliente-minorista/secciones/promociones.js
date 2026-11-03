// Botón "Copiar" de los cupones del cliente minorista

document.querySelectorAll('[data-copiar-cupon]').forEach((boton) => {
    boton.addEventListener('click', async () => {
        const codigo = boton.dataset.copiarCupon;

        try {
            await navigator.clipboard.writeText(codigo);
            boton.innerHTML = '<i class="bi bi-check2"></i> Copiado';
        } catch (error) {
            // si el navegador no deja copiar, se muestra el código
            boton.textContent = codigo;
        }

        setTimeout(() => {
            boton.innerHTML = '<i class="bi bi-clipboard"></i> Copiar';
        }, 2000);
    });
});
