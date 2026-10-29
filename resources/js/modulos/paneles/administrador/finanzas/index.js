import { initializeFacturacion } from './facturacion/index.js';
import { initializePagos } from './pagos/index.js';
import { initializeReembolsos } from './reembolsos/index.js';

const initializers = {
    facturacion: initializeFacturacion,
    pagos: initializePagos,
    reembolsos: initializeReembolsos,
};

document.querySelectorAll('[data-finance-module]').forEach((root) => {
    initializers[root.dataset.financeModule]?.(root);
});
