const dashboard = document.querySelector('.ventas-b2b-dashboard');

if (dashboard) {
    const periodButtons = dashboard.querySelectorAll('[data-ventas-periodo]');
    const periodMessage = dashboard.querySelector('[data-ventas-periodo-mensaje]');
    const messages = {
        7: 'La evolución aparecerá cuando se registren pedidos.',
        30: 'Los movimientos del último mes aparecerán cuando existan pedidos.',
        90: 'La tendencia trimestral aparecerá cuando haya historial comercial.',
    };

    periodButtons.forEach((button) => {
        button.addEventListener('click', () => {
            periodButtons.forEach((periodButton) => {
                const isActive = periodButton === button;

                periodButton.classList.toggle('is-active', isActive);
                periodButton.setAttribute('aria-pressed', String(isActive));
            });

            if (periodMessage) {
                periodMessage.textContent = messages[button.dataset.ventasPeriodo] ?? messages[7];
            }
        });
    });
}
