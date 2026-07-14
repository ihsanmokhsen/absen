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
- Status absensi: Hadir, Izin, Sakit, Tugas, Tubel, Cuti, Tanpa Berita
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

## Instalasi Lokal

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

6. Buka aplikasi lokal:

```text
http://127.0.0.1:8000
```

## Akun Login Default

```

Akun bidang hanya dapat mengisi absensi bidangnya masing-masing. Setelah bidang submit untuk satu tanggal, koreksi hanya dapat dilakukan oleh akun `bpad`. Akun `bpad` dapat mengakses semua bidang, rekap, dan data pegawai. Segera ubah password setelah aplikasi dipasang di server produksi.

## Panduan Hosting ke VPS aaPanel/Nginx

Panduan ini memakai contoh domain:

```text
https://absen.bpadntt.cloud
```

Sesuaikan nama domain, database, username, dan password dengan VPS masing-masing.

### 1. Arahkan DNS

Buat record DNS:

```text
Type : A
Name : absen
Value: IP_VPS
```

Tunggu propagasi DNS. Cek dari komputer lokal:

```bash
ping absen.bpadntt.cloud
```

### 2. Buat Website di aaPanel

Di aaPanel:

```text
Website -> Add site
Domain: absen.bpadntt.cloud
PHP   : PHP 8.3 atau 8.4
Root  : /www/wwwroot/absen/public
```

Penting: document root Laravel harus mengarah ke folder `public`, bukan folder project utama.

```text
Benar : /www/wwwroot/absen/public
Salah : /www/wwwroot/absen
```

### 3. Buat Database MySQL

Di menu database aaPanel, buat database:

```text
DB Name   : absenpagi_bpadntt
Username  : absenpagi_bpadntt
Password  : gunakan password kuat dari panel
Charset   : utf8mb4
Permission: Local server
```

Simpan password database karena akan dipakai di file `.env`.

### 4. Clone Project ke VPS

Masuk SSH:

```bash
ssh root@IP_VPS
```

Clone repo:

```bash
cd /www/wwwroot
git clone https://github.com/ihsanmokhsen/absen.git absen
cd /www/wwwroot/absen
```

Install dependency production:

```bash
composer install --no-dev --optimize-autoloader
```

### 5. Buat dan Atur File `.env`

```bash
cp -n .env.example .env
php artisan key:generate --force
nano .env
```

Isi bagian penting:

```env
APP_NAME="Absensi Apel BPAD NTT"
APP_ENV=production
APP_KEY=base64:ISI_HASIL_KEY_GENERATE
APP_DEBUG=false
APP_URL=https://absen.bpadntt.cloud

APP_TIMEZONE=Asia/Makassar
APP_LOCALE=id
APP_FALLBACK_LOCALE=id
APP_FAKER_LOCALE=id_ID

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=absenpagi_bpadntt
DB_USERNAME=absenpagi_bpadntt
DB_PASSWORD=password_database_panel
```

### 6. Konfigurasi Nginx untuk Laravel

Edit file vhost:

```bash
nano /www/server/panel/vhost/nginx/absen.bpadntt.cloud.conf
```

Pastikan ada konfigurasi seperti ini:

```nginx
server
{
    listen 80;
    listen 443 ssl;
    listen 443 quic;
    http2 on;
    http3 on;
    server_name absen.bpadntt.cloud;
    root /www/wwwroot/absen/public;
    index index.php index.html;

    include /www/server/panel/vhost/nginx/well-known/absen.bpadntt.cloud.conf;

    ssl_certificate    /www/server/panel/vhost/cert/absen.bpadntt.cloud/fullchain.pem;
    ssl_certificate_key    /www/server/panel/vhost/cert/absen.bpadntt.cloud/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers EECDH+CHACHA20:EECDH+AES128:EECDH+AES256:!MD5;
    ssl_prefer_server_ciphers on;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;
    add_header Strict-Transport-Security "max-age=31536000";
    add_header Alt-Svc 'h3=":443"; h3-29=":443"';
    error_page 497 https://$host$request_uri;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_pass unix:/tmp/php-cgi-84.sock;
        fastcgi_index index.php;
        include fastcgi.conf;
    }

    location ~ ^/(\.user.ini|\.htaccess|\.git|\.env|\.svn|\.project|LICENSE|README.md) {
        return 404;
    }

    location ~ \.well-known {
        allow all;
    }

    location ~* \.(gif|jpg|jpeg|png|bmp|swf)$ {
        expires 30d;
        access_log off;
        log_not_found off;
    }

    location ~* \.(js|css)$ {
        expires 12h;
        access_log off;
        log_not_found off;
    }

    access_log /www/wwwlogs/absen.bpadntt.cloud.log;
    error_log /www/wwwlogs/absen.bpadntt.cloud.error.log;
}
```

Jika memakai PHP 8.3, ubah socket PHP:

```nginx
fastcgi_pass unix:/tmp/php-cgi-83.sock;
```

Jika memakai PHP 8.4:

```nginx
fastcgi_pass unix:/tmp/php-cgi-84.sock;
```

Cek dan reload nginx:

```bash
nginx -t
systemctl reload nginx
```

### 7. Atur `open_basedir` aaPanel

Laravel harus dapat membaca folder project utama, bukan hanya folder `public`.

Jika muncul error:

```text
open_basedir restriction in effect
vendor/autoload.php is not within the allowed path
```

Atur `.user.ini`:

```bash
chattr -i /www/wwwroot/absen/public/.user.ini 2>/dev/null || true
rm -f /www/wwwroot/absen/public/.user.ini

cat > /www/wwwroot/absen/public/.user.ini <<'EOF'
open_basedir=/www/wwwroot/absen/:/tmp/
EOF

chown www:www /www/wwwroot/absen/public/.user.ini
chmod 644 /www/wwwroot/absen/public/.user.ini
/etc/init.d/php-fpm-84 restart
systemctl reload nginx
```

Jika file `.user.ini` tidak bisa diubah dari terminal, ubah lewat aaPanel:

```text
Website -> absen.bpadntt.cloud -> Settings -> PHP Version / Config
```

Matikan `open_basedir` atau ubah allowed path menjadi:

```text
/www/wwwroot/absen/:/tmp/
```

### 8. Migrasi, Seeder, Cache, dan Permission

```bash
cd /www/wwwroot/absen
php artisan migrate --seed --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache

chmod -R 775 storage bootstrap/cache
chown -R www:www storage bootstrap/cache
```

### 9. Aktifkan Pembersihan Absensi Tidak Lengkap

Aplikasi menghapus seluruh data hari sebelumnya apabila submit belum lengkap 5 bidang. Tambahkan cron berikut melalui menu Cron aaPanel atau `crontab -e`:

```cron
* * * * * cd /www/wwwroot/absen && php artisan schedule:run >> /dev/null 2>&1
```

Pembersihan dijalankan otomatis setiap hari pukul `00:05` WITA. Untuk menjalankannya manual:

```bash
cd /www/wwwroot/absen
php artisan attendance:purge-incomplete
```

### 10. Cek Aplikasi

Cek dari server:

```bash
curl -I -H "Host: absen.bpadntt.cloud" http://127.0.0.1/
curl -I https://absen.bpadntt.cloud/
```

Respons yang baik biasanya `200 OK` atau redirect ke halaman login.

Buka di browser:

```text
https://absen.bpadntt.cloud
```

Login dengan akun default:

```text
Akun: bpad
Password: bpad1
```

Segera ubah password setelah aplikasi dipakai produksi.

## Update Aplikasi di VPS

Setelah ada perubahan baru di GitHub:

```bash
cd /www/wwwroot/absen
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
chown -R www:www storage bootstrap/cache
```

## Troubleshooting VPS

### 404 Not Found nginx

Artinya request belum sampai ke Laravel. Cek:

```bash
ls -la /www/wwwroot/absen/public/index.php
nginx -T | grep -n "server_name absen.bpadntt.cloud\|root /www/wwwroot/absen/public\|try_files" -A 8 -B 8
```

Pastikan:

- `root` mengarah ke `/www/wwwroot/absen/public`
- ada blok `location / { try_files $uri $uri/ /index.php?$query_string; }`
- `nginx -t` sukses
- `systemctl reload nginx` sudah dijalankan

### Duplicate location `/`

Artinya ada lebih dari satu blok `location /` dalam vhost yang sama. Hapus duplikatnya, sisakan satu:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### Could not open input file: artisan

Artinya command dijalankan bukan dari folder project. Masuk dulu:

```bash
cd /www/wwwroot/absen
php artisan optimize:clear
```

### Composer could not find composer.json

Artinya project belum di-clone atau posisi terminal salah:

```bash
cd /www/wwwroot
git clone https://github.com/ihsanmokhsen/absen.git absen
cd /www/wwwroot/absen
composer install --no-dev --optimize-autoloader
```

### Laravel error 500

Cek log:

```bash
tail -n 100 /www/wwwroot/absen/storage/logs/laravel.log
tail -n 100 /www/wwwlogs/absen.bpadntt.cloud.error.log
```

### Database error

Cek `.env`:

```bash
cd /www/wwwroot/absen
grep "DB_" .env
php artisan migrate:status
```

Pastikan database, username, dan password sama dengan yang dibuat di aaPanel.

## Catatan Deploy VPS

- Arahkan document root web server ke folder `public`.
- Pastikan permission folder `storage` dan `bootstrap/cache` dapat ditulis oleh user web server.
- Gunakan `APP_ENV=production` dan `APP_DEBUG=false` di VPS.
- Jalankan `php artisan config:cache` setelah konfigurasi `.env` selesai.
