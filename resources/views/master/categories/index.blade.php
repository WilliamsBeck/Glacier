@extends('layouts.app')
@section('title', 'Kelola Kategori')
@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <h4 class="page-title">Kelola Kategori</h4>
</div>

{{-- TABS --}}
<ul class="nav nav-tabs mb-4" id="catTabs" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" id="tab-bahan" data-bs-toggle="tab" data-bs-target="#pane-bahan" type="button">
            <i class="bi bi-box-seam me-1"></i>Kategori Bahan Baku
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" id="tab-menu" data-bs-toggle="tab" data-bs-target="#pane-menu" type="button">
            <i class="bi bi-cup-straw me-1"></i>Kategori Menu
        </button>
    </li>
</ul>

<div class="tab-content" id="catTabContent">

    {{-- ===================== TAB BAHAN BAKU ===================== --}}
    <div class="tab-pane fade show active" id="pane-bahan" role="tabpanel">
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                        <span>Kategori Bahan Baku</span>
                        <span class="text-muted small fw-normal"><i class="bi bi-grip-vertical me-1"></i>Drag untuk ubah urutan</span>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:36px"></th>
                                    <th>Nama Kategori</th>
                                    <th style="width:130px" class="text-center">Digunakan</th>
                                    <th style="width:80px"></th>
                                </tr>
                            </thead>
                            <tbody id="ingCatList">
                                @foreach($ingredientCategories as $cat)
                                @php $count = \App\Models\Ingredient::where('category', $cat->name)->count(); @endphp
                                <tr data-id="{{ $cat->id }}" class="ing-cat-row">
                                    <td class="text-center text-muted drag-handle" style="cursor:grab">
                                        <i class="bi bi-grip-vertical"></i>
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('master.ingredient-categories.update', $cat) }}"
                                              class="d-flex gap-2 align-items-center">
                                            @csrf @method('PUT')
                                            <input type="text" name="label" value="{{ $cat->label }}"
                                                   class="form-control form-control-sm" style="max-width:220px">
                                            <button type="submit" class="btn btn-sm btn-outline-primary py-0 px-2">
                                                <i class="bi bi-check2"></i>
                                            </button>
                                        </form>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $count > 0 ? 'bg-primary' : 'bg-light text-muted border' }}">
                                            {{ $count }} bahan
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($count === 0)
                                        <form method="POST" action="{{ route('master.ingredient-categories.destroy', $cat) }}"
                                              onsubmit="return confirm('Hapus kategori {{ $cat->label }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                        @else
                                        <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                                @if($ingredientCategories->isEmpty())
                                <tr><td colspan="4" class="text-center text-muted py-3">Belum ada kategori</td></tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header fw-semibold">Tambah Kategori Bahan</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('master.ingredient-categories.store') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nama Kategori <span class="text-danger">*</span></label>
                                <input type="text" name="label" class="form-control"
                                       placeholder="cth: Sauce" value="{{ old('label') }}">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-plus-circle me-1"></i>Tambah
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card mt-3 border-0 bg-light">
                    <div class="card-body small text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Kategori tersedia di form <strong>Edit Bahan Baku</strong>.
                        Urutan menentukan tampilan di <strong>Saldo Stok</strong> dan <strong>Pencatatan Harian</strong>.<br><br>
                        Kategori hanya bisa dihapus jika tidak ada bahan yang menggunakannya.
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== TAB MENU ===================== --}}
    <div class="tab-pane fade" id="pane-menu" role="tabpanel">
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                        <span>Kategori Menu</span>
                        <span class="text-muted small fw-normal"><i class="bi bi-grip-vertical me-1"></i>Drag untuk ubah urutan</span>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:36px"></th>
                                    <th>Nama Kategori</th>
                                    <th style="width:130px" class="text-center">Digunakan</th>
                                    <th style="width:80px"></th>
                                </tr>
                            </thead>
                            <tbody id="menuCatList">
                                @foreach($menuCategories as $cat)
                                @php $count = \App\Models\Menu::where('category_id', $cat->id)->count(); @endphp
                                <tr data-id="{{ $cat->id }}" class="menu-cat-row">
                                    <td class="text-center text-muted drag-handle" style="cursor:grab">
                                        <i class="bi bi-grip-vertical"></i>
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('master.menu-categories.update', $cat) }}"
                                              class="d-flex gap-2 align-items-center">
                                            @csrf @method('PUT')
                                            <input type="text" name="name" value="{{ $cat->name }}"
                                                   class="form-control form-control-sm" style="max-width:220px">
                                            <button type="submit" class="btn btn-sm btn-outline-primary py-0 px-2">
                                                <i class="bi bi-check2"></i>
                                            </button>
                                        </form>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $count > 0 ? 'bg-primary' : 'bg-light text-muted border' }}">
                                            {{ $count }} menu
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($count === 0)
                                        <form method="POST" action="{{ route('master.menu-categories.destroy', $cat) }}"
                                              onsubmit="return confirm('Hapus kategori {{ $cat->name }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                        @else
                                        <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                                @if($menuCategories->isEmpty())
                                <tr><td colspan="4" class="text-center text-muted py-3">Belum ada kategori</td></tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header fw-semibold">Tambah Kategori Menu</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('master.menu-categories.store') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nama Kategori <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control"
                                       placeholder="cth: Ice Cream, Milk Tea" value="{{ old('name') }}">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-plus-circle me-1"></i>Tambah
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card mt-3 border-0 bg-light">
                    <div class="card-body small text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Kategori tersedia di form <strong>Tambah/Edit Menu</strong>.<br><br>
                        Kategori hanya bisa dihapus jika tidak ada menu yang menggunakannya.
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>{{-- end tab-content --}}

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>
<script>
// Buka tab yang sesuai berdasarkan hash URL (#menu atau #bahan)
(function() {
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
    onEnd: function() {
        var order = Array.from(document.querySelectorAll('#ingCatList .ing-cat-row'))
            .map(function(r) { return r.dataset.id; });
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
    onEnd: function() {
        var order = Array.from(document.querySelectorAll('#menuCatList .menu-cat-row'))
            .map(function(r) { return r.dataset.id; });
        fetch('{{ route("master.menu-categories.reorder") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ order: order })
        });
    }
});
</script>
@endpush
