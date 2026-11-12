/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/gestion/administracion/seguridad/roles-permisos/modulos.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

/**
 * Interacciones del módulo de roles y permisos: modulos.
 */

// Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
(()=>{const page=document.querySelector('[data-module-page]');if(!page)return;page.querySelectorAll('[data-open-dialog]').forEach(b=>b.addEventListener('click',()=>{const d=document.getElementById(b.dataset.openDialog);if(d&&!d.open)d.showModal()}));document.querySelectorAll('[data-close-dialog]').forEach(b=>b.addEventListener('click',()=>b.closest('dialog')?.close()));document.querySelectorAll('.tc-rbac-dialog').forEach(d=>d.addEventListener('click',e=>{if(e.target===d)d.close()}));page.querySelector('[data-module-filter]')?.addEventListener('input',e=>{const v=e.target.value.toLowerCase().trim();page.querySelectorAll('[data-module-row]').forEach(r=>r.hidden=v!==''&&!r.dataset.search.includes(v))});})();

