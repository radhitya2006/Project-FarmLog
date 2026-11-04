@props(['farmer', 'compact' => false])
<button class="{{ $compact ? 'admin-icon-button'.($farmer->is_active ? ' is-warning' : '') : 'btn '.($farmer->is_active ? 'btn-outline-danger' : 'btn-success') }}"
    type="button" data-bs-toggle="modal" data-bs-target="#accountStatusDialog"
    data-account-name="{{ $farmer->name }}" data-account-active="{{ $farmer->is_active ? '1' : '0' }}"
    data-status-url="{{ route('admin.users.status', $farmer) }}"
    aria-label="{{ $farmer->is_active ? 'Nonaktifkan' : 'Aktifkan' }} akses {{ $farmer->name }}"
    title="{{ $farmer->is_active ? 'Nonaktifkan' : 'Aktifkan' }} akses">
    <i class="bi {{ $farmer->is_active ? 'bi-person-dash' : 'bi-person-check' }}" aria-hidden="true"></i>
    @unless ($compact)
        {{ $farmer->is_active ? 'Nonaktifkan' : 'Aktifkan' }} akses
    @endunless
</button>
