@extends('layouts.app')
@section('title', isset($supplier)?'Edit Supplier':'Tambah Supplier')
@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <h4 class="page-title">{{ isset($supplier)?'Edit Supplier: '.$supplier->name:'Tambah Supplier' }}</h4>
    <a href="{{ route('master.suppliers.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
</div>
<div class="card" style="max-width:580px"><div class="card-body">
    <form method="POST" action="{{ isset($supplier) ? route('master.suppliers.update', $supplier) : route('master.suppliers.store') }}">
        @csrf @if(isset($supplier)) @method('PUT') @endif
        <div class="mb-3">
            <label class="form-label fw-semibold">Nama Supplier *</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $supplier->name ?? '') }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Tipe *</label>
            <select name="type" class="form-select" required>
                <option value="zhisheng"       {{ old('type', $supplier->type ?? '')==='zhisheng'?'selected':'' }}>Pusat</option>
                <option value="local_supplier" {{ old('type', $supplier->type ?? '')==='local_supplier'?'selected':'' }}>Supplier Lokal</option>
                <option value="other"          {{ old('type', $supplier->type ?? '')==='other'?'selected':'' }}>Lainnya</option>
            </select>
        </div>
        <div class="mb-3"><label class="form-label fw-semibold">Kontak (Telp/HP/Email)</label>
            <input type="text" name="contact" class="form-control" value="{{ old('contact', $supplier->contact ?? '') }}">
        </div>
        <div class="mb-3"><label class="form-label fw-semibold">Alamat</label>
            <textarea name="address" class="form-control" rows="3">{{ old('address', $supplier->address ?? '') }}</textarea>
        </div>
        <div class="mb-4"><div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="actSup" {{ old('is_active', $supplier->is_active ?? true)?'checked':'' }}>
            <label class="form-check-label fw-semibold" for="actSup">Supplier Aktif</label>
        </div></div>
        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i>Simpan</button>
    </form>
</div></div>
@endsection
