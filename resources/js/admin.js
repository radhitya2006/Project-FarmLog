const statusDialog = document.getElementById('accountStatusDialog');
const statusForm = statusDialog?.querySelector('[data-status-form]');
const statusConfirm = statusDialog?.querySelector('[data-status-confirm]');

statusDialog?.addEventListener('show.bs.modal', (event) => {
    const trigger = event.relatedTarget;
    statusConfirm.disabled = true;
    statusForm.removeAttribute('action');
    if (!trigger?.dataset.statusUrl) {
        event.preventDefault();
        return;
    }

    const active = trigger.dataset.accountActive === '1';
    const action = active ? 'Nonaktifkan' : 'Aktifkan';
    statusDialog.querySelector('#accountStatusTitle').textContent = `${action} akses petani?`;
    statusDialog.querySelector('[data-status-name]').textContent = trigger.dataset.accountName;
    statusDialog.querySelector('[data-status-description]').textContent = active
        ? 'Petani tidak akan dapat masuk dan sesi aktifnya akan dihentikan pada permintaan berikutnya. Lahan serta riwayat pertaniannya tetap tersimpan.'
        : 'Petani akan dapat masuk kembali menggunakan email dan kata sandinya. Riwayat pertanian dapat diakses kembali.';
    statusForm.setAttribute('action', trigger.dataset.statusUrl);
    statusForm.querySelector('[data-status-value]').value = active ? '0' : '1';
    statusConfirm.textContent = `Ya, ${action.toLowerCase()} akses`;
    statusConfirm.classList.toggle('btn-danger', active);
    statusConfirm.classList.toggle('btn-success', !active);
    statusConfirm.disabled = false;
});

statusForm?.addEventListener('submit', (event) => {
    if (!statusForm.hasAttribute('action') || statusConfirm.disabled) {
        event.preventDefault();
        return;
    }
    statusConfirm.disabled = true;
    statusConfirm.textContent = 'Memproses…';
    statusForm.setAttribute('aria-busy', 'true');
});

const resetStatusDialog = () => {
    if (!statusForm) return;
    statusForm.removeAttribute('action');
    statusForm.removeAttribute('aria-busy');
    statusForm.querySelector('[data-status-value]').value = '';
    statusConfirm.disabled = true;
    statusConfirm.textContent = 'Konfirmasi perubahan';
};

statusDialog?.addEventListener('hidden.bs.modal', resetStatusDialog);
window.addEventListener('pageshow', () => {
    // Reopening the dialog chooses a fresh target after browser back/forward.
    resetStatusDialog();
});
