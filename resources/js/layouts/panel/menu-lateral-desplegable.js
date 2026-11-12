/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/layouts/panel/menu-lateral-desplegable.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

// ==========================================
// MÓDULO: MENÚ DESPLEGABLE DE ADMINISTRACIÓN
// ==========================================
const menuAdministrador = document.querySelector('[data-admin-menu-desplegable]');

// Condicional: evalua "menuAdministrador && menuAdministrador.dataset.adminMenuInicializado !== 'true'" para decidir si ejecuta el bloque siguiente.
if (menuAdministrador && menuAdministrador.dataset.adminMenuInicializado !== 'true') {
    // Marcar el menú como inicializado para evitar duplicar eventos
    menuAdministrador.dataset.adminMenuInicializado = 'true';

    // ------------------------------------------
    // 1. CONSTANTES Y SELECTORES INICIALES
    // ------------------------------------------
    const grupos = [...menuAdministrador.querySelectorAll('[data-admin-menu-group]')];
    // Recorrido: usa map para procesar cada elemento y obtener o actualizar datos.
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
    // Funcion: claveGrupo concentra una tarea reutilizable del modulo.
    const claveGrupo = (grupo) => {
        const submenu = grupo.querySelector('[data-admin-menu-submenu]');
        return submenu?.id ? `technology-cell-admin-menu:${submenu.id}` : null;
    };

    /**
     * Recupera el estado guardado (abierto/cerrado) del sessionStorage.
     * @param {HTMLElement} grupo - Elemento contenedor del grupo de menú.
     * @returns {string|null} Estado guardado ('abierto'/'cerrado') o null.
     */
    // Funcion: estadoGuardado concentra una tarea reutilizable del modulo.
    const estadoGuardado = (grupo) => {
        const clave = claveGrupo(grupo);

        // Condicional: evalua "!recordarEstado || !clave" para decidir si ejecuta el bloque siguiente.
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
    // Funcion: guardarEstado concentra una tarea reutilizable del modulo.
    const guardarEstado = (grupo, abierto) => {
        const clave = claveGrupo(grupo);

        // Condicional: evalua "!recordarEstado || !clave" para decidir si ejecuta el bloque siguiente.
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
    // Funcion: actualizarGrupo concentra una tarea reutilizable del modulo.
    const actualizarGrupo = (grupo, abierto) => {
        const boton = grupo.querySelector('[data-admin-menu-toggle]');
        const submenu = grupo.querySelector('[data-admin-menu-submenu]');

        // Condicional: evalua "!boton || !submenu" para decidir si ejecuta el bloque siguiente.
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
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    grupos.forEach((grupo) => {
        const boton = grupo.querySelector('[data-admin-menu-toggle]');

        // Condicional: evalua "!boton" para decidir si ejecuta el bloque siguiente.
        if (!boton) {
            return;
        }

        // Restaurar estado inicial (guardado o según atributo HTML)
        const guardado = estadoGuardado(grupo);
        const estadoInicial = guardado === null 
            ? boton.getAttribute('aria-expanded') === 'true' 
            : guardado === 'abierto';

        actualizarGrupo(grupo, estadoInicial);

        // Evento: escucha una interaccion del usuario o del navegador y ejecuta la funcion asociada cuando ocurre.
        // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
        boton.addEventListener('click', () => {
            const abierto = boton.getAttribute('aria-expanded') !== 'true';
            actualizarGrupo(grupo, abierto);
            guardarEstado(grupo, abierto);
        });

        // Evento: escucha una interaccion del usuario o del navegador y ejecuta la funcion asociada cuando ocurre.
        // Evento: escucha el evento keydown y ejecuta la funcion asociada cuando ocurre.
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

            // Condicional: evalua "nextIndex === null" para decidir si ejecuta el bloque siguiente.
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

    // Condicional: evalua "linkConfiguracionAntiguo" para decidir si ejecuta el bloque siguiente.
    if (linkConfiguracionAntiguo) {
        linkConfiguracionAntiguo.hidden = true;
        linkConfiguracionAntiguo.setAttribute('aria-hidden', 'true');
    }
}