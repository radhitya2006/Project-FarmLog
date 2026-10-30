@extends('layouts.base')
@section('body')
<div class="auth-shell">
    <aside class="auth-brand">
        <a class="brand-name" href="{{ route('home') }}">FarmLog</a>
        <div class="auth-brand-copy">
            <p class="small text-uppercase">Pencatatan pertanian digital</p>
            <h1>Catat setiap musim,<br>panen lebih terukur.</h1>
            <p>Kelola lahan, musim tanam, kegiatan, dan hasil panen dalam satu tempat yang rapi.</p>
        </div>
        <small>© {{ date('Y') }} FarmLog</small>
    </aside>
    <main class="auth-main">
        <div class="auth-form">
            <h2 class="h3 fw-bold mb-3">@yield('heading')</h2>
            <p class="text-secondary mb-4">@yield('description')</p>
            <x-flash-message />
            @yield('content')
        </div>
    </main>
</div>
@endsection
