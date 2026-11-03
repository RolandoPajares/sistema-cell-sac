// Modal de solicitud de postventa (revisión de garantía o devolución)
// Antes de enviar se juntan tipo, producto y motivo dentro del mensaje

const formSolicitud = document.querySelector('[data-form-solicitud]');

if (formSolicitud) {
    const campo = (nombre) => formSolicitud.querySelector(`[data-solicitud-campo="${nombre}"]`);

    // al abrir desde un producto, se elige ese producto y el tipo de solicitud
    document.querySelectorAll('[data-cliente-modal-abrir="modal-solicitud"]').forEach((boton) => {
        boton.addEventListener('click', () => {
            if (boton.dataset.solicitudTipo) {
                campo('tipo').value = boton.dataset.solicitudTipo;
            }

            if (boton.dataset.solicitudProducto !== undefined) {
                campo('producto').selectedIndex = Number(boton.dataset.solicitudProducto);
            }
        });
    });

    formSolicitud.addEventListener('submit', () => {
        const mensaje = formSolicitud.querySelector('textarea[name="mensaje"]');
        const encabezado = `[${campo('tipo').value}] Producto: ${campo('producto').value}. Motivo: ${campo('motivo').value}.`;

        // se agrega el encabezado solo una vez
        if (!mensaje.value.startsWith('[')) {
            mensaje.value = encabezado + '\n' + mensaje.value;
        }
    });
}
