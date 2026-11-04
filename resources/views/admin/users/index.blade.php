@extends('layouts.admin')
@section('title', 'Akun Petani')
@section('breadcrumb', 'Akun Petani')
@section('content')
<div class="admin-page-heading"><div><p class="admin-eyebrow">PENGELOLAAN AKSES</p><h1>Akun Petani</h1><p>Lihat informasi akun petani dan kelola status akses mereka.</p></div></div>
<section class="admin-card">
    <div class="admin-card-header"><div><h2>Daftar petani <span class="admin-count">{{ $farmers->total() }}</span></h2><p>Nama, email, dan status akun dalam satu tempat.</p></div></div>
    <form class="admin-filter" action="{{ route('admin.users.index') }}" method="GET">
        <div class="admin-search"><label class="visually-hidden" for="search">Cari nama atau email</label><i class="bi bi-search" aria-hidden="true"></i><input class="form-control" type="search" id="search" name="search" value="{{ $search }}" placeholder="Cari nama atau email petani…" maxlength="150"></div>
        <div><label class="visually-hidden" for="status">Status akun</label><select class="form-select" id="status" name="status"><option value="">Semua status</option><option value="aktif" @selected($status === 'aktif')>Aktif</option><option value="nonaktif" @selected($status === 'nonaktif')>Nonaktif</option></select></div>
        <button class="btn btn-outline-success" type="submit">Terapkan</button>
        @if ($search !== '' || $status !== '')<a class="btn btn-light" href="{{ route('admin.users.index') }}">Reset</a>@endif
    </form>
    @if ($farmers->isEmpty())
        <x-admin-empty-state :title="$search !== '' || $status !== '' ? 'Petani tidak ditemukan' : 'Belum ada akun petani'" :description="$search !== '' || $status !== '' ? 'Coba nama, email, atau status lain untuk menemukan akun.' : 'Petani yang mendaftar akan tampil pada daftar ini.'">
            @if ($search !== '' || $status !== '')<a class="btn btn-outline-success" href="{{ route('admin.users.index') }}">Tampilkan semua petani</a>@endif
        </x-admin-empty-state>
    @else
        <div class="table-responsive"><table class="table admin-table mb-0">
            <caption class="visually-hidden">Daftar akun petani sesuai pencarian dan status yang dipilih</caption>
            <thead><tr><th scope="col">Petani</th><th scope="col">Status</th><th scope="col">Tanggal bergabung</th><th scope="col" class="text-end">Aksi</th></tr></thead>
            <tbody>@foreach ($farmers as $farmer)
                <tr><td>@include('admin.users.identity', ['farmer' => $farmer])</td><td><x-account-status :active="$farmer->is_active" /></td><td class="text-nowrap">{{ $farmer->created_at?->format('d/m/Y') ?? '—' }}</td><td><div class="admin-table-actions">
                    <a class="admin-icon-button" href="{{ route('admin.users.show', $farmer) }}" aria-label="Lihat detail {{ $farmer->name }}" title="Lihat detail"><i class="bi bi-eye" aria-hidden="true"></i></a>
                    <x-admin-status-button :farmer="$farmer" compact />
                </div></td></tr>
            @endforeach</tbody>
        </table></div>
        <div class="admin-pagination">{{ $farmers->links('pagination::bootstrap-5') }}</div>
    @endif
</section>
@include('admin.users.status-dialog')
@endsection
