@extends('layouts.farmer')
@section('title', 'Kegiatan Pertanian')
@section('breadcrumb', 'Kegiatan')
@section('content')
<a class="farmer-back-link" href="{{ route('farmer.lands.planting-seasons.show', [$land, $season]) }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali ke detail musim</a>
<div class="farmer-page-heading">
    <div><p class="farmer-eyebrow">CATATAN PERTANIAN</p><h1>Kegiatan</h1><p>Rencanakan pekerjaan dan catat kegiatan yang sudah dilakukan.</p></div>
    @can('create', [\App\Models\Activity::class, $season])
        <a class="btn btn-success" href="{{ route('farmer.lands.planting-seasons.activities.create', [$land, $season]) }}"><i class="bi bi-plus-lg me-2" aria-hidden="true"></i>Tambah kegiatan</a>
    @endcan
</div>
@include('farmer.activities._context')
<form class="farmer-search farmer-panel align-items-end" method="GET" action="{{ route('farmer.lands.planting-seasons.activities.index', [$land, $season]) }}" role="search">
    <div class="flex-grow-1"><label for="search" class="form-label">Cari kegiatan</label><input id="search" type="search" name="search" value="{{ $search }}" maxlength="150" class="form-control @error('search') is-invalid @enderror" placeholder="Nama kegiatan..."></div>
    <div class="season-filter-field"><label for="status" class="form-label">Status kegiatan</label><select id="status" name="status" class="form-select @error('status') is-invalid @enderror"><option value="">Semua status</option><option value="direncanakan" @selected($status === 'direncanakan')>Direncanakan</option><option value="selesai" @selected($status === 'selesai')>Selesai</option></select></div>
    <button class="btn btn-success" type="submit">Terapkan</button>
    @if($search !== '' || $status !== '')<a class="btn btn-outline-secondary" href="{{ route('farmer.lands.planting-seasons.activities.index', [$land, $season]) }}">Reset</a>@endif
</form>
<p class="farmer-result-count">{{ $activities->total() }} kegiatan{{ $search !== '' || $status !== '' ? ' sesuai filter' : ' tercatat' }}</p>
<section class="farmer-panel">
    @if($activities->isEmpty())
        @if($search !== '' || $status !== '' || $activities->total() > 0)
            <x-farmer-empty-state icon="bi-calendar-check" title="Kegiatan tidak ditemukan" description="Coba pencarian lain atau lihat semua kegiatan pada musim ini."><a class="btn btn-outline-success" href="{{ route('farmer.lands.planting-seasons.activities.index', [$land, $season]) }}">Lihat semua kegiatan</a></x-farmer-empty-state>
        @else
            <x-farmer-empty-state icon="bi-calendar-check" title="Belum ada kegiatan" description="Catat pekerjaan seperti persiapan lahan, penanaman, penyiraman, dan pemupukan.">
                @can('create', [\App\Models\Activity::class, $season])<a class="btn btn-success" href="{{ route('farmer.lands.planting-seasons.activities.create', [$land, $season]) }}">Tambah kegiatan pertama</a>@endcan
            </x-farmer-empty-state>
        @endif
    @else
        <div class="table-responsive"><table class="table activity-table mb-0">
            <caption class="visually-hidden">Kegiatan pada musim {{ $season->crop_name }} di lahan {{ $land->name }}</caption>
            <thead><tr><th scope="col">Kegiatan</th><th scope="col">Tanggal</th><th scope="col">Status</th><th scope="col"><span class="visually-hidden">Aksi</span></th></tr></thead>
            <tbody>@foreach($activities as $activity)<tr>
                <td class="activity-name">{{ $activity->name }}</td><td>{{ $activity->scheduled_for->format('d/m/Y') }}</td><td><x-activity-status :status="$activity->status" /></td>
                <td><a class="btn btn-outline-success" href="{{ route('farmer.lands.planting-seasons.activities.show', [$land, $season, $activity]) }}" aria-label="Lihat detail {{ $activity->name }} tanggal {{ $activity->scheduled_for->format('d/m/Y') }}">Lihat detail</a></td>
            </tr>@endforeach</tbody>
        </table></div>
    @endif
</section>
<div class="farmer-pagination mt-4">{{ $activities->links('pagination.farmer', ['resourceLabel' => 'kegiatan']) }}</div>
@endsection
