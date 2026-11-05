@extends('layouts.farmer')
@section('title', 'Dashboard')
@section('content')
<div class="farmer-page-heading"><div><p class="farmer-eyebrow">SELAMAT DATANG DI FARMLOG</p><h1>Selamat datang, {{ auth()->user()->name }}</h1><p>Langkah kecil hari ini untuk catatan pertanian yang lebih rapi.</p></div></div>
<section class="farmer-welcome farmer-panel"><span class="farmer-feature-icon"><i class="bi bi-map" aria-hidden="true"></i></span><h2>Mulai dari lahan Anda.</h2><p>Catat nama, luas, dan lokasi lahan dalam satu tempat. Buka menu Lahan untuk melihat atau memperbarui catatan Anda.</p><a class="btn btn-success" href="{{ route('farmer.lands.index') }}">Buka lahan saya <i class="bi bi-arrow-right ms-2" aria-hidden="true"></i></a></section>
<p class="text-secondary small mt-4">Ringkasan musim tanam, kegiatan, dan panen akan tersedia pada tahap berikutnya.</p>
@endsection
