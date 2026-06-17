# Desain: Impor Massal Master Data (Download Template + Import)

Tanggal: 2026-06-17
Status: Disetujui untuk implementasi (menunggu review spec)

## Tujuan

Di setiap halaman master data, pengguna dapat **mengunduh template Excel** dan
**mengimpor file** untuk menambah/memperbarui banyak baris sekaligus (bulk),
tanpa input satu per satu. Alur dengan **pratinjau (preview) lalu konfirmasi**.

## Scope

8 entitas master ikut fitur ini:

1. Kategori Bahan (`ingredient_categories`)
2. Kategori Menu (`menu_categories`)
3. Supplier (`suppliers`)
4. Toko (`stores`)
5. Bahan (`ingredients`)
6. Menu (`menus`)
7. Kemasan Bahan (`ingredient_packagings`)
8. Komposisi Bahan (`ingredient_compositions`)

**Dikecualikan (tetap input manual):**
- Resep (`recipes`) — relasi paling kompleks (menu+bahan+toko+tanggal berlaku, `recipe_group_id`).
- User (`users`) — sensitif (password & role).

## Keputusan Desain

- **Pendekatan A**: template per-entitas + satu mesin impor generik. Tombol
  "Download Template" & "Impor" ada di tiap halaman index master.
- **Upsert**: baris yang cocok dengan kunci unik → update; jika belum ada → buat baru.
- **Preview → Konfirmasi**: validasi dulu, tampilkan tabel pratinjau (Baru/Update/Error),
  lalu konfirmasi untuk commit. Commit bersifat **transaksional (all-or-nothing)**.
- **Relasi diacu lewat nama** (mis. kemasan menyebut nama bahan & nama supplier).
- Konsisten dengan pola impor existing (opname/sales) — PhpSpreadsheet + `ArrayExport`.

## Arsitektur & Komponen

### `MasterImportService` (baru, `app/Services/MasterImportService.php`)
Mesin generik, tidak tahu entitas spesifik. API utama:
- `parse(EntityConfig $cfg, string $filePath): ParsedResult` — baca file, validasi tiap baris,
  resolve relasi, hitung status (baru/update/error) per baris.
- `commit(EntityConfig $cfg, string $filePath): CommitResult` — parse ulang, jalankan upsert
  dalam 1 DB transaction; lempar exception (rollback) bila ada baris error.

`ParsedResult` berisi: daftar baris `{rowNum, status: new|update|error, data, errors[]}`,
ringkasan jumlah (total, baru, update, error).

### `EntityConfig` (registry, `config/master-imports.php`)
Per entitas mendefinisikan:
- `key` (slug entitas, mis. `ingredients`), `label`, `model` (class), `route_index`.
- `columns`: daftar `{header, field, rules, required}`.
- `unique_by`: kolom/kombinasi kunci unik untuk upsert.
- `relations`: peta `{column => [model, match_field, target_field, nullable]}` untuk lookup by nama → id.
- `sample_rows`: 1–2 baris contoh untuk template.
- `permission` (opsional): hak akses minimal.

### `MasterImportController` (baru, `app/Http/Controllers/MasterData/MasterImportController.php`)
Aksi generik berbasis `{entity}`:
- `template(string $entity)` — unduh template Excel (header + baris contoh + sheet "Petunjuk").
- `preview(string $entity)` — POST file → simpan ke `storage/app/imports/{token}.xlsx`,
  jalankan `parse`, render halaman preview.
- `commit(string $entity)` — POST `{token}` → `commit`, hapus file temp, redirect ke index master
  dengan ringkasan (mis. "12 baru, 3 update").

Validasi entity: hanya yang terdaftar di registry; selain itu `abort(404)`.

### Routes (`routes/web.php`, grup auth + prefix `master-import`)
```
GET  master-import/{entity}/template  -> template   name: master-import.template
POST master-import/{entity}/preview   -> preview     name: master-import.preview
POST master-import/{entity}/commit    -> commit      name: master-import.commit
```

### View
- Partial `resources/views/master/partials/import-buttons.blade.php` — tombol
  "Download Template" + "Impor" (modal upload). Disisipkan di tiap halaman index master.
- Halaman preview generik `resources/views/master/import-preview.blade.php` — tabel baris
  berstatus, ringkasan, tombol Konfirmasi / Batal. Tombol Konfirmasi nonaktif jika ada error.

## Definisi Template per Entitas

Kunci unik dipakai untuk mencocokkan upsert. Relasi diacu lewat nama.

| Entitas | Kunci unik | Kolom (header) | Aturan penting |
|---|---|---|---|
| Kategori Bahan | name | name, label, sort_order | name wajib & unik; sort_order integer ≥ 0 (opsional) |
| Kategori Menu | name | name, sort_order | name wajib & unik; sort_order integer ≥ 0 (opsional) |
| Supplier | name | name, type, contact, address, is_active | type ∈ {zhisheng, local_supplier, other}; is_active ∈ {1,0/ya,tidak} |
| Toko | store_code | store_code, name, area, is_active, lead_time_days, order_cycle_days, dos_window_days, par_days | store_code wajib & unik; angka-angka integer ≥ 0 (opsional, default ikut DB) |
| Bahan | name | name, type, category, unit_base, is_active | type ∈ {raw, semi_finished}; category ∈ {bubuk,teh,sirup,selai,solid,kemasan} (boleh kosong); unit_base wajib |
| Menu | name | name, category, is_active | category → `menu_categories.name` (harus sudah ada) → set `category_id` + simpan string `category` |
| Kemasan Bahan | ingredient + packaging_name | ingredient, packaging_name, supplier, crate_to_pack, pack_to_base, is_active | ingredient → `ingredients.name` (wajib ada); supplier → `suppliers.name` (boleh kosong); crate_to_pack & pack_to_base integer ≥ 1 |
| Komposisi Bahan | parent + child | parent, child, qty_needed | parent & child → `ingredients.name` (wajib ada); parent ≠ child; qty_needed > 0 |

Catatan:
- `is_active`: terima `1/0`, `ya/tidak`, `true/false`, kosong = `true`.
- Nilai relasi yang tidak ditemukan → baris berstatus **Error** dengan pesan jelas
  (mis. "Bahan 'Susu UHT' tidak ditemukan").
- `ingredients.category` adalah enum tetap (bukan FK ke tabel Kategori Bahan); keduanya master terpisah.

## Format File Template

- `.xlsx`, sheet "Data": baris 1 = header kolom; baris 2+ = contoh (boleh dihapus user).
- Sheet "Petunjuk": penjelasan kolom, nilai enum yang valid, catatan relasi.
- Saat impor, parser membaca sheet "Data", melewati baris kosong, header dicocokkan
  case-insensitive terhadap konfigurasi kolom.

## Alur Validasi & Error

- Tiap baris divalidasi terhadap `rules` + resolusi relasi.
- Baris kosong dilewati.
- Duplikat kunci unik **di dalam file yang sama** → error pada baris kedua dst.
- Halaman preview menampilkan semua baris dengan status & alasan error.
- Commit hanya boleh jalan bila **tidak ada baris error**; dijalankan dalam transaction
  (jika gagal di tengah, seluruh impor dibatalkan).

## Penanganan File Sementara

- File upload disimpan di `storage/app/imports/{token}.xlsx` saat preview.
- `token` = UUID, dikirim ke halaman preview sebagai hidden field.
- Saat commit, file dibaca ulang dari token lalu dihapus.
- (Opsional housekeeping: file lebih tua dari 24 jam bisa dibersihkan; di luar scope awal.)

## Pengujian

- **Unit**: `MasterImportService::parse` untuk tiap config — baris valid, enum salah,
  relasi tak ditemukan, duplikat dalam file, upsert (update vs insert).
- **Feature**: endpoint `template` (file ter-download, header benar), `preview`
  (status baris benar), `commit` (data tersimpan, transaksional rollback saat ada error).
- **Manual**: download template tiap entitas, isi, impor, verifikasi di halaman index.

## Out of Scope

- Impor Resep & User.
- Penjadwalan/housekeeping otomatis file temp.
- Impor lintas-entitas dalam satu file (multi-sheet).
