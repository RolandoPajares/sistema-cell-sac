/**
 * Mensajes, confirmaciones y agregado asíncrono al carrito de la tienda.
 */

(() => {
    const popup = document.querySelector('[data-popup]');
    const cartCount = document.querySelector('.nav-actions a[aria-label="Ver carrito"] sup');
    let toastTimer = null;
    let popupTimer = null;
    let showCartAdded = null;

    const showToast = (text, type = 'success') => {
        let toast = document.querySelector('[data-cart-toast]');
        if (!toast) {
            toast = document.createElement('div');
            toast.dataset.cartToast = 'true';
            document.body.append(toast);
        }
        toast.className = `cart-toast is-${type}`;
        toast.textContent = text;
        toast.hidden = false;
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => {
            toast.hidden = true;
        }, 3200);
    };

    const parseVariants = (form) => {
        try {
            const variants = JSON.parse(form.dataset.variants || '[]');
            return Array.isArray(variants) ? variants : [];
        } catch {
            return [];
        }
    };

    const setVariant = (form, value) => {
        let input = form.querySelector('input[name="variante_id"]');
        if (!input) {
            input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'variante_id';
            form.append(input);
        }
        input.value = value;
    };

    const addToCart = async (form) => {
        const submit = form.querySelector('[data-cart-submit]') || form.closest('[data-product-card]')?.querySelector('button');
        if (submit) submit.disabled = true;
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            let data = {};
            try { data = await response.json(); } catch { data = {}; }
            if (!response.ok) throw new Error(data.message || 'No se pudo agregar el producto al carrito.');
            if (cartCount && Number.isFinite(Number(data.cart_item_count))) cartCount.textContent = data.cart_item_count;
            if (popup && showCartAdded) showCartAdded(data, form);
            else showToast(data.message || 'Producto agregado al carrito.');
        } catch (error) {
            showToast(error.message || 'No se pudo agregar el producto al carrito.', 'error');
        } finally {
            if (submit) submit.disabled = false;
        }
    };

    if (!popup) {
        document.addEventListener('submit', (event) => {
            const form = event.target.closest('[data-product-purchase-form][data-cart-async="true"]');
            if (!form) return;
            event.preventDefault();
            addToCart(form);
        });
        return;
    }

    const title = popup.querySelector('[data-popup-title]');
    const message = popup.querySelector('[data-popup-message]');
    const icon = popup.querySelector('[data-popup-icon]');
    const actions = popup.querySelector('[data-popup-actions]');
    const detail = popup.querySelector('[data-popup-detail]');
    let activeForm = null;
    let previousFocus = null;

    const close = () => {
        clearTimeout(popupTimer);
        popup.hidden = true;
        document.body.classList.remove('popup-open');
        activeForm = null;
        previousFocus?.focus?.();
    };

    const setType = (type) => {
        popup.classList.toggle('is-success', type === 'success');
        popup.classList.toggle('is-error', type === 'error');
        icon.innerHTML = type === 'success' ? '<i class="bi bi-check-circle-fill"></i>' : '<i class="bi bi-exclamation-circle-fill"></i>';
    };

    const button = (label, className, callback) => {
        const element = document.createElement('button');
        element.type = 'button';
        element.className = `btn ${className}`;
        element.textContent = label;
        element.addEventListener('click', callback);
        return element;
    };

    const show = ({ heading, text, type = 'info', popupActions = [], form = null, source = null }) => {
        previousFocus = source || document.activeElement;
        activeForm = form;
        title.textContent = heading;
        message.textContent = text;
        actions.replaceChildren(...popupActions);
        if (detail) {
            detail.hidden = true;
            detail.replaceChildren();
        }
        setType(type);
        popup.hidden = false;
        document.body.classList.add('popup-open');
        actions.querySelector('button, select, a')?.focus();
    };

    showCartAdded = (data) => {
        const cart = document.createElement('a');
        cart.href = data.links?.cart || '#';
        cart.className = 'btn btn-primary';
        cart.textContent = 'Ir al carrito';
        show({
            heading: '¡Producto agregado!',
            text: data.message || 'Producto agregado al carrito.',
            type: 'success',
            popupActions: [button('Seguir comprando', 'btn-ghost', close), cart],
        });
        if (detail) {
            detail.hidden = false;
            detail.replaceChildren();
            const product = data.product || {};
            if (product.image) {
                const image = document.createElement('img');
                image.src = product.image;
                image.alt = product.name || 'Producto agregado';
                detail.append(image);
            }
            const content = document.createElement('div');
            const name = document.createElement('strong');
            name.textContent = product.name || 'Producto agregado';
            content.append(name);
            if (product.brand || product.category) {
                const category = document.createElement('small');
                category.textContent = [product.brand, product.category].filter(Boolean).join(' · ');
                content.append(category);
            }
            if (product.variant) {
                const variant = document.createElement('small');
                variant.textContent = `Variante: ${product.variant}`;
                content.append(variant);
            }
            const price = document.createElement('small');
            price.textContent = `Precio unitario: S/ ${Number(product.unit_price || 0).toFixed(2)}`;
            content.append(price);
            const summary = document.createElement('span');
            summary.textContent = `${product.quantity || 1} unidad(es) · S/ ${Number(product.subtotal || 0).toFixed(2)}`;
            content.append(summary);
            if (Number.isFinite(Number(product.available_stock))) {
                const stock = document.createElement('small');
                stock.textContent = `${product.available_stock} disponibles`;
                content.append(stock);
            }
            detail.append(content);
        }
    };

    const submitPurchase = (buyNow) => {
        if (!activeForm) return;
        activeForm.querySelector('[data-popup-buy-now]')?.remove();
        if (buyNow) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'comprar_ahora';
            input.value = '1';
            input.dataset.popupBuyNow = 'true';
            activeForm.append(input);
        }
        const form = activeForm;
        activeForm = null;
        popup.hidden = true;
        document.body.classList.remove('popup-open');
        form.dataset.popupSubmitting = 'true';
        form.requestSubmit();
    };

    const showPurchase = (form, source, loginUrl = null, buyNow = false) => {
        if (loginUrl) {
            const login = document.createElement('a');
            login.href = loginUrl;
            login.className = 'btn btn-ghost';
            login.textContent = 'Iniciar sesión';
            show({ heading: '¿Cómo deseas continuar?', text: 'Puedes ingresar a tu cuenta o continuar como invitado.', form, source, popupActions: [login, button('Continuar como invitado', 'btn-primary', () => submitPurchase(buyNow))] });
            return;
        }
        show({ heading: '¿Qué deseas hacer?', text: 'Elige cómo quieres continuar con este producto.', form, source, popupActions: [button('Agregar al carrito', 'btn-primary', () => submitPurchase(false)), button('Comprar ahora', 'btn-success', () => submitPurchase(true))] });
    };

    const showCardCartChoice = (form, source, loginUrl = null) => {
        const variants = parseVariants(form);
        const select = variants.length > 0 ? document.createElement('select') : null;
        if (select) {
            select.className = 'cart-variant-select';
            select.required = true;
            select.innerHTML = '<option value="">Selecciona una variante</option>';
            variants.forEach((variant) => {
                const option = document.createElement('option');
                option.value = variant.id;
                option.textContent = variant.label;
                select.append(option);
            });
        }
        const add = button('Agregar al carrito', 'btn-primary', () => {
            if (select && !select.value) { select.focus(); return; }
            if (select) setVariant(form, select.value);
            close();
            addToCart(form);
        });
        const popupActions = select ? [select] : [];
        if (loginUrl) {
            const login = document.createElement('a');
            login.href = loginUrl;
            login.className = 'btn btn-ghost';
            login.textContent = 'Iniciar sesión';
            popupActions.push(login, add);
        } else popupActions.push(add);
        show({ heading: variants.length > 0 ? 'Selecciona una variante' : 'Agregar al carrito', text: loginUrl ? 'Puedes ingresar a tu cuenta o continuar como invitado.' : 'Confirma que deseas agregar este producto al carrito.', form, source, popupActions });
    };

    const showCheckoutChoice = (trigger) => {
        const guest = document.createElement('a');
        guest.href = trigger.href;
        guest.className = 'btn btn-primary';
        guest.textContent = 'Comprar como invitado';
        const login = document.createElement('a');
        login.href = trigger.dataset.loginUrl;
        login.className = 'btn btn-ghost';
        login.textContent = 'Iniciar sesión';
        const register = document.createElement('a');
        register.href = trigger.dataset.registerUrl;
        register.className = 'btn btn-ghost';
        register.textContent = 'Crear una cuenta';
        show({
            heading: '¿Cómo deseas continuar con tu compra?',
            text: 'Conservaremos los productos de tu carrito mientras eliges cómo continuar.',
            source: trigger,
            popupActions: [guest, login, register],
        });
    };

    const updateCartInterface = (data, row = null) => {
        if (row && data.removed) row.remove();
        if (row && !data.removed) {
            const subtotal = row.querySelector('[data-line-subtotal]');
            if (subtotal) subtotal.textContent = `S/ ${Number(data.subtotal || 0).toFixed(2)}`;
        }
        const total = document.querySelector('[data-cart-total]');
        if (total && Number.isFinite(Number(data.cart_total))) total.textContent = `S/ ${Number(data.cart_total).toFixed(2)}`;
        const grandTotal = document.querySelector('[data-cart-grand-total]');
        if (grandTotal && Number.isFinite(Number(data.cart_total))) grandTotal.textContent = `S/ ${Number(data.cart_total).toFixed(2)}`;
        if (cartCount && Number.isFinite(Number(data.cart_item_count))) cartCount.textContent = data.cart_item_count;
        if (!document.querySelector('[data-cart-line]')) {
            const table = document.querySelector('[data-cart-lines]');
            const wrapper = table?.closest('.table-wrap, .cart-lines-card');
            if (wrapper) {
                table.hidden = true;
                wrapper.querySelector('.cart-summary')?.setAttribute('hidden', 'hidden');
                const empty = document.createElement('p');
                empty.className = 'empty panel';
                empty.textContent = 'Tu carrito está vacío.';
                wrapper.prepend(empty);
            }
        }
    };

    const submitCartAction = async (form) => {
        const row = form.closest('[data-cart-line]');
        const submit = form.querySelector('button');
        if (submit) submit.disabled = true;
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'No se pudo actualizar el carrito.');
            updateCartInterface(data, row);
            showToast(data.message || 'Carrito actualizado.');
        } catch (error) {
            showToast(error.message || 'No se pudo actualizar el carrito.', 'error');
        } finally {
            if (submit) submit.disabled = false;
        }
    };

    const confirmCartRemoval = (form) => {
        show({
            heading: '¿Deseas eliminar este producto de tu carrito?',
            text: 'Podrás volver a agregarlo desde el catálogo.',
            type: 'info',
            source: form.querySelector('button'),
            popupActions: [
                button('Sí, eliminar', 'btn-primary', () => { close(); submitCartAction(form); }),
                button('Cancelar', 'btn-ghost', close),
            ],
        });
    };

    const confirmCheckout = (form) => {
        const total = form.closest('.auth-wrap')?.querySelector('.cart-total strong')?.textContent?.trim() || 'el importe mostrado';
        show({
            heading: '¿Confirmas tu pedido?',
            text: `Revisaremos stock, precios, entrega y pago antes de registrar la compra. Total: ${total}.`,
            type: 'info',
            source: form.querySelector('[data-checkout-confirm]'),
            popupActions: [
                button('Confirmar pedido', 'btn-primary', () => {
                    const checkoutForm = activeForm;
                    if (!checkoutForm) return;
                    checkoutForm.dataset.checkoutSubmitting = 'true';
                    show({ heading: 'Estamos procesando tu pedido', text: 'Por favor, no cierres esta ventana.', type: 'info', popupActions: [] });
                    checkoutForm.requestSubmit();
                }),
                button('Regresar y revisar', 'btn-ghost', close),
            ],
            form,
        });
    };

    popup.querySelectorAll('[data-popup-close]').forEach((element) => element.addEventListener('click', close));
    document.addEventListener('keydown', (event) => { if (event.key === 'Escape' && !popup.hidden) close(); });

    document.addEventListener('click', (event) => {
        const quantityStep = event.target.closest('[data-qty-step]');
        if (quantityStep) {
            const form = quantityStep.closest('[data-cart-action="update"]');
            const input = form?.querySelector('input[name="cantidad"]');
            if (input) {
                const next = Number(input.value) + Number(quantityStep.dataset.qtyStep);
                input.value = Math.min(Number(input.max || 100), Math.max(Number(input.min || 1), next));
                form.requestSubmit();
            }
            return;
        }
        const copyLink = event.target.closest('[data-copy-link]');
        if (copyLink) {
            event.preventDefault();
            navigator.clipboard?.writeText(window.location.href)
                .then(() => showToast('Enlace de seguimiento copiado.'))
                .catch(() => showToast('No se pudo copiar el enlace.', 'error'));
            return;
        }
        const checkoutTrigger = event.target.closest('[data-checkout-trigger]');
        if (checkoutTrigger) {
            event.preventDefault();
            showCheckoutChoice(checkoutTrigger);
            return;
        }
        const cartTrigger = event.target.closest('[data-cart-add-trigger]');
        if (cartTrigger) {
            const form = cartTrigger.closest('.catalog-product-actions, .home-product-actions, .related-card, .smartmatch-tarjeta, .comparison-card')?.querySelector('[data-product-purchase-form]');
            if (!form) return;
            event.preventDefault();
            if (parseVariants(form).length > 0) {
                showCardCartChoice(form, cartTrigger);
            } else {
                addToCart(form);
            }
            return;
        }
        const trigger = event.target.closest('[data-purchase-trigger]');
        if (!trigger) return;
        const form = trigger.closest('.catalog-product-actions, .home-product-actions, [data-product-detail]')?.querySelector('[data-product-purchase-form]');
        if (!form) return;
        event.preventDefault();
        showPurchase(form, trigger, trigger.dataset.loginUrl || null, trigger.dataset.purchaseBuyNow === '1');
    });

    document.addEventListener('submit', (event) => {
        const checkoutForm = event.target.closest('[data-checkout-form]');
        if (checkoutForm) {
            if (checkoutForm.dataset.checkoutSubmitting === 'true') {
                delete checkoutForm.dataset.checkoutSubmitting;
                return;
            }
            event.preventDefault();
            confirmCheckout(checkoutForm);
            return;
        }
        const cartForm = event.target.closest('[data-cart-action]');
        if (cartForm) {
            event.preventDefault();
            if (cartForm.dataset.cartAction === 'remove') confirmCartRemoval(cartForm);
            else submitCartAction(cartForm);
            return;
        }
        const form = event.target.closest('[data-product-purchase-form]');
        if (!form) return;
        if (form.dataset.popupSubmitting === 'true') { delete form.dataset.popupSubmitting; return; }
        event.preventDefault();
        if (form.dataset.cartAsync === 'true') {
            if (parseVariants(form).length > 0) showCardCartChoice(form, event.submitter || form.querySelector('button[type="submit"]'));
            else addToCart(form);
            return;
        }
        form.querySelector('[data-popup-buy-now]')?.remove();
        showPurchase(form, event.submitter || form.querySelector('button[type="submit"]'));
    });

    const flash = document.querySelector('[data-popup-flash]');
    if (flash) show({ heading: flash.dataset.popupType === 'error' ? 'No se pudo completar la operación' : 'Operación completada', text: flash.dataset.popupMessage || '', type: flash.dataset.popupType || 'success', popupActions: [button('Cerrar', 'btn-primary', close)] });
    if (flash) {
        actions.replaceChildren();
        popupTimer = setTimeout(close, 25000);
    }
})();
