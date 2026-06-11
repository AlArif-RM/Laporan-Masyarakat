# Checklist Deploy

Gunakan daftar ini sebelum aplikasi dipasang ke server publik.

## 1. Environment

- pakai `.env.production.example` sebagai acuan, bukan `.env` lokal
- set `APP_ENV=production`
- set `APP_DEBUG=false`
- isi `APP_KEY` baru dengan `php artisan key:generate --show`
- ganti `APP_URL` ke domain produksi final

## 2. Database

- jangan gunakan user `root`
- buat user database khusus aplikasi
- gunakan password database yang kuat
- pastikan `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` sesuai server produksi

## 3. Session dan Cookie

- set `SESSION_SECURE_COOKIE=true` jika situs sudah HTTPS
- set `SESSION_DOMAIN` ke domain produksi
- biarkan `SESSION_HTTP_ONLY=true`
- periksa `SESSION_SAME_SITE=lax` atau sesuaikan kebutuhan integrasi Anda

## 4. HTTPS

- aktifkan SSL/TLS di web server
- pastikan admin login hanya diakses melalui HTTPS
- setelah HTTPS aktif, cek ulang cookie session dan upload logo

## 5. Kredensial Admin

- ganti semua password default admin
- pastikan CSV akun yang diunduh disimpan di tempat aman
- batasi siapa yang boleh memakai akun `superadmin`

## 6. Storage dan Upload

- pastikan folder `storage/` dan `bootstrap/cache/` writable
- jika memakai upload logo wilayah, pastikan folder `public/assets/logos/` dapat ditulis oleh aplikasi saat diperlukan
- hapus file ZIP import setelah selesai jika Anda menyimpannya manual di server

## 7. Route Admin Sensitif

Route berikut sekarang khusus `superadmin`:

- `/admin/akun`
- `/admin/akun/export/csv`
- `/admin/logo-wilayah`
- `/admin/logo-wilayah/import-zip`

Login admin juga sudah diberi throttle tambahan di route level.

## 8. Build dan Cache

Jalankan urutan berikut saat deploy:

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 9. Hal Yang Tidak Boleh Dibawa dari Lokal

- contoh kredensial uji
- `.env` lokal
- akun database lokal `root`
- domain lokal seperti `lapmas-laravel.test`

## 10. Pemeriksaan Akhir

- login admin berhasil
- buat laporan publik berhasil
- cek status laporan berhasil
- export PDF/XLS berhasil
- upload logo wilayah berhasil
- import ZIP logo berhasil
