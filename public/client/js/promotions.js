/**
 * promotions.js
 * Handles: countdown timer, FS slider nav, coupon tab filter, save-coupon AJAX, toast.
 * All backend data is read from data-* attributes — no inline JS in Blade.
 */

/* ====================================================================
   1. CONFIG — read from #promotionsAppConfig
   ==================================================================== */
const _cfg = document.getElementById('promotionsAppConfig');
const SAVE_URL_TPL = _cfg ? _cfg.dataset.saveUrl : '';
const LOGIN_URL    = _cfg ? _cfg.dataset.loginUrl : '/login';
const CSRF         = _cfg ? _cfg.dataset.csrf : '';

/* ====================================================================
   2. TOAST
   ==================================================================== */
const _toast    = document.getElementById('promoToast');
const _toastMsg = document.getElementById('promoToastMsg');
let _toastTimer = null;

function showToast(msg, type) {
    if (!_toast) return;
    _toastMsg.textContent = msg;
    _toast.style.background = type === 'error' ? '#991b1b' : '#1e293b';
    _toast.classList.add('show');
    clearTimeout(_toastTimer);
    _toastTimer = setTimeout(() => _toast.classList.remove('show'), 3200);
}

/* ====================================================================
   3. COUNTDOWN TIMER
   ==================================================================== */
const countdownEl  = document.getElementById('promoCountdownTime');
const countdownWrap = document.getElementById('promoCountdown');

function startCountdown() {
    if (!countdownWrap || !countdownEl) return;

    const endsAt = new Date(countdownWrap.dataset.endsAt);
    if (isNaN(endsAt)) return;

    function tick() {
        const diff = Math.max(0, endsAt - Date.now());
        const h = String(Math.floor(diff / 3600000)).padStart(2, '0');
        const m = String(Math.floor((diff % 3600000) / 60000)).padStart(2, '0');
        const s = String(Math.floor((diff % 60000) / 1000)).padStart(2, '0');
        countdownEl.textContent = `${h}:${m}:${s}`;
        if (diff === 0) { countdownWrap.innerHTML = '<i class="fa-solid fa-flag-checkered"></i> Đã kết thúc'; }
    }

    tick();
    setInterval(tick, 1000);
}

/* ====================================================================
   4. FLASH SALE SLIDER NAV
   ==================================================================== */
function initFsSlider() {
    const track = document.getElementById('promoFsTrack');
    const prev  = document.getElementById('promoPrev');
    const next  = document.getElementById('promoNext');
    if (!track || !prev || !next) return;

    const SCROLL_AMOUNT = 520;

    prev.addEventListener('click', () => track.scrollBy({ left: -SCROLL_AMOUNT, behavior: 'smooth' }));
    next.addEventListener('click', () => track.scrollBy({ left: SCROLL_AMOUNT, behavior: 'smooth' }));
}

/* ====================================================================
   5. COUPON TAB FILTER
   ==================================================================== */
function initCouponTabs() {
    const tabs = document.querySelectorAll('#couponTabs .promo-tab-btn');
    if (!tabs.length) return;

    tabs.forEach(btn => {
        btn.addEventListener('click', () => {
            // active button
            tabs.forEach(t => t.classList.remove('active'));
            btn.classList.add('active');

            // show/hide panes
            const target = btn.dataset.tab;
            document.querySelectorAll('.promo-coupon-pane').forEach(pane => {
                pane.classList.toggle('d-none', pane.id !== `pane-${target}`);
            });
        });
    });
}

/* ====================================================================
   6. SAVE COUPON — AJAX
   ==================================================================== */
function initSaveCoupons() {
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.promo-btn-save');
        if (!btn) return;

        const couponId = btn.dataset.couponId;
        if (!couponId || !SAVE_URL_TPL) return;

        const url = SAVE_URL_TPL.replace('__ID__', couponId);

        btn.disabled = true;
        btn.textContent = 'Đang lưu...';

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
        })
        .then(r => r.json())
        .then(data => {
            if (data.require_login) {
                window.location.href = LOGIN_URL;
                return;
            }
            if (data.success || data.already_saved) {
                // Replace button with "Đã lưu" badge
                const saved = document.createElement('span');
                saved.className = 'promo-btn-saved';
                saved.innerHTML = '<i class="fa-solid fa-check me-1"></i>Đã lưu';
                btn.replaceWith(saved);
                showToast(data.already_saved ? 'Bạn đã lưu mã này rồi.' : 'Đã lưu vào ví thành công!', 'success');
            } else {
                btn.disabled = false;
                btn.textContent = 'Lưu mã';
                showToast(data.message || 'Không thể lưu mã. Vui lòng thử lại.', 'error');
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.textContent = 'Lưu mã';
            showToast('Đã xảy ra lỗi. Vui lòng thử lại.', 'error');
        });
    });
}

/* ====================================================================
   7. INIT
   ==================================================================== */
document.addEventListener('DOMContentLoaded', () => {
    startCountdown();
    initFsSlider();
    initCouponTabs();
    initSaveCoupons();
});
