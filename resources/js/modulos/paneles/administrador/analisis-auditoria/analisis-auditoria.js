document.querySelectorAll('[data-analisis-auditoria]').forEach((panel) => {
    const configuracion = JSON.parse(panel.nextElementSibling?.textContent ?? '{}');
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
    const configuracion = JSON.parse(panel.querySelector('[data-audit-config]')?.textContent ?? '[]');
    const detalleVacio = panel.querySelector('[data-audit-detail-empty]');
    const detalleContenido = panel.querySelector('[data-audit-detail-content]');
    const filas = new Map(configuracion.map((evento) => [String(evento.id), evento]));

    const renderizar = (evento) => {
        if (!evento) {
            return;
        }

        detalleContenido.replaceChildren();
        const campos = {
            Operación: evento.accion,
            Usuario: evento.usuario,
            Módulo: evento.modulo,
            Entidad: `${evento.entidad}${evento.entidad_id ? ` #${evento.entidad_id}` : ''}`,
            Resultado: evento.resultado,
            'Dirección IP': evento.direccion_ip,
            'Sede ID': evento.sede_id,
            'Tabla afectada': evento.tabla,
            Origen: evento.origen,
            'Fecha y hora': evento.fecha,
            Observaciones: evento.observaciones,
        };
        Object.entries(campos).forEach(([etiqueta, valor]) => {
            const item = document.createElement('div');
            const titulo = document.createElement('dt');
            const contenido = document.createElement('dd');
            titulo.textContent = etiqueta;
            contenido.textContent = String(valor ?? '—');
            item.append(titulo, contenido);
            detalleContenido.append(item);
        });
        detalleVacio.hidden = true;
        detalleContenido.hidden = false;
        panel.querySelectorAll('[data-audit-row]').forEach((fila) => fila.classList.toggle('is-selected', fila.dataset.auditId === String(evento.id)));
    };

    panel.querySelectorAll('[data-audit-detail]').forEach((boton) => {
        boton.addEventListener('click', () => renderizar(filas.get(String(boton.dataset.auditDetail))));
    });

    renderizar(configuracion[0]);
});
