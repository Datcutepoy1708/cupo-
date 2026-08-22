<div class="modal fade" id="sellerReportReviewModal" tabindex="-1" aria-labelledby="sellerReportReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
            <div class="modal-header bg-danger text-white p-3">
                <h5 class="modal-title fw-bold fs-6" id="sellerReportReviewModalLabel">
                    <i class="fa-solid fa-flag me-2"></i>Báo cáo Đánh giá Vi phạm lên Admin
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="p-3 bg-light rounded-3 mb-3 border">
                    <div class="small text-muted mb-1">Khách hàng: <strong id="modalReportCustomerName" class="text-dark"></strong></div>
                    <div class="small fst-italic text-secondary" id="modalReportComment"></div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">Lý do khiếu nại vi phạm <span class="text-danger">*</span></label>
                    <select id="reportReasonSelect" class="form-select mb-2">
                        <option value="Đánh giá chứa từ ngữ thô tục / xúc phạm">Đánh giá chứa từ ngữ thô tục / xúc phạm</option>
                        <option value="Đánh giá spam / quảng cáo đối thủ / link lừa đảo">Đánh giá spam / quảng cáo đối thủ / link lừa đảo</option>
                        <option value="Đánh giá sai sự thật / vu khống phá hoại shop">Đánh giá sai sự thật / vu khống phá hoại shop</option>
                        <option value="Đánh giá lộ thông tin cá nhân (SĐT, địa chỉ)">Đánh giá lộ thông tin cá nhân (SĐT, địa chỉ)</option>
                        <option value="Khác">Lý do khác...</option>
                    </select>
                    <textarea id="reportReasonInput" class="form-control" rows="3" placeholder="Mô tả chi tiết bằng chứng hoặc lý do bạn muốn Admin gỡ đánh giá này..."></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Hủy bỏ</button>
                <button type="button" id="btnSubmitReport" class="btn btn-danger px-4 fw-semibold">
                    <i class="fa-solid fa-paper-plane me-1"></i>Gửi khiếu nại
                </button>
            </div>
        </div>
    </div>
</div>
