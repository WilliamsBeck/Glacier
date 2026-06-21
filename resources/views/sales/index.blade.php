@extends('layouts.app')
@section('title','Penjualan')
@section('content')
<div class="page-header d-flex justify-content-between align-items-start">
    <div>
        <h1 class="page-title">Penjualan Menu</h1>
        <p class="page-subtitle">Rekap omset &amp; kuantitas terjual per periode</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('sales.monthly.export', request()->query()) }}" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
        </a>
        <a href="{{ route('sales.monthly.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Tambah Penjualan
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card mb-3"><div class="card-body py-2">
    <form method="GET" class="row g-2 align-items-end">
        @php $bulanList = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember']; @endphp
        <div class="col-md-3">
            <label class="form-label small mb-1">Toko</label>
            <select name="store_id" class="form-select">
                <option value="">Semua Toko</option>
                @foreach($stores as $s)
                    <option value="{{ $s->id }}" {{ request('store_id')==$s->id?'selected':'' }}>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1">Bulan</label>
            <select name="month" class="form-select">
                <option value="">Semua</option>
                @for($m=1;$m<=12;$m++)
                    <option value="{{ $m }}" {{ (string)request('month')===(string)$m?'selected':'' }}>{{ $bulanList[$m] }}</option>
                @endfor
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1">Tahun</label>
            <select name="year" class="form-select">
                <option value="">Semua</option>
                @for($y=now()->year+1;$y>=now()->year-4;$y--)
                    <option value="{{ $y }}" {{ (string)request('year')===(string)$y?'selected':'' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-1">Periode</label>
            <select name="period_type" class="form-select">
                <option value="">Semua</option>
                <option value="end_month" {{ request('period_type')==='end_month'?'selected':'' }}>Akhir Bulan (1–30/31)</option>
                <option value="mid_month" {{ request('period_type')==='mid_month'?'selected':'' }}>Tengah Bulan (1–15)</option>
            </select>
        </div>
        <div class="col-md-auto ms-auto d-flex gap-2 align-items-end">
            <button type="submit" class="btn btn-primary">Cari</button>
            <a href="{{ route('sales.monthly.index') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>
</div></div>

<div class="card"><div class="card-body p-0"><div class="table-responsive">
    <table class="table table-index table-balanced mb-0 align-middle">
        <thead>
            <tr>
                <th class="col-name">Toko</th>
                <th>Bulan / Tahun</th>
                <th>Periode</th>
                <th>Omset</th>
                <th>Qty Terjual</th>
                <th style="width:70px">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($groups as $g)
            @php
                $key     = "{$g->store_id}_{$g->month}_{$g->year}_{$g->period_type}";
                $revenue = $revenueMap[$key] ?? null;
                $store   = $stores->firstWhere('id', $g->store_id);
                $params  = ['store_id' => $g->store_id, 'month' => $g->month, 'year' => $g->year, 'period_type' => $g->period_type];
                $monthNames = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
            @endphp
            <tr>
                <td class="col-name fw-semibold">{{ $store?->name ?? '—' }}</td>
                <td>{{ $monthNames[$g->month] }} {{ $g->year }}</td>
                <td>
                    <span class="badge bg-secondary">
                        {{ $g->period_type === 'mid_month' ? 'Tengah Bulan' : 'Akhir Bulan' }}
                    </span>
                </td>
                <td>
                    @if($revenue && $revenue->total_revenue > 0)
                        <span class="fw-semibold">Rp {{ number_format($revenue->total_revenue, 0, ',', '.') }}</span>
                    @else
                        <span class="text-soft small">—</span>
                    @endif
                </td>
                <td class="fw-semibold">{{ number_format($g->total_sold, 0, ',', '.') }} <span class="text-soft fw-normal small">pcs</span></td>
                <td>
                    <x-action-menu>
                        <x-action-view :href="route('sales.period.show', $params)" />
                        <x-action-edit :href="route('sales.period.edit', $params)" />
                        <x-action-delete :action="route('sales.period.destroy', $params)"
                                         confirm="Hapus seluruh data penjualan periode ini?" />
                    </x-action-menu>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada data penjualan</td></tr>
            @endforelse
        </tbody>
    </table>
</div></div></div>
@endsection
