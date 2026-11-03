@extends('layouts.auth')
@section('title', 'Daftar')
@section('heading', 'Mulai catatan pertanian Anda')
@section('description', 'Buat akun petani untuk mulai mengelola lahan.')
@section('content')
<form action="{{ route('register.store') }}" method="POST" data-submit-form>
    @csrf
    <x-form-field name="name" label="Nama" autocomplete="name" maxlength="150" placeholder="Nama lengkap Anda" />
    <x-form-field name="email" label="Email" type="email" autocomplete="username" maxlength="255" placeholder="nama@email.com" autocapitalize="none" spellcheck="false" />
    <x-form-field name="password" label="Kata sandi" type="password" autocomplete="new-password" minlength="8" maxlength="72" placeholder="Buat kata sandi" help="Gunakan minimal 8 karakter." />
    <x-form-field name="password_confirmation" label="Konfirmasi kata sandi" type="password" autocomplete="new-password" minlength="8" maxlength="72" placeholder="Ulangi kata sandi" />
    <x-auth-submit label="Buat akun" />
</form>
<p class="auth-switch">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></p>
@endsection
