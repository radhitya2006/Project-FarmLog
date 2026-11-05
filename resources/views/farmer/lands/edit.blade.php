@extends('layouts.farmer')
@section('title', 'Edit Lahan')
@section('breadcrumb', 'Edit lahan')
@section('content')
<a class="farmer-back-link" href="{{ route('farmer.lands.show', $land) }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali ke detail lahan</a>
<div class="farmer-page-heading"><div><p class="farmer-eyebrow">INFORMASI LAHAN</p><h1>Edit lahan</h1><p>Perbarui informasi agar catatan lahan tetap sesuai.</p></div></div>
<form class="farmer-panel farmer-form" method="POST" action="{{ route('farmer.lands.update', $land) }}" data-land-form>
    @method('PUT')
    @include('farmer.lands._form')
</form>
@endsection
