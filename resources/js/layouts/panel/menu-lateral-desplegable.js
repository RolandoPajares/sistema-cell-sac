// ==========================================
// MÓDULO: MENÚ DESPLEGABLE DE ADMINISTRACIÓN
// ==========================================
const menuAdministrador = document.querySelector('[data-admin-menu-desplegable]');

if (menuAdministrador && menuAdministrador.dataset.adminMenuInicializado !== 'true') {
    // Marcar el menú como inicializado para evitar duplicar eventos
    menuAdministrador.dataset.adminMenuInicializado = 'true';

    // ------------------------------------------
    // 1. CONSTANTES Y SELECTORES INICIALES
    // ------------------------------------------
    const grupos = [...menuAdministrador.querySelectorAll('[data-admin-menu-group]')];
    const botonesGrupo = grupos.map((grupo) => grupo.querySelector('[data-admin-menu-toggle]')).filter(Boolean);
    const recordarEstado = document.body.dataset.panelRole === 'administrador';

    // ------------------------------------------
    // 2. FUNCIONES AUXILIARES (ESTADO Y CLAVES)
    // ------------------------------------------

    /**
     * Genera una clave única en sessionStorage basada en el ID del submenú.
     * @param {HTMLElement} grupo - Elemento contenedor del grupo de menú.
     * @returns {string|null} Clave única o null si no posee ID.
     */
    const claveGrupo = (grupo) => {
        const submenu = grupo.querySelector('[data-admin-menu-submenu]');
        return submenu?.id ? `technology-cell-admin-menu:${submenu.id}` : null;
    };

    /**
     * Recupera el estado guardado (abierto/cerrado) del sessionStorage.
     * @param {HTMLElement} grupo - Elemento contenedor del grupo de menú.
     * @returns {string|null} Estado guardado ('abierto'/'cerrado') o null.
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
     * Guarda el estado actual del grupo en sessionStorage.
     * @param {HTMLElement} grupo - Elemento contenedor del grupo de menú.
     * @param {boolean} abierto - Indica si el menú está abierto.
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

    // ------------------------------------------
    // 3. MANIPULACIÓN DE LA INTERFAZ (DOM)
    // ------------------------------------------

    /**
     * Actualiza las clases, atributos ARIA y visibilidad del submenú.
     * @param {HTMLElement} grupo - Elemento contenedor del grupo de menú.
     * @param {boolean} abierto - Indica si debe mostrarse abierto.
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

    // ------------------------------------------
    // 4. CONFIGURACIÓN E INICIALIZACIÓN DE EVENTOS
    // ------------------------------------------
    grupos.forEach((grupo) => {
        const boton = grupo.querySelector('[data-admin-menu-toggle]');

        if (!boton) {
            return;
        }

        // Restaurar estado inicial (guardado o según atributo HTML)
        const guardado = estadoGuardado(grupo);
        const estadoInicial = guardado === null 
            ? boton.getAttribute('aria-expanded') === 'true' 
            : guardado === 'abierto';

        actualizarGrupo(grupo, estadoInicial);

        // Evento: Clic para desplegar / contraer
        boton.addEventListener('click', () => {
            const abierto = boton.getAttribute('aria-expanded') !== 'true';
            actualizarGrupo(grupo, abierto);
            guardarEstado(grupo, abierto);
        });

        // Evento: Navegación por teclado (Accesibilidad con flechas, Home y End)
        boton.addEventListener('keydown', (event) => {
            const currentIndex = botonesGrupo.indexOf(boton);
            let nextIndex = null;

            switch (event.key) {
                case 'ArrowDown':
                    nextIndex = (currentIndex + 1) % botonesGrupo.length;
                    break;
                case 'ArrowUp':
                    nextIndex = (currentIndex - 1 + botonesGrupo.length) % botonesGrupo.length;
                    break;
                case 'Home':
                    nextIndex = 0;
                    break;
                case 'End':
                    nextIndex = botonesGrupo.length - 1;
                    break;
            }

            if (nextIndex === null) {
                return;
            }

            event.preventDefault();
            botonesGrupo[nextIndex]?.focus();
        });
    });

    // ------------------------------------------
    // 5. LIMPIEZA DE ELEMENTOS ANTIGUOS
    // ------------------------------------------
    const linkConfiguracionAntiguo = menuAdministrador
        .closest('.admin-sidebar')
        ?.querySelector('.admin-sidebar-footer a[href*="/gestion/cuenta"]');

    if (linkConfiguracionAntiguo) {
        linkConfiguracionAntiguo.hidden = true;
        linkConfiguracionAntiguo.setAttribute('aria-hidden', 'true');
    }
}