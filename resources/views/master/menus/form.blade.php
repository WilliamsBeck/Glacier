@extends('layouts.app')
@section('title', isset($menu) ? 'Edit Menu' : 'Tambah Menu')
@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <h4 class="page-title">{{ isset($menu) ? 'Edit Menu: '.$menu->name : 'Tambah Menu Baru' }}</h4>
    <a href="{{ route('master.menus.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
</div>

{{-- =====================================================================
     RESEP TERSIMPAN — di LUAR form utama agar tidak ada nested form
     HTML tidak mendukung nested forms; browser akan overwrite _method
     ===================================================================== --}}
@php
    $bulanID = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agt','Sep','Okt','Nov','Des'];
    $formatID = function($date) use ($bulanID) {
        $c = \Carbon\Carbon::parse($date);
        return $c->format('d').' '.$bulanID[(int)$c->format('n')].' '.$c->format('Y');
    };
@endphp
@if(isset($menu) && isset($recipes) && $recipes->count())
<div class="card mb-3">
    <div class="card-header fw-semibold"><i class="bi bi-journal-check me-1"></i>Resep Tersimpan</div>
    <div class="card-body p-0">
        @foreach($recipes as $groupId => $items)
        @php
            $first      = $items->first();
            $date       = $first->effective_from->toDateString();
            // Daftar toko untuk versi ini (1 ingredient × N toko = N rows; ambil distinct)
            $vStoreIds  = $items->pluck('store_id')->unique()->values();
            $isDefault  = $vStoreIds->contains(null);
            $vStoreNames= $items->pluck('store.name')->filter()->unique()->values();
            // Ambil 1 set ingredients (semua toko share set yang sama dalam 1 group)
            $uniqueItems = $items->unique('ingredient_id')->values();
            $itemsData   = $uniqueItems->map(fn($it) => [
                'ingredient_id' => $it->ingredient_id,
                'qty_usage'     => $it->qty_usage,
                'unit'          => $it->unit,
            ])->values();
            $storeIdsForJs = $isDefault ? [] : $vStoreIds->filter()->values()->all();
        @endphp
        <div class="p-3 border-bottom">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <span class="badge bg-primary">Berlaku: {{ $formatID($date) }}</span>
                    @if($isDefault)
                        <span class="badge bg-success-subtle text-success-emphasis">Default (semua toko)</span>
                    @else
                        @foreach($vStoreNames as $sn)
                            <span class="badge bg-info-subtle text-info-emphasis"><i class="bi bi-shop me-1"></i>{{ $sn }}</span>
                        @endforeach
                    @endif
                </div>
                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-sm btn-outline-primary btn-edit-version"
                            data-date="{{ $date }}"
                            data-stores='@json($storeIdsForJs)'
                            data-items='@json($itemsData)'>
                        <i class="bi bi-pencil me-1"></i>Edit
                    </button>
                    <form method="POST"
                          action="{{ route('master.menus.recipe-version.destroy', [$menu, $groupId]) }}"
                          onsubmit="return confirm('Hapus versi resep {{ $formatID($date) }}?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-trash me-1"></i>Hapus
                        </button>
                    </form>
                </div>
            </div>
            <table class="table table-sm mb-0">
                <thead class="table-light">
                    <tr><th>Bahan</th><th class="text-end">Qty</th><th>Satuan</th></tr>
                </thead>
                <tbody>
                    @foreach($uniqueItems as $item)
                    <tr>
                        <td>{{ $item->ingredient->name }}</td>
                        <td class="text-end">{{ number_format($item->qty_usage, 0, ',', '.') }}</td>
                        <td>{{ $item->unit }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- =====================================================================
     FORM UTAMA — tidak ada nested form di sini
     ===================================================================== --}}
<form id="menuForm" method="POST" action="{{ isset($menu) ? route('master.menus.update', $menu) : route('master.menus.store') }}">
    @csrf @if(isset($menu)) @method('PUT') @endif

    <div class="row g-3">
        {{-- KIRI: Info Menu --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header fw-semibold">Info Menu</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Menu *</label>
                        <input type="text" name="name" class="form-control"
                               value="{{ old('name', $menu->name ?? '') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kategori</label>
                        <select name="category_id" class="form-select">
                            <option value="">— Pilih Kategori —</option>
                            @foreach($menuCategories as $mc)
                                <option value="{{ $mc->id }}"
                                    {{ old('category_id', $menu->category_id ?? '') == $mc->id ? 'selected' : '' }}>
                                    {{ $mc->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">
                            <a href="{{ route('master.categories.index') }}#menu" target="_blank" class="text-decoration-none">
                                <i class="bi bi-plus-circle me-1"></i>Kelola kategori menu
                            </a>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active"
                                   value="1" id="actMenu"
                                   {{ old('is_active', $menu->is_active ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="actMenu">Menu Aktif</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-save me-1"></i>Simpan
                    </button>
                </div>
            </div>
        </div>

        {{-- KANAN: Input Resep Baru --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header fw-semibold">
                    {{ isset($menu) ? 'Tambah / Update Versi Resep' : 'Resep Menu' }}
                </div>
                <div class="card-body">
                    <div class="mb-3 row g-2 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Tanggal Berlaku Resep *</label>
                            <input type="date" name="effective_from" class="form-control"
                                   value="{{ old('effective_from', now()->toDateString()) }}" required>
                            <div class="form-text">Jika tanggal & toko sama dengan versi lama, versi lama akan diganti.</div>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label fw-semibold">Berlaku untuk Toko</label>
                            <div class="border rounded p-2 d-flex flex-wrap gap-2" style="max-height:120px;overflow:auto">
                                @foreach($stores ?? [] as $s)
                                    <label class="form-check m-0">
                                        <input class="form-check-input store-checkbox" type="checkbox" name="store_ids[]" value="{{ $s->id }}">
                                        <span class="form-check-label small">{{ $s->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <div class="form-text small">Kosongkan = berlaku <strong>semua toko</strong> (default). Centang beberapa toko kalau resep beda untuk mereka.</div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm" id="recipeTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width:260px">Bahan</th>
                                    <th style="min-width:120px">Qty</th>
                                    <th style="min-width:100px">Satuan</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="recipeRows">
                                <tr class="recipe-row">
                                    <td>
                                        <select name="items[0][ingredient_id]" class="form-select form-select-sm">
                                            <option value="">— Pilih Bahan —</option>
                                            @foreach($ingredients as $ing)
                                            <option value="{{ $ing->id }}" data-unit="{{ $ing->unit_base }}">{{ $ing->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="number" name="items[0][qty_usage]" class="form-control form-control-sm" step="1" min="1" placeholder="0"></td>
                                    <td>
                                        <input type="text" name="items[0][unit]" class="form-control form-control-sm bg-light text-center unit-display" readonly placeholder="—">
                                    </td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" id="addRow" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i>Tambah Bahan
                    </button>
                    <div class="text-muted small mt-2">Kosongkan semua bahan jika tidak ingin mengubah resep.</div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
let rowIdx = 1;
const ingredientOptions = @json($ingredients->map(fn($i) => ['id' => $i->id, 'label' => $i->name, 'unit' => $i->unit_base]));

// Map id → unit_base untuk lookup cepat
const unitMap = {};
ingredientOptions.forEach(i => { unitMap[i.id] = i.unit; });

// Saat pilih bahan, unit otomatis sesuai unit_base bahan tersebut
function bindIngredientChange(row) {
    const ingSelect  = row.querySelector('select[name$="[ingredient_id]"]');
    const unitInput  = row.querySelector('[name$="[unit]"]');
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

    const tr = document.createElement('tr');
    tr.className = 'recipe-row';
    tr.innerHTML = `
        <td><select name="items[${rowIdx}][ingredient_id]" class="form-select form-select-sm">${opts}</select></td>
        <td><input type="number" name="items[${rowIdx}][qty_usage]" class="form-control form-control-sm" step="1" min="1" placeholder="0"></td>
        <td><input type="text" name="items[${rowIdx}][unit]" class="form-control form-control-sm bg-light text-center unit-display" readonly placeholder="—"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-x"></i></button></td>
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
        opts += `<option value="${i.id}" ${sel}>${i.label}</option>`;
    });
    const tr = document.createElement('tr');
    tr.className = 'recipe-row';
    tr.innerHTML = `
        <td><select name="items[${idx}][ingredient_id]" class="form-select form-select-sm">${opts}</select></td>
        <td><input type="number" name="items[${idx}][qty_usage]" class="form-control form-control-sm" step="1" min="1" value="${data?.qty_usage ?? ''}" placeholder="0"></td>
        <td><input type="text" name="items[${idx}][unit]" class="form-control form-control-sm bg-light text-center unit-display" readonly value="${data?.unit ?? ''}" placeholder="—"></td>
        <td>${idx > 0 ? '<button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-x"></i></button>' : ''}</td>
    `;
    const rb = tr.querySelector('.remove-row');
    if (rb) rb.addEventListener('click', () => tr.remove());
    bindIngredientChange(tr);
    return tr;
}

document.querySelectorAll('.btn-edit-version').forEach(btn => {
    btn.addEventListener('click', function() {
        const date     = this.dataset.date;
        const storeIds = JSON.parse(this.dataset.stores || '[]');
        const items    = JSON.parse(this.dataset.items || '[]');
        if (!items.length) return;

        // Set tanggal berlaku
        const dateInput = document.querySelector('input[name="effective_from"]');
        if (dateInput) dateInput.value = date;
        // Set ceklis toko (kosong = default semua toko)
        document.querySelectorAll('.store-checkbox').forEach(cb => {
            cb.checked = storeIds.map(String).includes(cb.value);
        });

        // Clear semua baris lama, isi ulang dari data versi
        const tbody = document.getElementById('recipeRows');
        tbody.innerHTML = '';
        rowIdx = 0;
        items.forEach(it => {
            tbody.appendChild(buildRecipeRow(rowIdx, it));
            rowIdx++;
        });

        // Scroll ke form bawah + visual feedback
        const formCard = dateInput.closest('.card');
        formCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
        formCard.style.transition = 'box-shadow .3s';
        formCard.style.boxShadow = '0 0 0 3px rgba(13,110,253,.35)';
        setTimeout(() => formCard.style.boxShadow = '', 1500);
    });
});
</script>
@endsection
