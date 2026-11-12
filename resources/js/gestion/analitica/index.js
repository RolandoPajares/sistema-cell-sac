/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/gestion/analitica/index.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

import { initializeAnaliticaClientes } from './clientes/index.js';
import { initializeMetricasMarketing } from './metricas-marketing/index.js';
import { initializeReportes } from './reportes/index.js';
import { initializeReportesFinancieros } from './reportes-financieros/index.js';

const initializers = {
    'analitica-clientes': initializeAnaliticaClientes,
    'metricas-marketing': initializeMetricasMarketing,
    'reportes-financieros': initializeReportesFinancieros,
};

// Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
document.querySelectorAll('[data-admin-analysis]').forEach((root) => {
    const initialize = initializers[root.dataset.adminAnalysis];

    initialize?.(root);

    const form = root.querySelector('[data-analysis-filter-form]');
    const feedback = root.querySelector('[data-analysis-feedback]');

    // Condicional: evalua "!form || root.dataset.adminAnalysisBehaviorReady === 'true'" para decidir si ejecuta el bloque siguiente.
    if (!form || root.dataset.adminAnalysisBehaviorReady === 'true') {
        return;
    }

    root.dataset.adminAnalysisBehaviorReady = 'true';

/**
 * Recupera y presenta el detalle de la información solicitada.
 *
 * @param {*} message - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
    // Funcion: showFeedback concentra una tarea reutilizable del modulo.
    const showFeedback = (message) => {
        // Condicional: evalua "!feedback" para decidir si ejecuta el bloque siguiente.
        if (!feedback) {
            return;
        }

        feedback.textContent = message;
        feedback.hidden = false;
        window.setTimeout(() => {
            feedback.hidden = true;
        }, 3800);
    };

/**
 * Gestiona la interacción y actualiza el estado visual correspondiente.
 *
 * @param {*} modal - Parámetro recibido por la función.
 *
 * @returns {*} Resultado de la operación.
 */
    // Funcion: closeModal concentra una tarea reutilizable del modulo.
    const closeModal = (modal) => {
        // Condicional: evalua "modal?.open" para decidir si ejecuta el bloque siguiente.
        if (modal?.open) {
            modal.close();
        }
    };

    // Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
    root.addEventListener('click', (event) => {
        const opener = event.target.closest('[data-analysis-open]');
        const closer = event.target.closest('[data-analysis-close]');
        const notice = event.target.closest('[data-analysis-notice]');
        const dialog = event.target.closest('dialog[data-analysis-modal]');

        // Condicional: evalua "opener && root.contains(opener)" para decidir si ejecuta el bloque siguiente.
        if (opener && root.contains(opener)) {
            const modal = root.querySelector(`[data-analysis-modal="${opener.dataset.analysisOpen}"]`);

            // Condicional: evalua "modal?.showModal" para decidir si ejecuta el bloque siguiente.
            if (modal?.showModal) {
                modal.showModal();
                modal.querySelector('select, [data-analysis-close]')?.focus();
            }
            return;
        }

        // Condicional: evalua "closer && root.contains(closer)" para decidir si ejecuta el bloque siguiente.
        if (closer && root.contains(closer)) {
            closeModal(closer.closest('dialog'));
            return;
        }

        // Condicional: evalua "notice && root.contains(notice)" para decidir si ejecuta el bloque siguiente.
        if (notice && root.contains(notice)) {
            showFeedback(notice.dataset.analysisNotice || 'Esta acción queda pendiente de integración.');
            return;
        }

        // Condicional: evalua "dialog && event.target === dialog" para decidir si ejecuta el bloque siguiente.
        if (dialog && event.target === dialog) {
            closeModal(dialog);
        }
    });

    // Evento: escucha el evento submit y ejecuta la funcion asociada cuando ocurre.
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        showFeedback('Los filtros se aplicarán cuando exista una fuente de datos integrada.');
    });
});

const reportesRoot = document.querySelector('[data-reportes-page]');

// Condicional: evalua "reportesRoot" para decidir si ejecuta el bloque siguiente.
if (reportesRoot) {
    initializeReportes(reportesRoot);
}
