@extends('layouts.base')
@section('body')
<div class="auth-shell">
    <aside class="auth-brand">
        <div class="auth-landscape" aria-hidden="true">
            <span class="auth-plot auth-plot-one"></span>
            <span class="auth-plot auth-plot-two"></span>
            <span class="auth-plot auth-plot-three"></span>
            <span class="auth-contour"></span>
        </div>
        <a class="auth-logo" href="{{ route('home') }}" aria-label="FarmLog, halaman masuk">
            <span class="auth-logo-mark" aria-hidden="true"><i class="bi bi-leaf"></i></span>
            <span>Farm<span class="auth-logo-accent">Log</span></span>
        </a>
        <div class="auth-brand-copy">
            <div class="auth-eyebrow"><i class="bi bi-patch-check" aria-hidden="true"></i> Pencatatan pertanian digital</div>
            <h2 class="auth-brand-title">Catat setiap musim,<br>panen lebih terukur.</h2>
            <p class="auth-brand-description">Kelola lahan, musim tanam, kegiatan, dan hasil panen dalam satu tempat yang rapi.</p>
            <ul class="auth-benefits">
                <li><i class="bi bi-map" aria-hidden="true"></i><span>Data lahan tersusun per lokasi dan luas</span></li>
                <li><i class="bi bi-calendar2-check" aria-hidden="true"></i><span>Rencana dan catatan kegiatan dalam satu tempat</span></li>
                <li><i class="bi bi-basket2" aria-hidden="true"></i><span>Rekap panen per musim, kg dan ton terpisah</span></li>
            </ul>
        </div>
        <small class="auth-copyright">© {{ date('Y') }} FarmLog · Manajemen Lahan Pertanian</small>
    </aside>
    <main class="auth-main" id="main-content">
        <div class="auth-form">
            <h1 class="auth-heading">@yield('heading')</h1>
            <p class="auth-description">@yield('description')</p>
            @if (session('success'))
                <div class="alert alert-success auth-flash" role="status">
                    <i class="bi bi-check-circle" aria-hidden="true"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @yield('content')
        </div>
    </main>
    <footer class="auth-mobile-footer">© {{ date('Y') }} FarmLog · Catatan pertanian Anda</footer>
</div>
@endsection
