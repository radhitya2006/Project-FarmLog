@props(['id', 'title', 'action', 'method' => 'DELETE', 'label'])
<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title fs-5" id="{{ $id }}Label">{{ $title }}</h2>
            <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">{{ $slot }}</div>
        <form method="POST" action="{{ $action }}" data-domain-form>
            @csrf
            @method($method)
            <input type="hidden" name="confirmed" value="1">
            <div class="modal-footer">
                <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Batal</button>
                <button class="btn btn-danger" type="submit" data-domain-submit>{{ $label }}</button>
            </div>
        </form>
    </div></div>
</div>
