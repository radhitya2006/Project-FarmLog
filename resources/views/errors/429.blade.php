@extends('layouts.base')
@section('title', 'Terlalu banyak percobaan')
@section('body')
<main class="container py-5">
    <h1 class="h3">Terlalu banyak percobaan</h1>
    <p role="alert">Silakan coba lagi dalam {{ $retryAfter ?? 60 }} detik.</p>
    <a class="btn btn-success" href="{{ route('login') }}">Kembali ke login</a>
</main>
@endsection
