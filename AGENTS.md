# Panduan Pengembangan FarmLog

Dokumen ini berlaku untuk seluruh repository. Gunakan panduan ini saat membuat, meninjau, atau mengubah kode agar implementasi tetap konsisten dengan [PRD.md](PRD.md) dan [DATABASE.md](DATABASE.md).

## 1. Sumber Kebenaran dan Batas MVP

- `PRD.md` adalah sumber kebenaran untuk perilaku produk, peran pengguna, alur, dan kriteria penerimaan.
- `DATABASE.md` adalah sumber kebenaran untuk tabel, nama kolom, tipe data, indeks, relasi, serta aturan penghapusan.
- `AGENTS.md` mengatur cara mengimplementasikan kedua dokumen tersebut.
- Jika kebutuhan baru bertentangan dengan PRD atau database, selaraskan dokumen terkait sebelum mengubah implementasi.
- Fokus MVP: registrasi, login, panel admin untuk akun petani, dashboard petani, lahan, musim tanam, kegiatan, dan panen.
- Jangan menambahkan fitur di luar MVP seperti verifikasi email, lupa kata sandi, login sosial, CAPTCHA, keuangan, cuaca, peta, notifikasi, atau inventaris kecuali diminta secara eksplisit.

## 2. Aturan Teknologi

- Backend menggunakan PHP dan Laravel versi stabil yang kompatibel dengan lingkungan proyek.
- Aplikasi berbentuk monolitik MVC. Halaman dirender di server menggunakan Blade.
- Gunakan Bootstrap untuk komponen dan tata letak responsif. Gunakan Vite hanya untuk mengelola aset frontend.
- Gunakan MySQL, Eloquent ORM, migration, factory, dan seeder Laravel.
- Autentikasi menggunakan session dan cookie Laravel. Jangan membuat REST API atau autentikasi token untuk MVP.
- Gunakan PHPUnit melalui fasilitas test Laravel dan `RefreshDatabase` untuk pengujian yang menyentuh database.
- Gunakan Laravel Pint untuk format PHP dan PSR-12 sebagai gaya dasar.
- Jangan menambahkan React, Vue, TypeScript, Tailwind, jQuery, atau framework backend lain tanpa perubahan arsitektur yang disetujui dan pembaruan dokumentasi.
- Tambahkan dependency hanya jika kebutuhan tidak dapat diselesaikan secara wajar dengan Laravel, Blade, Bootstrap, atau PHP standar. Jelaskan alasan dependency dalam perubahan yang menambahkannya.

Versi PHP, Laravel, Node.js, MySQL, dan dependency dikunci oleh file manifest dan lock setelah proyek diinisialisasi. Jangan menaikkan versi mayor sebagai bagian dari perubahan fitur biasa.

## 3. Struktur Folder yang Disarankan

Ikuti struktur standar Laravel dan kelompokkan kode berdasarkan tanggung jawab berikut:

```text
app/
├── Enums/                       # Nilai role, status, dan satuan yang dibatasi
├── Http/
│   ├── Controllers/
│   │   ├── Auth/                # Register, login, logout
│   │   ├── Admin/               # Pengelolaan akun petani
│   │   └── Farmer/              # Dashboard, lahan, musim, kegiatan, panen
│   ├── Middleware/              # Akun aktif dan pemeriksaan role
│   └── Requests/
│       ├── Auth/
│       ├── Admin/
│       └── Farmer/              # Form Request per operasi create/update
├── Models/                      # User, Land, PlantingSeason, Activity, Harvest
├── Policies/                    # Pemeriksaan kepemilikan setiap resource
└── Services/                    # Hanya untuk alur lintas model atau transaksi kompleks

database/
├── factories/
├── migrations/
└── seeders/

resources/
├── css/
├── js/
└── views/
    ├── layouts/
    ├── components/
    ├── auth/
    ├── admin/users/
    ├── farmer/dashboard/
    ├── farmer/lands/
    ├── farmer/planting-seasons/
    ├── farmer/activities/
    ├── farmer/harvests/
    └── errors/

routes/
└── web.php

tests/
├── Feature/
│   ├── Auth/
│   ├── Admin/
│   └── Farmer/
└── Unit/
```

Jangan membuat repository layer untuk membungkus Eloquent atau service untuk setiap CRUD. Tambahkan service ketika satu operasi melibatkan beberapa model, transaksi database, atau aturan bisnis yang memang tidak cocok ditempatkan di controller/model.

## 4. Konvensi Penamaan

- Kode, class, method, variable, route name, tabel, dan kolom menggunakan bahasa Inggris.
- Teks antarmuka, pesan validasi, flash message, dan judul halaman menggunakan Bahasa Indonesia.
- Class memakai `PascalCase`; method dan variable memakai `camelCase`; tabel dan kolom memakai `snake_case`.
- Nama model berbentuk tunggal: `Land`, `PlantingSeason`, `Activity`, dan `Harvest`.
- Nama tabel berbentuk jamak sesuai `DATABASE.md`: `lands`, `planting_seasons`, `activities`, dan `harvests`.
- Gunakan route name, bukan URL hardcoded, pada redirect dan tautan Blade. Contoh: `admin.users.index`, `farmer.dashboard`, `farmer.lands.index`.
- Gunakan PHP backed enum untuk `UserRole`, `PlantingSeasonStatus`, `ActivityStatus`, dan `HarvestUnit`; cast kolom terkait pada model.

## 5. Pola Implementasi Laravel

### Controller dan Request

- Controller harus tipis: terima request tervalidasi, jalankan policy/otorisasi, panggil operasi domain, lalu kembalikan view atau redirect.
- Gunakan Form Request terpisah untuk create dan update jika aturan validasinya berbeda.
- Jangan membaca field mentah dengan `$request->all()`. Gunakan `$request->validated()` dan pilih field yang memang dapat diubah.
- Gunakan route model binding untuk resource. Tetap jalankan policy karena binding saja tidak memeriksa kepemilikan.
- Operasi yang mengubah beberapa tabel atau menghapus musim beserta data turunannya harus berjalan dalam transaksi bila ada langkah tambahan di luar cascade database.

### Model dan Relasi

- Definisikan relasi Eloquent dua arah: `User::lands`, `Land::user`, `Land::plantingSeasons`, `PlantingSeason::land`, `PlantingSeason::activities`, dan `PlantingSeason::harvests`.
- Definisikan `$fillable` secara eksplisit atau gunakan assignment field yang eksplisit. Jangan memakai `$guarded = []`.
- Cast `is_active` ke boolean, tanggal ke `date`, dan nilai desimal dengan presisi sesuai kebutuhan tampilan/perhitungan.
- Gunakan eager loading untuk halaman daftar/detail yang membaca relasi agar tidak terjadi query N+1.
- Gunakan query agregat database untuk jumlah lahan, musim aktif, kegiatan tertunda, dan total panen. Jangan menjumlahkan koleksi besar di PHP jika dapat dilakukan dengan query.

### Blade dan Bootstrap

- Gunakan satu layout utama untuk petani dan satu layout/pola navigasi admin bila menunya berbeda.
- Pecah komponen berulang seperti alert, error form, input, modal konfirmasi, badge status, pagination, dan empty state menjadi Blade component/partial.
- Semua form perubahan data memakai `@csrf`; operasi update/delete memakai method spoofing yang sesuai.
- Tampilkan error validasi dekat field dan pertahankan nilai lama dengan `old()`.
- Semua halaman harus nyaman digunakan pada layar ponsel. Tabel lebar dibungkus `.table-responsive`.
- Aksi destruktif harus memiliki dialog konfirmasi yang menjelaskan data yang ikut terhapus.
- Hindari CSS dan JavaScript inline berulang. Letakkan aset proyek di `resources/css` dan `resources/js`.

## 6. Autentikasi, Role, dan Kepemilikan Data

- Registrasi publik selalu membuat `role = petani` dan `is_active = true` di server. Abaikan atau tolak input `role` dan `is_active` dari client.
- Normalisasi email pada registrasi, login, dan pengelolaan akun admin dengan aturan yang sama: hapus whitespace, lalu ubah ke huruf kecil sebelum validasi/penyimpanan.
- Kata sandi minimal 8 karakter dan selalu disimpan melalui hashing Laravel.
- Setelah registrasi, redirect ke login dengan pesan sukses dan email melalui flash session. Jangan login otomatis.
- Login dibatasi 5 percobaan per menit. Registrasi dibatasi 3 percobaan per jam per alamat IP.
- Admin diarahkan ke `/admin`; petani diarahkan ke `/dashboard`.
- Petani yang membuka route admin menerima `403 Forbidden`. Admin yang membuka dashboard petani dialihkan ke panel admin.
- Middleware akun aktif harus memblokir login dan request berikutnya dari akun nonaktif. Saat diblokir, hapus session autentikasi dengan aman.
- Hanya seeder yang boleh membuat admin. Panel admin tidak boleh menampilkan atau menerima perubahan role.
- Gunakan policy untuk `Land`, `PlantingSeason`, `Activity`, dan `Harvest`. Pemeriksaan harus menelusuri pemilik sampai `users.id`; menyembunyikan tombol bukan otorisasi.
- Query halaman petani harus dimulai dari user yang sedang login atau dibatasi dengan foreign key pemilik. Jangan mengambil resource global lalu hanya mengandalkan ID dari URL.

## 7. Aturan Database dan Domain

- Ikuti nama kolom, tipe data, indeks, dan foreign key di `DATABASE.md`. Perubahan skema harus dibuat melalui migration baru; jangan mengubah migration lama yang sudah dipakai bersama.
- `users → lands` dan `lands → planting_seasons` memakai `ON DELETE RESTRICT`.
- `planting_seasons → activities` dan `planting_seasons → harvests` memakai `ON DELETE CASCADE`.
- Akun petani dinonaktifkan dengan `is_active`; jangan hapus permanen akun dalam MVP.
- Lahan tidak dapat dihapus selama masih memiliki musim tanam.
- Penghapusan musim tanam harus dikonfirmasi dan ikut menghapus kegiatan serta panen terkait.
- Musim `selesai` tetap dapat dibaca. Blokir penambahan/perubahan kegiatan dan panen sampai musim diaktifkan kembali.
- `area_hectares` dan `quantity` wajib lebih dari nol. Perkiraan panen tidak boleh sebelum tanggal tanam; tanggal panen tidak boleh sebelum tanggal tanam.
- Satuan panen MVP hanya `kg` dan `ton`. Simpan serta jumlahkan secara terpisah; jangan mengonversi atau menjumlahkan dua satuan secara langsung.
- Tambahkan indeks berdasarkan `DATABASE.md`. Jangan menambah kolom turunan seperti total panen karena nilainya dapat dihitung dari data sumber.
- Seeder wajib menyediakan satu akun admin dan data demo secukupnya. Kredensial demo tidak boleh menjadi kredensial production dan harus didokumentasikan di README atau output seeder.

## 8. Routing dan Respons

- Letakkan seluruh endpoint browser di `routes/web.php`.
- Kelompokkan route guest untuk login/registrasi, route admin dengan prefix `/admin` dan name `admin.*`, serta route petani yang memakai middleware autentikasi, akun aktif, dan role petani.
- Gunakan resource route untuk CRUD saat bentuk URL-nya cocok. Aksi domain seperti menyelesaikan atau mengaktifkan kembali musim memakai route bernama eksplisit dengan method `PATCH`.
- Gunakan redirect + flash message untuk operasi form yang berhasil/gagal secara bisnis. Gunakan status `403` untuk pelanggaran role atau kepemilikan.
- Gunakan pagination untuk daftar akun, lahan, musim, kegiatan, dan panen; jangan memuat seluruh isi tabel tanpa batas.

## 9. Pengujian Wajib

Tambahkan Feature test untuk perilaku yang berubah. Prioritaskan alur dan batas keamanan berikut:

- registrasi, normalisasi email, unique email, password minimal, flash email, dan rate limit;
- login, logout, redirect berdasarkan role, akun nonaktif, dan rate limit;
- petani mendapat `403` pada panel admin dan admin dialihkan dari dashboard petani;
- admin dapat mengelola status/data petani tetapi tidak dapat mengubah role;
- petani hanya dapat mengakses lahan, musim, kegiatan, dan panen miliknya;
- validasi tanggal, angka positif, status, dan satuan;
- larangan menghapus lahan yang masih memiliki musim;
- cascade saat menghapus musim tanam;
- musim selesai bersifat read-only sampai diaktifkan kembali;
- ringkasan dashboard dan agregasi panen per satuan.

Gunakan factory untuk menyiapkan data, `RefreshDatabase` untuk isolasi, dan assertion pada database, redirect, session, serta status HTTP. Tambahkan Unit test hanya untuk aturan murni yang tidak perlu HTTP atau database; jangan menduplikasi Feature test.

Perintah verifikasi standar setelah proyek tersedia:

```bash
php artisan test
vendor/bin/pint --test
npm run build
```

Jalankan pemeriksaan yang relevan terhadap perubahan. Perubahan autentikasi, policy, migration, atau relasi harus menjalankan seluruh test suite.

## 10. Keamanan dan Konfigurasi

- Jangan commit `.env`, credential, token, database dump, log, atau data pribadi. Perbarui `.env.example` bila menambah variable konfigurasi.
- Gunakan CSRF bawaan Laravel, session regeneration setelah login, invalidasi session saat logout, dan escaping Blade default `{{ }}`.
- Hindari raw SQL. Jika diperlukan, gunakan binding parameter dan jelaskan alasan pemakaiannya.
- Jangan menampilkan stack trace atau pesan database kepada pengguna. Tampilkan pesan Bahasa Indonesia yang aman dan catat detail teknis melalui logger.
- Jangan log kata sandi, session ID, cookie, atau seluruh payload autentikasi.
- Validasi dan otorisasi tetap dilakukan di server meskipun frontend sudah membatasi input atau menyembunyikan aksi.

## 11. Git, Dokumentasi, dan Definition of Done

- Jangan commit dependency, hasil build, cache, log, atau `.env`; ikuti `.gitignore`.
- Pertahankan perubahan pengguna yang tidak terkait. Jangan melakukan reset, rewrite, atau penghapusan massal tanpa instruksi eksplisit.
- Satu perubahan sebaiknya memiliki tujuan yang jelas. Hindari refactor luas ketika mengerjakan fitur kecil.
- Perbarui `PRD.md` jika perilaku atau cakupan produk berubah; perbarui `DATABASE.md` jika skema, indeks, atau aturan relasi berubah; perbarui `README.md` jika setup atau perintah pengembangan berubah.

Sebuah perubahan selesai ketika:

1. Perilaku sesuai PRD dan skema sesuai DATABASE.
2. Validasi, autentikasi, otorisasi, dan kepemilikan data sudah diterapkan pada server.
3. UI berbahasa Indonesia, responsif, dan memiliki pesan sukses, error, serta empty state yang jelas.
4. Migration, factory, seeder, dan test yang relevan ikut diperbarui.
5. Test, pemeriksaan format, dan build frontend yang relevan lulus.
6. Tidak ada rahasia, artefak build, debug statement, atau perubahan di luar cakupan yang ikut masuk.
