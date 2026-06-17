@extends('layouts.app')
@section('title', isset($packaging)?'Edit Kemasan':'Tambah Kemasan')
@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <h4 class="page-title">{{ isset($packaging) ? 'Edit Kemasan' : 'Tambah Kemasan' }}</h4>
    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm btn-back"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
</div>
<div class="card" style="max-width:600px"><div class="card-body">
    <form method="POST" action="{{ isset($packaging) ? route('master.packagings.update', $packaging) : route('master.packagings.store') }}">
        @csrf @if(isset($packaging)) @method('PUT') @endif

        @if(isset($selectedIngredient))
            <div class="mb-3"><label class="form-label fw-semibold">Bahan</label>
                <input type="text" class="form-control" value="{{ $selectedIngredient->name }}" disabled>
                <input type="hidden" name="ingredient_id" value="{{ $selectedIngredient->id }}">
            </div>
        @elseif(isset($packaging))
            <div class="mb-3"><label class="form-label fw-semibold">Bahan</label>
                <input type="text" class="form-control" value="{{ $packaging->ingredient->name }}" disabled>
                <input type="hidden" name="ingredient_id" value="{{ $packaging->ingredient_id }}">
            </div>
        @else
            <div class="mb-3"><label class="form-label fw-semibold">Bahan *</label>
                <select name="ingredient_id" class="form-select" required>
                    <option value="">— Pilih Bahan —</option>
                    @foreach($ingredients as $i)<option value="{{ $i->id }}">{{ $i->name }}</option>@endforeach
                </select>
            </div>
        @endif

        <div class="mb-3"><label class="form-label fw-semibold">Supplier</label>
            <select name="supplier_id" class="form-select">
                <option value="">— Tidak Ada —</option>
                @foreach($suppliers as $s)<option value="{{ $s->id }}" {{ old('supplier_id', $packaging->supplier_id ?? '')==$s->id?'selected':'' }}>{{ $s->name }}</option>@endforeach
            </select>
        </div>
        <div class="mb-3"><label class="form-label fw-semibold">Nama Kemasan *</label>
            <input type="text" name="packaging_name" class="form-control" value="{{ old('packaging_name', $packaging->packaging_name ?? '') }}" placeholder="Contoh: Dus Zhisheng" required>
        </div>
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">1 Dus = berapa Pack? *</label>
                <input type="number" name="crate_to_pack" class="form-control" value="{{ old('crate_to_pack', $packaging->crate_to_pack ?? '') }}" min="1" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">1 Pack = berapa satuan? *</label>
                <input type="number" name="pack_to_base" class="form-control" value="{{ old('pack_to_base', $packaging->pack_to_base ?? '') }}" step="0.0001" min="0.0001" required>
            </div>
        </div>
        <div class="mb-4"><div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="actPack" {{ old('is_active', $packaging->is_active ?? true)?'checked':'' }}>
            <label class="form-check-label fw-semibold" for="actPack">Kemasan Aktif</label>
        </div></div>
        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i>Simpan</button>
    </form>
</div></div>
@endsection
