<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In — Glacier</title>
    <link rel="stylesheet" href="{{ asset('css/bootstrap-icons.css') }}">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            height: 100vh;
            display: flex;
            background: #fff;
            overflow: hidden;
        }

        /* ───── LEFT PANEL ───────────────────────────────────────────── */
        .left {
            width: 44%;
            background: linear-gradient(150deg, #dce9fb 0%, #cdd5f8 45%, #dcd0f5 100%);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 0 64px;
            overflow: hidden;
        }

        /* decorative circles */
        .left::before, .left::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,.3);
        }
        .left::before { width: 380px; height: 380px; top: -120px; right: -120px; }
        .left::after  { width: 260px; height: 260px; bottom: -80px; left: -80px; }

        .left-content { position: relative; z-index: 1; }

        .app-badge {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(255,255,255,.6);
            backdrop-filter: blur(8px);
            border-radius: 100px;
            padding: 6px 16px 6px 10px;
            font-size: .78rem; font-weight: 700;
            color: #3d4ec6; letter-spacing: .01em;
            margin-bottom: 40px;
        }
        .app-badge span { font-size: 1.15rem; }

        .left h1 {
            font-size: 2.05rem; font-weight: 800; line-height: 1.22;
            color: #1a2562; margin-bottom: 16px;
        }

        .left p {
            font-size: .92rem; color: #4a5490;
            line-height: 1.65; max-width: 280px;
        }

        /* ───── RIGHT PANEL ──────────────────────────────────────────── */
        .right {
            flex: 1;
            background: #f4f6fb;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 24px;
        }

        /* ───── CARD ─────────────────────────────────────────────────── */
        .card {
            background: #ffffff;
            border-radius: 18px;
            padding: 40px 36px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 4px 32px rgba(80,100,200,.10), 0 1px 4px rgba(0,0,0,.04);
            border: none;
        }

        .card-title {
            font-size: 1.7rem; font-weight: 800;
            color: #111827; margin-bottom: 4px;
        }
        .card-sub {
            font-size: .85rem; color: #9ca3af;
            margin-bottom: 28px;
        }

        /* ───── FORM ─────────────────────────────────────────────────── */
        label {
            display: block;
            font-size: .8rem; font-weight: 600; color: #374151;
            margin-bottom: 7px;
        }

        input[type="email"],
        input[type="password"],
        input[type="text"] {
            width: 100%;
            background: #f3f4f6;
            border: 1.5px solid #e5e7eb;
            border-radius: 9px;
            padding: 11px 14px;
            font-family: inherit;
            font-size: .9rem;
            color: #111827;
            outline: none;
            transition: border-color .18s, box-shadow .18s;
        }
        input:focus {
            border-color: #006275;
            box-shadow: 0 0 0 3px rgba(0,98,117,.13);
            background: #fff;
        }
        input.is-invalid { border-color: #ef4444 !important; }

        .field { margin-bottom: 18px; }

        .pwd-wrap { position: relative; }
        .pwd-wrap input { padding-right: 44px; }
        .eye-btn {
            position: absolute; right: 12px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: #9ca3af; font-size: .95rem; padding: 0;
            line-height: 1; transition: color .15s;
        }
        .eye-btn:hover { color: #006275; }

        /* remember */
        .remember-row {
            display: flex; align-items: center; gap: 9px;
            margin-bottom: 22px;
        }
        .remember-row input[type="checkbox"] {
            width: 16px; height: 16px;
            accent-color: #006275;
            border-radius: 4px; cursor: pointer;
            flex: none;
        }
        .remember-row label {
            font-size: .84rem; color: #6b7280;
            margin: 0; cursor: pointer;
        }

        /* submit */
        .btn-login {
            width: 100%; padding: 13px;
            background: #006275;
            color: #fff; border: none; border-radius: 10px;
            font-family: inherit; font-size: .93rem; font-weight: 700;
            cursor: pointer; letter-spacing: .01em;
            transition: background .18s, transform .1s;
        }
        .btn-login:hover  { background: #004f5f; }
        .btn-login:active { transform: scale(.985); }

        /* error */
        .err-box {
            display: flex; align-items: center; gap: 9px;
            background: #fef2f2; border: 1.5px solid #fecaca;
            border-radius: 9px; padding: 10px 13px;
            font-size: .83rem; color: #b91c1c;
            margin-bottom: 18px;
        }
        .invalid-msg {
            font-size: .77rem; color: #ef4444; margin-top: 5px;
        }

        /* ───── RESPONSIVE ───────────────────────────────────────────── */
        @media (max-width: 780px) {
            body { flex-direction: column; overflow: auto; height: auto; }
            .left { width: 100%; padding: 48px 32px 40px; }
            .left::before { width: 220px; height: 220px; }
            .left::after  { width: 160px; height: 160px; }
            .right { padding: 40px 20px 56px; }
        }
    </style>
</head>
<body>

{{-- LEFT --}}
<div class="left">
    <div class="left-content">
        <div class="app-badge">
            <span>❄️</span> Glacier
        </div>
        <h1>Fast, Efficient<br>and Productive</h1>
        <p>Sistem manajemen inventori bahan baku, perencanaan order, dan laporan HPP secara real-time.</p>
    </div>
</div>

{{-- RIGHT --}}
<div class="right">
    <div class="card">

        <div class="card-title">Sign In</div>
        <div class="card-sub">Glacier Inventory</div>

        @if($errors->any())
        <div class="err-box">
            <i class="bi bi-exclamation-circle-fill"></i>
            {{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="field">
                <label for="username">Username</label>
                <input type="text" id="username" name="username"
                       value="{{ old('username') }}"
                       placeholder="Masukkan username"
                       class="{{ $errors->has('username') ? 'is-invalid' : '' }}"
                       required autofocus autocomplete="username">
                @error('username')
                    <div class="invalid-msg">{{ $message }}</div>
                @enderror
            </div>

            <div class="field">
                <label for="pwdField">Password</label>
                <div class="pwd-wrap">
                    <input type="password" id="pwdField" name="password"
                           placeholder="••••••••"
                           class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                           required>
                    <button type="button" class="eye-btn" onclick="togglePwd()">
                        <i class="bi bi-eye" id="eyeIco"></i>
                    </button>
                </div>
                @error('password')
                    <div class="invalid-msg">{{ $message }}</div>
                @enderror
            </div>

            <div class="remember-row">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Ingat saya di perangkat ini</label>
            </div>

            <button type="submit" class="btn-login">Sign In</button>

        </form>
    </div>
</div>

<script>
function togglePwd() {
    var f = document.getElementById('pwdField');
    var i = document.getElementById('eyeIco');
    f.type = (f.type === 'password') ? 'text' : 'password';
    i.className = (f.type === 'text') ? 'bi bi-eye-slash' : 'bi bi-eye';
}
</script>
</body>
</html>
