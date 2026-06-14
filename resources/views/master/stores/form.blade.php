@extends('layouts.app')
@section('title', isset($store) ? 'Edit Toko' : 'Tambah Toko')

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
            background-color: #eff6ff;
            color: #3b82f6;
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

        .premium-input {
            border-radius: 12px !important;
            background-color: #f1f5f9 !important;
            /* Warna sesuai gambar (abu kebiruan soft) */
            border: 1px solid transparent !important;
            font-size: 0.98rem !important;
            padding: 14px 18px !important;
            color: #0f172a !important;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .premium-input:focus {
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

        /* ALERT INFO */
        .info-card-premium {
            background-color: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 16px;
            padding: 16px;
            color: #475569;
            font-size: 0.88rem;
            line-height: 1.6;
        }
    </style>

    <div class="web3-container pb-5">
        <div class="container-fluid pt-4">
            <div class="row">
                <div class="col-xl-7 col-lg-9 mx-auto">

                    <div class="mb-4">
                        <a href="{{ route('master.stores.index') }}" class="btn-back-pill">
                            <i class="bi bi-arrow-left me-2"></i>Kembali ke Daftar Toko
                        </a>
                    </div>

                    <div class="web3-form-card">
                        <div class="card-header-premium">
                            <div class="d-flex align-items-center gap-3">
                                <div class="header-icon-box">
                                    <i class="bi bi-shop"></i>
                                </div>
                                <div>
                                    <h4 class="section-title">
                                        {{ isset($store) ? 'Edit Data Toko' : 'Buat Toko Sistem Baru' }}
                                    </h4>
                                    <div class="text-muted small mt-1">Lengkapi data informasi toko di bawah ini</div>
                                </div>
                            </div>
                            <div>
                                <span
                                    class="badge {{ isset($store) ? 'bg-primary' : 'bg-success' }} px-3 py-2 rounded-pill fw-semibold"
                                    style="font-size: 0.75rem;">
                                    {{ isset($store) ? 'Mode Edit' : 'Toko Baru' }}
                                </span>
                            </div>
                        </div>

                        <div class="card-body-premium">
                            <form method="POST"
                                action="{{ isset($store) ? route('master.stores.update', $store) : route('master.stores.store') }}">
                                @csrf
                                @if(isset($store)) @method('PUT') @endif

                                <div class="row g-4 mb-4">
                                    <div class="col-12">
                                        <label class="premium-label">Nama Lengkap Toko <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="name"
                                            class="form-control premium-input @error('name') is-invalid @enderror"
                                            value="{{ old('name', $store->name ?? '') }}"
                                            placeholder="cth: Cabang Jakarta Selatan">
                                        @error('name')<div class="invalid-feedback fw-medium" style="font-size: 0.8rem;">
                                        {{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="premium-label">Nomor Batch (Kode Toko) <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="store_code"
                                            class="form-control premium-input @error('store_code') is-invalid @enderror"
                                            value="{{ old('store_code', $store->store_code ?? '') }}"
                                            placeholder="cth: JKT-001">
                                        <div class="form-text mt-2 text-muted" style="font-size: 0.75rem;">
                                            <i class="bi bi-info-circle me-1"></i> Kode unik, permanen setelah dipakai.
                                        </div>
                                        @error('store_code')<div class="invalid-feedback fw-medium"
                                        style="font-size: 0.8rem;">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="premium-label">Area / Wilayah <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="area"
                                            class="form-control premium-input @error('area') is-invalid @enderror"
                                            value="{{ old('area', $store->area ?? '') }}"
                                            placeholder="cth: Jakarta Selatan">
                                        @error('area')<div class="invalid-feedback fw-medium" style="font-size: 0.8rem;">
                                        {{ $message }}</div>@enderror
                                    </div>
                                </div>

                                @if(isset($store))
                                    <div class="info-card-premium mb-4">
                                        <div class="d-flex align-items-start gap-3">
                                            <i class="bi bi-gear-fill text-secondary mt-1 fs-5"></i>
                                            <div>
                                                <strong class="text-dark d-block mb-1">Konfigurasi Order Lanjutan</strong>
                                                Pengaturan <em>lead time</em>, siklus order, dan <em>window DOS</em> diatur
                                                terpisah melalui halaman <a href="{{ route('order-planning.index') }}"
                                                    class="text-primary fw-semibold text-decoration-none">Rencana Order</a>.
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="mb-4 pb-2 border-bottom">
                                    <div class="form-check form-switch d-flex align-items-center gap-3 ps-0 mb-4">
                                        <input class="form-check-input m-0" type="checkbox" name="is_active" id="isActive"
                                            value="1" {{ old('is_active', $store->is_active ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label premium-label mb-0" for="isActive"
                                            style="cursor: pointer;">Set sebagai Toko Aktif</label>
                                    </div>
                                </div>

                                <button type="submit" class="btn-submit-premium">
                                    <i
                                        class="bi bi-arrow-down-square-fill me-2"></i>{{ isset($store) ? 'Simpan Perubahan Toko' : 'Daftarkan Toko Baru Sekarang' }}
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection