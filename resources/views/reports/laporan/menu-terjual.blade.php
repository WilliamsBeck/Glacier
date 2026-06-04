@extends('layouts.app')
@section('title', 'Laporan Menu Terjual')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h4 class="page-title mb-0">Laporan Menu Terjual</h4>
        <p class="text-muted mb-0 small">Rekap penjualan menu per periode</p>
    </div>
</div>

{{-- FILTER --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.laporan.menu-terjual') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold small">Toko</label>
                <select name="store_id" class="form-select form-select-sm">
                    @foreach($stores as $s)
                        <option value="{{ $s->id }}" {{ $storeId == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold small">Bulan</label>
                <select name="month" class="form-select form-select-sm">
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $m)->isoFormat('MMMM') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold small">Tahun</label>
                <select name="year" class="form-select form-select-sm">
                    @foreach(range(now()->year - 2, now()->year) as $y)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold small">Periode</label>
                <select name="period_type" class="form-select form-select-sm">
                    <option value="end_month" {{ $periodType === 'end_month' ? 'selected' : '' }}>Akhir Bulan</option>
                    <option value="mid_month" {{ $periodType === 'mid_month' ? 'selected' : '' }}>Tengah Bulan</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-fill">
                    <i class="bi bi-search me-1"></i>Tampilkan
                </button>
                <a href="{{ route('reports.laporan.menu-terjual.export', request()->query()) }}"
                   class="btn btn-success btn-sm flex-fill">
                    <i class="bi bi-file-earmark-excel me-1"></i>Export
                </a>
            </div>
        </form>
    </div>
</div>

@if($storeId)

{{-- SUMMARY CARDS --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card border-primary">
            <div class="stat-icon bg-primary-subtle text-primary">
                <i class="bi bi-cup-straw fs-3"></i>
            </div>
            <div class="stat-info">
                <div class="stat-number text-primary">{{ number_format($totalSold) }}</div>
                <div class="stat-label">Total Menu Terjual</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card border-success">
            <div class="stat-icon bg-success-subtle text-success">
                <i class="bi bi-cash-stack fs-3"></i>
            </div>
            <div class="stat-info">
                <div class="stat-number text-success" style="font-size:15px">
                    Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                </div>
                <div class="stat-label">Total Pendapatan</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card border-info">
            <div class="stat-icon bg-info-subtle text-info">
                <i class="bi bi-tags fs-3"></i>
            </div>
            <div class="stat-info">
                <div class="stat-number text-info">{{ $rows->count() }}</div>
                <div class="stat-label">Varian Menu</div>
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
                Detail Menu Terjual —
                {{ \Carbon\Carbon::create($year, $month)->isoFormat('MMMM Y') }}
                ({{ $periodType === 'mid_month' ? 'Tengah Bulan' : 'Akhir Bulan' }})
            </div>
            <div class="card-body p-0">
                @if($rows->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                        Tidak ada data penjualan untuk periode ini
                    </div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Kategori</th>
                                <th>Nama Menu</th>
                                <th class="text-end">Qty Terjual</th>
                                <th class="text-end">% Share</th>
                                <th class="text-end">Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $i => $row)
                            <tr>
                                <td class="text-muted small">{{ $i + 1 }}</td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary">
                                        {{ $row->menu?->menuCategory?->name ?? 'Lainnya' }}
                                    </span>
                                </td>
                                <td class="fw-semibold">{{ $row->menu?->name ?? '-' }}</td>
                                <td class="text-end fw-semibold">{{ number_format($row->total_sold) }}</td>
                                <td class="text-end text-muted small">
                                    {{ $totalSold > 0 ? number_format($row->total_sold / $totalSold * 100, 1, ',', '.') : 0 }}%
                                </td>
                                <td class="text-end">Rp {{ number_format($row->total_revenue, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-primary fw-semibold">
                            <tr>
                                <td colspan="3">TOTAL</td>
                                <td class="text-end">{{ number_format($totalSold) }}</td>
                                <td class="text-end">100%</td>
                                <td class="text-end">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- PIE CHART BY CATEGORY --}}
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header fw-semibold">
                <i class="bi bi-pie-chart me-1"></i>Komposisi per Kategori
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                @if($byCategory->isEmpty())
                    <div class="text-center text-muted">Tidak ada data</div>
                @else
                    <canvas id="catChart" style="max-height:280px"></canvas>
                @endif
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

@push('scripts')
@if($storeId && $byCategory->isNotEmpty())
<script src="{{ asset('js/chart.umd.min.js') }}"></script>
<script>
@php
$catLabels = $byCategory->keys()->values()->all();
$catData   = $byCategory->values()->all();
$palette   = ['#6366f1','#f59e0b','#10b981','#ef4444','#3b82f6','#8b5cf6','#ec4899','#14b8a6','#f97316','#84cc16'];
@endphp
new Chart(document.getElementById('catChart'), {
    type: 'doughnut',
    data: {
        labels: @json($catLabels),
        datasets: [{ data: @json($catData), backgroundColor: @json($palette), borderWidth: 2 }]
    },
    options: {
        plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } },
        cutout: '55%'
    }
});
</script>
@endif
@endpush
