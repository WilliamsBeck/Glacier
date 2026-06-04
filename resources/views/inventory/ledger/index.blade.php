@extends('layouts.app')
@section('title','Ledger Harian')
@section('content')
<div class="page-header"><h4 class="page-title">Ledger Pergerakan Stok Harian</h4></div>
<div class="card mb-3"><div class="card-body py-2">
    <form method="GET" class="row g-2">
        <div class="col-md-2"><select name="store_id" class="form-select form-select-sm"><option value="">Semua Toko</option>@foreach($stores as $s)<option value="{{ $s->id }}" {{ request('store_id')==$s->id?'selected':'' }}>{{ $s->name }}</option>@endforeach</select></div>
        <div class="col-md-2"><select name="ingredient_id" class="form-select form-select-sm"><option value="">Semua Bahan</option>@foreach($ingredients as $i)<option value="{{ $i->id }}" {{ request('ingredient_id')==$i->id?'selected':'' }}>{{ $i->name }}</option>@endforeach</select></div>
        <div class="col-md-2"><select name="movement_type" class="form-select form-select-sm"><option value="">Semua Tipe</option><option value="purchase_in">Purchase In</option><option value="transfer_in">Transfer In</option><option value="transfer_out">Transfer Out</option><option value="production_in">Production In</option><option value="production_out">Production Out</option><option value="waste">Waste</option><option value="opname_adjustment">Opname Adj.</option></select></div>
        <div class="col-md-2"><input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}"></div>
        <div class="col-md-2"><input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}"></div>
        <div class="col-md-2 d-flex gap-1"><button type="submit" class="btn btn-primary btn-sm flex-grow-1">Cari</button><a href="{{ route('inventory.ledger.index') }}" class="btn btn-outline-secondary btn-sm">×</a></div>
    </form>
</div></div>
<div class="card"><div class="card-body p-0"><div class="table-responsive">
    <table class="table table-hover mb-0">
        <thead class="table-dark"><tr><th>Tanggal</th><th>Toko</th><th>Bahan</th><th>Tipe Mutasi</th><th class="text-end">Perubahan</th><th class="text-end">Saldo Setelah</th><th>Referensi</th></tr></thead>
        <tbody>
            @forelse($ledgers as $l)
            <tr>
                <td>{{ \Carbon\Carbon::parse($l->movement_date)->format('d M Y') }}</td>
                <td>{{ $l->store->name }}</td>
                <td class="fw-semibold">{{ $l->ingredient->name }}</td>
                <td><span class="badge {{ $l->qty_change > 0 ? 'bg-success' : 'bg-danger' }}">{{ $l->movement_type }}</span></td>
                <td class="text-end fw-bold {{ $l->qty_change > 0 ? 'text-success' : 'text-danger' }}">
                    {{ $l->qty_change > 0 ? '+' : '' }}{{ number_format($l->qty_change, 2, ',', '.') }}
                </td>
                <td class="text-end">{{ number_format($l->balance_after, 2, ',', '.') }}</td>
                <td class="font-monospace text-muted" style="font-size:11px">{{ $l->reference_type }}#{{ $l->reference_id }}</td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center py-4 text-muted">Tidak ada data ledger</td></tr>
            @endforelse
        </tbody>
    </table>
</div></div>
@if($ledgers->hasPages())<div class="card-footer">{{ $ledgers->withQueryString()->links() }}</div>@endif
</div>
@endsection
