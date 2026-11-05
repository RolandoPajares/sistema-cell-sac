const inicializarEncabezado = () => {
    const botonMenu = document.querySelector('.menu-toggle');
    const navegacion = document.querySelector('.navlinks');

    if (!botonMenu || !navegacion) {
        return;
    }

    botonMenu.addEventListener('click', (evento) => {
        const abierta = navegacion.classList.toggle('open');

        evento.currentTarget.setAttribute('aria-expanded', abierta ? 'true' : 'false');
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', inicializarEncabezado, { once: true });
} else {
    inicializarEncabezado();
}
