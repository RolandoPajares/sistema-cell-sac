// Botón "Copiar" de los cupones en Beneficios mayoristas

document.querySelectorAll('.benef-cliente [data-copiar-cupon]').forEach((boton) => {
    boton.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(boton.dataset.copiarCupon);
            boton.innerHTML = '<i class="bi bi-check2"></i> Copiado';
        } catch (error) {
            // si el navegador no deja copiar, se muestra el código
            boton.textContent = boton.dataset.copiarCupon;
        }

        setTimeout(() => {
            boton.innerHTML = '<i class="bi bi-clipboard"></i> Copiar';
        }, 2000);
    });
});
