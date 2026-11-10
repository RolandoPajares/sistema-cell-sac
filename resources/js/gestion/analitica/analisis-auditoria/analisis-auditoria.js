document.querySelectorAll('[data-analisis-auditoria]').forEach((panel) => {
    const configuracion = JSON.parse(atob(panel.dataset.aaConfig ?? 'e30='));
    const dialogo = panel.querySelector('[data-aa-dialog]');
    const detalle = panel.querySelector('[data-aa-detail-data]');

    panel.querySelectorAll('[data-aa-detail]').forEach((boton) => {
        boton.addEventListener('click', () => {
            const indice = Number(boton.dataset.aaDetail);
            const fila = configuracion.filas?.[indice];
            if (!Array.isArray(fila)) {
                return;
            }

            detalle.replaceChildren();
            configuracion.columnas.forEach((columna, posicion) => {
                const elemento = document.createElement('label');
                const etiqueta = document.createElement('span');
                const valor = document.createElement('input');
                elemento.className = 'tc-aa__dialog-field';
                valor.type = 'text';
                valor.readOnly = true;
                etiqueta.textContent = columna;
                valor.value = String(fila[posicion] ?? '—');
                elemento.append(etiqueta, valor);
                detalle.append(elemento);
            });
            dialogo.showModal();
        });
    });

    panel.querySelectorAll('[data-aa-close]').forEach((boton) => {
        boton.addEventListener('click', () => dialogo.close());
    });
});

document.querySelectorAll('[data-auditoria-operaciones]').forEach((panel) => {
    const configuracion = JSON.parse(atob(panel.dataset.auditConfig ?? 'W10='));
    const modal = panel.querySelector('[data-audit-modal]');
    const detalleContenido = panel.querySelector('[data-audit-detail-content]');
    const filas = new Map(configuracion.map((evento) => [String(evento.id), evento]));

/**
 * Ejecuta la operación renderizar utilizada por el módulo JavaScript.
 *
 * @param {*} evento - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
    const renderizar = (evento) => {
        if (!evento) {
            return;
        }

        detalleContenido.replaceChildren();
        const campos = {
            Operación: evento.accion,
            Usuario: evento.usuario,
            'Dirección IP': evento.direccion_ip,
            'Sede ID': evento.sede_id,
            Módulo: evento.modulo,
            Entidad: `${evento.entidad}${evento.entidad_id ? ` #${evento.entidad_id}` : ''}`,
            Resultado: evento.resultado,
            'Tabla afectada': evento.tabla,
            Origen: evento.origen,
            Observaciones: evento.observaciones,
            'Fecha y hora': evento.fecha,
        };
        Object.entries(campos).forEach(([etiqueta, valor]) => {
            const item = document.createElement('tr');
            const titulo = document.createElement('th');
            const contenido = document.createElement('td');
            titulo.scope = 'row';
            titulo.textContent = etiqueta;
            contenido.textContent = String(valor ?? '—');
            item.append(titulo, contenido);
            detalleContenido.append(item);
        });
        panel.querySelectorAll('[data-audit-row]').forEach((fila) => fila.classList.toggle('is-selected', fila.dataset.auditId === String(evento.id)));
        modal.showModal();
    };

    panel.querySelectorAll('[data-audit-detail]').forEach((boton) => {
        boton.addEventListener('click', () => renderizar(filas.get(String(boton.dataset.auditDetail))));
    });

    panel.querySelectorAll('[data-audit-close]').forEach((boton) => {
        boton.addEventListener('click', () => modal.close());
    });

    modal.addEventListener('click', (evento) => {
        if (evento.target === modal) {
            modal.close();
        }
    });
});
