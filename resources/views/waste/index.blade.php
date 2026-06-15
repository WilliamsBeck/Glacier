@extends('layouts.app')
@section('title','Catatan Waste')
@section('content')
<div class="page-header d-flex justify-content-between align-items-start">
    <div>
        <h1 class="page-title">Catatan Waste</h1>
        <p class="page-subtitle">Pencatatan bahan yang terbuang/rusak</p>
    </div>
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
    <table class="table table-index mb-0">
        <thead><tr><th>Tanggal</th><th>Toko</th><th class="col-name">Bahan Rusak</th><th>Total Kerugian</th><th class="col-name">Catatan</th><th style="width:70px">Aksi</th></tr></thead>
        <tbody>
            @forelse($logs as $log)
            <tr>
                <td class="text-nowrap">{{ \Carbon\Carbon::parse($log->waste_date)->format('d M Y') }}</td>
                <td class="text-nowrap">{{ $log->store->name }}</td>
                <td class="col-name">
                    <div class="d-flex flex-wrap gap-1">
                        @foreach($log->items as $item)
                            <span class="badge bg-secondary">{{ $item->ingredient->name }}</span>
                        @endforeach
                    </div>
                </td>
                <td class="text-danger fw-semibold text-nowrap">Rp {{ number_format($log->total_loss_amount, 0, ',', '.') }}</td>
                <td class="col-name text-soft small">{{ Str::limit($log->notes, 40) }}</td>
                <td>
                    <x-action-menu>
                        <x-action-view :href="route('waste.logs.show', $log)" />
                        <x-action-edit :href="route('waste.logs.edit', $log)" />
                        <x-action-delete :action="route('waste.logs.destroy', $log)"
                                         confirm="Hapus catatan waste ini? Stok akan dikembalikan." />
                    </x-action-menu>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="py-4 text-soft"><i class="bi bi-trash3 fs-2 d-block mb-2 opacity-25"></i>Belum ada data waste</td></tr>
            @endforelse
        </tbody>
    </table>
</div></div>
@if($logs->hasPages())<div class="card-footer">{{ $logs->withQueryString()->links() }}</div>@endif
</div>
@endsection
