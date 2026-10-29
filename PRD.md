# Product Requirements Document (PRD)

## Web Manajemen Lahan Pertanian

| Informasi | Keterangan |
| --- | --- |
| Jenis proyek | Proyek UAS Pemrograman Web |
| Platform | Aplikasi web responsif |
| Pengguna | Admin dan petani |
| Cakupan MVP | Registrasi, login, dan pengelolaan lahan sampai pencatatan hasil panen |
| Bahasa antarmuka | Bahasa Indonesia |

## 1. Ringkasan Produk

Web Manajemen Lahan Pertanian adalah aplikasi yang membantu petani mencatat dan memantau lahan, musim tanam, kegiatan pertanian, serta hasil panen. Aplikasi menyimpan riwayat setiap musim tanam agar petani dapat melihat kembali kegiatan dan hasil dari lahan yang dikelolanya.

Dalam MVP, petani membuat akun sendiri dan login. Admin mengelola status akun petani. Setiap petani hanya boleh melihat dan mengubah data miliknya.

## 2. Tujuan Produk

- Menyediakan pencatatan lahan dan musim tanam secara terstruktur.
- Membantu petani mengetahui kegiatan yang direncanakan dan yang sudah selesai.
- Menyimpan hasil panen, termasuk panen yang dilakukan beberapa kali dalam satu musim.
- Menyediakan ringkasan kondisi usaha tani pada satu halaman.
- Menyediakan registrasi mandiri dan login bagi petani.
- Menunjukkan penerapan autentikasi, otorisasi berbasis peran, relasi basis data, validasi, dan operasi CRUD dalam proyek UAS.

## 3. Target Pengguna

### 3.1 Admin

Admin adalah pengelola aplikasi. Admin menggunakan panel admin untuk:

- masuk melalui halaman login yang sama dengan petani;
- melihat daftar akun petani;
- membuat akun petani secara manual jika diperlukan;
- mengubah nama, email, dan data akun petani;
- mengelola status akun tanpa dapat mengubah peran akun;
- mengaktifkan atau menonaktifkan akun petani; dan
- keluar dari aplikasi.

Akun admin pertama dibuat melalui database seeder. Pendaftaran publik hanya menghasilkan akun dengan peran petani; akun admin tidak dapat dibuat melalui formulir registrasi. Admin tidak dapat mengubah peran akun.

### 3.2 Petani

Petani adalah pengguna utama aplikasi. Petani dapat:

- membuat akun secara mandiri dan login;
- melihat ringkasan data pertanian miliknya;
- mengelola data lahan;
- mengelola musim tanam pada setiap lahan;
- mencatat dan menyelesaikan kegiatan pertanian; dan
- mencatat hasil panen.

Petani tidak dapat mengakses panel admin atau data milik petani lain. Petani yang sudah login dan membuka halaman login atau registrasi dialihkan ke dashboard.

## 4. Ruang Lingkup MVP

### 4.1 Autentikasi dan Hak Akses

- Petani mendaftar menggunakan nama, email, kata sandi, dan konfirmasi kata sandi.
- Kata sandi harus memiliki minimal 8 karakter.
- Sebelum pemeriksaan keunikan, sistem menghapus spasi dari email lalu mengubahnya menjadi huruf kecil.
- Sistem menetapkan `role = petani` dan `status = aktif` di sisi server; pengguna tidak dapat memilih peran saat mendaftar.
- Setelah registrasi berhasil, sistem mengarahkan petani ke halaman login, mengisi kolom email melalui flash session, dan menampilkan pesan sukses.
- Admin dan petani masuk melalui satu halaman login menggunakan email dan kata sandi.
- Setelah login, admin diarahkan ke `/admin` dan petani diarahkan ke `/dashboard`.
- Petani yang membuka `/admin` menerima respons `403 Forbidden`.
- Admin yang membuka `/dashboard` dialihkan ke `/admin`.
- Login dibatasi maksimal 5 percobaan per menit.
- Registrasi dibatasi maksimal 3 percobaan per jam untuk setiap alamat IP.
- Akun yang dinonaktifkan tidak dapat masuk.
- Halaman admin hanya dapat diakses oleh pengguna dengan peran admin.
- Semua data lahan, musim tanam, kegiatan, dan panen harus dibatasi berdasarkan pemiliknya.
- Pengguna dapat keluar dan mengakhiri sesi dengan aman.

### 4.2 Panel Admin dan Akun Petani

- Menampilkan daftar petani beserta nama, email, dan status akun.
- Menambahkan akun petani secara manual dengan nama, email, dan kata sandi awal.
- Mengubah data akun petani.
- Mengaktifkan atau menonaktifkan akun petani.
- Admin tidak dapat mengubah peran akun.
- Menampilkan pesan validasi dan hasil operasi kepada admin.

Penghapusan permanen akun petani tidak digunakan dalam MVP agar riwayat data pertanian tidak hilang. Admin menggunakan status aktif atau nonaktif.

### 4.3 Ringkasan Petani

Halaman ringkasan menampilkan:

- jumlah lahan;
- jumlah musim tanam aktif;
- kegiatan yang belum selesai atau akan datang; dan
- total hasil panen berdasarkan musim tanam.

Ringkasan hanya mengambil data milik petani yang sedang masuk.

### 4.4 Pengelolaan Lahan

Petani dapat melihat, menambahkan, membuka detail, mengubah, dan menghapus lahan miliknya. Data minimum lahan terdiri dari:

- nama lahan;
- luas lahan dalam hektare;
- lokasi dalam bentuk teks; dan
- catatan opsional.

Lahan yang masih memiliki musim tanam tidak boleh dihapus langsung. Petani harus menghapus seluruh musim tanam pada lahan tersebut terlebih dahulu.

### 4.5 Pengelolaan Musim Tanam

Setiap musim tanam harus terhubung ke satu lahan. Data minimum musim tanam terdiri dari:

- komoditas;
- tanggal mulai tanam;
- perkiraan tanggal panen;
- status `aktif` atau `selesai`; dan
- catatan opsional.

Petani dapat membuat, melihat, mengubah, menghapus, menyelesaikan, dan mengaktifkan kembali musim tanam. Penghapusan musim tanam harus dikonfirmasi dan turut menghapus kegiatan serta panen yang terkait. Musim yang sudah selesai tetap dapat dilihat sebagai riwayat. Data kegiatan dan panen pada musim selesai tidak dapat ditambah atau diubah sebelum musim diaktifkan kembali.

### 4.6 Pengelolaan Kegiatan

Kegiatan terhubung ke satu musim tanam. Contoh kegiatan meliputi penanaman, penyiraman, pemupukan, penyiangan, dan pengendalian hama. Data minimum kegiatan terdiri dari:

- nama atau jenis kegiatan;
- tanggal kegiatan;
- keterangan opsional; dan
- status `direncanakan` atau `selesai`.

Petani dapat menambah, melihat, mengubah, menghapus, dan menandai kegiatan sebagai selesai.

### 4.7 Pencatatan Panen

Panen terhubung ke satu musim tanam. Satu musim dapat memiliki lebih dari satu catatan panen. Data minimum panen terdiri dari:

- tanggal panen;
- jumlah hasil panen;
- satuan hasil, misalnya kilogram atau ton; dan
- catatan opsional.

Petani dapat menambah, melihat, mengubah, dan menghapus catatan panen. Ringkasan total panen hanya menjumlahkan catatan dengan satuan yang sama; satuan berbeda ditampilkan secara terpisah.

## 5. Fitur di Luar MVP

Fitur berikut dapat dikembangkan setelah MVP selesai:

- verifikasi email;
- pemulihan kata sandi;
- login sosial;
- CAPTCHA dan perlindungan bot;
- pencatatan biaya, penjualan, dan laba sederhana;
- pengingat kegiatan melalui notifikasi;
- foto kondisi tanaman;
- unduhan laporan PDF atau Excel;
- inventaris benih, pupuk, pestisida, dan alat;
- informasi cuaca berdasarkan lokasi;
- peta dan batas lahan;
- kolaborasi kelompok tani;
- mode offline; dan
- analisis perbandingan antar musim tanam.

## 6. Arsitektur Aplikasi

### 6.1 Pola Arsitektur

Aplikasi menggunakan arsitektur monolitik berbasis Model-View-Controller (MVC). Antarmuka, aturan bisnis, dan akses basis data berada dalam satu aplikasi Laravel sehingga pengembangan, pengujian, dan deployment tetap sederhana untuk proyek UAS.

```text
Browser
   |
   | HTTP request / response
   v
Laravel Application
   |-- Routes dan Middleware
   |-- Controllers dan Form Validation
   |-- Services atau aturan bisnis bila diperlukan
   |-- Blade Views + Bootstrap
   |-- Eloquent Models
   v
MySQL Database
```

### 6.2 Komponen Utama

- **Routes:** menentukan URL dan mengarahkan permintaan ke controller.
- **Middleware:** memeriksa autentikasi, status akun, peran pengguna, dan rate limit.
- **Controllers:** menangani alur permintaan dan menghubungkan view dengan data.
- **Form Requests/validation:** memvalidasi semua data dari formulir di sisi server.
- **Eloquent Models:** merepresentasikan entitas dan relasi basis data.
- **Blade Views:** menampilkan halaman yang dirender oleh server.
- **Bootstrap:** menyediakan tata letak dan komponen yang responsif.
- **MySQL:** menyimpan akun serta seluruh data pertanian.

### 6.3 Model Data Konseptual

```text
User (petani) 1 --- * Lahan
Lahan         1 --- * MusimTanam
MusimTanam    1 --- * Kegiatan
MusimTanam    1 --- * Panen
```

Entitas utama:

| Entitas | Atribut penting |
| --- | --- |
| User | nama, email, password, peran, status aktif |
| Lahan | user_id, nama, luas, lokasi, catatan |
| Musim Tanam | lahan_id, komoditas, tanggal mulai, perkiraan panen, status, catatan |
| Kegiatan | musim_tanam_id, nama, tanggal, status, keterangan |
| Panen | musim_tanam_id, tanggal, jumlah, satuan, catatan |

Semua pemeriksaan kepemilikan data dilakukan di server menggunakan policy atau query yang dibatasi berdasarkan pengguna yang sedang masuk. Penyembunyian tombol pada antarmuka hanya menjadi pelengkap, bukan pengaman utama.

## 7. Alur Pengguna

### 7.1 Alur Registrasi Petani

1. Pengguna membuka halaman login.
2. Pengguna memilih **Daftar Sekarang**.
3. Pengguna mengisi nama, email, kata sandi, dan konfirmasi kata sandi.
4. Sistem memvalidasi data dan membuat akun petani aktif.
5. Sistem mengarahkan pengguna kembali ke halaman login, mengisi email melalui flash session, dan menampilkan pesan sukses.
6. Pengguna login dan diarahkan ke dashboard petani.

### 7.2 Alur Login dan Pengalihan Peran

1. Pengguna membuka halaman login.
2. Pengguna memasukkan email dan kata sandi.
3. Sistem memvalidasi kredensial dan status akun.
4. Jika data salah, sistem menampilkan pesan kesalahan.
5. Jika akun nonaktif, sistem menolak login dan menampilkan informasi status akun.
6. Jika pengguna adalah admin, sistem membuka panel admin.
7. Jika pengguna adalah petani, sistem membuka halaman ringkasan petani.

### 7.3 Alur Admin Mengelola Petani

1. Admin masuk ke panel admin.
2. Admin membuka menu Akun Petani.
3. Admin melihat daftar petani dan status akunnya.
4. Admin dapat membuat atau mengubah akun petani.
5. Admin dapat menonaktifkan akun tanpa menghapus riwayat datanya.
6. Sistem memvalidasi data dan menampilkan hasil operasi.

### 7.4 Alur Petani dari Lahan sampai Panen

1. Petani masuk dan melihat ringkasan.
2. Petani membuat atau memilih lahan.
3. Petani membuat musim tanam pada lahan tersebut.
4. Petani mencatat rencana kegiatan selama musim tanam.
5. Petani memperbarui kegiatan menjadi selesai setelah dikerjakan.
6. Saat panen dilakukan, petani mencatat tanggal, jumlah, dan satuannya.
7. Petani dapat menambahkan catatan panen berikutnya pada musim yang sama.
8. Setelah proses tanam berakhir, petani mengubah status musim menjadi selesai.
9. Data musim, kegiatan, dan panen tetap tersedia sebagai riwayat.

### 7.5 Alur Akses yang Ditolak

1. Petani mencoba membuka URL panel admin atau data milik petani lain.
2. Middleware atau policy memeriksa autentikasi, status akun, peran, dan kepemilikan data.
3. Sistem menolak permintaan dengan halaman `403 Forbidden` atau mengarahkan pengguna ke halaman yang sesuai.

## 8. Rekomendasi Teknologi

### 8.1 Tech Stack

| Bagian | Teknologi | Alasan |
| --- | --- | --- |
| Backend | Laravel (PHP) | Menyediakan routing, MVC, validasi, autentikasi, middleware, ORM, migration, seeder, dan pengujian dalam satu framework. Cocok untuk CRUD dan pembagian peran pada proyek UAS. |
| Frontend | Blade | Halaman dapat dirender langsung dari Laravel sehingga tidak memerlukan frontend dan REST API terpisah. |
| UI/CSS | Bootstrap | Komponen formulir, tabel, navigasi, dan tata letak responsif dapat dibuat dengan cepat. |
| Database | MySQL | Cocok untuk data relasional dan umum tersedia pada lingkungan lokal maupun hosting. |
| ORM | Eloquent | Mempermudah relasi dan pembatasan query berdasarkan pemilik data. |
| Autentikasi | Autentikasi berbasis sesi Laravel | Mendukung registrasi, login, dan pembatasan akses dalam aplikasi monolitik. |
| Pengujian | PHPUnit melalui fasilitas pengujian Laravel | Mendukung pengujian login, otorisasi, validasi, dan alur CRUD. |
| Version control | Git | Mencatat perubahan dan mendukung kolaborasi tim. |

Versi teknologi mengikuti versi stabil yang tersedia saat proyek mulai diimplementasikan. Versi PHP dan MySQL harus memenuhi kebutuhan versi Laravel yang dipilih.

### 8.2 Alasan Tidak Memisahkan Frontend dan Backend pada MVP

React, TypeScript, Tailwind, Node.js, dan Express dapat digunakan untuk aplikasi ini, tetapi akan menghasilkan dua aplikasi yang berkomunikasi melalui API. Tim juga perlu menangani kontrak API, autentikasi lintas aplikasi, CORS, state frontend, loading dan error API, serta deployment terpisah.

Laravel, Blade, dan Bootstrap dipilih karena seluruh kebutuhan MVP dapat diselesaikan dalam satu aplikasi. Pilihan ini mengurangi konfigurasi dan memberi lebih banyak waktu untuk menyelesaikan alur bisnis, keamanan akses, serta kualitas demo UAS.

## 9. Kebutuhan Nonfungsional

- Antarmuka harus responsif dan dapat digunakan pada layar ponsel maupun desktop.
- Semua formulir harus memiliki validasi sisi server dan pesan kesalahan yang mudah dipahami.
- Kata sandi harus memiliki minimal 8 karakter dan disimpan dalam bentuk hash.
- Email pengguna harus berformat valid, dinormalisasi menjadi huruf kecil, dan dihapus spasinya sebelum pemeriksaan keunikan.
- Login harus dibatasi maksimal 5 percobaan per menit.
- Registrasi harus dibatasi maksimal 3 percobaan per jam untuk setiap alamat IP.
- Semua operasi perubahan data harus dilindungi dari CSRF.
- Pengguna yang belum login tidak dapat membuka halaman internal.
- Waktu muat halaman pada data demo harus cukup cepat untuk presentasi kelas.
- Navigasi dan istilah pada antarmuka menggunakan Bahasa Indonesia yang konsisten.
- Sistem harus menampilkan halaman kosong yang informatif saat pengguna belum memiliki data.

## 10. Kriteria Penerimaan MVP

MVP dianggap selesai apabila:

- admin dapat login dan diarahkan ke panel admin;
- petani dapat membuat akun sendiri dan kembali ke halaman login setelah registrasi;
- pengguna yang sudah login dan membuka halaman login atau registrasi dialihkan ke halaman sesuai perannya;
- admin dapat membuat, mengubah, mengaktifkan, dan menonaktifkan akun petani;
- akun petani aktif dapat login, sedangkan akun nonaktif ditolak;
- petani dapat mengelola lahan dan musim tanam miliknya;
- petani dapat mencatat serta menyelesaikan kegiatan;
- petani dapat mencatat panen beberapa kali dalam satu musim;
- musim selesai tetap dapat dilihat sebagai riwayat;
- petani tidak dapat mengakses panel admin atau data petani lain;
- halaman ringkasan menampilkan data milik petani yang sedang login; dan
- tampilan utama dapat digunakan pada perangkat seluler.

## 11. Skenario Pengujian Utama

1. Registrasi valid membuat akun petani aktif, mengisi email melalui flash session, menampilkan pesan sukses, dan mengarahkan pengguna ke login.
2. Registrasi menolak email duplikat, kata sandi kurang dari 8 karakter, dan konfirmasi yang berbeda.
3. Manipulasi formulir registrasi tidak dapat menghasilkan akun admin.
4. Login berhasil mengarahkan admin ke `/admin` dan petani ke `/dashboard`.
5. Login gagal untuk kata sandi salah dan akun nonaktif.
6. Petani yang membuka `/admin` menerima respons `403 Forbidden`.
7. Pengguna yang sudah login dan membuka halaman login atau registrasi dialihkan ke halaman sesuai perannya.
8. Rate limit menolak percobaan login setelah 5 percobaan per menit dan registrasi setelah 3 percobaan per jam dari IP yang sama.
9. Email dengan perbedaan huruf besar, huruf kecil, atau spasi dianggap sebagai email yang sama dan ditolak sebagai duplikat.
10. Petani A tidak dapat melihat, mengubah, atau menghapus data Petani B.
11. Admin berhasil membuat akun petani dengan email unik, tetapi tidak dapat mengubah peran akun.
12. Validasi menolak data lahan, musim tanam, kegiatan, atau panen yang tidak lengkap atau tidak valid.
13. Satu lahan dapat memiliki beberapa riwayat musim tanam.
14. Satu musim tanam dapat memiliki beberapa kegiatan dan catatan panen.
15. Total panen dijumlahkan dengan benar dan dipisahkan berdasarkan satuan.
16. Penghapusan musim tanam meminta konfirmasi, menghapus kegiatan dan panen terkait, serta memungkinkan lahan tanpa musim tanam untuk dihapus.
17. Musim tanam yang selesai tetap tampil dan tidak menerima perubahan kegiatan atau panen sampai diaktifkan kembali.

## 12. Asumsi dan Batasan

- Aplikasi dibuat untuk kebutuhan UAS dan demonstrasi dengan jumlah pengguna terbatas.
- Petani dapat mendaftar sendiri, tetapi peran admin hanya dapat dibuat melalui seeder.
- Akun petani langsung aktif setelah registrasi tanpa verifikasi email.
- Setelah registrasi, petani kembali ke halaman login dan harus login secara manual.
- Verifikasi email dan perlindungan bot ditunda karena berada di luar cakupan MVP.
- Lokasi lahan disimpan sebagai teks; integrasi peta berada di luar MVP.
- Aplikasi membutuhkan koneksi internet atau jaringan lokal; mode offline berada di luar MVP.
- Admin hanya mengelola akun petani dan tidak mengubah data pertanian mereka dalam MVP.
- Keuangan, penjualan, inventaris, cuaca, dan notifikasi berada di luar MVP.
- Data contoh untuk demonstrasi disediakan melalui seeder agar alur aplikasi mudah dipresentasikan.
