@extends('layouts.app')
@section('title', isset($store) ? 'Edit Toko' : 'Tambah Toko')

@section('content')
<div class="page-header d-flex justify-content-between align-items-start">
    <div>
        <h1 class="page-title">{{ isset($store) ? 'Edit Toko' : 'Tambah Toko' }}</h1>
        <p class="page-subtitle">{{ isset($store) ? 'Perbarui data informasi toko' : 'Lengkapi data informasi toko baru' }}</p>
    </div>
    <a href="{{ route('master.stores.index') }}" class="btn btn-outline-secondary btn-back">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="row">
    <div class="col-xl-7 col-lg-9">
        <div class="card">
            <div class="card-header fw-semibold">
                <i class="bi bi-shop me-1 text-muted"></i>{{ isset($store) ? 'Data Toko' : 'Data Toko Baru' }}
            </div>
            <div class="card-body">
                <form method="POST"
                      action="{{ isset($store) ? route('master.stores.update', $store) : route('master.stores.store') }}">
                    @csrf
                    @if(isset($store)) @method('PUT') @endif

                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <label class="form-label">Nama Toko <span class="text-danger">*</span></label>
                            <input type="text" name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $store->name ?? '') }}"
                                   placeholder="cth: Cabang Jakarta Selatan">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Kode Toko <span class="text-danger">*</span></label>
                            <input type="text" name="store_code"
                                   class="form-control @error('store_code') is-invalid @enderror"
                                   value="{{ old('store_code', $store->store_code ?? '') }}"
                                   placeholder="cth: JKT-001">
                            <div class="form-text"><i class="bi bi-info-circle me-1"></i>Kode unik, permanen setelah dipakai.</div>
                            @error('store_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Area / Wilayah <span class="text-danger">*</span></label>
                            <input type="text" name="area"
                                   class="form-control @error('area') is-invalid @enderror"
                                   value="{{ old('area', $store->area ?? '') }}"
                                   placeholder="cth: Jakarta Selatan">
                            @error('area')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    @if(isset($store))
                    <div class="alert alert-light border d-flex align-items-start gap-2">
                        <i class="bi bi-gear-fill text-muted mt-1"></i>
                        <div class="small mb-0">
                            <strong>Konfigurasi Order Lanjutan</strong> — pengaturan <em>lead time</em>, siklus order,
                            dan <em>window DOS</em> diatur terpisah di halaman
                            <a href="{{ route('order-planning.index') }}" class="fw-semibold text-decoration-none">Rencana Order</a>.
                        </div>
                    </div>
                    @endif

                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" name="is_active" id="isActive"
                               value="1" {{ old('is_active', $store->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="isActive">Set sebagai Toko Aktif</label>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>{{ isset($store) ? 'Simpan Perubahan' : 'Simpan Toko' }}
                        </button>
                        <a href="{{ route('master.stores.index') }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
