document.querySelectorAll('[data-profile-dynamic-fields] input, [data-profile-dynamic-fields] textarea, [data-profile-dynamic-fields] select').forEach((field) => {
    field.addEventListener('input', () => field.setCustomValidity(''));
});
