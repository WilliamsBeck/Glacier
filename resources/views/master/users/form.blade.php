@extends('layouts.app')
@section('title', isset($user)?'Edit User':'Tambah User')
@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <h4 class="page-title">{{ isset($user)?'Edit User: '.$user->name:'Tambah User Baru' }}</h4>
    <a href="{{ route('master.users.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card"><div class="card-header fw-semibold">Data User</div><div class="card-body">
            <form method="POST" action="{{ isset($user) ? route('master.users.update', $user) : route('master.users.store') }}">
                @csrf @if(isset($user)) @method('PUT') @endif
                <div class="mb-3"><label class="form-label fw-semibold">Nama Lengkap *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name ?? '') }}" required>
                </div>
                <div class="mb-3"><label class="form-label fw-semibold">Email *</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email ?? '') }}" required>
                </div>
                <div class="mb-3"><label class="form-label fw-semibold">Role *</label>
                    <select name="role" class="form-select" required>
                        <option value="">— Pilih Role —</option>
                        <option value="super_admin" {{ old('role', $user->role ?? '')==='super_admin'?'selected':'' }}>Super Admin (akses semua toko)</option>
                        <option value="admin_area"  {{ old('role', $user->role ?? '')==='admin_area'?'selected':'' }}>Admin Area (akses toko tertentu)</option>
                    </select>
                </div>
                <div class="mb-3"><label class="form-label fw-semibold">Password {{ isset($user)?'(kosongkan jika tidak diubah)':'*' }}</label>
                    <input type="password" name="password" class="form-control" {{ isset($user)?'':'required' }} minlength="8">
                </div>
                <div class="mb-4"><label class="form-label fw-semibold">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" class="form-control" minlength="8">
                </div>
                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i>Simpan</button>
            </form>
        </div></div>
    </div>

    @if(isset($user) && $user->role === 'admin_area')
    <div class="col-lg-6">
        <div class="card"><div class="card-header fw-semibold">Akses Toko</div><div class="card-body">
            <form method="POST" action="{{ route('master.users.assign-store', $user) }}" class="mb-3">
                @csrf
                <div class="input-group input-group-sm">
                    <select name="store_id" class="form-select" required>
                        <option value="">— Tambah Akses Toko —</option>
                        @foreach($stores as $s)
                            @if(!in_array($s->id, $assignedStores))
                                <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->store_code }})</option>
                            @endif
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary">Tambah</button>
                </div>
            </form>
            <hr>
            <h6 class="fw-semibold mb-2">Toko yang Dapat Diakses</h6>
            @forelse($user->stores as $s)
            <div class="d-flex align-items-center px-2 py-1 border-bottom">
                <i class="bi bi-shop text-muted me-2"></i>
                <div class="flex-grow-1">
                    <div class="fw-semibold small">{{ $s->name }}</div>
                    <div class="text-muted" style="font-size:11px">{{ $s->store_code }} · {{ $s->area }}</div>
                </div>
                <form method="POST" action="{{ route('master.users.revoke-store', [$user, $s]) }}" class="d-inline" onsubmit="return confirm('Cabut akses toko ini?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg"></i></button>
                </form>
            </div>
            @empty
            <div class="text-muted small">Belum ada toko yang di-assign</div>
            @endforelse
        </div></div>
    </div>
    @endif
</div>
@endsection
