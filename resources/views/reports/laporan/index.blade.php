@extends('layouts.app')
@section('title', 'Detail Laporan')

@section('content')
<div class="page-header">
    <h4 class="page-title mb-0">Detail Laporan</h4>
    <p class="text-muted mb-0 small">Versi cetak/historis dari laporan yang ada di menu utama.</p>
</div>

<div class="row g-4 mt-1">

    {{-- Menu Terjual --}}
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('reports.laporan.menu-terjual') }}" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm laporan-card" style="border-top: 4px solid #0d6efd !important;">
                <div class="card-body d-flex gap-3 align-items-start p-4">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:52px;height:52px;background:#e8f0fe">
                        <i class="bi bi-cup-hot fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark">Laporan Menu Terjual</h6>
                        <p class="text-muted small mb-0">Rekap penjualan per menu dalam periode bulanan, lengkap dengan pendapatan dan kategori.</p>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 px-4 pb-3 pt-0">
                    <span class="text-primary small fw-semibold">Buka Laporan <i class="bi bi-arrow-right"></i></span>
                </div>
            </div>
        </a>
    </div>

    {{-- HPP --}}
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('reports.laporan.hpp') }}" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm laporan-card" style="border-top: 4px solid #198754 !important;">
                <div class="card-body d-flex gap-3 align-items-start p-4">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:52px;height:52px;background:#e9f7ef">
                        <i class="bi bi-percent fs-4 text-success"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark">Laporan HPP</h6>
                        <p class="text-muted small mb-0">Rekap Harga Pokok Produksi per toko — omset, HPP ideal vs aktual, dan margin.</p>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 px-4 pb-3 pt-0">
                    <span class="text-success small fw-semibold">Buka Laporan <i class="bi bi-arrow-right"></i></span>
                </div>
            </div>
        </a>
    </div>

    {{-- Waste --}}
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('reports.laporan.waste') }}" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm laporan-card" style="border-top: 4px solid #dc3545 !important;">
                <div class="card-body d-flex gap-3 align-items-start p-4">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:52px;height:52px;background:#fdecea">
                        <i class="bi bi-trash3 fs-4 text-danger"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark">Laporan Waste</h6>
                        <p class="text-muted small mb-0">Rekap kerugian waste bahan baku per rentang tanggal beserta analisis bahan paling sering waste.</p>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 px-4 pb-3 pt-0">
                    <span class="text-danger small fw-semibold">Buka Laporan <i class="bi bi-arrow-right"></i></span>
                </div>
            </div>
        </a>
    </div>

    {{-- Produksi --}}
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('reports.laporan.produksi') }}" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm laporan-card" style="border-top: 4px solid #fd7e14 !important;">
                <div class="card-body d-flex gap-3 align-items-start p-4">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:52px;height:52px;background:#fff3e0">
                        <i class="bi bi-gear fs-4 text-warning"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark">Laporan Produksi</h6>
                        <p class="text-muted small mb-0">Rekap log produksi semi-finished, jumlah batch, bahan dikonsumsi, dan total biaya produksi.</p>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 px-4 pb-3 pt-0">
                    <span class="text-warning small fw-semibold">Buka Laporan <i class="bi bi-arrow-right"></i></span>
                </div>
            </div>
        </a>
    </div>

    {{-- Mutasi Stok --}}
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('reports.laporan.mutasi-stok') }}" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm laporan-card" style="border-top: 4px solid #0dcaf0 !important;">
                <div class="card-body d-flex gap-3 align-items-start p-4">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:52px;height:52px;background:#e0f7fa">
                        <i class="bi bi-arrow-left-right fs-4 text-info"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark">Laporan Mutasi Stok</h6>
                        <p class="text-muted small mb-0">Rekap pembelian & penerimaan stok dari PI, Zhisheng, maupun Supplier per periode.</p>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 px-4 pb-3 pt-0">
                    <span class="text-info small fw-semibold">Buka Laporan <i class="bi bi-arrow-right"></i></span>
                </div>
            </div>
        </a>
    </div>

</div>

<style>
.laporan-card {
    transition: transform .15s ease, box-shadow .15s ease;
    cursor: pointer;
}
.laporan-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0,0,0,.1) !important;
}
</style>
@endsection
