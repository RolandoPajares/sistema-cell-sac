import './legacy/administrador.js';
import './legacy/david/panel.js';

document.addEventListener('DOMContentLoaded', () => {
    const token = document.querySelector('meta[name="csrf-token"]')?.content;

    document.querySelectorAll('[data-campaign-view]').forEach((element) => {
        if (!token) {
            return;
        }

        fetch(element.dataset.campaignView, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': token,
                Accept: 'application/json',
            },
            body: new URLSearchParams({ tipo: 'vista' }),
            keepalive: true,
        }).catch(() => {});
    });
});
