import { exportarTablaVisible } from '../../componentes/gestion/tabla-datos.js';

const abrirSelectorFecha = (input) => {
    if (typeof input?.showPicker === 'function') {
        input.showPicker();
        return;
    }

    input?.click();
};

const formatearFecha = (valor) => new Date(`${valor}T12:00:00`).toLocaleDateString('es-PE', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
});

document.querySelectorAll('[data-module-date]').forEach((inputFecha) => {
    const contenedorBoton = inputFecha.closest('.boton-fecha');
    const etiquetaTexto = contenedorBoton?.querySelector('[data-module-date-label]');

    contenedorBoton?.addEventListener('click', (evento) => {
        if (evento.target === inputFecha) {
            return;
        }

        evento.preventDefault();
        abrirSelectorFecha(inputFecha);
    });

    inputFecha.addEventListener('change', () => {
        if (inputFecha.value && etiquetaTexto) {
            etiquetaTexto.textContent = formatearFecha(inputFecha.value);
        }
    });
});

const filtrosContenedor = document.querySelector('[data-module-filters]');
const tablaFiltro = document.querySelector('[data-filter-table]');

if (tablaFiltro) {
    const campoBusqueda = filtrosContenedor?.querySelector('[data-filter-search]');
    const selectorEstado = filtrosContenedor?.querySelector('[data-filter-status]');
    const campoFechaFiltro = filtrosContenedor?.querySelector('[data-filter-date]');
    const etiquetaFechaFiltro = filtrosContenedor?.querySelector('[data-filter-date-label]');
    const contenedorFiltroFecha = filtrosContenedor?.querySelector('.module-filter-date');

    contenedorFiltroFecha?.addEventListener('click', (evento) => {
        if (evento.target === campoFechaFiltro) {
            return;
        }

        evento.preventDefault();
        abrirSelectorFecha(campoFechaFiltro);
    });

    campoFechaFiltro?.addEventListener('change', () => {
        if (campoFechaFiltro.value && etiquetaFechaFiltro) {
            etiquetaFechaFiltro.textContent = formatearFecha(campoFechaFiltro.value);
        }
    });

    const aplicarFiltros = () => {
        const textoBusqueda = (campoBusqueda?.value || '').trim().toLowerCase();
        const estadoSeleccionado = (selectorEstado?.value || '').toLowerCase();
        let filasVisibles = 0;

        tablaFiltro.querySelectorAll('[data-filter-row]').forEach((fila) => {
            const contenidoFila = fila.textContent.toLowerCase();
            const cumpleBusqueda = !textoBusqueda || contenidoFila.includes(textoBusqueda);
            const cumpleEstado = !estadoSeleccionado || contenidoFila.includes(estadoSeleccionado);

            fila.hidden = !(cumpleBusqueda && cumpleEstado);

            if (!fila.hidden) {
                filasVisibles += 1;
            }
        });

        const mensajeVacio = tablaFiltro.querySelector('[data-filter-empty]');

        if (mensajeVacio) {
            mensajeVacio.hidden = filasVisibles !== 0;
        }
    };

    filtrosContenedor?.querySelector('[data-apply-filters]')?.addEventListener('click', aplicarFiltros);
    campoBusqueda?.addEventListener('input', aplicarFiltros);
    selectorEstado?.addEventListener('change', aplicarFiltros);

    tablaFiltro.querySelector('[data-close-detail]')?.addEventListener('click', () => {
        const detalleTablaFlotante = tablaFiltro.querySelector('.detalle-mockup');

        if (detalleTablaFlotante) {
            detalleTablaFlotante.hidden = true;
        }
    });

    tablaFiltro.querySelector('[data-export-table]')?.addEventListener('click', () => {
        exportarTablaVisible(tablaFiltro);
    });
}
