@extends('layouts.app')
@section('title','Detail Produksi')
@section('content')
<div class="page-header d-flex justify-content-between align-items-start">
    <div><h4 class="page-title">Detail Produksi</h4></div>
    <div class="d-flex gap-2">
        <a href="{{ route('production.logs.index') }}" class="btn btn-outline-secondary btn-sm btn-back"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
        <a href="{{ route('production.logs.edit', $log) }}" class="btn btn-warning btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
        <form method="POST" action="{{ route('production.logs.destroy', $log) }}"
              data-confirm="Hapus data produksi ini?" data-confirm-type="error" data-confirm-danger="1" data-confirm-ok="Ya, hapus">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash3 me-1"></i>Hapus</button>
        </form>
    </div>
</div>
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card"><div class="card-header fw-semibold">Info Produksi</div><div class="card-body">
            @php
                $sfUnit      = $log->semiFinished->unit_base ?? '';
                $sfIsGram    = strtolower($sfUnit) === 'gram';
                $totalCostSf = $log->items->sum(fn($i) => $i->qty_consumed * $i->price_per_base);
                $qtyProduced = (float) $log->qty_produced;
                $hppPerUnit  = $qtyProduced > 0 ? $totalCostSf / $qtyProduced : 0;
            @endphp
            <table class="table table-sm table-borderless mb-0">
                <tr><td class="text-muted">Tanggal</td><td>{{ \Carbon\Carbon::parse($log->production_date)->format('d M Y') }}</td></tr>
                <tr><td class="text-muted">Toko</td><td>{{ $log->store->name }}</td></tr>
                <tr><td class="text-muted">Bahan Diproduksi</td><td class="fw-semibold">{{ $log->semiFinished->name }}</td></tr>
                <tr>
                    <td class="text-muted">Qty</td>
                    <td class="fw-bold">
                        {{ number_format($qtyProduced, $sfIsGram ? 0 : 2, ',', '.') }} {{ $sfUnit }}
                    </td>
                </tr>
                <tr><td class="text-muted">Dibuat oleh</td><td>{{ $log->createdBy->name }}</td></tr>
                @if($log->notes)<tr><td class="text-muted">Catatan</td><td>{{ $log->notes }}</td></tr>@endif
            </table>
            @if($totalCostSf > 0)
            <hr class="my-2">
            <table class="table table-sm table-borderless mb-0">
                <tr>
                    <td class="text-muted">Total Biaya Bahan</td>
                    <td class="fw-bold text-primary">Rp {{ number_format($totalCostSf, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="text-muted">HPP per {{ $sfUnit }}</td>
                    <td class="fw-bold text-success">
                        Rp {{ number_format($hppPerUnit, 2, ',', '.') }}
                        <span class="text-muted fw-normal small">/ {{ $sfUnit }}</span>
                    </td>
                </tr>
            </table>
            @endif
        </div></div>
    </div>
    <div class="col-lg-8">
        <div class="card"><div class="card-header fw-semibold">Bahan Baku yang Digunakan</div>
            <div class="card-body p-0"><div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Bahan Baku</th>
                            <th class="text-end">Qty Digunakan</th>
                            <th class="text-end">Harga FIFO</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalCost = 0; @endphp
                        @forelse($log->items as $item)
                        @php
                            $rawUnit  = $item->rawIngredient->unit_base ?? '';
                            $isGram   = strtolower($rawUnit) === 'gram';
                            $subtotal = $item->qty_consumed * $item->price_per_base;
                            $totalCost += $subtotal;
                        @endphp
                        <tr>
                            <td class="fw-semibold">{{ $item->rawIngredient->name }}</td>
                            <td class="text-end">
                                {{ number_format($item->qty_consumed, $isGram ? 0 : 2, ',', '.') }}
                                <span class="text-muted small">{{ $rawUnit }}</span>
                            </td>
                            <td class="text-end text-muted">
                                Rp {{ number_format($item->price_per_base, 2, ',', '.') }}
                            </td>
                            <td class="text-end fw-semibold">
                                Rp {{ number_format($subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-3 small">Tidak ada data komposisi</td></tr>
                        @endforelse
                    </tbody>
                    @if($log->items->isNotEmpty())
                    <tfoot>
                        <tr class="table-light fw-bold">
                            <td colspan="3" class="text-end">Total Estimasi Biaya Bahan</td>
                            <td class="text-end text-primary">Rp {{ number_format($totalCost, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div></div>
        </div>
    </div>
</div>
@endsection
