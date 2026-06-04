@extends('layouts.app')
@section('title','Detail Waste')
@section('content')
<div class="page-header d-flex justify-content-between align-items-start">
    <div><h4 class="page-title">Detail Waste</h4></div>
    <div class="d-flex gap-2">
        <a href="{{ route('waste.logs.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
        <a href="{{ route('waste.logs.edit', $log) }}" class="btn btn-warning btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
        <form method="POST" action="{{ route('waste.logs.destroy', $log) }}"
              onsubmit="return confirm('Hapus catatan waste ini? Stok akan dikembalikan.')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash3 me-1"></i>Hapus</button>
        </form>
    </div>
</div>

<div class="row g-3">
    {{-- Info ringkas --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header fw-semibold">Info Waste</div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted" style="width:40%">Tanggal</td><td>{{ \Carbon\Carbon::parse($log->waste_date)->format('d M Y') }}</td></tr>
                    <tr><td class="text-muted">Toko</td><td>{{ $log->store->name }}</td></tr>
                    <tr><td class="text-muted">Dicatat oleh</td><td>{{ $log->recordedBy->name }}</td></tr>
                    @if($log->notes)
                    <tr><td class="text-muted">Catatan</td><td>{{ $log->notes }}</td></tr>
                    @endif
                    <tr>
                        <td class="text-muted fw-semibold">Total Kerugian</td>
                        <td class="fw-bold text-danger fs-6">Rp {{ number_format($log->total_loss_amount, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Detail bahan --}}
    <div class="col-lg-8">
        @php
            $wasteItems  = $log->items->where('is_rework', false);
            $reworkItems = $log->items->where('is_rework', true);

            // Helper: hitung sisa pcs/gr setelah dikurangi porsi dus & pack
            $directBase = function($item) {
                $pkg = $item->packaging;
                $base = (float)$item->qty_base;
                if ($pkg) {
                    $base -= ($item->qty_crate ?? 0) * $pkg->crate_to_pack * $pkg->pack_to_base;
                    $base -= ($item->qty_pack  ?? 0) * $pkg->pack_to_base;
                    $base  = max(0, $base);
                }
                return $base;
            };
        @endphp

        {{-- Bahan Terbuang --}}
        <div class="card mb-3">
            <div class="card-header fw-semibold d-flex align-items-center gap-2">
                <i class="bi bi-trash3 text-danger"></i> Bahan Terbuang
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size:.875rem">
                        <thead class="table-dark">
                            <tr>
                                <th>Bahan</th>
                                <th class="text-center">Dus</th>
                                <th class="text-center">Pack</th>
                                <th class="text-center">Pcs / Gr</th>
                                <th class="text-end">Kerugian</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($wasteItems as $item)
                            @php $pkg = $item->packaging; $unit = $item->ingredient->unit_base ?? 'pcs'; $db = $directBase($item); @endphp
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $item->ingredient->name }}</div>
                                    @if($pkg)<div class="text-muted" style="font-size:.75rem">{{ $pkg->packaging_name }}</div>@endif
                                </td>
                                <td class="text-center">{{ $item->qty_crate ? number_format($item->qty_crate,0,',','.') : '—' }}</td>
                                <td class="text-center">{{ $item->qty_pack  ? number_format($item->qty_pack, 0,',','.') : '—' }}</td>
                                <td class="text-center">
                                    @if($db > 0.001){{ number_format($db,2,',','.') }} <span class="text-muted" style="font-size:.72rem">{{ $unit }}</span>
                                    @else<span class="text-muted">—</span>@endif
                                </td>
                                <td class="text-end fw-semibold text-danger">Rp {{ number_format($item->subtotal_loss,0,',','.') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-3 small">Tidak ada bahan terbuang</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="table-light fw-bold">
                                <td colspan="4" class="text-end text-muted">Total Kerugian</td>
                                <td class="text-end text-danger">Rp {{ number_format($log->total_loss_amount,0,',','.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{-- Bahan Rusak tapi Masih Bisa Dipakai --}}
        @if($reworkItems->isNotEmpty())
        <div class="card">
            <div class="card-header fw-semibold d-flex align-items-center gap-2"
                 style="background:#fff8e1;border-color:#ffe082">
                <i class="bi bi-arrow-repeat text-warning"></i> Rusak tapi Masih Bisa Dipakai
                <span class="badge bg-warning text-dark ms-1">Tidak dihitung kerugian</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size:.875rem">
                        <thead style="background:#fff3cd">
                            <tr>
                                <th>Bahan</th>
                                <th class="text-center">Dus</th>
                                <th class="text-center">Pack</th>
                                <th class="text-center">Pcs / Gr</th>
                                <th class="text-center text-muted fst-italic" style="font-size:.8rem">Kerugian</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reworkItems as $item)
                            @php $pkg = $item->packaging; $unit = $item->ingredient->unit_base ?? 'pcs'; $db = $directBase($item); @endphp
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $item->ingredient->name }}</div>
                                    @if($pkg)<div class="text-muted" style="font-size:.75rem">{{ $pkg->packaging_name }}</div>@endif
                                </td>
                                <td class="text-center">{{ $item->qty_crate ? number_format($item->qty_crate,0,',','.') : '—' }}</td>
                                <td class="text-center">{{ $item->qty_pack  ? number_format($item->qty_pack, 0,',','.') : '—' }}</td>
                                <td class="text-center">
                                    @if($db > 0.001){{ number_format($db,2,',','.') }} <span class="text-muted" style="font-size:.72rem">{{ $unit }}</span>
                                    @else<span class="text-muted">—</span>@endif
                                </td>
                                <td class="text-center text-muted fst-italic" style="font-size:.8rem">—</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
