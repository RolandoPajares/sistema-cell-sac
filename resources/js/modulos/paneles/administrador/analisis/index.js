import { initializeAnaliticaClientes } from './analitica-clientes/index.js';
import { initializeMetricasMarketing } from './metricas-marketing/index.js';
import { initializeReportes } from './reportes/index.js';
import { initializeReportesFinancieros } from './reportes-financieros/index.js';

const initializers = {
    'analitica-clientes': initializeAnaliticaClientes,
    'metricas-marketing': initializeMetricasMarketing,
    'reportes-financieros': initializeReportesFinancieros,
};

document.querySelectorAll('[data-admin-analysis]').forEach((root) => {
    const initialize = initializers[root.dataset.adminAnalysis];

    initialize?.(root);

    const form = root.querySelector('[data-analysis-filter-form]');
    const feedback = root.querySelector('[data-analysis-feedback]');

    if (!form || root.dataset.adminAnalysisBehaviorReady === 'true') {
        return;
    }

    root.dataset.adminAnalysisBehaviorReady = 'true';

    const showFeedback = (message) => {
        if (!feedback) {
            return;
        }

        feedback.textContent = message;
        feedback.hidden = false;
        window.setTimeout(() => {
            feedback.hidden = true;
        }, 3800);
    };

    const closeModal = (modal) => {
        if (modal?.open) {
            modal.close();
        }
    };

    root.addEventListener('click', (event) => {
        const opener = event.target.closest('[data-analysis-open]');
        const closer = event.target.closest('[data-analysis-close]');
        const notice = event.target.closest('[data-analysis-notice]');
        const dialog = event.target.closest('dialog[data-analysis-modal]');

        if (opener && root.contains(opener)) {
            const modal = root.querySelector(`[data-analysis-modal="${opener.dataset.analysisOpen}"]`);

            if (modal?.showModal) {
                modal.showModal();
                modal.querySelector('select, [data-analysis-close]')?.focus();
            }
            return;
        }

        if (closer && root.contains(closer)) {
            closeModal(closer.closest('dialog'));
            return;
        }

        if (notice && root.contains(notice)) {
            showFeedback(notice.dataset.analysisNotice || 'Esta acción queda pendiente de integración.');
            return;
        }

        if (dialog && event.target === dialog) {
            closeModal(dialog);
        }
    });

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        showFeedback('Los filtros se aplicarán cuando exista una fuente de datos integrada.');
    });
});

const reportesRoot = document.querySelector('[data-reportes-page]');

if (reportesRoot) {
    initializeReportes(reportesRoot);
}
