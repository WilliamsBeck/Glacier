@extends('layouts.app')
@section('title', 'Rekap Laporan')

@section('content')
<div class="container-fluid py-4">

  <div class="mb-4">
    <h4 class="mb-1 fw-semibold">Rekap & Ekspor Laporan</h4>
    <p class="text-muted small mb-0">Ringkasan semua laporan + tombol ekspor ke Excel</p>
  </div>

  {{-- Filter bulan/tahun --}}
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
      <form method="GET" class="row g-3 align-items-end">
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

  {{-- Per-store summary --}}
  @foreach($summaries as $s)
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
      <span class="fw-semibold fs-6">{{ $s->store->name }}</span>
      <div class="d-flex gap-2 flex-wrap justify-content-end">
        {{-- Export buttons --}}
        <a href="{{ route('reports.export.waste', ['store_id'=>$s->store->id,'month'=>$month,'year'=>$year]) }}"
           class="btn btn-sm btn-outline-danger">
          <i class="bi bi-download me-1"></i>Ekspor Waste
        </a>
        <a href="{{ route('reports.export.purchase', ['store_id'=>$s->store->id,'month'=>$month,'year'=>$year]) }}"
           class="btn btn-sm btn-outline-primary">
          <i class="bi bi-download me-1"></i>Ekspor Pembelian
        </a>
        <a href="{{ route('reports.export.hpp', ['store_id'=>$s->store->id,'month'=>$month,'year'=>$year]) }}"
           class="btn btn-sm btn-outline-success">
          <i class="bi bi-download me-1"></i>Ekspor HPP
        </a>
      </div>
    </div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-6 col-md-3">
          <div class="p-3 rounded-3 bg-light h-100">
            <div class="text-muted small mb-1">Omset</div>
            <div class="fw-bold">Rp {{ number_format($s->omset,0,',','.') }}</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="p-3 rounded-3 h-100" style="background:#eff6ff">
            <div class="text-muted small mb-1">Total Pembelian</div>
            <div class="fw-bold text-primary">Rp {{ number_format($s->total_purchase,0,',','.') }}</div>
            @if($s->hpp_ratio !== null)
            <div class="text-muted small">{{ number_format($s->hpp_ratio, 1, ',', '.') }}% dari omset</div>
            @endif
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="p-3 rounded-3 h-100" style="background:#fef2f2">
            <div class="text-muted small mb-1">Total Waste</div>
            <div class="fw-bold text-danger">Rp {{ number_format($s->total_waste,0,',','.') }}</div>
            @if($s->waste_ratio !== null)
            <div class="text-muted small">{{ number_format($s->waste_ratio, 2, ',', '.') }}% dari omset</div>
            @endif
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="p-3 rounded-3 h-100" style="background:#f0fdf4">
            <div class="text-muted small mb-1">Aksi Cepat</div>
            <div class="d-flex flex-column gap-1">
              <a href="{{ route('sales.hpp.index', ['store_id'=>$s->store->id,'month'=>$month,'year'=>$year]) }}"
                 class="btn btn-xs btn-outline-success btn-sm py-0 px-2 text-start">
                <i class="bi bi-calculator me-1"></i>Lihat HPP
              </a>
              <a href="{{ route('reports.waste', ['store_id'=>$s->store->id,'month'=>$month,'year'=>$year]) }}"
                 class="btn btn-xs btn-outline-danger btn-sm py-0 px-2 text-start">
                <i class="bi bi-trash me-1"></i>Analisis Waste
              </a>
              <a href="{{ route('reports.purchase', ['store_id'=>$s->store->id,'month'=>$month,'year'=>$year]) }}"
                 class="btn btn-xs btn-outline-primary btn-sm py-0 px-2 text-start">
                <i class="bi bi-bag me-1"></i>Laporan Pembelian
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endforeach

  @if($summaries->isEmpty())
    <div class="alert alert-info">Tidak ada data untuk periode ini.</div>
  @endif

</div>
@endsection
