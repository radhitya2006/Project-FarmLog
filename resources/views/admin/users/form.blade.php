<a class="admin-back-link" href="{{ route('admin.users.index') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali ke akun petani</a>
<div class="admin-page-heading"><div><p class="admin-eyebrow">AKUN PETANI</p><h1>{{ $editing ? 'Edit akun petani' : 'Tambah petani' }}</h1><p>{{ $editing ? 'Tinjau informasi akun dan perbarui data petani.' : 'Siapkan akun agar petani dapat mulai mencatat lahannya.' }}</p></div></div>
<x-admin-preview-notice />
<div class="row g-4">
    <div class="col-xl-8">
        <section class="admin-card">
            <div class="admin-card-header"><div><h2>{{ $editing ? 'Informasi akun' : 'Data petani baru' }}</h2><p>Kolom bertanda <span class="text-danger">*</span> wajib diisi.</p></div>@if($editing)<x-account-status :active="$farmer->is_active" />@endif</div>
            {{-- Preview inputs have no form or names, so entered data cannot be submitted. --}}
            <div class="admin-account-form" role="group" aria-label="Pratinjau formulir akun petani">
                <div class="mb-4"><label class="form-label" for="farmer-name">Nama lengkap <span class="text-danger" aria-hidden="true">*</span></label><input class="form-control" id="farmer-name" type="text" maxlength="150" value="{{ $farmer?->name }}" placeholder="Nama lengkap petani" autocomplete="off" required></div>
                <div class="mb-4"><label class="form-label" for="farmer-email">Email <span class="text-danger" aria-hidden="true">*</span></label><input class="form-control" id="farmer-email" type="email" maxlength="255" value="{{ $farmer?->email }}" placeholder="nama@email.com" autocomplete="off" autocapitalize="none" spellcheck="false" aria-describedby="farmer-email-help" required><p class="form-text" id="farmer-email-help">Email digunakan petani untuk masuk ke FarmLog.</p></div>
                @unless ($editing)
                    <div class="mb-4"><label class="form-label" for="farmer-password">Kata sandi awal <span class="text-danger" aria-hidden="true">*</span></label><div class="password-field"><input class="form-control" id="farmer-password" type="password" minlength="8" autocomplete="new-password" placeholder="Minimal 8 karakter" aria-describedby="farmer-password-help" required><button class="password-toggle" type="button" data-password-toggle="farmer-password" data-password-label="kata sandi awal" aria-controls="farmer-password" aria-pressed="false" aria-label="Tampilkan kata sandi awal"><i class="bi bi-eye" aria-hidden="true"></i></button></div><p class="form-text" id="farmer-password-help">Gunakan minimal 8 karakter untuk kata sandi awal petani.</p></div>
                @endunless
                @if ($editing)<div class="admin-account-meta"><i class="bi bi-calendar3" aria-hidden="true"></i> Bergabung pada {{ $farmer->created_at?->format('d/m/Y') ?? '—' }}</div>@endif
                <div class="admin-form-actions"><a class="btn btn-light" href="{{ route('admin.users.index') }}">Batal</a><button class="btn btn-success" type="button" disabled aria-describedby="save-preview-help"><i class="bi bi-check2" aria-hidden="true"></i> {{ $editing ? 'Simpan perubahan' : 'Tambah akun' }}</button></div>
                <p class="admin-save-help" id="save-preview-help">Penyimpanan akun tersedia pada tahap berikutnya.</p>
            </div>
        </section>
    </div>
    <div class="col-xl-4"><aside class="admin-guide admin-guide-light"><span class="admin-guide-icon"><i class="bi bi-person-badge" aria-hidden="true"></i></span><h2>Akun untuk petani</h2><p>Setiap akun pada menu ini memiliki peran petani. Peran akun tidak dapat diubah dari panel admin.</p><div class="admin-guide-note"><i class="bi bi-shield-check" aria-hidden="true"></i><span>{{ $editing ? 'Penonaktifan akses tidak menghapus lahan dan riwayat pertanian.' : 'Akun petani baru akan langsung aktif setelah berhasil dibuat.' }}</span></div></aside></div>
</div>
