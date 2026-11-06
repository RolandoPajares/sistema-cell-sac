/**
 * Descarga las filas visibles de una tabla como CSV.
 *
 * @param {HTMLElement} tabla
 * @param {string} nombreArchivo
 * @returns {void}
 */
/**
 * Ejecuta la operación exportarTablaVisible utilizada por el módulo JavaScript.
 *
 * @param {*} tabla - Parámetro recibido por la función.
 *
 * @param {*} nombreArchivo - Parámetro recibido por la función.
 *
 * @param {*} reporte - Parámetro recibido por la función.
 *
 * @param {*} csv - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
export function exportarTablaVisible(tabla, nombreArchivo = 'reporte.csv') {
    if (!tabla) {
        return;
    }

    const filas = [...tabla.querySelectorAll('table tr')]
        .filter((fila) => !fila.hidden)
        .map((fila) =>
            [...fila.children]
                .map((columna) => `"${columna.textContent.trim().replaceAll('"', '""')}"`)
                .join(',')
        );

    const archivoCsv = new Blob([filas.join('\n')], {
        type: 'text/csv;charset=utf-8',
    });
    const enlaceDescarga = document.createElement('a');

    enlaceDescarga.href = URL.createObjectURL(archivoCsv);
    enlaceDescarga.download = nombreArchivo;
    enlaceDescarga.click();
    URL.revokeObjectURL(enlaceDescarga.href);
}