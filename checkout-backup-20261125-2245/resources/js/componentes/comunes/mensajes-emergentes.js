/**
 * Mensajes, confirmaciones y agregado asíncrono al carrito de la tienda.
 */

(() => {
    const popup = document.querySelector('[data-popup]');
    const cartCount = document.querySelector('.nav-actions a[aria-label="Ver carrito"] sup');
    let toastTimer = null;

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
            showToast(data.message || 'Producto agregado al carrito.');
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
    let activeForm = null;
    let previousFocus = null;

    const close = () => {
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
        setType(type);
        popup.hidden = false;
        document.body.classList.add('popup-open');
        actions.querySelector('button, select, a')?.focus();
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

    popup.querySelectorAll('[data-popup-close]').forEach((element) => element.addEventListener('click', close));
    document.addEventListener('keydown', (event) => { if (event.key === 'Escape' && !popup.hidden) close(); });

    document.addEventListener('click', (event) => {
        const cartTrigger = event.target.closest('[data-cart-add-trigger]');
        if (cartTrigger) {
            const form = cartTrigger.closest('.catalog-product-actions, .home-product-actions, .related-card, .smartmatch-tarjeta, .comparison-card')?.querySelector('[data-product-purchase-form]');
            if (!form) return;
            event.preventDefault();
            showCardCartChoice(form, cartTrigger, cartTrigger.dataset.loginUrl || null);
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
})();
