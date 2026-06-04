@extends('layouts.app')
@section('title','Resep')
@section('content')
<div class="page-header d-flex justify-content-between align-items-start">
    <div><h4 class="page-title">Resep Menu</h4><p class="text-muted mb-0">Versi resep aktif menentukan HPP per periode</p></div>
    <a href="{{ route('master.recipes.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> Buat Resep Baru</a>
</div>
<div class="card mb-3"><div class="card-body py-2">
    <form method="GET" class="row g-2">
        <div class="col-md-3"><select name="menu_id" class="form-select form-select-sm"><option value="">Semua Menu</option>@foreach($menus as $m)<option value="{{ $m->id }}" {{ request('menu_id')==$m->id?'selected':'' }}>{{ $m->name }}</option>@endforeach</select></div>
        <div class="col-md-3"><select name="store_id" class="form-select form-select-sm">
            <option value="">Semua Toko</option>
            <option value="default" {{ request('store_id')==='default' ? 'selected' : '' }}>— Default (semua toko) —</option>
            @foreach($stores as $s)<option value="{{ $s->id }}" {{ request('store_id')==$s->id?'selected':'' }}>{{ $s->name }}</option>@endforeach
        </select></div>
        <div class="col-md-2"><button type="submit" class="btn btn-primary btn-sm">Filter</button></div>
    </form>
</div></div>

@php
    // Group resep per versi (menu + effective_from), masing2 punya 1 tombol Duplikat
    $grouped = $recipes->groupBy('recipe_group_id');
@endphp

<div class="card"><div class="card-body p-0"><div class="table-responsive">
    <table class="table table-hover mb-0">
        <thead class="table-dark">
            <tr>
                <th>Menu</th>
                <th>Berlaku Untuk</th>
                <th>Berlaku Sejak</th>
                <th>Komposisi Bahan</th>
                <th>Dibuat Oleh</th>
                <th style="width:120px"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($grouped as $key => $group)
            @php $first = $group->first(); @endphp
            <tr>
                <td class="fw-semibold align-top">{{ $first->menu->name }}</td>
                <td class="align-top">
                    @php
                        $isDefault   = $group->pluck('store_id')->contains(null);
                        $storeNames  = $group->pluck('store.name')->filter()->unique()->values();
                    @endphp
                    @if($isDefault)
                        <span class="badge bg-success-subtle text-success-emphasis">Default (semua toko)</span>
                    @else
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($storeNames as $sn)
                                <span class="badge bg-info-subtle text-info-emphasis"><i class="bi bi-shop me-1"></i>{{ $sn }}</span>
                            @endforeach
                        </div>
                    @endif
                </td>
                <td class="align-top">
                    <span class="badge bg-secondary">{{ $first->effective_from->format('d') . ' ' . ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agt','Sep','Okt','Nov','Des'][(int)$first->effective_from->format('n')] . ' ' . $first->effective_from->format('Y') }}</span>
                </td>
                <td>
                    @foreach($group->unique('ingredient_id') as $r)
                    <div class="small">
                        {{ $r->ingredient->name }}
                        <span class="text-muted">— {{ number_format($r->qty_usage, 0, ',', '.') }} {{ $r->unit }}</span>
                    </div>
                    @endforeach
                </td>
                <td class="align-top small text-muted">{{ $first->createdBy->name ?? '-' }}</td>
                <td class="align-top">
                    <a href="{{ route('master.recipes.duplicate', $first->id) }}"
                       class="btn btn-sm btn-outline-primary"
                       title="Duplikat versi ini sebagai resep baru">
                        <i class="bi bi-copy me-1"></i>Duplikat
                    </a>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center py-4 text-muted">Belum ada resep</td></tr>
            @endforelse
        </tbody>
    </table>
</div></div>
@if($recipes->hasPages())<div class="card-footer">{{ $recipes->withQueryString()->links() }}</div>@endif
</div>
@endsection
