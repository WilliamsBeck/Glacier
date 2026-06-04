@extends('layouts.app')
@section('title','Catatan Waste')
@section('content')
<div class="page-header d-flex justify-content-between align-items-start">
    <div><h4 class="page-title">Catatan Waste</h4><p class="text-muted mb-0">Pencatatan bahan yang terbuang/rusak</p></div>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.waste', ['store_id' => request('store_id')]) }}" class="btn btn-outline-secondary">
            <i class="bi bi-bar-chart me-1"></i>Analisis Waste
        </a>
        <a href="{{ route('waste.logs.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> Input Waste</a>
    </div>
</div>
<div class="card mb-3"><div class="card-body py-2">
    <form method="GET" class="row g-2">
        <div class="col-md-3"><select name="store_id" class="form-select form-select-sm"><option value="">Semua Toko</option>@foreach($stores as $s)<option value="{{ $s->id }}" {{ request('store_id')==$s->id?'selected':'' }}>{{ $s->name }}</option>@endforeach</select></div>
        <div class="col-md-2"><input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}" placeholder="Dari"></div>
        <div class="col-md-2"><input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}" placeholder="Sampai"></div>
        <div class="col-md-2 d-flex gap-1"><button type="submit" class="btn btn-primary btn-sm">Cari</button><a href="{{ route('waste.logs.index') }}" class="btn btn-outline-secondary btn-sm">×</a></div>
    </form>
</div></div>
<div class="card"><div class="card-body p-0"><div class="table-responsive">
    <table class="table table-hover mb-0">
        <thead class="table-dark"><tr><th>Tanggal</th><th>Toko</th><th>Bahan Rusak</th><th class="text-end">Total Kerugian</th><th>Catatan</th><th>Aksi</th></tr></thead>
        <tbody>
            @forelse($logs as $log)
            <tr>
                <td class="text-nowrap">{{ \Carbon\Carbon::parse($log->waste_date)->format('d M Y') }}</td>
                <td class="text-nowrap">{{ $log->store->name }}</td>
                <td>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach($log->items as $item)
                            <span class="badge bg-light text-dark border" style="font-size:.75rem;font-weight:500">
                                {{ $item->ingredient->name }}
                            </span>
                        @endforeach
                    </div>
                </td>
                <td class="text-end text-danger fw-semibold text-nowrap">Rp {{ number_format($log->total_loss_amount, 0, ',', '.') }}</td>
                <td class="text-muted small">{{ Str::limit($log->notes, 40) }}</td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="{{ route('waste.logs.show', $log) }}" class="btn btn-sm btn-outline-primary" title="Detail"><i class="bi bi-eye"></i></a>
                        <a href="{{ route('waste.logs.edit', $log) }}" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('waste.logs.destroy', $log) }}"
                              onsubmit="return confirm('Hapus catatan waste ini? Stok akan dikembalikan.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash3"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center py-4 text-muted"><i class="bi bi-trash3 fs-2 d-block mb-2 opacity-25"></i>Belum ada data waste</td></tr>
            @endforelse
        </tbody>
    </table>
</div></div>
@if($logs->hasPages())<div class="card-footer">{{ $logs->withQueryString()->links() }}</div>@endif
</div>
@endsection
