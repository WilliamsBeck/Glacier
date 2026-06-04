@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')

<div class="page-header">
    <h4 class="page-title">Dashboard</h4>
    <p class="text-muted mb-0">
        Selamat datang, <strong>{{ auth()->user()->name }}</strong>
    </p>
</div>

{{-- ═══════════════════════════════════════════════════════
     STAT CARDS
═══════════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">

    {{-- Toko Aktif --}}
    <div class="col-xl-3 col-md-6">
        <div class="stat-card border-primary">
            <div class="stat-icon bg-primary-subtle text-primary">
                <i class="bi bi-shop fs-3"></i>
            </div>
            <div class="stat-info">
                <div class="stat-number text-primary">{{ $totalActiveStores }}</div>
                <div class="stat-label">Toko Aktif</div>
            </div>
        </div>
    </div>

    {{-- Low Stock --}}
    <div class="col-xl-3 col-md-6">
        <div class="stat-card border-danger">
            <div class="stat-icon bg-danger-subtle text-danger">
                <i class="bi bi-exclamation-triangle fs-3"></i>
            </div>
            <div class="stat-info">
                <div class="stat-number text-danger">{{ $lowStocks->count() }}</div>
                <div class="stat-label">Stok Menipis (item)</div>
            </div>
        </div>
    </div>

    {{-- Total Waste --}}
    <div class="col-xl-3 col-md-6">
        <div class="stat-card border-warning">
            <div class="stat-icon bg-warning-subtle text-warning">
                <i class="bi bi-trash3 fs-3"></i>
            </div>
            <div class="stat-info">
                <div class="stat-number text-warning" style="font-size:15px">
                    Rp {{ number_format($totalWaste, 0, ',', '.') }}
                </div>
                <div class="stat-label">Total Waste Bulan Ini</div>
            </div>
        </div>
    </div>

    {{-- Total Produksi --}}
    <div class="col-xl-3 col-md-6">
        <div class="stat-card border-success">
            <div class="stat-icon bg-success-subtle text-success">
                <i class="bi bi-gear fs-3"></i>
            </div>
            <div class="stat-info">
                <div class="stat-number text-success">{{ $totalProduksi }}</div>
                <div class="stat-label">Produksi Bulan Ini (batch)</div>
            </div>
        </div>
    </div>

</div>

{{-- ═══════════════════════════════════════════════════════
     SECTION UTAMA
═══════════════════════════════════════════════════════ --}}
<div class="row g-3">

    {{-- ── Toko Belum Update Pencatatan Harian ─────────────────────── --}}
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold">
                    <i class="bi bi-calendar-x text-danger me-1"></i>
                    Belum Update Pencatatan Harian
                </span>
                <span class="badge bg-danger rounded-pill">
                    {{ $storesNotUpdated->count() }} toko
                </span>
            </div>
            <div class="card-body p-0">
                @if($storesNotUpdated->isEmpty())
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-check-circle text-success fs-2 d-block mb-2"></i>
                    Semua toko sudah update s/d
                    {{ \Carbon\Carbon::parse($yesterday)->isoFormat('D MMM Y') }} ✓
                </div>
                @else
                <div class="px-3 py-2 border-bottom bg-danger-subtle">
                    <small class="text-danger fw-semibold">
                        <i class="bi bi-info-circle me-1"></i>
                        Toko berikut belum ada catatan untuk
                        <strong>{{ \Carbon\Carbon::parse($yesterday)->isoFormat('D MMMM Y') }}</strong>
                    </small>
                </div>
                @foreach($storesNotUpdated as $store)
                @php $lastDate = $lastUsageDates[$store->id] ?? null; @endphp
                <div class="d-flex align-items-center px-3 py-2 border-bottom gap-2">
                    <div class="flex-shrink-0 rounded-circle bg-danger-subtle d-flex align-items-center
                                justify-content-center text-danger fw-bold"
                         style="width:34px;height:34px;font-size:.8rem">
                        {{ strtoupper(substr($store->name, 0, 2)) }}
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold" style="font-size:.875rem">{{ $store->name }}</div>
                        <div class="text-muted" style="font-size:.75rem">
                            @if($lastDate)
                                Terakhir: {{ \Carbon\Carbon::parse($lastDate)->isoFormat('D MMM Y') }}
                                <span class="text-danger">
                                    ({{ \Carbon\Carbon::parse($lastDate)->diffForHumans() }})
                                </span>
                            @else
                                <span class="text-danger">Belum pernah input</span>
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('inventory.daily-ledger.index', ['store_id' => $store->id]) }}"
                       class="btn btn-sm btn-outline-danger" style="font-size:.75rem">
                        Input
                    </a>
                </div>
                @endforeach
                @endif
            </div>
        </div>
    </div>

    {{-- ── Low Stock Alert ──────────────────────────────────────────── --}}
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold">
                    <i class="bi bi-exclamation-triangle text-danger me-1"></i>
                    Peringatan Stok Menipis
                    <small class="text-muted fw-normal">(Hari Pemakaian)</small>
                </span>
                <a href="{{ route('inventory.stocks.index') }}"
                   class="btn btn-sm btn-outline-danger">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                @forelse($lowStocks->take(8) as $stock)
                @php
                    $isCrit   = $stock->dos_status === 'critical';
                    $dosColor = $isCrit ? 'danger' : 'warning';
                    $dosText  = $isCrit ? 'text-white' : 'text-dark';
                @endphp
                <div class="d-flex align-items-center px-3 py-2 border-bottom gap-2">
                    <div class="flex-grow-1">
                        <div class="fw-semibold" style="font-size:.875rem">
                            {{ $stock->ingredient->name }}
                        </div>
                        <div class="text-muted" style="font-size:.75rem">
                            {{ $stock->store->name }} · lead time {{ $stock->lead_time_days ?? '?' }} hr
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-{{ $dosColor }} {{ $dosText }}" style="font-size:.78rem">
                            {{ $isCrit ? '🔴' : '🟡' }} {{ $stock->dos_value }} hr
                        </span>
                        <div class="text-muted" style="font-size:.72rem">
                            @php
                                $pkg = $stock->ingredient->packagings->first();
                                $b   = $stock->stock_balance;
                                if ($pkg && $pkg->crate_to_pack > 0 && $pkg->pack_to_base > 0) {
                                    $ctb = $pkg->crate_to_pack * $pkg->pack_to_base;
                                    $d = (int) floor($b / $ctb);
                                    $p = (int) floor(($b - $d * $ctb) / $pkg->pack_to_base);
                                    echo 'sisa ' . ($d ? $d.'D ' : '') . ($p ? $p.'P' : ($d ? '' : number_format($b, 0, ',', '.').' '.$stock->ingredient->unit_base));
                                } else {
                                    echo 'sisa ' . number_format($b, 0, ',', '.') . ' ' . $stock->ingredient->unit_base;
                                }
                            @endphp
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-check-circle text-success fs-2 d-block mb-2"></i>
                    Semua stok aman ✓
                    <div class="small mt-1">Set par level di halaman Saldo Stok</div>
                </div>
                @endforelse
            </div>
        </div>
    </div>


</div>
@endsection
