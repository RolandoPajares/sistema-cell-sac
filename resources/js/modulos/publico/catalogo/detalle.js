(() => {
    const root = document.querySelector('[data-product-detail]');
    if (!root) return;

    const mainImage = root.querySelector('[data-main-product-image]');
    const placeholder = root.querySelector('[data-image-placeholder]');
    const thumbs = [...root.querySelectorAll('.gallery-thumb')];
    const previous = root.querySelector('[data-gallery-prev]');
    const next = root.querySelector('[data-gallery-next]');
    const counter = root.querySelector('[data-gallery-counter]');
    let activeIndex = 0;

/**
 * Ejecuta la operación setImage utilizada por el módulo JavaScript.
 *
 * @param {*} source - Parámetro recibido por la función.
 *
 * @param {*} index - Parámetro recibido por la función.
 *
 * @param {*} activeIndex - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
    const setImage = (source, index = activeIndex) => {
        if (!source || !mainImage) return;
        mainImage.src = source;
        mainImage.hidden = false;
        placeholder?.setAttribute('hidden', 'hidden');
        activeIndex = index;
        thumbs.forEach((thumb, thumbIndex) => {
            const active = thumbIndex === activeIndex;
            thumb.classList.toggle('active', active);
            thumb.toggleAttribute('aria-current', active);
        });
        if (counter) counter.textContent = `${activeIndex + 1} de ${thumbs.length} imágenes`;
    };

/**
 * Ejecuta la operación moveImage utilizada por el módulo JavaScript.
 *
 * @param {*} step - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
    const moveImage = (step) => {
        if (thumbs.length < 2) return;
        const index = (activeIndex + step + thumbs.length) % thumbs.length;
        setImage(thumbs[index].dataset.src, index);
    };

    thumbs.forEach((thumb, index) => thumb.addEventListener('click', () => setImage(thumb.dataset.src, index)));
    previous?.addEventListener('click', () => moveImage(-1));
    next?.addEventListener('click', () => moveImage(1));
    if (thumbs.length > 1) {
        previous?.removeAttribute('hidden');
        next?.removeAttribute('hidden');
    }
    if (thumbs[0]) setImage(thumbs[0].dataset.src, 0);

    const zoomDialog = document.querySelector('[data-zoom-dialog]');
    const zoomImage = zoomDialog?.querySelector('[data-zoom-image]');
    root.querySelector('[data-zoom-open]')?.addEventListener('click', () => {
        if (!mainImage?.src || !zoomDialog) return;
        if (zoomImage) zoomImage.src = mainImage.src;
        zoomDialog.showModal();
    });
    zoomDialog?.querySelector('[data-zoom-close]')?.addEventListener('click', () => zoomDialog.close());
    zoomDialog?.addEventListener('click', (event) => { if (event.target === zoomDialog) zoomDialog.close(); });

    const favoriteButtons = [...root.querySelectorAll('[data-favorite-toggle]')];
    const favoriteStorageKey = `producto-favorito-${root.dataset.productId}`;
    let favoriteActive = false;
    try { favoriteActive = window.sessionStorage.getItem(favoriteStorageKey) === '1'; } catch { favoriteActive = false; }
/**
 * Ejecuta la operación updateFavoriteButtons utilizada por el módulo JavaScript.
 *
 * @param {*} active - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
    const updateFavoriteButtons = (active) => {
        favoriteButtons.forEach((button) => {
            button.setAttribute('aria-pressed', String(active));
            button.classList.toggle('is-favorite', active);
            button.title = active ? 'Quitar de favoritos' : 'Añadir a favoritos';
            const icon = button.querySelector('i');
            icon?.classList.toggle('bi-heart-fill', active);
            icon?.classList.toggle('bi-heart', !active);
            const label = button.querySelector('[data-favorite-label]');
            if (label) label.textContent = active ? 'Quitar de favoritos' : 'Añadir a favoritos';
        });
    };
    updateFavoriteButtons(favoriteActive);
    favoriteButtons.forEach((button) => button.addEventListener('click', () => {
        favoriteActive = !favoriteActive;
        try { window.sessionStorage.setItem(favoriteStorageKey, favoriteActive ? '1' : '0'); } catch {}
        updateFavoriteButtons(favoriteActive);
    }));

    let variantes = [];
    try { variantes = JSON.parse(root.dataset.variantData || '[]'); } catch { variantes = []; }
    const selectedVariant = root.querySelector('[data-selected-variant]');
    const stockLabel = root.querySelector('[data-current-stock]');
    const skuLabel = root.querySelector('[data-current-sku]');
    const quantityInput = root.querySelector('[data-quantity-input]');
    const purchaseButtons = [...root.querySelectorAll('[data-add-to-cart], .product-detail-buy--success')];
    const formMessage = root.querySelector('[data-form-message]');

/**
 * Ejecuta la operación updateVariant utilizada por el módulo JavaScript.
 *
 * @param {*} variant - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
    const updateVariant = (variant) => {
        if (!variant) return;
        const stock = Number(variant.stock || 0);
        if (selectedVariant) selectedVariant.value = variant.id;
        if (skuLabel) skuLabel.textContent = variant.sku || 'No registrado';
        if (stockLabel) {
            stockLabel.classList.toggle('is-available', stock > 0);
            stockLabel.classList.toggle('is-unavailable', stock <= 0);
            stockLabel.innerHTML = `<i class="bi ${stock > 0 ? 'bi-check-circle' : 'bi-x-circle'}" aria-hidden="true"></i>${stock > 0 ? `${stock} unidades disponibles` : 'Sin stock'}`;
        }
        if (quantityInput) {
            quantityInput.max = String(Math.max(1, stock));
            quantityInput.value = String(Math.min(Math.max(1, Number(quantityInput.value || 1)), Math.max(1, stock)));
        }
        purchaseButtons.forEach((button) => { button.disabled = stock <= 0; });
        if (variant.image) setImage(variant.image, activeIndex);
    };

    root.querySelectorAll('[data-variant-option]').forEach((input) => input.addEventListener('change', () => {
        root.querySelectorAll('.variant-option').forEach((option) => option.classList.toggle('is-selected', option.querySelector('input')?.checked));
        updateVariant(variantes.find((variant) => String(variant.id) === input.value));
    }));
    const initialVariant = variantes.find((variant) => String(variant.id) === selectedVariant?.value) || variantes[0];
    if (initialVariant) updateVariant(initialVariant);

/**
 * Ejecuta la operación clampQuantity utilizada por el módulo JavaScript.
 *
 * @returns {*} Resultado de la operación.
 */
    const clampQuantity = () => {
        if (!quantityInput) return;
        const max = Number(quantityInput.max || 1);
        quantityInput.value = String(Math.min(max, Math.max(1, Number.parseInt(quantityInput.value || '1', 10) || 1)));
    };
    root.querySelector('[data-quantity-minus]')?.addEventListener('click', () => { quantityInput.value = String(Math.max(1, Number(quantityInput.value || 1) - 1)); });
    root.querySelector('[data-quantity-plus]')?.addEventListener('click', () => { quantityInput.value = String(Math.min(Number(quantityInput.max || 1), Number(quantityInput.value || 1) + 1)); });
    quantityInput?.addEventListener('change', clampQuantity);

    root.querySelector('[data-product-form]')?.addEventListener('submit', (event) => {
        clampQuantity();
        const variant = variantes.find((item) => String(item.id) === selectedVariant?.value);
        if (variantes.length && !variant) {
            event.preventDefault();
            if (formMessage) { formMessage.hidden = false; formMessage.textContent = 'Selecciona una variante.'; }
            return;
        }
        if (variant && Number(quantityInput?.value || 1) > Number(variant.stock || 0)) {
            event.preventDefault();
            if (formMessage) { formMessage.hidden = false; formMessage.textContent = 'La cantidad supera el stock disponible.'; }
        }
    });

    root.querySelectorAll('[data-tab-target]').forEach((tab) => tab.addEventListener('click', () => {
        const target = tab.dataset.tabTarget;
        root.querySelectorAll('[data-tab-target]').forEach((item) => item.setAttribute('aria-selected', String(item === tab)));
        root.querySelectorAll('[data-tab-panel]').forEach((panel) => {
            const active = panel.dataset.tabPanel === target;
            panel.hidden = !active;
            panel.classList.toggle('is-active', active);
        });
    }));
})();