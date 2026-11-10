/**
 * Interacciones del módulo de roles y permisos: modulos.
 */

(()=>{const page=document.querySelector('[data-module-page]');if(!page)return;page.querySelectorAll('[data-open-dialog]').forEach(b=>b.addEventListener('click',()=>{const d=document.getElementById(b.dataset.openDialog);if(d&&!d.open)d.showModal()}));document.querySelectorAll('[data-close-dialog]').forEach(b=>b.addEventListener('click',()=>b.closest('dialog')?.close()));document.querySelectorAll('.tc-rbac-dialog').forEach(d=>d.addEventListener('click',e=>{if(e.target===d)d.close()}));page.querySelector('[data-module-filter]')?.addEventListener('input',e=>{const v=e.target.value.toLowerCase().trim();page.querySelectorAll('[data-module-row]').forEach(r=>r.hidden=v!==''&&!r.dataset.search.includes(v))});})();

