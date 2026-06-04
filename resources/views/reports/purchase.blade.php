@extends('layouts.app')
@section('title', 'Laporan Pembelian')

@section('content')
<div class="container-fluid py-4">

  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h4 class="mb-1 fw-semibold">Laporan Pembelian per Supplier</h4>
      <p class="text-muted small mb-0">Rincian pembelian bahan baku berdasarkan supplier</p>
    </div>
  </div>

  {{-- Filter --}}
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
      <form method="GET" class="row g-3 align-items-end">
        @if(!empty($storeId)) <input type="hidden" name="store_id" value="{{ $storeId }}"> @endif
        <div class="col-6 col-md-2">
          <label class="form-label small fw-semibold">Toko</label>
          <select name="store_id" class="form-select form-select-sm">
            @foreach($stores as $s)
              <option value="{{ $s->id }}" {{ $s->id == $storeId ? 'selected':'' }}>{{ $s->name }}</option>
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
        <div class="col-6 col-md-3">
          <label class="form-label small fw-semibold">Supplier</label>
          <select name="supplier_id" class="form-select form-select-sm">
            <option value="">Semua Supplier</option>
            @foreach($suppliers as $sup)
              <option value="{{ $sup->id }}" {{ $sup->id==$supplierId ? 'selected':'' }}>{{ $sup->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-12 col-md-2">
          <button type="submit" class="btn btn-primary btn-sm w-100">Tampilkan</button>
        </div>
      </form>
    </div>
  </div>

  @if($supplierRows->isNotEmpty())

  {{-- Summary cards --}}
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted small mb-1">Total Pembelian</div>
          <div class="fw-bold fs-5">Rp {{ number_format($grandTotal,0,',','.') }}</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted small mb-1">Jumlah Supplier</div>
          <div class="fw-bold fs-5">{{ $supplierRows->count() }}</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted small mb-1">Jumlah Invoice</div>
          <div class="fw-bold fs-5">{{ $supplierRows->sum(fn($r) => $r->invoices->count()) }}</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted small mb-1">Supplier Terbesar</div>
          <div class="fw-bold" style="font-size:.95rem">{{ $supplierRows->first()->supplier?->name ?? '-' }}</div>
          <div class="text-muted small">Rp {{ number_format($supplierRows->first()->total,0,',','.') }}</div>
        </div>
      </div>
    </div>
  </div>

  {{-- Chart --}}
  <div class="row g-4 mb-4">
    <div class="col-md-5">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white border-0 py-3 fw-semibold">Porsi per Supplier</div>
        <div class="card-body d-flex align-items-center justify-content-center">
          <canvas id="pieChart" height="220"></canvas>
        </div>
      </div>
    </div>
    <div class="col-md-7">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white border-0 py-3 fw-semibold">Trend 6 Bulan Terakhir</div>
        <div class="card-body">
          <canvas id="trendChart" height="180"></canvas>
        </div>
      </div>
    </div>
  </div>

  {{-- Supplier tables --}}
  @foreach($supplierRows as $row)
  <div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-0"
         style="cursor:pointer" data-bs-toggle="collapse" data-bs-target="#sup{{ $loop->index }}">
      <div>
        <span class="fw-semibold">{{ $row->supplier?->name ?? 'Tanpa Supplier' }}</span>
        <span class="badge bg-secondary-subtle text-secondary ms-2 small">{{ $row->supplier?->type_label ?? '' }}</span>
      </div>
      <div class="d-flex align-items-center gap-3">
        <span class="fw-bold text-primary">Rp {{ number_format($row->total,0,',','.') }}</span>
        <span class="badge bg-light text-muted">{{ $grandTotal>0 ? number_format($row->total/$grandTotal*100, 1, ',', '.') : 0 }}%</span>
        <i class="bi bi-chevron-down text-muted"></i>
      </div>
    </div>
    <div id="sup{{ $loop->index }}" class="collapse {{ $loop->first ? 'show':'' }}">
      <div class="card-body pt-0">

        {{-- Per-ingredient summary --}}
        <div class="mb-3">
          <div class="small fw-semibold text-muted mb-2 text-uppercase" style="letter-spacing:.05em">Ringkasan per Bahan</div>
          <div class="table-responsive">
            <table class="table table-sm align-middle small mb-0">
              <thead class="table-light">
                <tr>
                  <th>Bahan</th>
                  <th class="text-end">Total (Base)</th>
                  <th class="text-end">Avg Harga/Base</th>
                  <th class="text-end">Total Nilai</th>
                </tr>
              </thead>
              <tbody>
                @foreach($row->ing_rows as $ing)
                <tr>
                  <td>{{ $ing->ingredient->name }}</td>
                  <td class="text-end">{{ number_format($ing->total_base,2,',','.') }}</td>
                  <td class="text-end">Rp {{ number_format($ing->avg_price,0,',','.') }}</td>
                  <td class="text-end fw-semibold">Rp {{ number_format($ing->total_value,0,',','.') }}</td>
                </tr>
                @endforeach
              </tbody>
              <tfoot class="table-light">
                <tr>
                  <th colspan="3" class="text-end">Total</th>
                  <th class="text-end text-primary">Rp {{ number_format($row->total,0,',','.') }}</th>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        {{-- Invoice list --}}
        <div class="small fw-semibold text-muted mb-2 text-uppercase" style="letter-spacing:.05em">Invoice / Dokumen</div>
        <div class="table-responsive">
          <table class="table table-sm align-middle small mb-0">
            <thead class="table-light">
              <tr>
                <th>Tgl Transaksi</th>
                <th>Tipe</th>
                <th>No Ref</th>
                <th>Invoice</th>
                <th class="text-end">Nilai</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              @foreach($row->invoices as $inv)
              <tr>
                <td>{{ \Carbon\Carbon::parse($inv->transaction_date)->format('d/m/Y') }}</td>
                <td>{{ $inv->type_label }}</td>
                <td class="text-muted">{{ $inv->reference_no ?? '-' }}</td>
                <td>{{ $inv->invoice_no ?? '-' }}</td>
                <td class="text-end fw-semibold">Rp {{ number_format($inv->total,0,',','.') }}</td>
                <td>
                  <a href="{{ route('inventory.mutations.show', $inv->id) }}" class="btn btn-xs btn-outline-secondary btn-sm py-0 px-2" target="_blank">
                    <i class="bi bi-eye"></i>
                  </a>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  @endforeach

  @else
    <div class="alert alert-info">Tidak ada data pembelian untuk periode ini.</div>
  @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
@if($supplierRows->isNotEmpty())
// Pie chart
const pieLabels = @json($supplierRows->map(fn($r) => $r->supplier?->name ?? 'Lainnya')->values());
const pieData   = @json($supplierRows->map(fn($r) => round($r->total))->values());
const palette   = ['#3b82f6','#f59e0b','#10b981','#ef4444','#8b5cf6','#06b6d4','#f97316','#84cc16'];

new Chart(document.getElementById('pieChart'), {
    type: 'doughnut',
    data: {
        labels: pieLabels,
        datasets: [{ data: pieData, backgroundColor: palette, borderWidth: 2 }]
    },
    options: {
        plugins: {
            legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 } } },
            tooltip: {
                callbacks: {
                    label: ctx => ' Rp ' + ctx.parsed.toLocaleString('id-ID')
                }
            }
        }
    }
});

// Trend chart
const trendLabels = @json($monthlyTrend->pluck('label'));
const trendData   = @json($monthlyTrend->pluck('total'));
new Chart(document.getElementById('trendChart'), {
    type: 'bar',
    data: {
        labels: trendLabels,
        datasets: [{
            label: 'Total Pembelian',
            data: trendData,
            backgroundColor: '#3b82f622',
            borderColor: '#3b82f6',
            borderWidth: 2,
            borderRadius: 4,
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: {
            y: { ticks: { callback: v => 'Rp ' + v.toLocaleString('id-ID') } }
        }
    }
});
@endif
</script>
@endpush
@endsection
