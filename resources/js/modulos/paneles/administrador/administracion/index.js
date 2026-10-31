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

document.querySelectorAll('[data-admin-system-module]').forEach((root) => {
    initializers[root.dataset.adminSystemModule]?.(root);
});
