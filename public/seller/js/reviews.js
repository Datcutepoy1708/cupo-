/**
 * =========================================================
 * CUPO SELLER - PRODUCT REVIEWS JAVASCRIPT
 * =========================================================
 */

(function () {
    'use strict';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const reportModalEl = document.getElementById('sellerReportReviewModal');
    const bsReportModal = reportModalEl ? new bootstrap.Modal(reportModalEl) : null;
    let selectedReviewId = null;

    // Handle Reply Form Submit
    document.querySelectorAll('.reply-review-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const reviewId = this.dataset.reviewId;
            const textarea = this.querySelector('textarea[name="reply"]');
            const submitBtn = this.querySelector('button[type="submit"]');
            const replyText = textarea.value.trim();

            if (!replyText) {
                alert('Vui lòng nhập nội dung phản hồi!');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Đang gửi...';

            fetch(`/seller/reviews/${reviewId}/reply`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ reply: replyText })
            })
                .then(r => r.json())
                .then(res => {
                    if (res.data) {
                        location.reload();
                    } else {
                        alert(res.message || 'Có lỗi xảy ra!');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'Gửi phản hồi';
                    }
                })
                .catch(() => {
                    alert('Lỗi kết nối máy chủ!');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Gửi phản hồi';
                });
        });
    });

    // Handle Report Button Click
    document.querySelectorAll('.btn-report-review').forEach(btn => {
        btn.addEventListener('click', function () {
            selectedReviewId = this.dataset.reviewId;
            const reviewComment = this.dataset.comment || '';
            const customerName = this.dataset.customer || '';

            if (document.getElementById('modalReportCustomerName')) {
                document.getElementById('modalReportCustomerName').textContent = customerName;
            }
            if (document.getElementById('modalReportComment')) {
                document.getElementById('modalReportComment').textContent = `"${reviewComment}"`;
            }
            if (document.getElementById('reportReasonInput')) {
                document.getElementById('reportReasonInput').value = '';
            }

            bsReportModal?.show();
        });
    });

    // Handle Submit Report
    document.getElementById('btnSubmitReport')?.addEventListener('click', function () {
        if (!selectedReviewId) return;

        const reasonSelect = document.getElementById('reportReasonSelect')?.value || '';
        const reasonDetail = document.getElementById('reportReasonInput')?.value.trim() || '';
        const finalReason = reasonDetail ? `${reasonSelect}: ${reasonDetail}` : reasonSelect;

        if (!finalReason) {
            alert('Vui lòng chọn hoặc nhập lý do khiếu nại!');
            return;
        }

        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Đang gửi khiếu nại...';

        fetch(`/seller/reviews/${selectedReviewId}/report`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ report_reason: finalReason })
        })
            .then(r => r.json())
            .then(res => {
                alert(res.message || 'Đã gửi báo cáo vi phạm!');
                location.reload();
            })
            .catch(() => {
                alert('Lỗi kết nối máy chủ!');
                btn.disabled = false;
                btn.innerHTML = 'Gửi khiếu nại';
            });
    });

})();
