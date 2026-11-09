/**
 * Interacciones del módulo de roles y permisos: asignaciones.
 */

(() => { const page=document.querySelector('[data-assignment-page]'); if(!page)return; const open=(id)=>{const d=document.getElementById(id);if(d&&!d.open)d.showModal()}; page.querySelectorAll('[data-open-dialog]').forEach(b=>b.addEventListener('click',()=>open(b.dataset.openDialog))); document.querySelectorAll('[data-close-dialog]').forEach(b=>b.addEventListener('click',()=>b.closest('dialog')?.close())); document.querySelectorAll('.tc-rbac-dialog').forEach(d=>d.addEventListener('click',e=>{if(e.target===d)d.close()})); page.querySelector('[data-assignment-filter]')?.addEventListener('input',(e)=>{const v=e.target.value.toLowerCase().trim();page.querySelectorAll('[data-assignment-row]').forEach(r=>{r.hidden=v!==''&&!r.dataset.search.includes(v)})}); })();

