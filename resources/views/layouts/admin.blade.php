@extends('layouts.base')
@section('body')
<div class="admin-shell">
    <a class="admin-skip-link" href="#admin-content">Langsung ke konten</a>
    <aside class="admin-sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="adminNavigation" aria-labelledby="adminNavigationLabel">
        <div class="admin-brand-row">
            <a class="admin-brand" href="{{ route('admin.dashboard') }}" aria-label="FarmLog, ringkasan admin">
                <span class="admin-brand-icon"><i class="bi bi-leaf" aria-hidden="true"></i></span>
                <span>Farm<span class="admin-brand-accent">Log</span></span>
            </a>
            <button class="btn-close btn-close-white d-lg-none" type="button" data-bs-dismiss="offcanvas" data-bs-target="#adminNavigation" aria-label="Tutup navigasi"></button>
        </div>
        <div class="admin-sidebar-body">
            <p class="admin-nav-label" id="adminNavigationLabel">RUANG ADMIN</p>
            <nav class="admin-nav" aria-label="Navigasi admin">
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}" @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif>
                    <i class="bi bi-grid-1x2" aria-hidden="true"></i> Ringkasan
                </a>
                <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}" @if(request()->routeIs('admin.users.*')) aria-current="page" @endif>
                    <i class="bi bi-people" aria-hidden="true"></i> Akun Petani
                </a>
            </nav>
            <div class="admin-sidebar-note">
                <i class="bi bi-shield-check" aria-hidden="true"></i>
                <strong>Akun tertata,<br>petani siap berkarya.</strong>
                <p>Kelola akses petani dan jaga catatan pertanian tetap tersimpan.</p>
            </div>
        </div>
        <div class="admin-sidebar-bottom">
            <div class="admin-identity">
                <span class="admin-avatar admin-avatar-light" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                <div><strong>{{ auth()->user()->name }}</strong><span>Administrator</span></div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="admin-logout" type="submit"><i class="bi bi-box-arrow-right" aria-hidden="true"></i> Keluar</button>
            </form>
        </div>
    </aside>
    <div class="admin-workspace">
        <header class="admin-topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn admin-menu-button d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminNavigation" aria-controls="adminNavigation" aria-label="Buka navigasi"><i class="bi bi-list" aria-hidden="true"></i></button>
                <nav aria-label="Breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Panel Admin</a></li><li class="breadcrumb-item active" aria-current="page">@yield('breadcrumb', 'Ringkasan')</li></ol></nav>
            </div>
            <span class="admin-role"><i class="bi bi-shield-check" aria-hidden="true"></i> Admin</span>
        </header>
        <main class="admin-content" id="admin-content" tabindex="-1">
            <x-flash-message />
            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <p class="fw-semibold mb-1">Permintaan tidak dapat diproses.</p>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @yield('content')
        </main>
        <footer class="admin-footer"><span>&copy; {{ date('Y') }} FarmLog</span><span>Manajemen Lahan Pertanian</span></footer>
    </div>
</div>
@endsection
