@extends('layouts.app')
@section('title','Detail Opname')

@php
/**
 * Format selisih (dalam base unit) ke CTN + Pack.
 * Contoh: +2 CTN +3 Pack  |  -1 Pack  |  0
 */
function fmtVariance(float $var, ?int $ctrPack, ?int $packBase): string {
    if (!$ctrPack || !$packBase) {
        if (abs($var) < 0.01) return '0';
        return ($var >= 0 ? '+' : '') . number_format($var, 2, ',', '.');
    }
    if (abs($var) < 0.01) return '0';
    $sign = $var >= 0 ? '+' : '-';
    $abs  = abs($var);
    $ctn  = (int) floor($abs / ($ctrPack * $packBase));
    $rem  = $abs - ($ctn * $ctrPack * $packBase);
    $pk   = (int) floor($rem / $packBase);
    $parts = [];
    if ($ctn > 0) $parts[] = $sign . $ctn . ' CTN';
    if ($pk  > 0) $parts[] = $sign . $pk  . ' Pack';
    if (empty($parts)) $parts[] = $sign . '< 1 Pack';
    return implode(' ', $parts);
}
@endphp

@section('content')
<div class="page-header d-flex justify-content-between align-items-start">
    <div>
        <h4 class="page-title">Stok Opname — {{ $opname->store->name }}</h4>
        <p class="text-muted mb-0">
            {{ \Carbon\Carbon::parse($opname->opname_date)->format('d M Y') }} ·
            {{ $opname->period_type === 'mid_month' ? 'Periode 1–15' : 'Periode 1–30/31' }}
            {{ $opname->period_month }}/{{ $opname->period_year }}
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('opname.opnames.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
        <a href="{{ route('opname.opnames.export', $opname) }}" class="btn btn-outline-success btn-sm">
            <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
        </a>
        @if($isLocked)
            <button class="btn btn-danger btn-sm" disabled title="Terkunci">
                <i class="bi bi-lock me-1"></i>Hapus (Terkunci)
            </button>
            @if($opname->status !== 'approved')
            <button class="btn btn-success btn-sm" disabled title="Terkunci">
                <i class="bi bi-lock me-1"></i>Approve (Terkunci)
            </button>
            @endif
        @else
            {{-- Tombol Hapus: tampil untuk semua status, Super Admin bisa hapus approved --}}
            @if($opname->status !== 'approved')
                <form method="POST" action="{{ route('opname.opnames.destroy', $opname) }}" class="d-inline"
                      onsubmit="return confirm('Hapus opname ini?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger btn-sm"><i class="bi bi-trash me-1"></i>Hapus</button>
                </form>
                <form method="POST" action="{{ route('opname.opnames.approve', $opname) }}" class="d-inline"
                      onsubmit="return confirm('Approve opname? Selisih akan otomatis masuk ke ledger.')">
                    @csrf
                    <button class="btn btn-success btn-sm"><i class="bi bi-check-circle me-1"></i>Approve</button>
                </form>
            @else
                {{-- Approved: hanya Super Admin yang bisa hapus, dengan modal konfirmasi --}}
                @if(auth()->user()->isSuperAdmin())
                <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalHapusOpname">
                    <i class="bi bi-trash me-1"></i>Hapus
                </button>
                @endif
            @endif
        @endif
    </div>
</div>

{{-- ── Lock / Unlock Banner ─────────────────────────────────────────────── --}}
@if($isPastLock)
    @if($isLocked)
        <div class="alert alert-warning d-flex align-items-start gap-2 mt-3">
            <i class="bi bi-lock-fill fs-5 mt-1"></i>
            <div class="flex-fill">
                <strong>Data Terkunci</strong>
                <div class="small mt-1">Data bulan ini sudah melewati batas edit (H+7). Ajukan request unlock ke Super Admin.</div>
                @if($hasPending)
                    <span class="badge bg-info text-dark mt-2"><i class="bi bi-hourglass-split me-1"></i>Request unlock sedang menunggu persetujuan</span>
                @else
                    <button class="btn btn-sm btn-warning mt-2" data-bs-toggle="modal" data-bs-target="#modalUnlockOpname">
                        <i class="bi bi-unlock me-1"></i>Request Unlock
                    </button>
                @endif
            </div>
        </div>
    @elseif($hasUnlock && !auth()->user()->isSuperAdmin())
        <div class="alert alert-success d-flex align-items-center gap-2 mt-3">
            <i class="bi bi-unlock-fill fs-5"></i>
            <span><strong>Unlock Aktif</strong> — Data ini sudah dibuka oleh Super Admin dan dapat diedit.</span>
        </div>
    @elseif(auth()->user()->isSuperAdmin())
        <div class="alert alert-secondary d-flex align-items-center gap-2 mt-3 py-2">
            <i class="bi bi-shield-check fs-5"></i>
            <span class="small">Data bulan ini terkunci untuk user biasa. Anda login sebagai Super Admin — akses penuh.</span>
        </div>
    @endif
@endif

<div class="alert {{ $opname->status==='approved' ? 'alert-success' : 'alert-warning' }} d-flex align-items-center gap-2">
    <i class="bi bi-{{ $opname->status==='approved' ? 'check-circle' : 'clock' }}"></i>
    <span>
        Status: <strong>{{ $opname->status === 'approved' ? 'Disetujui' : 'Draft' }}</strong>
        @if($opname->status === 'draft') — Isi stok fisik lalu klik <strong>Setujui</strong> @endif
        @if($opname->status === 'approved') — Disetujui oleh {{ $opname->approvedBy?->name ?? '-' }} @endif
    </span>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {!! session('success') !!}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- FORM INPUT FISIK --}}
@if($opname->status !== 'approved')
<form method="POST" action="{{ route('opname.opnames.update', $opname) }}">
    @csrf @method('PUT')
@endif

<div class="card">
    <div class="card-header fw-semibold d-flex justify-content-between">
        <span><i class="bi bi-clipboard-check me-1"></i>Daftar Bahan</span>
        <span class="text-muted small fw-normal">{{ $opname->items->count() }} bahan</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            @php
                // Tandai bahan yang punya > 1 kemasan supaya bisa tampilkan sub-label
                $pkgCountByIng = $opname->items->groupBy('ingredient_id')->map->count();
            @endphp
            <table class="table table-sm table-hover mb-0 align-middle">
                <thead>
                    <tr class="table-dark">
                        <th rowspan="2" class="align-middle" style="min-width:180px">Bahan</th>
                        <th colspan="3" class="text-center border-start py-1" style="border-bottom:1px solid #4a6080">Stok Fisik</th>
                        <th colspan="2" class="text-center border-start py-1" style="border-bottom:1px solid #4a6080">Stok Sistem</th>
                        <th rowspan="2" class="text-end border-start align-middle" style="min-width:110px">Selisih<br><small class="fw-normal opacity-75">(Dus/Pack)</small></th>
                        <th rowspan="2" class="text-end border-start align-middle" style="min-width:95px">Harga<br><small class="fw-normal opacity-75">(/Dus)</small></th>
                        <th rowspan="2" class="text-end border-start align-middle" style="min-width:110px">Nilai Fisik<br><small class="fw-normal opacity-75">(Rp)</small></th>
                    </tr>
                    <tr class="table-dark" style="border-top:1px solid #4a6080">
                        <th class="text-center border-start fw-normal small py-1" style="width:75px">Dus</th>
                        <th class="text-center fw-normal small py-1" style="width:75px">Pack</th>
                        <th class="text-center fw-normal small py-1" style="width:80px">Pcs/Gr</th>
                        <th class="text-center border-start fw-normal small py-1" style="width:70px">Dus</th>
                        <th class="text-center fw-normal small py-1" style="width:70px">Pack</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($opname->items as $item)
                    @php
                        $pkg      = $item->packaging;
                        $ctrPack  = $pkg ? (int)$pkg->crate_to_pack : null;
                        $packBase = $pkg ? (int)$pkg->pack_to_base  : null;
                        $sysNeg   = $item->system_qty < 0;

                        // Label kemasan — "@ X pack" jika multi-kemasan + nama supplier jika LOKAL (bukan Zhisheng)
                        $isMultiPkg = ($pkgCountByIng[$item->ingredient_id] ?? 1) > 1;
                        $supLabel   = ($pkg && $pkg->supplier && $pkg->supplier->type !== 'zhisheng') ? $pkg->supplier->name : null;
                        $pkgLabel   = collect([($isMultiPkg && $ctrPack) ? '@ ' . $ctrPack . ' pack' : null, $supLabel])
                                        ->filter()->implode(' · ') ?: null;

                        // Stok sistem dalam Dus + Pack
                        if ($ctrPack && $packBase) {
                            $crateBase  = $ctrPack * $packBase;
                            $sysDus     = (int) floor($item->system_qty / $crateBase);
                            $sysPackRem = $item->system_qty - $sysDus * $crateBase;
                            $sysPack    = (int) floor($sysPackRem / $packBase);
                        } else {
                            $sysDus = null; $sysPack = null;
                        }

                        // Selisih tampilan: hanya Dus + Pack (Pcs/Gr tidak dihitung)
                        if ($ctrPack && $packBase) {
                            $physNoPcs  = ($item->physical_crate ?? 0) * $ctrPack * $packBase
                                        + ($item->physical_pack  ?? 0) * $packBase;
                            $sysRounded = floor($item->system_qty / $packBase) * $packBase;
                            $displayVar = $physNoPcs - $sysRounded;
                        } else {
                            $displayVar = $item->variance;
                        }
                        $varText  = fmtVariance($displayVar, $ctrPack, $packBase);
                        $isZero   = abs($displayVar) < 0.01;
                        $varClass = $isZero ? 'text-muted' : ($displayVar < 0 ? 'text-danger' : 'text-success');

                        // Harga & nilai
                        $harga      = $priceMap[$item->ingredient_id] ?? 0;
                        $hargaDus   = ($ctrPack && $packBase) ? $harga * $ctrPack * $packBase : $harga;
                        $nilaiFisik = $item->physical_qty * $harga;
                    @endphp
                    <tr
                        data-id="{{ $item->id }}"
                        data-system="{{ $item->system_qty }}"
                        data-crate="{{ $ctrPack ?? 0 }}"
                        data-pack="{{ $packBase ?? 1 }}"
                    >
                        <td>
                            <span class="fw-semibold">{{ $item->ingredient->name }}</span>
                            @if($pkgLabel)
                                <small class="text-muted d-block">{{ $pkgLabel }}</small>
                            @endif
                        </td>

                        {{-- Input fisik --}}
                        @if($opname->status !== 'approved')
                            <td class="border-start">
                                <input type="number"
                                       name="items[{{ $item->id }}][physical_crate]"
                                       class="form-control form-control-sm opname-input"
                                       value="{{ old("items.{$item->id}.physical_crate", $item->physical_crate) }}"
                                       min="0" placeholder="" style="width:68px">
                            </td>
                            <td>
                                <input type="number"
                                       name="items[{{ $item->id }}][physical_pack]"
                                       class="form-control form-control-sm opname-input"
                                       value="{{ old("items.{$item->id}.physical_pack", $item->physical_pack) }}"
                                       min="0" placeholder="" style="width:68px">
                            </td>
                            <td>
                                <input type="number"
                                       name="items[{{ $item->id }}][physical_base]"
                                       class="form-control form-control-sm opname-input"
                                       value="{{ old("items.{$item->id}.physical_base", $item->physical_base) }}"
                                       min="0" step="0.01" placeholder="" style="width:72px">
                            </td>
                        @else
                            <td class="text-center border-start small">{{ $item->physical_crate ?? '—' }}</td>
                            <td class="text-center small">{{ $item->physical_pack ?? '—' }}</td>
                            <td class="text-center small">{{ $item->physical_base ?? '—' }}</td>
                        @endif

                        {{-- Stok Sistem: kolom Dus --}}
                        <td class="text-center border-start {{ $sysNeg ? 'text-danger fw-bold' : '' }}">
                            @if($sysDus !== null)
                                {{ $sysDus > 0 ? $sysDus : ($sysNeg ? $sysDus : '—') }}
                            @else
                                {{ number_format($item->system_qty, 0, ',', '.') }}
                            @endif
                        </td>
                        {{-- Stok Sistem: kolom Pack --}}
                        <td class="text-center {{ $sysNeg ? 'text-danger fw-bold' : '' }}">
                            @if($sysDus !== null)
                                {{ $sysPack > 0 ? $sysPack : '—' }}
                            @else
                                —
                            @endif
                            @if($sysNeg)<small class="d-block text-danger">⚠</small>@endif
                        </td>

                        {{-- Selisih (Dus/Pack) --}}
                        <td class="text-end border-start fw-bold {{ $varClass }}" id="var-{{ $item->id }}">
                            {{ $varText }}
                        </td>

                        {{-- Harga & Nilai --}}
                        <td class="text-end border-start small text-muted">
                            @if($hargaDus > 0)
                                {{ number_format($hargaDus, 0, ',', '.') }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end border-start fw-semibold" id="nilai-{{ $item->id }}"
                            data-price="{{ $harga }}"{{-- data-price tetap per-base untuk kalkulasi JS --}}>
                            @if($harga > 0)
                                {{ number_format($nilaiFisik, 0, ',', '.') }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                @php
                    $grandTotal = $opname->items->sum(fn($i) => $i->physical_qty * ($priceMap[$i->ingredient_id] ?? 0));
                @endphp
                <tfoot>
                    <tr class="table-light fw-bold">
                        <td colspan="7" class="text-end border-top">TOTAL NILAI SO</td>
                        <td class="border-top"></td>
                        <td class="text-end border-top border-start fs-6" id="grand-total">
                            Rp {{ number_format($grandTotal, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @if($opname->status !== 'approved')
    <div class="card-footer text-end">
        <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-save me-1"></i>Simpan Stok Fisik
        </button>
    </div>
    @endif
</div>

@if($opname->status !== 'approved')</form>@endif

@if($opname->status === 'approved')
<div class="card mt-3 border-0 bg-light">
    <div class="card-body small text-muted">
        <i class="bi bi-info-circle me-1"></i>
        Opname ini sudah <strong>disetujui</strong>. Selisih telah otomatis diposting ke ledger sebagai penyesuaian stok opname.
        Data ini digunakan sebagai <strong>Stok Akhir (Closing Stock)</strong> dalam perhitungan HPP Aktual bulan
        {{ \Carbon\Carbon::create($opname->period_year, $opname->period_month, 1)->isoFormat('MMMM Y') }}.
    </div>
</div>
@endif

{{-- Modal Hapus Opname (hanya untuk approved, Super Admin) --}}
@if($opname->status === 'approved' && auth()->user()->isSuperAdmin())
<div class="modal fade" id="modalHapusOpname" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Hapus Opname yang Sudah Disetujui</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Opname ini sudah <strong>disetujui</strong> dan telah mempengaruhi saldo stok. Menghapusnya akan:</p>
                <ul class="small mb-3">
                    <li>Membatalkan semua penyesuaian ledger dari opname ini</li>
                    <li>Me-recalculate ulang saldo FIFO semua bahan</li>
                    <li>Menghapus mutation bootstrap (jika ada) yang dibuat saat approve</li>
                </ul>
                <div class="alert alert-warning py-2 small mb-0">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Tindakan ini <strong>tidak dapat dibatalkan</strong>. Data pencatatan harian bulan berikutnya
                    mungkin terpengaruh.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <form method="POST" action="{{ route('opname.opnames.destroy', $opname) }}" class="d-inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="bi bi-trash me-1"></i>Ya, Hapus Sekarang
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Modal Request Unlock Opname --}}
@if($isLocked && !$hasPending)
<div class="modal fade" id="modalUnlockOpname" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('inventory.unlock-requests.store') }}">
            @csrf
            <input type="hidden" name="resource_type"         value="opname">
            <input type="hidden" name="resource_id"           value="{{ $opname->id }}">
            <input type="hidden" name="store_id"              value="{{ $opname->store_id }}">
            <input type="hidden" name="resource_month"        value="{{ $lockMonth }}">
            <input type="hidden" name="resource_year"         value="{{ $lockYear }}">
            <input type="hidden" name="resource_period_type"  value="{{ $opname->period_type }}">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-unlock me-2"></i>Request Unlock Opname</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3">
                        Opname <strong>{{ $opname->store->name }}</strong>
                        periode <strong>{{ $opname->period_month }}/{{ $opname->period_year }}</strong>
                        sudah terkunci. Jelaskan alasan perlunya perubahan.
                    </p>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Alasan <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control form-control-sm" rows="3"
                            placeholder="Contoh: Ada kesalahan input stok fisik bahan X, perlu dikoreksi sebelum HPP dihitung..." required maxlength="1000"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning btn-sm">
                        <i class="bi bi-send me-1"></i>Kirim Request
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
/**
 * Format variance base → "±X CTN ±Y Pack"
 */
function fmtVar(varBase, crate, pack) {
    if (!crate || !pack) {
        if (Math.abs(varBase) < 0.01) return '0';
        return (varBase >= 0 ? '+' : '') + varBase.toFixed(2);
    }
    if (Math.abs(varBase) < 0.01) return '0';

    var sign  = varBase >= 0 ? '+' : '-';
    var abs   = Math.abs(varBase);
    var ctnN  = Math.floor(abs / (crate * pack));
    var rem   = abs - ctnN * crate * pack;
    var packN = Math.floor(rem / pack);

    var parts = [];
    if (ctnN  > 0) parts.push(sign + ctnN  + ' CTN');
    if (packN > 0) parts.push(sign + packN + ' Pack');
    if (parts.length === 0) parts.push(sign + '< 1 Pack');
    return parts.join(' ');
}

document.querySelectorAll('.opname-input').forEach(function(el) {
    el.addEventListener('input', function() {
        var row    = this.closest('tr');
        var id     = row.dataset.id;
        var crate  = parseFloat(row.dataset.crate) || 0;
        var pack   = parseFloat(row.dataset.pack)  || 1;
        var sysQty = parseFloat(row.dataset.system) || 0;

        var c = parseFloat(row.querySelector('[name$="[physical_crate]"]')?.value) || 0;
        var p = parseFloat(row.querySelector('[name$="[physical_pack]"]')?.value)  || 0;
        var b = parseFloat(row.querySelector('[name$="[physical_base]"]')?.value)  || 0;

        // Hitung total fisik dalam base unit
        var physBase = crate > 0 ? (c * crate * pack) + (p * pack) + b : (p * pack) + b;

        // Update total fisik (tampilkan dalam CTN)
        var totalCell = document.getElementById('total-' + id);
        if (totalCell) {
            if (crate > 0) {
                totalCell.textContent = (physBase / (crate * pack)).toFixed(2);
            } else {
                totalCell.textContent = physBase.toFixed(2);
            }
        }

        // Selisih: hanya Dus + Pack (Pcs/Gr tidak dihitung)
        var physNoPcs   = crate > 0 ? (c * crate * pack) + (p * pack) : (p * pack);
        var sysRounded  = pack > 0 ? Math.floor(sysQty / pack) * pack : sysQty;
        var varBase     = Math.round((physNoPcs - sysRounded) * 10000) / 10000;
        var varText = fmtVar(varBase, crate, pack);
        var varCell = document.getElementById('var-' + id);
        if (varCell) {
            varCell.textContent = varText;
            varCell.className   = 'text-end border-start fw-bold ';
            varCell.className  += varText === '0' ? 'text-muted' : (varBase < 0 ? 'text-danger' : 'text-success');
        }

        // Update nilai (harga × fisik) dan grand total
        var nilaiCell = document.getElementById('nilai-' + id);
        if (nilaiCell) {
            var price = parseFloat(nilaiCell.dataset.price) || 0;
            var nilai = physBase * price;
            nilaiCell.textContent = price > 0 ? 'Rp ' + Math.round(nilai).toLocaleString('id-ID') : '—';
        }

        // Update grand total — hitung langsung dari semua input baris
        var grandTotal = 0;
        document.querySelectorAll('tr[data-id]').forEach(function(r) {
            var nilaiEl = document.getElementById('nilai-' + r.dataset.id);
            if (!nilaiEl) return;
            var price = parseFloat(nilaiEl.dataset.price) || 0;
            var cr    = parseFloat(r.dataset.crate) || 0;
            var pk    = parseFloat(r.dataset.pack)  || 1;
            var c2    = parseFloat(r.querySelector('[name$="[physical_crate]"]')?.value) || 0;
            var p2    = parseFloat(r.querySelector('[name$="[physical_pack]"]')?.value)  || 0;
            var b2    = parseFloat(r.querySelector('[name$="[physical_base]"]')?.value)  || 0;
            var base  = cr > 0 ? (c2 * cr * pk) + (p2 * pk) + b2 : (p2 * pk) + b2;
            grandTotal += base * price;
        });
        var gtCell = document.getElementById('grand-total');
        if (gtCell) gtCell.textContent = 'Rp ' + Math.round(grandTotal).toLocaleString('id-ID');
    });
});
</script>
@endpush
