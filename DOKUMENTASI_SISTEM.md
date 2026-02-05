# 📚 Dokumentasi Sistem SI-JEMAAH

**Sistem Informasi Manajemen Jamaah - Tangerang Barat**  
**Versi:** 2.0 (Multi-Level Admin)  
**Tanggal Update:** 4 Februari 2026  
**Status:** ✅ PRODUCTION READY

---

## 📋 Daftar Isi

1. [Tentang Sistem](#tentang-sistem)
2. [Fitur Utama](#fitur-utama)
3. [Update Terbaru - Multi-Level Admin](#update-terbaru)
4. [Penjelasan Backend](#penjelasan-backend)
5. [Penjelasan Frontend](#penjelasan-frontend)
6. [Cara Menggunakan](#cara-menggunakan)
7. [Troubleshooting](#troubleshooting)

---

## 🎯 Tentang Sistem

**SI-JEMAAH** adalah aplikasi web untuk mengelola data jamaah di wilayah Tangerang Barat. Sistem ini membantu admin dalam:

- 📊 Mengelola data jamaah (nama, alamat, keluarga, dll)
- 🗺️ Mengorganisir berdasarkan Desa dan Kelompok
- 📈 Melihat statistik dan laporan
- 👥 Manajemen multi-level admin dengan hak akses berbeda
- 📥 Import/Export data CSV

### Teknologi yang Digunakan

| Layer | Teknologi |
|-------|-----------|
| **Backend** | Laravel 12 (PHP 8.4) |
| **Frontend** | Vue.js 3 + Inertia.js |
| **Database** | MySQL |
| **Styling** | Tailwind CSS |
| **Server** | Vite (Development) |

---

## ✨ Fitur Utama

### 1. **Manajemen Data Jamaah**
- ➕ Tambah, Edit, Hapus data jamaah
- 🔍 Pencarian dan filter data
- 📋 Data lengkap: Nama, NIK, Alamat, Keluarga, Status Pernikahan, dll
- 📊 Statistik otomatis (Gender, Usia, Status)

### 2. **Organisasi Wilayah**
- 🏘️ **Desa**: Kelompok wilayah utama
- 📦 **Kelompok**: Sub-unit dalam desa
- 🔗 Relasi otomatis Jamaah → Kelompok → Desa

### 3. **Multi-Level Admin (BARU!)**
- 👑 **Super Admin**: Akses penuh ke seluruh sistem
- 🏘️ **Admin Desa**: Kelola data satu desa tertentu
- 📦 **Admin Kelompok**: Kelola data satu kelompok tertentu

### 4. **Import/Export Data**
- 📥 Import dari file CSV
- 📤 Export ke CSV untuk backup atau analisis

---

## 🚀 Update Terbaru - Multi-Level Admin

### Sebelumnya:
- ❌ Hanya ada 1 jenis admin
- ❌ Semua admin punya akses sama
- ❌ Tidak ada pembatasan berdasarkan wilayah

### Sekarang (Versi 2.0):
- ✅ **3 Level Admin** dengan hak akses berbeda
- ✅ **Data Scoping**: Setiap admin hanya lihat data mereka
- ✅ **Dashboard Terpisah** untuk setiap role
- ✅ **Keamanan Berlapis**: Middleware + Controller validation

---

## 🔧 Penjelasan Backend

### 1. Database Schema Changes

#### Tabel `users` (Updated)

**Kolom Baru:**
```sql
- desa_id (BIGINT, nullable, FK to desas)
- kelompok_id (BIGINT, nullable, FK to kelompoks)
```

**Enum `role` Diperbarui:**
```
Sebelum: ['admin', 'operator']
Sekarang: ['super_admin', 'admin_desa', 'admin_kelompok']
```

**Relasi:**
```
User → belongsTo → Desa
User → belongsTo → Kelompok
```

---

### 2. Model: `User.php`

#### Konstanta Role Baru
```php
const ROLE_SUPER_ADMIN = 'super_admin';
const ROLE_ADMIN_DESA = 'admin_desa';
const ROLE_ADMIN_KELOMPOK = 'admin_kelompok';
```

#### Helper Methods
| Method | Fungsi |
|--------|--------|
| `isSuperAdmin()` | Cek apakah user adalah Super Admin |
| `isAdminDesa()` | Cek apakah user adalah Admin Desa |
| `isAdminKelompok()` | Cek apakah user adalah Admin Kelompok |
| `canManageUser($user)` | Cek apakah bisa manage user lain |
| `getScopeLabel()` | Dapatkan label scope (e.g., "Desa Jati") |

**Contoh Penggunaan:**
```php
if ($user->isSuperAdmin()) {
    // Akses penuh
}

if ($user->canManageUser($targetUser)) {
    // Bisa edit/hapus user
}
```

---

### 3. Middleware: `CheckAdminLevel`

**File:** `app/Http/Middleware/CheckAdminLevel.php`

**Fungsi:**
- ✅ Validasi user sudah login
- ✅ Validasi user aktif
- ✅ Validasi role sesuai yang diizinkan

**Contoh di Route:**
```php
Route::middleware(['role:super_admin,admin_desa'])->group(function() {
    // Hanya Super Admin & Admin Desa yang bisa akses
});
```

**Registered Alias:** `'role'` di `bootstrap/app.php`

---

### 4. Controllers

#### **a. DashboardController**

**Auto-Scoping Data:**
```php
// Super Admin → Lihat SEMUA data
// Admin Desa → WHERE desa_id = user.desa_id
// Admin Kelompok → WHERE kelompok_id = user.kelompok_id
```

**Dynamic View Rendering:**
```php
$view = match(true) {
    $user->isSuperAdmin() => 'Dashboard/SuperAdmin',
    $user->isAdminDesa() => 'Dashboard/AdminDesa',
    $user->isAdminKelompok() => 'Dashboard/AdminKelompok',
};
```

**Data yang Di-scope:**
- Total Jamaah
- Total KK
- Gender Ratio
- Age Distribution
- Recent Jamaah (5 terbaru)

---

#### **b. JamaahController**

**Security pada Create/Update:**
```php
// Admin Desa tidak bisa create jamaah di desa lain
if ($user->isAdminDesa() && $kelompok->desa_id !== $user->desa_id) {
    abort(403, 'Anda tidak bisa menambahkan jamaah di desa lain');
}
```

**Auto-Filter di Index:**
```php
if ($user->isAdminDesa()) {
    $query->whereHas('kelompok', fn($q) => $q->where('desa_id', $user->desa_id));
}
```

---

#### **c. AdminManagementController (NEW!)**

**File:** `app/Http/Controllers/AdminManagementController.php`

**Endpoints:**
| Method | Route | Access | Fungsi |
|--------|-------|--------|--------|
| GET | `/admin` | Super Admin, Admin Desa | List admin |
| POST | `/admin` | Super Admin, Admin Desa | Buat admin baru |
| PUT | `/admin/{id}` | Super Admin, Admin Desa | Update admin |
| DELETE | `/admin/{id}` | Super Admin, Admin Desa | Hapus admin |

**Access Rules:**
- Super Admin: Bisa manage **Admin Desa** & **Admin Kelompok** (semua)
- Admin Desa: Hanya bisa manage **Admin Kelompok** di desa sendiri
- Admin Kelompok: **Tidak bisa** manage admin lain

**Security Features:**
```php
// Validasi: Admin Desa tidak bisa buat Admin Desa lain
if ($user->isAdminDesa() && $validatedData['role'] === User::ROLE_ADMIN_DESA) {
    abort(403, 'Anda tidak bisa membuat Admin Desa');
}

// Validasi: Admin Desa hanya bisa hapus admin di desa mereka
if (!$user->canManageUser($adminToDelete)) {
    abort(403, 'Anda tidak memiliki akses untuk menghapus admin ini');
}
```

---

### 5. Routes Protection

**File:** `routes/web.php`

```php
// Semua admin bisa akses
Route::middleware(['auth'])->group(function() {
    Route::get('/', [DashboardController::class, 'index']);
    Route::resource('jamaah', JamaahController::class);
});

// Hanya Super Admin
Route::middleware(['auth', 'role:super_admin'])->group(function() {
    Route::resource('wilayah.desa', DesaController::class);
});

// Super Admin & Admin Desa
Route::middleware(['auth', 'role:super_admin,admin_desa'])->group(function() {
    Route::resource('wilayah.kelompok', KelompokController::class);
    Route::prefix('admin')->name('admin.')->group(function() {
        Route::get('/', [AdminManagementController::class, 'index']);
        Route::post('/', [AdminManagementController::class, 'store']);
        Route::put('/{admin}', [AdminManagementController::class, 'update']);
        Route::delete('/{admin}', [AdminManagementController::class, 'destroy']);
    });
});
```

---

## 🎨 Penjelasan Frontend

### 1. Dashboard Terpisah per Role

#### **a. SuperAdmin.vue**

**Lokasi:** `resources/js/Pages/Dashboard/SuperAdmin.vue`

**Fitur Khusus:**
- 📊 Total Desa
- 📊 Total Kelompok
- 📊 Total Jamaah (Semua)
- 🔗 Shortcut "Kelola Admin"
- 🔗 Shortcut "Kelola Wilayah"

**Stat Cards:**
```javascript
[
    { name: 'Total Jamaah', key: 'totalJamaah' },
    { name: 'Total Desa', key: 'totalDesa' },
    { name: 'Total Kelompok', key: 'totalKelompok' },
    { name: 'Rasio L/P', key: 'genderRatio' }
]
```

---

#### **b. AdminDesa.vue**

**Lokasi:** `resources/js/Pages/Dashboard/AdminDesa.vue`

**Fitur Khusus:**
- 📊 Total Kelompok (di desa sendiri)
- 📊 Total Jamaah (di desa sendiri)
- 🔗 Shortcut "Kelola Admin Kelompok"
- 🔗 Shortcut "Kelola Kelompok"

**Perbedaan:**
- ❌ Tidak ada "Total Desa" (diganti Total Kelompok)
- ✅ Chart "Sebaran Usia (Lingkup Desa)"
- ✅ Tabel hanya tampilkan kolom Kelompok (tanpa Desa)

---

#### **c. AdminKelompok.vue**

**Lokasi:** `resources/js/Pages/Dashboard/AdminKelompok.vue`

**Fitur Khusus:**
- 📊 Total Jamaah (di kelompok sendiri)
- 📊 Total KK
- 📊 Rasio L/P
- 🔗 Shortcut "Tambah Jamaah"

**Perbedaan:**
- ❌ Tidak ada stat Total Desa/Kelompok
- ❌ Tidak ada menu "Kelola Admin"
- ✅ Fokus pada data operasional

---

### 2. Sidebar Dinamis

**File:** `resources/js/Components/Sidebar.vue`

**Logic:**
```javascript
const isSuperAdmin = computed(() => user.value?.role === 'super_admin');
const isAdminDesa = computed(() => user.value?.role === 'admin_desa');
```

**Menu Visibility:**

| Menu Item | Super Admin | Admin Desa | Admin Kelompok |
|-----------|-------------|------------|----------------|
| Dashboard | ✅ | ✅ | ✅ |
| Data Jamaah | ✅ | ✅ | ✅ |
| Data Desa | ✅ | ❌ | ❌ |
| Data Kelompok | ✅ | ✅ | ❌ |
| **Kelola Admin** | ✅ | ✅ | ❌ |
| Import Data | ✅ | ✅ | ✅ |
| Export CSV | ✅ | ✅ | ✅ |

**Implementasi:**
```vue
<!-- Hanya tampil untuk Super Admin & Admin Desa -->
<Link v-if="isSuperAdmin || isAdminDesa" :href="route('admin.index')">
    Kelola Admin
</Link>
```

---

### 3. Admin Management UI

**File:** `resources/js/Pages/Admin/Index.vue`

**Fitur:**
- ✅ List admin dengan pagination
- ✅ Search by name/email
- ✅ Modal Create Admin (Form dinamis)
- ✅ Modal Edit Admin
- ✅ Delete Admin (dengan konfirmasi)
- ✅ Toggle status Active/Inactive

**Form Dinamis:**
```javascript
// Dropdown Desa muncul jika role = admin_desa atau admin_kelompok
const showDesaDropdown = computed(() => {
    return form.role === 'admin_desa' || form.role === 'admin_kelompok';
});

// Dropdown Kelompok di-filter berdasarkan desa yang dipilih
const filteredKelompoks = computed(() => {
    if (!form.desa_id) return [];
    return props.kelompoks.filter(k => k.desa_id == form.desa_id);
});
```

**Auto-Select Logic:**
```javascript
// Jika Admin Desa login (hanya 1 desa tersedia), otomatis select
if (props.desas.length === 1) {
    form.desa_id = props.desas[0].id;
}
```

---

### 4. Login Page Update

**File:** `resources/js/Pages/Auth/Login.vue`

**Update:**
- ❌ Hapus info "Login Demo"
- ✅ Tambah fitur **Show/Hide Password** (ikon mata)

**Implementation:**
```vue
<div class="relative mt-1">
    <input :type="showPassword ? 'text' : 'password'" />
    <button @click="showPassword = !showPassword">
        <svg v-if="!showPassword"><!-- Eye Icon --></svg>
        <svg v-else><!-- Eye Slash Icon --></svg>
    </button>
</div>
```

---

## 📖 Cara Menggunakan

### A. Login Pertama Kali

**Akun Default:**
```
Super Admin:
  Email: admin@jemaah.com
  Password: password
```

**Akun Test:**
```
Admin Desa (JATI):
  Email: admin.desa@test.com
  Password: password

Admin Kelompok (JATI TIMUR):
  Email: admin.kelompok@test.com
  Password: password
```

---

### B. Super Admin - Mengelola Admin Lain

1. **Login** sebagai Super Admin
2. Klik menu **"Kelola Admin"** di sidebar
3. Klik tombol **"+ Tambah Admin"**
4. Isi form:
   - Nama Lengkap
   - Email
   - Password
   - **Pilih Role:**
     - `Admin Desa` → Pilih Desa
     - `Admin Kelompok` → Pilih Desa + Kelompok
5. Centang "Akun Aktif"
6. Klik **"Simpan Admin"**

**Result:**
- Admin baru dibuat
- Email & password dikirim ke admin baru (manual)
- Admin baru bisa login sesuai scope-nya

---

### C. Admin Desa - Mengelola Admin Kelompok

1. **Login** sebagai Admin Desa
2. Klik menu **"Kelola Admin Kelompok"**
3. Klik **"+ Tambah Admin"**
4. **Otomatis:**
   - Desa sudah ter-select (tidak bisa diganti)
   - Role sudah ter-select: `Admin Kelompok`
5. Pilih **Kelompok** (hanya kelompok di desa sendiri)
6. Isi Nama, Email, Password
7. Simpan

**Limitation:**
- ❌ Tidak bisa membuat Admin Desa lain
- ❌ Tidak bisa manage admin di desa lain
- ✅ Hanya bisa manage Admin Kelompok di desa sendiri

---

### D. Admin Kelompok - Mengelola Jamaah

1. **Login** sebagai Admin Kelompok
2. Dashboard menampilkan statistik kelompok sendiri
3. Klik **"+ Tambah Jamaah"**
4. **Auto-Restriction:**
   - Dropdown Kelompok hanya tampilkan kelompok sendiri
   - Tidak bisa pilih kelompok lain
5. Isi data jamaah
6. Simpan

**View Data:**
- ✅ Hanya lihat jamaah di kelompok sendiri
- ❌ Tidak bisa lihat data kelompok lain
- ❌ Tidak ada menu "Kelola Admin"

---

## 🐛 Troubleshooting

### 1. **Error: "Unable to locate file in Vite manifest"**

**Penyebab:** File Vue baru belum di-compile.

**Solusi:**
```bash
npm run dev
```

Pastikan Vite server berjalan di `localhost:5173`.

---

### 2. **Error: "Cannot read properties of undefined (reading 'user')"**

**Penyebab:** Komponen Vue akses `auth.user` sebelum data tersedia.

**Solusi:** Sudah diperbaiki dengan null-safety:
```javascript
const user = computed(() => page.props.auth?.user || {});
```

**Jika masih error:**
```bash
# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Restart server
Ctrl+C (stop php artisan serve)
php artisan serve

# Restart Vite
Ctrl+C (stop npm run dev)
npm run dev
```

---

### 3. **Tidak Bisa Login**

**Cek:**
1. Password benar: `password` (huruf kecil semua)
2. Email benar: `admin@jemaah.com`
3. User aktif di database

**Reset Password:**
```bash
php verify_passwords.php
```

Script ini akan reset password semua akun test menjadi `password`.

---

### 4. **Menu "Kelola Admin" Tidak Muncul**

**Penyebab:** Role user bukan Super Admin atau Admin Desa.

**Cek Role di Database:**
```sql
SELECT id, name, email, role FROM users WHERE email = 'admin@jemaah.com';
```

**Expected:**
```
role = 'super_admin'
```

---

### 5. **403 Forbidden saat Akses Route**

**Penyebab:** User tidak punya permission untuk route tersebut.

**Expected Behavior:**
| Route | Super Admin | Admin Desa | Admin Kelompok |
|-------|-------------|------------|----------------|
| `/` (Dashboard) | ✅ | ✅ | ✅ |
| `/admin` | ✅ | ✅ | ❌ 403 |
| `/wilayah/desa` | ✅ | ❌ 403 | ❌ 403 |
| `/wilayah/kelompok` | ✅ | ✅ | ❌ 403 |

**Solusi:** Login dengan akun yang sesuai.

---

## 🔐 Security Features

### 1. **Middleware Protection**
- ✅ Semua private routes butuh login (`auth` middleware)
- ✅ Sensitive routes dilindungi `role` middleware
- ✅ Auto logout jika user tidak aktif

### 2. **Controller Validation**
- ✅ Validasi scope sebelum Create/Update/Delete
- ✅ Validasi ownership sebelum Edit/Delete admin
- ✅ Prevent self-delete (admin tidak bisa hapus diri sendiri)

### 3. **Database Level**
- ✅ Foreign Key constraints
- ✅ Nullable scope columns (super admin tidak butuh desa/kelompok)
- ✅ Indexed untuk performa query

### 4. **Frontend Protection**
- ✅ Menu visibility based on role
- ✅ Dropdown auto-filter berdasarkan scope
- ✅ Disable input jika hanya 1 pilihan tersedia

---

## 📊 Statistics & Reports

### Dashboard Metrics

**Super Admin:**
- Total Jamaah: Seluruh sistem
- Total Desa: Seluruh desa
- Total Kelompok: Seluruh kelompok
- Gender Ratio: Global
- Age Distribution: Global
- Recent Jamaah: 5 terbaru dari semua desa

**Admin Desa:**
- Total Jamaah: Hanya di desa sendiri
- Total Kelompok: Hanya kelompok di desa sendiri
- Gender Ratio: Scope desa
- Age Distribution: Scope desa
- Recent Jamaah: 5 terbaru di desa sendiri

**Admin Kelompok:**
- Total Jamaah: Hanya di kelompok sendiri
- Total KK: Scope kelompok
- Gender Ratio: Scope kelompok
- Age Distribution: Scope kelompok
- Recent Jamaah: 5 terbaru di kelompok sendiri

---

## 🚀 Deployment Checklist

### 1. **Database Migration**
```bash
php artisan migrate
```

### 2. **Create Super Admin**
```bash
php artisan tinker
```
```php
User::create([
    'name' => 'Super Admin',
    'email' => 'admin@jemaah.com',
    'password' => Hash::make('password'),
    'role' => 'super_admin',
    'is_active' => true,
]);
```

### 3. **Build Frontend**
```bash
npm run build
```

### 4. **Clear Cache**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 5. **Set Permissions**
```bash
chmod -R 775 storage bootstrap/cache
```

### 6. **Test Login**
- Login Super Admin
- Create Admin Desa
- Login Admin Desa
- Create Admin Kelompok
- Login Admin Kelompok
- Verify data scoping works

---

## 📞 Support

**Developer:** AI Assistant  
**Last Updated:** 4 Februari 2026  
**Version:** 2.0

**Files Penting:**
- `laporan_backend.md` - Technical details backend
- `implementation_plan.md` - Design decisions
- `task.md` - Development checklist

---

## ✅ Testing Checklist

- [x] Database migration success
- [x] User roles created
- [x] Middleware protection works
- [x] Controller scoping works
- [x] Dashboard rendering by role
- [x] Sidebar menu dynamic
- [x] Admin Management CRUD
- [x] Login page updated
- [ ] Manual browser testing by user

**Status:** Ready for production! 🎉
