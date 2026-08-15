/**
 * Vouchers Handler — AJAX Claim / Save Vouchers
 */
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // Claim Voucher Event Listener
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-voucher-claim');
        if (!btn || btn.classList.contains('loading') || btn.classList.contains('btn-voucher-saved')) {
            return;
        }

        e.preventDefault();
        const claimUrl = btn.dataset.claimUrl;
        const couponId = btn.dataset.couponId;

        if (!claimUrl) {
            return;
        }

        // Set Loading State
        const originalHtml = btn.innerHTML;
        btn.classList.add('loading');
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Đang lưu...';
        btn.disabled = true;

        fetch(claimUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
        })
            .then(async (response) => {
                const data = await response.json();

                if (response.status === 401 || data.require_login) {
                    if (confirm('Vui lòng đăng nhập để lưu mã giảm giá vào ví. Đi đến trang đăng nhập ngay?')) {
                        window.location.href = '/login';
                    }
                    btn.innerHTML = originalHtml;
                    btn.classList.remove('loading');
                    btn.disabled = false;
                    return;
                }

                if (response.ok && data.success) {
                    // Update Button State to Saved
                    btn.classList.remove('btn-voucher-claim', 'loading');
                    btn.classList.add('btn-voucher-saved');
                    btn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Đã Lưu';
                    btn.disabled = true;

                    // Update Owned Voucher Count Badges if present
                    const countBadges = document.querySelectorAll('.owned-voucher-count-badge');
                    countBadges.forEach((badge) => {
                        badge.textContent = data.owned_count;
                    });

                    // Show Toast Notification
                    showVoucherToast(data.message || 'Đã lưu mã giảm giá vào ví của bạn!', 'success');
                } else {
                    btn.innerHTML = originalHtml;
                    btn.classList.remove('loading');
                    btn.disabled = false;
                    showVoucherToast(data.message || 'Không thể lưu mã giảm giá.', 'error');
                }
            })
            .catch((error) => {
                console.error('Error claiming voucher:', error);
                btn.innerHTML = originalHtml;
                btn.classList.remove('loading');
                btn.disabled = false;
                showVoucherToast('Có lỗi xảy ra khi lưu mã giảm giá. Vui lòng thử lại!', 'error');
            });
    });

    // Simple Toast Notification Helper
    function showVoucherToast(message, type = 'success') {
        let toastContainer = document.getElementById('voucherToastContainer');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'voucherToastContainer';
            toastContainer.style.position = 'fixed';
            toastContainer.style.bottom = '24px';
            toastContainer.style.right = '24px';
            toastContainer.style.zIndex = '99999';
            document.body.appendChild(toastContainer);
        }

        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show shadow-lg d-flex align-items-center gap-2 mb-2`;
        toast.style.minWidth = '280px';
        toast.style.borderRadius = '8px';
        toast.style.animation = 'fadeInUp 0.3s ease';
        toast.innerHTML = `
            <i class="fa-solid ${type === 'success' ? 'fa-circle-check text-success' : 'fa-circle-exclamation text-danger'} fs-5"></i>
            <div class="flex-fill small fw-medium">${message}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="padding: 0.75rem;"></button>
        `;

        toastContainer.appendChild(toast);

        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    }
});
