@extends('layouts.app')
@section('title', 'Laporan Data Waste')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h4 class="page-title mb-0">Laporan Data Waste</h4>
        <p class="text-muted mb-0 small">Rekap kerugian waste bahan baku per rentang tanggal</p>
    </div>
</div>

{{-- FILTER --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.laporan.waste') }}" class="row g-3 align-items-end">
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
            <div class="col-md-5 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-fill">
                    <i class="bi bi-search me-1"></i>Tampilkan
                </button>
                <a href="{{ route('reports.laporan.waste.export', request()->query()) }}"
                   class="btn btn-success btn-sm flex-fill">
                    <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
                </a>
            </div>
        </form>
    </div>
</div>

@if($storeId)

{{-- SUMMARY CARDS --}}
@php
$byIngredient = $rows->groupBy(fn($r) => $r->ingredient?->name ?? 'Lainnya')
    ->map(fn($g) => $g->sum('subtotal_loss'))
    ->sortDesc();
$topWaste = $byIngredient->first();
$topName  = $byIngredient->keys()->first();
@endphp

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card border-danger">
            <div class="stat-icon bg-danger-subtle text-danger"><i class="bi bi-trash3 fs-3"></i></div>
            <div class="stat-info">
                <div class="stat-number text-danger" style="font-size:15px">Rp {{ number_format($grandTotal, 0, ',', '.') }}</div>
                <div class="stat-label">Total Kerugian Waste</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card border-warning">
            <div class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-list-ul fs-3"></i></div>
            <div class="stat-info">
                <div class="stat-number text-warning">{{ $rows->count() }}</div>
                <div class="stat-label">Total Item Waste</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card border-secondary">
            <div class="stat-icon bg-secondary-subtle text-secondary"><i class="bi bi-trophy fs-3"></i></div>
            <div class="stat-info">
                <div class="stat-number text-secondary" style="font-size:13px">{{ $topName ?? '-' }}</div>
                <div class="stat-label">Bahan Paling Sering Waste</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- TABLE --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header fw-semibold">
                <i class="bi bi-table me-1"></i>
                Detail Waste —
                {{ \Carbon\Carbon::parse($dateFrom)->isoFormat('D MMM Y') }} s/d {{ \Carbon\Carbon::parse($dateTo)->isoFormat('D MMM Y') }}
            </div>
            <div class="card-body p-0">
                @if($rows->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                        Tidak ada data waste untuk periode ini
                    </div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Bahan</th>
                                <th class="text-end">Qty Base</th>
                                <th class="text-end">Harga/Base</th>
                                <th class="text-end">Kerugian</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $row)
                            <tr>
                                <td class="text-muted small text-nowrap">
                                    {{ \Carbon\Carbon::parse($row->wasteLog->waste_date)->isoFormat('D MMM Y') }}
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $row->ingredient?->name ?? '-' }}</div>
                                    <small class="text-muted">{{ $row->ingredient?->unit_base }}</small>
                                </td>
                                <td class="text-end">{{ number_format($row->qty_base, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($row->price_per_base, 0, ',', '.') }}</td>
                                <td class="text-end fw-semibold text-danger">
                                    Rp {{ number_format($row->subtotal_loss, 0, ',', '.') }}
                                </td>
                                <td class="text-muted small">{{ $row->wasteLog->notes ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-danger fw-semibold">
                            <tr>
                                <td colspan="4">TOTAL KERUGIAN</td>
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

    {{-- TOP BAHAN WASTE --}}
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header fw-semibold">
                <i class="bi bi-bar-chart me-1"></i>Top Bahan Waste
            </div>
            <div class="card-body p-0">
                @forelse($byIngredient->take(8) as $name => $loss)
                @php $pct = $grandTotal > 0 ? $loss / $grandTotal * 100 : 0; @endphp
                <div class="px-3 py-2 border-bottom">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-semibold text-truncate" style="max-width:60%">{{ $name }}</span>
                        <span class="small text-danger fw-semibold">Rp {{ number_format($loss, 0, ',', '.') }}</span>
                    </div>
                    <div class="progress" style="height:4px">
                        <div class="progress-bar bg-danger" style="width:{{ $pct }}%"></div>
                    </div>
                    <div class="text-muted" style="font-size:.7rem">{{ number_format($pct, 1, ',', '.') }}% dari total</div>
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
