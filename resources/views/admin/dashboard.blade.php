@extends('layouts.admin')
@section('title', 'Ringkasan Admin')
@section('content')
<div class="admin-page-heading">
    <div><p class="admin-eyebrow">PANEL ADMIN</p><h1>Selamat datang, {{ auth()->user()->name }}.</h1><p>Pantau akun petani dan kelola akses FarmLog dari sini.</p></div>
    <a class="btn btn-success" href="{{ route('admin.users.index') }}"><i class="bi bi-people" aria-hidden="true"></i> Lihat akun petani</a>
</div>
<div class="row g-3 admin-stats" aria-label="Ringkasan akun petani">
    @foreach ([['Total petani', $total, 'Semua akun petani terdaftar', 'bi-people', ''], ['Petani aktif', $active, 'Dapat masuk ke FarmLog', 'bi-person-check', 'green'], ['Petani nonaktif', $inactive, 'Akses masuk dinonaktifkan', 'bi-person-dash', 'amber']] as [$label, $value, $description, $icon, $tone])
        <div class="col-md-4"><section class="admin-stat-card"><div class="d-flex justify-content-between align-items-center gap-2"><h2>{{ $label }}</h2><span class="admin-stat-icon {{ $tone }}"><i class="bi {{ $icon }}" aria-hidden="true"></i></span></div><strong class="admin-stat-value">{{ number_format($value, 0, ',', '.') }}</strong><p>{{ $description }}</p></section></div>
    @endforeach
</div>
<div class="row g-4">
    <div class="col-12">
        <section class="admin-card">
            <div class="admin-card-header"><div><h2>Petani terbaru</h2><p>Akun yang baru bergabung di FarmLog.</p></div><a class="admin-text-link" href="{{ route('admin.users.index') }}">Lihat semua <i class="bi bi-arrow-right" aria-hidden="true"></i></a></div>
            @if ($recentFarmers->isEmpty())
                <x-admin-empty-state title="Belum ada akun petani" description="Akun petani akan tampil di sini setelah pendaftaran pertama." />
            @else
                <div class="table-responsive"><table class="table admin-table mb-0">
                    <caption class="visually-hidden">Lima akun petani yang terakhir terdaftar</caption>
                    <thead><tr><th scope="col">Petani</th><th scope="col">Status</th><th scope="col">Bergabung</th><th scope="col"><span class="visually-hidden">Aksi</span></th></tr></thead>
                    <tbody>@foreach ($recentFarmers as $farmer)
                        <tr><td>@include('admin.users.identity', ['farmer' => $farmer])</td><td><x-account-status :active="$farmer->is_active" /></td><td class="text-nowrap">{{ $farmer->created_at?->format('d/m/Y') ?? '—' }}</td><td><a class="admin-icon-button" href="{{ route('admin.users.show', $farmer) }}" aria-label="Lihat detail {{ $farmer->name }}"><i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></td></tr>
                    @endforeach</tbody>
                </table></div>
            @endif
        </section>
    </div>
</div>
@endsection
