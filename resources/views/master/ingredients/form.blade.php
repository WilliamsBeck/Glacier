@extends('layouts.app')
@section('title', isset($ingredient) ? 'Edit Bahan' : 'Tambah Bahan')
@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <h4 class="page-title">{{ isset($ingredient) ? 'Edit Bahan: '.$ingredient->name : 'Tambah Bahan Baru' }}</h4>
    <a href="{{ route('master.ingredients.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
</div>

<form id="ingredientForm" method="POST" action="{{ isset($ingredient) ? route('master.ingredients.update', $ingredient) : route('master.ingredients.store') }}">
    @csrf @if(isset($ingredient)) @method('PUT') @endif

    <div class="row g-3">
        {{-- KOLOM KIRI: Info Dasar --}}
        <div class="col-lg-5">
            <div class="card h-100"><div class="card-header fw-semibold">Info Dasar Bahan</div><div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Bahan *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $ingredient->name ?? '') }}" required>
                </div>
<div class="mb-3">
                    <label class="form-label fw-semibold">Tipe *</label>
                    <select name="type" id="typeSelect" class="form-select" required onchange="onTypeChange()">
                        <option value="">— Pilih —</option>
                        <option value="raw"           {{ old('type', $ingredient->type ?? '')==='raw'?'selected':'' }}>Baku</option>
                        <option value="semi_finished" {{ old('type', $ingredient->type ?? '')==='semi_finished'?'selected':'' }}>Setengah Jadi</option>
                    </select>
                </div>
                <div class="mb-3" id="wrapCategory" style="{{ old('type', $ingredient->type ?? '') === 'semi_finished' ? 'display:none' : '' }}">
                    <label class="form-label fw-semibold">Kategori</label>
                    <select name="category" class="form-select">
                        <option value="">— Pilih Kategori —</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->name }}" {{ old('category', $ingredient->category ?? '') === $cat->name ? 'selected' : '' }}>
                            {{ $cat->label }}
                        </option>
                        @endforeach
                    </select>
                    <div class="form-text">
                        <a href="{{ route('master.categories.index') }}" target="_blank" class="text-decoration-none">
                            <i class="bi bi-plus-circle me-1"></i>Kelola kategori
                        </a>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Satuan Dasar *</label>
                    <select name="unit_base" id="unitBase" class="form-select" required>
                        <option value="">— Pilih —</option>
                        <option value="gram" {{ old('unit_base', $ingredient->unit_base ?? '')==='gram'?'selected':'' }}>Gram</option>
                        <option value="pcs"  {{ old('unit_base', $ingredient->unit_base ?? '')==='pcs'?'selected':'' }}>Pcs</option>
                    </select>
                </div>
                <div class="mb-3"><div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="actIng" {{ old('is_active', $ingredient->is_active ?? true)?'checked':'' }}>
                    <label class="form-check-label fw-semibold" for="actIng">Bahan Aktif</label>
                </div></div>
            </div></div>
        </div>

        {{-- KOLOM KANAN: Kemasan / Komposisi --}}
        <div class="col-lg-7">
            {{-- Bagian Kemasan: hanya untuk Raw --}}
            <div id="sectionPackaging" style="{{ (old('type', $ingredient->type ?? '') === 'semi_finished') ? 'display:none' : '' }}">
            <div class="card"><div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                @php
                    $currentUnit = old('unit_base', $ingredient->unit_base ?? '');
                    $unitLabel   = $currentUnit === 'gram' ? 'Gram' : ($currentUnit === 'pcs' ? 'Pcs' : 'Satuan');
                @endphp
                <span>Kemasan (Dus / Pack / <span id="headerUnitLabel">{{ $unitLabel }}</span>)</span>
            </div><div class="card-body">

                {{-- Kemasan yg sudah ada — EDITABLE --}}
                @if(isset($ingredient) && $ingredient->packagings->count())
                <div class="mb-3">
                    <div class="text-muted small fw-semibold mb-2">Kemasan tersimpan (bisa langsung diedit):</div>
                    @foreach($ingredient->packagings as $pack)
                    <div class="existing-packaging-row border rounded p-3 mb-2 position-relative {{ !$pack->is_active ? 'pkg-inactive' : '' }}"
                         id="pkgRow-{{ $pack->id }}"
                         style="background:{{ $pack->is_active ? '#f8f9ff' : '#f3f4f6' }}">
                        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-primary" style="font-size:0.7rem">Kemasan #{{ $loop->iteration }}</span>
                                <span class="badge pkg-status-badge {{ $pack->is_active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}"
                                      id="pkgStatusBadge-{{ $pack->id }}"
                                      style="font-size:0.65rem">
                                    {{ $pack->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input pkg-toggle-active" type="checkbox"
                                           id="pkgToggle-{{ $pack->id }}"
                                           data-packaging-id="{{ $pack->id }}"
                                           {{ $pack->is_active ? 'checked' : '' }}>
                                    <label class="form-check-label small fw-semibold" for="pkgToggle-{{ $pack->id }}"
                                           style="cursor:pointer">Tampilkan di Saldo Stok</label>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2"
                                        onclick="deletePackaging({{ $pack->id }}, this)">
                                    <i class="bi bi-trash"></i> Hapus
                                </button>
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Nama Kemasan</label>
                                <input type="text"
                                       name="existing_packagings[{{ $pack->id }}][packaging_name]"
                                       class="form-control form-control-sm packaging-name-input"
                                       value="{{ $pack->packaging_name }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Supplier</label>
                                <select name="existing_packagings[{{ $pack->id }}][supplier_id]"
                                        class="form-select form-select-sm packaging-supplier-input">
                                    <option value="">— Tidak Ada —</option>
                                    @foreach($suppliers as $s)
                                        <option value="{{ $s->id }}" {{ $pack->supplier_id == $s->id ? 'selected' : '' }}>
                                            {{ $s->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Jumlah Pack per Dus</label>
                                <input type="number"
                                       name="existing_packagings[{{ $pack->id }}][crate_to_pack]"
                                       class="form-control form-control-sm crate-input"
                                       value="{{ $pack->crate_to_pack }}" min="1" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Isi per Pack (<span class="unit-label">{{ ucfirst($ingredient->unit_base) }}</span>)</label>
                                <input type="number"
                                       name="existing_packagings[{{ $pack->id }}][pack_to_base]"
                                       class="form-control form-control-sm pack-input"
                                       value="{{ (int)$pack->pack_to_base }}" step="1" min="1" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Total Isi per Dus</label>
                                <input type="text" class="form-control form-control-sm bg-light total-display"
                                       value="{{ number_format((int)($pack->crate_to_pack * $pack->pack_to_base), 0, ',', '.') }} {{ ucfirst($ingredient->unit_base) }}" readonly>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

                @php $hasExistingPack = isset($ingredient) && $ingredient->packagings->count(); @endphp

                {{-- Form tambah kemasan baru --}}
                <div id="packagingRows">
                    @unless($hasExistingPack)
                    <div class="packaging-row border rounded p-3 mb-2 position-relative">
                        <button type="button" class="btn-close position-absolute top-0 end-0 m-2 remove-row" style="display:none"></button>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Nama Kemasan</label>
                                <input type="text" name="packagings[0][packaging_name]" class="form-control form-control-sm packaging-name-input" placeholder="Contoh: Dus Zhisheng">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Supplier</label>
                                <select name="packagings[0][supplier_id]" class="form-select form-select-sm packaging-supplier-input">
                                    <option value="">— Tidak Ada —</option>
                                    @foreach($suppliers as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Jumlah Pack per Dus</label>
                                <input type="number" name="packagings[0][crate_to_pack]" class="form-control form-control-sm crate-input" min="1" placeholder="Contoh: 12">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Isi per Pack (<span class="unit-label">satuan</span>)</label>
                                <input type="number" name="packagings[0][pack_to_base]" class="form-control form-control-sm pack-input" step="1" min="1" placeholder="Contoh: 500">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Total Isi per Dus</label>
                                <input type="text" class="form-control form-control-sm bg-light total-display" readonly placeholder="Dihitung otomatis">
                            </div>
                        </div>
                    </div>
                    @endunless
                </div>
                <button type="button" id="addPackaging" class="btn btn-outline-secondary btn-sm mt-1">
                    <i class="bi bi-plus-circle me-1"></i>Tambah Kemasan{{ $hasExistingPack ? ' Baru' : '' }}
                </button>
                @unless($hasExistingPack)
                <div class="text-muted small mt-2">Kosongkan jika data kemasan belum tersedia.</div>
                @endunless
            </div></div>
            </div>{{-- end sectionPackaging --}}

            {{-- Bagian Komposisi: hanya untuk Semi Finished --}}
            <div id="sectionComposition" style="{{ (old('type', $ingredient->type ?? '') !== 'semi_finished') ? 'display:none' : '' }}">
            @if(true)
            <div class="card mt-3">
                <div class="card-header fw-semibold">Komposisi Bahan Baku</div>
                <div class="card-body">

                    {{-- Komposisi tersimpan (hanya mode edit) --}}
                    @if(isset($ingredient) && $ingredient->compositions->count())
                    <div class="mb-3">
                        <div class="text-muted small mb-2">Komposisi tersimpan:</div>
                        @foreach($ingredient->compositions as $comp)
                        <div class="d-flex align-items-center gap-2 mb-2 p-2 border rounded bg-light">
                            <div class="flex-grow-1 small">
                                <strong>{{ $comp->child->name }}</strong>
                                <span class="text-muted">
                                    — {{ number_format($comp->qty_needed, 4, ',', '.') }} {{ $comp->child->unit_base }} per 1 {{ $ingredient->unit_base }}
                                </span>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger"
                                onclick="if(confirm('Hapus komposisi ini?')) { document.getElementById('delComp{{ $comp->id }}').click() }">
                                <i class="bi bi-trash"></i>
                            </button>
                            <input type="checkbox" name="delete_compositions[]" value="{{ $comp->id }}"
                                id="delComp{{ $comp->id }}" style="display:none" form="ingredientForm">
                        </div>
                        @endforeach
                        <hr class="my-3">
                    </div>
                    @endif

                    {{-- Form tambah komposisi baru --}}
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">
                            Jika membuat
                            <input type="text" name="total_output" id="totalOutput"
                                class="form-control form-control-sm d-inline-block mx-1 num-fmt"
                                style="width:100px"
                                placeholder="cth: 11.000">
                            <span id="unitLabelComp">{{ $ingredient->unit_base ?? 'gram' }}</span> {{ $ingredient->name ?? 'ini' }}, butuh bahan:
                        </label>
                    </div>

                    {{-- Header label baris (hanya tampil sekali sebagai judul kolom) --}}
                    <div class="row g-2 mb-1 px-1">
                        <div class="col-5"><span class="form-label small fw-semibold mb-0">Bahan Baku</span></div>
                        <div class="col-3"><span class="form-label small fw-semibold mb-0">Qty digunakan</span></div>
                        <div class="col-3"><span class="form-label small fw-semibold mb-0">Per 1 <span id="perUnitHeaderLabel">{{ $ingredient->unit_base ?? 'gram' }}</span></span></div>
                        <div class="col-1"></div>
                    </div>

                    <div id="compositionRows">
                        <div class="composition-row row g-2 mb-2 align-items-center">
                            <div class="col-5">
                                <select name="compositions[0][child_id]" class="form-select form-select-sm child-select">
                                    <option value="">— Pilih Bahan —</option>
                                    @foreach($rawIngredients as $raw)
                                        <option value="{{ $raw->id }}" data-unit="{{ $raw->unit_base }}">{{ $raw->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-3">
                                <div class="input-group input-group-sm">
                                    <input type="text" name="compositions[0][qty_used]" class="form-control qty-used-input num-fmt" placeholder="cth: 3.000">
                                    <span class="input-group-text small unit-label-comp">satuan</span>
                                </div>
                            </div>
                            <div class="col-3">
                                <input type="text" class="form-control form-control-sm bg-light per-unit-display text-muted" readonly placeholder="—">
                            </div>
                            <div class="col-1 d-flex justify-content-center">
                                <button type="button" class="btn btn-outline-danger btn-sm remove-comp-row" style="display:none">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <button type="button" id="addComposition" class="btn btn-outline-secondary btn-sm mt-1">
                        <i class="bi bi-plus-circle me-1"></i>Tambah Bahan Lagi
                    </button>
                    <div class="text-muted small mt-2">Kosongkan jika tidak ada perubahan komposisi.</div>
                </div>
            </div>
            @endif
            </div>{{-- end sectionComposition --}}
        </div>
    </div>

    <div class="mt-3 d-flex gap-2">
        <button type="submit" name="action" value="save" class="btn btn-primary px-4">
            <i class="bi bi-save me-1"></i>Simpan
        </button>
        @unless(isset($ingredient))
        <button type="submit" name="action" value="save_and_new" class="btn btn-outline-primary px-4">
            <i class="bi bi-plus-circle me-1"></i>Simpan &amp; Tambah Lagi
        </button>
        @endunless
    </div>
</form>

<template id="packagingTemplate">
    <div class="packaging-row border rounded p-3 mb-2 position-relative">
        <button type="button" class="btn-close position-absolute top-0 end-0 m-2 remove-row"></button>
        <div class="row g-2">
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Nama Kemasan</label>
                <input type="text" name="" class="form-control form-control-sm packaging-name-input" placeholder="Contoh: Dus Zhisheng">
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Supplier</label>
                <select name="" class="form-select form-select-sm packaging-supplier-input">
                    <option value="">— Tidak Ada —</option>
                    @foreach($suppliers as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Jumlah Pack per Dus</label>
                <input type="number" name="" class="form-control form-control-sm crate-input" min="1" placeholder="Contoh: 12">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Isi per Pack (<span class="unit-label">satuan</span>)</label>
                <input type="number" name="" class="form-control form-control-sm pack-input" step="1" min="1" placeholder="Contoh: 500">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Total Isi per Dus</label>
                <input type="text" class="form-control form-control-sm bg-light total-display" readonly placeholder="Dihitung otomatis">
            </div>
        </div>
    </div>
</template>

<script>
// ── Hapus kemasan via AJAX (hindari nested form) ───────────
function deletePackaging(packId, btn) {
    if (!confirm('Hapus kemasan ini?')) return;
    const row = btn.closest('.existing-packaging-row');
    row.style.opacity = '0.5';
    btn.disabled = true;

    fetch('{{ url("master/packagings") }}/' + packId, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: '_method=DELETE'
    })
    .then(function(r) {
        if (r.ok || r.redirected) {
            row.remove();
        } else {
            row.style.opacity = '';
            btn.disabled = false;
            alert('Gagal menghapus kemasan.');
        }
    })
    .catch(function() {
        row.style.opacity = '';
        btn.disabled = false;
        alert('Gagal menghapus kemasan.');
    });
}

// ── Toggle aktif/nonaktif kemasan via AJAX ───────────────────
document.addEventListener('change', function(e) {
    const toggle = e.target.closest('.pkg-toggle-active');
    if (!toggle) return;

    const packId  = toggle.dataset.packagingId;
    const row     = document.getElementById('pkgRow-' + packId);
    const badge   = document.getElementById('pkgStatusBadge-' + packId);
    toggle.disabled = true;

    fetch('{{ url("master/packagings") }}/' + packId + '/toggle-active', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
    })
    .then(function(r) { return r.ok ? r.json() : Promise.reject(r); })
    .then(function(res) {
        toggle.disabled = false;
        toggle.checked  = res.is_active;
        if (badge) {
            badge.textContent = res.is_active ? 'Aktif' : 'Nonaktif';
            badge.className = 'badge pkg-status-badge ' +
                (res.is_active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary');
            badge.style.fontSize = '0.65rem';
        }
        if (row) {
            row.style.background = res.is_active ? '#f8f9ff' : '#f3f4f6';
            row.classList.toggle('pkg-inactive', !res.is_active);
        }
    })
    .catch(function() {
        toggle.disabled = false;
        toggle.checked  = !toggle.checked; // revert
        alert('Gagal mengubah status kemasan.');
    });
});

// ── Komposisi ──────────────────────────────────────────────
let compIdx = 1;

function updateCompUnitLabel(row) {
    const sel  = row.querySelector('.child-select');
    const lbl  = row.querySelector('.unit-label-comp');
    if (!sel || !lbl) return;
    const opt  = sel.options[sel.selectedIndex];
    const unit = opt?.dataset?.unit || 'satuan';
    lbl.textContent = unit;
    recalcPerUnit(row);
}

function recalcPerUnit(row) {
    const disp       = row.querySelector('.per-unit-display');
    if (!disp) return;
    const totalOutput = NumberFmt.parse(document.getElementById('totalOutput')?.value || '0');
    const qtyUsed     = NumberFmt.parse(row.querySelector('.qty-used-input')?.value || '0');
    const sel         = row.querySelector('.child-select');
    const opt         = sel?.options[sel.selectedIndex];
    const unit        = opt?.dataset?.unit || '';
    if (totalOutput > 0 && qtyUsed > 0) {
        const perUnit = qtyUsed / totalOutput;
        // Tampilkan 4 desimal, hilangkan trailing zeros
        disp.value = parseFloat(perUnit.toFixed(6)) + (unit ? ' ' + unit : '');
    } else {
        disp.value = '';
    }
}

function recalcAllComps() {
    document.querySelectorAll('.composition-row').forEach(row => {
        updateCompUnitLabel(row);
        recalcPerUnit(row);
    });
}

function updateCompRemoveButtons() {
    const rows = document.querySelectorAll('#compositionRows .composition-row');
    rows.forEach(function(r) {
        const btn = r.querySelector('.remove-comp-row');
        if (btn) btn.style.display = rows.length > 1 ? '' : 'none';
    });
}

function attachCompRowEvents(row, idx) {
    const sel   = row.querySelector('.child-select');
    const qty   = row.querySelector('.qty-used-input');
    if (sel) {
        sel.name = `compositions[${idx}][child_id]`;
        sel.addEventListener('change', () => updateCompUnitLabel(row));
    }
    if (qty) {
        qty.name = `compositions[${idx}][qty_used]`;
        qty.addEventListener('input', () => recalcPerUnit(row));
    }
    const removeBtn = row.querySelector('.remove-comp-row');
    if (removeBtn) {
        removeBtn.addEventListener('click', () => {
            row.remove();
            updateCompRemoveButtons();
        });
    }
}

const rawIngredients = @json($rawIngredients);

function buildCompRowHTML(idx) {
    let opts = '<option value="">— Pilih Bahan —</option>';
    rawIngredients.forEach(r => { opts += `<option value="${r.id}" data-unit="${r.unit_base}">${r.name}</option>`; });
    return `<div class="composition-row row g-2 mb-2 align-items-center">
        <div class="col-5">
            <select name="compositions[${idx}][child_id]" class="form-select form-select-sm child-select">${opts}</select>
        </div>
        <div class="col-3">
            <div class="input-group input-group-sm">
                <input type="text" name="compositions[${idx}][qty_used]" class="form-control qty-used-input num-fmt" placeholder="cth: 3.000">
                <span class="input-group-text small unit-label-comp">satuan</span>
            </div>
        </div>
        <div class="col-3">
            <input type="text" class="form-control form-control-sm bg-light per-unit-display text-muted" readonly placeholder="—">
        </div>
        <div class="col-1 d-flex justify-content-center">
            <button type="button" class="btn btn-outline-danger btn-sm remove-comp-row"><i class="bi bi-x"></i></button>
        </div>
    </div>`;
}

// ── Packaging ──────────────────────────────────────────────
let packIdx = 1;

function getUnit() {
    return document.getElementById('unitBase').value || 'satuan';
}

function getUnitLabel() {
    const v = document.getElementById('unitBase').value;
    if (v === 'gram') return 'Gram';
    if (v === 'pcs')  return 'Pcs';
    return 'Satuan';
}

function updateUnitLabels() {
    const unit  = getUnit();
    const label = getUnitLabel();
    document.querySelectorAll('.unit-label').forEach(el => el.textContent = label);
    const header = document.getElementById('headerUnitLabel');
    if (header) header.textContent = label;
    recalcAll();
}

function recalcRow(row) {
    const crate = parseFloat(row.querySelector('.crate-input').value) || 0;
    const pack  = parseFloat(row.querySelector('.pack-input').value)  || 0;
    const total = crate * pack;
    const label = getUnitLabel();
    const disp  = row.querySelector('.total-display');
    if (total > 0) {
        disp.value = NumberFmt.format(total) + ' ' + label;
    } else {
        disp.value = '';
    }
}

function recalcAll() {
    document.querySelectorAll('.packaging-row, .existing-packaging-row').forEach(recalcRow);
}

function attachRowEvents(row, idx) {
    row.querySelector('.packaging-name-input').name     = `packagings[${idx}][packaging_name]`;
    row.querySelector('.packaging-supplier-input').name = `packagings[${idx}][supplier_id]`;
    row.querySelector('.crate-input').name              = `packagings[${idx}][crate_to_pack]`;
    row.querySelector('.pack-input').name               = `packagings[${idx}][pack_to_base]`;

    row.querySelector('.crate-input').addEventListener('input', () => recalcRow(row));
    row.querySelector('.pack-input').addEventListener('input',  () => recalcRow(row));

    const removeBtn = row.querySelector('.remove-row');
    if (removeBtn) {
        removeBtn.style.display = '';   // pastikan X terlihat (row pertama hidden via CSS, row baru langsung tampil)
        removeBtn.addEventListener('click', function () {
            row.remove();
            updateRemoveButtons();
        });
    }
}

function updateRemoveButtons() {
    const rows = document.querySelectorAll('#packagingRows .packaging-row');
    rows.forEach(function (r, i) {
        const btn = r.querySelector('.remove-row');
        if (btn) btn.style.display = rows.length > 1 ? '' : 'none';
    });
}

document.addEventListener('DOMContentLoaded', function () {
    // Existing packagings: attach recalc events (tanpa rename karena field name sudah fix)
    document.querySelectorAll('.existing-packaging-row').forEach(function(row) {
        row.querySelector('.crate-input')?.addEventListener('input', () => recalcRow(row));
        row.querySelector('.pack-input')?.addEventListener('input',  () => recalcRow(row));
    });

    // Packaging baru: attach events ke row pertama
    const firstPackRow = document.querySelector('#packagingRows .packaging-row');
    if (firstPackRow) attachRowEvents(firstPackRow, 0);
    updateRemoveButtons();

    // Toggle kemasan vs komposisi vs kategori berdasarkan tipe
    function toggleSections() {
        const tipe = document.querySelector('[name="type"]').value;
        const isSemi = tipe === 'semi_finished';
        document.getElementById('sectionPackaging').style.display   = isSemi ? 'none' : '';
        document.getElementById('sectionComposition').style.display = isSemi ? '' : 'none';
        document.getElementById('wrapCategory').style.display       = isSemi ? 'none' : '';
    }
    function onTypeChange() { toggleSections(); }
    document.querySelector('[name="type"]').addEventListener('change', toggleSections);
    toggleSections();

    document.getElementById('unitBase').addEventListener('change', function() {
        updateUnitLabels();
        // Update "Per 1 X" header label
        const v = this.value;
        const lbl = v === 'gram' ? 'gram' : (v === 'pcs' ? 'pcs' : 'satuan');
        const hdr = document.getElementById('perUnitHeaderLabel');
        if (hdr) hdr.textContent = lbl;
    });

    // total_output berubah → recalc semua per-unit
    document.getElementById('totalOutput')?.addEventListener('input', () => {
        document.querySelectorAll('#compositionRows .composition-row').forEach(recalcPerUnit);
    });

    document.getElementById('addPackaging').addEventListener('click', function () {
        const tmpl      = document.getElementById('packagingTemplate');
        const container = document.getElementById('packagingRows');
        container.appendChild(tmpl.content.cloneNode(true));
        // Ambil row dari DOM setelah append — lebih reliable dari reference fragment
        const allRows = container.querySelectorAll('.packaging-row');
        const row     = allRows[allRows.length - 1];
        attachRowEvents(row, packIdx++);
        updateUnitLabels();
        // Tampilkan tombol hapus di semua row jika sudah > 1
        updateRemoveButtons();
    });

    updateUnitLabels();

    // Komposisi: attach events ke row pertama
    const firstCompRow = document.querySelector('.composition-row');
    if (firstCompRow) {
        attachCompRowEvents(firstCompRow, 0);
        updateCompUnitLabel(firstCompRow);
        updateCompRemoveButtons();
    }

    const addCompBtn = document.getElementById('addComposition');
    if (addCompBtn) {
        addCompBtn.addEventListener('click', function () {
            const container = document.getElementById('compositionRows');
            const div = document.createElement('div');
            div.innerHTML = buildCompRowHTML(compIdx);
            const row = div.firstElementChild;
            container.appendChild(row);
            attachCompRowEvents(row, compIdx++);
            updateCompRemoveButtons();
        });
    }
});
</script>

<style>
.pkg-inactive {
    opacity: .65;
}
.pkg-inactive::before {
    content: '⏸ NONAKTIF';
    position: absolute;
    top: 8px;
    left: 50%;
    transform: translateX(-50%);
    font-size: .6rem;
    font-weight: 700;
    color: #6b7280;
    letter-spacing: 1px;
    pointer-events: none;
}
.pkg-inactive input,
.pkg-inactive select {
    background: #fafbfc !important;
}
</style>
@endsection
