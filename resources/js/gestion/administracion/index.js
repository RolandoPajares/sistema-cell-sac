/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/gestion/administracion/index.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

import { initializeIntegraciones } from './integraciones/index.js';
import { initializeNotificaciones } from './notificaciones/index.js';
import { initializeRespaldos } from './respaldos-mantenimiento/index.js';
import { initializeRolesPermisos } from './roles-permisos/index.js';

const initializers = {
    integraciones: initializeIntegraciones,
    notificaciones: initializeNotificaciones,
    'respaldos-mantenimiento': initializeRespaldos,
    'roles-permisos': initializeRolesPermisos,
};

// Recorrido: usa forEach para procesar cada elemento y obtener o actualizar datos.
document.querySelectorAll('[data-admin-system-module]').forEach((root) => {
    initializers[root.dataset.adminSystemModule]?.(root);
});