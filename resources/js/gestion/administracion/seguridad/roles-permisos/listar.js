/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/gestion/administracion/seguridad/roles-permisos/listar.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

/**
 * Interacciones del módulo de roles y permisos: listar.
 */

// Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
(() => { const page = document.querySelector('[data-rbac-page]'); if (!page) return; page.querySelectorAll('[data-open-dialog]').forEach((button) => button.addEventListener('click', () => document.getElementById(button.dataset.openDialog)?.showModal())); document.querySelectorAll('[data-close-dialog]').forEach((button) => button.addEventListener('click', () => button.closest('dialog')?.close())); })();

const roleName = document.querySelector('[data-role-name]');
const roleCode = document.querySelector('[data-role-code]');

// Condicional: evalua "roleName && roleCode" para decidir si ejecuta el bloque siguiente.
if (roleName && roleCode) {
    // Funcion: updateRoleCode concentra una tarea reutilizable del modulo.
    const updateRoleCode = () => {
        roleCode.value = roleName.value
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9]+/g, '_')
            .replace(/^_+|_+$/g, '')
            .slice(0, 40) || 'rol_nuevo';
    };

    // Evento: escucha el evento input y ejecuta la funcion asociada cuando ocurre.
    roleName.addEventListener('input', updateRoleCode);
    updateRoleCode();
}

