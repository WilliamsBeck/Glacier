@extends('layouts.app')
@section('title', 'Supplier')

@section('content')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
        .web3-dashboard {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #ffffff;
            border-radius: 24px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.01);
        }

        /* INPUT BOX: Tetap modern abu-abu empuk, tapi teks di dalamnya diperjelas */
        .search-box-premium {
            border-radius: 12px !important;
            background-color: #f4f5f6 !important;
            border: 1px solid transparent !important;
            font-size: 0.95rem !important;
            /* Ukuran pas di mata */
            padding: 12px 16px !important;
            color: #0f172a !important;
            /* Warna teks tajam (Slate 900) */
            font-weight: 500;
        }

        .search-box-premium:focus {
            background-color: #ffffff !important;
            border-color: #cbd5e1 !important;
        }

        /* STRUKTUR TABEL: Baris melayang/terpisah yang Anda sukai, tapi lebih rapi */
        .table-crypto {
            border-collapse: separate;
            border-spacing: 0 10px;
            /* Jarak antar baris dijaga agar tidak sumpek */
        }

        .table-crypto thead th {
            font-size: 0.8rem !important;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #64748b !important;
            /* Abu-abu profesional */
            font-weight: 600;
            border: none !important;
            padding: 12px 20px !important;
        }

        .table-crypto tbody tr {
            background-color: #ffffff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.01);
            transition: transform 0.15s ease;
        }

        .table-crypto tbody tr:hover {
            background-color: #f8fafc !important;
            /* Hover tipis yang elegan */
        }

        /* Border-radius khusus untuk baris yang melayang agar melengkung di ujungnya saja */
        .table-crypto tbody td {
            border: none !important;
            padding: 18px 20px !important;
            /* Padding tebal agar data punya ruang napas */
            vertical-align: middle;
        }

        .table-crypto tbody tr td:first-child {
            border-top-left-radius: 14px !important;
            border-bottom-left-radius: 14px !important;
        }

        .table-crypto tbody tr td:last-child {
            border-top-right-radius: 14px !important;
            border-bottom-right-radius: 14px !important;
        }

        /* LINGKARAN INISIAL: Tetap bulat ikonik, tapi warnanya dibuat soft-dark agar teks nama di sebelahnya lebih menonjol */
        .supplier-avatar {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background-color: #1e293b;
            /* Charcoal Navy premium */
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        /* BADGE STATUS & TIPE: Teks tebal, warna pastel kontras tinggi (Sangat mudah dibaca) */
        .badge-clear {
            font-weight: 600;
            font-size: 0.78rem;
            padding: 6px 12px;
            border-radius: 8px;
            display: inline-block;
        }
    </style>

    <div class="p-4 web3-dashboard">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-700 mb-1" style="font-weight: 700; color: #0f172a; letter-spacing: -0.5px;">Manajemen Supplier
                </h3>
                <p class="text-muted small mb-0" style="font-size: 0.88rem;">Menampilkan database rantai pasok logistik
                    resmi</p>
            </div>
            <a href="{{ route('master.suppliers.create') }}" class="btn btn-dark rounded-pill px-4 py-2 fw-semibold btn-sm"
                style="background-color: #0f172a; border: none; font-size: 0.88rem;">
                <i class="bi bi-plus-lg me-1"></i> Tambah Supplier
            </a>
        </div>

        <div class="mb-3 p-3" style="background-color: #f8fafc; border-radius: 16px;">
            <form method="GET" class="row g-2">
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control search-box-premium"
                        placeholder="🔍 Cari nama supplier..." value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <select name="type" class="form-select search-box-premium">
                        <option value="">Semua Tipe</option>
                        <option value="zhisheng" {{ request('type') === 'zhisheng' ? 'selected' : '' }}>Pusat</option>
                        <option value="local_supplier" {{ request('type') === 'local_supplier' ? 'selected' : '' }}>Supplier
                            Lokal
                        </option>
                        <option value="other" {{ request('type') === 'other' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2 justify-content-end">
                    <button type="submit" class="btn btn-dark btn-sm fw-semibold px-4"
                        style="border-radius: 10px; background-color: #0f172a; padding: 10px 20px;">
                        Cari
                    </button>
                    <a href="{{ route('master.suppliers.index') }}"
                        class="btn btn-outline-secondary btn-sm fw-semibold d-flex align-items-center px-3"
                        style="border-radius: 10px;">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table align-middle table-crypto mb-0">
                <thead>
                    <tr>
                        <th width="60" class="text-center">#</th>
                        <th>Informasi Supplier</th>
                        <th>Kontak / Telp</th>
                        <th>Status Bisnis</th>
                        <th width="120" class="text-end">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $sup)
                        <tr style="border: 1px solid #e2e8f0;">
                            <td class="text-center text-muted fw-medium" style="font-size: 0.85rem;">
                                {{ $suppliers->firstItem() + $loop->index }}
                            </td>

                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="supplier-avatar">
                                        {{ strtoupper(substr($sup->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold mb-1" style="font-size: 1rem; color: #0f172a;">
                                            {{ $sup->name }}
                                        </div>

                                        @if($sup->type === 'zhisheng')
                                            <span class="badge-clear" style="background-color: #e0f2fe; color: #0369a1;">
                                                <i class="bi bi-building me-1"></i> {{ $sup->type_label }}
                                            </span>
                                        @elseif($sup->type === 'local_supplier')
                                            <span class="badge-clear" style="background-color: #dcfce7; color: #15803d;">
                                                <i class="bi bi-geo-alt me-1"></i> {{ $sup->type_label }}
                                            </span>
                                        @else
                                            <span class="badge-clear" style="background-color: #f1f5f9; color: #475569;">
                                                <i class="bi bi-box me-1"></i> {{ $sup->type_label }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="fw-semibold" style="color: #334155; font-size: 0.95rem;">
                                {{ $sup->contact ?? '-' }}
                            </td>

                            <td>
                                @if($sup->is_active)
                                    <span class="badge-clear" style="background-color: #bbf7d0; color: #166534;">
                                        <span class="d-inline-block rounded-circle me-1"
                                            style="width: 6px; height: 6px; background-color: #166534; vertical-align: middle;"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="badge-clear" style="background-color: #f1f5f9; color: #64748b;">
                                        <span class="d-inline-block rounded-circle me-1"
                                            style="width: 6px; height: 6px; background-color: #64748b; vertical-align: middle;"></span>
                                        Nonaktif
                                    </span>
                                @endif
                            </td>

                            <td class="text-end">
                                <div class="d-inline-flex gap-2">
                                    <a href="{{ route('master.suppliers.edit', $sup) }}"
                                        class="btn btn-sm btn-light border fw-medium"
                                        style="border-radius: 8px; color: #475569; padding: 6px 10px;">
                                        <i class="bi bi-pencil-square me-1"></i> Edit
                                    </a>
                                    <form method="POST" action="{{ route('master.suppliers.destroy', $sup) }}" class="d-inline"
                                        onsubmit="return confirm('Hapus supplier ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-light border text-danger fw-medium"
                                            style="border-radius: 8px; background-color: #fff5f5; border-color: #fee2e2 !important; padding: 6px 10px;">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-folder2-open fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                <span class="fw-medium">Data tidak ditemukan atau belum terdaftar.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($suppliers->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-4 pt-3" style="border-top: 1px solid #f1f5f9;">
                <div class="small fw-medium text-muted" style="font-size: 0.85rem;">
                    Menampilkan {{ $suppliers->firstItem() }} - {{ $suppliers->lastItem() }} dari {{ $suppliers->total() }} data
                </div>
                <div>
                    {{ $suppliers->withQueryString()->links() }}
                </div>
            </div>
        @endif

    </div>
@endsection