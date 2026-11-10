const dialog = document.querySelector('[data-profile-photo-dialog]');
const openButton = document.querySelector('[data-profile-photo-open]');
const photoInput = document.querySelector('[data-profile-photo-input]');

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

photoInput?.addEventListener('change', () => {
    if (photoInput.files?.length > 0) {
        photoInput.form?.submit();
    }
});
