# 🕌 SI-JEMAAH

**Sistem Informasi Manajemen Jamaah - Tangerang Barat**

Aplikasi web untuk mengelola data jamaah dengan fitur multi-level admin, data wilayah (Desa/Kelompok), statistik, dan import/export CSV.

---

## ✨ Fitur Utama

- 📊 **Dashboard Statistik** - Visualisasi data jamaah per wilayah
- 👥 **Multi-Level Admin** - Super Admin, Admin Desa, Admin Kelompok
- 🗺️ **Manajemen Wilayah** - Desa dan Kelompok
- 📥 **Import/Export** - Dari/ke file CSV
- 🔐 **Role-Based Access** - Data scoping otomatis per level admin

---

## 🚀 Quick Setup

### Opsi 1: Dengan Docker (Recommended)

```bash
# Clone repository
git clone https://github.com/indramaulin76/PPG.git
cd PPG

# Windows
docker-setup.bat

# macOS/Linux
chmod +x docker-setup.sh
./docker-setup.sh
```

**Akses:** http://localhost:8000

---

### Opsi 2: Manual Setup

#### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8.0+

#### Steps

```bash
# 1. Clone & Install
git clone https://github.com/indramaulin76/PPG.git
cd PPG
composer install
npm install

# 2. Environment
cp .env.example .env
php artisan key:generate

# 3. Database (edit .env dulu untuk DB credentials)
php artisan migrate

# 4. Jalankan
php artisan serve
npm run dev
```

**Akses:** http://localhost:8000

---

## 🔑 Default Login

| Role | Email | Password |
|------|-------|----------|
| Super Admin | admin@jemaah.com | password |

> Buat akun tambahan melalui menu **Kelola Admin** setelah login.

---

## 🏗️ Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | Laravel 12, PHP 8.4 |
| Frontend | Vue.js 3, Inertia.js |
| Database | MySQL 8.0 |
| Styling | Tailwind CSS |
| Container | Docker + Nginx |

---

## 📁 Struktur Admin

```
Super Admin (Akses Global)
├── Admin Desa (Per Desa)
│   └── Admin Kelompok (Per Kelompok)
```

Setiap level hanya bisa melihat dan mengelola data sesuai scope-nya.

---

## 📖 Dokumentasi Lengkap

- **[DOKUMENTASI_SISTEM.md](DOKUMENTASI_SISTEM.md)** - Penjelasan detail sistem
- **[DOCKER.md](DOCKER.md)** - Panduan Docker lengkap

---

## 📝 License

MIT License - Bebas digunakan dan dimodifikasi.
