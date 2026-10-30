@extends('layouts.internal')
@section('title', 'Dashboard')
@section('content')
<h1 class="h3">Selamat datang, {{ auth()->user()->name }}</h1>
<p class="text-secondary">Akun petani Anda siap digunakan. Ringkasan dan pengelolaan lahan akan tersedia pada tahap berikutnya.</p>
@endsection
