@extends('layouts.app')
@section('title', 'Mutasi Stok')

@section('content')
<div class="page-header d-flex justify-content-between align-items-start">
    <div>
        <h4 class="page-title">Mutasi Stok</h4>
        <p class="text-muted mb-0">Daftar semua transaksi masuk/keluar bahan baku</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('inventory.mutations.export', request()->query()) }}" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
        </a>
        <a href="{{ route('inventory.mutations.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Input Mutasi
        </a>
    </div>
</div>

{{-- FILTER --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold mb-1">Tipe</label>
                <select name="type" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    <option value="purchase_zhisheng"  {{ request('type') === 'purchase_zhisheng'  ? 'selected' : '' }}>Pembelian Pusat</option>
                    <option value="purchase_supplier"  {{ request('type') === 'purchase_supplier'  ? 'selected' : '' }}>Pembelian Supplier Lokal</option>
                    <option value="sale_internal"      {{ request('type') === 'sale_internal'      ? 'selected' : '' }}>Pembelian Internal</option>
                    <option value="sale_external"      {{ request('type') === 'sale_external'      ? 'selected' : '' }}>Pembelian Eksternal</option>
                    <option value="opening_stock"      {{ request('type') === 'opening_stock'      ? 'selected' : '' }}>Input Stok Awal</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold mb-1">Toko</label>
                <select name="store_id" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach($stores as $store)
                        <option value="{{ $store->id }}" {{ request('store_id') == $store->id ? 'selected' : '' }}>
                            {{ $store->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    <option value="draft"     {{ request('status') === 'draft'     ? 'selected' : '' }}>Draft</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold mb-1">Dari</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold mb-1">Sampai</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>
            <div class="col-6 col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">Cari</button>
                <a href="{{ route('inventory.mutations.index') }}" class="btn btn-outline-secondary btn-sm">×</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" style="font-size:.875rem">
                <thead class="table-dark">
                    <tr>
                        <th style="min-width:130px">No SJ</th>
                        <th style="min-width:130px">Tipe</th>
                        <th style="min-width:150px">Pengirim</th>
                        <th style="min-width:150px">Penerima</th>
                        <th style="min-width:105px">Tgl Kirim</th>
                        <th style="min-width:105px">Tgl Terima</th>
                        <th class="text-center" style="width:60px">Item</th>
                        <th style="width:110px">Status</th>
                        <th style="width:80px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mutations as $mut)
                    @php
                        $typeConfig = [
                            'purchase_zhisheng' => ['Pembelian Pusat',          'bg-info text-dark'],
                            'purchase_supplier' => ['Pembelian Supplier Lokal', 'bg-info text-dark'],
                            'sale_internal'     => ['Pembelian Internal',       'bg-primary'],
                            'sale_external'     => ['Pembelian Eksternal',      'bg-primary'],
                            'opening_stock'     => ['Input Stok Awal',          'bg-secondary'],
                        ];
                        $tc = $typeConfig[$mut->type] ?? [$mut->type, 'bg-secondary'];

                        // Pengirim: supplier (untuk pembelian) atau source store (untuk transfer/sale)
                        if ($mut->isPurchase()) {
                            $pengirim = $mut->supplier->name ?? '<span class="text-muted">—</span>';
                        } else {
                            $pengirim = $mut->sourceStore->name ?? '<span class="text-muted">—</span>';
                        }

                        // Penerima: destination store
                        $penerima = $mut->destinationStore->name ?? '<span class="text-muted">—</span>';
                    @endphp
                    <tr>
                        {{-- No SJ --}}
                        <td>
                            @if($mut->invoice_no)
                                <span class="fw-semibold">{{ $mut->invoice_no }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        {{-- Tipe --}}
                        <td><span class="badge {{ $tc[1] }}" style="font-size:.72rem">{{ $tc[0] }}</span></td>
                        {{-- Pengirim --}}
                        <td>{!! $pengirim !!}</td>
                        {{-- Penerima --}}
                        <td>{!! $penerima !!}</td>
                        {{-- Tgl Transaksi / Tgl Stok (untuk opening_stock) --}}
                        <td class="text-nowrap">
                            {{ $mut->transaction_date ? $mut->transaction_date->format('d M Y') : '—' }}
                            @if($mut->type === 'opening_stock')
                                <div class="small text-muted" style="font-size:.65rem">Tgl Stok</div>
                            @endif
                        </td>
                        {{-- Tgl Terima (tidak relevan untuk opening_stock) --}}
                        <td class="text-nowrap">
                            @if($mut->type === 'opening_stock')
                                <span class="text-muted">—</span>
                            @elseif($mut->delivery_date)
                                <span class="text-success">{{ $mut->delivery_date->format('d M Y') }}</span>
                            @else
                                <span class="text-muted">Belum diterima</span>
                            @endif
                        </td>
                        {{-- Item count --}}
                        <td class="text-center">
                            <span class="badge bg-light text-dark border">{{ $mut->items_count }}</span>
                        </td>
                        {{-- Status --}}
                        <td>
                            @if($mut->status === 'draft')
                                @if(!$mut->delivery_date && $mut->type !== 'opening_stock')
                                    <span class="badge bg-info text-dark"><i class="bi bi-truck me-1"></i>Dalam Perjalanan</span>
                                @else
                                    <span class="badge bg-warning text-dark">Draft</span>
                                @endif
                            @elseif($mut->status === 'confirmed')
                                <span class="badge bg-success">Confirmed</span>
                            @else
                                <span class="badge bg-secondary">Cancelled</span>
                            @endif
                        </td>
                        {{-- Aksi --}}
                        <td class="d-flex gap-1">
                            <a href="{{ route('inventory.mutations.show', $mut) }}"
                               class="btn btn-sm btn-outline-primary" title="Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            <form method="POST" action="{{ route('inventory.mutations.destroy', $mut) }}"
                                  onsubmit="return confirm('Hapus mutasi {{ $mut->reference_no }} secara permanen?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-arrow-left-right fs-1 d-block mb-2 opacity-25"></i>
                            Tidak ada data mutasi
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($mutations->hasPages())
    <div class="card-footer">{{ $mutations->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
