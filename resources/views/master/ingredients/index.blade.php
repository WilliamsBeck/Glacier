@extends('layouts.app')
@section('title','Bahan')
@section('content')
<div class="page-header d-flex justify-content-between align-items-start">
    <div><h4 class="page-title">Bahan Baku & Setengah Jadi</h4></div>
    <a href="{{ route('master.ingredients.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> Tambah Bahan</a>
</div>
<div class="card mb-3"><div class="card-body py-2">
    <form method="GET" class="row g-2">
        <div class="col-md-4"><input type="text" name="search" class="form-control form-control-sm" placeholder="Cari nama..." value="{{ request('search') }}"></div>
        <div class="col-md-3"><select name="type" class="form-select form-select-sm"><option value="">Semua Tipe</option><option value="raw" {{ request('type')==='raw'?'selected':'' }}>Baku</option><option value="semi_finished" {{ request('type')==='semi_finished'?'selected':'' }}>Setengah Jadi</option></select></div>
        <div class="col-md-2 d-flex gap-1"><button type="submit" class="btn btn-primary btn-sm">Cari</button><a href="{{ route('master.ingredients.index') }}" class="btn btn-outline-secondary btn-sm">×</a></div>
    </form>
</div></div>
<div class="card"><div class="card-body p-0"><div class="table-responsive">
    <table class="table table-hover mb-0">
        <thead class="table-dark"><tr><th>#</th><th>Nama</th><th>Tipe</th><th>Satuan</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
            @forelse($ingredients as $ing)
            <tr>
                <td class="text-muted small">{{ $ingredients->firstItem() + $loop->index }}</td>
                <td class="fw-semibold">{{ $ing->name }}</td>
                <td>@if($ing->type==='raw')<span class="badge bg-primary">Raw</span>@else<span class="badge" style="background:#6f42c1;color:#fff">Semi</span>@endif</td>
                <td><span class="badge bg-light text-dark border">{{ $ing->unit_base }}</span></td>
                <td><span class="badge {{ $ing->is_active?'bg-success':'bg-secondary' }}">{{ $ing->is_active?'Aktif':'Nonaktif' }}</span></td>
                <td>
                    <a href="{{ route('master.ingredients.edit', $ing) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                    <form method="POST" action="{{ route('master.ingredients.destroy', $ing) }}" class="d-inline" onsubmit="return confirm('Hapus bahan ini?')">
                        @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada data bahan</td></tr>
            @endforelse
        </tbody>
    </table>
</div></div>
@if($ingredients->hasPages())<div class="card-footer">{{ $ingredients->withQueryString()->links() }}</div>@endif
</div>
@endsection
