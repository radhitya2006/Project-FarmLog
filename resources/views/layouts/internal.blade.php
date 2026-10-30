@extends('layouts.base')
@section('body')
<header class="border-bottom bg-white">
    <nav class="container py-3 d-flex flex-wrap gap-3 align-items-center justify-content-between" aria-label="Navigasi utama">
        <a class="fw-bold fs-4 text-success text-decoration-none" href="{{ route(auth()->user()->dashboardRoute()) }}">FarmLog</a>
        <div class="d-flex gap-3 align-items-center">
            <span>{{ auth()->user()->name }}</span>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="btn btn-outline-secondary" type="submit">Keluar</button>
            </form>
        </div>
    </nav>
</header>
<main class="container py-5">
    <x-flash-message />
    @yield('content')
</main>
@endsection
