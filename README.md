# FarmLog

Aplikasi web manajemen lahan pertanian untuk proyek UAS Pemrograman Web.

## Tech Stack

- PHP 8.3 dan Laravel 13
- Blade dan Bootstrap 5.3
- MySQL 8
- Vite 8 dan Node.js 24
- PHPUnit 12 dan Laravel Pint

## Menjalankan Proyek

1. Nyalakan MySQL melalui Laragon dan buat database kosong bernama `farm_log`.
2. Salin `.env.example` menjadi `.env` jika `.env` belum tersedia.
3. Sesuaikan kredensial `DB_*` di `.env`.
4. Jalankan perintah berikut dari root proyek:

```powershell
composer install
npm.cmd install
php artisan key:generate
php artisan migrate
php artisan db:seed
npm.cmd run build
php artisan serve
```

Seeder membuat akun admin. Pada lingkungan `local`/`testing`, seeder juga membuat petani dan data pertanian contoh. Kata sandi acak akun baru ditampilkan sekali di terminal saat `db:seed`; simpan untuk demo. Menjalankan seeder ulang tidak mengganti kata sandi akun yang sudah ada. Untuk lingkungan selain lokal/pengujian, atur `FARMLOG_ADMIN_PASSWORD` di `.env` sebelum menjalankan seeder; akun demo tidak dibuat.

Untuk test database, buat database MySQL terpisah bernama `farm_log_test`. `php artisan test` memakai database ini dan dapat menghapus ulang isinya; jangan pakai database utama untuk pengujian.

Jika PHP dan Composer Laragon belum masuk `PATH`, gunakan executable lengkap atau tambahkan folder PHP dan Composer Laragon ke `PATH`, lalu buka ulang VS Code.

Dokumentasi kebutuhan dan aturan implementasi tersedia di [PRD.md](PRD.md), [DATABASE.md](DATABASE.md), dan [AGENTS.md](AGENTS.md).

## Autentikasi (Tahap 2)

Halaman login dan registrasi menggunakan font Plus Jakarta Sans serta Bootstrap Icons yang dipasang sebagai dependency npm agar font/ikon konsisten dan dapat dimuat secara lokal saat demo. CSS autentikasi berada di `resources/css/auth.css`. Setelah menarik perubahan dependency, jalankan `npm.cmd ci` lalu `npm.cmd run build`. Layout dua panel akan menjadi satu kolom pada layar kecil.

Setelah menjalankan `npm.cmd run build` dan `php artisan serve`, buka `http://127.0.0.1:8000/login`. Pendaftaran tersedia melalui tautan **Daftar sekarang**. Akun baru selalu menjadi petani aktif; setelah mendaftar, pengguna kembali ke login dengan email terisi.

Admin diarahkan ke `/admin`, petani ke `/dashboard`. Dashboard petani masih berupa halaman tujuan sementara. Keluar menggunakan tombol **Keluar** (POST dengan CSRF).

## UI Panel Admin

Panel admin menggunakan layout responsif dengan sidebar, font Plus Jakarta Sans, dan warna hijau FarmLog. Halaman `/admin` menampilkan jumlah petani aktif/nonaktif serta lima akun terbaru dari database. Menu **Akun Petani** (`/admin/users`) menyediakan pencarian nama/email, filter status, dan pagination 10 akun per halaman. MySQL perlu berjalan untuk membaca data tersebut.

Menu **Lihat detail** menampilkan nama, email, tanggal bergabung, dan status akun tanpa formulir edit. Admin hanya dapat mengaktifkan atau menonaktifkan akses akun petani setelah mengonfirmasi dampaknya pada dialog. Perubahan tersimpan melalui `PATCH /admin/users/{user}/status`; endpoint hanya menerima `is_active` dan menolak input perubahan identitas/peran. Akun admin tidak dapat menjadi target perubahan status.

Panel admin tidak menyediakan tambah, edit identitas, atau hapus akun. Petani mendaftar sendiri; data demo tetap dapat disediakan melalui seeder. Penonaktifan tidak menghapus akun atau riwayat pertanian. Untuk mencoba, buka **Akun Petani**, pilih **Lihat detail** atau aksi status pada daftar, lalu konfirmasi. Akun nonaktif tidak dapat login dan sesi yang sudah aktif akan dihentikan pada request berikutnya. Setelah perubahan status, admin diarahkan ke detail akun dengan pesan hasil operasi.

Login dibatasi 5 permintaan per menit per IP, termasuk percobaan berhasil/gagal; registrasi dibatasi 3 permintaan per jam per IP, termasuk input tidak valid. Jika batas tercapai, server mengembalikan HTTP 429 beserta waktu tunggu. Kata sandi registrasi minimal 8 karakter dan dibatasi 72 byte sesuai batas bcrypt. Akun nonaktif tidak dapat login dan sesi yang sudah aktif akan dihentikan pada request berikutnya.

Policy `Land`, `PlantingSeason`, `Activity`, dan `Harvest` sudah tersedia melalui penemuan policy Laravel. Saat membuat CRUD, controller tetap wajib menjalankan otorisasi. Untuk pembuatan data anak, berikan induknya, misalnya `Gate::authorize('create', [PlantingSeason::class, $land])` dan `Gate::authorize('create', [Activity::class, $season])`. Policy menolak akses pengguna lain/admin serta perubahan kegiatan dan panen pada musim selesai.

Jalankan pemeriksaan dengan `php artisan test`, `vendor/bin/pint --test`, dan `npm.cmd run build`. Test database menggunakan `farm_log_test`; data lokal pada `farm_log` tidak digunakan oleh test.
