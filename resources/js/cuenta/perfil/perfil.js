import './componentes/foto-perfil.js';
import './componentes/campos-dinamicos.js';

document.querySelectorAll('[data-profile-page]').forEach((page) => {
    const tabs = page.querySelectorAll('[data-profile-tab]');
    const panels = page.querySelectorAll('[data-profile-panel]');

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            const selected = tab.dataset.profileTab;
            tabs.forEach((item) => item.classList.toggle('is-active', item === tab));
            panels.forEach((panel) => {
                panel.hidden = panel.dataset.profilePanel !== selected;
                panel.classList.toggle('is-active', panel.dataset.profilePanel === selected);
            });
        });
    });
});
