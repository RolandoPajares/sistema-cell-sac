/**
 * Interacciones del módulo de roles y permisos: listar.
 */

(() => { const page = document.querySelector('[data-rbac-page]'); if (!page) return; page.querySelectorAll('[data-open-dialog]').forEach((button) => button.addEventListener('click', () => document.getElementById(button.dataset.openDialog)?.showModal())); document.querySelectorAll('[data-close-dialog]').forEach((button) => button.addEventListener('click', () => button.closest('dialog')?.close())); })();

const roleName = document.querySelector('[data-role-name]');
const roleCode = document.querySelector('[data-role-code]');

if (roleName && roleCode) {
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

    roleName.addEventListener('input', updateRoleCode);
    updateRoleCode();
}

