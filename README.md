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
