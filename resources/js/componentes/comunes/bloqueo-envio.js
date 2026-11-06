/** Evita dobles envíos y deja visible el estado de guardado en formularios administrativos. */
document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || form.dataset.submitLocked === '1') {
        return;
    }

    if (!form.closest('.modulo-seguridad-rbac, .modulo-usuarios')) {
        return;
    }

    form.dataset.submitLocked = '1';
    form.setAttribute('aria-busy', 'true');
    form.querySelectorAll('button[type="submit"]').forEach((button) => {
        button.disabled = true;
        button.dataset.originalLabel = button.innerHTML;
        button.innerHTML = '<i class="bi bi-arrow-repeat" aria-hidden="true"></i> Guardando…';
    });
});
