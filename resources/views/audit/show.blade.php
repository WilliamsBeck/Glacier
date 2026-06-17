@extends('layouts.app')
@section('title', 'Detail Log Aktivitas #' . $log->id)

@section('content')
<div class="container-fluid py-4">
  <div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('audit.index') }}" class="btn btn-sm btn-outline-secondary btn-back">
      <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
    <div>
      <h4 class="mb-0 fw-semibold">Detail Log Aktivitas #{{ $log->id }}</h4>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-md-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white border-0 py-3 fw-semibold">Informasi</div>
        <div class="card-body">
          <dl class="row mb-0 small">
            <dt class="col-5 text-muted">Waktu</dt>
            <dd class="col-7">{{ $log->created_at->format('d/m/Y H:i:s') }}</dd>
            <dt class="col-5 text-muted">Aksi</dt>
            <dd class="col-7"><span class="badge bg-primary-subtle text-primary">
  {{ match($log->action) {
    'created'   => 'Dibuat',
    'updated'   => 'Diubah',
    'deleted'   => 'Dihapus',
    'confirmed' => 'Dikonfirmasi',
    'approved'  => 'Disetujui',
    'rejected'  => 'Ditolak',
    default     => ucfirst($log->action),
  } }}
</span></dd>
            <dt class="col-5 text-muted">Model</dt>
            <dd class="col-7">{{ $log->model }} #{{ $log->model_id }}</dd>
            <dt class="col-5 text-muted">User</dt>
            <dd class="col-7">{{ $log->user_name ?? '—' }}</dd>
            <dt class="col-5 text-muted">IP Address</dt>
            <dd class="col-7">{{ $log->ip_address ?? '-' }}</dd>
            <dt class="col-5 text-muted">Deskripsi</dt>
            <dd class="col-7">{{ $log->description }}</dd>
          </dl>
        </div>
      </div>
    </div>

    @if($log->old_values)
    <div class="col-md-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white border-0 py-3 fw-semibold text-danger">
          <i class="bi bi-dash-circle me-1"></i>Nilai Sebelum
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm small mb-0">
              <tbody>
                @foreach($log->old_values as $key => $value)
                <tr>
                  <td class="text-muted fw-semibold" style="width:40%">{{ $key }}</td>
                  <td>{{ is_array($value) ? json_encode($value) : $value }}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    @endif

    @if($log->new_values)
    <div class="col-md-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white border-0 py-3 fw-semibold text-success">
          <i class="bi bi-plus-circle me-1"></i>Nilai Sesudah
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm small mb-0">
              <tbody>
                @foreach($log->new_values as $key => $value)
                <tr>
                  <td class="text-muted fw-semibold" style="width:40%">{{ $key }}</td>
                  <td>{{ is_array($value) ? json_encode($value) : $value }}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    @endif
  </div>
</div>
@endsection
