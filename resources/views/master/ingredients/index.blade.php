@extends('layouts.app')
@section('title', 'Bahan Baku')

@section('content')
<div class="page-header d-flex justify-content-between align-items-start">
    <div>
        <h1 class="page-title">Bahan Baku &amp; Setengah Jadi</h1>
        <p class="page-subtitle">Manajemen master data bahan</p>
    </div>
    <a href="{{ route('master.ingredients.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Tambah Bahan
    </a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control"
                       placeholder="Cari nama bahan baku…" value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="type" class="form-select">
                    <option value="">Semua Tipe</option>
                    <option value="raw" {{ request('type') === 'raw' ? 'selected' : '' }}>Bahan Baku (Raw)</option>
                    <option value="semi_finished" {{ request('type') === 'semi_finished' ? 'selected' : '' }}>Setengah Jadi</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2 justify-content-end">
                <button type="submit" class="btn btn-primary">Cari</button>
                <a href="{{ route('master.ingredients.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-index mb-0">
            <thead>
                <tr>
                    <th width="48">#</th>
                    <th class="col-name">Nama Bahan</th>
                    <th>Tipe</th>
                    <th>Satuan</th>
                    <th>Status</th>
                    <th width="80">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ingredients as $ing)
                    <tr>
                        <td class="text-soft">{{ $ingredients->firstItem() + $loop->index }}</td>
                        <td class="col-name">
                            <span class="fw-medium">{{ $ing->name }}</span>
                        </td>
                        <td>
                            @if($ing->type === 'raw')
                                <span class="badge bg-primary">Baku (Raw)</span>
                            @else
                                <span class="badge bg-info">Setengah Jadi</span>
                            @endif
                        </td>
                        <td><span class="text-soft">{{ $ing->unit_base }}</span></td>
                        <td>
                            @if($ing->is_active)
                                <span class="badge bg-success">
                                    <span class="d-inline-block rounded-circle me-1" style="width:5px;height:5px;background:currentColor"></span>
                                    Aktif
                                </span>
                            @else
                                <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <x-action-menu>
                                <x-action-edit :href="route('master.ingredients.edit', $ing)" />
                                <x-action-delete :action="route('master.ingredients.destroy', $ing)"
                                                 confirm="Hapus bahan baku ini?" />
                            </x-action-menu>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-5 text-soft">
                            <i class="bi bi-box2 fs-3 d-block mb-2 opacity-25"></i>
                            Belum ada data bahan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($ingredients->hasPages())
        <div class="card-footer d-flex justify-content-between align-items-center">
            <div class="text-soft">
                Menampilkan {{ $ingredients->firstItem() }}–{{ $ingredients->lastItem() }} dari {{ $ingredients->total() }}
            </div>
            <div>{{ $ingredients->withQueryString()->links() }}</div>
        </div>
    @endif
</div>
@endsection
