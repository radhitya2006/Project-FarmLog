@props(['status'])
<span class="season-status {{ $status === \App\Enums\ActivityStatus::Finished ? 'season-status-finished' : 'season-status-active' }}">{{ $status === \App\Enums\ActivityStatus::Finished ? 'Selesai' : 'Direncanakan' }}</span>
