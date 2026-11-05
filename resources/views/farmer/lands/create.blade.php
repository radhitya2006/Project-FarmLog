@extends('layouts.farmer')
@section('title', 'Tambah Lahan')
@section('breadcrumb', 'Tambah lahan')
@section('content')
<a class="farmer-back-link" href="{{ route('farmer.lands.index') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali ke lahan</a>
<div class="farmer-page-heading"><div><p class="farmer-eyebrow">LAHAN BARU</p><h1>Tambah lahan</h1><p>Kenali lahan Anda, mulai dengan informasi dasarnya.</p></div></div>
<form class="farmer-panel farmer-form" method="POST" action="{{ route('farmer.lands.store') }}" data-land-form>
    @include('farmer.lands._form')
</form>
@endsection
