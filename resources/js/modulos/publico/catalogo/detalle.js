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
     * Actualiza la imagen principal, miniatura activa y contador de la galería.
     *
     * @param {string|undefined} source - URL de la imagen que se mostrará.
     * @param {number} [index=activeIndex] - Índice de la miniatura seleccionada.
     * @returns {void} No devuelve valor; actualiza el DOM.
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
     * Avanza o retrocede circularmente entre las imágenes de la galería.
     *
     * @param {number} step - Desplazamiento relativo, normalmente 1 o -1.
     * @returns {void} No devuelve valor; delega la actualización en setImage.
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
    const authenticated = root.dataset.authenticated === '1';
    const loginUrl = root.dataset.loginUrl || '/login';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const formMessage = root.querySelector('[data-form-message]');
    /**
     * Redirige al inicio de sesión conservando la URL del detalle actual.
     *
     * @returns {void} Cambia la ubicación del navegador.
     */
    const goToLogin = () => {
        const returnUrl = window.location.pathname + window.location.search;
        window.location.assign(loginUrl + '?redirect=' + encodeURIComponent(returnUrl));
    };
    let favoriteActive = root.dataset.favoriteInitial === '1';
    /**
     * Sincroniza el estado visual y accesible de todos los botones de favorito.
     *
     * @param {boolean} active - Indica si el producto está guardado como favorito.
     * @returns {void} No devuelve valor; actualiza atributos, iconos y etiquetas.
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
    favoriteButtons.forEach((button) => button.addEventListener('click', async () => {
        if (!authenticated) { goToLogin(); return; }
        favoriteButtons.forEach((item) => { item.disabled = true; });
        try {
            const response = await fetch(root.dataset.favoriteUrl, { method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken || '' } });
            if (!response.ok) throw new Error('favorite-request-failed');
            const data = await response.json();
            favoriteActive = Boolean(data.favorito);
            updateFavoriteButtons(favoriteActive);
            root.querySelectorAll('[data-favorite-status]').forEach((status) => { status.textContent = favoriteActive ? 'Guardado en tu cuenta' : 'Eliminado de tu cuenta'; });
        } catch {
            if (formMessage) { formMessage.textContent = 'No se pudo guardar el favorito.'; formMessage.hidden = false; }
        } finally { favoriteButtons.forEach((item) => { item.disabled = false; }); }
    }));

    const ratingWidgets = [...root.querySelectorAll('[data-rating-widget], [data-review-widget]')];
    const ratingSummary = root.querySelector('[data-rating-summary]');
    const reviewMessage = root.querySelector('[data-review-message]');
    let selectedRating = Number(root.dataset.ratingInitial || 0);
    /**
     * Actualiza la selección visual de estrellas y el mensaje de valoración.
     *
     * @param {number|string} rating - Valor recibido del control de estrellas.
     * @returns {void} No devuelve valor; normaliza y refleja la selección en el DOM.
     */
    const updateRating = (rating) => {
        selectedRating = Math.max(0, Math.min(5, Number(rating) || 0));
        ratingWidgets.forEach((widget) => widget.querySelectorAll('[data-rating-value]').forEach((button) => {
            const value = Number(button.dataset.ratingValue);
            const active = value <= selectedRating;
            button.setAttribute('aria-checked', String(value === selectedRating));
            button.classList.toggle('is-selected', active);
            button.querySelector('i')?.classList.toggle('bi-star-fill', active);
            button.querySelector('i')?.classList.toggle('bi-star', !active);
        }));
        if (reviewMessage && selectedRating) reviewMessage.textContent = 'Tu valoración actual: ' + selectedRating + '/5.';
    };
    updateRating(selectedRating);
    ratingWidgets.forEach((widget) => widget.querySelectorAll('[data-rating-value]').forEach((button) => button.addEventListener('click', async () => {
        if (!authenticated) { goToLogin(); return; }
        const rating = Number(button.dataset.ratingValue);
        ratingWidgets.forEach((item) => { item.querySelectorAll('[data-rating-value]').forEach((ratingButton) => { ratingButton.disabled = true; }); });
        try {
            const body = new URLSearchParams({ valoracion: String(rating) });
            const response = await fetch(root.dataset.reviewUrl, { method: 'POST', headers: { Accept: 'application/json', 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8', 'X-CSRF-TOKEN': csrfToken || '' }, body });
            if (!response.ok) throw new Error('review-request-failed');
            const data = await response.json();
            updateRating(data.valoracion);
            if (ratingSummary) ratingSummary.textContent = 'Promedio: ' + data.promedio + '/5 (' + data.cantidad + ' reseñas)';
            if (reviewMessage) reviewMessage.textContent = 'Tu valoración se guardó en la base de datos.';
        } catch {
            if (reviewMessage) reviewMessage.textContent = 'No se pudo guardar la valoración.';
        } finally { ratingWidgets.forEach((item) => { item.querySelectorAll('[data-rating-value]').forEach((ratingButton) => { ratingButton.disabled = false; }); }); }
    })));

    let variantes = [];
    try { variantes = JSON.parse(root.dataset.variantData || '[]'); } catch { variantes = []; }
    const selectedVariant = root.querySelector('[data-selected-variant]');
    const stockLabel = root.querySelector('[data-current-stock]');
    const skuLabel = root.querySelector('[data-current-sku]');
    const quantityInput = root.querySelector('[data-quantity-input]');
    const purchaseButtons = [...root.querySelectorAll('[data-add-to-cart], .product-detail-buy--success')];

    /**
     * Actualiza SKU, stock, cantidad máxima, botones e imagen de la variante elegida.
     *
     * @param {{id: number|string, stock?: number|string, sku?: string, image?: string}} variant - Variante seleccionada.
     * @returns {void} No devuelve valor; refleja la variante en el formulario.
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
     * Limita la cantidad del formulario al intervalo entre uno y el stock máximo.
     *
     * @returns {void} No devuelve valor; corrige el valor del input cuando existe.
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
