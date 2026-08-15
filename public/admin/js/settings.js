/**
 * CUPO ADMIN — Cài Đặt Hệ Thống (settings.js)
 *
 * Xử lý:
 * 1. Chuyển đổi Tab cài đặt mượt mà
 * 2. Xem trước ảnh khi chọn upload (Logo, Favicon, OG Image)
 * 3. Ẩn / hiện mật khẩu và Secret Key
 * 4. Gửi Ajax Test Email kiểm tra kết nối SMTP
 *
 * Tuân thủ Rule 24 AGENT.md: Cấu hình và dữ liệu lấy từ data-* attributes
 */

(function () {
    'use strict';

    const cfgEl = document.getElementById('settingsAppConfig');
    if (!cfgEl) return;

    const CSRF = cfgEl.dataset.csrf || document.querySelector('meta[name="csrf-token"]')?.content;
    const TEST_MAIL_URL = cfgEl.dataset.testMailUrl;

    // =========================================================================
    // 1. CHUYỂN ĐỔI TAB CÀI ĐẶT
    // =========================================================================
    const navItems = document.querySelectorAll('.settings-nav-item');
    const tabPanes = document.querySelectorAll('.settings-tab-pane');

    navItems.forEach(item => {
        item.addEventListener('click', function () {
            const targetSelector = this.dataset.target;
            const targetPane = document.querySelector(targetSelector);
            if (!targetPane) return;

            navItems.forEach(nav => nav.classList.remove('active'));
            tabPanes.forEach(pane => pane.classList.remove('active'));

            this.classList.add('active');
            targetPane.classList.add('active');

            // Lưu tab active vào history hash
            if (history.replaceState) {
                history.replaceState(null, null, targetSelector);
            }
        });
    });

    // Mở đúng tab nếu có hash trên URL (VD: #tab-mail)
    if (window.location.hash) {
        const activeNav = document.querySelector(`.settings-nav-item[data-target="${window.location.hash}"]`);
        if (activeNav) {
            activeNav.click();
        }
    }

    // =========================================================================
    // 2. XEM TRƯỚC ẢNH KHI CHỌN UPLOAD
    // =========================================================================
    document.querySelectorAll('.btn-trigger-upload').forEach(btn => {
        btn.addEventListener('click', function () {
            const targetInput = document.querySelector(this.dataset.target);
            if (targetInput) targetInput.click();
        });
    });

    document.querySelectorAll('.file-input-preview').forEach(input => {
        input.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;

            const previewImg = document.querySelector(this.dataset.preview);
            const placeholder = document.querySelector(this.dataset.placeholder);

            if (previewImg) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImg.src = e.target.result;
                    previewImg.classList.remove('d-none');
                    if (placeholder) placeholder.classList.add('d-none');
                };
                reader.readAsDataURL(file);
            }
        });
    });

    // =========================================================================
    // 3. ẨN / HIỆN MẬT KHẨU VÀ SECRET KEY
    // =========================================================================
    document.querySelectorAll('.btn-toggle-password').forEach(btn => {
        btn.addEventListener('click', function () {
            const input = this.closest('.input-group')?.querySelector('.password-toggle-input');
            if (!input) return;

            const isPassword = input.getAttribute('type') === 'password';
            input.setAttribute('type', isPassword ? 'text' : 'password');

            const icon = this.querySelector('i');
            if (icon) {
                icon.className = isPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
            }
        });
    });

    // =========================================================================
    // 4. GỬI EMAIL TEST KIỂM TRA SMTP
    // =========================================================================
    const btnSendTestMail = document.getElementById('btnSendTestMail');
    const testEmailInput = document.getElementById('testEmailInput');
    const testMailResult = document.getElementById('testMailResult');

    if (btnSendTestMail && testEmailInput) {
        btnSendTestMail.addEventListener('click', function () {
            const email = testEmailInput.value.trim();
            if (!email) {
                testEmailInput.classList.add('is-invalid');
                return;
            }
            testEmailInput.classList.remove('is-invalid');

            const originalHtml = btnSendTestMail.innerHTML;
            btnSendTestMail.disabled = true;
            btnSendTestMail.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Đang gửi...';
            if (testMailResult) testMailResult.innerHTML = '';

            fetch(TEST_MAIL_URL, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                },
                body: JSON.stringify({ test_email: email }),
            })
                .then(async r => {
                    const data = await r.json();
                    if (!r.ok || !data.success) {
                        throw data.message || 'Lỗi gửi email.';
                    }
                    return data;
                })
                .then(data => {
                    if (testMailResult) {
                        testMailResult.innerHTML = `<span class="text-success fw-bold"><i class="fa-solid fa-circle-check me-1"></i>${escapeHtml(data.message)}</span>`;
                    }
                    showToast('Đã gửi email thử nghiệm thành công!', 'success');
                })
                .catch(err => {
                    if (testMailResult) {
                        testMailResult.innerHTML = `<span class="text-danger fw-bold"><i class="fa-solid fa-triangle-exclamation me-1"></i>${escapeHtml(String(err))}</span>`;
                    }
                    showToast(String(err), 'danger');
                })
                .finally(() => {
                    btnSendTestMail.disabled = false;
                    btnSendTestMail.innerHTML = originalHtml;
                });
        });
    }

    // =========================================================================
    // HELPERS
    // =========================================================================
    function escapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function showToast(message, type = 'success') {
        const toastEl = document.getElementById('settingsToast');
        if (!toastEl) return;

        toastEl.className = `toast align-items-center text-white border-0 bg-${type === 'success' ? 'success' : 'danger'}`;
        const body = document.getElementById('settingsToastMessage');
        if (body) {
            body.innerHTML = `<i class="fa-solid fa-${type === 'success' ? 'circle-check' : 'triangle-exclamation'} me-2"></i>${escapeHtml(message)}`;
        }
        const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
        toast.show();
    }

})();
