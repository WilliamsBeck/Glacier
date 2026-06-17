@extends('layouts.app')
@section('title', 'Pratinjau Impor ' . $cfg['label'])

@section('content')
@php
    $s = $parsed['summary'];
    $headers = array_map(fn($c) => $c['header'], $cfg['columns']);
@endphp

<div class="page-header d-flex justify-content-between align-items-start">
    <div>
        <h1 class="page-title">Pratinjau Impor — {{ $cfg['label'] }}</h1>
        <p class="page-subtitle">Periksa data di bawah sebelum disimpan.</p>
    </div>
    <a href="{{ route($cfg['route_index']) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>

{{-- Ringkasan --}}
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="stat-card border-secondary"><div class="stat-info">
        <div class="stat-number">{{ $s['total'] }}</div><div class="stat-label">Total Baris</div>
    </div></div></div>
    <div class="col-md-3"><div class="stat-card border-success"><div class="stat-info">
        <div class="stat-number text-success">{{ $s['new'] }}</div><div class="stat-label">Baru</div>
    </div></div></div>
    <div class="col-md-3"><div class="stat-card border-primary"><div class="stat-info">
        <div class="stat-number text-primary">{{ $s['update'] }}</div><div class="stat-label">Update</div>
    </div></div></div>
    <div class="col-md-3"><div class="stat-card border-danger"><div class="stat-info">
        <div class="stat-number text-danger">{{ $s['error'] }}</div><div class="stat-label">Error</div>
    </div></div></div>
</div>

@if($s['error'] > 0)
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle me-1"></i>
        Ada {{ $s['error'] }} baris bermasalah. Perbaiki file lalu unggah ulang — impor hanya bisa
        dilanjutkan jika semua baris valid.
    </div>
@elseif($s['total'] === 0)
    <div class="alert alert-warning"><i class="bi bi-info-circle me-1"></i> Tidak ada data pada file.</div>
@endif

<div class="card">
    <div class="table-responsive">
        <table class="table table-index mb-0 align-middle">
            <thead>
                <tr>
                    <th width="60">Baris</th>
                    <th width="90">Status</th>
                    @foreach($headers as $h)<th>{{ $h }}</th>@endforeach
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($parsed['rows'] as $row)
                <tr class="{{ $row['status'] === 'error' ? 'table-danger' : '' }}">
                    <td class="text-muted">{{ $row['row_num'] }}</td>
                    <td>
                        @if($row['status'] === 'new')
                            <span class="badge bg-success-subtle text-success">Baru</span>
                        @elseif($row['status'] === 'update')
                            <span class="badge bg-primary-subtle text-primary">Update</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger">Error</span>
                        @endif
                    </td>
                    @foreach($headers as $h)
                        <td class="small">{{ $row['display'][$h] ?? '' }}</td>
                    @endforeach
                    <td class="small text-danger">{{ implode(' | ', $row['errors']) }}</td>
                </tr>
                @empty
                <tr><td colspan="{{ count($headers) + 3 }}" class="text-center text-muted py-4">Tidak ada baris.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-3">
    <a href="{{ route($cfg['route_index']) }}" class="btn btn-outline-secondary">Batal</a>
    <form method="POST" action="{{ route('master.import.commit', $entity) }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <button type="submit" class="btn btn-primary" {{ ($s['error'] > 0 || $s['total'] === 0) ? 'disabled' : '' }}>
            <i class="bi bi-check-lg me-1"></i> Konfirmasi & Simpan ({{ $s['new'] + $s['update'] }})
        </button>
    </form>
</div>
@endsection
