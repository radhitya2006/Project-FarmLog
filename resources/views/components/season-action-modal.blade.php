@props(['id', 'title', 'action', 'method' => 'PATCH', 'label', 'variant' => 'success'])
<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="{{ $id }}Label">{{ $title }}</h2>
                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">{{ $slot }}</div>
            <form method="POST" action="{{ $action }}" data-season-form>
                @csrf
                @method($method)
                <input type="hidden" name="confirmed" value="1">
                <div class="modal-footer">
                    <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-{{ $variant }}" type="submit" data-season-submit>{{ $label }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
