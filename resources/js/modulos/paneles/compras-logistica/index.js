
/* ==========================================
   CELL SAC - COMPRAS Y LOGÍSTICA
   Menú lateral desplegable
========================================== */

(() => {
    // Evitar registrar el mismo listener varias veces.
    if (window.clMenuInitialized) return;
    window.clMenuInitialized = true;

    document.addEventListener('click', (event) => {
        const toggle = event.target.closest('.cl-menu__toggle');

        if (!toggle) return;

        const menu = toggle.closest('.cl-menu');
        const currentGroup = toggle.closest('.cl-menu__group');

        if (!menu || !currentGroup) return;

        const submenu = currentGroup.querySelector(
            '.cl-menu__submenu'
        );

        if (!submenu) return;

        const willOpen = submenu.hidden;

        // Cerrar todas las categorías.
        menu.querySelectorAll('.cl-menu__group').forEach((group) => {
            const button = group.querySelector('.cl-menu__toggle');
            const content = group.querySelector('.cl-menu__submenu');

            if (!button || !content) return;

            content.hidden = true;
            button.setAttribute('aria-expanded', 'false');
            group.classList.remove('is-open');
        });

        // Abrir la categoría seleccionada.
        if (willOpen) {
            submenu.hidden = false;
            toggle.setAttribute('aria-expanded', 'true');
            currentGroup.classList.add('is-open');
        }
    });
})();
