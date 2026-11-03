// Filtro de la tabla de pedidos del cliente mayorista
// Muestra todos, solo los que están en proceso o solo los entregados

const panelMayorista = document.querySelector('.mayorista-panel');

if (panelMayorista) {
    const botones = panelMayorista.querySelectorAll('[data-filtro-mayorista]');
    const filas = panelMayorista.querySelectorAll('.mayorista-tabla tbody tr[data-estado]');
    const filaSinResultados = panelMayorista.querySelector('.mayorista-sin-resultados');

    // estados que cuentan como "en proceso"
    const estadosEnProceso = ['Pendiente', 'En proceso', 'Enviado'];

    botones.forEach((boton) => {
        boton.addEventListener('click', () => {
            const filtro = boton.dataset.filtroMayorista;
            let visibles = 0;

            // marcar el boton activo
            botones.forEach((otro) => otro.classList.remove('activo'));
            boton.classList.add('activo');

            filas.forEach((fila) => {
                const estado = fila.dataset.estado;
                let mostrar = filtro === 'todos';

                if (filtro === 'proceso') {
                    mostrar = estadosEnProceso.includes(estado);
                } else if (filtro !== 'todos') {
                    mostrar = estado === filtro;
                }

                fila.hidden = !mostrar;

                if (mostrar) {
                    visibles++;
                }
            });

            // si no hay pedidos con ese estado se muestra un mensaje
            if (filaSinResultados) {
                filaSinResultados.hidden = visibles > 0 || filas.length === 0;
            }
        });
    });
}
