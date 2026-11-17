/**
 * GUIA DE EXPOSICION JAVASCRIPT
 * Archivo: resources/js/gestion/administracion/seguridad/roles-permisos/asignaciones.js
 * Este modulo contiene comportamiento de interfaz, eventos, validaciones y/o
 * comunicacion con el backend. Los comentarios explican el flujo sin cambiar la logica.
 */

/**
 * Interacciones del módulo de roles y permisos: asignaciones.
 */

// Evento: escucha el evento click y ejecuta la funcion asociada cuando ocurre.
(() => { const page=document.querySelector('[data-assignment-page]'); if(!page)return; const open=(id)=>{const d=document.getElementById(id);if(d&&!d.open)d.showModal()}; page.querySelectorAll('[data-open-dialog]').forEach(b=>b.addEventListener('click',()=>open(b.dataset.openDialog))); document.querySelectorAll('[data-close-dialog]').forEach(b=>b.addEventListener('click',()=>b.closest('dialog')?.close())); document.querySelectorAll('.tc-rbac-dialog').forEach(d=>d.addEventListener('click',e=>{if(e.target===d)d.close()})); page.querySelector('[data-assignment-filter]')?.addEventListener('input',(e)=>{const v=e.target.value.toLowerCase().trim();page.querySelectorAll('[data-assignment-row]').forEach(r=>{r.hidden=v!==''&&!r.dataset.search.includes(v)})}); })();

