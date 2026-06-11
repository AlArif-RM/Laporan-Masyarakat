# Admin Wilayah Nasional

Dokumen ini menjelaskan format akun admin wilayah nasional yang dibangkitkan dari tabel `regions`.

## Ringkasan

- URL aplikasi: `http://lapmas-laravel.test`
- URL login admin: `http://lapmas-laravel.test/admin/login`
- Password sementara semua akun: `admin123`
- Akun tertinggi: `superadmin`

## Cakupan akun

Akun dibangkitkan otomatis dari master wilayah yang sedang dipakai aplikasi.

- `38` admin provinsi
- `514` admin kabupaten/kota
- `7285` admin kecamatan
- `1` superadmin

Total akun hasil master wilayah aktif: `7838`

Catatan:

- Angka ini mengikuti data master wilayah nasional yang saat ini ada di tabel `regions` dan file SQL fresh.
- Jika nanti master wilayah diganti, jumlah akun otomatis ikut berubah.

## Format username

- Provinsi: `provinsi_<nama_wilayah>`
- Kabupaten: `kabupaten_<nama_wilayah>`
- Kota: `kota_<nama_wilayah>`
- Kecamatan: `kecamatan_<nama_wilayah>`

Contoh:

- `provinsi_sumatera_utara`
- `kabupaten_deli_serdang`
- `kota_medan`
- `kecamatan_percut_sei_tuan`

Jika ada nama wilayah yang bentrok secara nasional, sistem menambahkan suffix kode wilayah agar tetap unik.

## Aturan logo wilayah

- Logo provinsi: `public/assets/logos/provinces/<kode>.png`
- Logo kabupaten/kota: `public/assets/logos/regencies/<kode>.png`
- Logo kecamatan: `public/assets/logos/districts/<kode>.png`
- Halaman upload logo: `http://lapmas-laravel.test/admin/logo-wilayah`
- Halaman ini juga mendukung import massal lewat file ZIP.

Ekstensi yang didukung:

- `.png`
- `.jpg`
- `.jpeg`
- `.webp`

Contoh:

- `public/assets/logos/provinces/12.png`
- `public/assets/logos/regencies/12.07.png`
- `public/assets/logos/districts/12.07.26.png`

Jika file logo wilayah tidak ada, PDF akan tampil tanpa logo.

## Format ZIP Import Logo

Anda bisa upload satu file ZIP berisi banyak logo wilayah sekaligus.

Struktur yang didukung:

- `provinces/12.png`
- `regencies/12.07.png`
- `districts/12.07.26.png`

Atau langsung di root ZIP:

- `12.png`
- `12.07.png`
- `12.07.26.png`

Aturan:

- nama file harus memakai kode wilayah
- ekstensi yang didukung: `.png`, `.jpg`, `.jpeg`, `.webp`
- file dengan kode wilayah yang tidak dikenali akan dilewati
- jika sebuah wilayah sudah punya logo lama, file lama akan diganti

## SQL dan Seeder

- SQL fresh import: `database/sql/lapmas_fresh_mysql.sql`
- Seeder akun wilayah nasional: `database/seeders/DistrictAdminSeeder.php`
- Generator SQL fresh: `scripts/generate_fresh_mysql_sql.php`
