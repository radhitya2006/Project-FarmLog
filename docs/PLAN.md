# Rencana Pengembangan FarmLog

## Ringkasan

Repository saat ini masih berupa kerangka Laravel 13: baru ada halaman `welcome`, migration `users`, dan test bawaan. Pengembangan berikutnya berfokus pada MVP dalam `PRD.md`. Desain login yang kamu tunjukkan menjadi acuan visual, dengan tautan lupa kata sandi, “Ingat saya”, dan pengisian akun demo tidak ditampilkan pada MVP.

## Tahapan implementasi

1. **Fondasi data.** Pastikan koneksi MySQL `farmlog`, lalu buat migration untuk `lands`, `planting_seasons`, `activities`, dan `harvests` sesuai `DATABASE.md`. Lengkapi enum, model, relasi, factory, serta seeder admin dan data demo. Selaraskan `User.php` bawaan Laravel yang masih merujuk `email_verified_at` dan `remember_token`, padahal kolom tersebut tidak ada di skema.

2. **Autentikasi dan akses.** Buat registrasi, login, dan logout berbasis session; normalisasi email; password minimal delapan karakter; batas percobaan; penolakan akun nonaktif; serta pengalihan berdasarkan peran. Terapkan middleware dan policy sejak awal agar batas admin dan kepemilikan data dapat diuji sebelum membuat CRUD.

3. **Fondasi UI.** Buat token warna dan tipografi, layout autentikasi mengikuti mockup, serta layout admin dan petani dengan sidebar responsif. Siapkan komponen form, pesan validasi, alert, badge, empty state, pagination, dan dialog konfirmasi. Semua teks antarmuka berbahasa Indonesia.

4. **Fitur inti secara berurutan.** Kerjakan panel admin untuk akun petani, lalu CRUD lahan, musim tanam, kegiatan, dan panen. Setelah data tersebut tersedia, buat dashboard petani dengan agregasi hasil panen `kg` dan `ton` yang terpisah. Setiap modul mencakup validasi, otorisasi, tampilan mobile, dan Feature test sebelum lanjut ke modul berikutnya.

5. **Penyelesaian MVP.** Uji alur dari pendaftaran sampai pencatatan panen, akses lintas akun, penghapusan musim beserta data turunannya, dan musim selesai yang hanya bisa dibaca sampai diaktifkan kembali. Jalankan `php artisan test`, `vendor/bin/pint --test`, dan `npm.cmd run build`; perbarui README dengan langkah setup dan kredensial demo lokal.

## Batas dan keputusan

AI Insight per musim tanam ditempatkan **setelah MVP memenuhi kriteria penerimaan**. Pada tahap itu, fitur AI dirinci dahulu di `PRD.md`; `DATABASE.md` diperbarui hanya jika insight perlu disimpan. Tidak ada perubahan file atau database yang dilakukan dalam tahap perencanaan ini.

Milestone terdekat yang paling masuk akal adalah **fondasi data dan autentikasi**. Hasilnya dapat diuji melalui registrasi, login admin/petani, pembatasan peran, dan relasi database sebelum pengerjaan seluruh halaman CRUD.
