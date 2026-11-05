@extends('layouts.farmer')
@section('title', 'Lahan Saya')
@section('breadcrumb', 'Lahan')
@section('content')
<div class="farmer-page-heading">
    <div><p class="farmer-eyebrow">KELOLA LAHAN</p><h1>Lahan saya</h1><p>Setiap lahan, awal dari cerita panen Anda.</p></div>
    <a class="btn btn-success" href="{{ route('farmer.lands.create') }}"><i class="bi bi-plus-lg me-2" aria-hidden="true"></i>Tambah lahan</a>
</div>
<form class="farmer-search farmer-panel" action="{{ route('farmer.lands.index') }}" method="GET" role="search">
    <div class="flex-grow-1"><label for="search" class="visually-hidden">Cari nama atau lokasi lahan</label><input id="search" class="form-control @error('search') is-invalid @enderror" type="search" name="search" value="{{ $search }}" maxlength="150" placeholder="Cari nama atau lokasi lahan..."></div>
    <button class="btn btn-success" type="submit"><i class="bi bi-search me-2" aria-hidden="true"></i>Cari</button>
    @if($search !== '')<a class="btn btn-outline-secondary" href="{{ route('farmer.lands.index') }}">Reset</a>@endif
</form>
<p class="farmer-result-count">{{ $lands->total() }} lahan{{ $search !== '' ? ' ditemukan untuk “'.$search.'”' : ' tercatat' }}</p>
@if($lands->isEmpty())
    <div class="farmer-panel">
        @if($search !== '')
            <x-farmer-empty-state title="Lahan tidak ditemukan" description="Coba nama atau lokasi lain untuk menemukan lahan Anda."><a class="btn btn-outline-success" href="{{ route('farmer.lands.index') }}">Lihat semua lahan</a></x-farmer-empty-state>
        @elseif($lands->total() > 0)
            <x-farmer-empty-state title="Tidak ada lahan di halaman ini" description="Kembali ke halaman pertama untuk melihat lahan Anda."><a class="btn btn-outline-success" href="{{ route('farmer.lands.index') }}">Halaman pertama</a></x-farmer-empty-state>
        @else
            <x-farmer-empty-state title="Mulai dari lahan pertama Anda" description="Catat nama, luas, dan lokasi lahan agar informasi pertanian tersimpan dengan rapi."><a class="btn btn-success" href="{{ route('farmer.lands.create') }}">Tambah lahan pertama</a></x-farmer-empty-state>
        @endif
    </div>
@else
    <div class="row g-4">
        @foreach($lands as $land)
            <div class="col-md-6 col-xl-4">
                <article class="land-card farmer-panel h-100">
                    <div class="land-card-top"><span class="farmer-feature-icon"><i class="bi bi-map" aria-hidden="true"></i></span><span class="land-season-count">{{ $land->planting_seasons_count }} musim tanam</span></div>
                    <h2>{{ $land->name }}</h2>
                    <p class="land-location"><i class="bi bi-geo-alt" aria-hidden="true"></i><span>{{ $land->location }}</span></p>
                    <div class="land-card-bottom"><div><span class="land-area-label">Luas lahan</span><strong>{{ number_format((float) $land->area_hectares, 2, ',', '.') }} <small>ha</small></strong></div><a class="btn btn-outline-success" href="{{ route('farmer.lands.show', $land) }}" aria-label="Lihat detail {{ $land->name }}">Lihat detail <i class="bi bi-arrow-right ms-1" aria-hidden="true"></i></a></div>
                </article>
            </div>
        @endforeach
    </div>
    <div class="farmer-pagination mt-4">{{ $lands->links('pagination.farmer') }}</div>
@endif
@endsection
