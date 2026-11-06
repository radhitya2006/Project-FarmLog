@extends('layouts.farmer')
@section('title', 'Detail Kegiatan')
@section('breadcrumb', 'Detail kegiatan')
@section('content')
<a class="farmer-back-link" href="{{ route('farmer.lands.planting-seasons.activities.index', [$land, $season]) }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali ke kegiatan</a>
<div class="farmer-page-heading"><div><p class="farmer-eyebrow">DETAIL KEGIATAN</p><h1>{{ $activity->name }}</h1></div><x-activity-status :status="$activity->status" /></div>
@include('farmer.activities._context')
<div class="row g-4">
    <div class="col-lg-8"><section class="farmer-panel farmer-details"><h2>Informasi kegiatan</h2><dl class="mb-0">
        <div><dt>Nama kegiatan</dt><dd>{{ $activity->name }}</dd></div>
        <div><dt>Tanggal kegiatan</dt><dd>{{ $activity->scheduled_for->format('d/m/Y') }}</dd></div>
        <div><dt>Keterangan</dt><dd class="land-notes">{{ $activity->notes ?? 'Belum ada keterangan untuk kegiatan ini.' }}</dd></div>
    </dl></section></div>
    @can('update', $activity)
        <div class="col-lg-4">
            <section class="farmer-panel farmer-details"><h2>Kelola kegiatan</h2><div class="d-flex flex-column gap-2 mt-3">
                <a class="btn btn-success" href="{{ route('farmer.lands.planting-seasons.activities.edit', [$land, $season, $activity]) }}">Edit kegiatan</a>
                @if($activity->status === \App\Enums\ActivityStatus::Planned)
                    <form method="POST" action="{{ route('farmer.lands.planting-seasons.activities.finish', [$land, $season, $activity]) }}" data-domain-form>@csrf @method('PATCH')<button class="btn btn-outline-success w-100" type="submit" data-domain-submit><i class="bi bi-check2 me-2" aria-hidden="true"></i>Tandai selesai</button></form>
                @endif
            </div></section>
            <section class="farmer-panel farmer-delete-panel mt-4"><h2>Hapus kegiatan</h2><p>Kegiatan ini akan dihapus permanen. Catatan musim dan kegiatan lain tetap tersimpan.</p><button class="btn btn-outline-danger" type="button" data-bs-toggle="modal" data-bs-target="#deleteActivityModal">Hapus kegiatan</button></section>
        </div>
    @endcan
</div>
@can('delete', $activity)
        <x-confirm-action-modal id="deleteActivityModal" title="Hapus kegiatan ini?" :action="route('farmer.lands.planting-seasons.activities.destroy', [$land, $season, $activity])" label="Ya, hapus kegiatan">
            Kegiatan <strong class="text-break">{{ $activity->name }}</strong> pada tanggal {{ $activity->scheduled_for->format('d/m/Y') }} akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.
        </x-confirm-action-modal>
@endcan
@endsection
