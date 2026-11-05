@props(['status'])
<span class="season-status {{ $status === \App\Enums\PlantingSeasonStatus::Active ? 'season-status-active' : 'season-status-finished' }}">{{ $status === \App\Enums\PlantingSeasonStatus::Active ? 'Aktif' : 'Selesai' }}</span>
