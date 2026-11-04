<div class="modal fade" id="accountStatusDialog" tabindex="-1" aria-labelledby="accountStatusTitle" aria-describedby="accountStatusDescription" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content admin-modal" method="POST" data-status-form>
            @csrf
            @method('PATCH')
            <input type="hidden" name="is_active" value="" data-status-value>
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="accountStatusTitle">Ubah status akses?</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup dialog"></button>
            </div>
            <div class="modal-body">
                <p class="admin-modal-account" data-status-name></p>
                <p id="accountStatusDescription" class="mb-0" data-status-description></p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" type="button" data-bs-dismiss="modal">Batal</button>
                <button class="btn btn-success" type="submit" disabled data-status-confirm>Konfirmasi perubahan</button>
            </div>
        </form>
    </div>
</div>
