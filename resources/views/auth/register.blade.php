@extends('layouts.auth')
@section('title', 'Daftar')
@section('heading', 'Mulai catatan pertanian Anda')
@section('description', 'Buat akun petani untuk mulai mengelola lahan.')
@section('content')
<form action="{{ route('register.store') }}" method="POST" data-submit-form>
    @csrf
    <x-form-field name="name" label="Nama" autocomplete="name" maxlength="150" />
    <x-form-field name="email" label="Email" type="email" autocomplete="username" maxlength="255" />
    <p class="small text-secondary">Gunakan kata sandi minimal 8 karakter.</p>
    <x-form-field name="password" label="Kata sandi" type="password" autocomplete="new-password" minlength="8" maxlength="72" />
    <x-form-field name="password_confirmation" label="Konfirmasi kata sandi" type="password" autocomplete="new-password" minlength="8" maxlength="72" />
    <button type="submit" class="btn btn-success w-100 mt-3">Buat akun</button>
</form>
<p class="text-center mt-4 mb-0">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></p>
@endsection
