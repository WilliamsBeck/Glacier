@extends('layouts.app')
@section('title', 'User Management')

@section('content')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
        .web3-form-container {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        /* KARTU KONTEN ELEGAN */
        .web3-form-card {
            background-color: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.01);
            border: 1px solid #e2e8f0;
            padding: 24px;
        }

        .web3-card-p0 {
            padding: 0;
            overflow: hidden;
        }

        /* INPUT & SELECT PREMIUM */
        .premium-input {
            border-radius: 12px !important;
            background-color: #f8fafc !important;
            border: 1px solid #cbd5e1 !important;
            font-size: 0.95rem !important;
            padding: 10px 16px !important;
            color: #0f172a !important;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .premium-input:focus {
            background-color: #ffffff !important;
            border-color: #0f172a !important;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05) !important;
            outline: none;
        }

        .premium-select {
            border-radius: 12px !important;
            background-color: #f8fafc !important;
            border: 1px solid #cbd5e1 !important;
            font-size: 0.95rem !important;
            padding: 10px 16px !important;
            color: #0f172a !important;
            font-weight: 500;
            cursor: pointer;
        }

        /* BUTTONS */
        .btn-premium-dark {
            background-color: #0f172a;
            color: #ffffff;
            border-radius: 12px;
            font-weight: 600;
            padding: 10px 20px;
            transition: all 0.2s ease;
            border: 1px solid #0f172a;
        }

        .btn-premium-dark:hover {
            background-color: #1e293b;
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15);
        }

        .btn-action-edit {
            background-color: #f1f5f9;
            color: #3b82f6;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 6px 12px;
            transition: all 0.2s;
        }

        .btn-action-edit:hover {
            background-color: #e0f2fe;
            color: #0284c7;
            border-color: #bae6fd;
        }

        .btn-action-delete {
            background-color: #fff5f5;
            color: #ef4444;
            border: 1px solid #fee2e2;
            border-radius: 8px;
            padding: 6px 12px;
            transition: all 0.2s;
        }

        .btn-action-delete:hover {
            background-color: #fef2f2;
            color: #b91c1c;
            border-color: #fecaca;
        }

        /* BADGES */
        .badge-super-admin {
            background-color: #fef2f2;
            color: #ef4444;
            border: 1px solid #fecaca;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 8px;
        }

        .badge-admin-area {
            background-color: #eff6ff;
            color: #3b82f6;
            border: 1px solid #bfdbfe;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 8px;
        }

        .badge-toko {
            background-color: #f0fdf4;
            color: #10b981;
            border: 1px solid #bbf7d0;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 8px;
        }

        /* TABLE */
        .premium-table th {
            background-color: #f8fafc;
            color: #64748b;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 16px;
            border-bottom: 2px solid #e2e8f0;
        }

        .premium-table td {
            padding: 16px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.95rem;
        }
    </style>

    <div class="web3-form-container pb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-700 mb-1" style="font-weight: 700; color: #0f172a; letter-spacing: -0.5px;">
                    Manajemen User
                </h3>
                <p class="text-muted small mb-0" style="font-size: 0.88rem;">Kelola hak akses dan data pengguna sistem</p>
            </div>
            <a href="{{ route('master.users.create') }}" class="btn btn-premium-dark d-flex align-items-center">
                <i class="bi bi-plus-circle me-2"></i> Tambah User Baru
            </a>
        </div>

        <div class="web3-form-card mb-4" style="padding: 16px 24px;">
            <form method="GET" class="row g-3 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-0 position-absolute text-muted"
                            style="z-index: 10; padding-left: 16px; top: 8px;">
                            <!-- <i class="bi bi-search"></i> -->
                        </span>
                        <input type="text" name="search" class="form-control premium-input ps-2"
                            placeholder="🔍Cari berdasarkan nama / email..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="role" class="form-select premium-select">
                        <option value="">Semua Role</option>
                        <option value="super_admin" {{ request('role') === 'super_admin' ? 'selected' : '' }}>Super Admin
                        </option>
                        <option value="admin_area" {{ request('role') === 'admin_area' ? 'selected' : '' }}>Admin Area
                        </option>
                    </select>
                </div>
                <div class="col-md-3 text-end">
                    <button type="submit" class="btn btn-premium-dark w-100">
                        Filter Data
                    </button>
                </div>
            </form>
        </div>

        <div class="web3-form-card web3-card-p0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 premium-table table-borderless">
                    <thead>
                        <tr>
                            <th style="width: 60px;" class="text-center">#</th>
                            <th>Informasi User</th>
                            <th>Email Aktif</th>
                            <th>Role Sistem</th>
                            <th>Toko Akses</th>
                            <th class="text-end" style="padding-right: 24px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td class="text-muted small text-center fw-medium">{{ $users->firstItem() + $loop->index }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center text-primary fw-bold me-3"
                                            style="width: 40px; height: 40px; font-size: 1.1rem;">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <span class="fw-semibold text-dark">{{ $user->name }}</span>
                                    </div>
                                </td>
                                <td class="text-muted">{{ $user->email }}</td>
                                <td>
                                    @if($user->isSuperAdmin())
                                        <span class="badge-super-admin">
                                            <i class="bi bi-shield-lock-fill me-1"></i> Super Admin
                                        </span>
                                    @else
                                        <span class="badge-admin-area">
                                            <i class="bi bi-person-badge-fill me-1"></i> Admin Area
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->isSuperAdmin())
                                        <span class="text-muted small fw-medium">
                                            <i class="bi bi-infinity me-1"></i> Semua Toko
                                        </span>
                                    @else
                                        <span class="badge-toko">
                                            <i class="bi bi-shop me-1"></i> {{ $user->stores->count() }} Toko
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end" style="padding-right: 24px;">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('master.users.edit', $user) }}" class="btn btn-action-edit"
                                            title="Edit User">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        @if($user->id !== auth()->id())
                                            <form method="POST" action="{{ route('master.users.destroy', $user) }}" class="d-inline"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini secara permanen?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-action-delete" title="Hapus User">
                                                    <i class="bi bi-trash3-fill"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted d-flex flex-column align-items-center">
                                        <i class="bi bi-people mb-2" style="font-size: 2.5rem; color: #cbd5e1;"></i>
                                        <span class="fw-medium">Belum ada data user yang ditemukan.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="card-footer bg-white border-top-0 px-4 py-3" style="border-top: 1px solid #e2e8f0 !important;">
                    {{ $users->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection