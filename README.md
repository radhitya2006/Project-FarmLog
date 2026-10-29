# FarmLog

Aplikasi web manajemen lahan pertanian untuk proyek UAS Pemrograman Web.

## Tech Stack

- PHP 8.3 dan Laravel 13
- Blade dan Bootstrap 5.3
- MySQL 8
- Vite 8 dan Node.js 24
- PHPUnit 12 dan Laravel Pint

## Menjalankan Proyek

1. Nyalakan MySQL melalui Laragon dan buat database bernama `farmlog`.
2. Salin `.env.example` menjadi `.env` jika `.env` belum tersedia.
3. Sesuaikan kredensial `DB_*` di `.env`.
4. Jalankan perintah berikut dari root proyek:

```powershell
composer install
npm.cmd install
php artisan key:generate
php artisan migrate
npm.cmd run build
php artisan serve
```

Jika PHP dan Composer Laragon belum masuk `PATH`, gunakan executable lengkap atau tambahkan folder PHP dan Composer Laragon ke `PATH`, lalu buka ulang VS Code.

Dokumentasi kebutuhan dan aturan implementasi tersedia di [PRD.md](PRD.md), [DATABASE.md](DATABASE.md), dan [AGENTS.md](AGENTS.md).
