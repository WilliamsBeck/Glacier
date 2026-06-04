@extends('layouts.app')
@section('title','Edit Produksi')
@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <h4 class="page-title">Edit Produksi</h4>
    <a href="{{ route('production.logs.show', $log) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

<form method="POST" action="{{ route('production.logs.update', $log) }}">
    @csrf @method('PUT')

    <div class="row g-3">
        {{-- ── Info Produksi ── --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header fw-semibold">Info Produksi</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Toko</label>
                        <input type="text" class="form-control" value="{{ $log->store->name }}" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tanggal Produksi <span class="text-danger">*</span></label>
                        <input type="date" name="production_date" class="form-control" required
                               value="{{ old('production_date', $log->production_date->format('Y-m-d')) }}">
                        @error('production_date')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Catatan</label>
                        <textarea name="notes" class="form-control" rows="3"
                                  placeholder="Opsional">{{ old('notes', $log->notes) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Detail Produksi ── --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header fw-semibold">Detail Bahan Diproduksi</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Bahan Setengah Jadi <span class="text-danger">*</span></label>
                        <select name="semi_finished_id" id="semiSelect" class="form-select" required
                                onchange="updateComposition()">
                            <option value="">— Pilih Bahan —</option>
                            @foreach($semiFinished as $sf)
                                <option value="{{ $sf->id }}"
                                    data-unit="{{ $sf->unit_base }}"
                                    data-compositions='@json($sf->compositions->map(fn($c)=>["name"=>$c->child->name,"qty"=>$c->qty_needed,"unit"=>$c->child->unit_base]))'
                                    {{ old('semi_finished_id', $log->semi_finished_id) == $sf->id ? 'selected' : '' }}>
                                    {{ $sf->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('semi_finished_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Qty Diproduksi <span class="text-danger">*</span></label>
                        <div class="input-group" style="max-width:280px">
                            <input type="number" name="qty_produced" id="qtyInput" class="form-control"
                                   step="{{ strtolower($log->semiFinished->unit_base ?? '') === 'gram' ? '1' : '0.01' }}"
                                   min="{{ strtolower($log->semiFinished->unit_base ?? '') === 'gram' ? '1' : '0.01' }}" required
                                   value="{{ old('qty_produced', strtolower($log->semiFinished->unit_base ?? '') === 'gram' ? (int)$log->qty_produced : $log->qty_produced) }}"
                                   oninput="updateComposition()">
                            <span class="input-group-text" id="unitLabel">{{ $log->semiFinished->unit_base }}</span>
                        </div>
                        @error('qty_produced')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    {{-- Preview komposisi --}}
                    <div id="compositionPreview" class="mt-3">
                        @if($log->items->isNotEmpty())
                        <label class="form-label fw-semibold text-muted small">Bahan Baku yang Digunakan</label>
                        <div class="d-flex flex-wrap gap-2" id="compBadges">
                            @foreach($log->items as $item)
                            <span class="badge bg-light text-dark border" style="font-size:.82rem;font-weight:500">
                                {{ $item->rawIngredient->name }}:
                                <strong>{{ number_format($item->qty_consumed, 2, ',', '.') }} {{ $item->rawIngredient->unit_base }}</strong>
                            </span>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-check-lg me-1"></i>Simpan Perubahan
                </button>
                <a href="{{ route('production.logs.show', $log) }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </div>
    </div>
</form>
@endsection

@php
$semiFinishedJs = $semiFinished->map(function($sf) {
    return [
        'id'   => $sf->id,
        'unit' => $sf->unit_base,
        'compositions' => $sf->compositions->map(function($c) {
            return [
                'name' => $c->child->name ?? '?',
                'qty'  => (float) $c->qty_needed,
                'unit' => $c->child->unit_base ?? '',
            ];
        })->values()->all(),
    ];
})->values()->all();
@endphp
@push('scripts')
<script>
var semiFinishedJs = @json($semiFinishedJs);

function fmtQty(val, unit) {
    return (unit || '').toLowerCase() === 'gram'
        ? Math.round(val).toLocaleString('id-ID')
        : val.toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2});
}

function updateComposition() {
    var sel    = document.getElementById('semiSelect');
    var qtyInp = document.getElementById('qtyInput');
    var ingId  = sel.value;
    var qty    = parseFloat(qtyInp.value) || 0;
    var unitL  = document.getElementById('unitLabel');
    var prev   = document.getElementById('compositionPreview');

    var sf = semiFinishedJs.find(function(i){ return i.id == ingId; });
    var sfUnit = sf ? sf.unit : '';
    unitL.textContent = sfUnit;

    // Step input: bulat untuk gram
    var sfIsGram = sfUnit.toLowerCase() === 'gram';
    qtyInp.step = sfIsGram ? '1' : '0.01';
    qtyInp.min  = sfIsGram ? '1' : '0.01';

    if (!sf || !sf.compositions.length) {
        prev.innerHTML = ingId
            ? '<span class="text-warning small"><i class="bi bi-exclamation-triangle me-1"></i>Bahan ini belum punya komposisi</span>'
            : '';
        return;
    }

    var badgesHtml = sf.compositions.map(function(c) {
        var qtyStr = qty > 0 ? ': <strong>' + fmtQty(c.qty * qty, c.unit) + ' ' + c.unit + '</strong>' : '';
        return '<span class="badge bg-light text-dark border me-1" style="font-size:.82rem;font-weight:500">'
            + c.name + qtyStr + '</span>';
    }).join('');

    prev.innerHTML = '<label class="form-label fw-semibold text-muted small d-block">Bahan Baku yang Digunakan</label>'
        + '<div class="d-flex flex-wrap gap-1">' + badgesHtml + '</div>';
}

// Init preview on load
updateComposition();
</script>
@endpush
