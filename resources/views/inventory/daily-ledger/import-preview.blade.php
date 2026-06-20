@extends('layouts.app')
@section('title', 'Preview Import Pemakaian')
@section('content')

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h4 class="page-title">Preview Import Pemakaian Harian</h4>
        <p class="text-muted small mb-0">{{ $store->name }} — {{ $bulanLbl }}</p>
    </div>
    <a href="{{ route('inventory.daily-ledger.index', ['store_id' => $store_id, 'month' => $month, 'year' => $year]) }}"
       class="btn btn-outline-secondary btn-sm btn-back">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="alert alert-warning d-flex align-items-start gap-2">
    <i class="bi bi-exclamation-triangle fs-4 flex-shrink-0"></i>
    <div>
        <strong>Ditemukan {{ count($issues) }} pemakaian yang melebihi stok tersedia.</strong>
        <div class="small mt-1">
            File belum disimpan. Periksa daftar di bawah, lalu pilih tindakan:
            <strong>Batalkan & Perbaiki</strong> (rekomendasi) atau <strong>Tetap Simpan</strong> (stok bisa minus).
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header fw-semibold py-2">
        <i class="bi bi-list-ul me-1"></i>Daftar Anomali Stok
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:60px">Tgl</th>
                        <th>Bahan</th>
                        <th class="text-end">Stok Tersedia</th>
                        <th class="text-end">Pemakaian (Excel)</th>
                        <th class="text-end">Selisih (Kurang)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($issues as $i)
                    @php
                        $kurangBase = $i['usage_base'] - $i['avail_base'];
                    @endphp
                    <tr>
                        <td class="text-center">{{ $i['day'] }}</td>
                        <td class="fw-semibold">{{ $i['ingredient'] }}</td>
                        <td class="text-end">
                            {{ number_format($i['avail_pack'], 0, ',', '.') }} pack
                            <small class="text-muted">({{ number_format($i['avail_base'], 0, ',', '.') }} {{ $i['unit'] }})</small>
                        </td>
                        <td class="text-end text-danger fw-bold">
                            {{ number_format($i['usage_pack'], 0, ',', '.') }} pack
                            <small class="text-muted">({{ number_format($i['usage_base'], 0, ',', '.') }} {{ $i['unit'] }})</small>
                        </td>
                        <td class="text-end text-danger">
                            −{{ number_format($kurangBase, 0, ',', '.') }} {{ $i['unit'] }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="d-flex gap-2 justify-content-end">
    <a href="{{ route('inventory.daily-ledger.index', ['store_id' => $store_id, 'month' => $month, 'year' => $year]) }}"
       class="btn btn-success">
        <i class="bi bi-arrow-left me-1"></i>Batalkan & Perbaiki Excel
    </a>
    <form method="POST" action="{{ route('inventory.daily-ledger.import-usage') }}"
          data-confirm="Yakin tetap simpan? Stok beberapa bahan akan jadi MINUS dan perlu opname untuk perbaiki." data-confirm-type="warning" data-confirm-ok="Ya, tetap simpan">
        @csrf
        <input type="hidden" name="store_id"  value="{{ $store_id }}">
        <input type="hidden" name="month"     value="{{ $month }}">
        <input type="hidden" name="year"      value="{{ $year }}">
        <input type="hidden" name="temp_file" value="{{ $temp_file }}">
        <input type="hidden" name="force"     value="1">
        <button type="submit" class="btn btn-outline-danger">
            <i class="bi bi-exclamation-triangle me-1"></i>Tetap Simpan (stok bisa minus)
        </button>
    </form>
</div>

@endsection
