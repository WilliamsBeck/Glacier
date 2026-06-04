@extends('layouts.app')
@section('title','User Management')
@section('content')
<div class="page-header d-flex justify-content-between align-items-start">
    <div><h4 class="page-title">Manajemen User</h4></div>
    <a href="{{ route('master.users.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> Tambah User</a>
</div>
<div class="card mb-3"><div class="card-body py-2">
    <form method="GET" class="row g-2">
        <div class="col-md-4"><input type="text" name="search" class="form-control form-control-sm" placeholder="Cari nama / email..." value="{{ request('search') }}"></div>
        <div class="col-md-3"><select name="role" class="form-select form-select-sm"><option value="">Semua Role</option><option value="super_admin" {{ request('role')==='super_admin'?'selected':'' }}>Super Admin</option><option value="admin_area" {{ request('role')==='admin_area'?'selected':'' }}>Admin Area</option></select></div>
        <div class="col-md-2"><button type="submit" class="btn btn-primary btn-sm">Cari</button></div>
    </form>
</div></div>
<div class="card"><div class="card-body p-0"><div class="table-responsive">
    <table class="table table-hover mb-0">
        <thead class="table-dark"><tr><th>#</th><th>Nama</th><th>Email</th><th>Role</th><th>Toko Akses</th><th>Aksi</th></tr></thead>
        <tbody>
            @forelse($users as $user)
            <tr>
                <td class="text-muted small">{{ $users->firstItem() + $loop->index }}</td>
                <td class="fw-semibold">{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>@if($user->isSuperAdmin())<span class="badge bg-danger">Super Admin</span>@else<span class="badge bg-primary">Admin Area</span>@endif</td>
                <td>@if($user->isSuperAdmin())<span class="text-muted small">Semua Toko</span>@else<span class="badge bg-info">{{ $user->stores->count() }} toko</span>@endif</td>
                <td>
                    <a href="{{ route('master.users.edit', $user) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                    @if($user->id !== auth()->id())
                    <form method="POST" action="{{ route('master.users.destroy', $user) }}" class="d-inline" onsubmit="return confirm('Hapus user ini?')">
                        @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada user</td></tr>
            @endforelse
        </tbody>
    </table>
</div></div>
@if($users->hasPages())<div class="card-footer">{{ $users->withQueryString()->links() }}</div>@endif
</div>
@endsection
