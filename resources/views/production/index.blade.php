@extends('layouts.app')
@section('title','Produksi')
@section('content')
<div class="page-header d-flex justify-content-between align-items-start">
    <div>
        <h1 class="page-title">Produksi Bahan Setengah Jadi</h1>
        <p class="page-subtitle">Catatan produksi bahan setengah jadi per toko</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('production.analysis', ['store_id' => request('store_id')]) }}" class="btn btn-outline-secondary">
            <i class="bi bi-bar-chart me-1"></i>Analisis Produksi
        </a>
        <a href="{{ route('production.logs.export', request()->query()) }}" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
        </a>
        <a href="{{ route('production.logs.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> Input Produksi</a>
    </div>
</div>
<div class="card mb-3"><div class="card-body py-2">
    <form method="GET" class="row g-2">
        <div class="col-md-3"><select name="store_id" class="form-select form-select-sm"><option value="">Semua Toko</option>@foreach($stores as $s)<option value="{{ $s->id }}" {{ request('store_id')==$s->id?'selected':'' }}>{{ $s->name }}</option>@endforeach</select></div>
        <div class="col-md-3"><input type="date" name="date" class="form-control form-control-sm" value="{{ request('date') }}"></div>
        <div class="col-md-2"><button type="submit" class="btn btn-primary btn-sm">Cari</button></div>
    </form>
</div></div>
<div class="card"><div class="card-body p-0"><div class="table-responsive">
    <table class="table table-index mb-0">
        <thead><tr><th>Tanggal</th><th>Toko</th><th class="col-name">Bahan Setengah Jadi</th><th>Qty Diproduksi</th><th>Est. Biaya Bahan</th><th class="col-name">Catatan</th><th style="width:70px">Aksi</th></tr></thead>
        <tbody>
            @forelse($logs as $log)
            @php
                $sfUnit    = $log->semiFinished->unit_base ?? '';
                $sfIsGram  = strtolower($sfUnit) === 'gram';
                $totalCost = $log->items->sum(fn($i) => $i->qty_consumed * $i->price_per_base);
            @endphp
            <tr>
                <td class="text-nowrap">{{ \Carbon\Carbon::parse($log->production_date)->format('d M Y') }}</td>
                <td class="text-nowrap">{{ $log->store->name }}</td>
                <td class="col-name fw-semibold">{{ $log->semiFinished->name }}</td>
                <td class="text-nowrap">
                    {{ number_format($log->qty_produced, $sfIsGram ? 0 : 2, ',', '.') }} {{ $sfUnit }}
                </td>
                <td class="text-nowrap fw-semibold">
                    @if($totalCost > 0)
                        Rp {{ number_format($totalCost, 0, ',', '.') }}
                    @else
                        <span class="text-soft">—</span>
                    @endif
                </td>
                <td class="col-name text-soft small">{{ Str::limit($log->notes, 35) }}</td>
                <td>
                    <x-action-menu>
                        <x-action-view :href="route('production.logs.show', $log)" />
                        <x-action-edit :href="route('production.logs.edit', $log)" />
                        <x-action-delete :action="route('production.logs.destroy', $log)"
                                         confirm="Hapus data produksi ini?" />
                    </x-action-menu>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="py-4 text-soft"><i class="bi bi-gear fs-2 d-block mb-2 opacity-25"></i>Belum ada data produksi</td></tr>
            @endforelse
        </tbody>
    </table>
</div></div>
@if($logs->hasPages())<div class="card-footer">{{ $logs->withQueryString()->links() }}</div>@endif
</div>
@endsection
