@extends('layouts.app')
@section('title', isset($supplier) ? 'Edit Supplier' : 'Tambah Supplier')

@section('content')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
        .web3-container {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            min-height: 100vh;
        }

        /* TOMBOL KEMBALI (PILL STYLE) */
        .btn-back-pill {
            display: inline-flex;
            align-items: center;
            background-color: #ffffff;
            color: #475569;
            border: 1px solid #cbd5e1;
            border-radius: 50px;
            font-weight: 600;
            padding: 8px 20px;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-back-pill:hover {
            background-color: #f1f5f9;
            color: #0f172a;
            border-color: #94a3b8;
        }

        /* KARTU FORM UTAMA */
        .web3-form-card {
            background-color: #ffffff;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.02), 0 5px 15px rgba(0, 0, 0, 0.01);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        /* HEADER KARTU */
        .card-header-premium {
            padding: 24px 30px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .header-icon-box {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background-color: #fff7ed;
            /* Soft orange/amber untuk supplier */
            color: #ea580c;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .section-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }

        /* AREA KONTEN FORM */
        .card-body-premium {
            padding: 30px;
        }

        /* LABEL DAN INPUT MAIN FORM */
        .premium-label {
            font-weight: 600;
            color: #1e293b;
            font-size: 0.92rem;
            margin-bottom: 10px;
            display: block;
        }

        .premium-input,
        .premium-select {
            border-radius: 12px !important;
            background-color: #f1f5f9 !important;
            border: 1px solid transparent !important;
            font-size: 0.98rem !important;
            padding: 14px 18px !important;
            color: #0f172a !important;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        textarea.premium-input {
            resize: vertical;
            min-height: 100px;
        }

        .premium-input:focus,
        .premium-select:focus {
            background-color: #ffffff !important;
            border-color: #cbd5e1 !important;
            box-shadow: 0 0 0 4px rgba(15, 23, 42, 0.05) !important;
            outline: none;
        }

        .premium-input::placeholder {
            color: #94a3b8 !important;
            font-weight: 500;
        }

        /* TOMBOL SUBMIT BAWAH */
        .btn-submit-premium {
            background-color: #0f172a;
            color: #ffffff;
            border-radius: 50px;
            font-weight: 700;
            padding: 16px 0;
            width: 100%;
            border: none;
            font-size: 1rem;
            transition: all 0.2s ease;
        }

        .btn-submit-premium:hover {
            background-color: #1e293b;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.15);
        }

        /* TOGGLE SWITCH */
        .form-switch .form-check-input {
            width: 2.8em;
            height: 1.4em;
            cursor: pointer;
            border: none;
            background-color: #cbd5e1;
        }

        .form-switch .form-check-input:checked {
            background-color: #10b981;
        }
    </style>

    <div class="web3-container pb-5">
        <div class="container-fluid pt-4">
            <div class="row">
                <div class="col-xl-7 col-lg-9 mx-auto">

                    <div class="mb-4">
                        <a href="{{ route('master.suppliers.index') }}" class="btn-back-pill">
                            <i class="bi bi-arrow-left me-2"></i>Kembali ke Daftar Supplier
                        </a>
                    </div>

                    <div class="web3-form-card">
                        <div class="card-header-premium">
                            <div class="d-flex align-items-center gap-3">
                                <div class="header-icon-box">
                                    <i class="bi bi-truck"></i>
                                </div>
                                <div>
                                    <h4 class="section-title">
                                        {{ isset($supplier) ? 'Edit Data Supplier' : 'Daftarkan Supplier Baru' }}
                                    </h4>
                                    <div class="text-muted small mt-1">
                                        {{ isset($supplier) ? 'Memperbarui data mitra rantai pasok' : 'Daftarkan mitra rantai pasok ke dalam sistem' }}
                                    </div>
                                </div>
                            </div>
                            <div>
                                <span
                                    class="badge {{ isset($supplier) ? 'bg-primary' : 'bg-success' }} px-3 py-2 rounded-pill fw-semibold"
                                    style="font-size: 0.75rem;">
                                    {{ isset($supplier) ? 'Mode Edit' : 'Supplier Baru' }}
                                </span>
                            </div>
                        </div>

                        <div class="card-body-premium">
                            <form method="POST"
                                action="{{ isset($supplier) ? route('master.suppliers.update', $supplier) : route('master.suppliers.store') }}">
                                @csrf
                                @if(isset($supplier)) @method('PUT') @endif

                                <div class="row g-4 mb-4">
                                    <div class="col-12">
                                        <label class="premium-label">Nama Supplier <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="name"
                                            class="form-control premium-input @error('name') is-invalid @enderror"
                                            value="{{ old('name', $supplier->name ?? '') }}"
                                            placeholder="Contoh: PT Semesta Raya Logistik" required>
                                        @error('name')<div class="invalid-feedback fw-semibold mt-1"
                                        style="font-size: 0.82rem;">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="premium-label">Tipe / Kategori <span
                                                class="text-danger">*</span></label>
                                        <select name="type"
                                            class="form-select premium-select @error('type') is-invalid @enderror" required>
                                            <option value="" disabled {{ old('type', $supplier->type ?? '') === '' ? 'selected' : '' }}>-- Pilih Tipe Supplier --</option>
                                            <option value="zhisheng" {{ old('type', $supplier->type ?? '') === 'zhisheng' ? 'selected' : '' }}>Pusat</option>
                                            <option value="local_supplier" {{ old('type', $supplier->type ?? '') === 'local_supplier' ? 'selected' : '' }}>Supplier Lokal</option>
                                            <option value="other" {{ old('type', $supplier->type ?? '') === 'other' ? 'selected' : '' }}>Lainnya</option>
                                        </select>
                                        @error('type')<div class="invalid-feedback fw-semibold mt-1"
                                        style="font-size: 0.82rem;">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="premium-label">Kontak (Telp/HP/Email)</label>
                                        <input type="text" name="contact"
                                            class="form-control premium-input @error('contact') is-invalid @enderror"
                                            value="{{ old('contact', $supplier->contact ?? '') }}"
                                            placeholder="Contoh: 0812-3456-7890">
                                        @error('contact')<div class="invalid-feedback fw-semibold mt-1"
                                        style="font-size: 0.82rem;">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-12">
                                        <label class="premium-label">Alamat Lengkap</label>
                                        <textarea name="address"
                                            class="form-control premium-input @error('address') is-invalid @enderror"
                                            placeholder="Masukkan alamat lengkap supplier / gudang...">{{ old('address', $supplier->address ?? '') }}</textarea>
                                        @error('address')<div class="invalid-feedback fw-semibold mt-1"
                                        style="font-size: 0.82rem;">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="mb-4 pb-4 border-bottom">
                                    <div class="form-check form-switch d-flex align-items-center gap-3 ps-0">
                                        <input class="form-check-input m-0" type="checkbox" name="is_active" id="actSup"
                                            value="1" {{ old('is_active', $supplier->is_active ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label premium-label mb-0" for="actSup"
                                            style="cursor: pointer;">Set Sebagai Supplier Aktif</label>
                                    </div>
                                    <div class="form-text mt-2 fw-medium text-secondary"
                                        style="font-size: 0.82rem; line-height: 1.4;">
                                        <i class="bi bi-info-circle me-1"></i> Supplier yang nonaktif tidak akan muncul di
                                        pilihan saat membuat Rencana Order.
                                    </div>
                                </div>

                                <button type="submit" class="btn-submit-premium">
                                    <i
                                        class="bi bi-cloud-arrow-up-fill me-2"></i>{{ isset($supplier) ? 'Simpan Perubahan Supplier' : 'Daftarkan Supplier Sekarang' }}
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection