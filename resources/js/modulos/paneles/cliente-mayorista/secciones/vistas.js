// Cambia entre las vistas del panel (resumen, perfil, reportes, configuración...)
// usando el # de la dirección. Ejemplos: /mi-cuenta#perfil o /mi-cuenta#perfil/seguridad
// Lo que va después de la "/" es una parte de esa vista (se baja hasta ella).

const contenedorVistas = document.querySelector('[data-cliente-vistas]');

if (contenedorVistas) {
    const vistas = contenedorVistas.querySelectorAll('[data-cliente-vista]');
    const enlacesMenu = document.querySelectorAll('[data-cliente-menu-vista]');
    const nombresValidos = [...vistas].map((vista) => vista.dataset.clienteVista);

    const mostrarVista = () => {
        const ancla = decodeURIComponent(window.location.hash.replace('#', ''));
        let [nombre, parte] = ancla.split('/');

        // si el # no es una vista, se muestra el resumen
        if (!nombresValidos.includes(nombre)) {
            nombre = 'resumen';
            parte = '';
        }

        let vistaActual = null;

        vistas.forEach((vista) => {
            vista.hidden = vista.dataset.clienteVista !== nombre;

            if (!vista.hidden) {
                vistaActual = vista;
            }
        });

        // en el menú se marca la opción exacta (ej. "perfil/seguridad"); si no hay, la de la vista
        const claveMenu = parte ? nombre + '/' + parte : nombre;
        const hayExacta = [...enlacesMenu].some((enlace) => enlace.dataset.clienteMenuVista === claveMenu);

        enlacesMenu.forEach((enlace) => {
            const activo = enlace.dataset.clienteMenuVista === (hayExacta ? claveMenu : nombre);
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

        // ir a la parte pedida: si es un botón (un filtro) se presiona, si no se baja hasta ella.
        // sin parte, si la vista tiene filtro "todas" se vuelve a mostrar todo
        const destino = vistaActual?.querySelector('[data-cliente-ancla="' + (parte || 'todas') + '"]');

        if (destino && destino.matches('button')) {
            destino.click();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } else if (destino && parte) {
            destino.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    };

    mostrarVista();
    window.addEventListener('hashchange', mostrarVista);
}
