// Guarda y aplica la configuración del cliente mayorista
// Se guarda en el navegador (localStorage), no en la base de datos

const cajaConfig = document.querySelector('[data-config-cliente]');

if (cajaConfig) {
    const clave = 'cellsac-config-' + cajaConfig.dataset.configCliente;
    const campos = cajaConfig.querySelectorAll('input[name], select[name]');
    const mensaje = cajaConfig.querySelector('[data-config-mensaje]');

    // leer lo guardado (si el navegador no deja, se usa lo de por defecto)
    const leer = () => {
        try {
            return JSON.parse(localStorage.getItem(clave)) || {};
        } catch (error) {
            return {};
        }
    };

    // poner los valores guardados en el formulario
    const cargarFormulario = (datos) => {
        campos.forEach((campo) => {
            if (!(campo.name in datos)) {
                return;
            }

            if (campo.type === 'checkbox') {
                campo.checked = datos[campo.name];
            } else {
                campo.value = datos[campo.name];
            }
        });
    };

    // aplicar la configuración al panel
    const aplicar = (datos) => {
        document.querySelector('[data-cliente-vistas]')?.classList.toggle('is-compacto', Boolean(datos.compacto));

        const limite = Number(datos.pedidos_resumen) || 0;
        document.querySelectorAll('[data-pedido-resumen]').forEach((pedido, indice) => {
            pedido.classList.toggle('fuera-de-limite', limite > 0 && indice >= limite);
        });
    };

    const avisar = (texto) => {
        mensaje.textContent = texto;
        setTimeout(() => {
            mensaje.textContent = '';
        }, 2500);
    };

    cajaConfig.querySelector('[data-config-guardar]').addEventListener('click', () => {
        const datos = {};

        campos.forEach((campo) => {
            datos[campo.name] = campo.type === 'checkbox' ? campo.checked : campo.value;
        });

        try {
            localStorage.setItem(clave, JSON.stringify(datos));
            avisar('Configuración guardada');
        } catch (error) {
            avisar('No se pudo guardar en este navegador');
        }

        aplicar(datos);
    });

    cajaConfig.querySelector('[data-config-restablecer]').addEventListener('click', () => {
        try {
            localStorage.removeItem(clave);
        } catch (error) {
            // si falla no pasa nada
        }

        window.location.reload();
    });

    const guardado = leer();
    cargarFormulario(guardado);
    aplicar(guardado);
}
