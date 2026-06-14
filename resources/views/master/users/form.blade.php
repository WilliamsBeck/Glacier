@extends('layouts.app')
@section('title', isset($user) ? 'Edit User' : 'Tambah User')

@section('content')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    .web3-container {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background-color: #f8fafc; /* Latar belakang halaman super light */
        min-height: 100vh;
    }

    /* KARTU KONTEN ELEGAN & MEWAH */
    .web3-form-card {
        background-color: #ffffff;
        border-radius: 24px; /* Sudut lebih melengkung agar modern */
        box-shadow: 0 15px 35px rgba(0,0,0,0.02), 0 5px 15px rgba(0,0,0,0.01); /* Shadow berlapis agar halus */
        border: 1px solid #e2e8f0;
        overflow: hidden; /* Agar gradien header tidak keluar */
    }

    /* HEADER KARTU DENGAN GRADIEN SOFT */
    .card-header-premium {
        background: linear-gradient(135deg, #f0f9ff 0%, #ffffff 100%);
        padding: 24px 30px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .section-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        display: flex;
        align-items: center;
    }

    /* AREA KONTEN FORM */
    .card-body-premium {
        padding: 30px;
    }

    /* LABEL DAN INPUT MAIN FORM */
    .premium-label {
        font-weight: 600;
        color: #1e293b; /* Lebih gelap sedikit untuk keterbacaan */
        font-size: 0.92rem;
        margin-bottom: 10px;
        display: block;
    }
    .premium-input, .premium-select {
        border-radius: 14px !important; /* Sudut input melengkung modern */
        background-color: #fcfdfe !important; /* Latar input hampir putih */
        border: 1px solid #cbd5e1 !important; 
        font-size: 0.98rem !important; /* Sedikit lebih besar agar enak dibaca */
        padding: 14px 18px !important; /* Padding ekstra untuk kenyamanan */
        color: #0f172a !important;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    .premium-input:focus, .premium-select:focus {
        background-color: #ffffff !important;
        border-color: #0f172a !important; /* Border hitam saat fokus */
        box-shadow: 0 0 0 4px rgba(15, 23, 42, 0.08) !important; /* Shadow fokus halus */
        outline: none;
    }

    /* BUTTONS */
    .btn-premium-dark {
        background-color: #0f172a;
        color: #ffffff;
        border-radius: 50px; /* Pill style button */
        font-weight: 700;
        padding: 14px 30px;
        transition: all 0.2s ease;
        border: none;
        font-size: 1rem;
        letter-spacing: 0.3px;
    }
    .btn-premium-dark:hover {
        background-color: #1e293b;
        color: #ffffff;
        transform: translateY(-2px); /* Efek melayang saat hover */
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.15);
    }

    .btn-premium-outline {
        background-color: #ffffff;
        color: #475569;
        border: 1px solid #cbd5e1;
        border-radius: 50px;
        font-weight: 600;
        padding: 10px 20px;
        transition: all 0.2s ease;
        font-size: 0.88rem;
    }
    .btn-premium-outline:hover {
        background-color: #f1f5f9;
        color: #0f172a;
        border-color: #94a3b8;
    }

    .btn-action-delete {
        background-color: #fff5f5;
        color: #ef4444;
        border: 1px solid #fee2e2;
        border-radius: 10px;
        transition: all 0.2s;
    }
    .btn-action-delete:hover {
        background-color: #fef2f2;
        color: #b91c1c;
        border-color: #fecaca;
    }

    /* INFO & LIST TOKO */
    .info-card-premium {
        background-color: #f0fdf4; /* Light green info untuk area ini */
        border: 1px dashed #bbf7d0;
        border-radius: 16px;
        padding: 18px;
        color: #166534;
        font-size: 0.9rem;
        line-height: 1.6;
    }
    .store-list-container {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        background-color: #fcfdfe;
        padding: 10px;
        max-height: 350px;
        overflow-y: auto;
    }
    .store-list-item {
        background-color: #ffffff;
        border-radius: 12px;
        margin-bottom: 8px;
        border: 1px solid #f1f5f9;
        transition: all 0.2s ease;
    }
    .store-list-item:hover {
        background-color: #f8fafc !important;
        border-color: #e2e8f0;
    }
    
    /* Avatar user icon di header form edit */
    .user-avatar-header {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background-color: #0f172a;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.25rem;
    }
</style>

<div class="web3-container pb-5">
    <div class="container-fluid pt-4">
        <div class="row">
            <div class="col-xl-8 col-lg-10 mx-auto">
                
                <div class="d-flex justify-content-start mb-4">
                    <a href="{{ route('master.users.index') }}" class="btn btn-premium-outline d-flex align-items-center">
                        <i class="bi bi-arrow-left me-2"></i>Kembali ke Daftar User
                    </a>
                </div>

                <div class="d-flex flex-column gap-5">
                    
                    <div class="web3-form-card">
                        <div class="card-header-premium">
                            <div class="d-flex align-items-center gap-3">
                                @if(isset($user))
                                    <div class="user-avatar-header">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @else
                                    <div class="user-avatar-header bg-primary bg-opacity-10 text-primary">
                                        <i class="bi bi-person-plus-fill fs-4"></i>
                                    </div>
                                @endif
                                <div>
                                    <h4 class="section-title">
                                        {{ isset($user) ? 'Perbarui Data User' : 'Buat User Sistem Baru' }}
                                    </h4>
                                    @if(isset($user))
                                        <div class="text-muted small mt-1">ID User: {{ $user->username }}</div>
                                    @else
                                        <div class="text-muted small mt-1">Lengkapi data kredensial login di bawah ini</div>
                                    @endif
                                </div>
                            </div>
                            <span class="badge {{ isset($user) ? 'bg-primary' : 'bg-success' }} rounded-pill px-3 py-2 fw-semibold" style="font-size: 0.75rem;">
                                {{ isset($user) ? 'Mode Edit' : 'User Baru' }}
                            </span>
                        </div>
                        
                        <div class="card-body-premium">
                            <form method="POST" action="{{ isset($user) ? route('master.users.update', $user) : route('master.users.store') }}">
                                @csrf @if(isset($user)) @method('PUT') @endif
                                
                                <div class="row g-4">
                                    <div class="col-md-6 mb-2">
                                        <label class="premium-label">Nama Lengkap <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control premium-input" value="{{ old('name', $user->name ?? '') }}" placeholder="cth: John Doe" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-2">
                                        <label class="premium-label">Username (Login ID) <span class="text-danger">*</span></label>
                                        <input type="text" name="username" class="form-control premium-input" value="{{ old('username', $user->username ?? '') }}" placeholder="cth: johndoe99" required>
                                    </div>
                                    
                                    <div class="col-12 mb-2">
                                        <label class="premium-label">Alamat Email <span class="text-danger">*</span></label>
                                        <input type="email" name="email" class="form-control premium-input" value="{{ old('email', $user->email ?? '') }}" placeholder="johndoe@email.com" required>
                                    </div>
                                    
                                    <div class="col-12 mb-2">
                                        <label class="premium-label">Role Akses Sistem <span class="text-danger">*</span></label>
                                        <select name="role" class="form-select premium-select" required>
                                            <option value="">— Pilih Level Akses —</option>
                                            <option value="super_admin" {{ old('role', $user->role ?? '') === 'super_admin' ? 'selected' : '' }}>Super Admin (Akses Penuh Seluruh Toko)</option>
                                            <option value="admin_area" {{ old('role', $user->role ?? '') === 'admin_area' ? 'selected' : '' }}>Admin Area (Terbatas pada Toko yang Ditentukan)</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-6 mb-2">
                                        <label class="premium-label">
                                            Password 
                                            @if(isset($user)) 
                                                <span class="text-muted fw-normal" style="font-size: 0.78rem;">(Biarkan kosong jika tidak ingin mengubah)</span> 
                                            @else 
                                                <span class="text-danger">*</span> 
                                            @endif
                                        </label>
                                        <input type="password" name="password" class="form-control premium-input" placeholder="Minimal 8 karakter" {{ isset($user) ? '' : 'required' }} minlength="8">
                                    </div>
                                    
                                    <div class="col-md-6 mb-4">
                                        <label class="premium-label">Ulangi Password Baru</label>
                                        <input type="password" name="password_confirmation" class="form-control premium-input" placeholder="Konfirmasi password" minlength="8">
                                    </div>
                                    
                                    <div class="col-12 pt-3 border-top">
                                        <button type="submit" class="btn btn-premium-dark w-100">
                                            <i class="bi bi-save2-fill me-2"></i>{{ isset($user) ? 'Simpan Perubahan Data' : 'Daftarkan User Baru Sekarang' }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    @if(isset($user) && $user->role === 'admin_area')
                        <div class="web3-form-card mb-5">
                            <div class="card-header-premium">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="user-avatar-header bg-success bg-opacity-10 text-success">
                                        <i class="bi bi-shop-window fs-4"></i>
                                    </div>
                                    <div>
                                        <h4 class="section-title">Otorisasi Akses Toko</h4>
                                        <div class="text-muted small mt-1">Tentukan toko mana saja yang dapat dikelola oleh user ini</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card-body-premium">
                                <div class="info-card-premium mb-4 d-flex gap-3 align-items-start">
                                    <i class="bi bi-patch-check-fill fs-4 mt-1"></i>
                                    <div>
                                        <div class="fw-bold mb-1">Status: Admin Area</div>
                                        User dengan role Admin Area **wajib** diberikan akses ke minimal satu toko agar dapat bekerja. Pilih toko dari daftar di bawah untuk menambah akses.
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('master.users.assign-store', $user) }}" class="mb-5 p-4 border rounded-4 bg-light bg-opacity-50">
                                    @csrf
                                    <label class="premium-label">Berikan Otorisasi Toko Baru</label>
                                    <div class="row g-3">
                                        <div class="col-md-9">
                                            <select name="store_id" class="form-select premium-select" required>
                                                <option value="">— Cari dan Pilih Toko yang Tersedia —</option>
                                                @foreach($stores as $s)
                                                    @if(!in_array($s->id, $assignedStores))
                                                        <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->store_code }}) — {{ $s->area }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <button type="submit" class="btn btn-primary w-100 fw-bold h-100" style="border-radius: 14px;">
                                                <i class="bi bi-plus-lg me-1"></i>Tambah
                                            </button>
                                        </div>
                                    </div>
                                </form>
                                
                                <div class="mt-2">
                                    <h6 class="fw-bold text-dark mb-3 px-1" style="font-size: 1rem;">Daftar Toko Terotorisasi ({{ $user->stores->count() }})</h6>
                                    
                                    <div class="store-list-container flex-grow-1">
                                        @forelse($user->stores as $s)
                                            <div class="store-list-item d-flex align-items-center p-3">
                                                <div class="bg-light text-secondary rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 44px; height: 44px; border: 1px solid #e2e8f0;">
                                                    <i class="bi bi-shop fs-5"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="fw-bold text-dark" style="font-size: 0.98rem;">{{ $s->name }}</div>
                                                    <div class="d-flex align-items-center gap-2 mt-1">
                                                        <span class="badge bg-light text-secondary border px-2 py-1" style="font-size: 0.7rem; border-radius: 6px;">Kode: {{ $s->store_code }}</span>
                                                        <span class="text-muted small"><i class="bi bi-geo-alt-fill me-1"></i>{{ $s->area }}</span>
                                                    </div>
                                                </div>
                                                <form method="POST" action="{{ route('master.users.revoke-store', [$user, $s]) }}" class="d-inline m-0 ms-3" onsubmit="return confirm('Apakah Anda yakin ingin mencabut otorisasi akses toko {{ $s->name }} dari user ini?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-action-delete p-3" title="Cabut Otorisasi">
                                                        <i class="bi bi-x-lg fw-bold"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        @empty
                                            <div class="text-center py-5 d-flex flex-column align-items-center justify-content-center h-100 text-muted">
                                                <i class="bi bi-building-exclamation fs-1 mb-3 opacity-50"></i>
                                                <span class="fw-medium small">Belum ada otorisasi toko untuk user ini. <br> User tidak akan bisa login sebelum toko di-assign.</span>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection