@extends('layouts.app')
@section('title', 'Request Perpanjangan Edit Harian')
@section('content')

<div class="page-header d-flex justify-content-between align-items-start">
    <div>
        <h4 class="page-title">Request Perpanjangan Edit Harian</h4>
        <p class="text-muted small mb-0">Kelola permintaan unlock data harian dari toko</p>
    </div>
    @if($pendingCount > 0)
        <span class="badge bg-danger fs-6">{{ $pendingCount }} Menunggu</span>
    @endif
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Filter status --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    <option value="pending"  {{ request('status')==='pending'  ?'selected':'' }}>Menunggu</option>
                    <option value="approved" {{ request('status')==='approved' ?'selected':'' }}>Disetujui</option>
                    <option value="rejected" {{ request('status')==='rejected' ?'selected':'' }}>Ditolak</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Toko</th>
                        <th>Periode</th>
                        <th>Diminta Oleh</th>
                        <th>Tambahan Hari</th>
                        <th>Alasan</th>
                        <th>Status</th>
                        <th>Berlaku Hingga</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                    @php
                        $monthNames = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
                    @endphp
                    <tr>
                        <td class="fw-semibold">{{ $req->store->name }}</td>
                        <td>{{ $monthNames[$req->request_month] }} {{ $req->request_year }}</td>
                        <td>{{ $req->requestedBy->name }}<br>
                            <small class="text-muted">{{ $req->created_at->isoFormat('D MMM, HH:mm') }}</small>
                        </td>
                        <td class="text-center fw-semibold">+{{ $req->extra_days }} hari</td>
                        <td style="max-width:220px">
                            <span class="small">{{ $req->reason }}</span>
                        </td>
                        <td>
                            @if($req->isPending())
                                <span class="badge bg-warning text-dark">Menunggu</span>
                            @elseif($req->isApproved())
                                <span class="badge bg-success">Disetujui</span>
                                @if($req->admin_notes)
                                    <div class="small text-muted mt-1">{{ $req->admin_notes }}</div>
                                @endif
                            @else
                                <span class="badge bg-danger">Ditolak</span>
                                @if($req->admin_notes)
                                    <div class="small text-muted mt-1">{{ $req->admin_notes }}</div>
                                @endif
                            @endif
                        </td>
                        <td class="small">
                            @if($req->isApproved() && $req->new_lock_until)
                                {{ $req->new_lock_until->isoFormat('D MMMM Y') }}
                                @if($req->new_lock_until->lt(now()))
                                    <span class="badge bg-secondary ms-1">Kadaluarsa</span>
                                @endif
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            @if($req->isPending())
                                {{-- Approve --}}
                                <button class="btn btn-success btn-sm mb-1"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalApprove{{ $req->id }}">
                                    <i class="bi bi-check-lg me-1"></i>Approve
                                </button>
                                {{-- Reject --}}
                                <button class="btn btn-danger btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalReject{{ $req->id }}">
                                    <i class="bi bi-x-lg me-1"></i>Tolak
                                </button>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                    </tr>

                    {{-- Modal Approve --}}
                    @if($req->isPending())
                    <div class="modal fade" id="modalApprove{{ $req->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <form method="POST" action="{{ route('inventory.daily-edit-requests.approve', $req) }}">
                                @csrf
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Approve Request</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p class="mb-2 small">
                                            <strong>{{ $req->store->name }}</strong> —
                                            {{ $monthNames[$req->request_month] }} {{ $req->request_year }} —
                                            <span class="text-success fw-semibold">+{{ $req->extra_days }} hari</span>
                                        </p>
                                        <p class="text-muted small mb-3">Alasan: {{ $req->reason }}</p>
                                        @php
                                            $lastEdit = \Carbon\Carbon::create($req->request_year, $req->request_month, 1)->addMonth()->addDays(6);
                                            $newUntil = $lastEdit->copy()->addDays($req->extra_days);
                                        @endphp
                                        <div class="alert alert-success py-2 small">
                                            Data akan bisa diedit hingga <strong>{{ $newUntil->isoFormat('D MMMM Y') }}</strong>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Catatan (opsional)</label>
                                            <textarea name="admin_notes" class="form-control form-control-sm" rows="2" placeholder="Catatan untuk pemohon..."></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="bi bi-check-lg me-1"></i>Approve
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Modal Reject --}}
                    <div class="modal fade" id="modalReject{{ $req->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <form method="POST" action="{{ route('inventory.daily-edit-requests.reject', $req) }}">
                                @csrf
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Tolak Request</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p class="mb-2 small">
                                            <strong>{{ $req->store->name }}</strong> —
                                            {{ $monthNames[$req->request_month] }} {{ $req->request_year }}
                                        </p>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Alasan penolakan <span class="text-danger">*</span></label>
                                            <textarea name="admin_notes" class="form-control form-control-sm" rows="2"
                                                placeholder="Jelaskan alasan penolakan..." required></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="bi bi-x-lg me-1"></i>Tolak Request
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endif

                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2 opacity-25"></i>
                            Tidak ada request.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($requests->hasPages())
        <div class="card-footer">{{ $requests->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
