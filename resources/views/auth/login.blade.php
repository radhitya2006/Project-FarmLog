@extends('layouts.auth')
@section('title', 'Masuk')
@section('heading', 'Selamat datang kembali')
@section('description', 'Masuk untuk mengelola lahan dan catatan pertanian Anda.')
@section('content')
<form action="{{ route('login.store') }}" method="POST" data-submit-form>
    @csrf
    <x-form-field name="email" label="Email" type="email" :value="session('email', '')" autocomplete="username" maxlength="255" placeholder="nama@email.com" autocapitalize="none" spellcheck="false" />
    <x-form-field name="password" label="Kata sandi" type="password" autocomplete="current-password" placeholder="Masukkan kata sandi" />
    <x-auth-submit label="Masuk" />
</form>
<p class="auth-switch">Belum punya akun? <a href="{{ route('register') }}">Daftar sekarang</a></p>
@endsection
