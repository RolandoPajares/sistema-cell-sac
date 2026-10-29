import '../../../../css/paneles/administrador/index.css';
import './componentes/menu-lateral-desplegable.js';
import './administracion/index.js';
import './analisis/index.js';
import './configuracion/index.js';
import './finanzas/index.js';
import '../../gestion/ventas/index.js';
import '../../gestion/modulos-pendientes.js';

const dashboard = document.querySelector('[data-admin-dashboard]');

if (dashboard) {
    const salesChart = dashboard.querySelector('[data-dashboard-sales-chart]');
    const rangeSelect = dashboard.querySelector('[data-dashboard-range]');
    const salesLine = dashboard.querySelector('[data-dashboard-sales-line]');
    const salesColumns = [...dashboard.querySelectorAll('[data-sales-month]')];

    const renderSalesLine = () => {
        if (!salesChart || !salesLine || salesColumns.length === 0) {
            return;
        }

        const visibleColumns = salesColumns.filter((column) => !column.hidden);
        const values = visibleColumns.map((column) => Number(column.dataset.value ?? 0));
        const maximum = Number(salesChart.dataset.chartMax ?? Math.max(...values, 1));
        const step = visibleColumns.length > 1 ? 960 / (visibleColumns.length - 1) : 0;
        const points = visibleColumns.map((column, index) => {
            const x = 20 + (step * index);
            const y = 145 - ((Number(column.dataset.value ?? 0) / maximum) * 120);

            return `${x},${y}`;
        });

        salesLine.setAttribute('points', points.join(' '));
        salesChart.querySelectorAll('[data-dashboard-line-point]').forEach((point) => point.remove());

        visibleColumns.forEach((column, index) => {
            const point = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
            const value = Number(column.dataset.value ?? 0);

            point.dataset.dashboardLinePoint = 'true';
            point.setAttribute('cx', String(20 + (step * index)));
            point.setAttribute('cy', String(145 - ((value / maximum) * 120)));
            point.setAttribute('r', '5');
            point.setAttribute('fill', '#0875ed');
            point.setAttribute('stroke', '#ffffff');
            point.setAttribute('stroke-width', '3');
            salesLine.parentElement?.appendChild(point);
        });
    };

    const applySalesRange = () => {
        const range = Number(rangeSelect?.value ?? salesColumns.length);
        const firstVisibleIndex = Math.max(0, salesColumns.length - range);

        salesColumns.forEach((column, index) => {
            column.hidden = index < firstVisibleIndex;
        });

        renderSalesLine();
    };

    rangeSelect?.addEventListener('change', applySalesRange);
    window.addEventListener('resize', renderSalesLine, { passive: true });
    applySalesRange();

    dashboard.querySelectorAll('[data-dashboard-progress]').forEach((progress) => {
        const width = progress.style.getPropertyValue('--progress');

        progress.style.setProperty('--progress', '0%');
        window.requestAnimationFrame(() => {
            progress.style.setProperty('--progress', width);
        });
    });
}
