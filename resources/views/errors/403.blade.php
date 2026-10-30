@extends('layouts.base')
@section('title', 'Akses ditolak')
@section('body')
<main class="container py-5">
    <h1 class="h3">Akses ditolak</h1>
    <p>Anda tidak memiliki izin untuk membuka halaman atau data ini.</p>
    <a class="btn btn-success" href="{{ auth()->check() ? route(auth()->user()->dashboardRoute()) : route('login') }}">Kembali</a>
</main>
@endsection
