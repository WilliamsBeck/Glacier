@extends('layouts.app')
@section('title','Stok Opname')
@section('content')
<div class="page-header d-flex justify-content-between align-items-start">
    <div><h4 class="page-title">Stok Opname</h4><p class="text-muted mb-0">Jadwal: tgl 15 (periode 1–15) dan akhir bulan (periode 1–30/31) tiap toko</p></div>
    <div class="d-flex gap-2">
        <a href="{{ route('opname.opnames.import.form') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-upload me-1"></i>Import Excel
        </a>
        <a href="{{ route('opname.opnames.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i>Buat Opname
        </a>
    </div>
</div>
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show">
    <i class="bi bi-exclamation-triangle me-1"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card mb-3"><div class="card-body py-2">
    <form method="GET" class="row g-2">
        <div class="col-md-2"><select name="store_id" class="form-select form-select-sm"><option value="">Semua Toko</option>@foreach($stores as $s)<option value="{{ $s->id }}" {{ request('store_id')==$s->id?'selected':'' }}>{{ $s->name }}</option>@endforeach</select></div>
        <div class="col-md-2"><select name="period_type" class="form-select form-select-sm"><option value="">Semua Periode</option><option value="mid_month" {{ request('period_type')==='mid_month'?'selected':'' }}>Periode 1–15</option><option value="end_month" {{ request('period_type')==='end_month'?'selected':'' }}>Periode 1–30/31</option></select></div>
        <div class="col-md-1"><input type="number" name="month" class="form-control form-control-sm" placeholder="Bln" min="1" max="12" value="{{ request('month') }}"></div>
        <div class="col-md-1"><input type="number" name="year" class="form-control form-control-sm" placeholder="Thn" value="{{ request('year', date('Y')) }}"></div>
        <div class="col-md-2 d-flex gap-1"><button type="submit" class="btn btn-primary btn-sm">Cari</button><a href="{{ route('opname.opnames.index') }}" class="btn btn-outline-secondary btn-sm">×</a></div>
    </form>
</div></div>
<div class="card"><div class="card-body p-0"><div class="table-responsive">
    <table class="table table-hover mb-0">
        <thead class="table-dark"><tr><th>Toko</th><th>Tanggal Opname</th><th>Periode</th><th>Status</th><th>Dilakukan Oleh</th><th>Aksi</th></tr></thead>
        <tbody>
            @forelse($opnames as $op)
            <tr>
                <td class="fw-semibold">{{ $op->store->name }}</td>
                <td>{{ \Carbon\Carbon::parse($op->opname_date)->format('d M Y') }}</td>
                <td><span class="badge bg-light text-dark border">{{ $op->period_type === 'mid_month' ? 'Tgl 1–15' : 'Tgl 1–30/31' }} {{ $op->period_month }}/{{ $op->period_year }}</span></td>
                <td>
                    @if($op->status==='draft') <span class="badge bg-warning text-dark">Draft</span>
                    @else <span class="badge bg-success">Disetujui</span> @endif
                </td>
                <td>{{ $op->performedBy->name }}</td>
                <td class="d-flex gap-1">
                    <a href="{{ route('opname.opnames.show', $op) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                    @if($op->status !== 'approved')
                        <form method="POST" action="{{ route('opname.opnames.destroy', $op) }}" onsubmit="return confirm('Hapus opname ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center py-4 text-muted"><i class="bi bi-clipboard-check fs-2 d-block mb-2 opacity-25"></i>Belum ada opname</td></tr>
            @endforelse
        </tbody>
    </table>
</div></div>
@if($opnames->hasPages())<div class="card-footer">{{ $opnames->withQueryString()->links() }}</div>@endif
</div>
@endsection
