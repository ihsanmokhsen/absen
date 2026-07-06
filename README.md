# Absensi Apel Pagi BPAD Provinsi NTT

Aplikasi web sederhana untuk input absensi apel pagi harian pegawai BPAD Provinsi NTT. Dibuat ringan agar mudah dipindahkan ke VPS.

## Stack

- Laravel 12
- PHP 8.3+
- MySQL
- Blade
- Bootstrap 5 CDN
- Tanpa React, Vue, Tailwind, dan tanpa npm build
- Timezone `Asia/Makassar`
- Locale `id`

## Fitur

- Login admin dan login per bidang
- CRUD pegawai dan nonaktifkan pegawai
- Filter pegawai berdasarkan bidang
- Input absensi per tanggal dan per bidang
- Status absensi: Hadir, Izin, Sakit, Tugas, Tubel, Cuti, Terlambat
- Validasi submit satu kali per bidang per tanggal dengan opsi edit oleh admin
- Status Submit 5 Bidang
- Dashboard ringkas
- Rekap Harian format print-friendly ukuran A4
- Tombol Export PDF / Print menggunakan `window.print()`

## Bidang

- SEKRETARIAT
- PENDAPATAN 1
- PENDAPATAN 2
- ASET 1
- ASET 2

## Instalasi Lokal atau VPS

1. Install dependency PHP:

```bash
composer install
```

2. Salin konfigurasi environment:

```bash
cp .env.example .env
php artisan key:generate
```

3. Atur database MySQL pada `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=absenpagi_bpadntt
DB_USERNAME=root
DB_PASSWORD=
```

4. Jalankan migrasi dan seeder:

```bash
php artisan migrate --seed
```

5. Jalankan server:

```bash
php artisan serve
```

## Akun Login Default

```text
BPAD/Admin
Akun: bpad
Password: bpad1

SEKRETARIAT
Akun: sekretariat
Password: sekretariat1

PENDAPATAN 1
Akun: pendapatan1
Password: pendapatan1

PENDAPATAN 2
Akun: pendapatan2
Password: pendapatan2

ASET 1
Akun: aset1
Password: aset1

ASET 2
Akun: aset2
Password: aset2
```

Akun bidang hanya dapat mengisi absensi bidangnya masing-masing. Setelah bidang submit untuk satu tanggal, koreksi hanya dapat dilakukan oleh akun `bpad`. Akun `bpad` dapat mengakses semua bidang, rekap, dan data pegawai. Segera ubah password setelah aplikasi dipasang di server produksi.

## Catatan Deploy VPS

- Arahkan document root web server ke folder `public`.
- Pastikan permission folder `storage` dan `bootstrap/cache` dapat ditulis oleh user web server.
- Gunakan `APP_ENV=production` dan `APP_DEBUG=false` di VPS.
- Jalankan `php artisan config:cache` setelah konfigurasi `.env` selesai.
