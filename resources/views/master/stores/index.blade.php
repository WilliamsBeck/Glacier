@extends('layouts.app')
@section('title', 'Toko')

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

        /* INPUT BOX: Modern abu-abu empuk, teks di dalamnya diperjelas */
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
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05) !important;
        }

        /* STRUKTUR TABEL: Baris melayang/terpisah dengan ruang napas ekstra */
        .table-crypto {
            border-collapse: separate;
            border-spacing: 0 10px;
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
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .table-crypto tbody tr:hover {
            background-color: #f8fafc !important;
            /* Hover tipis elegan */
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        }

        /* Border untuk baris melayang agar rapi */
        .table-crypto tbody td {
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            padding: 18px 20px !important;
            /* Padding tebal agar terbaca jelas */
            vertical-align: middle;
        }

        .table-crypto tbody tr td:first-child {
            border-left: 1px solid #e2e8f0;
            border-top-left-radius: 14px !important;
            border-bottom-left-radius: 14px !important;
        }

        .table-crypto tbody tr td:last-child {
            border-right: 1px solid #e2e8f0;
            border-top-right-radius: 14px !important;
            border-bottom-right-radius: 14px !important;
        }

        /* AVATAR TOKO: Slate gelap elegan, bukan hitam mati */
        .store-icon-box {
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

        /* BADGE STATUS: Teks tebal, warna pastel kontras tinggi */
        .badge-clear {
            font-weight: 600;
            font-size: 0.85rem;
            padding: 6px 12px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
        }
    </style>

    <div class="p-4 web3-dashboard">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-700 mb-1" style="font-weight: 700; color: #0f172a; letter-spacing: -0.5px;">Manajemen Toko
                </h3>
                <p class="text-muted small mb-0" style="font-size: 0.88rem;">Total <span
                        class="fw-bold text-dark">{{ $stores->total() }} toko</span> terdaftar dalam sistem</p>
            </div>
            <a href="{{ route('master.stores.create') }}"
                class="btn btn-dark rounded-pill px-4 py-2 fw-semibold btn-sm shadow-sm"
                style="background-color: #0f172a; border: none; font-size: 0.88rem;">
                <i class="bi bi-plus-lg me-1"></i> Tambah Toko
            </a>
        </div>

        <div class="mb-3 p-3" style="background-color: #f8fafc; border-radius: 16px;">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control search-box-premium"
                        placeholder="🔍 Cari nama atau kode toko..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="area" class="form-select search-box-premium">
                        <option value="">Semua Area</option>
                        @foreach($areas as $area)
                            <option value="{{ $area }}" {{ request('area') == $area ? 'selected' : '' }}>{{ $area }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select search-box-premium">
                        <option value="">Semua Status</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2 justify-content-end">
                    <button type="submit" class="btn btn-dark btn-sm fw-semibold px-4"
                        style="border-radius: 10px; background-color: #0f172a; padding: 10px 20px;">
                        Cari
                    </button>
                    <a href="{{ route('master.stores.index') }}"
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
                        <th>Informasi Toko</th>
                        <th>Area / Wilayah</th>
                        <th>Status</th>
                        <th width="140" class="text-end pe-4">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stores as $s)
                        <tr>
                            <td class="text-center text-muted fw-medium" style="font-size: 0.85rem;">
                                {{ $stores->firstItem() + $loop->index }}
                            </td>

                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="store-icon-box">
                                        {{ strtoupper(substr($s->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold mb-1" style="font-size: 1rem; color: #0f172a;">
                                            {{ $s->name }}
                                        </div>
                                        <div class="fw-medium text-secondary"
                                            style="font-size: 0.82rem; background-color: #f1f5f9; padding: 2px 8px; border-radius: 4px; display: inline-block;">
                                            <i class="bi bi-upc-scan me-1"></i> {{ $s->store_code }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="fw-semibold" style="color: #334155; font-size: 0.95rem;">
                                {{ $s->area }}
                            </td>

                            <td>
                                @if($s->is_active)
                                    <span class="badge-clear" style="background-color: #dcfce7; color: #166534;">
                                        <span class="d-inline-block rounded-circle me-2"
                                            style="width: 6px; height: 6px; background-color: #166534;"></span>
                                        Aktif Beroperasi
                                    </span>
                                @else
                                    <span class="badge-clear" style="background-color: #f1f5f9; color: #64748b;">
                                        <span class="d-inline-block rounded-circle me-2"
                                            style="width: 6px; height: 6px; background-color: #64748b;"></span>
                                        Nonaktif
                                    </span>
                                @endif
                            </td>

                            <td class="text-end pe-3">
                                <div class="d-inline-flex gap-2">
                                    <a href="{{ route('master.stores.edit', $s) }}"
                                        class="btn btn-sm btn-light border fw-medium"
                                        style="border-radius: 8px; color: #475569; padding: 6px 10px;">
                                        <i class="bi bi-pencil-square me-1"></i> Edit
                                    </a>
                                    <form method="POST" action="{{ route('master.stores.destroy', $s) }}" class="d-inline"
                                        onsubmit="return confirm('Hapus toko {{ $s->name }}?')">
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
                                <i class="bi bi-shop fs-2 d-block mb-3 opacity-25"></i>
                                <span class="fw-medium">Belum ada data toko terdaftar.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($stores->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-4 pt-3" style="border-top: 1px solid #f1f5f9;">
                <div class="small fw-medium text-muted" style="font-size: 0.85rem;">
                    Menampilkan {{ $stores->firstItem() }} - {{ $stores->lastItem() }} dari {{ $stores->total() }} aset toko
                </div>
                <div class="premium-pagination">
                    {{ $stores->withQueryString()->links() }}
                </div>
            </div>
        @endif

    </div>
@endsection