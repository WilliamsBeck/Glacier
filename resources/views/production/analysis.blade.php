@extends('layouts.app')
@section('title', 'Analisis Produksi')

@section('content')
<div class="container-fluid py-4">

  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h4 class="mb-1 fw-semibold">Analisis Produksi</h4>
      <p class="text-muted small mb-0">Jumlah batch, kuantitas, dan biaya bahan baku produksi per periode</p>
    </div>
    <a href="{{ route('production.logs.index', ['store_id' => $storeId]) }}" class="btn btn-outline-secondary btn-sm btn-back">
      <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
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
        <div class="col-6 col-md-2">
          <button type="submit" class="btn btn-primary btn-sm w-100">Tampilkan</button>
        </div>
      </form>
    </div>
  </div>

  @if($productRows->isNotEmpty())

  {{-- Summary cards --}}
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted small mb-1">Total Biaya Produksi</div>
          <div class="fw-bold fs-5 text-primary">Rp {{ number_format($totalCost,0,',','.') }}</div>
          <div class="text-muted small">bulan ini ({{ $days }} hari)</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted small mb-1">Total Batch Produksi</div>
          <div class="fw-bold fs-5">{{ number_format($logs->count(),0,',','.') }}</div>
          <div class="text-muted small">≈ {{ number_format($logs->count()/$days,1,',','.') }} batch/hari</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted small mb-1">Jenis Produk</div>
          <div class="fw-bold fs-5">{{ $productRows->count() }}</div>
          <div class="text-muted small">bahan setengah jadi</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted small mb-1">Biaya per Batch (rata-rata)</div>
          <div class="fw-bold fs-5">
            Rp {{ $logs->count() > 0 ? number_format($totalCost/$logs->count(),0,',','.') : '0' }}
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Charts --}}
  <div class="row g-4 mb-4">
    <div class="col-md-8">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
          <span class="fw-semibold">Trend 6 Bulan Terakhir</span>
          <select id="trendMode" class="form-select form-select-sm" style="width:160px">
            <option value="cost">Biaya (Rp)</option>
            <option value="batches">Jumlah Batch</option>
          </select>
        </div>
        <div class="card-body">
          <canvas id="monthlyChart" height="140"></canvas>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white border-0 py-3 fw-semibold">Porsi Biaya per Produk</div>
        <div class="card-body d-flex align-items-center justify-content-center">
          <canvas id="pieChart" height="200"></canvas>
        </div>
      </div>
    </div>
  </div>

  {{-- Per-product table --}}
  @foreach($productRows as $pr)
  <div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-0"
         style="cursor:pointer" data-bs-toggle="collapse" data-bs-target="#prod{{ $loop->index }}">
      <div class="d-flex align-items-center gap-3">
        <span class="fw-semibold">{{ $pr->ingredient->name ?? 'Tanpa Nama' }}</span>
        <span class="badge bg-secondary-subtle text-secondary">{{ $pr->batches }} batch</span>
        <span class="badge bg-primary-subtle text-primary">
          {{ number_format($pr->total_qty, 2, ',', '.') }} {{ $pr->ingredient->unit_base ?? '' }}
        </span>
      </div>
      <div class="d-flex align-items-center gap-3">
        <span class="fw-bold text-primary">Rp {{ number_format($pr->cost,0,',','.') }}</span>
        <span class="text-muted small">
          ≈ {{ number_format($pr->avg_per_day, 2, ',', '.') }} /hari
        </span>
        <i class="bi bi-chevron-down text-muted"></i>
      </div>
    </div>
    <div id="prod{{ $loop->index }}" class="collapse {{ $loop->first ? 'show' : '' }}">
      <div class="card-body pt-0">
        <div class="row g-3 mb-3">
          <div class="col-6 col-md-3">
            <div class="p-2 rounded bg-light text-center">
              <div class="text-muted small">Biaya per Batch</div>
              <div class="fw-semibold">Rp {{ number_format($pr->cost_per_batch,0,',','.') }}</div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="p-2 rounded bg-light text-center">
              <div class="text-muted small">Total Qty Diproduksi</div>
              <div class="fw-semibold">{{ number_format($pr->total_qty,2,',','.') }} {{ $pr->ingredient->unit_base ?? '' }}</div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="p-2 rounded bg-light text-center">
              <div class="text-muted small">Rata-rata per Hari</div>
              <div class="fw-semibold">{{ number_format($pr->avg_per_day,2,',','.') }} /hari</div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="p-2 rounded bg-light text-center">
              <div class="text-muted small">Total Biaya</div>
              <div class="fw-semibold text-primary">Rp {{ number_format($pr->cost,0,',','.') }}</div>
            </div>
          </div>
        </div>

        @if($pr->ing_rows->isNotEmpty())
        <div class="small fw-semibold text-muted text-uppercase mb-2" style="letter-spacing:.05em">
          Bahan Baku yang Dikonsumsi
        </div>
        <div class="table-responsive">
          <table class="table table-sm align-middle small mb-0">
            <thead class="table-light">
              <tr>
                <th>Bahan Baku</th>
                <th class="text-end">Total Dikonsumsi</th>
                <th class="text-end">Avg Harga/Base</th>
                <th class="text-end">Total Biaya</th>
                <th class="text-end">% dari Total</th>
              </tr>
            </thead>
            <tbody>
              @foreach($pr->ing_rows as $ing)
              <tr>
                <td>
                  <div class="fw-semibold">{{ $ing->ingredient->name ?? '-' }}</div>
                  <div class="text-muted" style="font-size:.72rem">{{ $ing->ingredient->unit_base ?? '' }}</div>
                </td>
                <td class="text-end">{{ number_format($ing->qty_consumed,2,',','.') }}</td>
                <td class="text-end">Rp {{ number_format($ing->avg_price,0,',','.') }}</td>
                <td class="text-end fw-semibold">Rp {{ number_format($ing->total_cost,0,',','.') }}</td>
                <td class="text-end">
                  @php $pct = $pr->cost > 0 ? $ing->total_cost / $pr->cost * 100 : 0; @endphp
                  <div class="d-flex align-items-center justify-content-end gap-2">
                    <div class="progress" style="width:50px;height:5px">
                      <div class="progress-bar bg-primary" style="width:{{ min(100,$pct) }}%"></div>
                    </div>
                    <span>{{ number_format($pct, 1, ',', '.') }}%</span>
                  </div>
                </td>
              </tr>
              @endforeach
            </tbody>
            <tfoot class="table-light">
              <tr>
                <th colspan="3" class="text-end">Total</th>
                <th class="text-end text-primary">Rp {{ number_format($pr->cost,0,',','.') }}</th>
                <th class="text-end">100%</th>
              </tr>
            </tfoot>
          </table>
        </div>
        @endif
      </div>
    </div>
  </div>
  @endforeach

  @else
    <div class="alert alert-info">Tidak ada data produksi untuk periode ini.</div>
  @endif

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
@if($monthlyTrend->isNotEmpty())
const MONTHLY_LABELS  = @json($monthlyTrend->pluck('label'));
const MONTHLY_COST    = @json($monthlyTrend->pluck('cost'));
const MONTHLY_BATCHES = @json($monthlyTrend->pluck('batches'));

let monthlyChart = null;

function renderMonthly(mode) {
    if (monthlyChart) monthlyChart.destroy();
    const isCost = mode === 'cost';
    monthlyChart = new Chart(document.getElementById('monthlyChart'), {
        type: 'bar',
        data: {
            labels: MONTHLY_LABELS,
            datasets: [{
                label: isCost ? 'Biaya Produksi' : 'Jumlah Batch',
                data: isCost ? MONTHLY_COST : MONTHLY_BATCHES,
                backgroundColor: '#3b82f622',
                borderColor: '#3b82f6',
                borderWidth: 2,
                borderRadius: 4,
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    ticks: {
                        callback: v => isCost
                            ? 'Rp ' + v.toLocaleString('id-ID')
                            : v + ' batch'
                    }
                }
            }
        }
    });
}

document.getElementById('trendMode').addEventListener('change', function () {
    renderMonthly(this.value);
});
renderMonthly('cost');
@endif

@if($productRows->isNotEmpty())
const PIE_LABELS = @json($productRows->map(fn($r) => $r->ingredient->name ?? '-')->values());
const PIE_DATA   = @json($productRows->map(fn($r) => round($r->cost))->values());
const PALETTE    = ['#3b82f6','#f59e0b','#10b981','#ef4444','#8b5cf6','#06b6d4','#f97316'];

new Chart(document.getElementById('pieChart'), {
    type: 'doughnut',
    data: {
        labels: PIE_LABELS,
        datasets: [{ data: PIE_DATA, backgroundColor: PALETTE, borderWidth: 2 }]
    },
    options: {
        plugins: {
            legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } },
            tooltip: {
                callbacks: {
                    label: ctx => ' Rp ' + ctx.parsed.toLocaleString('id-ID')
                }
            }
        }
    }
});
@endif
</script>
@endpush
@endsection
