@extends('layouts.app')
@section('title', isset($store) ? 'Edit Toko' : 'Tambah Toko')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <h4 class="page-title">{{ isset($store) ? 'Edit Toko: '.$store->name : 'Tambah Toko Baru' }}</h4>
    <a href="{{ route('master.stores.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>

<div class="card" style="max-width:580px">
    <div class="card-body">
        <form method="POST"
              action="{{ isset($store) ? route('master.stores.update', $store) : route('master.stores.store') }}">
            @csrf
            @if(isset($store)) @method('PUT') @endif

            <div class="mb-3">
                <label class="form-label fw-semibold">Nomor Batch <span class="text-danger">*</span></label>
                <input type="text" name="store_code"
                       class="form-control @error('store_code') is-invalid @enderror"
                       value="{{ old('store_code', $store->store_code ?? '') }}"
                       placeholder="Kode batch">
                <div class="form-text">Kode unik, tidak bisa diubah setelah digunakan di transaksi.</div>
                @error('store_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Nama Toko <span class="text-danger">*</span></label>
                <input type="text" name="name"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $store->name ?? '') }}"
                       placeholder="Nama lengkap toko">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Area <span class="text-danger">*</span></label>
                <input type="text" name="area"
                       class="form-control @error('area') is-invalid @enderror"
                       value="{{ old('area', $store->area ?? '') }}"
                       placeholder="Contoh: Jakarta Selatan">
                @error('area')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            @if(isset($store))
            <div class="alert alert-light border small mb-3">
                <i class="bi bi-info-circle me-1 text-primary"></i>
                <strong>Konfigurasi order</strong> (lead time, siklus order, window DOS)
                diatur di halaman <a href="{{ route('order-planning.index') }}">Rencana Order</a>.
            </div>
            @endif

            <div class="mb-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_active"
                           id="isActive" value="1"
                           {{ old('is_active', $store->is_active ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="isActive">Toko Aktif</label>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-save me-1"></i> Simpan
                </button>
                <a href="{{ route('master.stores.index') }}" class="btn btn-outline-secondary px-4">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
