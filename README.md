# LapMas - Laporan Masyarakat

LapMas adalah aplikasi web untuk pengelolaan laporan masyarakat berdasarkan wilayah administrasi. Proyek ini dibuat sebagai proyek PKL yang saya kerjakan bersama teman saya.

## Tentang Proyek

Aplikasi ini membantu masyarakat membuat laporan, mengecek status laporan, dan membantu admin wilayah mengelola laporan sesuai kewenangan wilayahnya.

## Fitur Utama

- Form pengaduan/laporan masyarakat
- Nomor tiket laporan otomatis
- Cek status laporan
- Detail laporan publik
- Export laporan ke PDF
- Login admin
- Dashboard admin
- Pengelolaan status laporan
- Pembatasan akses admin berdasarkan wilayah
- Manajemen akun admin wilayah
- Export data akun admin ke CSV
- Upload logo wilayah satuan atau massal menggunakan ZIP

## Teknologi

- Laravel 13
- PHP 8.3
- MySQL
- Inertia.js
- React
- Vite
- Tailwind CSS
- Laravel Breeze
- Laravel Sanctum

## Instalasi

Clone repository:

```bash
git clone <url-repository>
cd lapmas-laravel
```

Install dependency PHP:

```bash
composer install
```

Install dependency JavaScript:

```bash
npm install
```

Salin file environment:

```bash
cp .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

Atur database di file `.env`, lalu jalankan migrasi:

```bash
php artisan migrate
```

Jalankan seeder jika diperlukan:

```bash
php artisan db:seed
```

## Menjalankan Aplikasi

Jalankan server Laravel:

```bash
php artisan serve
```

Jalankan Vite:

```bash
npm run dev
```

Akses aplikasi:

```text
http://127.0.0.1:8000
```

## Login Admin Demo

Halaman login admin:

```text
http://127.0.0.1:8000/admin/login
```

Akun default dari seeder:

```text
Username: superadmin
Password: admin123
```

## Upload Logo Wilayah Massal

Login sebagai super admin, lalu buka menu:

```text
Admin > Logo Wilayah > Import ZIP
```

Aturan file ZIP:

- Maksimal 10 MB
- Format gambar: `.png`, `.jpg`, `.jpeg`, atau `.webp`
- Nama file harus memakai kode wilayah dari tabel `regions`
- Contoh:
  - `12.png` untuk Provinsi Sumatera Utara
  - `12.07.png` untuk Kabupaten Deli Serdang
  - `12.07.26.png` untuk Kecamatan Percut Sei Tuan

Struktur folder di dalam ZIP boleh bebas, misalnya:

```text
provinces/12.png
regencies/12.07.png
districts/12.07.26.png
```

Yang paling penting adalah nama file logo sesuai kode wilayah.

## Struktur Singkat

```text
app/Models              Model aplikasi
app/Http/Controllers    Controller aplikasi
app/Http/Middleware     Middleware akses admin
resources/js/Pages      Halaman React/Inertia
routes/web.php          Route utama aplikasi
database/seeders        Seeder data awal
public/assets/logos     Logo wilayah
```

## Catatan

Project ini dibuat untuk kebutuhan pembelajaran dan pelaksanaan PKL. Beberapa konfigurasi seperti database, APP_KEY, dan kredensial lokal tidak disertakan di repository demi keamanan.
