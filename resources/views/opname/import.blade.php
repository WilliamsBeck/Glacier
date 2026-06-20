@extends('layouts.app')
@section('title','Import Stok Opname')
@section('content')

<div class="page-header d-flex justify-content-between align-items-center">
    <h4 class="page-title">Import Stok Opname</h4>
    <a href="{{ route('opname.opnames.index') }}" class="btn btn-outline-secondary btn-sm btn-back">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <strong><i class="bi bi-exclamation-triangle me-1"></i>Gagal import:</strong>
        <ul class="mb-0 mt-1">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card" style="max-width:520px">
    <div class="card-body">
        <p class="text-muted small mb-3">
            Upload file template yang sudah diisi. Belum punya template? Download dulu dari halaman
            <a href="{{ route('opname.opnames.create') }}">Buat Opname</a> (pilih toko &amp; tanggal, lalu klik <strong>Download Template</strong>).
        </p>
        <div class="alert alert-info d-flex align-items-center gap-2 py-2 px-3 mb-3" style="font-size:.85rem">
            <i class="bi bi-info-circle-fill"></i>
            <span>Template berisi data sistem qty terkini — harus didownload ulang tiap periode baru.</span>
        </div>
        <form method="POST" action="{{ route('opname.opnames.import') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold">File Excel (.xlsx)</label>
                <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-upload me-1"></i>Upload & Import
            </button>
        </form>
    </div>
</div>

@endsection
