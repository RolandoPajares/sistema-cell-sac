const dashboard = document.querySelector('.ventas-b2c-dashboard');

if (dashboard && dashboard.dataset.ventasB2cInicializado !== 'true') {
    dashboard.dataset.ventasB2cInicializado = 'true';

    const periodButtons = dashboard.querySelectorAll('[data-ventas-b2c-periodo]');
    const periodMessage = dashboard.querySelector('[data-ventas-b2c-mensaje]');
    const messages = {
        7: 'La actividad aparecerá cuando se registren pedidos B2C.',
        30: 'El resumen mensual aparecerá cuando existan ventas minoristas.',
        90: 'La tendencia trimestral aparecerá cuando haya historial de ventas.',
    };

    periodButtons.forEach((button) => {
        button.addEventListener('click', () => {
            periodButtons.forEach((periodButton) => {
                const isActive = periodButton === button;

                periodButton.classList.toggle('is-active', isActive);
                periodButton.setAttribute('aria-pressed', String(isActive));
            });

            if (periodMessage) {
                periodMessage.textContent = messages[button.dataset.ventasB2cPeriodo] ?? messages[7];
            }
        });
    });
}
