@extends('layouts.app')
@section('title','Input Waste')

@push('styles')
<style>
    #wasteTable { table-layout: fixed; }
    #wasteTable input[type=number]::-webkit-inner-spin-button,
    #wasteTable input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
    #wasteTable input[type=number] { -moz-appearance: textfield; }
</style>
@endpush

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <h4 class="page-title">Input Waste Bahan</h4>
    <a href="{{ route('waste.logs.index') }}" class="btn btn-outline-secondary btn-sm btn-back">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

<form method="POST" action="{{ route('waste.logs.store') }}" id="wasteForm">
    @csrf
    <div class="row g-3">

        {{-- ── Info Waste ── --}}
        <div class="col-lg-3">
            <div class="card">
                <div class="card-header fw-semibold">Info Waste</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Toko <span class="text-danger">*</span></label>
                        <select name="store_id" class="form-select" required>
                            <option value="">— Pilih Toko —</option>
                            @foreach($stores as $s)
                                <option value="{{ $s->id }}" {{ (old('store_id') == $s->id || (count($stores)==1)) ? 'selected' : '' }}>
                                    {{ $s->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="waste_date" class="form-control"
                               value="{{ old('waste_date', date('Y-m-d')) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Catatan</label>
                        <textarea name="notes" class="form-control" rows="3"
                                  placeholder="Alasan waste (expired, rusak, tumpah, dll)">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Daftar Bahan ── --}}
        <div class="col-lg-9">
            @error('stock_error')
            <div class="alert alert-danger alert-dismissible fade show mb-3 py-2" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $message }}
                <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
            </div>
            @enderror
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center fw-semibold">
                    <span>Bahan yang Di-waste</span>
                    <button type="button" class="btn btn-sm btn-danger" onclick="addWasteRow()">
                        <i class="bi bi-plus-circle me-1"></i>Tambah Bahan
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0" id="wasteTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:24%">Bahan <span class="text-danger">*</span></th>
                                    <th style="width:16%">Kemasan</th>
                                    <th style="width:8%">Dus</th>
                                    <th style="width:8%">Pack</th>
                                    <th style="width:9%">Pcs/Gr</th>
                                    <th style="width:20%">Nilai Kerugian</th>
                                    <th style="width:5%"></th>
                                </tr>
                            </thead>
                            <tbody id="wasteContainer"></tbody>
                        </table>
                    </div>
                    <div id="wasteEmpty" class="text-center text-muted py-3 small">
                        <i class="bi bi-trash3 me-1"></i>Belum ada bahan. Klik "+ Tambah Bahan" untuk menambahkan.
                    </div>
                </div>
            </div>

            {{-- ── Rework: rusak tapi masih bisa dipakai ── --}}
            <div class="card mt-3">
                <div class="card-header d-flex justify-content-between align-items-center fw-semibold"
                     style="background:#fff8e1;border-color:#ffe082">
                    <span><i class="bi bi-arrow-repeat me-1 text-warning"></i>Rusak tapi Masih Bisa Dipakai</span>
                    <button type="button" class="btn btn-sm btn-warning" onclick="addReworkRow()">
                        <i class="bi bi-plus-circle me-1"></i>Tambah
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0" id="reworkTable">
                            <thead style="background:#fff3cd">
                                <tr>
                                    <th style="width:28%">Bahan <span class="text-danger">*</span></th>
                                    <th style="width:18%">Kemasan</th>
                                    <th style="width:10%">Dus</th>
                                    <th style="width:10%">Pack</th>
                                    <th style="width:11%">Pcs/Gr</th>
                                    <th style="width:18%;color:#888;font-style:italic;font-size:.72rem">Tidak ada kerugian</th>
                                    <th style="width:5%"></th>
                                </tr>
                            </thead>
                            <tbody id="reworkContainer">
                                {{-- Baris ditambah via JS --}}
                            </tbody>
                        </table>
                    </div>
                    <div id="reworkEmpty" class="text-center text-muted py-3 small">
                        <i class="bi bi-arrow-repeat me-1"></i>Belum ada bahan. Klik "+ Tambah" jika ada bahan rusak yang masih bisa dipakai.
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-danger px-4"
                        onclick="return confirm('Catat waste ini? Stok akan berkurang.')">
                    <i class="bi bi-trash3 me-1"></i> Catat Waste
                </button>
                <a href="{{ route('waste.logs.index') }}" class="btn btn-outline-secondary ms-2">Batal</a>
            </div>
        </div>

    </div>
</form>
@endsection

@push('scripts')
<script>
var wRowCount    = 1;
var ingredientJs = @json($ingredientJs);
var batchCache   = {};   // key: ingId+'_'+storeId → [{remaining_qty, price_per_base}, ...]

// ── Format Rupiah ─────────────────────────────────────────────────────────────
function fmtRp(n) {
    return 'Rp ' + Math.round(n).toLocaleString('id-ID');
}

// ── Hitung total base qty dari row (untuk kalkulasi biaya) ───────────────────
function calcBaseQty(row) {
    var ctb  = parseFloat(row.dataset.ctb) || 0;
    var ptb  = parseFloat(row.dataset.ptb) || 0;
    var qtyC = parseFloat(row.querySelector('input[name$="[qty_crate]"]')?.value) || 0;
    var qtyP = parseFloat(row.querySelector('input[name$="[qty_pack]"]')?.value)  || 0;
    var qtyB = parseFloat(row.querySelector('input[name$="[qty_base]"]')?.value)  || 0;
    return (qtyC * ctb) + (qtyP * ptb) + qtyB;
}

// ── Hitung qty kemasan saja (Dus+Pack, tanpa pcs/gr) untuk cek stok ──────────
// Pcs/Gr tidak memotong saldo stok, jadi tidak perlu divalidasi.
function calcPackagingQty(row) {
    var ctb  = parseFloat(row.dataset.ctb) || 0;
    var ptb  = parseFloat(row.dataset.ptb) || 0;
    if (ctb <= 0 && ptb <= 0) return 0; // tidak ada kemasan → tidak ada yang dicek
    var qtyC = parseFloat(row.querySelector('input[name$="[qty_crate]"]')?.value) || 0;
    var qtyP = parseFloat(row.querySelector('input[name$="[qty_pack]"]')?.value)  || 0;
    return (qtyC * ctb) + (qtyP * ptb);
}

// ── Hitung total stok tersedia dari batch list ────────────────────────────────
function calcTotalStock(batches) {
    return batches.reduce(function(sum, b){ return sum + (parseFloat(b.remaining_qty) || 0); }, 0);
}

// ── Tampilkan / hapus peringatan stok ────────────────────────────────────────
function showStockWarning(row, available, requested, unit) {
    var existing = row.querySelector('.stock-warning');
    if (requested > available + 0.001) {
        if (!existing) {
            existing = document.createElement('div');
            existing.className = 'stock-warning text-danger small mt-1';
            existing.style.fontSize = '.72rem';
            var cell = row.querySelector('td:nth-child(6)');
            if (cell) cell.appendChild(existing);
        }
        var ctb = parseFloat(row.dataset.ctb) || 0;
        var ptb = parseFloat(row.dataset.ptb) || 0;
        var availStr = '';
        if (ctb > 0 && ptb > 0) {
            var dus  = Math.floor(available / ctb);
            var rem  = available - (dus * ctb);
            var pack = Math.floor(rem / ptb);
            var base = Math.round((rem - pack * ptb) * 1000) / 1000;
            var parts = [];
            if (dus  > 0)      parts.push(dus + ' Dus');
            if (pack > 0)      parts.push(pack + ' Pack');
            if (base > 0.001)  parts.push(base.toLocaleString('id-ID', {maximumFractionDigits:2}) + ' ' + unit);
            availStr = parts.length > 0 ? parts.join(' ') : '0 ' + unit;
        } else {
            availStr = available.toLocaleString('id-ID', {maximumFractionDigits:2}) + ' ' + unit;
        }
        existing.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i>Melebihi saldo stok (tersedia: ' + availStr + ')';
    } else {
        if (existing) existing.remove();
    }
}

function clearStockWarning(row) {
    var w = row.querySelector('.stock-warning');
    if (w) w.remove();
}

// ── Hitung biaya FIFO dari batch list untuk sejumlah qty ─────────────────────
// Batch diambil dari yang paling lama (index 0 = masuk pertama)
function calcFifoCost(batches, qty, offset) {
    var skip      = offset || 0;   // qty yang sudah dipakai baris waste sebelumnya (FIFO)
    var remaining = qty;
    var cost      = 0;
    for (var i = 0; i < batches.length; i++) {
        var avail = parseFloat(batches[i].remaining_qty) || 0;
        // Lewati dulu porsi yang sudah dikonsumsi baris sebelumnya
        if (skip > 0) {
            var s = Math.min(skip, avail);
            avail -= s; skip -= s;
        }
        if (remaining <= 0 || avail <= 0) continue;
        var take  = Math.min(remaining, avail);
        cost     += take * (parseFloat(batches[i].price_per_base) || 0);
        remaining -= take;
    }
    // Kalau qty melebihi stok → estimasi pakai harga batch terakhir
    if (remaining > 0 && batches.length > 0) {
        cost += remaining * (parseFloat(batches[batches.length - 1].price_per_base) || 0);
    }
    return cost;
}

// ── Total base-qty baris waste SEBELUM baris ini dengan bahan & kemasan sama ──
// Dipakai sebagai offset FIFO supaya baris berikutnya melanjutkan dari batch
// yang belum terpakai (konsisten dengan cara server memotong stok berurutan).
function priorConsumedSameBatch(currentRow, ingId, pkgId) {
    var total = 0;
    var rows  = document.querySelectorAll('.waste-row');
    for (var i = 0; i < rows.length; i++) {
        if (rows[i] === currentRow) break;
        var rIng = rows[i].querySelector('.ing-select')?.value;
        var rPkg = rows[i].querySelector('.pkg-select')?.value || '';
        if (rIng == ingId && rPkg === (pkgId || '')) total += calcBaseQty(rows[i]);
    }
    return total;
}

// ── Recalc kerugian SEMUA baris waste (urut) — perlu karena baris berikutnya
//    bergantung pada konsumsi baris sebelumnya ───────────────────────────────
function recalcAllWasteLosses() {
    document.querySelectorAll('.waste-row').forEach(function(r) {
        fetchPriceAndCalc(r.id.replace('wrow-', ''));
    });
}

// ── Fetch FIFO batches untuk 1 ingredient → cache → callback(batches) ────────
function fetchBatches(ingId, storeId, packagingId, callback) {
    if (!ingId || !storeId) { callback([]); return; }
    var pkg = packagingId || '';
    var key = ingId + '_' + storeId + '_' + (pkg || 'all');
    if (batchCache[key] !== undefined) { callback(batchCache[key]); return; }
    var url = '{{ url("api-internal/ingredient") }}/' + ingId + '/stock-price?store_id=' + storeId;
    if (pkg) url += '&packaging_id=' + pkg;
    fetch(url)
        .then(function(r){ return r.json(); })
        .then(function(data) {
            // Batches sudah diurutkan by id (FIFO) dari API; difilter per kemasan jika dipilih
            batchCache[key] = data.batches || [];
            callback(batchCache[key]);
        })
        .catch(function(){ callback([]); });
}

// ── Kalkulasi & tampilkan kerugian untuk satu row ─────────────────────────────
function fetchPriceAndCalc(idx) {
    var row     = document.getElementById('wrow-' + idx);
    if (!row) return;
    var ingId   = row.querySelector('.ing-select')?.value;
    var ingOpt  = row.querySelector('.ing-select')?.selectedOptions[0];
    var type    = ingOpt ? ingOpt.dataset.type : '';
    var storeId = document.querySelector('select[name="store_id"]')?.value;
    var display = row.querySelector('.loss-display');

    if (!ingId) return;
    if (!storeId) {
        if (display) display.textContent = '— Pilih toko dulu';
        return;
    }

    var totalBase = calcBaseQty(row);
    if (totalBase <= 0) { showLoss(row, 0); return; }

    if (display) display.innerHTML =
        '<span class="text-muted" style="font-size:.72rem"><i class="bi bi-hourglass-split me-1"></i>Menghitung...</span>';

    var ing  = ingredientJs.find(function(i){ return i.id == ingId; });

    if (type === 'raw') {
        // Bahan baku: FIFO dari batch kemasan yang dipilih (konsisten dgn server).
        // offset = qty yg sudah dipakai baris waste sebelumnya (bahan+kemasan sama).
        var pkgId  = row.querySelector('.pkg-select')?.value || '';
        var offset = priorConsumedSameBatch(row, ingId, pkgId);
        fetchBatches(ingId, storeId, pkgId, function(batches) {
            showLoss(row, calcFifoCost(batches, totalBase, offset));
        });

    } else {
        // Setengah jadi: hitung biaya dari komponen

        if (!ing || !ing.compositions || ing.compositions.length === 0) {
            if (display) display.textContent = '— Tidak ada resep';
            return;
        }

        var promises = ing.compositions.map(function(c) {
            var rawQty = c.qty_needed * totalBase;
            return new Promise(function(resolve) {
                fetchBatches(c.child_id, storeId, null, function(batches) {
                    resolve(calcFifoCost(batches, rawQty));
                });
            });
        });

        Promise.all(promises).then(function(values) {
            showLoss(row, values.reduce(function(s, v){ return s + v; }, 0));
        });
    }
}

function showLoss(row, loss) {
    var display   = row.querySelector('.loss-display');
    var totalBase = calcBaseQty(row);
    if (!display) return;
    display.textContent      = totalBase > 0 ? fmtRp(loss) : '—';
    display.style.color      = loss > 0 ? '#dc3545' : '#6c757d';
    display.style.fontWeight = loss > 0 ? '600' : 'normal';
    var hidden = row.querySelector('.hidden-raw-loss');
    if (hidden) hidden.value = Math.round(loss);
}

function recalcLoss(idx) { fetchPriceAndCalc(idx); }

// ── Update tampilan kolom kerugian sesuai tipe bahan ─────────────────────────
function updateLossDisplay(idx, price, type) {
    var row     = document.getElementById('wrow-' + idx);
    if (!row) return;
    var nomWrap = row.querySelector('.wrap-nominal');   // input manual (tidak dipakai lagi)
    var rawWrap = row.querySelector('.wrap-raw-loss');  // auto display

    // Kedua tipe kini auto-display dari FIFO
    if (nomWrap) nomWrap.style.display = 'none';
    if (rawWrap) rawWrap.style.display = 'block';

    if (type) fetchPriceAndCalc(idx);
}

// ── Build <optgroup> options ──────────────────────────────────────────────────
function buildIngOpts() {
    var semi = ingredientJs.filter(function(i){ return i.type === 'semi_finished'; });
    var raw  = ingredientJs.filter(function(i){ return i.type === 'raw'; });
    var html = '<option value="">— Pilih Bahan —</option>';
    if (semi.length) {
        html += '<optgroup label="Setengah Jadi">';
        semi.forEach(function(i){ html += '<option value="'+i.id+'" data-type="semi_finished">'+i.name+'</option>'; });
        html += '</optgroup>';
    }
    if (raw.length) {
        html += '<optgroup label="Bahan Baku">';
        raw.forEach(function(i){ html += '<option value="'+i.id+'" data-type="raw">'+i.name+'</option>'; });
        html += '</optgroup>';
    }
    return html;
}

// ── Called when ingredient changes ───────────────────────────────────────────
function onIngredientChange(idx) {
    var row    = document.getElementById('wrow-' + idx);
    var select = row.querySelector('.ing-select');
    var ingId  = select.value;
    var opt    = select.options[select.selectedIndex];
    var type   = opt ? opt.dataset.type : '';

    // Invalidate price cache for this row
    row.dataset.ctb = 0;
    row.dataset.ptb = 0;

    // Reset packaging
    var pkgSel = row.querySelector('.pkg-select');
    pkgSel.innerHTML = '<option value="">— Pilih Kemasan —</option>';
    row.querySelector('.wrap-packaging').classList.add('d-none');
    row.querySelector('.wrap-crate').classList.add('d-none');
    row.querySelector('.wrap-pack').classList.add('d-none');

    // Reset unit label
    var ing  = ingredientJs.find(function(i){ return i.id == ingId; });
    var unit = ing ? ing.unit : 'pcs';
    row.querySelector('.label-unit').textContent = unit;

    // Show/hide kerugian
    updateLossDisplay(idx, null, type);
    var nomInput = row.querySelector('.nominal-input');
    if (nomInput) nomInput.required = (type === 'semi_finished');

    recheckAllStockWarnings(); // cek stok setelah bahan berubah (packaging sudah di-reset di atas)

    if (!ing || !ing.packagings || ing.packagings.length === 0) return;

    // Build packaging options
    var pkgHtml = '<option value="">— Pilih Kemasan —</option>';
    ing.packagings.forEach(function(p) {
        var ctb = p.crate_to_pack * p.pack_to_base;
        pkgHtml += '<option value="'+p.id+'"'
            + ' data-ctb="'+ctb+'"'
            + ' data-ptb="'+p.pack_to_base+'">'
            + p.packaging_name
            + ' (1 Dus = '+p.crate_to_pack+' Pack × '+p.pack_to_base+' '+unit+')'
            + '</option>';
    });
    pkgSel.innerHTML = pkgHtml;
    row.querySelector('.wrap-packaging').classList.remove('d-none');

    if (ing.packagings.length === 1) {
        pkgSel.selectedIndex = 1;
        onPackagingChange(idx);
    }
}

// ── Called when packaging changes ────────────────────────────────────────────
function onPackagingChange(idx) {
    var row    = document.getElementById('wrow-' + idx);
    var pkgSel = row.querySelector('.pkg-select');
    var opt    = pkgSel.options[pkgSel.selectedIndex];
    var ctb    = parseFloat(opt ? opt.dataset.ctb : 0) || 0;
    var ptb    = parseFloat(opt ? opt.dataset.ptb : 0) || 0;

    row.dataset.ctb = ctb;
    row.dataset.ptb = ptb;

    row.querySelector('.wrap-crate').classList.toggle('d-none', ctb <= 0);
    row.querySelector('.wrap-pack').classList.toggle('d-none',  ptb <= 0);
    recalcAllWasteLosses();
    recheckAllStockWarnings();
}

// ── Qty input changed → recalc loss & cek stok ────────────────────────────────
document.addEventListener('input', function(e) {
    var row = e.target.closest('.waste-row');
    if (!row) return;
    if (!e.target.matches('input[name*="qty_crate"], input[name*="qty_pack"], input[name*="qty_base"]')) return;
    recalcAllWasteLosses();
    recheckAllStockWarnings();
});

// ── Store changed → reset all price caches & recalc ──────────────────────────
document.querySelector('select[name="store_id"]')?.addEventListener('change', function() {
    batchCache = {};
    document.querySelectorAll('.waste-row').forEach(function(row) {
        var idx    = row.id.replace('wrow-', '');
        var ingOpt = row.querySelector('.ing-select')?.selectedOptions[0];
        var type   = ingOpt ? ingOpt.dataset.type : '';
        if (type) fetchPriceAndCalc(idx);
    });
    recheckAllStockWarnings();
});

// ── Add row ───────────────────────────────────────────────────────────────────
function addWasteRow() {
    document.getElementById('wasteEmpty').style.display = 'none';
    var idx  = wRowCount++;
    var opts = buildIngOpts();
    var html =
        '<td>' +
            '<select name="items['+idx+'][ingredient_id]" class="form-select form-select-sm ing-select" required onchange="onIngredientChange('+idx+')">' +
                opts +
            '</select>' +
        '</td>' +
        '<td>' +
            '<div class="wrap-packaging d-none">' +
                '<select name="items['+idx+'][packaging_id]" class="form-select form-select-sm pkg-select" onchange="onPackagingChange('+idx+')">' +
                    '<option value="">— Pilih Kemasan —</option>' +
                '</select>' +
            '</div>' +
            '<span class="text-muted small">—</span>' +
        '</td>' +
        '<td><div class="wrap-crate d-none"><input type="number" name="items['+idx+'][qty_crate]" class="form-control form-control-sm" min="0" placeholder="0"></div><span class="text-muted">—</span></td>' +
        '<td><div class="wrap-pack d-none"><input type="number" name="items['+idx+'][qty_pack]" class="form-control form-control-sm" min="0" placeholder="0"></div><span class="text-muted">—</span></td>' +
        '<td><input type="number" name="items['+idx+'][qty_base]" class="form-control form-control-sm" step="0.01" min="0" placeholder="0"><span class="label-unit text-muted small d-block" style="font-size:.7rem">pcs</span></td>' +
        '<td>' +
            '<div class="wrap-nominal" style="display:none"></div>' +
            '<div class="wrap-raw-loss">' +
                '<span class="loss-display text-muted small fw-semibold">—</span>' +
                '<input type="hidden" name="items['+idx+'][nominal_loss]" class="hidden-raw-loss" value="0">' +
            '</div>' +
        '</td>' +
        '<td class="text-center">' +
            '<button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" onclick="removeWasteRow('+idx+')" title="Hapus">' +
                '<i class="bi bi-x-lg"></i>' +
            '</button>' +
        '</td>';

    var tr = document.createElement('tr');
    tr.className = 'waste-row';
    tr.id        = 'wrow-' + idx;
    tr.dataset.ctb = 0;
    tr.dataset.ptb = 0;
    tr.innerHTML = html;
    document.getElementById('wasteContainer').appendChild(tr);
    updateRemoveBtns();
}

function removeWasteRow(idx) {
    document.getElementById('wrow-' + idx).remove();
    if (!document.querySelector('.waste-row')) {
        document.getElementById('wasteEmpty').style.display = '';
    }
    updateRemoveBtns();
    recalcAllWasteLosses();
    recheckAllStockWarnings();
}

function updateRemoveBtns() {
    document.querySelectorAll('.waste-row .btn-remove-row').forEach(function(btn) {
        btn.style.display = 'inline-block';
    });
}

// ══ REWORK SECTION ════════════════════════════════════════════════════════════
var rRowCount = 0;

function buildReworkIngOpts() {
    // Rework hanya untuk bahan baku (raw) — yang bisa dipakai lagi secara fisik
    var all = ingredientJs;
    var html = '<option value="">— Pilih Bahan —</option>';
    var semi = all.filter(function(i){ return i.type === 'semi_finished'; });
    var raw  = all.filter(function(i){ return i.type === 'raw'; });
    if (semi.length) {
        html += '<optgroup label="Setengah Jadi">';
        semi.forEach(function(i){ html += '<option value="'+i.id+'" data-type="semi_finished">'+i.name+'</option>'; });
        html += '</optgroup>';
    }
    if (raw.length) {
        html += '<optgroup label="Bahan Baku">';
        raw.forEach(function(i){ html += '<option value="'+i.id+'" data-type="raw">'+i.name+'</option>'; });
        html += '</optgroup>';
    }
    return html;
}

function addReworkRow(preIngId, prePkgId, preCrate, prePack, preBase) {
    var idx  = rRowCount++;
    var opts = buildReworkIngOpts();
    var html =
        '<td>' +
            '<select name="reworks['+idx+'][ingredient_id]" class="form-select form-select-sm rw-ing-select" required onchange="onReworkIngChange('+idx+')">' +
                opts +
            '</select>' +
        '</td>' +
        '<td>' +
            '<div class="rw-wrap-pkg d-none">' +
                '<select name="reworks['+idx+'][packaging_id]" class="form-select form-select-sm rw-pkg-select" onchange="onReworkPkgChange('+idx+')">' +
                    '<option value="">— Pilih Kemasan —</option>' +
                '</select>' +
            '</div>' +
            '<span class="text-muted small rw-no-pkg">—</span>' +
        '</td>' +
        '<td><div class="rw-wrap-crate d-none"><input type="number" name="reworks['+idx+'][qty_crate]" class="form-control form-control-sm" min="0" placeholder="0"></div><span class="text-muted rw-dash-c">—</span></td>' +
        '<td><div class="rw-wrap-pack d-none"><input type="number" name="reworks['+idx+'][qty_pack]" class="form-control form-control-sm" min="0" placeholder="0"></div><span class="text-muted rw-dash-p">—</span></td>' +
        '<td><input type="number" name="reworks['+idx+'][qty_base]" class="form-control form-control-sm" step="0.01" min="0" placeholder="0"><span class="rw-label-unit text-muted small d-block" style="font-size:.7rem">pcs</span></td>' +
        '<td><span class="text-muted small fst-italic" style="font-size:.72rem">—</span></td>' +
        '<td class="text-center">' +
            '<button type="button" class="btn btn-sm btn-outline-warning" onclick="removeReworkRow('+idx+')" title="Hapus"><i class="bi bi-x-lg"></i></button>' +
        '</td>';

    var tr = document.createElement('tr');
    tr.className   = 'rework-row';
    tr.id          = 'rrow-' + idx;
    tr.dataset.ctb = 0;
    tr.dataset.ptb = 0;
    tr.innerHTML   = html;
    document.getElementById('reworkContainer').appendChild(tr);
    document.getElementById('reworkEmpty').style.display = 'none';

    // Pre-fill jika ada
    if (preIngId) {
        tr.querySelector('.rw-ing-select').value = preIngId;
        onReworkIngChange(idx);
        if (prePkgId) {
            tr.querySelector('.rw-pkg-select').value = String(prePkgId);
            onReworkPkgChange(idx);
        }
        var qc = tr.querySelector('input[name$="[qty_crate]"]');
        var qp = tr.querySelector('input[name$="[qty_pack]"]');
        var qb = tr.querySelector('input[name$="[qty_base]"]');
        if (qc && preCrate) qc.value = preCrate;
        if (qp && prePack)  qp.value = prePack;
        if (qb && preBase)  qb.value = preBase;
    }
}

function removeReworkRow(idx) {
    document.getElementById('rrow-' + idx)?.remove();
    if (!document.querySelector('.rework-row')) {
        document.getElementById('reworkEmpty').style.display = '';
    }
    recheckAllStockWarnings();
}

function onReworkIngChange(idx) {
    var row    = document.getElementById('rrow-' + idx);
    var select = row.querySelector('.rw-ing-select');
    var ingId  = select.value;
    row.dataset.ctb = 0;
    row.dataset.ptb = 0;

    var pkgSel = row.querySelector('.rw-pkg-select');
    pkgSel.innerHTML = '<option value="">— Pilih Kemasan —</option>';
    row.querySelector('.rw-wrap-pkg').classList.add('d-none');
    row.querySelector('.rw-wrap-crate').classList.add('d-none');
    row.querySelector('.rw-wrap-pack').classList.add('d-none');

    var ing  = ingredientJs.find(function(i){ return i.id == ingId; });
    var unit = ing ? ing.unit : 'pcs';
    row.querySelector('.rw-label-unit').textContent = unit;

    if (!ing || !ing.packagings || ing.packagings.length === 0) {
        checkReworkStock(idx);
        return;
    }

    var pkgHtml = '<option value="">— Pilih Kemasan —</option>';
    ing.packagings.forEach(function(p) {
        var ctb = p.crate_to_pack * p.pack_to_base;
        pkgHtml += '<option value="'+p.id+'" data-ctb="'+ctb+'" data-ptb="'+p.pack_to_base+'">'
            + p.packaging_name + ' (1 Dus = '+p.crate_to_pack+' Pack × '+p.pack_to_base+' '+unit+')'
            + '</option>';
    });
    pkgSel.innerHTML = pkgHtml;
    row.querySelector('.rw-wrap-pkg').classList.remove('d-none');
    row.querySelector('.rw-no-pkg').style.display = 'none';

    if (ing.packagings.length === 1) {
        pkgSel.selectedIndex = 1;
        onReworkPkgChange(idx);
    }
}

function onReworkPkgChange(idx) {
    var row    = document.getElementById('rrow-' + idx);
    var pkgSel = row.querySelector('.rw-pkg-select');
    var opt    = pkgSel.options[pkgSel.selectedIndex];
    var ctb    = parseFloat(opt ? opt.dataset.ctb : 0) || 0;
    var ptb    = parseFloat(opt ? opt.dataset.ptb : 0) || 0;
    row.dataset.ctb = ctb;
    row.dataset.ptb = ptb;
    row.querySelector('.rw-wrap-crate').classList.toggle('d-none', ctb <= 0);
    row.querySelector('.rw-wrap-pack').classList.toggle('d-none',  ptb <= 0);
    checkReworkStock(idx);
}

function calcReworkBaseQty(row) {
    var ctb  = parseFloat(row.dataset.ctb) || 0;
    var ptb  = parseFloat(row.dataset.ptb) || 0;
    var qtyC = parseFloat(row.querySelector('input[name$="[qty_crate]"]')?.value) || 0;
    var qtyP = parseFloat(row.querySelector('input[name$="[qty_pack]"]')?.value)  || 0;
    var qtyB = parseFloat(row.querySelector('input[name$="[qty_base]"]')?.value)  || 0;
    return (qtyC * ctb) + (qtyP * ptb) + qtyB;
}

function checkReworkStock(idx) {
    recheckAllStockWarnings();
}

// ── Cek stok kumulatif semua baris (waste + rework) ───────────────────────────
// Menjumlahkan pkgQty per ingredient lintas seksi lalu bandingkan ke saldo stok.
// Dipanggil setiap kali qty, bahan, kemasan, atau toko berubah di baris mana pun.
function recheckAllStockWarnings() {
    var storeId = document.querySelector('select[name="store_id"]')?.value;
    if (!storeId) {
        document.querySelectorAll('.waste-row, .rework-row').forEach(clearStockWarning);
        return;
    }

    var rowsByIng = {}; // ingId → [{row, pkgQty, unit}, ...]

    document.querySelectorAll('.waste-row').forEach(function(row) {
        var ingId = row.querySelector('.ing-select')?.value;
        if (!ingId) { clearStockWarning(row); return; }
        var pkgQty = calcPackagingQty(row);
        if (pkgQty <= 0) { clearStockWarning(row); return; }
        var ing  = ingredientJs.find(function(i){ return i.id == ingId; });
        var unit = ing ? ing.unit : 'pcs';
        if (!rowsByIng[ingId]) rowsByIng[ingId] = [];
        rowsByIng[ingId].push({ row: row, pkgQty: pkgQty, unit: unit });
    });

    document.querySelectorAll('.rework-row').forEach(function(row) {
        var ingId = row.querySelector('.rw-ing-select')?.value;
        if (!ingId) { clearStockWarning(row); return; }
        var pkgQty = calcPackagingQty(row);
        if (pkgQty <= 0) { clearStockWarning(row); return; }
        var ing  = ingredientJs.find(function(i){ return i.id == ingId; });
        var unit = ing ? ing.unit : 'pcs';
        if (!rowsByIng[ingId]) rowsByIng[ingId] = [];
        rowsByIng[ingId].push({ row: row, pkgQty: pkgQty, unit: unit });
    });

    // Per ingredient: bandingkan total pkgQty gabungan vs stok tersedia
    Object.keys(rowsByIng).forEach(function(ingId) {
        var entries  = rowsByIng[ingId];
        var totalQty = entries.reduce(function(s, e){ return s + e.pkgQty; }, 0);
        fetchBatches(ingId, storeId, null, function(batches) {
            var available = calcTotalStock(batches);
            entries.forEach(function(entry) {
                showStockWarning(entry.row, available, totalQty, entry.unit);
            });
        });
    });
}

// ── Qty input changed → cek stok rework ──────────────────────────────────────
document.addEventListener('input', function(e) {
    var row = e.target.closest('.rework-row');
    if (!row) return;
    if (!e.target.matches('input[name*="qty_crate"], input[name*="qty_pack"], input[name*="qty_base"]')) return;
    var idx = row.id.replace('rrow-', '');
    checkReworkStock(idx);
});
</script>
@endpush
