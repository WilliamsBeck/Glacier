@extends('layouts.app')
@section('title', isset($supplier) ? 'Edit Supplier' : 'Tambah Supplier')

@section('content')
<div class="page-header d-flex justify-content-between align-items-start">
    <div>
        <h1 class="page-title">{{ isset($supplier) ? 'Edit Supplier' : 'Tambah Supplier' }}</h1>
        <p class="page-subtitle">{{ isset($supplier) ? 'Perbarui data mitra rantai pasok' : 'Daftarkan mitra rantai pasok baru' }}</p>
    </div>
    <a href="{{ route('master.suppliers.index') }}" class="btn btn-outline-secondary btn-back">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="row">
    <div class="col-xl-7 col-lg-9">
        <div class="card">
            <div class="card-header fw-semibold">
                <i class="bi bi-truck me-1 text-muted"></i>{{ isset($supplier) ? 'Data Supplier' : 'Data Supplier Baru' }}
            </div>
            <div class="card-body">
                <form method="POST"
                      action="{{ isset($supplier) ? route('master.suppliers.update', $supplier) : route('master.suppliers.store') }}">
                    @csrf
                    @if(isset($supplier)) @method('PUT') @endif

                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <label class="form-label">Nama Supplier <span class="text-danger">*</span></label>
                            <input type="text" name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $supplier->name ?? '') }}"
                                   placeholder="cth: PT Semesta Raya Logistik" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Tipe / Kategori <span class="text-danger">*</span></label>
                            <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                <option value="" disabled {{ old('type', $supplier->type ?? '') === '' ? 'selected' : '' }}>— Pilih Tipe Supplier —</option>
                                <option value="zhisheng" {{ old('type', $supplier->type ?? '') === 'zhisheng' ? 'selected' : '' }}>Pusat</option>
                                <option value="local_supplier" {{ old('type', $supplier->type ?? '') === 'local_supplier' ? 'selected' : '' }}>Supplier Lokal</option>
                                <option value="other" {{ old('type', $supplier->type ?? '') === 'other' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                            @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Kontak (Telp/HP/Email)</label>
                            <input type="text" name="contact"
                                   class="form-control @error('contact') is-invalid @enderror"
                                   value="{{ old('contact', $supplier->contact ?? '') }}"
                                   placeholder="cth: 0812-3456-7890">
                            @error('contact')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Alamat Lengkap</label>
                            <textarea name="address" rows="3"
                                      class="form-control @error('address') is-invalid @enderror"
                                      placeholder="Masukkan alamat lengkap supplier / gudang…">{{ old('address', $supplier->address ?? '') }}</textarea>
                            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="form-check form-switch mb-1">
                        <input class="form-check-input" type="checkbox" name="is_active" id="actSup"
                               value="1" {{ old('is_active', $supplier->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="actSup">Set sebagai Supplier Aktif</label>
                    </div>
                    <div class="form-text mb-4">
                        <i class="bi bi-info-circle me-1"></i>Supplier nonaktif tidak muncul di pilihan saat membuat Rencana Order.
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>{{ isset($supplier) ? 'Simpan Perubahan' : 'Simpan Supplier' }}
                        </button>
                        <a href="{{ route('master.suppliers.index') }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
