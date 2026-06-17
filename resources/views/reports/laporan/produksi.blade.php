@extends('layouts.app')
@section('title', 'Laporan Data Produksi')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h4 class="page-title mb-0">Laporan Data Produksi</h4>
        <p class="text-muted mb-0 small">Rekap batch produksi dan biaya bahan per rentang tanggal</p>
    </div>
</div>

{{-- FILTER --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.laporan.produksi') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold small">Toko</label>
                <select name="store_id" class="form-select form-select-sm">
                    @foreach($stores as $s)
                        <option value="{{ $s->id }}" {{ $storeId == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold small">Dari Tanggal</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold small">Sampai Tanggal</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
            </div>
            <div class="col-md-5 d-flex gap-2 justify-content-end">
                <button type="submit" class="btn btn-primary btn-laporan">
                    <i class="bi bi-search me-1"></i>Tampilkan
                </button>
                <a href="{{ route('reports.laporan.produksi.export', request()->query()) }}"
                   class="btn btn-success btn-laporan">
                    <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
                </a>
            </div>
        </form>
    </div>
</div>

@if($storeId)

{{-- SUMMARY CARDS --}}
@php
$byProduct = $rows->groupBy(fn($r) => $r->product?->name ?? 'Lainnya')
    ->map(fn($g) => ['batch' => $g->count(), 'cost' => $g->sum('cost')]);
$days = \Carbon\Carbon::parse($dateFrom)->diffInDays(\Carbon\Carbon::parse($dateTo)) + 1;
@endphp

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card border-success">
            <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-gear fs-3"></i></div>
            <div class="stat-info">
                <div class="stat-number text-success">{{ $totalBatch }}</div>
                <div class="stat-label">Total Batch Produksi</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card border-primary">
            <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-cash-stack fs-3"></i></div>
            <div class="stat-info">
                <div class="stat-number text-primary" style="font-size:15px">Rp {{ number_format($grandTotal, 0, ',', '.') }}</div>
                <div class="stat-label">Total Biaya Produksi</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card border-info">
            <div class="stat-icon bg-info-subtle text-info"><i class="bi bi-calendar-range fs-3"></i></div>
            <div class="stat-info">
                <div class="stat-number text-info">{{ $days > 0 ? number_format($totalBatch / $days, 1, ',', '.') : 0 }}</div>
                <div class="stat-label">Rata-rata Batch/Hari</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card border-warning">
            <div class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-boxes fs-3"></i></div>
            <div class="stat-info">
                <div class="stat-number text-warning">{{ $byProduct->count() }}</div>
                <div class="stat-label">Varian Produk</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- MAIN TABLE --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header fw-semibold">
                <i class="bi bi-table me-1"></i>
                Detail Produksi —
                {{ \Carbon\Carbon::parse($dateFrom)->isoFormat('D MMM Y') }} s/d {{ \Carbon\Carbon::parse($dateTo)->isoFormat('D MMM Y') }}
            </div>
            <div class="card-body p-0">
                @if($rows->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                        Tidak ada data produksi untuk periode ini
                    </div>
                @else
                <div class="table-responsive">
                    <table class="table table-index mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th class="col-name">Produk</th>
                                <th class="text-end">Qty Diproduksi</th>
                                <th class="text-end">Biaya Bahan</th>
                                <th class="text-center">Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $row)
                            <tr>
                                <td class="text-muted small text-nowrap">
                                    {{ \Carbon\Carbon::parse($row->log->production_date)->isoFormat('D MMM Y') }}
                                </td>
                                <td class="col-name fw-semibold">{{ $row->product?->name ?? '-' }}</td>
                                <td class="text-end">
                                    {{ number_format($row->qty, 2, ',', '.') }}
                                    <small class="text-muted">{{ $row->product?->unit_base }}</small>
                                </td>
                                <td class="text-end fw-semibold text-success">
                                    Rp {{ number_format($row->cost, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    @if($row->items->isNotEmpty())
                                    <button class="btn btn-outline-secondary btn-sm py-0 px-2"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#items-{{ $loop->index }}">
                                        <i class="bi bi-chevron-down" style="font-size:.7rem"></i>
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            @if($row->items->isNotEmpty())
                            <tr class="collapse" id="items-{{ $loop->index }}">
                                <td colspan="5" class="p-0">
                                    <div class="bg-light px-4 py-2">
                                        <small class="text-muted fw-semibold d-block mb-1">Bahan yang dikonsumsi:</small>
                                        @foreach($row->items as $item)
                                        <div class="d-flex justify-content-between small py-1 border-bottom">
                                            <span>{{ $item->rawIngredient?->name ?? '-' }}</span>
                                            <span class="text-muted">
                                                {{ number_format($item->qty_consumed, 3, ',', '.') }} {{ $item->rawIngredient?->unit_base }}
                                                × Rp {{ number_format($item->price_per_base, 0, ',', '.') }}
                                                = <strong>Rp {{ number_format($item->qty_consumed * $item->price_per_base, 0, ',', '.') }}</strong>
                                            </span>
                                        </div>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                        <tfoot class="table-success fw-semibold">
                            <tr>
                                <td colspan="3">TOTAL ({{ $totalBatch }} batch)</td>
                                <td class="text-end">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- RINGKASAN PER PRODUK --}}
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header fw-semibold">
                <i class="bi bi-pie-chart me-1"></i>Ringkasan per Produk
            </div>
            <div class="card-body p-0">
                @forelse($byProduct as $name => $info)
                @php $pct = $grandTotal > 0 ? $info['cost'] / $grandTotal * 100 : 0; @endphp
                <div class="px-3 py-2 border-bottom">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-semibold text-truncate" style="max-width:55%">{{ $name }}</span>
                        <div class="text-end">
                            <div class="small text-success fw-semibold">Rp {{ number_format($info['cost'], 0, ',', '.') }}</div>
                            <div class="text-muted" style="font-size:.7rem">{{ $info['batch'] }} batch</div>
                        </div>
                    </div>
                    <div class="progress" style="height:4px">
                        <div class="progress-bar bg-success" style="width:{{ $pct }}%"></div>
                    </div>
                </div>
                @empty
                <div class="text-center py-4 text-muted small">Tidak ada data</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@else
<div class="alert alert-info">
    <i class="bi bi-info-circle me-1"></i> Pilih toko terlebih dahulu untuk melihat laporan.
</div>
@endif
@endsection
