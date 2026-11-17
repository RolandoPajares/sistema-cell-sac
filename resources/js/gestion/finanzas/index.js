/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/gestion/finanzas/index.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

import { initializeFacturacion } from './comprobantes/index.js';
import './finanzas-facturacion/finanzas-facturacion.js';
import { initializePagos } from './pagos-cobros/index.js';
import { initializeReembolsos } from './reembolsos/index.js';

const initializers = {
    facturacion: initializeFacturacion,
    pagos: initializePagos,
    reembolsos: initializeReembolsos,
};

// Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
document.querySelectorAll('[data-finance-module]').forEach((root) => {
    initializers[root.dataset.financeModule]?.(root);
});
