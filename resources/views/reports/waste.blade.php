@extends('layouts.app')
@section('title', 'Analisis Waste')

@section('content')
<div class="container-fluid py-4">

  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h4 class="mb-1 fw-semibold">Analisis Waste</h4>
      <p class="text-muted small mb-0">Breakdown kerugian bahan baku per periode</p>
    </div>
  </div>

  {{-- Filter --}}
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
      <form method="GET" class="row g-3 align-items-end">
        <div class="col-6 col-md-3">
          <label class="form-label small fw-semibold">Toko</label>
          <select name="store_id" class="form-select form-select-sm">
            @foreach($stores as $s)
              <option value="{{ $s->id }}" {{ $s->id==$storeId ? 'selected':'' }}>{{ $s->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-6 col-md-2">
          <label class="form-label small fw-semibold">Bulan</label>
          <select name="month" class="form-select form-select-sm">
            @foreach(range(1,12) as $m)
              <option value="{{ $m }}" {{ $m==$month ? 'selected':'' }}>
                {{ \Carbon\Carbon::create(null,$m)->translatedFormat('F') }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-6 col-md-1">
          <label class="form-label small fw-semibold">Tahun</label>
          <select name="year" class="form-select form-select-sm">
            @foreach(range(now()->year-2, now()->year+1) as $y)
              <option value="{{ $y }}" {{ $y==$year ? 'selected':'' }}>{{ $y }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-12 col-md-2">
          <button type="submit" class="btn btn-primary btn-sm w-100">Tampilkan</button>
        </div>
      </form>
    </div>
  </div>

  @if($ingRows->isNotEmpty())

  {{-- Summary cards --}}
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted small mb-1">Total Kerugian Bulan Ini</div>
          <div class="fw-bold fs-5 text-danger">Rp {{ number_format($totalLossMonth,0,',','.') }}</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted small mb-1">Jenis Bahan Terbuang</div>
          <div class="fw-bold fs-5">{{ $ingRows->count() }}</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted small mb-1">Bahan Terboros</div>
          <div class="fw-bold" style="font-size:.95rem">{{ $ingRows->first()->ingredient->name ?? '-' }}</div>
          <div class="text-muted small">Rp {{ number_format($ingRows->first()->total_loss ?? 0,0,',','.') }}</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted small mb-1">Jumlah Pencatatan</div>
          <div class="fw-bold fs-5">{{ $recentLogs->count() }}</div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4 mb-4">
    {{-- Monthly trend --}}
    <div class="col-md-8">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white border-0 py-3 fw-semibold">Trend 6 Bulan Terakhir</div>
        <div class="card-body">
          <canvas id="monthlyChart" height="140"></canvas>
        </div>
      </div>
    </div>
    {{-- Daily trend --}}
    <div class="col-md-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white border-0 py-3 fw-semibold">Daily — Bulan Ini</div>
        <div class="card-body">
          @if($dailyTrend->isNotEmpty())
          <canvas id="dailyChart" height="180"></canvas>
          @else
          <p class="text-muted small">Belum ada data harian.</p>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- Per-ingredient table --}}
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3 fw-semibold">Breakdown per Bahan</div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Bahan</th>
              <th class="text-end">Qty Terbuang (Base)</th>
              <th class="text-end">Qty (Dus)</th>
              <th class="text-end">Avg Harga</th>
              <th class="text-end">Kerugian</th>
              <th class="text-end">% dari Total</th>
            </tr>
          </thead>
          <tbody>
            @foreach($ingRows as $i => $row)
            <tr>
              <td class="text-muted">{{ $i+1 }}</td>
              <td>
                <div class="fw-semibold">{{ $row->ingredient->name }}</div>
                <div class="text-muted" style="font-size:.75rem">{{ $row->ingredient->unit_base }}</div>
              </td>
              <td class="text-end">{{ number_format($row->total_base,2,',','.') }}</td>
              <td class="text-end text-muted">{{ $row->total_dus ? number_format($row->total_dus,3,',','.') : '-' }}</td>
              <td class="text-end">Rp {{ number_format($row->avg_price,0,',','.') }}</td>
              <td class="text-end fw-semibold text-danger">Rp {{ number_format($row->total_loss,0,',','.') }}</td>
              <td class="text-end">
                @php $pct = $totalLossMonth > 0 ? $row->total_loss / $totalLossMonth * 100 : 0; @endphp
                <div class="d-flex align-items-center justify-content-end gap-2">
                  <div class="progress" style="width:60px;height:6px">
                    <div class="progress-bar bg-danger" style="width:{{ min(100,$pct) }}%"></div>
                  </div>
                  <span>{{ number_format($pct, 1, ',', '.') }}%</span>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
          <tfoot class="table-light">
            <tr>
              <th colspan="5" class="text-end">Total</th>
              <th class="text-end text-danger">Rp {{ number_format($totalLossMonth,0,',','.') }}</th>
              <th class="text-end">100%</th>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>

  {{-- Recent logs --}}
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3 fw-semibold">Catatan Waste Terbesar</div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-sm align-middle small mb-0">
          <thead class="table-light">
            <tr>
              <th>Tanggal</th>
              <th>Bahan</th>
              <th class="text-end">Kerugian</th>
              <th>Keterangan</th>
            </tr>
          </thead>
          <tbody>
            @foreach($recentLogs as $log)
              @foreach($log->items as $item)
              <tr>
                @if($loop->first)
                <td rowspan="{{ $log->items->count() }}" class="text-muted">
                  {{ \Carbon\Carbon::parse($log->waste_date)->format('d/m/Y') }}
                  @if($loop->first && $loop->parent->first)
                  <span class="badge bg-danger-subtle text-danger ms-1">Terbesar</span>
                  @endif
                </td>
                @endif
                <td>{{ $item->ingredient->name ?? '-' }}</td>
                <td class="text-end text-danger">Rp {{ number_format($item->subtotal_loss,0,',','.') }}</td>
                @if($loop->first)
                <td rowspan="{{ $log->items->count() }}" class="text-muted small">{{ $log->notes ?? '-' }}</td>
                @endif
              </tr>
              @endforeach
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  @else
    <div class="alert alert-info">Tidak ada data waste untuk periode ini.</div>
  @endif

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
@if($monthlyTrend->isNotEmpty())
new Chart(document.getElementById('monthlyChart'), {
    type: 'bar',
    data: {
        labels: @json($monthlyTrend->pluck('label')),
        datasets: [{
            label: 'Kerugian Waste',
            data: @json($monthlyTrend->pluck('total')),
            backgroundColor: '#ef444422',
            borderColor: '#ef4444',
            borderWidth: 2,
            borderRadius: 4,
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: { y: { ticks: { callback: v => 'Rp ' + v.toLocaleString('id-ID') } } }
    }
});
@endif

@if($dailyTrend->isNotEmpty())
new Chart(document.getElementById('dailyChart'), {
    type: 'line',
    data: {
        labels: @json($dailyTrend->pluck('label')),
        datasets: [{
            label: 'Kerugian',
            data: @json($dailyTrend->pluck('total')),
            borderColor: '#ef4444',
            backgroundColor: '#ef444411',
            fill: true,
            tension: 0.3,
            pointRadius: 3,
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: { y: { ticks: { callback: v => 'Rp ' + v.toLocaleString('id-ID') } } }
    }
});
@endif
</script>
@endpush
@endsection
