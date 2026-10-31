// Cambia entre las vistas del panel (resumen, perfil, reportes, configuración...)
// usando el # de la dirección, por ejemplo: /mi-cuenta#perfil

const contenedorVistas = document.querySelector('[data-cliente-vistas]');

if (contenedorVistas) {
    const vistas = contenedorVistas.querySelectorAll('[data-cliente-vista]');
    const enlacesMenu = document.querySelectorAll('[data-cliente-menu-vista]');
    const nombresValidos = [...vistas].map((vista) => vista.dataset.clienteVista);

    const mostrarVista = () => {
        let nombre = window.location.hash.replace('#', '');

        // si el # no es una vista, se muestra el resumen
        if (!nombresValidos.includes(nombre)) {
            nombre = 'resumen';
        }

        vistas.forEach((vista) => {
            vista.hidden = vista.dataset.clienteVista !== nombre;
        });

        // marcar en el menú la opción de la vista actual
        enlacesMenu.forEach((enlace) => {
            const activo = enlace.dataset.clienteMenuVista === nombre;
            enlace.classList.toggle('is-active', activo);

            const grupo = enlace.closest('[data-cliente-menu-grupo]');

            if (grupo && activo) {
                grupo.classList.add('is-active', 'is-open');
                grupo.querySelector('[data-cliente-menu-boton]')?.setAttribute('aria-expanded', 'true');
                grupo.querySelector('[data-cliente-menu-submenu]').hidden = false;
            }
        });

        // quitar el resaltado de los grupos que ya no tienen nada activo
        document.querySelectorAll('[data-cliente-menu-grupo].is-active').forEach((grupo) => {
            if (!grupo.querySelector('.cliente-menu__opcion.is-active')) {
                grupo.classList.remove('is-active');
            }
        });

        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    mostrarVista();
    window.addEventListener('hashchange', mostrarVista);
}
