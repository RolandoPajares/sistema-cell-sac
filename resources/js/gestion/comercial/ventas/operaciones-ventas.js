document.querySelectorAll('[data-ventas-diseno]').forEach((page) => {
    const search = page.querySelector('[data-oc-search]');
    const filters = [...page.querySelectorAll('[data-oc-filter]')];
    const rows = [...page.querySelectorAll('[data-oc-row]')];
    const empty = page.querySelector('[data-oc-empty]');

/**
 * Gestiona la interacciÃ³n y actualiza el estado visual correspondiente.
 *
 * @returns {*} Resultado de la operaciÃ³n.
 */
    const applyFilters = () => {
        const query = search?.value.trim().toLocaleLowerCase('es') ?? '';
        let visible = 0;

        rows.forEach((row) => {
            const textMatches = row.textContent.toLocaleLowerCase('es').includes(query);
            const filtersMatch = filters.every((filter) => {
                const selected = filter.value;
                const field = filter.dataset.ocFilter;
                const key = `oc${field.charAt(0).toUpperCase()}${field.slice(1)}`;

                return !selected || row.dataset[key] === selected;
            });

            row.hidden = !(textMatches && filtersMatch);
            visible += Number(!row.hidden);
        });

        if (empty) {
            empty.hidden = visible > 0;
        }
    };

    search?.addEventListener('input', applyFilters);
    filters.forEach((filter) => filter.addEventListener('change', applyFilters));
    page.querySelector('[data-oc-apply]')?.addEventListener('click', applyFilters);
});

