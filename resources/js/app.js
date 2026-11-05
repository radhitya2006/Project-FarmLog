import 'bootstrap';
import './admin';
import './lands';

document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.passwordToggle);
        const visible = input.type === 'password';
        input.type = visible ? 'text' : 'password';
        const icon = button.querySelector('i');
        icon.classList.toggle('bi-eye', !visible);
        icon.classList.toggle('bi-eye-slash', visible);
        button.setAttribute('aria-pressed', String(visible));
        button.setAttribute('aria-label', `${visible ? 'Sembunyikan' : 'Tampilkan'} ${button.dataset.passwordLabel}`);
    });
});

document.querySelectorAll('[data-submit-form]').forEach((form) => {
    form.addEventListener('submit', () => {
        const button = form.querySelector('[type="submit"]');
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        button.querySelector('[data-submit-label]').textContent = 'Memproses…';
        button.querySelector('[data-submit-spinner]').classList.remove('d-none');
        button.querySelector('[data-submit-icon]').classList.add('d-none');
    });
});

// Restore buttons when a browser restores the form from its back/forward cache.
window.addEventListener('pageshow', () => {
    document.querySelectorAll('[data-original-label]').forEach((button) => {
        button.disabled = false;
        button.removeAttribute('aria-busy');
        button.querySelector('[data-submit-label]').textContent = button.dataset.originalLabel;
        button.querySelector('[data-submit-spinner]').classList.add('d-none');
        button.querySelector('[data-submit-icon]').classList.remove('d-none');
    });
});
