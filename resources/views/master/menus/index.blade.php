@extends('layouts.app')
@section('title','Menu')
@section('content')
<div class="page-header d-flex justify-content-between align-items-start">
    <div><h4 class="page-title">Daftar Menu</h4></div>
    <a href="{{ route('master.menus.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> Tambah Menu</a>
</div>
<div class="card mb-3"><div class="card-body py-2">
    <form method="GET" class="row g-2">
        <div class="col-md-4"><input type="text" name="search" class="form-control form-control-sm" placeholder="Cari menu..." value="{{ request('search') }}"></div>
        <div class="col-md-2"><button type="submit" class="btn btn-primary btn-sm">Cari</button></div>
    </form>
</div></div>
<div class="card"><div class="card-body p-0"><div class="table-responsive">
    <table class="table table-hover mb-0">
        <thead class="table-dark"><tr><th>#</th><th>Nama Menu</th><th>Jumlah Versi Resep</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
            @forelse($menus as $menu)
            <tr>
                <td class="text-muted small">{{ $menus->firstItem() + $loop->index }}</td>
                <td class="fw-semibold">{{ $menu->name }}</td>
                <td>
                    @if($menu->recipe_versions_count > 0)
                        <span class="badge bg-info text-dark">{{ $menu->recipe_versions_count }} versi resep</span>
                    @else
                        <span class="text-muted small fst-italic">Belum ada resep</span>
                    @endif
                </td>
                <td><span class="badge {{ $menu->is_active?'bg-success':'bg-secondary' }}">{{ $menu->is_active?'Aktif':'Nonaktif' }}</span></td>
                <td>
                    <a href="{{ route('master.menus.edit', $menu) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Edit & Resep</a>
                    <form method="POST" action="{{ route('master.menus.destroy', $menu) }}" class="d-inline" onsubmit="return confirm('Hapus menu ini beserta semua resepnya?')">
                        @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center py-4 text-muted">Belum ada menu</td></tr>
            @endforelse
        </tbody>
    </table>
</div></div>
@if($menus->hasPages())<div class="card-footer">{{ $menus->withQueryString()->links() }}</div>@endif
</div>
@endsection
