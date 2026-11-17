/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/auth/register.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

const selector = document.querySelector('[data-customer-type]');
const field = document.querySelector('[data-wholesale-field]');
const ruc = document.querySelector('#ruc');

// Condicional: evalua "selector && field && ruc" para decidir si ejecuta el bloque siguiente.
if (selector && field && ruc) {
    // Funcion: update concentra una tarea reutilizable del modulo.
    const update = () => {
        const wholesale = selector.value === 'mayorista';
        field.hidden = !wholesale;
        ruc.required = wholesale;

        // Condicional: evalua "!wholesale" para decidir si ejecuta el bloque siguiente.
        if (!wholesale) {
            ruc.value = '';
        }
    };

    // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
    selector.addEventListener('change', update);
    update();
}
