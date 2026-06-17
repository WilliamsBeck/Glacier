@extends('layouts.app')
@section('title', 'Log Aktivitas')

@section('content')
<div class="container-fluid py-4">

  <div class="page-header">
    <h1 class="page-title">Log Aktivitas</h1>
    <p class="page-subtitle">Semua perubahan data tercatat di sini</p>
  </div>

  {{-- Filter --}}
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
      <form method="GET" class="row g-2 align-items-end">
        <div class="col-6 col-md-2">
          <label class="form-label small fw-semibold">Aksi</label>
          <select name="action" class="form-select form-select-sm">
            <option value="">Semua</option>
            @foreach($actions as $a)
              <option value="{{ $a }}" {{ request('action')==$a ? 'selected':'' }}>{{ ucfirst($a) }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-6 col-md-2">
          <label class="form-label small fw-semibold">Model</label>
          <select name="model" class="form-select form-select-sm">
            <option value="">Semua</option>
            @foreach($models as $m)
              <option value="{{ $m }}" {{ request('model')==$m ? 'selected':'' }}>{{ $m }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-6 col-md-2">
          <label class="form-label small fw-semibold">User</label>
          <input type="text" name="user" class="form-control form-control-sm"
                 value="{{ request('user') }}" placeholder="Nama user...">
        </div>
        <div class="col-6 col-md-2">
          <label class="form-label small fw-semibold">Dari Tanggal</label>
          <input type="date" name="date_from" class="form-control form-control-sm"
                 value="{{ request('date_from') }}">
        </div>
        <div class="col-6 col-md-2">
          <label class="form-label small fw-semibold">Sampai</label>
          <input type="date" name="date_to" class="form-control form-control-sm"
                 value="{{ request('date_to') }}">
        </div>
        <div class="col-6 col-md-2">
          <label class="form-label small fw-semibold">Pencarian</label>
          <input type="text" name="search" class="form-control form-control-sm"
                 value="{{ request('search') }}" placeholder="Deskripsi...">
        </div>
        <div class="col-12 col-md-1">
          <button type="submit" class="btn btn-primary btn-sm w-100">Cari</button>
        </div>
        <div class="col-12 col-md-1">
          <a href="{{ route('audit.index') }}" class="btn btn-outline-secondary btn-sm w-100">Reset</a>
        </div>
      </form>
    </div>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
      <span class="fw-semibold">{{ number_format($logs->total(), 0, ',', '.') }} entri log</span>
      <span class="text-muted small">Halaman {{ $logs->currentPage() }} dari {{ $logs->lastPage() }}</span>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-index align-middle small mb-0">
          <thead>
            <tr>
              <th class="col-name">Waktu</th>
              <th>Aksi</th>
              <th>Model</th>
              <th class="col-name">Deskripsi</th>
              <th>User</th>
              <th>IP</th>
              <th style="width:70px">Detail</th>
            </tr>
          </thead>
          <tbody>
            @forelse($logs as $log)
            <tr>
              <td class="col-name text-muted" style="white-space:nowrap">
                {{ $log->created_at->format('d/m/Y H:i') }}
                <div class="text-muted" style="font-size:.7rem">{{ $log->created_at->diffForHumans() }}</div>
              </td>
              <td>
                @php
                  $badge = match($log->action) {
                    'created'   => 'bg-success-subtle text-success',
                    'updated'   => 'bg-primary-subtle text-primary',
                    'deleted'   => 'bg-danger-subtle text-danger',
                    'confirmed','approved' => 'bg-info-subtle text-info',
                    'rejected'  => 'bg-warning-subtle text-warning',
                    default     => 'bg-secondary-subtle text-secondary',
                  };
                @endphp
                @php
                  $actionLabel = match($log->action) {
                    'created'   => 'Dibuat',
                    'updated'   => 'Diubah',
                    'deleted'   => 'Dihapus',
                    'confirmed' => 'Dikonfirmasi',
                    'approved'  => 'Disetujui',
                    'rejected'  => 'Ditolak',
                    default     => ucfirst($log->action),
                  };
                @endphp
                <span class="badge {{ $badge }}">{{ $actionLabel }}</span>
              </td>
              <td>
                <span class="badge bg-secondary-subtle text-secondary">{{ $log->model }}</span>
                @if($log->model_id)
                  <span class="text-muted" style="font-size:.75rem">#{{ $log->model_id }}</span>
                @endif
              </td>
              <td class="col-name">{{ $log->description }}</td>
              <td>
                <div class="fw-semibold">{{ $log->user_name ?? '—' }}</div>
              </td>
              <td class="text-muted" style="font-size:.75rem">{{ $log->ip_address ?? '-' }}</td>
              <td>
                @if($log->old_values || $log->new_values)
                <x-action-menu>
                    <x-action-view :href="route('audit.show', $log)" />
                </x-action-menu>
                @endif
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center text-muted py-4">Belum ada log yang sesuai filter.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    @if($logs->hasPages())
    <div class="card-footer bg-white border-0 py-3">
      {{ $logs->links() }}
    </div>
    @endif
  </div>
</div>
@endsection
