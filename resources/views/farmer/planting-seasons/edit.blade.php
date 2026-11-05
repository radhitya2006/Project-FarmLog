@extends('layouts.farmer')
@section('title', 'Edit Musim Tanam')
@section('breadcrumb', 'Edit musim tanam')
@section('content')
<a class="farmer-back-link" href="{{ route('farmer.lands.planting-seasons.show', [$land, $season]) }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali ke detail musim</a>
<div class="farmer-page-heading"><div><p class="farmer-eyebrow">INFORMASI MUSIM</p><h1>Edit musim tanam</h1><p class="text-break">Perbarui informasi musim di {{ $land->name }}.</p></div></div>
<form class="farmer-panel farmer-form" method="POST" action="{{ route('farmer.lands.planting-seasons.update', [$land, $season]) }}" data-season-form>
    @method('PUT')
    @include('farmer.planting-seasons._form')
</form>
@endsection
