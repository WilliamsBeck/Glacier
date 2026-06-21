@extends('layouts.app')
@section('title', 'Laporan Data HPP')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h4 class="page-title mb-0">Laporan Data HPP</h4>
        <p class="text-muted mb-0 small">Harga Pokok Penjualan semua toko per periode</p>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <a href="{{ route('reports.laporan.hpp.export', request()->query()) }}" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
        </a>
        <a href="{{ route('reports.laporan.index') }}" class="btn btn-outline-secondary btn-sm btn-back">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>
</div>

{{-- FILTER --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.laporan.hpp') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
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
            <div class="col-md-5 d-flex gap-2 justify-content-end">
                <button type="submit" class="btn btn-primary btn-laporan">
                    <i class="bi bi-search me-1"></i>Tampilkan
                </button>
            </div>
        </form>
    </div>
</div>

@if($rows->isNotEmpty())

{{-- SUMMARY CARDS --}}
@php
$totalOmset    = $rows->sum('omset');
$totalHppIdeal = $rows->sum('hpp_ideal');
$totalHppAktual = $rows->whereNotNull('hpp_aktual')->sum('hpp_aktual');
$avgPctIdeal   = $rows->whereNotNull('pct_hpp_ideal')->avg('pct_hpp_ideal');
$avgPctAktual  = $rows->whereNotNull('pct_hpp_aktual')->avg('pct_hpp_aktual');
@endphp
<div class="row g-3 mb-4">
    <div class="col">
        <div class="stat-card border-success">
            <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-cash-stack fs-3"></i></div>
            <div class="stat-info">
                <div class="stat-number text-success" style="font-size:14px">Rp {{ number_format($totalOmset, 0, ',', '.') }}</div>
                <div class="stat-label">Total Omset</div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="stat-card border-warning">
            <div class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-calculator fs-3"></i></div>
            <div class="stat-info">
                <div class="stat-number text-warning" style="font-size:14px">Rp {{ number_format($totalHppIdeal, 0, ',', '.') }}</div>
                <div class="stat-label">Total HPP Ideal</div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="stat-card border-info">
            <div class="stat-icon bg-info-subtle text-info"><i class="bi bi-percent fs-3"></i></div>
            <div class="stat-info">
                <div class="stat-number text-info">{{ $avgPctIdeal ? number_format($avgPctIdeal, 1, ',', '.') . '%' : '-' }}</div>
                <div class="stat-label">Rata-rata % HPP Ideal</div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="stat-card border-warning">
            <div class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-receipt-cutoff fs-3"></i></div>
            <div class="stat-info">
                <div class="stat-number text-warning" style="font-size:14px">Rp {{ number_format($totalHppAktual, 0, ',', '.') }}</div>
                <div class="stat-label">Total HPP Aktual</div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="stat-card border-danger">
            <div class="stat-icon bg-danger-subtle text-danger"><i class="bi bi-percent fs-3"></i></div>
            <div class="stat-info">
                <div class="stat-number text-danger">{{ $avgPctAktual ? number_format($avgPctAktual, 1, ',', '.') . '%' : '-' }}</div>
                <div class="stat-label">Rata-rata % HPP Aktual</div>
            </div>
        </div>
    </div>
</div>

{{-- TABLE --}}
<div class="card">
    <div class="card-header fw-semibold">
        <i class="bi bi-table me-1"></i>
        Detail HPP — {{ \Carbon\Carbon::create($year, $month)->isoFormat('MMMM Y') }}
        ({{ $periodType === 'mid_month' ? 'Tengah Bulan' : 'Akhir Bulan' }})
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-index mb-0 align-middle">
                <thead>
                    <tr>
                        <th class="col-name" style="width:16%">Toko</th>
                        <th class="text-end" style="width:12%">Omset</th>
                        <th class="text-end" style="width:12%">HPP Ideal</th>
                        <th class="text-end" style="width:12%">% HPP Ideal</th>
                        <th class="text-end" style="width:12%">HPP Aktual</th>
                        <th class="text-end" style="width:12%">% HPP Aktual</th>
                        <th class="text-end" style="width:12%">Margin Ideal</th>
                        <th class="text-center" style="width:12%">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                    @php
                        $pctIdeal  = $row->pct_hpp_ideal;
                        $pctAktual = $row->pct_hpp_aktual;
                        $statusColor = 'secondary';
                        $statusText  = 'No Data';
                        if ($pctAktual !== null) {
                            if ($pctAktual <= 30) { $statusColor = 'success'; $statusText = 'Baik'; }
                            elseif ($pctAktual <= 35) { $statusColor = 'warning'; $statusText = 'Perhatian'; }
                            else { $statusColor = 'danger'; $statusText = 'Kritis'; }
                        }
                    @endphp
                    <tr>
                        <td class="col-name fw-semibold">{{ $row->store->name }}</td>
                        <td class="text-end">Rp {{ $row->omset ? number_format($row->omset, 0, ',', '.') : '-' }}</td>
                        <td class="text-end">Rp {{ $row->hpp_ideal ? number_format($row->hpp_ideal, 0, ',', '.') : '-' }}</td>
                        <td class="text-end">
                            @if($pctIdeal)
                                <span class="badge bg-primary-subtle text-primary">{{ number_format($pctIdeal, 2, ',', '.') }}%</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($row->hpp_aktual)
                                Rp {{ number_format($row->hpp_aktual, 0, ',', '.') }}
                            @else
                                <span class="text-muted small">Belum ada data aktual</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($pctAktual)
                                <span class="badge bg-{{ $statusColor }}-subtle text-{{ $statusColor }}">{{ number_format($pctAktual, 2, ',', '.') }}%</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($row->margin_ideal)
                                <span class="text-success fw-semibold">{{ number_format($row->margin_ideal, 1, ',', '.') }}%</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $statusColor }}">{{ $statusText }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-secondary fw-semibold">
                    <tr>
                        <td>TOTAL / RATA-RATA</td>
                        <td class="text-end">Rp {{ number_format($totalOmset, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($totalHppIdeal, 0, ',', '.') }}</td>
                        <td class="text-end">{{ $avgPctIdeal ? number_format($avgPctIdeal, 2, ',', '.') . '%' : '-' }}</td>
                        <td class="text-end">-</td>
                        <td class="text-end">{{ $avgPctAktual ? number_format($avgPctAktual, 2, ',', '.') . '%' : '-' }}</td>
                        <td class="text-end">-</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

@else
<div class="alert alert-info">
    <i class="bi bi-info-circle me-1"></i>
    Tidak ada data HPP untuk periode <strong>{{ \Carbon\Carbon::create($year, $month)->isoFormat('MMMM Y') }}</strong>.
    Pastikan data penjualan sudah diinput.
</div>
@endif
@endsection
