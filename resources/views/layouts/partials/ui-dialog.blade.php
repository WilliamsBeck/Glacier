{{-- ════════════════════════════════════════════════════════════════════════
     GLOBAL UI DIALOG — pengganti alert()/confirm() bawaan browser.
     Pakai:
       await uiAlert('Pesan', {type:'success|error|warning|info', title:'...'})
       const ok = await uiConfirm('Yakin?', {type:'warning', confirmText:'Hapus'})
       <form data-confirm="Hapus data ini?">  →  intercept submit otomatis
     window.alert() juga otomatis memakai dialog ini.
═══════════════════════════════════════════════════════════════════════════ --}}
<div id="uiDialogOverlay" class="ui-dialog-overlay" aria-hidden="true">
    <div class="ui-dialog-box" role="dialog" aria-modal="true" aria-labelledby="uiDialogTitle">
        <div class="ui-dialog-icon" id="uiDialogIcon"><i class="bi bi-question-lg"></i></div>
        <div class="ui-dialog-title" id="uiDialogTitle">Konfirmasi</div>
        <div class="ui-dialog-msg" id="uiDialogMsg"></div>
        <div class="ui-dialog-actions">
            <button type="button" class="ui-dialog-btn ui-dialog-cancel" id="uiDialogCancel">Batal</button>
            <button type="button" class="ui-dialog-btn ui-dialog-ok" id="uiDialogOk">OK</button>
        </div>
    </div>
</div>

<style>
    .ui-dialog-overlay {
        position: fixed; inset: 0; z-index: 4000;
        display: flex; align-items: center; justify-content: center;
        background: rgba(15, 23, 42, .55); backdrop-filter: blur(2px);
        opacity: 0; pointer-events: none; transition: opacity .18s ease;
        padding: 16px;
    }
    .ui-dialog-overlay.show { opacity: 1; pointer-events: auto; }
    .ui-dialog-box {
        background: #fff; border-radius: 16px; width: 100%; max-width: 400px;
        padding: 28px 26px 22px; text-align: center;
        box-shadow: 0 24px 60px rgba(0, 0, 0, .25);
        transform: translateY(12px) scale(.97); transition: transform .2s cubic-bezier(.2, .9, .3, 1.2);
        font-family: 'Inter', sans-serif;
    }
    .ui-dialog-overlay.show .ui-dialog-box { transform: translateY(0) scale(1); }
    .ui-dialog-icon {
        width: 58px; height: 58px; border-radius: 50%; margin: 0 auto 16px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.7rem; color: #fff; background: #006275;
    }
    .ui-dialog-icon.is-success { background: #16a34a; }
    .ui-dialog-icon.is-error   { background: #dc2626; }
    .ui-dialog-icon.is-warning { background: #f59e0b; }
    .ui-dialog-icon.is-info    { background: #006275; }
    .ui-dialog-title {
        font-size: 1.08rem; font-weight: 700; color: #0f172a; margin-bottom: 6px;
    }
    .ui-dialog-msg {
        font-size: .88rem; color: #475569; line-height: 1.5; margin-bottom: 22px;
        white-space: pre-line;
    }
    .ui-dialog-actions { display: flex; gap: 10px; justify-content: center; }
    .ui-dialog-btn {
        flex: 1; max-width: 160px; border: 0; border-radius: 10px;
        padding: 10px 16px; font-size: .88rem; font-weight: 600; cursor: pointer;
        transition: filter .15s, background .15s;
    }
    .ui-dialog-cancel { background: #e2e8f0; color: #334155; }
    .ui-dialog-cancel:hover { background: #cbd5e1; }
    .ui-dialog-ok { background: #006275; color: #fff; }
    .ui-dialog-ok:hover { filter: brightness(1.08); }
    .ui-dialog-ok.is-danger { background: #dc2626; }
    .ui-dialog-box.is-alert .ui-dialog-cancel { display: none; }
    .ui-dialog-box.is-alert .ui-dialog-ok { max-width: 200px; }
</style>

<script>
    window.UIDialog = (function () {
        const overlay = document.getElementById('uiDialogOverlay');
        const box     = overlay.querySelector('.ui-dialog-box');
        const iconWrap= document.getElementById('uiDialogIcon');
        const titleEl = document.getElementById('uiDialogTitle');
        const msgEl   = document.getElementById('uiDialogMsg');
        const okBtn   = document.getElementById('uiDialogOk');
        const cancelBtn = document.getElementById('uiDialogCancel');

        const ICONS = {
            success: 'bi-check-lg', error: 'bi-x-lg',
            warning: 'bi-exclamation-lg', info: 'bi-info-lg',
            question: 'bi-question-lg',
        };
        const DEFAULT_TITLE = {
            success: 'Berhasil', error: 'Gagal', warning: 'Perhatian',
            info: 'Informasi', question: 'Konfirmasi',
        };

        let resolver = null;

        function settle(val) {
            if (!resolver) return;
            const r = resolver; resolver = null;
            overlay.classList.remove('show');
            overlay.setAttribute('aria-hidden', 'true');
            setTimeout(() => r(val), 150);
        }

        function show(opts) {
            const {
                message = '', isConfirm = false,
                type = isConfirm ? 'question' : 'info',
                title = DEFAULT_TITLE[type] || 'Informasi',
                confirmText = 'OK', cancelText = 'Batal',
                danger = false,
            } = opts;

            // bila ada dialog terbuka, tutup dulu
            if (resolver) settle(isConfirm ? false : undefined);

            iconWrap.className = 'ui-dialog-icon is-' + type;
            iconWrap.innerHTML = '<i class="bi ' + (ICONS[type] || ICONS.info) + '"></i>';
            titleEl.textContent = title;
            msgEl.textContent = message;
            okBtn.textContent = confirmText;
            cancelBtn.textContent = cancelText;
            box.classList.toggle('is-alert', !isConfirm);
            okBtn.classList.toggle('is-danger', !!danger || (isConfirm && type === 'error'));

            overlay.classList.add('show');
            overlay.setAttribute('aria-hidden', 'false');
            setTimeout(() => okBtn.focus(), 50);

            return new Promise(res => { resolver = res; });
        }

        okBtn.addEventListener('click', () => settle(true));
        cancelBtn.addEventListener('click', () => settle(false));
        overlay.addEventListener('mousedown', e => {
            if (e.target === overlay) settle(box.classList.contains('is-alert') ? true : false);
        });
        document.addEventListener('keydown', e => {
            if (!overlay.classList.contains('show')) return;
            if (e.key === 'Escape') settle(box.classList.contains('is-alert') ? true : false);
            if (e.key === 'Enter')  settle(true);
        });

        return {
            alert:   (message, o = {}) => show({ ...o, message: String(message ?? ''), isConfirm: false }),
            confirm: (message, o = {}) => show({ ...o, message: String(message ?? ''), isConfirm: true }),
        };
    })();

    window.uiAlert   = (msg, o) => UIDialog.alert(msg, o);
    window.uiConfirm = (msg, o) => UIDialog.confirm(msg, o);

    // Override alert() bawaan browser → semua alert() lama otomatis pakai dialog ini
    window.alert = (msg) => { UIDialog.alert(msg); };

    // ── Auto-wire tombol/link [data-confirm] (mis. 1 dari beberapa submit) ──────
    document.addEventListener('click', function (e) {
        const el = e.target.closest('[data-confirm]');
        if (!el || el.tagName === 'FORM') return;
        // form pakai handler submit di bawah; di sini hanya untuk button/a
        if (el.dataset.confirmed === '1') { el.dataset.confirmed = ''; return; }
        e.preventDefault();
        e.stopPropagation();
        uiConfirm(el.getAttribute('data-confirm'), {
            type: el.dataset.confirmType || 'warning',
            confirmText: el.dataset.confirmOk || 'Ya, lanjut',
            cancelText: el.dataset.confirmCancel || 'Batal',
            danger: el.dataset.confirmDanger === '1',
        }).then(ok => {
            if (!ok) return;
            el.dataset.confirmed = '1';
            const form = el.form || el.closest('form');
            if (el.type === 'submit' && form) {
                form.dataset.confirmed = '1'; // lewati handler submit form
                if (form.requestSubmit) form.requestSubmit(el); else form.submit();
            } else {
                el.click();
            }
        });
    }, true);

    // ── Auto-wire: <form data-confirm="..."> ───────────────────────────────────
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!(form instanceof HTMLFormElement)) return;
        const msg = form.getAttribute('data-confirm');
        if (!msg) return;
        if (form.dataset.confirmed === '1') { form.dataset.confirmed = ''; return; }
        e.preventDefault();
        e.stopPropagation();
        uiConfirm(msg, {
            type: form.dataset.confirmType || 'warning',
            confirmText: form.dataset.confirmOk || 'Ya, lanjut',
            cancelText: form.dataset.confirmCancel || 'Batal',
            danger: form.dataset.confirmDanger === '1',
        }).then(ok => {
            if (!ok) return;
            form.dataset.confirmed = '1';
            if (form.requestSubmit) form.requestSubmit(); else form.submit();
        });
    }, false);
</script>
