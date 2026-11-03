// Menú lateral desplegable del cliente (abre y cierra los grupos)
// Se basa en el menú del administrador para que todos se vean igual

const menuCliente = document.querySelector('[data-cliente-menu]');

if (menuCliente && menuCliente.dataset.iniciado !== 'true') {
    menuCliente.dataset.iniciado = 'true';

    const grupos = menuCliente.querySelectorAll('[data-cliente-menu-grupo]');

    // abre o cierra un grupo
    const cambiarGrupo = (grupo, abrir) => {
        const boton = grupo.querySelector('[data-cliente-menu-boton]');
        const submenu = grupo.querySelector('[data-cliente-menu-submenu]');

        grupo.classList.toggle('is-open', abrir);
        boton.setAttribute('aria-expanded', abrir ? 'true' : 'false');
        submenu.hidden = !abrir;
    };

    grupos.forEach((grupo) => {
        const boton = grupo.querySelector('[data-cliente-menu-boton]');

        boton.addEventListener('click', () => {
            const estaAbierto = boton.getAttribute('aria-expanded') === 'true';
            cambiarGrupo(grupo, !estaAbierto);
        });
    });
}
