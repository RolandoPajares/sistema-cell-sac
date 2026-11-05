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
