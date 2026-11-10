const dialog = document.querySelector('[data-profile-photo-dialog]');
const openButton = document.querySelector('[data-profile-photo-open]');

if (dialog && openButton) {
    openButton.addEventListener('click', () => dialog.showModal());

    dialog.querySelectorAll('[data-profile-photo-close]').forEach((button) => {
        button.addEventListener('click', () => dialog.close());
    });

    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            dialog.close();
        }
    });
}
