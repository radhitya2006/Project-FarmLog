const statusPreview = document.getElementById('accountStatusPreview');

statusPreview?.addEventListener('show.bs.modal', (event) => {
    const trigger = event.relatedTarget;
    if (!trigger) return;

    const active = trigger.dataset.accountActive === '1';
    const action = active ? 'Nonaktifkan' : 'Aktifkan';
    statusPreview.querySelector('#accountStatusTitle').textContent = `${action} akun petani?`;
    statusPreview.querySelector('[data-status-name]').textContent = trigger.dataset.accountName;
    statusPreview.querySelector('[data-status-description]').textContent = active
        ? 'Petani tidak akan dapat masuk dan sesi aktifnya akan dihentikan pada permintaan berikutnya. Lahan serta riwayat pertaniannya tetap tersimpan.'
        : 'Petani akan dapat masuk kembali menggunakan email dan kata sandinya. Riwayat pertanian dapat diakses kembali.';
    const confirm = statusPreview.querySelector('[data-status-confirm]');
    confirm.textContent = `${action} akun`;
    confirm.classList.toggle('btn-danger', active);
    confirm.classList.toggle('btn-success', !active);
});
