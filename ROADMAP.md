# ROADMAP & ARSITEKTUR: SISTEM DATABASE JAMAAH TANGERANG BARAT (SIM-J)

**Status Dokumen:** Draft v1.0  
**Tech Stack:** Laravel 12, Vue.js 3 (Composition API), Inertia.js, Tailwind CSS, MySQL.  
**Input Data:** CSV/Excel (~6.700 Data Jamaah).

---

## 📅 PHASE 1: PREPARATION & DATABASE ARCHITECTURE
*Tujuan: Membangun pondasi data yang kuat dan ternormalisasi agar performa query cepat meski data ribuan.*

### 1.1. Schema Design (Database)
Data dari CSV akan dipecah menjadi tabel-tabel relasional (Normalisasi):

**A. Tabel Master (Wilayah)**
* `desas`: (id, nama_desa [ex: JATI], kode_desa)
* `kelompoks`: (id, desa_id, nama_kelompok [ex: JATI LAMA])

**B. Tabel Data Penduduk**
* `keluargas`: (id, no_kk, alamat_rumah, kepala_keluarga_id [opsional])
* `jamaahs`:
    * `id` (BigInt)
    * `kelompok_id` (Foreign Key)
    * `keluarga_id` (Foreign Key - Nullable)
    * `nama_lengkap` (String)
    * `tgl_lahir` (Date) -> *Umur dihitung otomatis (Accessors)*
    * `jenis_kelamin` (Enum: L, P)
    * `status_pernikahan` (Enum: MENIKAH, BELUM, JANDA, DUDA)
    * `pendidikan_aktivitas` (String) -> *ex: SD, KULIAH, BEKERJA*
    * `no_telepon` (String)
    * `role_dlm_keluarga` (Enum: KEPALA, ISTRI, ANAK, LAINNYA)

### 1.2. Backend Setup (Laravel 12)
* Install Laravel 12 + Jetstream (Inertia Stack).
* Setup Models & Relationships (`hasMany`, `belongsTo`).
* **Import Strategy:** Membuat Service Class khusus (`JamaahImportService`) menggunakan library *Laravel Excel* untuk memindahkan data CSV ke SQL dan menjaga relasi Desa/Kelompok secara otomatis saat upload.

---

## 🖥️ PHASE 2: FRONTEND ARCHITECTURE (VUE.JS + INERTIA)
*Fokus Utama: User Interface yang responsif, cepat, dan mudah digunakan untuk mengelola ribuan data.*

### 2.1. Structure & Directory
Menggunakan struktur modular Inertia.js di dalam folder `resources/js/`:

```text
resources/js/
├── Components/         # Komponen UI Kecil (Reusable)
│   ├── UI/             # Button, Input, Modal, Card
│   ├── Data/           # Pagination, FilterDropdown, BadgeStatus
│   └── Charts/         # Grafik Statistik (Chart.js/ApexCharts)
├── Layouts/            # Template Halaman
│   ├── AppLayout.vue   # Sidebar, Navbar, Footer
│   └── GuestLayout.vue # Login Page
├── Pages/              # Halaman Utama (Views)
│   ├── Dashboard.vue   # Statistik Global
│   ├── Jamaah/         # Modul Jamaah
│       ├── Index.vue   # Tabel Utama (Datatable)
│       ├── Create.vue  # Form Tambah
│       ├── Edit.vue    # Form Edit
│       └── Show.vue    # Detail Profil
│   ├── Wilayah/        # Manajemen Desa/Kelompok
│   └── Laporan/        # Halaman Export Data
├── Stores/             # State Management (Pinia - Opsional jika kompleks)
└── Utils/              # Helper Functions (Format Tanggal Indo, Hitung Umur)
```

### 2.2. Detailed Page Specification

#### A. Halaman Dashboard (Pages/Dashboard.vue)
* Top Cards: Total Jamaah, Total KK, Rasio L/P.
* Chart 1: Grafik Batang "Sebaran Usia" (Balita, Remaja, Pemuda, Lansia).
* Chart 2: Grafik Pie "Status Pernikahan" (Janda/Duda perlu perhatian khusus).
* Table Preview: 5 Data terbaru yang diinput.

#### B. Halaman Data Jamaah (Pages/Jamaah/Index.vue)
Ini adalah halaman paling krusial. Tidak boleh meload 6.000 data sekaligus (akan lag).

* Teknologi: Server-side Pagination (Laravel Paginator).
* Fitur Pencarian: Search bar (mencari nama).
* Advanced Filter (Sidebar/Dropdown):
    * Filter Desa & Kelompok.
    * Filter Kategori Usia (Generus).
    * Filter Status (Janda/Duda/Yatim).
* Kolom Tabel: Nama, L/P, Umur (Auto calc), Desa, Kelompok, Action (Edit/Delete).

#### C. Halaman Detail Keluarga (Pages/Keluarga/Show.vue)
* Menampilkan data dalam format Kartu Keluarga.
* Header: Nama Kepala Keluarga & Alamat.
* List Anggota: Tabel berisi Istri dan Anak-anak dalam satu view.

#### D. Form Input/Edit (Pages/Jamaah/Form.vue)
* Menggunakan komponen form reusable.
* Dynamic Dropdown: Saat pilih "Desa A", dropdown "Kelompok" hanya menampilkan kelompok milik Desa A.
* Validasi Realtime: Menampilkan error jika NIK/Nama kosong.

### 2.3. UI/UX Components Design (Tailwind)
* Theme: Clean, Professional (Dominan Putih/Abu-abu, Aksen Biru/Hijau).
* Responsive: Tabel harus bisa di-scroll horizontal pada tampilan HP (Mobile Friendly).

---

## ⚙️ PHASE 3: DEVELOPMENT STEPS (ROADMAP)

**Step 1: Inisialisasi Project (Hari 1)**
* Install Laravel 12.
* Setup Database MySQL.
* Install Frontend Dependencies (Vue, Tailwind).

**Step 2: Backend Core (Hari 2-3)**
* Buat Migration (Tabel Desas, Kelompoks, Jamaahs).
* Buat Models & Factory (Dummy Data).
* Buat Script Import CSV (Penting agar development punya data nyata).

**Step 3: Frontend Foundation (Hari 4-5) - FOKUS SEKARANG**
* Setup AppLayout (Sidebar Menu).
* Buat Komponen Dasar (Button.vue, Input.vue, Modal.vue).
* Implementasi Dashboard UI (Dummy data dulu).

**Step 4: CRUD Implementation (Hari 6-10)**
* Module Jamaah (Read & Search).
* Module Jamaah (Create & Update).
* Module Filter & Export.

**Step 5: Testing & Deployment (Hari 11)**
* Uji coba import data asli.
* Cek performa query.
* Deploy ke Hosting/VPS.

---

## 🎯 FOKUS FRONTEND (VUE.JS)

Karena Anda ingin memulai dari **Frontend**, strategi terbaiknya adalah:

1. **Mocking Data:** Kita tidak perlu menunggu Backend/Database selesai 100%. Kita bisa membuat tampilan Vue dengan data palsu (Hardcode JSON) dulu untuk memastikan tata letak (Layout) bagus.
2. **Component Driven:** Kita akan buat komponen kecil dulu. Contohnya, membuat komponen `BadgeStatus.vue` yang otomatis berwarna merah jika statusnya "JANDA" atau hijau jika "MENIKAH".

---

## ✅ NEXT STEPS
Mulai dengan setup struktur folder Vue.js dan membuat komponen-komponen dasar UI.
