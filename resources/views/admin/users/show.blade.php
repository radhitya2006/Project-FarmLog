@extends('layouts.admin')
@section('title', 'Detail Akun Petani')
@section('breadcrumb', 'Detail Akun Petani')
@section('content')
<a class="admin-back-link" href="{{ route('admin.users.index') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali ke akun petani</a>
<div class="admin-page-heading">
    <div>
        <p class="admin-eyebrow">AKUN PETANI</p>
        <h1>Detail akun petani</h1>
        <p>Informasi akun dan status akses petani ke FarmLog.</p>
    </div>
</div>
<div class="row g-4">
    <div class="col-xl-8">
        <section class="admin-card">
            <div class="admin-card-header">
                <h2>Informasi akun</h2>
                <x-account-status :active="$farmer->is_active" />
            </div>
            <dl class="admin-account-details mb-0">
                <div><dt>Nama lengkap</dt><dd>{{ $farmer->name }}</dd></div>
                <div><dt>Email</dt><dd>{{ $farmer->email }}</dd></div>
                <div><dt>Tanggal bergabung</dt><dd>{{ $farmer->created_at?->format('d/m/Y') ?? '—' }}</dd></div>
                <div><dt>Status akses</dt><dd>{{ $farmer->is_active ? 'Aktif — dapat masuk ke FarmLog' : 'Nonaktif — tidak dapat masuk ke FarmLog' }}</dd></div>
            </dl>
        </section>
    </div>
    <div class="col-xl-4">
        <section class="admin-guide admin-guide-light">
            <span class="admin-guide-icon"><i class="bi bi-shield-check" aria-hidden="true"></i></span>
            <h2>Kelola status akses</h2>
            <p>Admin hanya dapat mengaktifkan atau menonaktifkan akses. Identitas akun petani tidak dapat diubah melalui panel ini.</p>
            <x-admin-status-button :farmer="$farmer" />
            <div class="admin-guide-note"><i class="bi bi-database-check" aria-hidden="true"></i><span>Penonaktifan tidak menghapus akun, lahan, atau riwayat pertanian.</span></div>
        </section>
    </div>
</div>
@include('admin.users.status-dialog')
@endsection
