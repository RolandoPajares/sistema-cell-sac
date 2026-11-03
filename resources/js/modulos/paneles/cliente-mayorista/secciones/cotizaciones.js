// "Mis cotizaciones" del cliente mayorista:
// filtros por estado, buscador y formulario de nueva cotización (agregar productos y total)

const cajaCotizaciones = document.querySelector('.cotiz-cliente');

if (cajaCotizaciones) {
    const botones = cajaCotizaciones.querySelectorAll('[data-filtro-cotizacion]');
    const buscador = cajaCotizaciones.querySelector('[data-buscar-cotizacion]');
    const filas = cajaCotizaciones.querySelectorAll('tr[data-grupo-cotizacion]');
    const sinResultados = cajaCotizaciones.querySelector('.cotiz-cliente-sin-resultados');
    let filtroActual = 'todas';

    // muestra solo las filas que cumplen el filtro y la búsqueda
    const aplicar = () => {
        const texto = (buscador?.value || '').trim().toLowerCase();
        let visibles = 0;

        filas.forEach((fila) => {
            const pasaFiltro = filtroActual === 'todas' || fila.dataset.grupoCotizacion === filtroActual;
            const pasaTexto = texto === '' || fila.dataset.textoCotizacion.includes(texto);
            fila.hidden = !(pasaFiltro && pasaTexto);

            if (!fila.hidden) {
                visibles++;
            }
        });

        if (sinResultados) {
            sinResultados.hidden = visibles > 0 || filas.length === 0;
        }
    };

    botones.forEach((boton) => {
        boton.addEventListener('click', () => {
            filtroActual = boton.dataset.filtroCotizacion;
            botones.forEach((otro) => otro.classList.toggle('activo', otro === boton));
            aplicar();
        });
    });

    buscador?.addEventListener('input', aplicar);
}

// ----- formulario de nueva cotización -----
const formCotizacion = document.querySelector('[data-form-cotizacion]');

if (formCotizacion) {
    const contenedor = formCotizacion.querySelector('[data-items-cotizacion]');
    const totalTexto = formCotizacion.querySelector('[data-total-cotizacion]');

    // vuelve a numerar los nombres items[0], items[1]... y calcula el total
    const actualizar = () => {
        let total = 0;

        contenedor.querySelectorAll('[data-item-cotizacion]').forEach((fila, indice) => {
            const select = fila.querySelector('select');
            const cantidad = fila.querySelector('input');
            select.name = `items[${indice}][producto_id]`;
            cantidad.name = `items[${indice}][cantidad]`;

            const precio = Number(select.selectedOptions[0]?.dataset.precio || 0);
            total += precio * (Number(cantidad.value) || 0);
        });

        totalTexto.textContent = 'S/ ' + total.toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    formCotizacion.querySelector('[data-agregar-item]').addEventListener('click', () => {
        const nueva = contenedor.querySelector('[data-item-cotizacion]').cloneNode(true);
        nueva.querySelector('select').value = '';
        nueva.querySelector('input').value = 10;
        contenedor.appendChild(nueva);
        actualizar();
    });

    // quitar un producto (siempre queda al menos uno)
    contenedor.addEventListener('click', (evento) => {
        const boton = evento.target.closest('[data-quitar-item]');

        if (boton && contenedor.querySelectorAll('[data-item-cotizacion]').length > 1) {
            boton.closest('[data-item-cotizacion]').remove();
            actualizar();
        }
    });

    contenedor.addEventListener('input', actualizar);
    contenedor.addEventListener('change', actualizar);
    actualizar();
}
