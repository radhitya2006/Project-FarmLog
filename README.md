# FarmLog

Aplikasi web manajemen lahan pertanian untuk proyek UAS Pemrograman Web.

## Tech Stack

- PHP 8.3 dengan ekstensi BCMath dan Laravel 13
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

Dokumentasi kebutuhan dan aturan implementasi tersedia di [PRD.md](docs/PRD.md), [DATABASE.md](docs/DATABASE.md), dan [AGENTS.md](docs/AGENTS.md).

## Autentikasi (Tahap 2)

Halaman login dan registrasi menggunakan font Plus Jakarta Sans serta Bootstrap Icons yang dipasang sebagai dependency npm agar font/ikon konsisten dan dapat dimuat secara lokal saat demo. CSS autentikasi berada di `resources/css/auth.css`. Setelah menarik perubahan dependency, jalankan `npm.cmd ci` lalu `npm.cmd run build`. Layout dua panel akan menjadi satu kolom pada layar kecil.

Setelah menjalankan `npm.cmd run build` dan `php artisan serve`, buka `http://127.0.0.1:8000/login`. Pendaftaran tersedia melalui tautan **Daftar sekarang**. Akun baru selalu menjadi petani aktif; setelah mendaftar, pengguna kembali ke login dengan email terisi.

Admin diarahkan ke `/admin`, petani ke `/dashboard`. Keluar menggunakan tombol **Keluar** (POST dengan CSRF).

## Dashboard Petani

Dashboard menampilkan jumlah lahan, jumlah musim aktif, dan jumlah kegiatan yang belum selesai milik petani. Kartu musim aktif dipaginasi 6 musim per halaman dengan tanggal tanam terbaru lebih dahulu (lalu ID terbaru untuk tanggal sama). Musim selesai tidak masuk ke kartu aktif; data petani lain tidak ditampilkan pada seluruh bagian Dashboard.

Tombol **Catat kegiatan**, **Catat panen**, dan **Musim tanam aktif** membawa petani ke bagian musim aktif pada halaman Dashboard. Pilih kartu berdasarkan komoditas, nama lahan, tanggal tanam, dan perkiraan panen. Setiap kartu menyediakan formulir **Catat kegiatan** dan **Catat panen** untuk musim tersebut, **Detail musim**, serta **Lihat kegiatan**. **Lahan saya** membuka daftar lahan.

Petani tanpa lahan mendapat arahan **Tambah lahan**. Petani yang sudah memiliki lahan tetapi belum memiliki musim aktif mendapat arahan **Buka lahan saya** untuk membuat atau mengaktifkan kembali musim. Tombol pencatatan cepat dinonaktifkan jika tidak ada musim aktif. Status musim dan kepemilikan tetap diperiksa pada halaman tujuan; musim yang sudah selesai tidak menerima kegiatan atau panen baru meskipun Dashboard sebelumnya masih menampilkannya. Sidebar berisi Dashboard, Lahan, dan Keuangan.

**Kegiatan belum selesai** menampilkan kegiatan direncanakan dari seluruh musim, termasuk musim selesai yang catatannya terkunci. Urutannya tanggal terlama lalu ID, dengan tautan langsung ke detail kegiatan. **Ringkasan hasil panen** menampilkan musim yang memiliki catatan panen, termasuk musim selesai, dengan total kg dan ton terpisah serta tautan ke daftar panen. Setiap bagian memuat 6 baris per halaman. Parameter `page`, `activities_page`, dan `harvest_page` berdiri sendiri, mempertahankan halaman bagian lainnya dan mengarahkan kembali ke bagian yang sesuai.

## Menu Lahan Petani

Setelah login sebagai petani, buka **Lahan** pada sidebar atau `/lands`. Daftar menggunakan kartu, pencarian nama/lokasi, dan pagination 12 lahan per halaman dengan urutan terbaru. Sidebar berubah menjadi menu yang dapat dibuka pada layar ponsel.

Pilih **Tambah lahan**, isi nama, luas dalam hektare, lokasi teks, dan catatan opsional. Luas harus 0,01–99.999.999,99 hektare dengan maksimal dua angka desimal; nama maksimal 150 karakter, lokasi 255 karakter, dan catatan 5.000 karakter. Setelah menyimpan, halaman detail menampilkan data dan jumlah musim tanam. **Edit lahan** menyimpan perubahan lalu kembali ke detail.

**Hapus lahan** meminta konfirmasi dan hanya tersedia untuk lahan tanpa musim tanam. Server juga menolak penghapusan jika masih ada musim aktif maupun selesai. Data dan operasi CRUD dibatasi ke pemilik; petani lain mendapat respons 403, ID tidak ditemukan mendapat 404, dan admin diarahkan ke panel admin.

Untuk mencoba alur, tambah lahan baru → lihat detail → edit → hapus melalui dialog konfirmasi. Coba pencarian dan akun tanpa lahan untuk melihat empty state. Pada lahan yang memiliki musim tanam, hapus seluruh musim melalui menu Musim Tanam terlebih dahulu sebelum menghapus lahan. Jalankan `npm.cmd run build` untuk memuat aset terbaru.

## Musim Tanam Petani

Buka **Lahan → Lihat detail → Lihat musim tanam**. Daftar pada `/lands/{land}/planting-seasons` hanya menampilkan musim di lahan tersebut, dengan filter semua/aktif/selesai, urutan terbaru, dan pagination 12 musim. Tekan **Terapkan** setelah memilih status; **Reset** menghapus filter status.

**Tambah musim tanam** tersedia dari detail lahan maupun daftar musim pada lahan tersebut. Lahan otomatis ditentukan dari halaman yang sedang dibuka. Petani yang belum memiliki lahan perlu menambah lahan melalui menu **Lahan** terlebih dahulu.

Sidebar petani memiliki tiga menu: **Dashboard**, **Lahan**, dan **Keuangan**. Menu Lahan tetap aktif saat membuka daftar, detail, tambah, atau edit musim tanam. Menu Keuangan aktif pada ringkasan keuangan dan formulir transaksi. Tautan kembali pada halaman musim membawa pengguna ke daftar musim pada lahan tersebut atau detail lahannya. Seluruh endpoint musim memakai lahan sebagai induk.

Isi komoditas (maksimal 150 karakter), tanggal tanam, perkiraan panen, dan catatan opsional (maksimal 5.000 karakter). Perkiraan panen tidak boleh sebelum tanggal tanam. Musim baru selalu aktif; satu lahan boleh memiliki beberapa musim aktif. Setelah simpan atau edit, pengguna kembali ke detail musim yang menampilkan informasi dan jumlah kegiatan/panen. Tanggal tanam tidak dapat digeser melewati tanggal panen yang sudah tercatat.

**Selesaikan musim** mengunci edit informasi musim serta perubahan kegiatan/panen. **Aktifkan kembali** membuka penguncian tersebut. Kedua aksi membutuhkan konfirmasi dan menggunakan endpoint `PATCH .../{planting_season}/finish` atau `/reopen`. Permintaan berulang pada status yang sama tidak mengubah data. Form tambah/edit tidak menerima `status`, `land_id`, atau `user_id`.

**Hapus musim tanam** tersedia untuk musim aktif maupun selesai. Dialog menjelaskan jumlah kegiatan dan catatan panen yang ikut dihapus permanen; server mensyaratkan konfirmasi. Setelah hapus, pengguna kembali ke daftar musim. Jika seluruh musim pada lahan sudah dihapus, lahan dapat dihapus. Musim milik petani lain ditolak dengan 403; pasangan lahan–musim yang tidak cocok mendapat 404.

Untuk mencoba alur: tambah musim → edit informasi → catat kegiatan/panen → selesaikan → pastikan perubahan terkunci → aktifkan kembali → hapus setelah konfirmasi. Detail musim menampilkan jumlah kegiatan/panen dan total panen per satuan. Tidak diperlukan migration atau dependency baru. Pengujian otomatis menggunakan `farm_log_test`, termasuk cascade penghapusan dan perlindungan kepemilikan.

## Kegiatan Pertanian

Buka **Lahan → Detail lahan → Musim tanam → Detail musim → Lihat kegiatan**. Tombol **Tambah kegiatan** tersedia dari detail musim dan daftar kegiatan selama musim aktif. Kegiatan berada di dalam musim tanam; sidebar petani tetap berisi Dashboard dan Lahan.

Formulir berisi nama kegiatan (maksimal 150 karakter), tanggal, status **Direncanakan**/**Selesai**, dan keterangan opsional (maksimal 5.000 karakter). Tanggal sebelum tanggal tanam diperbolehkan untuk persiapan lahan. Setelah simpan atau edit, petani diarahkan ke detail kegiatan. Daftar menyediakan pencarian nama, filter status, dan pagination 12 kegiatan per halaman; urutannya tanggal terbaru, lalu ID terbaru jika tanggal sama.

Pada detail kegiatan, gunakan **Tandai selesai** untuk menyelesaikan kegiatan yang direncanakan. Permintaan ulang tidak mengubah waktu pembaruan. Status dapat dikembalikan ke **Direncanakan** melalui edit selama musim masih aktif. **Hapus kegiatan** meminta konfirmasi dan hanya menghapus kegiatan tersebut. Penghapusan kembali ke daftar kegiatan.

Saat musim selesai, daftar dan detail kegiatan tetap bisa dibaca; tambah, edit, hapus, dan tandai selesai ditolak di server. Aktifkan kembali musim melalui detail musim untuk melanjutkan perubahan. Semua route berada di `/lands/{land}/planting-seasons/{planting_season}/activities`; aksi selesai menggunakan `PATCH .../{activity}/finish`. Pemilik dan musim ditentukan di server, bukan dari input formulir. Akses petani lain mendapat 403, sedangkan pasangan induk/resource yang tidak cocok atau tidak ditemukan mendapat 404.

Untuk mencoba: tambah kegiatan → cari/filter di daftar → buka detail → tandai selesai → edit → hapus setelah konfirmasi. Coba juga menyelesaikan musim untuk memastikan kegiatan terkunci, lalu aktifkan kembali. Tidak diperlukan migration, seeder, atau dependency baru. Jalankan `npm.cmd run build` untuk memuat aset terbaru; seluruh pengujian database menggunakan `farm_log_test`.

## Hasil Panen

Buka **Lahan → Detail lahan → Musim tanam → Detail musim → Lihat hasil panen**. **Catat panen** tersedia pada detail musim, daftar panen, serta kartu musim aktif di Dashboard. Form menerima tanggal panen, jumlah 0,01–9.999.999.999,99 dengan maksimal dua angka desimal, satuan kg/ton, serta catatan opsional maksimal 5.000 karakter. Tanggal panen tidak boleh sebelum tanggal tanam. Satu musim dapat memiliki beberapa catatan panen, termasuk pada tanggal yang sama.

Daftar panen menampilkan seluruh satuan dengan pagination 12 catatan, diurutkan tanggal panen terbaru lalu ID. Total pada detail musim, daftar panen, dan Dashboard dihitung dari database per musim dan satuan. Kilogram dan ton tidak dikonversi atau dijumlahkan bersama. Total pada daftar panen mencakup seluruh catatan musim, tidak dipengaruhi halaman, dan tampilan angka mempertahankan presisi desimal.

Simpan/edit kembali ke detail panen dengan pesan sukses. **Hapus catatan panen** meminta konfirmasi dan kembali ke daftar, tanpa menghapus musim atau catatan lain. Musim selesai tetap dapat dibaca, tetapi tambah/edit/hapus ditolak di server sampai diaktifkan kembali. Semua route panen berada di `/lands/{land}/planting-seasons/{planting_season}/harvests`; pemilik dan musim ditentukan dari server. Petani lain mendapat 403, resource yang tidak ditemukan atau pasangan lahan–musim–panen yang salah mendapat 404. Penulisan panen dan perubahan musim memakai penguncian musim dalam transaksi agar aturan status/tanggal tetap diperiksa saat penyimpanan.

Untuk mencoba alur lengkap, daftar akun petani → login → tambah lahan → tambah musim → catat dan selesaikan kegiatan → catat beberapa panen kg/ton → lihat ringkasan Dashboard → selesaikan musim → periksa penguncian → aktifkan kembali. Penghapusan seluruh musim setelah konfirmasi menghapus kegiatan/panen turunannya dan memungkinkan lahan tanpa musim dihapus. Jalankan `php artisan test`, `php vendor/bin/pint --test`, dan `npm.cmd run build`. Tidak ada migration, dependency, atau perubahan kredensial yang diperlukan.

## Keuangan per Musim

Setelah mengambil perubahan, pastikan BCMath aktif (`php --ri bcmath`), jalankan `composer install`, `php artisan migrate`, dan `npm.cmd run build`. Dua migration baru menambahkan `expenses` dan `sales` tanpa mengubah catatan lama. Seeder menyediakan contoh transaksi pada musim demo jika dijalankan secara eksplisit; tidak diperlukan untuk memakai fitur.

Buka **Keuangan** di sidebar (`/finance`) untuk melihat total uang seluruh musim, lalu pilih kartu musim. Tombol **Keuangan musim** di detail musim membuka halaman yang sama: `/lands/{land}/planting-seasons/{planting_season}/finance`. Daftar musim memuat 12 kartu per halaman. Detail keuangan menyediakan **Catat pengeluaran** dan **Catat penjualan** beserta edit/hapus melalui dialog konfirmasi. Kedua daftar transaksi dipaginasi 10 baris secara independen dengan parameter `expenses_page` dan `sales_page`, tanggal terbaru lalu ID terbaru.

Pengeluaran menyimpan tanggal, nama, kategori (bibit, pupuk, pestisida, upah, irigasi, sewa, transportasi, lainnya), nominal, dan catatan opsional. Penjualan menyimpan tanggal penerimaan uang, keterangan, nominal, dan catatan. Semua nominal dalam rupiah, positif, maksimal 9.999.999.999.999,99 dengan dua angka desimal; tanggal boleh sebelum tanggal tanam. Pencatatan hanya untuk uang yang sudah dibayar/diterima. Stok terjual, harga/kg, piutang, utang, dan penyusutan tidak dicakup.

**Selisih tercatat** = pemasukan penjualan − pengeluaran, bukan jaminan laba final saat pencatatan belum lengkap. **Biaya/kg** = pengeluaran ÷ (panen kg + panen ton × 1.000), dibulatkan half-up dua desimal memakai BCMath. Jika biaya belum tercatat atau panen nol, tampil **—** dengan alasan. Ringkasan panen asal tetap memisahkan kg dan ton. Total mencakup seluruh transaksi, tidak hanya halaman aktif; uang dihitung dan ditampilkan tanpa float.

Transaksi tetap dapat diperbarui saat musim selesai. Penguncian informasi musim, kegiatan, dan panen tetap berlaku. Scoped binding dan policy memastikan hanya pemilik dapat mengakses transaksi; data ID induk dari formulir ditolak. Penghapusan musim setelah konfirmasi menghapus pengeluaran dan penjualan turunannya, dan dialog menampilkan jumlah catatan yang terdampak.

Untuk mencoba: **Keuangan → pilih musim → catat biaya → catat penjualan → lihat selisih dan biaya/kg**. Berat panen diambil dari catatan panen musim tersebut. Uji menggunakan `php artisan test`; database pengujian adalah `farm_log_test`.

## UI Panel Admin

Panel admin menggunakan layout responsif dengan sidebar, font Plus Jakarta Sans, dan warna hijau FarmLog. Halaman `/admin` menampilkan jumlah petani aktif/nonaktif serta lima akun terbaru dari database. Menu **Akun Petani** (`/admin/users`) menyediakan pencarian nama/email, filter status, dan pagination 10 akun per halaman. MySQL perlu berjalan untuk membaca data tersebut.

Menu **Lihat detail** menampilkan nama, email, tanggal bergabung, dan status akun tanpa formulir edit. Admin hanya dapat mengaktifkan atau menonaktifkan akses akun petani setelah mengonfirmasi dampaknya pada dialog. Perubahan tersimpan melalui `PATCH /admin/users/{user}/status`; endpoint hanya menerima `is_active` dan menolak input perubahan identitas/peran. Akun admin tidak dapat menjadi target perubahan status.

Panel admin tidak menyediakan tambah, edit identitas, atau hapus akun. Petani mendaftar sendiri; data demo tetap dapat disediakan melalui seeder. Penonaktifan tidak menghapus akun atau riwayat pertanian. Untuk mencoba, buka **Akun Petani**, pilih **Lihat detail** atau aksi status pada daftar, lalu konfirmasi. Akun nonaktif tidak dapat login dan sesi yang sudah aktif akan dihentikan pada request berikutnya. Setelah perubahan status, admin diarahkan ke detail akun dengan pesan hasil operasi.

Login dibatasi 5 permintaan per menit per IP, termasuk percobaan berhasil/gagal; registrasi dibatasi 3 permintaan per jam per IP, termasuk input tidak valid. Jika batas tercapai, server mengembalikan HTTP 429 beserta waktu tunggu. Kata sandi registrasi minimal 8 karakter dan dibatasi 72 byte sesuai batas bcrypt. Akun nonaktif tidak dapat login dan sesi yang sudah aktif akan dihentikan pada request berikutnya.

Policy `Land`, `PlantingSeason`, `Activity`, dan `Harvest` sudah tersedia melalui penemuan policy Laravel. Saat membuat CRUD, controller tetap wajib menjalankan otorisasi. Untuk pembuatan data anak, berikan induknya, misalnya `Gate::authorize('create', [PlantingSeason::class, $land])` dan `Gate::authorize('create', [Activity::class, $season])`. Policy menolak akses pengguna lain/admin serta perubahan kegiatan dan panen pada musim selesai.

Jalankan pemeriksaan dengan `php artisan test`, `vendor/bin/pint --test`, dan `npm.cmd run build`. Test database menggunakan `farm_log_test`; data lokal pada `farm_log` tidak digunakan oleh test.
