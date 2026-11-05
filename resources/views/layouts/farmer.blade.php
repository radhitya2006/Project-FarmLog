@extends('layouts.base')
@section('body')
<div class="farmer-shell">
    <a class="farmer-skip-link" href="#farmer-content">Langsung ke konten</a>
    <aside class="farmer-sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="farmerNavigation" aria-labelledby="farmerNavigationLabel">
        <div class="farmer-brand-row">
            <a class="farmer-brand" href="{{ route('farmer.dashboard') }}" aria-label="FarmLog, dashboard petani"><i class="bi bi-leaf" aria-hidden="true"></i><span>Farm<span>Log</span></span></a>
            <button class="btn-close btn-close-white d-lg-none" type="button" data-bs-dismiss="offcanvas" data-bs-target="#farmerNavigation" aria-label="Tutup navigasi"></button>
        </div>
        <div class="farmer-sidebar-body">
            <p class="farmer-nav-label" id="farmerNavigationLabel">RUANG PETANI</p>
            <nav class="farmer-nav" aria-label="Navigasi petani">
                <a href="{{ route('farmer.dashboard') }}" class="{{ request()->routeIs('farmer.dashboard') ? 'is-active' : '' }}" @if(request()->routeIs('farmer.dashboard')) aria-current="page" @endif><i class="bi bi-grid-1x2" aria-hidden="true"></i> Dashboard</a>
                <a href="{{ route('farmer.lands.index') }}" class="{{ request()->routeIs('farmer.lands.*') ? 'is-active' : '' }}" @if(request()->routeIs('farmer.lands.*')) aria-current="page" @endif><i class="bi bi-map" aria-hidden="true"></i> Lahan</a>
            </nav>
            <div class="farmer-sidebar-note"><i class="bi bi-flower1" aria-hidden="true"></i><strong>Catatan rapi,<br>langkah lebih pasti.</strong><p>Mulai dari lahan, simpan perjalanan pertanian Anda bersama FarmLog.</p></div>
        </div>
        <div class="farmer-sidebar-bottom">
            <div class="farmer-identity"><span class="farmer-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span><div><strong>{{ auth()->user()->name }}</strong><span>Petani</span></div></div>
            <form action="{{ route('logout') }}" method="POST">@csrf<button class="farmer-logout" type="submit"><i class="bi bi-box-arrow-right" aria-hidden="true"></i> Keluar</button></form>
        </div>
    </aside>
    <div class="farmer-workspace">
        <header class="farmer-topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#farmerNavigation" aria-controls="farmerNavigation" aria-label="Buka navigasi"><i class="bi bi-list fs-4" aria-hidden="true"></i></button>
                <nav aria-label="Breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="{{ route('farmer.dashboard') }}">Ruang Petani</a></li><li class="breadcrumb-item active" aria-current="page">@yield('breadcrumb', 'Dashboard')</li></ol></nav>
            </div>
            <span class="farmer-role d-none d-sm-inline-flex"><i class="bi bi-person-check" aria-hidden="true"></i> Petani</span>
        </header>
        <main class="farmer-content" id="farmer-content" tabindex="-1">
            <x-flash-message />
            @if(session('error'))<div class="alert alert-danger" role="alert">{{ session('error') }}</div>@endif
            @if($errors->any())<div class="alert alert-danger" role="alert"><p class="fw-semibold mb-1">Periksa kembali data yang Anda masukkan.</p><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            @yield('content')
        </main>
        <footer class="farmer-footer"><span>&copy; {{ date('Y') }} FarmLog</span><span>Manajemen Lahan Pertanian</span></footer>
    </div>
</div>
@endsection
