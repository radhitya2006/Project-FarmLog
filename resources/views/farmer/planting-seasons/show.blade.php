@extends('layouts.farmer')
@section('title', 'Detail Musim Tanam')
@section('breadcrumb', 'Detail musim tanam')
@section('content')
<a class="farmer-back-link" href="{{ route('farmer.lands.planting-seasons.index', $land) }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali ke musim tanam</a>
<div class="farmer-page-heading">
    <div><p class="farmer-eyebrow">DETAIL MUSIM TANAM</p><h1>{{ $season->crop_name }}</h1><p class="text-break">Lahan: <a href="{{ route('farmer.lands.show', $land) }}">{{ $land->name }}</a></p></div>
    <x-season-status :status="$season->status" />
</div>
@if($season->status === \App\Enums\PlantingSeasonStatus::Finished)
    <div class="alert alert-secondary" role="status"><i class="bi bi-lock me-2" aria-hidden="true"></i>Musim ini sudah selesai. Aktifkan kembali untuk mengedit informasi musim, kegiatan, atau panen.</div>
@endif
<div class="row g-4">
    <div class="col-lg-8">
        <section class="farmer-panel farmer-details"><h2>Informasi musim</h2><dl class="mb-0">
            <div><dt>Komoditas</dt><dd>{{ $season->crop_name }}</dd></div>
            <div><dt>Tanggal tanam</dt><dd>{{ $season->planted_at->format('d/m/Y') }}</dd></div>
            <div><dt>Perkiraan tanggal panen</dt><dd>{{ $season->expected_harvest_at->format('d/m/Y') }}</dd></div>
            <div><dt>Catatan</dt><dd class="land-notes">{{ $season->notes ?? 'Belum ada catatan untuk musim ini.' }}</dd></div>
        </dl></section>
        <section class="farmer-panel farmer-details mt-4"><h2>Catatan musim</h2>
            <div class="row g-3 mt-1"><div class="col-sm-6"><div class="season-count"><i class="bi bi-calendar-check" aria-hidden="true"></i><div><strong>{{ $season->activities_count }}</strong><span>kegiatan tercatat</span></div></div></div><div class="col-sm-6"><div class="season-count"><i class="bi bi-basket" aria-hidden="true"></i><div><strong>{{ $season->harvests_count }}</strong><span>catatan panen</span></div></div></div></div>
            <div class="d-flex flex-wrap gap-2 mt-4">
                <a class="btn btn-outline-success" href="{{ route('farmer.lands.planting-seasons.activities.index', [$land, $season]) }}">Lihat kegiatan</a>
                @can('create', [\App\Models\Activity::class, $season])<a class="btn btn-success" href="{{ route('farmer.lands.planting-seasons.activities.create', [$land, $season]) }}"><i class="bi bi-plus-lg me-2" aria-hidden="true"></i>Tambah kegiatan</a>@endcan
            </div>
            <h3 class="fs-6 mt-4 mb-3">Total panen musim</h3><x-harvest-summary :season="$season" />
            <div class="d-flex flex-wrap gap-2 mt-4">
                <a class="btn btn-outline-success" href="{{ route('farmer.lands.planting-seasons.harvests.index', [$land, $season]) }}">Lihat hasil panen</a>
                @can('create', [\App\Models\Harvest::class, $season])<a class="btn btn-success" href="{{ route('farmer.lands.planting-seasons.harvests.create', [$land, $season]) }}"><i class="bi bi-plus-lg me-2" aria-hidden="true"></i>Catat panen</a>@endcan
            </div>
        </section>
    </div>
    <div class="col-lg-4">
        <section class="farmer-panel farmer-season-summary"><h2>Kelola musim</h2>
            @if($season->status === \App\Enums\PlantingSeasonStatus::Active)
                <p>Perbarui informasi selama musim berjalan. Selesaikan musim setelah proses tanam berakhir.</p>
                <div class="d-flex flex-column gap-2"><a class="btn btn-success" href="{{ route('farmer.lands.planting-seasons.edit', [$land, $season]) }}">Edit musim tanam</a><button class="btn btn-outline-success" type="button" data-bs-toggle="modal" data-bs-target="#finishSeasonModal">Selesaikan musim</button></div>
            @else
                <p>Riwayat tetap tersimpan. Aktifkan kembali musim jika Anda perlu memperbarui catatannya.</p>
                <button class="btn btn-success w-100" type="button" data-bs-toggle="modal" data-bs-target="#reopenSeasonModal">Aktifkan kembali</button>
            @endif
        </section>
        <section class="farmer-panel farmer-delete-panel mt-4"><h2>Hapus musim tanam</h2><p>Seluruh kegiatan dan panen terkait akan ikut dihapus permanen.</p><button class="btn btn-outline-danger" type="button" data-bs-toggle="modal" data-bs-target="#deleteSeasonModal">Hapus musim tanam</button></section>
    </div>
</div>
@if($season->status === \App\Enums\PlantingSeasonStatus::Active)
    <x-season-action-modal id="finishSeasonModal" title="Selesaikan musim tanam?" :action="route('farmer.lands.planting-seasons.finish', [$land, $season])" label="Ya, selesaikan musim">
        Musim <strong class="text-break">{{ $season->crop_name }}</strong> akan ditandai selesai. Informasi musim, kegiatan, dan panen akan terkunci sampai Anda mengaktifkannya kembali. Data yang sudah tercatat tetap tersimpan.
    </x-season-action-modal>
@else
    <x-season-action-modal id="reopenSeasonModal" title="Aktifkan kembali musim?" :action="route('farmer.lands.planting-seasons.reopen', [$land, $season])" label="Ya, aktifkan kembali">
        Musim <strong class="text-break">{{ $season->crop_name }}</strong> akan kembali aktif. Informasi musim, kegiatan, dan panen dapat diperbarui kembali.
    </x-season-action-modal>
@endif
<x-season-action-modal id="deleteSeasonModal" title="Hapus musim tanam ini?" :action="route('farmer.lands.planting-seasons.destroy', [$land, $season])" method="DELETE" label="Ya, hapus musim" variant="danger">
    Musim <strong class="text-break">{{ $season->crop_name }}</strong>, beserta <strong>{{ $season->activities_count }} kegiatan</strong> dan <strong>{{ $season->harvests_count }} catatan panen</strong>, akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.
</x-season-action-modal>
@endsection
