const menuAdministrador = document.querySelector('[data-admin-menu-desplegable]');

if (menuAdministrador && menuAdministrador.dataset.adminMenuInicializado !== 'true') {
    menuAdministrador.dataset.adminMenuInicializado = 'true';

    const grupos = [...menuAdministrador.querySelectorAll('[data-admin-menu-group]')];
    const botonesGrupo = grupos.map((grupo) => grupo.querySelector('[data-admin-menu-toggle]')).filter(Boolean);
    const recordarEstado = document.body.dataset.panelRole === 'administrador';

/**
 * Ejecuta la operación claveGrupo utilizada por el módulo JavaScript.
 *
 * @param {*} grupo - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
    const claveGrupo = (grupo) => {
        const submenu = grupo.querySelector('[data-admin-menu-submenu]');

        return submenu?.id ? `technology-cell-admin-menu:${submenu.id}` : null;
    };

/**
 * Ejecuta la operación estadoGuardado utilizada por el módulo JavaScript.
 *
 * @param {*} grupo - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
    const estadoGuardado = (grupo) => {
        const clave = claveGrupo(grupo);

        if (!recordarEstado || !clave) {
            return null;
        }

        try {
            return sessionStorage.getItem(clave);
        } catch {
            return null;
        }
    };

/**
 * Registra la información recibida y coordina la actualización de la interfaz.
 *
 * @param {*} grupo - Parámetro recibido por la función.
 *
 * @param {*} abierto - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
    const guardarEstado = (grupo, abierto) => {
        const clave = claveGrupo(grupo);

        if (!recordarEstado || !clave) {
            return;
        }

        try {
            sessionStorage.setItem(clave, abierto ? 'abierto' : 'cerrado');
        } catch {
            return;
        }
    };

/**
 * Actualiza la información visible o el estado del componente.
 *
 * @param {*} grupo - Parámetro recibido por la función.
 *
 * @param {*} abierto - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
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

        const guardado = estadoGuardado(grupo);
        actualizarGrupo(grupo, guardado === null ? boton.getAttribute('aria-expanded') === 'true' : guardado === 'abierto');

        boton.addEventListener('click', () => {
            const abierto = boton.getAttribute('aria-expanded') !== 'true';
            actualizarGrupo(grupo, abierto);
            guardarEstado(grupo, abierto);
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