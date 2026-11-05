/**
 * Interacción exclusiva de los selectores de fecha del dashboard administrativo.
 * Las interacciones de módulos de Gestión viven en modulos/gestion.
 */
(() => {
    const selectorFecha = document.querySelector('[data-dashboard-date]');
    const etiquetaFecha = document.querySelector('[data-dashboard-date-label]');

    if (selectorFecha) {
        const abrirSelector = () => {
            if (typeof selectorFecha.showPicker === 'function') {
                selectorFecha.showPicker();
            } else {
                selectorFecha.click();
            }
        };

        selectorFecha.closest('.boton-fecha')?.addEventListener('click', (evento) => {
            if (evento.target !== selectorFecha) {
                evento.preventDefault();
                abrirSelector();
            }
        });

        selectorFecha.addEventListener('change', () => {
            if (!selectorFecha.value || !etiquetaFecha) {
                return;
            }

            const fechaObjeto = new Date(`${selectorFecha.value}T12:00:00`);
            etiquetaFecha.textContent = fechaObjeto.toLocaleDateString('es-PE', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
            });
        });
    }

    document.querySelector('[data-dashboard-period]')?.addEventListener('change', (evento) => {
        const urlActual = new URL(window.location.href);
        urlActual.searchParams.set('months', evento.target.value);
        window.location.href = urlActual.toString();
    });
})();
