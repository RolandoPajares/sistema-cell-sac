/**
 * Muestra el detalle del proveedor asociado a una alerta de reposición.
 */
const raizListado = document.querySelector('.modulo-listado-real');
const panelProveedor = raizListado?.querySelector('[data-provider-panel]');

if (raizListado && panelProveedor) {
    raizListado.querySelectorAll('[data-provider-detail]').forEach((botonDetalle) => {
        botonDetalle.addEventListener('click', () => {
            const filaRegistro = botonDetalle.closest('[data-record]');
            let datosRegistro = {};

            try {
                datosRegistro = JSON.parse(filaRegistro?.dataset.record || '{}');
            } catch (_) {
                datosRegistro = {};
            }

            panelProveedor.querySelector('[data-p-product]').textContent = datosRegistro.producto || '—';
            panelProveedor.querySelector('[data-p-name]').textContent = datosRegistro.proveedor || 'Sin proveedor asignado';
            panelProveedor.querySelector('[data-p-email]').textContent = datosRegistro.correo_proveedor || '—';
            panelProveedor.querySelector('[data-p-phone]').textContent = datosRegistro.telefono_proveedor || '—';
            panelProveedor.hidden = false;
            panelProveedor.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
    });

    panelProveedor.querySelector('[data-close-provider]')?.addEventListener('click', () => {
        panelProveedor.hidden = true;
    });
}
