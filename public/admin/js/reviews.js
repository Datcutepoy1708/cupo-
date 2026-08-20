/**
 * =========================================================
 * CUPO ADMIN - PRODUCT REVIEWS MODERATION JAVASCRIPT
 * =========================================================
 */

(function () {
    'use strict';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const resolveModalEl = document.getElementById('adminResolveReportModal');
    const bsResolveModal = resolveModalEl ? new bootstrap.Modal(resolveModalEl) : null;
    let selectedReviewId = null;

    // Toggle Review Visibility (Hide / Show)
    document.querySelectorAll('.btn-toggle-review').forEach(btn => {
        btn.addEventListener('click', function () {
            const reviewId = this.dataset.reviewId;
            const currentStatus = this.dataset.status;
            const actionText = currentStatus === 'approved' ? 'ẩn đánh giá này khỏi sàn' : 'khôi phục hiển thị đánh giá này';

            if (!confirm(`Bạn có chắc chắn muốn ${actionText}?`)) return;

            this.disabled = true;

            fetch(`/admin/reviews/${reviewId}/toggle`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
                .then(r => r.json())
                .then(res => {
                    alert(res.message);
                    location.reload();
                })
                .catch(() => {
                    alert('Lỗi kết nối máy chủ!');
                    this.disabled = false;
                });
        });
    });

    // Open Resolve Report Modal
    document.querySelectorAll('.btn-resolve-report').forEach(btn => {
        btn.addEventListener('click', function () {
            selectedReviewId = this.dataset.reviewId;
            const shopName = this.dataset.shop || '';
            const reason = this.dataset.reason || '';
            const comment = this.dataset.comment || '';

            if (document.getElementById('modalReportShopName')) {
                document.getElementById('modalReportShopName').textContent = shopName;
            }
            if (document.getElementById('modalReportReasonText')) {
                document.getElementById('modalReportReasonText').textContent = reason;
            }
            if (document.getElementById('modalCustomerCommentText')) {
                document.getElementById('modalCustomerCommentText').textContent = `"${comment}"`;
            }
            if (document.getElementById('adminNoteInput')) {
                document.getElementById('adminNoteInput').value = '';
            }

            bsResolveModal?.show();
        });
    });

    // Submit Resolve Decision
    function submitResolveReport(decision) {
        if (!selectedReviewId) return;

        const adminNote = document.getElementById('adminNoteInput')?.value.trim() || '';

        fetch(`/admin/reviews/${selectedReviewId}/resolve-report`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                decision: decision,
                admin_note: adminNote
            })
        })
            .then(r => r.json())
            .then(res => {
                alert(res.message);
                location.reload();
            })
            .catch(() => {
                alert('Lỗi kết nối máy chủ!');
            });
    }

    document.getElementById('btnApproveReport')?.addEventListener('click', function () {
        submitResolveReport('approve_report');
    });

    document.getElementById('btnDismissReport')?.addEventListener('click', function () {
        submitResolveReport('dismiss_report');
    });

})();
