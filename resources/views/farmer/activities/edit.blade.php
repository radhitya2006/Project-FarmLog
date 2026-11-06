@extends('layouts.farmer')
@section('title', 'Edit Kegiatan')
@section('breadcrumb', 'Edit kegiatan')
@section('content')
<a class="farmer-back-link" href="{{ route('farmer.lands.planting-seasons.activities.show', [$land, $season, $activity]) }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali ke detail kegiatan</a>
<div class="farmer-page-heading"><div><p class="farmer-eyebrow">INFORMASI KEGIATAN</p><h1>Edit kegiatan</h1><p>Perbarui informasi dan status kegiatan pertanian Anda.</p></div></div>
@include('farmer.activities._context')
<form class="farmer-panel farmer-form" method="POST" action="{{ route('farmer.lands.planting-seasons.activities.update', [$land, $season, $activity]) }}" data-domain-form>
    @method('PUT')
    @include('farmer.activities._form')
</form>
@endsection
