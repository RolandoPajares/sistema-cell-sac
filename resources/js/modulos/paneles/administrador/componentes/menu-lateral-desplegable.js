const menuAdministrador = document.querySelector('[data-admin-menu-desplegable]');

if (menuAdministrador && menuAdministrador.dataset.adminMenuInicializado !== 'true') {
    menuAdministrador.dataset.adminMenuInicializado = 'true';

    const grupos = [...menuAdministrador.querySelectorAll('[data-admin-menu-group]')];
    const botonesGrupo = grupos.map((grupo) => grupo.querySelector('[data-admin-menu-toggle]')).filter(Boolean);

    const actualizarGrupo = (grupo, abierto) => {
        const boton = grupo.querySelector('[data-admin-menu-toggle]');
        const submenu = grupo.querySelector('[data-admin-menu-submenu]');

        if (!boton || !submenu) {
            return;
        }

        grupo.classList.toggle('is-open', abierto);
        boton.setAttribute('aria-expanded', abierto ? 'true' : 'false');
        submenu.hidden = !abierto;
    };

    grupos.forEach((grupo) => {
        const boton = grupo.querySelector('[data-admin-menu-toggle]');

        if (!boton) {
            return;
        }

        actualizarGrupo(grupo, boton.getAttribute('aria-expanded') === 'true');

        boton.addEventListener('click', () => {
            actualizarGrupo(grupo, boton.getAttribute('aria-expanded') !== 'true');
        });

        boton.addEventListener('keydown', (event) => {
            const currentIndex = botonesGrupo.indexOf(boton);
            let nextIndex = null;

            if (event.key === 'ArrowDown') {
                nextIndex = (currentIndex + 1) % botonesGrupo.length;
            } else if (event.key === 'ArrowUp') {
                nextIndex = (currentIndex - 1 + botonesGrupo.length) % botonesGrupo.length;
            } else if (event.key === 'Home') {
                nextIndex = 0;
            } else if (event.key === 'End') {
                nextIndex = botonesGrupo.length - 1;
            }

            if (nextIndex === null) {
                return;
            }

            event.preventDefault();
            botonesGrupo[nextIndex]?.focus();
        });
    });

    const linkConfiguracionAntiguo = menuAdministrador.closest('.admin-sidebar')?.querySelector('.admin-sidebar-footer a[href*="/gestion/cuenta"]');

    if (linkConfiguracionAntiguo) {
        linkConfiguracionAntiguo.hidden = true;
        linkConfiguracionAntiguo.setAttribute('aria-hidden', 'true');
    }
}
