@extends('layouts.farmer')
@section('title', 'Detail Lahan')
@section('breadcrumb', 'Detail lahan')
@section('content')
<a class="farmer-back-link" href="{{ route('farmer.lands.index') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali ke lahan</a>
<div class="farmer-page-heading"><div><p class="farmer-eyebrow">DETAIL LAHAN</p><h1>{{ $land->name }}</h1><p>Informasi dasar dan catatan lahan Anda.</p></div><a class="btn btn-success" href="{{ route('farmer.lands.edit', $land) }}"><i class="bi bi-pencil-square me-2" aria-hidden="true"></i>Edit lahan</a></div>
<div class="row g-4">
    <div class="col-lg-8">
        <section class="farmer-panel farmer-details"><h2>Informasi lahan</h2><dl class="mb-0">
            <div><dt>Nama lahan</dt><dd>{{ $land->name }}</dd></div>
            <div><dt>Lokasi</dt><dd>{{ $land->location }}</dd></div>
            <div><dt>Luas lahan</dt><dd>{{ number_format((float) $land->area_hectares, 2, ',', '.') }} hektare</dd></div>
            <div><dt>Catatan</dt><dd class="land-notes">{{ $land->notes ?? 'Belum ada catatan untuk lahan ini.' }}</dd></div>
        </dl></section>
    </div>
    <div class="col-lg-4">
        <section class="farmer-panel farmer-season-summary"><span class="farmer-feature-icon"><i class="bi bi-flower1" aria-hidden="true"></i></span><h2>Musim tanam</h2><p><strong>{{ $land->planting_seasons_count }}</strong> musim tercatat di lahan ini.</p><p class="text-secondary small mb-0">Pengelolaan musim tanam akan tersedia pada tahap berikutnya.</p></section>
        <section class="farmer-panel farmer-delete-panel mt-4"><h2>Hapus lahan</h2>
            @if($land->planting_seasons_count > 0)
                <p id="delete-help">Lahan masih memiliki musim tanam. Seluruh musim tanam harus dihapus terlebih dahulu, termasuk musim yang sudah selesai.</p><button class="btn btn-outline-danger" type="button" disabled aria-describedby="delete-help">Hapus lahan</button>
            @else
                <p>Hapus hanya jika lahan ini tidak lagi perlu dicatat. Tindakan ini tidak dapat dibatalkan.</p><button class="btn btn-outline-danger" type="button" data-bs-toggle="modal" data-bs-target="#deleteLandModal">Hapus lahan</button>
            @endif
        </section>
    </div>
</div>
@if($land->planting_seasons_count === 0)
    <div class="modal fade" id="deleteLandModal" tabindex="-1" aria-labelledby="deleteLandLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
            <div class="modal-header"><h2 class="modal-title fs-5" id="deleteLandLabel">Hapus lahan ini?</h2><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
            <div class="modal-body">Lahan <strong class="text-break">{{ $land->name }}</strong> akan dihapus permanen. Lahan ini belum memiliki musim tanam. Tindakan ini tidak dapat dibatalkan.</div>
            <form method="POST" action="{{ route('farmer.lands.destroy', $land) }}" data-land-form>@csrf @method('DELETE')<div class="modal-footer"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Batal</button><button class="btn btn-danger" type="submit" data-land-submit>Ya, hapus lahan</button></div></form>
        </div></div>
    </div>
@endif
@endsection
