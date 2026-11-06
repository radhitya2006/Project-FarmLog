@extends('layouts.farmer')
@section('title', 'Tambah Kegiatan')
@section('breadcrumb', 'Tambah kegiatan')
@section('content')
<a class="farmer-back-link" href="{{ route('farmer.lands.planting-seasons.activities.index', [$land, $season]) }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali ke kegiatan</a>
<div class="farmer-page-heading"><div><p class="farmer-eyebrow">KEGIATAN BARU</p><h1>Tambah kegiatan</h1><p>Susun rencana atau catat pekerjaan yang sudah selesai.</p></div></div>
@include('farmer.activities._context')
<form class="farmer-panel farmer-form" method="POST" action="{{ route('farmer.lands.planting-seasons.activities.store', [$land, $season]) }}" data-domain-form>
    @include('farmer.activities._form')
</form>
@endsection
