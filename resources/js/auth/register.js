const selector = document.querySelector('[data-customer-type]');
const field = document.querySelector('[data-wholesale-field]');
const ruc = document.querySelector('#ruc');

if (selector && field && ruc) {
    const update = () => {
        const wholesale = selector.value === 'mayorista';
        field.hidden = !wholesale;
        ruc.required = wholesale;

        if (!wholesale) {
            ruc.value = '';
        }
    };

    selector.addEventListener('change', update);
    update();
}
