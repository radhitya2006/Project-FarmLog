document.querySelectorAll('[data-land-form], [data-season-form], [data-domain-form]').forEach((form) => {
    const button = form.querySelector('[data-land-submit], [data-season-submit], [data-domain-submit]');
    const originalLabel = button.textContent;

    form.addEventListener('submit', (event) => {
        if (button.disabled) {
            event.preventDefault();
            return;
        }

        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        button.textContent = 'Memproses…';
    });

    window.addEventListener('pageshow', () => {
        button.disabled = false;
        button.removeAttribute('aria-busy');
        button.textContent = originalLabel;
    });
});
