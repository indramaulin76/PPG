# 🚀 Panduan Lengkap Deploy SI-JEMAAH ke Debian 13 (Tanpa Docker & Menggunakan Cloudflare)

Panduan ini berisi langkah-langkah dari nol untuk meng-deploy aplikasi SI-JEMAAH (Laravel 12 + Vue 3 + Inertia) ke Home Server menggunakan sistem operasi **Debian 13**. Konfigurasi ini dirancang agar berjalan mulus di balik **Cloudflare** dengan domain `ppgtangbar.id`.

---

## 🏗️ 1. Persiapan Server Debian 13

Sebelum memulai, pastikan server Anda sudah terhubung ke internet dan di-update.

```bash
apt update && apt upgrade -y
```

### Install Dependensi Inti (Nginx, PHP 8.4, MariaDB, Composer, Node.js)

Karena Laravel 12 membutuhkan PHP 8.2+ (disarankan 8.4), kita perlu menambahkan repository Sury:

```bash
# Install tool dasar
apt install -y curl wget gnupg2 ca-certificates lsb-release apt-transport-https unzip

# Tambahkan repository PHP Sury
wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee /etc/apt/sources.list.d/php.list
apt update

# Install Nginx, MariaDB, dan PHP 8.4 beserta ekstensinya
apt install -y nginx mariadb-server \
php8.4 php8.4-fpm php8.4-mysql php8.4-mbstring php8.4-xml php8.4-bcmath \
php8.4-curl php8.4-zip php8.4-gd php8.4-intl php8.4-cli

# Install Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# Install Node.js (versi 20 LTS) & NPM
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs
```

---

## 🗄️ 2. Konfigurasi Database (MariaDB)

Mari kita buat database dan user untuk SI-JEMAAH.

```bash
# Masuk ke console MariaDB
mysql -u root

# Jalankan perintah SQL berikut di dalam console MariaDB:
CREATE DATABASE sijemaah_db;
CREATE USER 'sijemaah_user'@'localhost' IDENTIFIED BY 'password_kuat_anda';
GRANT ALL PRIVILEGES ON sijemaah_db.* TO 'sijemaah_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

## 📦 3. Upload & Setup Aplikasi Laravel

Misalkan kita meletakkan aplikasi di `/var/www/ppgtangbar.id`.

### Pindahkan File Aplikasi
1. Upload folder project SI-JEMAAH (yang sudah di-build dan dibersihkan dari Docker) ke server Anda. Anda bisa menggunakan `scp`, `rsync`, atau `git clone`.
2. Pindahkan isinya agar berada tepat di `/var/www/ppgtangbar.id`.

```bash
# Pastikan folder ada
mkdir -p /var/www/ppgtangbar.id

# Pindahkan file (Asumsi Anda upload ke home direktori dulu)
# cp -r ~/upload_sijemaah/* /var/www/ppgtangbar.id/
```

### Install Dependensi & Build Frontend
Masuk ke folder aplikasi dan jalankan perintah instalasi:

```bash
cd /var/www/ppgtangbar.id

# Install dependensi PHP (tanpa paket development)
composer install --optimize-autoloader --no-dev

# Install dependensi NPM (Jika Anda upload folder tanpa public/build)
npm install
npm run build
```

### Set Environment Variables (.env)
Ganti file `.env.example` menjadi `.env` dan sesuaikan konfigurasinya.

```bash
cp .env.example .env
nano .env
```

Ubah pengaturan berikut di dalam file `.env`:

```ini
APP_NAME="SI-JEMAAH"
APP_ENV=production
APP_KEY= # (Nanti di-generate di langkah selanjutnya)
APP_DEBUG=false
APP_URL=https://ppgtangbar.id

# Konfigurasi Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sijemaah_db
DB_USERNAME=sijemaah_user
DB_PASSWORD=password_kuat_anda

# Konfigurasi Cache (Penting untuk Performa)
CACHE_STORE=file
SESSION_DRIVER=database
QUEUE_CONNECTION=database
```

### Generate Key, Migrate & Cache
Setelah `.env` disimpan, jalankan optimasi Laravel:

```bash
# Generate APP_KEY
php artisan key:generate

# Migrasi Database (Jika Anda belum membawa database lama)
php artisan migrate --force

# Seed Data Awal (Superadmin & Data Master Wilayah)
php artisan db:seed --force

# Optimasi Cache untuk Production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Atur Permission File
Langkah **krusial** agar Nginx bisa membaca dan menulis file cache/log Laravel.

```bash
# Berikan kepemilikan kepada user Nginx (www-data)
chown -R www-data:www-data /var/www/ppgtangbar.id

# Berikan hak akses penuh (write) ke folder storage dan cache
chmod -R 775 /var/www/ppgtangbar.id/storage
chmod -R 775 /var/www/ppgtangbar.id/bootstrap/cache
```

---

## 🌐 4. Konfigurasi Nginx & Cloudflare

Karena kita akan meletakkan server ini di balik Cloudflare, SSL (HTTPS) akan ditangani oleh Cloudflare (Flexible / Full Mode). Nginx di server Anda cukup berjalan di Port 80 (HTTP), tetapi akan menerima trafik dari Cloudflare yang sudah dienkripsi.

### Buat File Konfigurasi Virtual Host

```bash
nano /etc/nginx/sites-available/ppgtangbar.id
```

Isikan dengan konfigurasi berikut:

```nginx
server {
    listen 80;
    server_name ppgtangbar.id www.ppgtangbar.id;
    
    # Arahkan root Nginx langsung ke folder public Laravel
    root /var/www/ppgtangbar.id/public;

    # Headers Keamanan Standar
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    # Routing utama Laravel
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Sembunyikan file sistem & log error statis
    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }
    error_page 404 /index.php;

    # Eksekusi PHP-FPM
    location ~ \.php$ {
        # Pastikan versi PHP sesuai dengan yang terinstall (contoh: 8.4)
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # Blokir akses ke file .env, .git, dll
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Aktifkan Konfigurasi & Restart Nginx

```bash
# Buat symlink untuk mengaktifkan
ln -s /etc/nginx/sites-available/ppgtangbar.id /etc/nginx/sites-enabled/

# Cek apakah ada error pengetikan di Nginx
nginx -t

# Restart layanan jika statusnya OK
systemctl restart nginx
systemctl restart php8.4-fpm
```

---

## ☁️ 5. Pengaturan Cloudflare (Domain `ppgtangbar.id`)

1. Login ke Dashboard Cloudflare.
2. Masuk ke menu **DNS -> Records**.
   - Tambahkan *A Record*:
     - **Type:** `A`
     - **Name:** `@` (atau `ppgtangbar.id`)
     - **IPv4 Address:** `[IP Publik Server Debian Anda]`
     - **Proxy status:** 🟠 Proxied (Awan warna orange harus menyala)
   - Tambahkan *CNAME Record* (opsional untuk www):
     - **Type:** `CNAME`
     - **Name:** `www`
     - **Target:** `ppgtangbar.id`
     - **Proxy status:** 🟠 Proxied

3. Masuk ke menu **SSL/TLS -> Overview**.
   - Pilih mode **Flexible** (Jika Nginx di server Anda murni Port 80 tanpa SSL lokal).
   - Pilih mode **Full** (Jika server Anda punya SSL *self-signed* atau Let's Encrypt palsu). Rekomendasi awal: Gunakan **Flexible** agar lebih mudah.

4. Masuk ke menu **SSL/TLS -> Edge Certificates**.
   - Aktifkan fitur **"Always Use HTTPS"** agar seluruh trafik otomatis masuk ke sambungan aman `https://`.

---

## ✅ 6. Mengatasi Masalah Laravel di Balik Proxy (Cloudflare)

Karena server Nginx berjalan di HTTP (Port 80), tetapi Cloudflare memaksa HTTPS, Laravel sering "bingung" dan merender link halaman (`route()`) menjadi `http://`. Hal ini menyebabkan error "Mixed Content" dan halaman rusak.

Untuk memperbaikinya, edit file *middleware* pendeteksi Proxy di Laravel 12.

Buka file konfigurasi middleware utama aplikasi Anda (di Laravel 11/12 biasanya ada di `bootstrap/app.php` atau `App\Http\Middleware\TrustProxies.php`).

Jika Anda menggunakan struktur Laravel terbaru (di `bootstrap/app.php`):

```bash
nano /var/www/ppgtangbar.id/bootstrap/app.php
```

Pastikan Anda menambahkan fungsi `trustProxies` di dalam fungsi `withMiddleware`:

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);
        
        // --- TAMBAHKAN BARIS INI UNTUK CLOUDFLARE ---
        $middleware->trustProxies(at: '*'); 
        // --------------------------------------------
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

*(Catatan: `trustProxies(at: '*')` akan memercayai semua proxy load balancer, termasuk Cloudflare, sehingga Laravel tahu ia harus me-render URL ke `https://`).*

Setelah mengubah file tersebut, **wajib** bersihkan cache kembali:
```bash
php artisan config:clear
php artisan route:clear
```

---

## 🎉 Selesai!

Sekarang buka **[https://ppgtangbar.id](https://ppgtangbar.id)** di browser Anda. Aplikasi SI-JEMAAH seharusnya sudah tampil, lengkap dengan sistem login Super Admin.

**Ceklist Maintenance Rutin:**
- Jika Anda mengubah kode CSS/JS (Vue): `npm run build`
- Jika Anda mengubah kode PHP (Laravel): `php artisan optimize:clear` lalu `php artisan optimize`
- Memantau Error: `tail -f /var/www/ppgtangbar.id/storage/logs/laravel.log`
