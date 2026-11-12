/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/componentes/gestion/tabla-datos.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

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
// Funcion: exportarTablaVisible concentra una tarea reutilizable del modulo.
export function exportarTablaVisible(tabla, nombreArchivo = 'reporte.csv') {
    // Condicional: evalua "!tabla" para decidir si ejecuta el bloque siguiente.
    if (!tabla) {
        return;
    }

    const filas = [...tabla.querySelectorAll('table tr')]
        // Recorrido: usa filter para procesar cada elemento y obtener o actualizar datos.
        .filter((fila) => !fila.hidden)
        // Recorrido: usa map para procesar cada elemento y obtener o actualizar datos.
        .map((fila) =>
            [...fila.children]
                // Recorrido: usa map para procesar cada elemento y obtener o actualizar datos.
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