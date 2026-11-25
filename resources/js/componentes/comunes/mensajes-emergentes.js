/**
 * Mensajes y confirmaciones emergentes de la tienda.
 */

(() => {
    const popup = document.querySelector('[data-popup]');
    if (!popup) return;

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
        icon.innerHTML = type === 'success'
            ? '<i class="bi bi-check-circle-fill"></i>'
            : '<i class="bi bi-exclamation-circle-fill"></i>';
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
        actions.querySelector('button')?.focus();
    };

    const submitPurchase = (buyNow) => {
        if (!activeForm) return;
        const existing = activeForm.querySelector('[data-popup-buy-now]');
        existing?.remove();
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
            show({
                heading: '¿Cómo deseas continuar?',
                text: 'Puedes ingresar a tu cuenta o continuar como invitado.',
                type: 'info',
                form,
                source,
                popupActions: [
                    login,
                    button('Continuar como invitado', 'btn-primary', () => submitPurchase(buyNow)),
                ],
            });
            return;
        }

        show({
            heading: '¿Qué deseas hacer?',
            text: 'Elige cómo quieres continuar con este producto.',
            type: 'info',
            form,
            source,
            popupActions: [
                button('Agregar al carrito', 'btn-primary', () => submitPurchase(false)),
                button('Comprar ahora', 'btn-success', () => submitPurchase(true)),
            ],
        });
    };

    popup.querySelectorAll('[data-popup-close]').forEach((element) => element.addEventListener('click', close));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !popup.hidden) close();
    });

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-purchase-trigger]');
        if (!trigger) return;
        const form = trigger.closest('.catalog-product-actions, [data-product-detail]')?.querySelector('[data-product-purchase-form]');
        if (!form) return;
        event.preventDefault();
        showPurchase(form, trigger, trigger.dataset.loginUrl || null, trigger.dataset.purchaseBuyNow === '1');
    });

    document.addEventListener('submit', (event) => {
        const form = event.target.closest('[data-product-purchase-form]');
        if (!form) return;
        if (form.dataset.popupSubmitting === 'true') {
            delete form.dataset.popupSubmitting;
            return;
        }
        event.preventDefault();
        form.querySelector('[data-popup-buy-now]')?.remove();
        showPurchase(form, event.submitter || form.querySelector('button[type="submit"]'));
    });

    const flash = document.querySelector('[data-popup-flash]');
    if (flash) {
        show({
            heading: flash.dataset.popupType === 'error' ? 'No se pudo completar la operación' : 'Operación completada',
            text: flash.dataset.popupMessage || '',
            type: flash.dataset.popupType || 'success',
            popupActions: [button('Cerrar', 'btn-primary', close)],
        });
    }
})();
