<div class="modal fade" id="adminResolveReportModal" tabindex="-1" aria-labelledby="adminResolveReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white p-4">
                <h5 class="modal-title fw-bold" id="adminResolveReportModalLabel">
                    <i class="fa-solid fa-scale-balanced text-warning me-2"></i>Xử lý Khiếu nại Báo cáo Đánh giá
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 border h-100">
                            <span class="text-muted small d-block">Gian hàng khiếu nại:</span>
                            <strong id="modalReportShopName" class="text-dark fs-6"></strong>
                            <div class="mt-2 text-danger small">
                                <span class="fw-bold d-block">Lý do báo cáo vi phạm:</span>
                                <span id="modalReportReasonText"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 border h-100">
                            <span class="text-muted small d-block">Nội dung đánh giá bị khiếu nại:</span>
                            <div id="modalCustomerCommentText" class="fst-italic text-dark mt-1"></div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">Ghi chú xử lý của Ban quản trị sàn:</label>
                    <textarea id="adminNoteInput" class="form-control" rows="3" placeholder="Nhập lý do chấp thuận ẩn đánh giá hoặc lý do bác bỏ khiếu nại..."></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light d-flex justify-content-between">
                <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Đóng</button>
                <div class="d-flex gap-2">
                    <button type="button" id="btnDismissReport" class="btn btn-outline-danger px-4">
                        <i class="fa-solid fa-xmark me-1"></i>Bác bỏ khiếu nại (Giữ đánh giá)
                    </button>
                    <button type="button" id="btnApproveReport" class="btn btn-success px-4 fw-semibold">
                        <i class="fa-solid fa-check me-1"></i>Chấp thuận & Ẩn đánh giá
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
