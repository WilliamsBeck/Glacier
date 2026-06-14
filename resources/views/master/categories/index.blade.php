@extends('layouts.app')
@section('title', 'Kelola Kategori')

@section('content')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
        .web3-form-container {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        /* KARTU KONTEN ELEGAN */
        .web3-form-card {
            background-color: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.01);
            border: 1px solid #e2e8f0;
            padding: 24px;
            height: 100%;
        }

        .section-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* LABEL DAN INPUT MAIN FORM */
        .premium-label {
            font-weight: 600;
            color: #0f172a;
            font-size: 0.9rem;
            margin-bottom: 8px;
            display: block;
        }

        .premium-input {
            border-radius: 12px !important;
            background-color: #f8fafc !important;
            border: 1px solid #cbd5e1 !important;
            font-size: 0.95rem !important;
            padding: 12px 16px !important;
            color: #0f172a !important;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .premium-input:focus {
            background-color: #ffffff !important;
            border-color: #0f172a !important;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05) !important;
            outline: none;
        }

        /* INPUT SMALL UNTUK INLINE EDIT */
        .premium-input-sm {
            border-radius: 8px !important;
            background-color: #f8fafc !important;
            border: 1px solid #cbd5e1 !important;
            font-size: 0.85rem !important;
            padding: 6px 12px !important;
            color: #0f172a !important;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .premium-input-sm:focus {
            background-color: #ffffff !important;
            border-color: #0f172a !important;
            outline: none;
        }

        /* TABS PREMIUM */
        .premium-tabs {
            border-bottom: 2px solid #e2e8f0;
            gap: 12px;
        }

        .premium-tabs .nav-link {
            color: #64748b;
            font-weight: 600;
            font-size: 0.95rem;
            border: none;
            padding: 12px 24px;
            border-radius: 12px 12px 0 0;
            transition: all 0.2s;
            background-color: transparent;
            position: relative;
        }

        .premium-tabs .nav-link:hover {
            color: #0f172a;
            background-color: #f8fafc;
        }

        .premium-tabs .nav-link.active {
            color: #0f172a;
            background-color: transparent;
        }

        .premium-tabs .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 2px;
            background-color: #0f172a;
            border-radius: 2px 2px 0 0;
        }

        /* BADGE KUSTOM */
        .badge-premium-active {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 8px;
        }

        .badge-premium-inactive {
            background-color: #f1f5f9;
            color: #64748b;
            border: 1px solid #e2e8f0;
            font-weight: 500;
            padding: 6px 12px;
            border-radius: 8px;
        }

        /* KOTAK INFO */
        .info-card-premium {
            background-color: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 16px;
            padding: 16px;
            color: #475569;
            font-size: 0.85rem;
            line-height: 1.5;
        }
    </style>

    <div class="web3-form-container pb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-700 mb-1" style="font-weight: 700; color: #0f172a; letter-spacing: -0.5px;">
                    Kelola Kategori
                </h3>
                <p class="text-muted small mb-0" style="font-size: 0.88rem;">Atur kategori untuk bahan baku dan menu secara
                    interaktif</p>
            </div>
        </div>

        {{-- TABS --}}
        <ul class="nav nav-tabs premium-tabs mb-4" id="catTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" id="tab-bahan" data-bs-toggle="tab" data-bs-target="#pane-bahan"
                    type="button">
                    <i class="bi bi-box-seam me-2"></i>Kategori Bahan Baku
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="tab-menu" data-bs-toggle="tab" data-bs-target="#pane-menu" type="button">
                    <i class="bi bi-cup-straw me-2"></i>Kategori Menu
                </button>
            </li>
        </ul>

        <div class="tab-content" id="catTabContent">

            {{-- ===================== TAB BAHAN BAKU ===================== --}}
            <div class="tab-pane fade show active" id="pane-bahan" role="tabpanel">
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="web3-form-card">
                            <div class="section-title border-bottom pb-3 mb-0">
                                <span><i class="bi bi-list-ul me-2 text-primary"></i>Daftar Kategori Bahan Baku</span>
                                <span class="text-muted small fw-medium"
                                    style="font-size: 0.8rem; background: #f1f5f9; padding: 4px 10px; border-radius: 8px;">
                                    <i class="bi bi-grip-vertical me-1"></i>Drag untuk ubah urutan
                                </span>
                            </div>
                            <div class="card-body p-0 mt-2">
                                <table class="table table-hover mb-0 align-middle table-borderless">
                                    <thead style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                                        <tr>
                                            <th style="width:36px; border-radius: 12px 0 0 12px;"></th>
                                            <th class="text-muted fw-semibold small py-3">Nama Kategori</th>
                                            <th style="width:130px" class="text-center text-muted fw-semibold small py-3">
                                                Digunakan</th>
                                            <th style="width:80px; border-radius: 0 12px 12px 0;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="ingCatList">
                                        @foreach($ingredientCategories as $cat)
                                            @php $count = \App\Models\Ingredient::where('category', $cat->name)->count(); @endphp
                                            <tr data-id="{{ $cat->id }}" class="ing-cat-row"
                                                style="border-bottom: 1px solid #f1f5f9;">
                                                <td class="text-center text-muted drag-handle py-3" style="cursor:grab">
                                                    <i class="bi bi-grid-3x2-gap-fill text-secondary opacity-50"></i>
                                                </td>
                                                <td class="py-3">
                                                    <form method="POST"
                                                        action="{{ route('master.ingredient-categories.update', $cat) }}"
                                                        class="d-flex gap-2 align-items-center">
                                                        @csrf @method('PUT')
                                                        <input type="text" name="label" value="{{ $cat->label }}"
                                                            class="form-control premium-input-sm" style="max-width:240px">
                                                        <button type="submit" class="btn btn-light border btn-sm text-primary"
                                                            style="border-radius: 8px;" title="Simpan Perubahan">
                                                            <i class="bi bi-check2"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                                <td class="text-center py-3">
                                                    <span
                                                        class="{{ $count > 0 ? 'badge-premium-active' : 'badge-premium-inactive' }} small">
                                                        {{ $count }} bahan
                                                    </span>
                                                </td>
                                                <td class="text-center py-3">
                                                    @if($count === 0)
                                                        <form method="POST"
                                                            action="{{ route('master.ingredient-categories.destroy', $cat) }}"
                                                            onsubmit="return confirm('Hapus kategori {{ $cat->label }}?')">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="btn btn-light border btn-sm text-danger"
                                                                style="border-radius: 8px; background-color: #fff5f5; border-color: #fee2e2 !important;"
                                                                title="Hapus Kategori">
                                                                <i class="bi bi-trash3"></i>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <span class="text-muted small fw-medium"
                                                            title="Tidak dapat dihapus karena sedang digunakan">—</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                        @if($ingredientCategories->isEmpty())
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-5 fw-medium">Belum ada kategori
                                                    bahan baku</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="web3-form-card mb-4" style="height: auto;">
                            <div class="section-title border-bottom pb-3">
                                <i class="bi bi-plus-circle-fill me-2" style="color: #0ea5e9;"></i>Tambah Baru
                            </div>
                            <form method="POST" action="{{ route('master.ingredient-categories.store') }}">
                                @csrf
                                <div class="mb-4 mt-3">
                                    <label class="premium-label">Nama Kategori Bahan <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="label" class="form-control premium-input"
                                        placeholder="cth: Sauce, Powder, Syrup" value="{{ old('label') }}" required>
                                </div>
                                <button type="submit" class="btn btn-dark w-100 fw-bold py-2"
                                    style="background-color: #0f172a; border-radius: 12px;">
                                    Tambah Kategori
                                </button>
                            </form>
                        </div>

                        <div class="info-card-premium">
                            <div class="fw-bold text-dark mb-2"><i
                                    class="bi bi-info-circle-fill text-primary me-2"></i>Informasi</div>
                            <p class="mb-2">Kategori akan tersedia di form <strong>Edit Bahan Baku</strong>.</p>
                            <p class="mb-2">Urutan tabel di sebelah kiri akan menentukan tampilan pada menu <strong>Saldo
                                    Stok</strong> dan <strong>Pencatatan Harian</strong>.</p>
                            <p class="mb-0 text-danger fw-medium"><i class="bi bi-exclamation-triangle me-1"></i> Kategori
                                hanya bisa dihapus jika tidak ada bahan yang menggunakannya.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===================== TAB MENU ===================== --}}
            <div class="tab-pane fade" id="pane-menu" role="tabpanel">
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="web3-form-card">
                            <div class="section-title border-bottom pb-3 mb-0">
                                <span><i class="bi bi-list-ul me-2 text-primary"></i>Daftar Kategori Menu</span>
                                <span class="text-muted small fw-medium"
                                    style="font-size: 0.8rem; background: #f1f5f9; padding: 4px 10px; border-radius: 8px;">
                                    <i class="bi bi-grip-vertical me-1"></i>Drag untuk ubah urutan
                                </span>
                            </div>
                            <div class="card-body p-0 mt-2">
                                <table class="table table-hover mb-0 align-middle table-borderless">
                                    <thead style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                                        <tr>
                                            <th style="width:36px; border-radius: 12px 0 0 12px;"></th>
                                            <th class="text-muted fw-semibold small py-3">Nama Kategori</th>
                                            <th style="width:130px" class="text-center text-muted fw-semibold small py-3">
                                                Digunakan</th>
                                            <th style="width:80px; border-radius: 0 12px 12px 0;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="menuCatList">
                                        @foreach($menuCategories as $cat)
                                            @php $count = \App\Models\Menu::where('category_id', $cat->id)->count(); @endphp
                                            <tr data-id="{{ $cat->id }}" class="menu-cat-row"
                                                style="border-bottom: 1px solid #f1f5f9;">
                                                <td class="text-center text-muted drag-handle py-3" style="cursor:grab">
                                                    <i class="bi bi-grid-3x2-gap-fill text-secondary opacity-50"></i>
                                                </td>
                                                <td class="py-3">
                                                    <form method="POST"
                                                        action="{{ route('master.menu-categories.update', $cat) }}"
                                                        class="d-flex gap-2 align-items-center">
                                                        @csrf @method('PUT')
                                                        <input type="text" name="name" value="{{ $cat->name }}"
                                                            class="form-control premium-input-sm" style="max-width:240px">
                                                        <button type="submit" class="btn btn-light border btn-sm text-primary"
                                                            style="border-radius: 8px;" title="Simpan Perubahan">
                                                            <i class="bi bi-check2"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                                <td class="text-center py-3">
                                                    <span
                                                        class="{{ $count > 0 ? 'badge-premium-active' : 'badge-premium-inactive' }} small">
                                                        {{ $count }} menu
                                                    </span>
                                                </td>
                                                <td class="text-center py-3">
                                                    @if($count === 0)
                                                        <form method="POST"
                                                            action="{{ route('master.menu-categories.destroy', $cat) }}"
                                                            onsubmit="return confirm('Hapus kategori {{ $cat->name }}?')">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="btn btn-light border btn-sm text-danger"
                                                                style="border-radius: 8px; background-color: #fff5f5; border-color: #fee2e2 !important;"
                                                                title="Hapus Kategori">
                                                                <i class="bi bi-trash3"></i>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <span class="text-muted small fw-medium"
                                                            title="Tidak dapat dihapus karena sedang digunakan">—</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                        @if($menuCategories->isEmpty())
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-5 fw-medium">Belum ada kategori
                                                    menu</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="web3-form-card mb-4" style="height: auto;">
                            <div class="section-title border-bottom pb-3">
                                <i class="bi bi-plus-circle-fill me-2" style="color: #0ea5e9;"></i>Tambah Baru
                            </div>
                            <form method="POST" action="{{ route('master.menu-categories.store') }}">
                                @csrf
                                <div class="mb-4 mt-3">
                                    <label class="premium-label">Nama Kategori Menu <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control premium-input"
                                        placeholder="cth: Ice Cream, Milk Tea" value="{{ old('name') }}" required>
                                </div>
                                <button type="submit" class="btn btn-dark w-100 fw-bold py-2"
                                    style="background-color: #0f172a; border-radius: 12px;">
                                    Tambah Kategori
                                </button>
                            </form>
                        </div>

                        <div class="info-card-premium">
                            <div class="fw-bold text-dark mb-2"><i
                                    class="bi bi-info-circle-fill text-primary me-2"></i>Informasi</div>
                            <p class="mb-2">Kategori akan tersedia di form <strong>Tambah/Edit Menu</strong>.</p>
                            <p class="mb-0 text-danger fw-medium"><i class="bi bi-exclamation-triangle me-1"></i> Kategori
                                hanya bisa dihapus jika tidak ada menu yang menggunakannya.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>{{-- end tab-content --}}
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>
    <script>
        // Buka tab yang sesuai berdasarkan hash URL (#menu atau #bahan)
        (function () {
            var hash = window.location.hash;
            if (hash === '#menu') {
                var tab = document.getElementById('tab-menu');
                if (tab) bootstrap.Tab.getOrCreateInstance(tab).show();
            }
        })();

        // Drag & drop kategori bahan
        Sortable.create(document.getElementById('ingCatList'), {
            handle: '.drag-handle',
            animation: 150,
            onEnd: function () {
                var order = Array.from(document.querySelectorAll('#ingCatList .ing-cat-row'))
                    .map(function (r) { return r.dataset.id; });
                fetch('{{ route("master.ingredient-categories.reorder") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ order: order })
                });
            }
        });

        // Drag & drop kategori menu
        Sortable.create(document.getElementById('menuCatList'), {
            handle: '.drag-handle',
            animation: 150,
            onEnd: function () {
                var order = Array.from(document.querySelectorAll('#menuCatList .menu-cat-row'))
                    .map(function (r) { return r.dataset.id; });
                fetch('{{ route("master.menu-categories.reorder") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ order: order })
                });
            }
        });
    </script>
@endpush