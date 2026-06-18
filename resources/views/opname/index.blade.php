@extends('layouts.app')
@section('title','Stok Opname')
@section('content')
<div class="page-header d-flex justify-content-between align-items-start">
    <div>
        <h1 class="page-title">Stok Opname</h1>
        <p class="page-subtitle">Jadwal: tgl 15 (periode 1–15) dan akhir bulan (periode 1–30/31) tiap toko</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('opname.opnames.import.form') }}" class="btn btn-outline-primary">
            <i class="bi bi-upload me-1"></i>Import Excel
        </a>
        <a href="{{ route('opname.opnames.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Tambah Opname
        </a>
    </div>
</div>
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show">
    <i class="bi bi-exclamation-triangle me-1"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card mb-3"><div class="card-body py-2">
    <form method="GET" class="row g-2 align-items-center">
        <div class="col-md-2"><select name="store_id" class="form-select"><option value="">Semua Toko</option>@foreach($stores as $s)<option value="{{ $s->id }}" {{ request('store_id')==$s->id?'selected':'' }}>{{ $s->name }}</option>@endforeach</select></div>
        <div class="col-md-2"><select name="period_type" class="form-select"><option value="">Semua Periode</option><option value="mid_month" {{ request('period_type')==='mid_month'?'selected':'' }}>Periode 1–15</option><option value="end_month" {{ request('period_type')==='end_month'?'selected':'' }}>Periode 1–30/31</option></select></div>
        <div class="col-md-2"><select name="month" class="form-select"><option value="">Semua Bulan</option>@foreach(['1'=>'Januari','2'=>'Februari','3'=>'Maret','4'=>'April','5'=>'Mei','6'=>'Juni','7'=>'Juli','8'=>'Agustus','9'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'] as $num=>$nama)<option value="{{ $num }}" {{ request('month')==$num?'selected':'' }}>{{ $nama }}</option>@endforeach</select></div>
        <div class="col-md-1"><input type="number" name="year" class="form-control" placeholder="Thn" value="{{ request('year', date('Y')) }}"></div>
        <div class="col-md-auto ms-auto d-flex gap-2"><button type="submit" class="btn btn-primary">Cari</button><a href="{{ route('opname.opnames.index') }}" class="btn btn-outline-secondary">Reset</a></div>
    </form>
</div></div>
<div class="card"><div class="card-body p-0"><div class="table-responsive">
    <table class="table table-index table-balanced mb-0">
        <thead><tr><th class="col-name" style="width:16.66%">Toko</th><th style="width:16.66%">Tanggal Opname</th><th style="width:16.66%">Periode</th><th style="width:16.66%">Status</th><th class="col-name" style="width:16.66%">Dilakukan Oleh</th><th class="text-center" style="width:16.7%">Aksi</th></tr></thead>
        <tbody>
            @forelse($opnames as $op)
            <tr>
                <td class="col-name fw-semibold">{{ $op->store->name }}</td>
                <td>{{ \Carbon\Carbon::parse($op->opname_date)->format('d M Y') }}</td>
                <td><span class="badge bg-secondary">{{ $op->period_type === 'mid_month' ? 'Tgl 1–15' : 'Tgl 1–30/31' }} {{ $op->period_month }}/{{ $op->period_year }}</span></td>
                <td>
                    @if($op->status==='draft') <span class="badge bg-warning">Draft</span>
                    @else <span class="badge bg-success">Disetujui</span> @endif
                </td>
                <td class="col-name">{{ $op->performedBy->name }}</td>
                <td class="text-center">
                    <x-action-menu>
                        <x-action-view :href="route('opname.opnames.show', $op)" />
                        @if($op->status !== 'approved')
                            <x-action-delete :action="route('opname.opnames.destroy', $op)"
                                             confirm="Hapus opname ini?" />
                        @endif
                    </x-action-menu>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="py-4 text-soft"><i class="bi bi-clipboard-check fs-2 d-block mb-2 opacity-25"></i>Belum ada opname</td></tr>
            @endforelse
        </tbody>
    </table>
</div></div>
@if($opnames->hasPages())<div class="card-footer">{{ $opnames->withQueryString()->links() }}</div>@endif
</div>
@endsection
