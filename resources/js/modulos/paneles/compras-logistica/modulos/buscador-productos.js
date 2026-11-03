
/* ==========================================
   CELL SAC - COMPRAS Y LOGÍSTICA
   Buscador de productos para registrar compras
========================================== */

(() => {
    const SELECTOR = 'select[name="items[0][producto_id]"]';

    function inicializarBuscador(select) {
        if (select.dataset.searchInitialized) return;

        const label = select.closest('label');
        const form = select.closest('form');

        if (!label || !form) return;

        select.dataset.searchInitialized = 'true';

        // Crear contenedor del buscador.
        const wrapper = document.createElement('div');
        wrapper.className = 'cl-product-search';

        const icon = document.createElement('i');
        icon.className = 'bi bi-search cl-product-search__icon';
        icon.setAttribute('aria-hidden', 'true');

        const input = document.createElement('input');
        input.type = 'search';
        input.className = 'cl-product-search__input';
        input.placeholder = 'Buscar producto por nombre...';
        input.autocomplete = 'off';
        input.required = true;
        input.setAttribute('aria-label', 'Buscar producto');
        input.setAttribute('role', 'combobox');
        input.setAttribute('aria-expanded', 'false');

        const results = document.createElement('div');
        results.className = 'cl-product-search__results';
        results.setAttribute('role', 'listbox');
        results.hidden = true;

        // Mantener el select original para enviar el ID real.
        select.classList.add('cl-product-search__native');
        select.required = false;

        select.parentNode.insertBefore(wrapper, select);

        wrapper.append(icon, input, results, select);

        const options = () =>
            Array.from(select.options).filter(
                option => option.value && !option.disabled
            );

        function cerrarResultados() {
            results.hidden = true;
            input.setAttribute('aria-expanded', 'false');
        }

        function seleccionarProducto(option) {
            select.value = option.value;
            input.value = option.textContent.trim();

            input.setCustomValidity('');
            cerrarResultados();

            select.dispatchEvent(
                new Event('change', { bubbles: true })
            );
        }

        function mostrarResultados(query = '') {
            results.replaceChildren();

            const normalizedQuery = query
                .trim()
                .toLocaleLowerCase('es');

            const matches = options().filter(option =>
                option.textContent
                    .trim()
                    .toLocaleLowerCase('es')
                    .includes(normalizedQuery)
            ).slice(0, 8);

            if (!matches.length) {
                const empty = document.createElement('div');
                empty.className = 'cl-product-search__empty';
                empty.textContent = 'No se encontraron productos.';
                results.append(empty);
            } else {
                matches.forEach(option => {
                    const button = document.createElement('button');

                    button.type = 'button';
                    button.className = 'cl-product-search__option';
                    button.setAttribute('role', 'option');
                    button.textContent = option.textContent.trim();

                    button.addEventListener('click', () => {
                        seleccionarProducto(option);
                    });

                    results.append(button);
                });
            }

            results.hidden = false;
            input.setAttribute('aria-expanded', 'true');
        }

        // Mantener la selección inicial del formulario.
        const initialOption = select.selectedOptions[0];

        if (initialOption?.value) {
            input.value = initialOption.textContent.trim();
        }

        input.addEventListener('focus', () => {
            mostrarResultados(input.value);
        });

        input.addEventListener('input', () => {
            // Evitar enviar un producto anterior si cambia la búsqueda.
            select.value = '';

            input.setCustomValidity(
                'Selecciona un producto de los resultados.'
            );

            mostrarResultados(input.value);
        });

        input.addEventListener('keydown', event => {
            if (event.key === 'Escape') {
                cerrarResultados();
            }
        });

        form.addEventListener('submit', event => {
            if (!select.value) {
                input.setCustomValidity(
                    'Debes seleccionar un producto existente.'
                );

                input.reportValidity();
                event.preventDefault();
            }
        });

        document.addEventListener('click', event => {
            if (!wrapper.contains(event.target)) {
                cerrarResultados();
            }
        });
    }

    function iniciar() {
        document.querySelectorAll(SELECTOR).forEach(
            inicializarBuscador
        );
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', iniciar);
    } else {
        iniciar();
    }
})();
