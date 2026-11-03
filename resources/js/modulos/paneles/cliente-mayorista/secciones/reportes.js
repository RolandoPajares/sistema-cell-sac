// Descarga el reporte de compras como archivo CSV (se abre en Excel)

const botonDescargar = document.querySelector('[data-reporte-descargar]');

if (botonDescargar) {
    botonDescargar.addEventListener('click', () => {
        const reporte = botonDescargar.closest('.reporte-cliente');
        const filas = [];

        // primero los totales
        reporte.querySelectorAll('.reporte-cliente-kpis article').forEach((kpi) => {
            filas.push([kpi.querySelector('small').textContent, kpi.querySelector('strong').textContent]);
        });

        filas.push([]);

        // luego la tabla de productos
        reporte.querySelectorAll('[data-reporte-tabla] tr').forEach((fila) => {
            filas.push([...fila.children].map((celda) => celda.textContent.trim()));
        });

        // cada valor entre comillas y separado con ;
        const csv = filas
            .map((fila) => fila.map((valor) => '"' + String(valor).replaceAll('"', '""') + '"').join(';'))
            .join('\n');

        const archivo = new Blob(['﻿' + csv], { type: 'text/csv;charset=utf-8' });
        const enlace = document.createElement('a');
        enlace.href = URL.createObjectURL(archivo);
        enlace.download = botonDescargar.dataset.reporteArchivo;
        enlace.click();
        URL.revokeObjectURL(enlace.href);
    });
}
