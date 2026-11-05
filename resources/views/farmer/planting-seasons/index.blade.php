@extends('layouts.farmer')
@section('title', 'Musim Tanam')
@section('breadcrumb', 'Musim tanam')
@section('content')
<a class="farmer-back-link" href="{{ route('farmer.lands.show', $land) }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali ke detail lahan</a>
<div class="farmer-page-heading">
    <div><p class="farmer-eyebrow">PERJALANAN LAHAN</p><h1>Musim tanam</h1><p class="text-break">Kelola musim tanam di {{ $land->name }}.</p></div>
    <a class="btn btn-success" href="{{ route('farmer.lands.planting-seasons.create', $land) }}"><i class="bi bi-plus-lg me-2" aria-hidden="true"></i>Tambah musim tanam</a>
</div>
<form class="farmer-search farmer-panel align-items-end" action="{{ route('farmer.lands.planting-seasons.index', $land) }}" method="GET">
    <div class="season-filter-field">
        <label for="status" class="form-label">Status musim</label>
        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
            <option value="">Semua status</option><option value="aktif" @selected($status === 'aktif')>Aktif</option><option value="selesai" @selected($status === 'selesai')>Selesai</option>
        </select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <button class="btn btn-success" type="submit">Terapkan</button>
    @if($status !== '')<a class="btn btn-outline-secondary" href="{{ route('farmer.lands.planting-seasons.index', $land) }}">Reset</a>@endif
</form>
<p class="farmer-result-count">{{ $seasons->total() }} musim tanam{{ $status !== '' ? ' sesuai filter' : ' tercatat' }}</p>
@if($seasons->isEmpty())
    <div class="farmer-panel">
        @if($status !== '' || $seasons->total() > 0)
            <x-farmer-empty-state icon="bi-flower1" title="Musim tanam tidak ditemukan" description="Lihat semua musim atau kembali ke halaman pertama untuk melanjutkan."><a class="btn btn-outline-success" href="{{ route('farmer.lands.planting-seasons.index', $land) }}">Lihat semua musim</a></x-farmer-empty-state>
        @else
            <x-farmer-empty-state icon="bi-flower1" title="Mulai musim tanam pertama" description="Catat komoditas dan tanggal tanam untuk menyimpan perjalanan lahan ini."><a class="btn btn-success" href="{{ route('farmer.lands.planting-seasons.create', $land) }}">Tambah musim tanam</a></x-farmer-empty-state>
        @endif
    </div>
@else
    <div class="row g-4">
        @foreach($seasons as $season)
            <div class="col-md-6 col-xl-4">
                <article class="land-card farmer-panel h-100">
                    <div class="land-card-top"><span class="farmer-feature-icon"><i class="bi bi-flower1" aria-hidden="true"></i></span><x-season-status :status="$season->status" /></div>
                    <h2>{{ $season->crop_name }}</h2>
                    <dl class="season-dates"><div><dt>Tanggal tanam</dt><dd>{{ $season->planted_at->format('d/m/Y') }}</dd></div><div><dt>Perkiraan panen</dt><dd>{{ $season->expected_harvest_at->format('d/m/Y') }}</dd></div></dl>
                    <div class="land-card-bottom"><a class="btn btn-outline-success w-100" href="{{ route('farmer.lands.planting-seasons.show', [$land, $season]) }}" aria-label="Lihat detail musim {{ $season->crop_name }} tanggal {{ $season->planted_at->format('d/m/Y') }}">Lihat detail <i class="bi bi-arrow-right ms-2" aria-hidden="true"></i></a></div>
                </article>
            </div>
        @endforeach
    </div>
    <div class="farmer-pagination mt-4">{{ $seasons->links('pagination.farmer', ['resourceLabel' => 'musim tanam']) }}</div>
@endif
@endsection
