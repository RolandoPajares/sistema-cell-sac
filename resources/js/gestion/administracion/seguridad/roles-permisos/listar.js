/**
 * Interacciones del módulo de roles y permisos: listar.
 */

(() => { const page = document.querySelector('[data-rbac-page]'); if (!page) return; page.querySelectorAll('[data-open-dialog]').forEach((button) => button.addEventListener('click', () => document.getElementById(button.dataset.openDialog)?.showModal())); document.querySelectorAll('[data-close-dialog]').forEach((button) => button.addEventListener('click', () => button.closest('dialog')?.close())); })();

