// Pestañas "Mis datos" y "Seguridad" del panel del cliente minorista

const panelMinorista = document.querySelector('.minorista-panel');

if (panelMinorista) {
    const pestanas = panelMinorista.querySelectorAll('[data-pestana-minorista]');
    const contenidos = panelMinorista.querySelectorAll('[data-contenido-minorista]');

    const mostrarPestana = (nombre) => {
        pestanas.forEach((pestana) => {
            pestana.classList.toggle('activo', pestana.dataset.pestanaMinorista === nombre);
        });

        contenidos.forEach((contenido) => {
            contenido.hidden = contenido.dataset.contenidoMinorista !== nombre;
        });
    };

    pestanas.forEach((pestana) => {
        pestana.addEventListener('click', () => mostrarPestana(pestana.dataset.pestanaMinorista));
    });
}
