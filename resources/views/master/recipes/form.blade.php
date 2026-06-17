@extends('layouts.app')
@section('title','Buat Resep')
@section('content')
@php
    $isDuplicate  = isset($sourceItems) && $sourceItems->isNotEmpty();
    $sourceMenu   = $isDuplicate ? $recipe->menu_id : old('menu_id');
    $sourceDate   = $isDuplicate ? $recipe->effective_from->format('Y-m-d') : null;
@endphp
<div class="page-header d-flex justify-content-between align-items-center">
    <h4 class="page-title">{{ $isDuplicate ? 'Duplikat Resep' : 'Buat Resep Baru' }}</h4>
    <a href="{{ route('master.recipes.index') }}" class="btn btn-outline-secondary btn-sm btn-back"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
</div>

@if($isDuplicate)
<div class="alert alert-warning d-flex align-items-center gap-2">
    <i class="bi bi-copy fs-5"></i>
    <div>
        Menduplikat resep <strong>{{ $recipe->menu->name }}</strong> berlaku
        <strong>{{ $recipe->effective_from->format('d') . ' ' . ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agt','Sep','Okt','Nov','Des'][(int)$recipe->effective_from->format('n')] . ' ' . $recipe->effective_from->format('Y') }}</strong>.
        Ubah tanggal berlaku, lalu simpan sebagai versi baru.
    </div>
</div>
@else
<div class="alert alert-info"><i class="bi bi-info-circle me-1"></i>Resep lama tetap tersimpan saat Anda buat versi baru. HPP periode lalu akan tetap pakai resep yang berlaku saat itu.</div>
@endif

<form method="POST" action="{{ route('master.recipes.store') }}">
    @csrf
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card"><div class="card-header fw-semibold">Info Resep</div><div class="card-body">
                <div class="mb-3"><label class="form-label fw-semibold">Menu *</label>
                    <select name="menu_id" class="form-select" required>
                        <option value="">— Pilih Menu —</option>
                        @foreach($menus as $m)
                            <option value="{{ $m->id }}" {{ $sourceMenu == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3"><label class="form-label fw-semibold">Berlaku untuk Toko</label>
                    <div class="border rounded p-2 d-flex flex-wrap gap-2" style="max-height:150px;overflow:auto">
                        @foreach($stores ?? [] as $s)
                            <label class="form-check m-0">
                                <input class="form-check-input" type="checkbox" name="store_ids[]" value="{{ $s->id }}">
                                <span class="form-check-label small">{{ $s->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    <div class="form-text small">Kosongkan = berlaku <strong>semua toko</strong> (default). Centang beberapa toko kalau resep beda untuk mereka.</div>
                </div>
                <div class="mb-3"><label class="form-label fw-semibold">Berlaku Sejak *</label>
                    <input type="date" name="effective_from" class="form-control"
                           value="{{ old('effective_from', date('Y-m-d')) }}" required>
                </div>
                @if($isDuplicate)
                <div class="form-text text-warning">
                    <i class="bi bi-exclamation-triangle me-1"></i>Pastikan tanggal berlaku sudah diubah dari versi aslinya ({{ $sourceDate }}).
                </div>
                @endif
            </div></div>
        </div>
        <div class="col-lg-8">
            <div class="card"><div class="card-header d-flex justify-content-between fw-semibold">
                <span>Komposisi Bahan per 1 Pcs Menu</span>
                <button type="button" class="btn btn-sm btn-success" onclick="addRecipeRow()"><i class="bi bi-plus-circle me-1"></i>Tambah</button>
            </div>
            <div class="card-body" id="recipeContainer">

                @if($isDuplicate)
                    {{-- Pre-fill dari resep sumber --}}
                    @foreach($sourceItems as $idx => $src)
                    <div class="item-row {{ $idx > 0 ? 'mt-2' : '' }}" id="rrow-{{ $idx }}">
                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row"
                                onclick="removeRecipeRow({{ $idx }})" style="{{ $sourceItems->count() <= 1 ? 'display:none' : '' }}">×</button>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Bahan *</label>
                                <select name="items[{{ $idx }}][ingredient_id]" class="form-select form-select-sm" required>
                                    <option value="">— Pilih Bahan —</option>
                                    @foreach($ingredients as $i)
                                        <option value="{{ $i->id }}" data-unit="{{ $i->unit_base }}"
                                            {{ $i->id == $src->ingredient_id ? 'selected' : '' }}>{{ $i->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-3">
                                <label class="form-label small fw-semibold">Qty *</label>
                                <input type="number" name="items[{{ $idx }}][qty_usage]"
                                       class="form-control form-control-sm" step="0.01" min="0.01"
                                       value="{{ $src->qty_usage }}" required>
                            </div>
                            <div class="col-3">
                                <label class="form-label small fw-semibold">Satuan</label>
                                <input type="text" name="items[{{ $idx }}][unit]"
                                       class="form-control form-control-sm bg-light text-center unit-display"
                                       readonly value="{{ $src->unit }}">
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    {{-- Form kosong standar --}}
                    <div class="item-row" id="rrow-0">
                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" onclick="removeRecipeRow(0)" style="display:none">×</button>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Bahan *</label>
                                <select name="items[0][ingredient_id]" class="form-select form-select-sm" required>
                                    <option value="">— Pilih Bahan —</option>
                                    @foreach($ingredients as $i)<option value="{{ $i->id }}" data-unit="{{ $i->unit_base }}">{{ $i->name }}</option>@endforeach
                                </select>
                            </div>
                            <div class="col-3"><label class="form-label small fw-semibold">Qty *</label>
                                <input type="number" name="items[0][qty_usage]" class="form-control form-control-sm" step="0.01" min="0.01" required>
                            </div>
                            <div class="col-3"><label class="form-label small fw-semibold">Satuan</label>
                                <input type="text" name="items[0][unit]" class="form-control form-control-sm bg-light text-center unit-display" readonly placeholder="—">
                            </div>
                        </div>
                    </div>
                @endif

            </div></div>
            <div class="mt-3"><button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i>Simpan Resep</button></div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
var rRowCount = {{ isset($sourceItems) ? $sourceItems->count() : 1 }};
var ings = @json($ingredients->map(fn($i)=>['id'=>$i->id,'name'=>$i->name,'unit'=>$i->unit_base]));

function addRecipeRow() {
    var idx = rRowCount++;
    var opts = ings.map(function(i){ return '<option value="'+i.id+'" data-unit="'+i.unit+'">'+i.name+'</option>';}).join('');
    var html = '<div class="item-row mt-2" id="rrow-'+idx+'">' +
        '<button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" onclick="removeRecipeRow('+idx+')">×</button>' +
        '<div class="row g-2">' +
        '<div class="col-6"><label class="form-label small fw-semibold">Bahan *</label><select name="items['+idx+'][ingredient_id]" class="form-select form-select-sm" required><option value="">— Pilih Bahan —</option>'+opts+'</select></div>' +
        '<div class="col-3"><label class="form-label small fw-semibold">Qty *</label><input type="number" name="items['+idx+'][qty_usage]" class="form-control form-control-sm" step="0.01" min="0.01" required></div>' +
        '<div class="col-3"><label class="form-label small fw-semibold">Satuan</label><input type="text" name="items['+idx+'][unit]" class="form-control form-control-sm bg-light text-center unit-display" readonly placeholder="—"></div>' +
        '</div></div>';
    document.getElementById('recipeContainer').insertAdjacentHTML('beforeend', html);
    bindUnitChange(document.getElementById('rrow-'+idx));
    updateRecipeBtns();
}

function bindUnitChange(row) {
    var ingSel  = row.querySelector('select[name$="[ingredient_id]"]');
    var unitInp = row.querySelector('input[name$="[unit]"]');
    if (ingSel && unitInp) {
        ingSel.addEventListener('change', function() {
            var opt = this.options[this.selectedIndex];
            unitInp.value = (opt && opt.dataset.unit) ? opt.dataset.unit : '';
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('#recipeContainer .item-row').forEach(bindUnitChange);
    updateRecipeBtns();
});

function removeRecipeRow(idx) { document.getElementById('rrow-'+idx).remove(); updateRecipeBtns(); }
function updateRecipeBtns() {
    var rows = document.querySelectorAll('.item-row');
    rows.forEach(function(r) {
        var b = r.querySelector('.btn-remove-row');
        if (b) b.style.display = rows.length > 1 ? 'block' : 'none';
    });
}
</script>
@endpush
