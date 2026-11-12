/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/publico/catalogo/detalle.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

(() => {
    const root = document.querySelector('[data-product-detail]');
    // Condicional: evalua "!root" para decidir si ejecuta el bloque siguiente.
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
    // Funcion: setImage concentra una tarea reutilizable del modulo.
    const setImage = (source, index = activeIndex) => {
        // Condicional: evalua "!source || !mainImage" para decidir si ejecuta el bloque siguiente.
        if (!source || !mainImage) return;
        mainImage.src = source;
        mainImage.hidden = false;
        placeholder?.setAttribute('hidden', 'hidden');
        activeIndex = index;
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        thumbs.forEach((thumb, thumbIndex) => {
            const active = thumbIndex === activeIndex;
            thumb.classList.toggle('active', active);
            thumb.toggleAttribute('aria-current', active);
        });
        // Condicional: evalua "counter" para decidir si ejecuta el bloque siguiente.
        if (counter) counter.textContent = `${activeIndex + 1} de ${thumbs.length} imágenes`;
    };

    /**
     * Avanza o retrocede circularmente entre las imágenes de la galería.
     *
     * @param {number} step - Desplazamiento relativo, normalmente 1 o -1.
     * @returns {void} No devuelve valor; delega la actualización en setImage.
     */
    // Funcion: moveImage concentra una tarea reutilizable del modulo.
    const moveImage = (step) => {
        // Condicional: evalua "thumbs.length < 2" para decidir si ejecuta el bloque siguiente.
        if (thumbs.length < 2) return;
        const index = (activeIndex + step + thumbs.length) % thumbs.length;
        setImage(thumbs[index].dataset.src, index);
    };

    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    thumbs.forEach((thumb, index) => thumb.addEventListener('click', () => setImage(thumb.dataset.src, index)));
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    previous?.addEventListener('click', () => moveImage(-1));
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    next?.addEventListener('click', () => moveImage(1));
    // Condicional: evalua "thumbs.length > 1" para decidir si ejecuta el bloque siguiente.
    if (thumbs.length > 1) {
        previous?.removeAttribute('hidden');
        next?.removeAttribute('hidden');
    }
    // Condicional: evalua "thumbs[0]) setImage(thumbs[0].dataset.src, 0" para decidir si ejecuta el bloque siguiente.
    if (thumbs[0]) setImage(thumbs[0].dataset.src, 0);

    const zoomDialog = document.querySelector('[data-zoom-dialog]');
    const zoomImage = zoomDialog?.querySelector('[data-zoom-image]');
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    root.querySelector('[data-zoom-open]')?.addEventListener('click', () => {
        // Condicional: evalua "!mainImage?.src || !zoomDialog" para decidir si ejecuta el bloque siguiente.
        if (!mainImage?.src || !zoomDialog) return;
        // Condicional: evalua "zoomImage" para decidir si ejecuta el bloque siguiente.
        if (zoomImage) zoomImage.src = mainImage.src;
        zoomDialog.showModal();
    });
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    zoomDialog?.querySelector('[data-zoom-close]')?.addEventListener('click', () => zoomDialog.close());
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
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
    // Funcion: goToLogin concentra una tarea reutilizable del modulo.
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
    // Funcion: updateFavoriteButtons concentra una tarea reutilizable del modulo.
    const updateFavoriteButtons = (active) => {
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        favoriteButtons.forEach((button) => {
            button.setAttribute('aria-pressed', String(active));
            button.classList.toggle('is-favorite', active);
            button.title = active ? 'Quitar de favoritos' : 'Añadir a favoritos';
            const icon = button.querySelector('i');
            icon?.classList.toggle('bi-heart-fill', active);
            icon?.classList.toggle('bi-heart', !active);
            const label = button.querySelector('[data-favorite-label]');
            // Condicional: evalua "label" para decidir si ejecuta el bloque siguiente.
            if (label) label.textContent = active ? 'Quitar de favoritos' : 'Añadir a favoritos';
        });
    };
    updateFavoriteButtons(favoriteActive);
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    favoriteButtons.forEach((button) => button.addEventListener('click', async () => {
        // Condicional: evalua "!authenticated) { goToLogin(" para decidir si ejecuta el bloque siguiente.
        if (!authenticated) { goToLogin(); return; }
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        favoriteButtons.forEach((item) => { item.disabled = true; });
        try {
            // Peticion asincrona: envia datos al servidor con fetch y espera su respuesta sin recargar la pagina.
            const response = await fetch(root.dataset.favoriteUrl, { method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken || '' } });
            // Condicional: evalua "!response.ok) throw new Error('favorite-request-failed'" para decidir si ejecuta el bloque siguiente.
            if (!response.ok) throw new Error('favorite-request-failed');
            // Flujo asincrono: espera el resultado de una operacion que puede tardar antes de continuar.
            const data = await response.json();
            favoriteActive = Boolean(data.favorito);
            updateFavoriteButtons(favoriteActive);
            // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
            root.querySelectorAll('[data-favorite-status]').forEach((status) => { status.textContent = favoriteActive ? 'Guardado en tu cuenta' : 'Eliminado de tu cuenta'; });
        } catch {
            // Condicional: evalua "formMessage" para decidir si ejecuta el bloque siguiente.
            if (formMessage) { formMessage.textContent = 'No se pudo guardar el favorito.'; formMessage.hidden = false; }
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
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
    // Funcion: updateRating concentra una tarea reutilizable del modulo.
    const updateRating = (rating) => {
        selectedRating = Math.max(0, Math.min(5, Number(rating) || 0));
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        ratingWidgets.forEach((widget) => widget.querySelectorAll('[data-rating-value]').forEach((button) => {
            const value = Number(button.dataset.ratingValue);
            const active = value <= selectedRating;
            button.setAttribute('aria-checked', String(value === selectedRating));
            button.classList.toggle('is-selected', active);
            button.querySelector('i')?.classList.toggle('bi-star-fill', active);
            button.querySelector('i')?.classList.toggle('bi-star', !active);
        }));
        // Condicional: evalua "reviewMessage && selectedRating" para decidir si ejecuta el bloque siguiente.
        if (reviewMessage && selectedRating) reviewMessage.textContent = 'Tu valoración actual: ' + selectedRating + '/5.';
    };
    updateRating(selectedRating);
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    ratingWidgets.forEach((widget) => widget.querySelectorAll('[data-rating-value]').forEach((button) => button.addEventListener('click', async () => {
        // Condicional: evalua "!authenticated) { goToLogin(" para decidir si ejecuta el bloque siguiente.
        if (!authenticated) { goToLogin(); return; }
        const rating = Number(button.dataset.ratingValue);
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        ratingWidgets.forEach((item) => { item.querySelectorAll('[data-rating-value]').forEach((ratingButton) => { ratingButton.disabled = true; }); });
        try {
            const body = new URLSearchParams({ valoracion: String(rating) });
            // Peticion asincrona: envia datos al servidor con fetch y espera su respuesta sin recargar la pagina.
            const response = await fetch(root.dataset.reviewUrl, { method: 'POST', headers: { Accept: 'application/json', 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8', 'X-CSRF-TOKEN': csrfToken || '' }, body });
            // Condicional: evalua "!response.ok) throw new Error('review-request-failed'" para decidir si ejecuta el bloque siguiente.
            if (!response.ok) throw new Error('review-request-failed');
            // Flujo asincrono: espera el resultado de una operacion que puede tardar antes de continuar.
            const data = await response.json();
            updateRating(data.valoracion);
            // Condicional: evalua "ratingSummary) ratingSummary.textContent = 'Promedio: ' + data.promedio + '/5 (' + data.cantidad + ' reseñas" para decidir si ejecuta el bloque siguiente.
            if (ratingSummary) ratingSummary.textContent = 'Promedio: ' + data.promedio + '/5 (' + data.cantidad + ' reseñas)';
            // Condicional: evalua "reviewMessage" para decidir si ejecuta el bloque siguiente.
            if (reviewMessage) reviewMessage.textContent = 'Tu valoración se guardó en la base de datos.';
        } catch {
            // Condicional: evalua "reviewMessage" para decidir si ejecuta el bloque siguiente.
            if (reviewMessage) reviewMessage.textContent = 'No se pudo guardar la valoración.';
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
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
    // Funcion: updateVariant concentra una tarea reutilizable del modulo.
    const updateVariant = (variant) => {
        // Condicional: evalua "!variant" para decidir si ejecuta el bloque siguiente.
        if (!variant) return;
        const stock = Number(variant.stock || 0);
        // Condicional: evalua "selectedVariant" para decidir si ejecuta el bloque siguiente.
        if (selectedVariant) selectedVariant.value = variant.id;
        // Condicional: evalua "skuLabel" para decidir si ejecuta el bloque siguiente.
        if (skuLabel) skuLabel.textContent = variant.sku || 'No registrado';
        // Condicional: evalua "stockLabel" para decidir si ejecuta el bloque siguiente.
        if (stockLabel) {
            stockLabel.classList.toggle('is-available', stock > 0);
            stockLabel.classList.toggle('is-unavailable', stock <= 0);
            stockLabel.innerHTML = `<i class="bi ${stock > 0 ? 'bi-check-circle' : 'bi-x-circle'}" aria-hidden="true"></i>${stock > 0 ? `${stock} unidades disponibles` : 'Sin stock'}`;
        }
        // Condicional: evalua "quantityInput" para decidir si ejecuta el bloque siguiente.
        if (quantityInput) {
            quantityInput.max = String(Math.max(1, stock));
            quantityInput.value = String(Math.min(Math.max(1, Number(quantityInput.value || 1)), Math.max(1, stock)));
        }
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        purchaseButtons.forEach((button) => { button.disabled = stock <= 0; });
        // Condicional: evalua "variant.image) setImage(variant.image, activeIndex" para decidir si ejecuta el bloque siguiente.
        if (variant.image) setImage(variant.image, activeIndex);
    };

    // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
    root.querySelectorAll('[data-variant-option]').forEach((input) => input.addEventListener('change', () => {
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        root.querySelectorAll('.variant-option').forEach((option) => option.classList.toggle('is-selected', option.querySelector('input')?.checked));
        updateVariant(variantes.find((variant) => String(variant.id) === input.value));
    }));
    // Funcion: initialVariant concentra una tarea reutilizable del modulo.
    const initialVariant = variantes.find((variant) => String(variant.id) === selectedVariant?.value) || variantes[0];
    // Condicional: evalua "initialVariant) updateVariant(initialVariant" para decidir si ejecuta el bloque siguiente.
    if (initialVariant) updateVariant(initialVariant);

    /**
     * Limita la cantidad del formulario al intervalo entre uno y el stock máximo.
     *
     * @returns {void} No devuelve valor; corrige el valor del input cuando existe.
     */
    // Funcion: clampQuantity concentra una tarea reutilizable del modulo.
    const clampQuantity = () => {
        // Condicional: evalua "!quantityInput" para decidir si ejecuta el bloque siguiente.
        if (!quantityInput) return;
        const max = Number(quantityInput.max || 1);
        quantityInput.value = String(Math.min(max, Math.max(1, Number.parseInt(quantityInput.value || '1', 10) || 1)));
    };
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    root.querySelector('[data-quantity-minus]')?.addEventListener('click', () => { quantityInput.value = String(Math.max(1, Number(quantityInput.value || 1) - 1)); });
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    root.querySelector('[data-quantity-plus]')?.addEventListener('click', () => { quantityInput.value = String(Math.min(Number(quantityInput.max || 1), Number(quantityInput.value || 1) + 1)); });
    // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
    quantityInput?.addEventListener('change', clampQuantity);

    // Evento: escucha el evento submit y ejecuta la funcion asociada cuando ocurre.
    root.querySelector('[data-product-form]')?.addEventListener('submit', (event) => {
        clampQuantity();
        // Funcion: variant concentra una tarea reutilizable del modulo.
        const variant = variantes.find((item) => String(item.id) === selectedVariant?.value);
        // Condicional: evalua "variantes.length && !variant" para decidir si ejecuta el bloque siguiente.
        if (variantes.length && !variant) {
            event.preventDefault();
            // Condicional: evalua "formMessage" para decidir si ejecuta el bloque siguiente.
            if (formMessage) { formMessage.hidden = false; formMessage.textContent = 'Selecciona una variante.'; }
            return;
        }
        // Condicional: evalua "variant && Number(quantityInput?.value || 1) > Number(variant.stock || 0)" para decidir si ejecuta el bloque siguiente.
        if (variant && Number(quantityInput?.value || 1) > Number(variant.stock || 0)) {
            event.preventDefault();
            // Condicional: evalua "formMessage" para decidir si ejecuta el bloque siguiente.
            if (formMessage) { formMessage.hidden = false; formMessage.textContent = 'La cantidad supera el stock disponible.'; }
        }
    });

    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    root.querySelectorAll('[data-tab-target]').forEach((tab) => tab.addEventListener('click', () => {
        const target = tab.dataset.tabTarget;
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        root.querySelectorAll('[data-tab-target]').forEach((item) => item.setAttribute('aria-selected', String(item === tab)));
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        root.querySelectorAll('[data-tab-panel]').forEach((panel) => {
            const active = panel.dataset.tabPanel === target;
            panel.hidden = !active;
            panel.classList.toggle('is-active', active);
        });
    }));
})();
