const page = document.querySelector('[data-menu-page]');

if (page) {
    const open = (id) => {
        const dialog = document.getElementById(id);
        if (dialog && !dialog.open) {
            dialog.showModal();
        }
    };

    page.querySelectorAll('[data-open-dialog]').forEach((button) => {
        button.addEventListener('click', () => open(button.dataset.openDialog));
    });

    document.querySelectorAll('[data-close-dialog]').forEach((button) => {
        button.addEventListener('click', () => button.closest('dialog')?.close());
    });

    document.querySelectorAll('.tc-rbac-dialog').forEach((dialog) => {
        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) {
                dialog.close();
            }
        });
    });

    const descendants = (key) => [...page.querySelectorAll(`[data-tree-parent="${key}"]`)];
    const setExpanded = (key, expanded) => {
        descendants(key).forEach((row) => {
            row.hidden = !expanded;
            if (!expanded && row.dataset.treeKey) {
                setExpanded(row.dataset.treeKey, false);
            }
        });
    };

    page.querySelectorAll('[data-tree-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const expanded = button.getAttribute('aria-expanded') !== 'true';
            button.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            const icon = button.querySelector('i');
            icon?.classList.toggle('bi-chevron-down', expanded);
            icon?.classList.toggle('bi-chevron-right', !expanded);
            setExpanded(button.dataset.treeToggle, expanded);
        });
    });

    const filter = page.querySelector('[data-menu-filter]');
    const status = page.querySelector('[data-menu-status]');
    const applyFilters = () => {
        const value = (filter?.value || '').toLowerCase().trim();
        const state = status?.value || '';
        page.querySelectorAll('[data-menu-row]').forEach((row) => {
            row.hidden = (value !== '' && !row.dataset.search.includes(value))
                || (state !== '' && row.dataset.status !== state);
        });
    };

    filter?.addEventListener('input', applyFilters);
    status?.addEventListener('change', applyFilters);
}
