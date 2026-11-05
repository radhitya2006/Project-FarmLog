@extends('layouts.farmer')
@section('title', 'Tambah Musim Tanam')
@section('breadcrumb', 'Tambah musim tanam')
@section('content')
<a class="farmer-back-link" href="{{ route('farmer.lands.planting-seasons.index', $land) }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali ke musim tanam</a>
<div class="farmer-page-heading"><div><p class="farmer-eyebrow">MUSIM BARU</p><h1>Tambah musim tanam</h1><p class="text-break">Mulai catatan musim baru di {{ $land->name }}. Musim akan berstatus aktif.</p></div></div>
<form class="farmer-panel farmer-form" method="POST" action="{{ route('farmer.lands.planting-seasons.store', $land) }}" data-season-form>
    @include('farmer.planting-seasons._form')
</form>
@endsection
