@props(['active'])
<span class="account-status {{ $active ? 'account-status-active' : 'account-status-inactive' }}"><i class="bi {{ $active ? 'bi-check-circle-fill' : 'bi-pause-circle-fill' }}" aria-hidden="true"></i> {{ $active ? 'Aktif' : 'Nonaktif' }}</span>
