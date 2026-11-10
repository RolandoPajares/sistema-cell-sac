const openCampaignDialog = (dialog) => {
    if (dialog && !dialog.open) {
        dialog.showModal();
    }
};

const closeCampaignDialog = (dialog) => {
    if (dialog?.open) {
        dialog.close();
    }
};

const initializeCampaignForms = (page) => {
    page.querySelectorAll('[data-campaign-form]').forEach((form) => {
        const fileInput = form.querySelector('[data-campaign-image-input]');
        const urlInput = form.querySelector('[data-campaign-image-url]');
        const preview = form.querySelector('[data-campaign-preview]');
        const previewImage = form.querySelector('[data-campaign-preview-image]');
        const destination = form.querySelector('[data-campaign-destination]');
        const allSegments = form.querySelector('[data-campaign-all]');
        const segments = [...form.querySelectorAll('input[name="segmentos[]"]')];

        const showPreview = (source) => {
            if (!source || !previewImage || !preview) {
                return;
            }

            previewImage.src = source;
            preview.hidden = false;
        };

        fileInput?.addEventListener('change', () => {
            const file = fileInput.files?.[0];

            if (file) {
                showPreview(URL.createObjectURL(file));
            }
        });
        urlInput?.addEventListener('input', () => showPreview(urlInput.value.trim()));
        destination?.addEventListener('change', () => {
            if (urlInput && destination.value) {
                urlInput.value = destination.value;
            }
        });
        form.querySelector('[data-clear-campaign-image]')?.addEventListener('click', () => {
            if (fileInput) {
                fileInput.value = '';
            }

            if (urlInput) {
                urlInput.value = '';
            }

            if (preview) {
                preview.hidden = true;
            }
        });
        allSegments?.addEventListener('change', () => {
            if (allSegments.checked) {
                segments.forEach((checkbox) => { checkbox.checked = false; });
            }
        });
        segments.forEach((checkbox) => checkbox.addEventListener('change', () => {
            if (checkbox.checked && allSegments) {
                allSegments.checked = false;
            }

            if (allSegments && !segments.some((item) => item.checked)) {
                allSegments.checked = true;
            }
        }));
    });
};

document.querySelectorAll('[data-campanias-page]').forEach((page) => {
    page.querySelectorAll('[data-open-campaign-create]').forEach((button) => {
        button.addEventListener('click', () => openCampaignDialog(document.querySelector('#nueva-campania')));
    });
    page.querySelector('[data-open-campaign-import]')?.addEventListener('click', () => {
        openCampaignDialog(document.querySelector('#importar-campanias'));
    });
    page.querySelectorAll('[data-open-campaign-edit]').forEach((button) => {
        button.addEventListener('click', () => openCampaignDialog(document.querySelector(`#editar-campania-${button.dataset.openCampaignEdit}`)));
    });
    page.querySelectorAll('[data-open-campaign-view]').forEach((button) => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            openCampaignDialog(document.querySelector(`#campania-detalle-${button.dataset.openCampaignView}`));
        });
    });
    page.querySelectorAll('[data-close-campaign-dialog]').forEach((button) => {
        button.addEventListener('click', () => closeCampaignDialog(button.closest('dialog')));
    });
    page.querySelectorAll('.campanias-dialog').forEach((dialog) => {
        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) {
                closeCampaignDialog(dialog);
            }
        });
    });
    page.querySelector('[data-campaign-per-page]')?.addEventListener('change', (event) => {
        event.currentTarget.form?.submit();
    });
    page.querySelectorAll('[data-campanias-angle]').forEach((element) => {
        element.style.setProperty('--campanias-angle', `${element.dataset.campaniasAngle}deg`);
    });
    page.querySelectorAll('[data-campanias-width]').forEach((element) => {
        element.style.width = `${element.dataset.campaniasWidth}%`;
    });

    initializeCampaignForms(page);

    if (page.dataset.campaniasReopen === 'crear') {
        openCampaignDialog(document.querySelector('#nueva-campania'));
    }
});
