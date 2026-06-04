@extends('layouts.app')
@section('title', 'Toko')

@section('content')
<div class="page-header d-flex justify-content-between align-items-start">
    <div>
        <h4 class="page-title">Manajemen Toko</h4>
        <p class="text-muted mb-0">Total {{ $stores->total() }} toko terdaftar</p>
    </div>
    <a href="{{ route('master.stores.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Tambah Toko
    </a>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="🔍 Cari nama atau kode toko..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="area" class="form-select form-select-sm">
                    <option value="">Semua Area</option>
                    @foreach($areas as $area)
                        <option value="{{ $area }}" {{ request('area') == $area ? 'selected' : '' }}>{{ $area }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">Cari</button>
                <a href="{{ route('master.stores.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th width="50">#</th>
                        <th>Kode</th>
                        <th>Nama Toko</th>
                        <th>Area</th>
                        <th>Status</th>
                        <th width="100">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stores as $s)
                    <tr>
                        <td class="text-muted small">{{ $stores->firstItem() + $loop->index }}</td>
                        <td><span class="badge bg-secondary">{{ $s->store_code }}</span></td>
                        <td class="fw-semibold">{{ $s->name }}</td>
                        <td>{{ $s->area }}</td>
                        <td>
                            <span class="badge {{ $s->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $s->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('master.stores.edit', $s) }}"
                               class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('master.stores.destroy', $s) }}"
                                  class="d-inline" onsubmit="return confirm('Hapus toko {{ $s->name }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-shop fs-1 d-block mb-2 opacity-25"></i>
                            Belum ada data toko
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($stores->hasPages())
    <div class="card-footer">{{ $stores->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
