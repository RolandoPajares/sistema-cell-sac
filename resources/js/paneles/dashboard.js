/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/paneles/dashboard.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

import '../../css/paneles/dashboard.css';
import '../layouts/panel/menu-lateral-desplegable.js';
import '../gestion/administracion/organizacion/organizacion.js';
import '../gestion/comercial/ventas/operaciones-ventas.js';
import '../gestion/inventario-logistica/inventario-logistica/inventario-logistica.js';
import '../gestion/inventario-logistica/transferencias/transferencias.js';
import '../gestion/finanzas/finanzas-facturacion/finanzas-facturacion.js';
import '../gestion/ecommerce-marketing/ecommerce-marketing.js';
import '../gestion/analitica/analisis-auditoria/analisis-auditoria.js';
import '../gestion/configuracion/configuracion-sistema.js';
import '../gestion/administracion/index.js';
import '../gestion/analitica/index.js';
import '../gestion/configuracion/index.js';
import '../gestion/finanzas/index.js';
import '../gestion/comercial/ventas/index.js';
import '../modulos/gestion/modulos-pendientes.js';

const dashboard = document.querySelector('[data-admin-dashboard]');

// Condicional: evalua "dashboard" para decidir si ejecuta el bloque siguiente.
if (dashboard) {
    const salesChart = dashboard.querySelector('[data-dashboard-sales-chart]');
    const rangeSelect = dashboard.querySelector('[data-dashboard-range]');
    const salesLine = dashboard.querySelector('[data-dashboard-sales-line]');
    const salesColumns = [...dashboard.querySelectorAll('[data-sales-month]')];

/**
 * Ejecuta la operación renderSalesLine utilizada por el módulo JavaScript.
 *
 * @returns {*} Resultado de la operación.
 */
    // Funcion: renderSalesLine concentra una tarea reutilizable del modulo.
    const renderSalesLine = () => {
        // Condicional: evalua "!salesChart || !salesLine || salesColumns.length === 0" para decidir si ejecuta el bloque siguiente.
        if (!salesChart || !salesLine || salesColumns.length === 0) {
            return;
        }

        // Recorrido: usa filter para procesar cada elemento y obtener o actualizar datos.
        const visibleColumns = salesColumns.filter((column) => !column.hidden);
        // Recorrido: usa map para procesar cada elemento y obtener o actualizar datos.
        const values = visibleColumns.map((column) => Number(column.dataset.value ?? 0));
        const maximum = Number(salesChart.dataset.chartMax ?? Math.max(...values, 1));
        const step = visibleColumns.length > 1 ? 960 / (visibleColumns.length - 1) : 0;
        // Recorrido: usa map para procesar cada elemento y obtener o actualizar datos.
        const points = visibleColumns.map((column, index) => {
            const x = 20 + (step * index);
            const y = 145 - ((Number(column.dataset.value ?? 0) / maximum) * 120);

            return `${x},${y}`;
        });

        salesLine.setAttribute('points', points.join(' '));
        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        salesChart.querySelectorAll('[data-dashboard-line-point]').forEach((point) => point.remove());

        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
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

/**
 * Gestiona la interacción y actualiza el estado visual correspondiente.
 *
 * @returns {*} Resultado de la operación.
 */
    // Funcion: applySalesRange concentra una tarea reutilizable del modulo.
    const applySalesRange = () => {
        const range = Number(rangeSelect?.value ?? salesColumns.length);
        const firstVisibleIndex = Math.max(0, salesColumns.length - range);

        // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
        salesColumns.forEach((column, index) => {
            column.hidden = index < firstVisibleIndex;
        });

        renderSalesLine();
    };

    // Evento: escucha el evento change y ejecuta la funcion asociada cuando ocurre.
    rangeSelect?.addEventListener('change', applySalesRange);
    // Evento: escucha el evento resize y ejecuta la funcion asociada cuando ocurre.
    window.addEventListener('resize', renderSalesLine, { passive: true });
    applySalesRange();

    // Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
    dashboard.querySelectorAll('[data-dashboard-progress]').forEach((progress) => {
        const width = progress.style.getPropertyValue('--progress');

        progress.style.setProperty('--progress', '0%');
        window.requestAnimationFrame(() => {
            progress.style.setProperty('--progress', width);
        });
    });
}
