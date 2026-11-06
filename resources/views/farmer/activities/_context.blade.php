<p class="activity-context">Lahan: <a href="{{ route('farmer.lands.show', $land) }}">{{ $land->name }}</a><span aria-hidden="true"> · </span>Musim: <a href="{{ route('farmer.lands.planting-seasons.show', [$land, $season]) }}">{{ $season->crop_name }} ({{ $season->planted_at->format('d/m/Y') }})</a></p>
@if($season->status === \App\Enums\PlantingSeasonStatus::Finished)
    <div class="alert alert-secondary" role="status"><i class="bi bi-lock me-2" aria-hidden="true"></i>Musim ini sudah selesai. Kegiatan hanya dapat dilihat. Aktifkan kembali musim melalui detail musim untuk mengubah kegiatan.</div>
@endif
