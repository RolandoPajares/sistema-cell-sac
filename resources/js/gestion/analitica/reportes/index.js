/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/gestion/analitica/reportes/index.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

/**
 * Ejecuta la operación initializeReportes utilizada por el módulo JavaScript.
 *
 * @param {*} root - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
// Funcion: initializeReportes concentra una tarea reutilizable del modulo.
export const initializeReportes = (root) => {
    // Condicional: evalua "root && root.dataset.adminAnalysisReady !== 'true'" para decidir si ejecuta el bloque siguiente.
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
// Funcion: activate concentra una tarea reutilizable del modulo.
const activate = (view, updateUrl = true) => {
    // Funcion: selectedTab concentra una tarea reutilizable del modulo.
    const selectedTab = tabs.find((tab) => tab.dataset.reportTab === view);

    // Condicional: evalua "!selectedTab" para decidir si ejecuta el bloque siguiente.
    if (!selectedTab) {
        return;
    }

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    tabs.forEach((tab) => {
        const selected = tab === selectedTab;
        tab.classList.toggle('is-active', selected);
        tab.setAttribute('aria-selected', selected ? 'true' : 'false');
    });
    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    panels.forEach((panel) => {
        const selected = panel.dataset.reportPanel === view;
        panel.hidden = !selected;
        panel.classList.toggle('is-active', selected);
    });
    // Condicional: evalua "viewInput" para decidir si ejecuta el bloque siguiente.
    if (viewInput) {
        viewInput.value = view;
    }
    // Condicional: evalua "updateUrl" para decidir si ejecuta el bloque siguiente.
    if (updateUrl) {
        const url = new URL(window.location.href);
        url.searchParams.set('vista', view);
        window.history.replaceState({}, '', url);
    }
};

// Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
tabs.forEach((tab) => tab.addEventListener('click', () => activate(tab.dataset.reportTab)));
// Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
document.querySelectorAll('[data-report-tab-link]').forEach((link) => {
    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    link.addEventListener('click', () => activate(link.dataset.reportTabLink));
});
// Evento: escucha el evento submit y ejecuta la funcion asociada cuando ocurre.
form?.addEventListener('submit', () => {
    // Condicional: evalua "viewInput" para decidir si ejecuta el bloque siguiente.
    if (viewInput) {
        viewInput.value = document.querySelector('[data-report-tab].is-active')?.dataset.reportTab || 'resumen';
    }
});
// Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
period?.addEventListener('change', () => {
    // Condicional: evalua "period.value === 'custom'" para decidir si ejecuta el bloque siguiente.
    if (period.value === 'custom') {
        return;
    }
    // Condicional: evalua "period.value === 'all'" para decidir si ejecuta el bloque siguiente.
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
    // Funcion: toIso concentra una tarea reutilizable del modulo.
    const toIso = (date) => [date.getFullYear(), String(date.getMonth() + 1).padStart(2, '0'), String(date.getDate()).padStart(2, '0')].join('-');
    // Condicional: evalua "from && to" para decidir si ejecuta el bloque siguiente.
    if (from && to) {
        from.value = toIso(start);
        to.value = toIso(today);
    }
    form?.submit();
});
