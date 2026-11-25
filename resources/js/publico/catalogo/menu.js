(() => {
    const drawer = document.querySelector('[data-catalog-menu]');
    if (!drawer) return;
    const openButton = document.querySelector('[data-catalog-menu-open]');
    const backdrop = document.querySelector('.catalog-menu-backdrop');
    const closeButtons = document.querySelectorAll('[data-catalog-menu-close]');
    const backButton = drawer.querySelector('[data-catalog-menu-back]');
    const title = drawer.querySelector('[data-catalog-menu-title]');
    const optionsContainer = drawer.querySelector('[data-catalog-menu-options]');
    const status = drawer.querySelector('[data-catalog-menu-status]');
    let requestController = null;
    let stack = [];

    const requestUrl = (params = {}) => {
        const url = new URL(drawer.dataset.menuUrl, window.location.href);
        Object.entries(params).forEach(([key, value]) => {
            if (key === 'atributos' && value && typeof value === 'object') {
                Object.entries(value).forEach(([attributeId, values]) => (Array.isArray(values) ? values : [values]).forEach((item) => url.searchParams.append(`atributos[${attributeId}][]`, item)));
                return;
            }
            (Array.isArray(value) ? value : [value]).forEach((item) => url.searchParams.append(Array.isArray(value) ? `${key}[]` : key, item));
        });
        return url;
    };
    const showStatus = (message = '', error = false) => { status.textContent = message; status.hidden = message === ''; status.classList.toggle('is-error', error); };
    const load = async (params, addToStack = true) => {
        requestController?.abort(); requestController = new AbortController(); showStatus('Cargando opciones...');
        try {
            const response = await fetch(requestUrl(params), { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, signal: requestController.signal });
            if (!response.ok) throw new Error('No se pudieron cargar las opciones.');
            const data = await response.json(); if (addToStack) stack.push(data); render(data); showStatus('');
        } catch (error) { if (error.name !== 'AbortError') showStatus(error.message, true); }
    };
    const render = (data) => {
        title.textContent = data.titulo || 'Explorar catálogo'; backButton.hidden = stack.length < 2; optionsContainer.replaceChildren();
        if (!data.opciones?.length) { const empty = document.createElement('p'); empty.className = 'catalog-menu-empty'; empty.textContent = 'No hay opciones compatibles.'; optionsContainer.append(empty); return; }
        const fragment = document.createDocumentFragment();
        data.opciones.forEach((option) => {
            const item = document.createElement('button'); item.type = 'button'; item.className = 'catalog-menu-option';
            const label = document.createElement('span'); label.textContent = option.nombre;
            const meta = document.createElement('small'); meta.textContent = `${option.cantidad_productos ?? 0} productos`;
            item.append(label, meta); if (option.tiene_hijos) { const arrow = document.createElement('i'); arrow.className = 'bi bi-chevron-right'; arrow.setAttribute('aria-hidden', 'true'); item.append(arrow); }
            item.addEventListener('click', () => {
                if ((option.tipo === 'marca' || option.tipo === 'productos') && option.url) {
                    close();
                    window.location.assign(option.url);
                    return;
                }
                load(option.parametros || {});
            });
            fragment.append(item);
        }); optionsContainer.append(fragment);
    };
    const open = () => { drawer.hidden = false; backdrop.hidden = false; drawer.setAttribute('aria-hidden', 'false'); openButton?.setAttribute('aria-expanded', 'true'); if (stack.length === 0) load({}); };
    const close = () => { drawer.hidden = true; backdrop.hidden = true; drawer.setAttribute('aria-hidden', 'true'); openButton?.setAttribute('aria-expanded', 'false'); };
    openButton?.addEventListener('click', open); closeButtons.forEach((button) => button.addEventListener('click', close));
    backButton.addEventListener('click', () => { if (stack.length < 2) return; stack.pop(); render(stack.at(-1)); });
    document.addEventListener('keydown', (event) => { if (event.key === 'Escape' && !drawer.hidden) close(); });
})();
