@extends('layouts.app')
@section('title','Forecasting Pembelian')
@section('content')
<div class="page-header"><h4 class="page-title">Forecasting Kebutuhan Pembelian</h4>
    <p class="text-muted">Estimasi bahan yang perlu dibeli berdasarkan pemakaian historis</p>
</div>

<div class="card mb-4"><div class="card-header fw-semibold">Parameter Forecasting</div><div class="card-body">
    <form method="POST" action="{{ route('forecasting.calculate') }}">
        @csrf
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label fw-semibold">Toko *</label>
                <select name="store_id" class="form-select" required>
                    <option value="">— Pilih Toko —</option>
                    @foreach($stores as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Kebutuhan untuk (hari) *</label>
                <input type="number" name="days_needed" class="form-control" value="7" min="1" max="90" required>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Acuan pemakaian (hari) *</label>
                <input type="number" name="ref_days" class="form-control" value="30" min="1" max="90" required>
                <div class="form-text">Hitung rata2 dari N hari terakhir</div>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Buffer (%)</label>
                <input type="number" name="buffer_pct" class="form-control" value="10" min="0" max="100">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-graph-up-arrow me-1"></i> Hitung Kebutuhan
                </button>
            </div>
        </div>
    </form>
</div></div>

@isset($results)
<div class="card"><div class="card-header d-flex justify-content-between fw-semibold">
    <span>Hasil Forecasting</span>
    <span class="text-muted small">{{ count($results) }} bahan perlu dibeli</span>
</div><div class="card-body p-0"><div class="table-responsive">
    <table class="table table-hover mb-0">
        <thead class="table-dark"><tr>
            <th>Bahan</th><th class="text-end">Stok Saat Ini</th>
            <th class="text-end">Rata2/Hari</th><th class="text-end">Kebutuhan</th>
            <th class="text-end">Perlu Beli</th><th>Dalam Kemasan</th>
            <th class="text-end">Harga Terakhir</th><th class="text-end">Est. Budget</th>
        </tr></thead>
        <tbody>
            @forelse($results as $r)
            <tr>
                <td class="fw-semibold">{{ $r['ingredient']->name }}</td>
                <td class="text-end">{{ number_format($r['current_qty'], 0, ',', '.') }} {{ $r['ingredient']->unit_base }}</td>
                <td class="text-end">{{ number_format($r['avg_per_day'], 0, ',', '.') }}</td>
                <td class="text-end">{{ number_format($r['needed'], 0, ',', '.') }}</td>
                <td class="text-end text-danger fw-bold">{{ number_format($r['to_buy_base'], 0, ',', '.') }}</td>
                <td>
                    @if($r['packaging'])
                        <span class="badge bg-light text-dark border">
                            {{ $r['dus'] }} Dus + {{ $r['pack'] }} Pack
                        </span>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td class="text-end">Rp {{ number_format($r['last_price'],2,',','.') }}</td>
                <td class="text-end fw-bold text-primary">Rp {{ number_format($r['est_budget'],0,',','.') }}</td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-center py-4 text-muted">Semua stok mencukupi untuk periode ini</td></tr>
            @endforelse
        </tbody>
        @if(count($results) > 0)
        <tfoot><tr class="table-light fw-bold">
            <td colspan="7" class="text-end">Total Estimasi Budget</td>
            <td class="text-end text-primary">Rp {{ number_format(collect($results)->sum('est_budget'),0,',','.') }}</td>
        </tr></tfoot>
        @endif
    </table>
</div></div></div>
@endisset
@endsection
