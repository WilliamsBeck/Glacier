@extends('layouts.app')
@section('title','Supplier')
@section('content')
<div class="page-header d-flex justify-content-between align-items-start">
    <div><h4 class="page-title">Manajemen Supplier</h4></div>
    <a href="{{ route('master.suppliers.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> Tambah Supplier</a>
</div>
<div class="card mb-3"><div class="card-body py-2">
    <form method="GET" class="row g-2">
        <div class="col-md-4"><input type="text" name="search" class="form-control form-control-sm" placeholder="🔍 Cari nama..." value="{{ request('search') }}"></div>
        <div class="col-md-3"><select name="type" class="form-select form-select-sm"><option value="">Semua Tipe</option><option value="zhisheng" {{ request('type')==='zhisheng'?'selected':'' }}>Pusat</option><option value="local_supplier" {{ request('type')==='local_supplier'?'selected':'' }}>Supplier Lokal</option><option value="other" {{ request('type')==='other'?'selected':'' }}>Lainnya</option></select></div>
        <div class="col-md-2 d-flex gap-1"><button type="submit" class="btn btn-primary btn-sm">Cari</button><a href="{{ route('master.suppliers.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a></div>
    </form>
</div></div>
<div class="card"><div class="card-body p-0"><div class="table-responsive">
    <table class="table table-hover mb-0">
        <thead class="table-dark"><tr><th>#</th><th>Nama Supplier</th><th>Tipe</th><th>Kontak</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
            @forelse($suppliers as $sup)
            <tr>
                <td class="text-muted small">{{ $suppliers->firstItem() + $loop->index }}</td>
                <td class="fw-semibold">{{ $sup->name }}</td>
                <td><span class="badge {{ $sup->type==='zhisheng'?'bg-primary':($sup->type==='local_supplier'?'bg-success':'bg-secondary') }}">{{ $sup->type_label }}</span></td>
                <td>{{ $sup->contact ?? '-' }}</td>
                <td><span class="badge {{ $sup->is_active?'bg-success':'bg-secondary' }}">{{ $sup->is_active?'Aktif':'Nonaktif' }}</span></td>
                <td>
                    <a href="{{ route('master.suppliers.edit', $sup) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                    <form method="POST" action="{{ route('master.suppliers.destroy', $sup) }}" class="d-inline" onsubmit="return confirm('Hapus supplier ini?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada data supplier</td></tr>
            @endforelse
        </tbody>
    </table>
</div></div>
@if($suppliers->hasPages())<div class="card-footer">{{ $suppliers->withQueryString()->links() }}</div>@endif
</div>
@endsection
