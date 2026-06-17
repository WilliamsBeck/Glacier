@extends('layouts.app')
@section('title', 'Rencana Order Zhisheng')
@section('content')

@php
$monthNames = ['','Januari','Februari','Maret','April','Mei','Juni',
               'Juli','Agustus','September','Oktober','November','Desember'];
$periodLabels = ['mid_month' => 'Tengah Bulan', 'end_month' => 'Akhir Bulan'];
@endphp

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h4 class="page-title">Rencana Order Zhisheng</h4>
        <p class="text-muted small mb-0">Kalkulator kebutuhan pembelian berdasarkan konsumsi historis</p>
    </div>
    @if(isset($tableData) && is_array($tableData) && count($tableData) > 0)
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-outline-secondary">
            <i class="bi bi-printer me-1"></i>Print
        </button>
        <form method="GET" action="{{ route('order-planning.export') }}" class="d-inline">
            @foreach(request()->all() as $k => $v)
                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
            @endforeach
            <button type="submit" class="btn btn-outline-success">
                <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
            </button>
        </form>
    </div>
    @endif
</div>

{{-- ═══════════════════════════════════════════════════════════════════════════
     BANNER KONFIGURASI ORDER
═══════════════════════════════════════════════════════════════════════════ --}}
<div id="bannerStorePar" class="alert alert-info d-flex align-items-center gap-3 py-2 mb-2 no-print" style="display:none">
    <i class="bi bi-info-circle fs-5 flex-shrink-0"></i>
    <div class="flex-grow-1 small" id="bannerText"></div>
    <a id="btnSetStorePar" href="{{ route('inventory.stocks.index') }}{{ request('store_id') ? '?store_id='.request('store_id') : '' }}"
       class="btn btn-sm btn-outline-primary flex-shrink-0">
        <i class="bi bi-pencil-square me-1"></i>Ubah di Saldo Stok
    </a>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════════
     FORM PARAMETER ORDER
═══════════════════════════════════════════════════════════════════════════ --}}
<div class="card mb-3 no-print">
    <div class="card-header fw-semibold py-2">
        <i class="bi bi-sliders me-1"></i>Parameter Order
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('order-planning.index') }}" id="orderForm">

        {{-- ── TOKO (paling atas, prominent) ───────────────────────────────── --}}
        <div class="row g-2 align-items-end mb-3 pb-3 border-bottom">
            <div class="col-md-6">
                <label class="form-label small fw-bold text-uppercase text-secondary" style="letter-spacing:.04em">
                    <i class="bi bi-shop me-1"></i>Toko <span class="text-danger">*</span>
                </label>
                <select name="store_id" id="storeSelect" class="form-select" required
                        onchange="onStoreChange()">
                    <option value="">— Pilih Toko —</option>
                    @foreach($stores as $s)
                    <option value="{{ $s->id }}"
                        {{ request('store_id') == $s->id ? 'selected' : '' }}>
                        {{ $s->name }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- ── SEKSI 1: REFERENSI KONSUMSI ──────────────────────────────────── --}}
        <div class="border rounded p-3 mb-3">
            <div class="small fw-bold text-secondary mb-2 text-uppercase" style="letter-spacing:.04em">
                1 · Referensi Konsumsi
            </div>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">
                        Bulan Referensi <span class="text-danger">*</span>
                        <i class="bi bi-info-circle text-muted" style="cursor:help"
                           title="Bulan yang dipakai sebagai acuan konsumsi harian rata-rata"></i>
                    </label>
                    <select name="ref_month" class="form-select form-select-sm" required>
                        @foreach($monthNames as $i => $nm)
                        @if($i === 0) @continue @endif
                        <option value="{{ $i }}"
                            {{ (int)request('ref_month', $defaultMonth) === $i ? 'selected' : '' }}>
                            {{ $nm }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Tahun</label>
                    <input type="number" name="ref_year" class="form-control form-control-sm"
                        value="{{ request('ref_year', $defaultYear) }}" min="2020" required>
                </div>
            </div>
        </div>

        {{-- ── SEKSI 2: JADWAL ORDER ────────────────────────────────────────── --}}
        <div class="border rounded p-3 mb-3">
            <div class="small fw-bold text-secondary mb-2 text-uppercase" style="letter-spacing:.04em">
                2 · Jadwal Order
            </div>

            {{-- Pemesanan untuk Bulan apa --}}
            <div class="row g-3 align-items-end mb-3">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">
                        Pemesanan Untuk Bulan <span class="text-danger">*</span>
                        <i class="bi bi-info-circle text-muted" style="cursor:help"
                           title="Label bulan order ini — untuk catatan/identifikasi PO"></i>
                    </label>
                    <select name="order_month" id="orderMonthInput" class="form-select form-select-sm" required>
                        @foreach($monthNames as $i => $nm)
                        @if($i === 0) @continue @endif
                        <option value="{{ $i }}"
                            {{ (int)request('order_month', now()->addMonth()->month) === $i ? 'selected' : '' }}>
                            {{ $nm }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Tahun <span class="text-danger">*</span></label>
                    <input type="number" name="order_year" id="orderYearInput"
                        class="form-control form-control-sm"
                        value="{{ request('order_year', now()->addMonth()->year) }}" min="2020" required>
                </div>
            </div>

            <hr class="my-3">

            {{-- Detail tanggal untuk hitung kebutuhan --}}
            <div class="small fw-semibold text-muted mb-2">
                <i class="bi bi-calculator me-1"></i>Detail untuk hitung jumlah kebutuhan:
            </div>
            <div class="row g-3 align-items-start">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">
                        Tgl SO Terakhir <span class="text-danger">*</span>
                        <i class="bi bi-info-circle text-muted" style="cursor:help"
                           title="Tanggal stok opname terakhir. Sistem akan menghitung kebutuhan dari tanggal ini sampai pesanan berikutnya tiba."></i>
                    </label>
                    <input type="date" name="ref_date" id="refDateInput"
                           class="form-control form-control-sm"
                           value="{{ request('ref_date', date('Y-m-d')) }}" required
                           onchange="recalcCoverage()">
                    <div class="form-text" style="font-size:.7rem">Stok saat ini diambil per tanggal ini</div>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-semibold">
                        Tgl Pemesanan Berikutnya <span class="text-danger">*</span>
                        <i class="bi bi-info-circle text-muted" style="cursor:help"
                           title="Tanggal Anda rencana kirim PO untuk order berikutnya (setelah order ini)"></i>
                    </label>
                    <input type="date" name="next_order_date" id="nextOrderDateInput"
                           class="form-control form-control-sm"
                           value="{{ request('next_order_date') }}" required
                           onchange="recalcCoverage()">
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-semibold">
                        Tgl Tiba Pemesanan Berikutnya
                        <i class="bi bi-info-circle text-muted" style="cursor:help"
                           title="Auto: tgl pesan berikutnya + lead time toko"></i>
                    </label>
                    <input type="date" id="nextArrivalDate"
                           class="form-control form-control-sm bg-light"
                           readonly>
                    <div class="form-text text-success" style="font-size:.7rem" id="leadTimeHint"></div>
                </div>

                <div class="col-md-auto">
                    <label class="form-label small fw-semibold">&nbsp;</label>
                    <div id="coverageBadge" style="padding-top:6px"></div>
                </div>
            </div>
        </div>

        {{-- ── SEKSI 3: SUMBER DATA STOK SAAT INI ──────────────────────────── --}}
        <div class="border rounded p-3 mb-3">
            <div class="small fw-bold text-secondary mb-2 text-uppercase" style="letter-spacing:.04em">
                3 · Sumber Data Stok Saat Ini
            </div>
            <div class="row g-3">
                <div class="col-12">
                    {{-- Radio: FIFO/Saldo Berjalan --}}
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="stock_source"
                               id="srcFifo" value="fifo"
                               {{ request('stock_source', 'fifo') === 'fifo' ? 'checked' : '' }}
                               onchange="onStockSourceChange()">
                        <label class="form-check-label" for="srcFifo">
                            <strong>Saldo Berjalan</strong>
                            <span class="text-muted small">
                                — dari pencatatan harian (FIFO). Cocok untuk pembelian urgent sebelum opname.
                            </span>
                            <span class="badge bg-warning text-dark ms-1" style="font-size:.65rem">
                                ⚠ pcs/gr mungkin tidak tercatat
                            </span>
                        </label>
                    </div>

                    {{-- Radio: Opname --}}
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="stock_source"
                               id="srcOpname" value="opname"
                               {{ request('stock_source') === 'opname' ? 'checked' : '' }}
                               onchange="onStockSourceChange()">
                        <label class="form-check-label" for="srcOpname">
                            <strong>Dari Opname</strong>
                            <span class="text-muted small">
                                — hasil hitung fisik opname (lebih akurat, termasuk pcs/gr).
                            </span>
                        </label>
                    </div>

                    {{-- Dropdown pilih opname --}}
                    <div id="opnamePickerWrap" class="ms-4 mt-2"
                         style="{{ request('stock_source') === 'opname' ? '' : 'display:none' }}">
                        <select name="opname_id" id="opnamePicker"
                                class="form-select form-select-sm" style="max-width:380px">
                            <option value="">— Pilih Opname —</option>
                        </select>
                        <div class="text-muted small mt-1" id="opnameNote"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── SEKSI 4: PARAMETER LAIN ──────────────────────────────────────── --}}
        <div class="border rounded p-3 mb-3">
            <div class="small fw-bold text-secondary mb-2 text-uppercase" style="letter-spacing:.04em">
                4 · Parameter Lain
            </div>
            <div class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Buffer Keamanan</label>
                    <div class="input-group input-group-sm">
                        <input type="number" name="buffer_pct" class="form-control"
                            value="{{ request('buffer_pct', 0) }}" min="0" max="100" step="5">
                        <span class="input-group-text">%</span>
                    </div>
                    <div class="form-text" style="font-size:.65rem">
                        Tambahan di atas kebutuhan pokok
                    </div>
                </div>
                <div class="col-md-3 d-flex align-items-end pb-1">
                    <div>
                        <input type="hidden" name="split_order" value="0">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="split_order"
                                   value="1" id="chkSplit"
                                   {{ request('split_order') ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="chkSplit">
                                Pecah 2 order (50:50)
                            </label>
                        </div>
                        <div class="text-muted" style="font-size:.65rem">
                            Kuantitas dibagi 2 order dengan jumlah sama
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="bi bi-calculator me-1"></i>Hitung Kebutuhan
        </button>

        </form>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════════
     RINGKASAN PARAMETER (tampil setelah hitung)
═══════════════════════════════════════════════════════════════════════════ --}}
@if(isset($store) && isset($tableData) && $tableData !== false)
<div class="alert alert-light border mb-3 py-2 no-print">
    <div class="row g-2 small align-items-center">
        <div class="col-auto"><strong>{{ $store->name }}</strong></div>
        <div class="col-auto text-muted">·</div>
        <div class="col-auto">
            <i class="bi bi-bar-chart-line me-1"></i>Ref: <strong>{{ $monthNames[$refMonth] }} {{ $refYear }}</strong>
        </div>
        <div class="col-auto text-muted">·</div>
        @if($orderDate)
        <div class="col-auto">
            <i class="bi bi-send me-1"></i>Order: <strong>{{ $orderDate->isoFormat('D MMM Y') }}</strong>
        </div>
        <div class="col-auto text-muted">·</div>
        @endif
        <div class="col-auto">
            <i class="bi bi-truck me-1"></i>Tiba: <strong>{{ $deliveryDate->isoFormat('D MMM Y') }}</strong>
            @if($leadTimeDays !== null)
                <span class="text-muted">({{ $leadTimeDays }} hr perjalanan)</span>
            @endif
        </div>
        <div class="col-auto text-muted">·</div>
        <div class="col-auto">
            <i class="bi bi-calendar-range me-1"></i>Coverage: <strong>{{ $daysToCover }} hari</strong>
            s/d <strong>{{ $coverageEnd->isoFormat('D MMM Y') }}</strong>
        </div>
        @if($bufferPct > 0)
        <div class="col-auto text-muted">·</div>
        <div class="col-auto"><strong>Buffer {{ $bufferPct }}%</strong></div>
        @endif
        @if($splitOrder)
        <div class="col-auto text-muted">·</div>
        <div class="col-auto"><span class="badge bg-primary">Split 2 order 50:50</span></div>
        @endif
        <div class="col-auto text-muted">·</div>
        <div class="col-auto">
            @if($stockSource === 'opname' && $selectedOpname)
                <span class="badge bg-info text-dark">
                    <i class="bi bi-clipboard-check me-1"></i>Stok dari Opname
                    {{ $selectedOpname->opname_date->isoFormat('D MMM Y') }}
                    ({{ $periodLabels[$selectedOpname->period_type] ?? $selectedOpname->period_type }})
                </span>
            @else
                <span class="badge bg-secondary">
                    <i class="bi bi-journal-text me-1"></i>Stok dari Saldo Berjalan
                </span>
            @endif
        </div>
    </div>
</div>
@endif

{{-- PESAN TIDAK ADA DATA ────────────────────────────────────────────────── --}}
@if(isset($message))
<div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle me-1"></i>{{ $message }}
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════════════════════
     TABEL HASIL
═══════════════════════════════════════════════════════════════════════════ --}}
@if(isset($tableData) && is_array($tableData) && count($tableData) > 0)

<div class="print-header d-none">
    <h5>RENCANA ORDER ZHISHENG — {{ $store->name }}</h5>
    <p>
        Ref: {{ $monthNames[$refMonth] }} {{ $refYear }} |
        @if($orderDate) Order dikirim: {{ $orderDate->isoFormat('D MMMM Y') }} | @endif
        Barang tiba: {{ $deliveryDate->isoFormat('D MMMM Y') }} |
        Coverage s/d: {{ $coverageEnd->isoFormat('D MMMM Y') }} ({{ $daysToCover }} hari) |
        Buffer: {{ $bufferPct }}% |
        Stok: {{ $stockSource === 'opname' && $selectedOpname ? 'Opname '.$selectedOpname->opname_date->isoFormat('D MMM Y') : 'Saldo Berjalan' }} |
        Dicetak: {{ now()->isoFormat('D MMMM Y, HH:mm') }}
    </p>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0 order-table" id="orderTable">
                <thead class="table-dark">
                    <tr>
                        <th rowspan="2" class="align-middle" style="min-width:180px">Bahan</th>
                        <th colspan="2" class="text-center border-start border-secondary">
                            Konsumsi Ref ({{ $monthNames[$refMonth] }})
                        </th>
                        <th colspan="2" class="text-center border-start border-secondary">
                            Stok Saat Ini
                            @if($stockSource === 'opname' && $selectedOpname)
                                <div style="font-size:.65rem;font-weight:normal">
                                    opname {{ $selectedOpname->opname_date->isoFormat('D MMM') }}
                                </div>
                            @endif
                        </th>
                        <th rowspan="2" class="text-center align-middle border-start border-secondary" style="min-width:60px">
                            Hari<br>Cover
                        </th>
                        <th rowspan="2" class="text-center align-middle" style="min-width:80px">
                            Kebutuhan<br>(Pack)
                        </th>
                        @if($bufferPct > 0)
                        <th rowspan="2" class="text-center align-middle" style="min-width:70px">
                            Buffer<br>(Pack)
                        </th>
                        @endif
                        @if($splitOrder)
                        <th colspan="2" class="text-center bg-primary text-white border-start border-secondary">
                            Beli (Dus) ▲
                        </th>
                        @else
                        <th rowspan="2" class="text-center align-middle bg-success text-white border-start border-secondary" style="min-width:90px">
                            Beli<br>(Dus) ▲
                        </th>
                        @endif
                    </tr>
                    <tr class="small">
                        <th class="text-center border-start border-secondary">Total Pack</th>
                        <th class="text-center">Rata²/Hari</th>
                        <th class="text-center border-start border-secondary">Pack</th>
                        <th class="text-center">Dus</th>
                        @if($splitOrder)
                        <th class="text-center bg-primary text-white border-start border-secondary" style="min-width:80px">
                            Order 1<br>
                            <span style="font-size:.68rem;font-weight:normal;opacity:.85">
                                tiba {{ $deliveryDate->isoFormat('D MMM') }}
                            </span>
                        </th>
                        <th class="text-center bg-info text-dark" style="min-width:80px">
                            Order 2<br>
                            <span style="font-size:.68rem;font-weight:normal;opacity:.75">
                                tiba {{ isset($splitDate) ? $splitDate->isoFormat('D MMM') : '—' }}
                            </span>
                        </th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                @php $totalDus = 0; $totalO1 = 0; $totalO2 = 0; @endphp
                @foreach($tableData as $row)
                @php
                    $totalDus += $row->net_dus;
                    $totalO1  += $row->order1_dus;
                    $totalO2  += $row->order2_dus;
                    $needsBuy  = $row->net_dus > 0;
                @endphp
                <tr class="{{ !$needsBuy ? 'text-muted' : '' }}">
                    <td class="fw-semibold">
                        {{ $row->ingredient->name }}
                        <div class="small fw-normal text-muted">{{ $row->packaging->packaging_name }}</div>
                        @if($row->active_days < 7)
                            <span class="badge bg-warning text-dark" style="font-size:.62rem">⚠ data {{ $row->active_days }} hari</span>
                        @endif
                    </td>
                    <td class="text-center border-start">{{ $row->ref_total_pack }}</td>
                    <td class="text-center">{{ $row->avg_daily_pack }}</td>
                    <td class="text-center border-start">{{ $row->stock_pack }}</td>
                    <td class="text-center text-muted">{{ number_format($row->stock_dus, 1, ',', '.') }}</td>
                    <td class="text-center border-start">{{ $row->days_cover }}</td>
                    <td class="text-center">{{ $row->gross_pack }}</td>
                    @if($bufferPct > 0)
                    <td class="text-center text-muted small">+{{ $row->buffer_pack }}</td>
                    @endif
                    @if($splitOrder)
                    <td class="text-center fw-bold border-start {{ $row->order1_dus > 0 ? 'bg-primary bg-opacity-10' : '' }}">
                        {{ $row->order1_dus > 0 ? $row->order1_dus . ' Dus' : '—' }}
                    </td>
                    <td class="text-center fw-bold {{ $row->order2_dus > 0 ? 'bg-info bg-opacity-10' : '' }}">
                        {{ $row->order2_dus > 0 ? $row->order2_dus . ' Dus' : '—' }}
                    </td>
                    @else
                    <td class="text-center fw-bold border-start {{ $needsBuy ? 'bg-success bg-opacity-10 text-success' : '' }}">
                        {{ $needsBuy ? $row->net_dus . ' Dus' : '—' }}
                    </td>
                    @endif
                </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-dark fw-bold">
                        <td colspan="{{ 6 + ($bufferPct > 0 ? 1 : 0) }}">TOTAL</td>
                        <td class="text-center">—</td>
                        @if($splitOrder)
                        <td class="text-center bg-primary">{{ $totalO1 }} Dus</td>
                        <td class="text-center bg-info text-dark">{{ $totalO2 }} Dus</td>
                        @else
                        <td class="text-center bg-success">{{ $totalDus }} Dus</td>
                        @endif
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div class="text-muted small mt-2 no-print">
    <i class="bi bi-info-circle me-1"></i>
    <strong>▲ Jumlah Dus dibulatkan ke atas (ceiling)</strong> — agar stok tidak pernah kurang dari kebutuhan.
    Stok saat ini sudah dikurangi dari perhitungan.
</div>

@elseif(isset($tableData) && $tableData === false)
<div class="card"><div class="card-body text-center py-5 text-muted">
    <i class="bi bi-calculator fs-1 d-block mb-2 opacity-25"></i>
    Isi parameter di atas lalu klik <strong>Hitung Kebutuhan</strong>
</div></div>
@endif

{{-- ═══════════════════════════════════════════════════════════════════════════
     MODAL KONFIGURASI ORDER TOKO
═══════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalStorePar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title mb-0"><i class="bi bi-gear me-1"></i>Konfigurasi Order Toko</h6>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-light border small mb-3">
                    <strong>Cara pakai:</strong><br>
                    • <strong>Siklus order</strong> = seberapa sering kamu order (mis. setiap 15 hari)<br>
                    • <strong>Lead time</strong> = berapa hari kiriman tiba setelah order (mis. 3 hari)<br><br>
                    ⚠️ <strong>Low stock di Saldo Stok muncul saat DOS &lt; lead time</strong> — artinya stok hampir
                    tidak cukup sampai kiriman tiba. Bukan saat DOS &lt; siklus order.
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Siklus Order <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <input type="number" id="orderCycleInput" class="form-control"
                                   min="1" max="90" placeholder="cth: 15">
                            <span class="input-group-text">hari</span>
                        </div>
                        <div class="text-muted" style="font-size:.72rem">Seberapa sering order</div>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Lead Time <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <input type="number" id="leadTimeInput" class="form-control"
                                   min="1" max="30" placeholder="cth: 3">
                            <span class="input-group-text">hari</span>
                        </div>
                        <div class="text-muted" style="font-size:.72rem">Waktu tunggu kiriman tiba</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Window Rata-rata Pemakaian <span class="text-danger">*</span></label>
                        <div class="d-flex gap-2">
                            @foreach([7 => '7 hari (1 minggu)', 14 => '14 hari (2 minggu)', 30 => '30 hari (1 bulan)'] as $val => $label)
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="dosWindowRadio"
                                       id="dosWindow{{ $val }}" value="{{ $val }}">
                                <label class="form-check-label small" for="dosWindow{{ $val }}">{{ $label }}</label>
                            </div>
                            @endforeach
                        </div>
                        <div class="text-muted" style="font-size:.72rem">
                            Berapa hari ke belakang untuk menghitung rata-rata pemakaian harian
                        </div>
                    </div>
                </div>
                <div class="mt-3 p-2 bg-light rounded small" id="parPreview" style="display:none">
                    Dengan konfigurasi ini:<br>
                    🔴 <strong>Kritis</strong> saat DOS &lt; <span id="prevCrit">?</span> hari (harus order sekarang)<br>
                    🟡 <strong>Segera order</strong> saat DOS &lt; <span id="prevWarn">?</span> hari
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-sm btn-primary" id="saveStoreParBtn">
                    <i class="bi bi-check-lg me-1"></i>Simpan
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════════
     JAVASCRIPT
═══════════════════════════════════════════════════════════════════════════ --}}
@push('scripts')
<script>
// Data dari server
const OPNAMES_BY_STORE = @json($opnamesByStore ?? []);
const STORE_CONFIGS    = @json($storeConfigs   ?? []);
const MONTH_ID = ['','Januari','Februari','Maret','April','Mei','Juni',
                  'Juli','Agustus','September','Oktober','November','Desember'];
const PERIOD_LABEL = { mid_month: 'Tengah Bulan', end_month: 'Akhir Bulan' };

// ── Helper ─────────────────────────────────────────────────────────────────
function addDays(dateStr, n) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    d.setDate(d.getDate() + n);
    return d.toISOString().slice(0, 10);
}

function fmtDate(dateStr) {
    if (!dateStr) return '—';
    const d = new Date(dateStr);
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}

function diffDays(a, b) {
    if (!a || !b) return null;
    return Math.round((new Date(b) - new Date(a)) / 86400000);
}

// ── Saat toko berubah ──────────────────────────────────────────────────────
function onStoreChange() {
    populateOpnames();
    recalcCoverage();
    updateStoreConfigInfo();
}

// ── Hitung coverage: dari tgl ref → tgl tiba pesanan berikutnya ────────────
const BULAN_NAMES = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

function recalcCoverage() {
    const storeId  = document.getElementById('storeSelect').value;
    const cfg      = storeId ? (STORE_CONFIGS[storeId] ?? null) : null;
    const leadTime = cfg?.lead_time_days ?? 0;

    const refDate       = document.getElementById('refDateInput').value;
    const nextOrderDate = document.getElementById('nextOrderDateInput').value;
    const arrivalEl     = document.getElementById('nextArrivalDate');
    const leadHint      = document.getElementById('leadTimeHint');
    const badge         = document.getElementById('coverageBadge');

    // Hitung tgl tiba berikutnya = pesan berikutnya + lead time
    let arrivalDate = '';
    if (nextOrderDate) {
        const d = new Date(nextOrderDate);
        d.setDate(d.getDate() + leadTime);
        arrivalDate = d.toISOString().slice(0, 10);
        arrivalEl.value = arrivalDate;
        leadHint.innerHTML = leadTime > 0
            ? `<i class="bi bi-clock me-1"></i>+${leadTime} hari lead time`
            : '<i class="bi bi-exclamation-triangle me-1 text-warning"></i>Lead time belum di-set';
    } else {
        arrivalEl.value = '';
        leadHint.textContent = '';
    }

    // Coverage = arrival - ref_date
    if (refDate && arrivalDate) {
        const days = Math.round((new Date(arrivalDate) - new Date(refDate)) / 86400000);
        if (days > 0) {
            badge.innerHTML = `<span class="badge bg-primary" style="font-size:.85rem;padding:.4rem .7rem">
                <i class="bi bi-calendar-range me-1"></i><strong>${days} hari</strong> kebutuhan
            </span>`;
        } else {
            badge.innerHTML = `<span class="text-danger small"><i class="bi bi-exclamation-triangle me-1"></i>Tgl tiba harus setelah tgl referensi</span>`;
        }
    } else {
        badge.textContent = '';
    }
}

// ── Isi dropdown opname ────────────────────────────────────────────────────
function populateOpnames() {
    const storeId = document.getElementById('storeSelect').value;
    const picker  = document.getElementById('opnamePicker');
    const note    = document.getElementById('opnameNote');
    const list    = OPNAMES_BY_STORE[storeId] ?? [];
    const savedId = "{{ request('opname_id') }}";

    picker.innerHTML = '<option value="">— Pilih Opname —</option>';

    if (list.length === 0) {
        note.textContent = 'Belum ada opname approved untuk toko ini.';
        note.className   = 'text-warning small mt-1';
    } else {
        note.textContent = '';
        list.forEach(op => {
            const d    = new Date(op.opname_date);
            const dStr = d.toLocaleDateString('id-ID', { day:'numeric', month:'long', year:'numeric' });
            const per  = PERIOD_LABEL[op.period_type] ?? op.period_type;
            const opt  = document.createElement('option');
            opt.value       = op.id;
            opt.textContent = `${dStr} — ${per}`;
            if (String(op.id) === savedId) opt.selected = true;
            picker.appendChild(opt);
        });
    }
}

// ── Toggle sumber stok ─────────────────────────────────────────────────────
function onStockSourceChange() {
    const isOpname = document.getElementById('srcOpname').checked;
    document.getElementById('opnamePickerWrap').style.display = isOpname ? '' : 'none';
}

// ── Init saat halaman load ─────────────────────────────────────────────────
populateOpnames();
recalcCoverage();
updateStoreConfigInfo();

// ══════════════════════════════════════════════════════════════════════════════
// KONFIGURASI ORDER TOKO (Lead Time & Siklus Order)
// ══════════════════════════════════════════════════════════════════════════════
const storeParUrl = "{{ route('inventory.stocks.set-store-par') }}";
const csrfToken   = "{{ csrf_token() }}";

function updateStoreConfigInfo() {
    const storeId = document.getElementById('storeSelect').value;
    const cfg     = storeId ? (STORE_CONFIGS[storeId] ?? null) : null;
    const banner  = document.getElementById('bannerStorePar');
    const text    = document.getElementById('bannerText');
    const btn     = document.getElementById('btnSetStorePar');

    // Sembunyikan kalau belum pilih toko / belum di-set
    if (!storeId || !cfg || !cfg.lead_time_days || !cfg.order_cycle_days) {
        banner.style.display = 'none';
        return;
    }

    // Tampil hanya jika config sudah ada
    banner.style.display = '';
    banner.className = 'alert alert-info d-flex align-items-center gap-3 py-2 mb-2 no-print';
    text.innerHTML   = `<strong>Siklus Order:</strong> ${cfg.order_cycle_days} hari`
        + ` <span class="text-muted mx-2">·</span> `
        + `<strong>Lead Time:</strong> ${cfg.lead_time_days} hari`;

    if (btn) {
        btn.href = `{{ route('inventory.stocks.index') }}?store_id=${storeId}`;
    }
}

function updateParPreview() {
    const orderCycle = parseInt(document.getElementById('orderCycleInput').value) || 0;
    const leadTime   = parseInt(document.getElementById('leadTimeInput').value)   || 0;
    const preview    = document.getElementById('parPreview');

    if (orderCycle > 0 && leadTime > 0) {
        const buffer = Math.ceil(orderCycle / 3);
        document.getElementById('prevCrit').textContent = leadTime;
        document.getElementById('prevWarn').textContent = leadTime + buffer;
        preview.style.display = '';
    } else {
        preview.style.display = 'none';
    }
}

// Buka modal — isi nilai saat ini dari STORE_CONFIGS
document.getElementById('btnSetStorePar')?.addEventListener('click', () => {
    const storeId = document.getElementById('storeSelect').value;
    if (!storeId) { alert('Pilih toko terlebih dahulu.'); return; }

    const cfg = STORE_CONFIGS[storeId] ?? {};
    document.getElementById('orderCycleInput').value = cfg.order_cycle_days ?? '';
    document.getElementById('leadTimeInput').value   = cfg.lead_time_days   ?? '';

    // Set radio window DOS
    const window = cfg.dos_window_days ?? 30;
    const radio  = document.querySelector(`input[name="dosWindowRadio"][value="${window}"]`);
    if (radio) radio.checked = true;

    updateParPreview();
    new bootstrap.Modal(document.getElementById('modalStorePar')).show();
    setTimeout(() => document.getElementById('orderCycleInput').focus(), 400);
});

document.getElementById('orderCycleInput')?.addEventListener('input', updateParPreview);
document.getElementById('leadTimeInput')?.addEventListener('input', updateParPreview);

document.getElementById('saveStoreParBtn')?.addEventListener('click', () => {
    const storeId    = document.getElementById('storeSelect').value;
    const orderCycle = parseInt(document.getElementById('orderCycleInput').value);
    const leadTime   = parseInt(document.getElementById('leadTimeInput').value);
    const dosWindow  = parseInt(document.querySelector('input[name="dosWindowRadio"]:checked')?.value ?? 30);

    if (!storeId)                     { alert('Pilih toko terlebih dahulu.'); return; }
    if (!orderCycle || orderCycle < 1) { alert('Masukkan siklus order (min 1 hari).'); return; }
    if (!leadTime   || leadTime   < 1) { alert('Masukkan lead time (min 1 hari).'); return; }
    if (!dosWindow)                   { alert('Pilih window rata-rata pemakaian.'); return; }
    if (leadTime > orderCycle) {
        if (!confirm('Lead time lebih besar dari siklus order — apakah ini benar?')) return;
    }

    const btn = document.getElementById('saveStoreParBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan…';

    fetch(storeParUrl, {
        method : 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body   : JSON.stringify({
            store_id        : parseInt(storeId),
            lead_time_days  : leadTime,
            order_cycle_days: orderCycle,
            dos_window_days : dosWindow,
        }),
    })
    .then(r => r.json())
    .then(d => {
        if (d.ok) {
            // Update local cache
            if (STORE_CONFIGS[storeId]) {
                STORE_CONFIGS[storeId].lead_time_days   = d.lead_time_days;
                STORE_CONFIGS[storeId].order_cycle_days = d.order_cycle_days;
                STORE_CONFIGS[storeId].dos_window_days  = d.dos_window_days;
            }
            bootstrap.Modal.getInstance(document.getElementById('modalStorePar')).hide();
            updateStoreConfigInfo();
            recalcCoverage();
        } else {
            alert('Gagal menyimpan. Periksa kembali nilai yang dimasukkan.');
        }
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Simpan';
    })
    .catch(() => {
        alert('Terjadi kesalahan jaringan, coba lagi.');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Simpan';
    });
});
</script>
@endpush

@push('styles')
<style>
@media print {
    .no-print, .sidebar, nav, .page-header .btn { display: none !important; }
    .print-header { display: block !important; margin-bottom: 12px; }
    .print-header h5 { font-size: 14px; margin-bottom: 4px; }
    .print-header p  { font-size: 11px; color: #555; }
    body, .card { box-shadow: none !important; }
    .table-sm td, .table-sm th { font-size: 11px; padding: 3px 6px; }
    .order-table thead { background: #333 !important; color: white !important; }
}
</style>
@endpush
@endsection
