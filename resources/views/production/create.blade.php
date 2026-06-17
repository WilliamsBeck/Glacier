@extends('layouts.app')
@section('title','Input Produksi')
@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <h4 class="page-title">Input Produksi Bahan Setengah Jadi</h4>
    <a href="{{ route('production.logs.index') }}" class="btn btn-outline-secondary btn-sm btn-back">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

<form method="POST" action="{{ route('production.logs.store') }}" id="prodForm">
    @csrf

    {{-- ── Detail umum ──────────────────────────────────────────────────── --}}
    <div class="card mb-3">
        <div class="card-header fw-semibold">Detail Produksi</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Toko <span class="text-danger">*</span></label>
                    <select name="store_id" class="form-select" required>
                        <option value="">— Pilih Toko —</option>
                        @foreach($stores as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Tanggal Produksi <span class="text-danger">*</span></label>
                    <input type="date" name="production_date" class="form-control"
                           value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Catatan</label>
                    <input type="text" name="notes" class="form-control" placeholder="Opsional">
                </div>
            </div>
        </div>
    </div>

    {{-- ── Daftar produksi (multi-baris) ────────────────────────────────── --}}
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Daftar Bahan Diproduksi</span>
            <button type="button" class="btn btn-sm btn-success" id="addRowBtn">
                <i class="bi bi-plus-circle me-1"></i>Tambah Baris
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0" id="prodTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40%">Bahan Setengah Jadi <span class="text-danger">*</span></th>
                            <th style="width:20%">Qty Diproduksi <span class="text-danger">*</span></th>
                            <th>Bahan baku yang dikonsumsi</th>
                            <th style="width:50px"></th>
                        </tr>
                    </thead>
                    <tbody id="rowsBody">
                        {{-- Rows ditambah lewat JS --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary" id="submitBtn">
        <i class="bi bi-gear me-1"></i> Proses Semua Produksi
    </button>
</form>

{{-- ═══════════ Template baris (di-clone via JS) ═══════════ --}}
<template id="rowTpl">
    <tr class="prod-row">
        <td>
            <select name="items[__INDEX__][semi_finished_id]" class="form-select form-select-sm semi-select" required>
                <option value="">— Pilih Bahan —</option>
                @foreach($semiFinished as $sf)
                    <option value="{{ $sf->id }}"
                        data-compositions='@json($sf->compositions->map(fn($c)=>["name"=>$c->child->name,"qty"=>$c->qty_needed,"unit"=>$c->child->unit_base]))'
                        data-unit="{{ $sf->unit_base }}">
                        {{ $sf->name }}
                    </option>
                @endforeach
            </select>
        </td>
        <td>
            <div class="input-group input-group-sm">
                <input type="number" name="items[__INDEX__][qty_produced]" class="form-control qty-input"
                       step="0.01" min="0.01" required placeholder="0">
                <span class="input-group-text unit-label text-muted" style="min-width:2.5rem;justify-content:center"></span>
            </div>
        </td>
        <td>
            <div class="preview small text-muted">Pilih bahan & isi qty untuk lihat konsumsi</div>
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger remove-row">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>
</template>

@endsection

@push('scripts')
<script>
let rowIndex = 0;
const tpl     = document.getElementById('rowTpl');
const tbody   = document.getElementById('rowsBody');

function addRow() {
    const html = tpl.innerHTML.replace(/__INDEX__/g, rowIndex++);
    tbody.insertAdjacentHTML('beforeend', html);
}

function fmtQty(val, unit) {
    return (unit || '').toLowerCase() === 'gram'
        ? Math.round(val).toLocaleString('id-ID')
        : val.toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2});
}

function updatePreview(tr) {
    const sel    = tr.querySelector('.semi-select');
    const qtyInp = tr.querySelector('.qty-input');
    const qty    = parseFloat(qtyInp.value) || 0;
    const prev   = tr.querySelector('.preview');
    const unitLb = tr.querySelector('.unit-label');
    const opt    = sel.options[sel.selectedIndex];

    if (!opt || !opt.value) {
        prev.innerHTML = '<span class="text-muted">Pilih bahan & isi qty untuk lihat konsumsi</span>';
        unitLb.textContent = '';
        qtyInp.step = '0.01'; qtyInp.min = '0.01';
        return;
    }

    const sfUnit = opt.dataset.unit || '';
    unitLb.textContent = sfUnit;

    // Step input: bulat untuk gram
    const sfIsGram = sfUnit.toLowerCase() === 'gram';
    qtyInp.step = sfIsGram ? '1' : '0.01';
    qtyInp.min  = sfIsGram ? '1' : '0.01';

    const comps = opt.dataset.compositions ? JSON.parse(opt.dataset.compositions) : [];

    if (!comps.length) {
        prev.innerHTML = '<span class="text-warning">⚠ Bahan ini belum punya komposisi</span>';
        return;
    }

    if (qty <= 0) {
        prev.innerHTML = comps.map(c => `<span class="text-muted small">${c.name}</span>`).join(', ');
        return;
    }

    prev.innerHTML = comps.map(c =>
        `<span class="badge bg-light text-dark border me-1">${c.name}: <strong>${fmtQty(c.qty * qty, c.unit)} ${c.unit}</strong></span>`
    ).join('');
}

// Event delegation
tbody.addEventListener('change', e => {
    if (e.target.classList.contains('semi-select')) updatePreview(e.target.closest('tr'));
});
tbody.addEventListener('input', e => {
    if (e.target.classList.contains('qty-input')) updatePreview(e.target.closest('tr'));
});
tbody.addEventListener('click', e => {
    if (e.target.closest('.remove-row')) {
        if (tbody.querySelectorAll('tr').length <= 1) {
            alert('Minimal 1 baris.');
            return;
        }
        e.target.closest('tr').remove();
    }
});

document.getElementById('addRowBtn').addEventListener('click', addRow);

document.getElementById('prodForm').addEventListener('submit', e => {
    const rows = tbody.querySelectorAll('tr');
    if (rows.length === 0) {
        e.preventDefault();
        alert('Tambah minimal 1 bahan untuk diproduksi.');
        return;
    }
    if (!confirm(`Konfirmasi catat produksi ${rows.length} bahan?`)) {
        e.preventDefault();
    }
});

// Init: 1 baris default
addRow();
</script>
@endpush
