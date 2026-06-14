@extends('layouts.app')
@section('title', isset($menu) ? 'Edit Menu' : 'Tambah Menu')

@section('content')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
        .web3-container {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            min-height: 100vh;
        }

        /* TOMBOL KEMBALI (PILL STYLE) */
        .btn-back-pill {
            display: inline-flex;
            align-items: center;
            background-color: #ffffff;
            color: #475569;
            border: 1px solid #cbd5e1;
            border-radius: 50px;
            font-weight: 600;
            padding: 8px 20px;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-back-pill:hover {
            background-color: #f1f5f9;
            color: #0f172a;
            border-color: #94a3b8;
        }

        /* KARTU UTAMA */
        .web3-form-card {
            background-color: #ffffff;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.02), 0 5px 15px rgba(0, 0, 0, 0.01);
            border: 1px solid #e2e8f0;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        /* HEADER KARTU */
        .card-header-premium {
            padding: 20px 24px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .header-icon-box {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
        }

        .icon-menu {
            background-color: #eff6ff;
            color: #2563eb;
        }

        /* Biru untuk Menu */
        .icon-recipe {
            background-color: #f0fdf4;
            color: #16a34a;
        }

        /* Hijau untuk Resep */
        .icon-saved {
            background-color: #f8fafc;
            color: #475569;
        }

        /* Slate untuk Tersimpan */

        .section-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }

        /* AREA KONTEN FORM */
        .card-body-premium {
            padding: 24px;
            flex: 1;
        }

        /* LABEL DAN INPUT MAIN FORM */
        .premium-label {
            font-weight: 600;
            color: #1e293b;
            font-size: 0.9rem;
            margin-bottom: 8px;
            display: block;
        }

        .premium-input,
        .premium-select {
            border-radius: 12px !important;
            background-color: #f1f5f9 !important;
            border: 1px solid transparent !important;
            font-size: 0.95rem !important;
            padding: 12px 16px !important;
            color: #0f172a !important;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .premium-input:focus,
        .premium-select:focus {
            background-color: #ffffff !important;
            border-color: #cbd5e1 !important;
            box-shadow: 0 0 0 4px rgba(15, 23, 42, 0.05) !important;
            outline: none;
        }

        .premium-input::placeholder {
            color: #94a3b8 !important;
        }

        /* INPUT FIELD SMALL (Untuk baris dinamis Resep) */
        .premium-input-sm {
            border-radius: 8px !important;
            background-color: #f8fafc !important;
            border: 1px solid #e2e8f0 !important;
            font-size: 0.88rem !important;
            padding: 10px 12px !important;
            color: #0f172a !important;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .premium-input-sm:focus {
            background-color: #ffffff !important;
            border-color: #cbd5e1 !important;
            box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.05) !important;
            outline: none;
        }

        /* TOMBOL SUBMIT BESAR */
        .btn-submit-premium {
            background-color: #0f172a;
            color: #ffffff;
            border-radius: 12px;
            font-weight: 700;
            padding: 14px 0;
            width: 100%;
            border: none;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .btn-submit-premium:hover {
            background-color: #1e293b;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.15);
        }

        /* TOGGLE SWITCH */
        .form-switch .form-check-input {
            width: 2.8em;
            height: 1.4em;
            cursor: pointer;
            border: none;
            background-color: #cbd5e1;
        }

        .form-switch .form-check-input:checked {
            background-color: #10b981;
        }

        /* KOTAK CHECKBOX TOKO */
        .store-checkbox-box {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background-color: #f8fafc;
            padding: 16px;
            max-height: 140px;
            overflow-y: auto;
        }

        .store-checkbox-box::-webkit-scrollbar {
            width: 6px;
        }

        .store-checkbox-box::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        /* BADGE KUSTOM */
        .badge-clear {
            font-weight: 600;
            font-size: 0.82rem;
            padding: 6px 12px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
        }
    </style>

    <div class="web3-container pb-5">
        <div class="container-fluid pt-4">
            <div class="row">
                <div class="col-xl-10 col-lg-12 mx-auto">

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h3 class="fw-700 mb-1" style="font-weight: 700; color: #0f172a; letter-spacing: -0.5px;">
                                {{ isset($menu) ? 'Edit Menu: ' . $menu->name : 'Tambah Menu Baru' }}
                            </h3>
                            <p class="text-muted small mb-0" style="font-size: 0.9rem;">Kelola master data menu dan varian
                                resepnya</p>
                        </div>
                        <a href="{{ route('master.menus.index') }}" class="btn-back-pill">
                            <i class="bi bi-arrow-left me-2"></i>Kembali
                        </a>
                    </div>

                    {{-- =====================================================================
                    RESEP TERSIMPAN — di LUAR form utama agar tidak ada nested form
                    ===================================================================== --}}
                    @php
                        $bulanID = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];
                        $formatID = function ($date) use ($bulanID) {
                            $c = \Carbon\Carbon::parse($date);
                            return $c->format('d') . ' ' . $bulanID[(int) $c->format('n')] . ' ' . $c->format('Y');
                        };
                    @endphp

                    @if(isset($menu) && isset($recipes) && $recipes->count())
                        <div class="web3-form-card mb-4">
                            <div class="card-header-premium bg-light border-bottom">
                                <div class="header-icon-box icon-saved">
                                    <i class="bi bi-journal-check"></i>
                                </div>
                                <h4 class="section-title">Daftar Versi Resep Tersimpan</h4>
                            </div>
                            <div class="card-body p-4 bg-light">
                                <div class="row g-3">
                                    @foreach($recipes as $groupId => $items)
                                        @php
                                            $first = $items->first();
                                            $date = $first->effective_from->toDateString();
                                            $vStoreIds = $items->pluck('store_id')->unique()->values();
                                            $isDefault = $vStoreIds->contains(null);
                                            $vStoreNames = $items->pluck('store.name')->filter()->unique()->values();
                                            $uniqueItems = $items->unique('ingredient_id')->values();
                                            $itemsData = $uniqueItems->map(fn($it) => [
                                                'ingredient_id' => $it->ingredient_id,
                                                'qty_usage' => $it->qty_usage,
                                                'unit' => $it->unit,
                                            ])->values();
                                            $storeIdsForJs = $isDefault ? [] : $vStoreIds->filter()->values()->all();
                                        @endphp
                                        <div class="col-md-6 col-lg-4">
                                            <div class="bg-white rounded-4 border p-3 h-100 shadow-sm d-flex flex-column">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <div class="d-flex flex-column gap-2">
                                                        <span class="badge-clear"
                                                            style="background-color: #0f172a; color: #ffffff; width: fit-content;">
                                                            <i class="bi bi-calendar-event me-1"></i> {{ $formatID($date) }}
                                                        </span>
                                                        <div class="d-flex flex-wrap gap-1">
                                                            @if($isDefault)
                                                                <span class="badge-clear"
                                                                    style="background-color: #dcfce7; color: #166534; font-size: 0.75rem; padding: 4px 8px;">Default
                                                                    (Semua Toko)</span>
                                                            @else
                                                                @foreach($vStoreNames as $sn)
                                                                    <span class="badge-clear"
                                                                        style="background-color: #e0f2fe; color: #0284c7; font-size: 0.75rem; padding: 4px 8px;">
                                                                        <i class="bi bi-shop me-1"></i> {{ $sn }}
                                                                    </span>
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="d-flex gap-1">
                                                        <button type="button" class="btn btn-sm btn-light border btn-edit-version"
                                                            style="border-radius: 8px; color: #475569;" data-date="{{ $date }}"
                                                            data-stores='@json($storeIdsForJs)' data-items='@json($itemsData)'>
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        <form method="POST"
                                                            action="{{ route('master.menus.recipe-version.destroy', ['menu' => $menu->id, 'group' => $first->recipe_group_id ?? 'kosong']) }}"
                                                            onsubmit="return confirm('Hapus versi resep {{ $formatID($date) }}?')">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-light border text-danger"
                                                                style="border-radius: 8px; background-color: #fff5f5; border-color: #fee2e2 !important;">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>

                                                <div class="mt-auto">
                                                    <table class="table table-sm mb-0 align-middle">
                                                        <thead style="background-color: #f8fafc;">
                                                            <tr>
                                                                <th class="text-muted fw-semibold py-2 border-0 rounded-start"
                                                                    style="font-size: 0.8rem;">Bahan</th>
                                                                <th class="text-end text-muted fw-semibold py-2 border-0"
                                                                    style="font-size: 0.8rem;">Qty</th>
                                                                <th class="text-muted fw-semibold py-2 border-0 rounded-end"
                                                                    style="font-size: 0.8rem;">Satuan</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($uniqueItems as $item)
                                                                <tr>
                                                                    <td class="fw-medium text-dark border-0 border-bottom"
                                                                        style="font-size: 0.85rem;">{{ $item->ingredient->name }}</td>
                                                                    <td class="text-end fw-bold text-dark border-0 border-bottom"
                                                                        style="font-size: 0.85rem;">
                                                                        {{ number_format($item->qty_usage, 0, ',', '.') }}
                                                                    </td>
                                                                    <td class="text-secondary border-0 border-bottom"
                                                                        style="font-size: 0.8rem;">{{ $item->unit }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- =====================================================================
                    FORM UTAMA
                    ===================================================================== --}}
                    <form id="menuForm" method="POST"
                        action="{{ isset($menu) ? route('master.menus.update', $menu) : route('master.menus.store') }}">
                        @csrf @if(isset($menu)) @method('PUT') @endif

                        <div class="row g-4">
                            {{-- KIRI: Info Menu --}}
                            <div class="col-lg-4">
                                <div class="web3-form-card">
                                    <div class="card-header-premium">
                                        <div class="header-icon-box icon-menu">
                                            <i class="bi bi-info-circle-fill"></i>
                                        </div>
                                        <h4 class="section-title">Info Menu Dasar</h4>
                                    </div>

                                    <div class="card-body-premium">
                                        <div class="mb-4">
                                            <label class="premium-label">Nama Menu <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="name" class="form-control premium-input"
                                                value="{{ old('name', $menu->name ?? '') }}"
                                                placeholder="Contoh: Brown Sugar Boba" required>
                                        </div>

                                        <div class="mb-4">
                                            <label class="premium-label">Kategori</label>
                                            <select name="category_id" class="form-select premium-select">
                                                <option value="">— Pilih Kategori —</option>
                                                @foreach($menuCategories as $mc)
                                                    <option value="{{ $mc->id }}" {{ old('category_id', $menu->category_id ?? '') == $mc->id ? 'selected' : '' }}>
                                                        {{ $mc->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="form-text mt-2 fw-medium" style="font-size: 0.85rem;">
                                                <a href="{{ route('master.categories.index') }}#menu" target="_blank"
                                                    class="text-decoration-none text-primary">
                                                    <i class="bi bi-plus-circle me-1"></i>Kelola kategori menu
                                                </a>
                                            </div>
                                        </div>

                                        <div class="mb-4 pt-4 border-top">
                                            <div class="form-check form-switch d-flex align-items-center gap-3 ps-0">
                                                <input class="form-check-input m-0" type="checkbox" name="is_active"
                                                    value="1" id="actMenu" {{ old('is_active', $menu->is_active ?? true) ? 'checked' : '' }}>
                                                <label class="form-check-label premium-label mb-0" for="actMenu"
                                                    style="cursor: pointer;">Set Menu Aktif</label>
                                            </div>
                                        </div>

                                        <div class="mt-auto pt-2">
                                            <button type="submit" class="btn-submit-premium">
                                                <i class="bi bi-save2-fill me-2"></i>Simpan Data Menu
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- KANAN: Input Resep Baru --}}
                            <div class="col-lg-8">
                                <div class="web3-form-card" id="recipeFormCard">
                                    <div class="card-header-premium">
                                        <div class="header-icon-box icon-recipe">
                                            <i class="bi bi-card-list"></i>
                                        </div>
                                        <h4 class="section-title">
                                            {{ isset($menu) ? 'Tambah / Update Versi Resep' : 'Resep Menu' }}
                                        </h4>
                                    </div>

                                    <div class="card-body-premium">
                                        <div class="row g-4 mb-4">
                                            <div class="col-md-5">
                                                <label class="premium-label">Tanggal Berlaku <span
                                                        class="text-danger">*</span></label>
                                                <input type="date" name="effective_from" class="form-control premium-input"
                                                    value="{{ old('effective_from', now()->toDateString()) }}" required>
                                                <div class="form-text mt-2 text-muted"
                                                    style="font-size: 0.8rem; line-height: 1.5;">
                                                    <i class="bi bi-info-circle me-1"></i>Jika tanggal & toko sama dengan
                                                    versi lama, otomatis akan menimpa data lama.
                                                </div>
                                            </div>
                                            <div class="col-md-7">
                                                <label class="premium-label">Berlaku Khusus Untuk Toko</label>
                                                <div class="store-checkbox-box d-flex flex-wrap gap-3">
                                                    @foreach($stores ?? [] as $s)
                                                        <label class="form-check m-0 d-flex align-items-center gap-2">
                                                            <input class="form-check-input store-checkbox m-0" type="checkbox"
                                                                name="store_ids[]" value="{{ $s->id }}">
                                                            <span class="form-check-label fw-medium text-dark"
                                                                style="font-size: 0.88rem; cursor: pointer;">{{ $s->name }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                                <div class="form-text mt-2" style="font-size: 0.8rem; line-height: 1.4;">
                                                    Kosongkan jika berlaku untuk <strong>semua toko</strong> (default).
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Header Tabel Resep --}}
                                        <div class="row g-2 mb-2 px-2 border-bottom pb-2 mt-4">
                                            <div class="col-5"><span class="premium-label text-muted mb-0"
                                                    style="font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.5px;">Bahan
                                                    Baku</span></div>
                                            <div class="col-3"><span class="premium-label text-muted mb-0"
                                                    style="font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.5px;">Qty
                                                    Digunakan</span></div>
                                            <div class="col-3"><span class="premium-label text-muted mb-0"
                                                    style="font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.5px;">Satuan</span>
                                            </div>
                                            <div class="col-1"></div>
                                        </div>

                                        <div id="recipeRows" class="px-2">
                                            <div class="recipe-row row g-2 mb-3 align-items-center">
                                                <div class="col-5">
                                                    <select name="items[0][ingredient_id]"
                                                        class="form-select premium-select premium-input-sm">
                                                        <option value="">— Pilih Bahan —</option>
                                                        @foreach($ingredients as $ing)
                                                            <option value="{{ $ing->id }}" data-unit="{{ $ing->unit_base }}">
                                                                {{ $ing->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-3">
                                                    <input type="number" name="items[0][qty_usage]"
                                                        class="form-control premium-input-sm" step="1" min="1"
                                                        placeholder="cth: 50">
                                                </div>
                                                <div class="col-3">
                                                    <input type="text" name="items[0][unit]"
                                                        class="form-control premium-input-sm bg-light text-center unit-display text-muted fw-bold border-0"
                                                        readonly placeholder="—">
                                                </div>
                                                <div class="col-1 d-flex justify-content-center"></div>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                                            <button type="button" id="addRow"
                                                class="btn btn-outline-dark btn-sm rounded-pill px-4 py-2 fw-bold d-flex align-items-center gap-2"
                                                style="border-color: #cbd5e1;">
                                                <i class="bi bi-plus-lg"></i> Tambah Bahan
                                            </button>
                                            <div class="text-muted small fw-medium bg-light px-3 py-2 rounded-3 border">
                                                <i class="bi bi-lightbulb text-warning me-1"></i>Kosongkan bahan jika tidak
                                                ingin menyimpan resep
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <script>
        let rowIdx = 1;
        const ingredientOptions = @json($ingredients->map(fn($i) => ['id' => $i->id, 'label' => $i->name, 'unit' => $i->unit_base]));

        // Map id → unit_base untuk lookup cepat
        const unitMap = {};
        ingredientOptions.forEach(i => { unitMap[i.id] = i.unit; });

        // Saat pilih bahan, unit otomatis sesuai unit_base bahan tersebut
        function bindIngredientChange(row) {
            const ingSelect = row.querySelector('select[name$="[ingredient_id]"]');
            const unitInput = row.querySelector('[name$="[unit]"]');
            ingSelect.addEventListener('change', function () {
                const unit = unitMap[this.value];
                if (unitInput) unitInput.value = unit || '';
            });
        }

        // Bind ke baris pertama yang sudah ada di HTML
        document.querySelectorAll('#recipeRows .recipe-row').forEach(bindIngredientChange);

        document.getElementById('addRow').addEventListener('click', function () {
            let opts = '<option value="">— Pilih Bahan —</option>';
            ingredientOptions.forEach(i => { opts += `<option value="${i.id}">${i.label}</option>`; });

            const tr = document.createElement('div');
            tr.className = 'recipe-row row g-2 mb-3 align-items-center';
            tr.innerHTML = `
                    <div class="col-5">
                        <select name="items[${rowIdx}][ingredient_id]" class="form-select premium-select premium-input-sm">${opts}</select>
                    </div>
                    <div class="col-3">
                        <input type="number" name="items[${rowIdx}][qty_usage]" class="form-control premium-input-sm" step="1" min="1" placeholder="cth: 50">
                    </div>
                    <div class="col-3">
                        <input type="text" name="items[${rowIdx}][unit]" class="form-control premium-input-sm bg-light text-center unit-display text-muted fw-bold border-0" readonly placeholder="—">
                    </div>
                    <div class="col-1 d-flex justify-content-center">
                        <button type="button" class="btn btn-light border text-danger btn-sm remove-row" style="border-radius: 8px;"><i class="bi bi-x-lg"></i></button>
                    </div>
                `;
            tr.querySelector('.remove-row').addEventListener('click', () => tr.remove());
            bindIngredientChange(tr);
            document.getElementById('recipeRows').appendChild(tr);
            rowIdx++;
        });

        // ── Edit Versi Resep: pre-fill form bawah dengan data versi lama ───────────
        function buildRecipeRow(idx, data) {
            let opts = '<option value="">— Pilih Bahan —</option>';
            ingredientOptions.forEach(i => {
                const sel = (data && i.id == data.ingredient_id) ? 'selected' : '';
                opts += `<option value="${i.id}" ${sel}">${i.label}</option>`;
            });
            const tr = document.createElement('div');
            tr.className = 'recipe-row row g-2 mb-3 align-items-center';
            tr.innerHTML = `
                    <div class="col-5">
                        <select name="items[${idx}][ingredient_id]" class="form-select premium-select premium-input-sm">${opts}</select>
                    </div>
                    <div class="col-3">
                        <input type="number" name="items[${idx}][qty_usage]" class="form-control premium-input-sm" step="1" min="1" value="${data?.qty_usage ?? ''}" placeholder="cth: 50">
                    </div>
                    <div class="col-3">
                        <input type="text" name="items[${idx}][unit]" class="form-control premium-input-sm bg-light text-center unit-display text-muted fw-bold border-0" readonly value="${data?.unit ?? ''}" placeholder="—">
                    </div>
                    <div class="col-1 d-flex justify-content-center">
                        ${idx > 0 ? '<button type="button" class="btn btn-light border text-danger btn-sm remove-row" style="border-radius: 8px;"><i class="bi bi-x-lg"></i></button>' : ''}
                    </div>
                `;
            const rb = tr.querySelector('.remove-row');
            if (rb) rb.addEventListener('click', () => tr.remove());
            bindIngredientChange(tr);
            return tr;
        }

        document.querySelectorAll('.btn-edit-version').forEach(btn => {
            btn.addEventListener('click', function () {
                const date = this.dataset.date;
                const storeIds = JSON.parse(this.dataset.stores || '[]');
                const items = JSON.parse(this.dataset.items || '[]');
                if (!items.length) return;

                // Set tanggal berlaku
                const dateInput = document.querySelector('input[name="effective_from"]');
                if (dateInput) dateInput.value = date;

                // Set ceklis toko
                document.querySelectorAll('.store-checkbox').forEach(cb => {
                    cb.checked = storeIds.map(String).includes(cb.value);
                });

                // Clear baris lama, isi ulang dari data
                const tbody = document.getElementById('recipeRows');
                tbody.innerHTML = '';
                rowIdx = 0;
                items.forEach(it => {
                    tbody.appendChild(buildRecipeRow(rowIdx, it));
                    rowIdx++;
                });

                // Scroll dan visual feedback
                const formCard = document.getElementById('recipeFormCard');
                formCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
                formCard.style.transition = 'box-shadow .4s ease, transform .4s ease';
                formCard.style.transform = 'translateY(-4px)';
                formCard.style.boxShadow = '0 0 0 4px rgba(37, 99, 235, 0.15), 0 20px 40px rgba(0,0,0,0.05)';
                setTimeout(() => {
                    formCard.style.transform = 'translateY(0)';
                    formCard.style.boxShadow = '0 15px 35px rgba(0,0,0,0.02), 0 5px 15px rgba(0,0,0,0.01)';
                }, 1500);
            });
        });
    </script>
@endsection