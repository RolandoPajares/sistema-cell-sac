/**
 * Ejecuta la operación initializeReportes utilizada por el módulo JavaScript.
 *
 * @param {*} root - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
export const initializeReportes = (root) => {
    if (root && root.dataset.adminAnalysisReady !== 'true') {
        root.dataset.adminAnalysisReady = 'true';
    }
};

const form = document.querySelector('[data-report-filter-form]');
const tabs = [...document.querySelectorAll('[data-report-tab]')];
const panels = [...document.querySelectorAll('[data-report-panel]')];
const viewInput = form?.querySelector('[data-report-view]');
const period = form?.querySelector('[data-report-period]');
const from = form?.querySelector('[data-report-from]');
const to = form?.querySelector('[data-report-to]');

// Cambia la pestaña visible sin modificar los datos entregados por el servidor.
const activate = (view, updateUrl = true) => {
    const selectedTab = tabs.find((tab) => tab.dataset.reportTab === view);

    if (!selectedTab) {
        return;
    }

    tabs.forEach((tab) => {
        const selected = tab === selectedTab;
        tab.classList.toggle('is-active', selected);
        tab.setAttribute('aria-selected', selected ? 'true' : 'false');
    });
    panels.forEach((panel) => {
        const selected = panel.dataset.reportPanel === view;
        panel.hidden = !selected;
        panel.classList.toggle('is-active', selected);
    });
    if (viewInput) {
        viewInput.value = view;
    }
    if (updateUrl) {
        const url = new URL(window.location.href);
        url.searchParams.set('vista', view);
        window.history.replaceState({}, '', url);
    }
};

tabs.forEach((tab) => tab.addEventListener('click', () => activate(tab.dataset.reportTab)));
document.querySelectorAll('[data-report-tab-link]').forEach((link) => {
    link.addEventListener('click', () => activate(link.dataset.reportTabLink));
});
form?.addEventListener('submit', () => {
    if (viewInput) {
        viewInput.value = document.querySelector('[data-report-tab].is-active')?.dataset.reportTab || 'resumen';
    }
});
period?.addEventListener('change', () => {
    if (period.value === 'custom') {
        return;
    }
    if (period.value === 'all') {
        from?.removeAttribute('name');
        to?.removeAttribute('name');
        form?.submit();
        return;
    }
    const days = Number(period.value);
    const today = new Date();
    const start = new Date(today);
    start.setDate(today.getDate() - (days - 1));
    const toIso = (date) => [date.getFullYear(), String(date.getMonth() + 1).padStart(2, '0'), String(date.getDate()).padStart(2, '0')].join('-');
    if (from && to) {
        from.value = toIso(start);
        to.value = toIso(today);
    }
    form?.submit();
});
