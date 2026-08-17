{{--
    Modal: Phán quyết của Admin (Dùng chung cho Hoàn tiền và Từ chối khiếu nại)
    Tự động cập nhật tiêu đề, màu nút và action URL qua disputes.js
--}}
<div class="modal fade" id="decisionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="decisionModalTitle">
                    <i class="fa-solid fa-gavel me-2"></i>
                    Quyết định xử lý khiếu nại
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3" id="decisionModalDesc">
                    Vui lòng nhập lý do / phán quyết xử lý khiếu nại này.
                </p>
                <div class="mb-3">
                    <label for="adminDecisionNote" class="form-label fw-semibold">
                        Ghi chú / Quyết định của Admin <span class="text-danger">*</span>
                    </label>
                    <textarea id="adminDecisionNote"
                              class="form-control"
                              rows="4"
                              placeholder="Nhập chi tiết phán quyết và giải trình..."></textarea>
                    <div class="invalid-feedback d-none" id="decisionNoteError"></div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-sm" id="confirmDecisionBtn">
                    <i class="fa-solid fa-check me-1"></i>Xác nhận
                </button>
            </div>
        </div>
    </div>
</div>
