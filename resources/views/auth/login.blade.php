@extends('layouts.auth')
@section('title', 'Masuk')
@section('heading', 'Selamat datang kembali')
@section('description', 'Masuk untuk mengelola lahan dan catatan pertanian Anda.')
@section('content')
<form action="{{ route('login.store') }}" method="POST" data-submit-form>
    @csrf
    <x-form-field name="email" label="Email" type="email" :value="session('email', '')" autocomplete="username" maxlength="255" />
    <x-form-field name="password" label="Kata sandi" type="password" autocomplete="current-password" />
    <button type="submit" class="btn btn-success w-100 mt-3">Masuk</button>
</form>
<p class="text-center mt-4 mb-0">Belum punya akun? <a href="{{ route('register') }}">Daftar sekarang</a></p>
@endsection
