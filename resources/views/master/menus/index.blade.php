@extends('layouts.app')
@section('title', 'Menu')

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
            padding: 12px 16px !important;
            color: #0f172a !important;
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
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        }

        /* Border untuk baris melayang agar rapi */
        .table-crypto tbody td {
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            padding: 18px 20px !important;
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

        /* AVATAR MENU: Slate gelap elegan */
        .menu-icon-box {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background-color: #1e293b;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        /* BADGE STATUS & INFO: Teks tebal, warna pastel kontras tinggi */
        .badge-clear {
            font-weight: 600;
            font-size: 0.82rem;
            padding: 6px 12px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
        }

        /* ── SULAP PAGINATION MENJADI PREMIUM & BERSIH ── */
        .premium-pagination nav div:first-child {
            display: none !important;
        }

        .premium-pagination nav div:last-child {
            margin-bottom: 0 !important;
        }

        .premium-pagination .pagination {
            margin-bottom: 0 !important;
            gap: 6px;
        }

        .premium-pagination .page-link {
            border-radius: 10px !important;
            border: 1px solid #e2e8f0 !important;
            color: #475569 !important;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 8px 14px;
            box-shadow: none !important;
            transition: all 0.2s ease;
        }

        .premium-pagination .page-item.active .page-link {
            background-color: #0f172a !important;
            border-color: #0f172a !important;
            color: #ffffff !important;
        }

        .premium-pagination .page-link:hover {
            background-color: #f1f5f9 !important;
            border-color: #cbd5e1 !important;
            color: #0f172a !important;
        }

        .premium-pagination .page-item.disabled .page-link {
            background-color: #f8fafc !important;
            color: #cbd5e1 !important;
            border-color: #e2e8f0 !important;
        }
    </style>

    <div class="p-4 web3-dashboard">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-700 mb-1" style="font-weight: 700; color: #0f172a; letter-spacing: -0.5px;">Daftar Menu</h3>
                <p class="text-muted small mb-0" style="font-size: 0.88rem;">Manajemen master data menu dan varian resep</p>
            </div>
            <a href="{{ route('master.menus.create') }}"
                class="btn btn-dark rounded-pill px-4 py-2 fw-semibold btn-sm shadow-sm"
                style="background-color: #0f172a; border: none; font-size: 0.88rem;">
                <i class="bi bi-plus-lg me-1"></i> Tambah Menu
            </a>
        </div>

        <div class="mb-3 p-3" style="background-color: #f8fafc; border-radius: 16px;">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control search-box-premium"
                        placeholder="🔍 Cari nama menu..." value="{{ request('search') }}">
                </div>
                <div class="col-md-7 d-flex gap-2 justify-content-end">
                    <button type="submit" class="btn btn-dark btn-sm fw-semibold px-4"
                        style="border-radius: 10px; background-color: #0f172a; padding: 10px 20px;">
                        Cari
                    </button>
                    <a href="{{ route('master.menus.index') }}"
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
                        <th>Nama Menu</th>
                        <th>Jumlah Versi Resep</th>
                        <th>Status</th>
                        <th width="160" class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($menus as $menu)
                        <tr>
                            <td class="text-center text-muted fw-medium" style="font-size: 0.85rem;">
                                {{ $menus->firstItem() + $loop->index }}
                            </td>

                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="menu-icon-box">
                                        {{ strtoupper(substr($menu->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold mb-0" style="font-size: 1rem; color: #0f172a;">
                                            {{ $menu->name }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td>
                                @if($menu->recipe_versions_count > 0)
                                    <span class="badge-clear" style="background-color: #e0f2fe; color: #0284c7;">
                                        <i class="bi bi-journal-text me-1"></i> {{ $menu->recipe_versions_count }} versi resep
                                    </span>
                                @else
                                    <span class="badge-clear"
                                        style="background-color: #f1f5f9; color: #64748b; font-style: italic;">
                                        Belum ada resep
                                    </span>
                                @endif
                            </td>

                            <td>
                                @if($menu->is_active)
                                    <span class="badge-clear" style="background-color: #dcfce7; color: #166534;">
                                        <span class="d-inline-block rounded-circle me-2"
                                            style="width: 6px; height: 6px; background-color: #166534;"></span>
                                        Aktif
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
                                    <a href="{{ route('master.menus.edit', $menu) }}"
                                        class="btn btn-sm btn-light border fw-medium"
                                        style="border-radius: 8px; color: #475569; padding: 6px 10px;"
                                        title="Edit & Kelola Resep">
                                        <i class="bi bi-pencil-square me-1"></i> Edit
                                    </a>
                                    <form method="POST" action="{{ route('master.menus.destroy', $menu) }}" class="d-inline"
                                        onsubmit="return confirm('Hapus menu ini beserta semua resepnya?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-light border text-danger fw-medium"
                                            style="border-radius: 8px; background-color: #fff5f5; border-color: #fee2e2 !important; padding: 6px 10px;"
                                            title="Hapus Menu">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-cup-straw fs-2 d-block mb-3 opacity-25"></i>
                                <span class="fw-medium">Belum ada data menu terdaftar.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($menus->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-4 pt-3" style="border-top: 1px solid #f1f5f9;">
                <div class="small fw-semibold text-muted" style="font-size: 0.88rem;">
                    Menampilkan {{ $menus->firstItem() }} - {{ $menus->lastItem() }} dari
                    {{ $menus->total() }} menu
                </div>
                <div class="premium-pagination">
                    {{ $menus->withQueryString()->links() }}
                </div>
            </div>
        @endif

    </div>
@endsection