import { initializeFacturacion } from './comprobantes/index.js';
import './finanzas-facturacion/finanzas-facturacion.js';
import { initializePagos } from './pagos-cobros/index.js';
import { initializeReembolsos } from './reembolsos/index.js';

const initializers = {
    facturacion: initializeFacturacion,
    pagos: initializePagos,
    reembolsos: initializeReembolsos,
};

document.querySelectorAll('[data-finance-module]').forEach((root) => {
    initializers[root.dataset.financeModule]?.(root);
});
