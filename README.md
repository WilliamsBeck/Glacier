# PANDUAN INSTALASI MIXUE INVENTORY
## Step by Step untuk Pemula

---

## 📦 ISI FOLDER INI

```
mixue/
├── README.md                    ← File ini
├── .env.example                 ← Template environment variables
├── bootstrap_app.php            ← File untuk replace bootstrap/app.php
├── database/
│   ├── migrations/              ← 17 file migration (semua tabel)
│   └── seeders/
│       └── DatabaseSeeder.php   ← Seeder super admin
├── app/
│   ├── Models/                  ← 19 Eloquent models
│   ├── Http/
│   │   ├── Controllers/         ← Semua controller
│   │   └── Middleware/          ← CheckRole, CheckStoreAccess
│   └── Services/                ← FifoService, StockLedgerService, MutationService
├── routes/
│   └── web.php                  ← Semua routing
├── resources/views/             ← Semua Blade templates
└── public/css/
    └── app.css                  ← Custom styling
```

---

## 🚀 STEP 1 — INSTALL LARAGON

1. Download Laragon Full di: https://laragon.org/download
2. Install ke `C:\laragon` (jangan ubah lokasi default)
3. Buka Laragon → klik **Start All**
4. Pastikan Apache & MySQL hijau (running)

---

## 🚀 STEP 2 — BUAT PROJECT LARAVEL

Buka Terminal Laragon (Menu → Terminal), ketik:

```bash
cd C:\laragon\www
composer create-project laravel/laravel mixue-inventory
cd mixue-inventory
```

---

## 🚀 STEP 3 — INSTALL BREEZE (UNTUK LOGIN)

```bash
composer require laravel/breeze --dev
php artisan breeze:install blade
```

Saat diminta dark mode / pest, pilih **No** semua.

---

## 🚀 STEP 4 — BUAT DATABASE

1. Buka browser → http://localhost/phpmyadmin
2. Login: username = `root`, password = (kosongkan)
3. Klik **New** di sidebar kiri
4. Nama database: `mixue_inventory`
5. Collation: `utf8mb4_unicode_ci`
6. Klik **Create**

---

## 🚀 STEP 5 — KONFIGURASI .env

Buka file `.env` di root project Laravel kamu, edit bagian database:

```
APP_NAME="Mixue Inventory"
APP_URL=http://mixue-inventory.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mixue_inventory
DB_USERNAME=root
DB_PASSWORD=
```

Lalu generate app key:
```bash
php artisan key:generate
```

---

## 🚀 STEP 6 — COPY SEMUA FILE DARI ZIP INI

Buka folder ZIP yang sudah di-extract, copy ke project Laravel kamu:

| Dari ZIP | Ke project Laravel |
|----------|---------------------|
| `database/migrations/*` | `mixue-inventory/database/migrations/` |
| `database/seeders/DatabaseSeeder.php` | `mixue-inventory/database/seeders/DatabaseSeeder.php` (replace) |
| `app/Models/*` | `mixue-inventory/app/Models/` |
| `app/Http/Controllers/*` | `mixue-inventory/app/Http/Controllers/` |
| `app/Http/Middleware/*` | `mixue-inventory/app/Http/Middleware/` |
| `app/Services/*` | `mixue-inventory/app/Services/` (folder baru, buat dulu) |
| `routes/web.php` | `mixue-inventory/routes/web.php` (replace) |
| `resources/views/*` | `mixue-inventory/resources/views/` (replace `auth/login.blade.php`) |
| `public/css/app.css` | `mixue-inventory/public/css/app.css` |
| `bootstrap_app.php` | Copy ISI-nya ke `mixue-inventory/bootstrap/app.php` (replace seluruh isi) |

⚠️ **PENTING:** File `bootstrap_app.php` di ZIP ini berbeda nama dengan target. Buka file `bootstrap_app.php`, copy isinya, lalu paste ke `bootstrap/app.php` di project Laravel kamu (replace semua isinya).

---

## 🚀 STEP 7 — DOWNLOAD BOOTSTRAP & ICONS (TANPA Node.js)

### A. Bootstrap CSS/JS
1. Buka https://getbootstrap.com/docs/5.3/getting-started/download/
2. Klik **Download** di bagian **Compiled CSS and JS**
3. Extract file zip-nya
4. Copy file ini ke folder project:
   - `bootstrap.min.css` → `public/css/`
   - `bootstrap.bundle.min.js` → `public/js/`

### B. Bootstrap Icons
1. Buka https://github.com/twbs/icons/releases (cari versi terbaru, klik Assets)
2. Download `bootstrap-icons-X.XX.X.zip`
3. Extract
4. Copy:
   - `font/bootstrap-icons.css` → `public/css/`
   - Folder `font/fonts/` → `public/fonts/` (sehingga jadi `public/fonts/bootstrap-icons.woff2`)

### C. jQuery
1. Buka https://jquery.com/download/
2. Klik kanan pada link **"Download the compressed, production jQuery 3.x.x"** → Save Link As
3. Save dengan nama `jquery.min.js` → ke `public/js/`

Setelah selesai, struktur folder `public/` harus seperti ini:
```
public/
├── css/
│   ├── bootstrap.min.css
│   ├── bootstrap-icons.css
│   └── app.css
├── js/
│   ├── bootstrap.bundle.min.js
│   └── jquery.min.js
└── fonts/
    ├── bootstrap-icons.woff
    └── bootstrap-icons.woff2
```

---

## 🚀 STEP 8 — JALANKAN MIGRATION

```bash
php artisan migrate
```

Kalau ada error, coba reset dulu:
```bash
php artisan migrate:fresh
```

---

## 🚀 STEP 9 — BUAT SUPER ADMIN

```bash
php artisan db:seed
```

Akan muncul:
```
✅ Super Admin dibuat:
   Email   : admin@mixue.id
   Password: Admin123!
```

---

## 🚀 STEP 10 — JALANKAN APLIKASI

```bash
php artisan serve
```

Buka browser: **http://localhost:8000**

Login dengan:
- Email: `admin@mixue.id`
- Password: `Admin123!`

---

## 📋 ALUR KERJA APLIKASI

Setelah login sebagai Super Admin, lakukan urutan ini:

### 1. Setup Master Data (Super Admin)
1. **Toko** — Tambah semua toko (sidebar: Master Data → Toko)
2. **Supplier** — Tambah Zhisheng + supplier lokal
3. **Bahan** — Tambah bahan baku (raw) dan setengah jadi (semi_finished)
4. **Kemasan** — Untuk tiap bahan raw, tambah kemasan (Dus → Pack → Gram)
5. **Komposisi** — Untuk tiap bahan semi_finished, isi resep bahan baku pembentuknya
6. **Menu** — Tambah menu yang dijual
7. **Resep** — Untuk tiap menu, isi takaran bahan per pcs
8. **User** — Tambah admin area, assign ke toko tertentu

### 2. Input Stok Awal
- Master Data → Mutasi → Input Mutasi
- Pilih tipe **opening_stock**
- Pilih toko, isi semua bahan + harga
- Confirmed → stok masuk

### 3. Operasional Harian
- **Pembelian** dari Zhisheng / supplier → Input Mutasi
- **Transfer antar toko** → Input Mutasi tipe transfer
- **Produksi semi_finished** (boba, dll) → menu Produksi
- **Waste** bahan rusak → menu Waste
- **Stock Opname** tgl 15 dan akhir bulan → menu Opname

### 4. Akhir Periode
- Input data **Penjualan** per menu per toko (mid/end month)
- Generate **Laporan HPP** → lihat selisih ideal vs aktual

---

## 🛠️ TROUBLESHOOTING

**Class not found / namespace error:**
```bash
composer dump-autoload
```

**View not found / Cache error:**
```bash
php artisan view:clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

**Mau reset ulang database:**
```bash
php artisan migrate:fresh --seed
```

**CSS/JS tidak muncul:**
- Pastikan file ada di `public/css/` dan `public/js/`
- Cek di browser Network tab, apakah ada 404
- Pastikan `APP_URL` di `.env` sudah benar

**Error saat migration: foreign key constraint:**
- Pastikan urutan file migration benar (lihat nomor di nama file)
- Coba `php artisan migrate:fresh`

---

## 📚 STRUKTUR APLIKASI

### Role
- **Super Admin** (admin@mixue.id) — akses semua toko + master data
- **Admin Area** — akses hanya toko yang di-assign

### 21 Tabel Database
Lihat file migration di folder `database/migrations/` untuk detail.

### Service Layer
- **FifoService** — kalkulasi FIFO untuk harga & deduksi stok
- **StockLedgerService** — catat ke ledger + update saldo
- **MutationService** — confirm mutasi + trigger ledger

---

## 🌐 DEPLOY KE HOSTINGER

Setelah aplikasi jalan di lokal dan teruji:

1. Buat repo di GitHub, push project
2. Login Hostinger → buka SSH
3. `cd public_html && git clone <repo-url> .`
4. `composer install --no-dev --optimize-autoloader`
5. Copy `.env` production dengan kredensial DB Hostinger
6. `php artisan key:generate`
7. `php artisan migrate --force`
8. `php artisan db:seed --force`
9. Set Document Root di hPanel ke `public_html/public`

Detail lengkap deploy lihat di file Word panduan sebelumnya.

---

**Selamat coding! 🚀**
