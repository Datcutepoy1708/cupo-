{{--
    Modal: Phản hồi yêu cầu hỗ trợ / Kháng nghị từ Seller
--}}
<div class="modal fade" id="ticketResponseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-primary">
                    <i class="fa-solid fa-reply-all me-2"></i>
                    Phản hồi yêu cầu của Seller
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="p-3 bg-light rounded-3 mb-3">
                    <div class="text-muted small">Kháng nghị từ Seller:</div>
                    <div class="fw-bold fs-6 text-dark" id="modalTicketSubject">—</div>
                </div>

                <div class="mb-3">
                    <label for="adminResponseText" class="form-label fw-semibold">
                        Nội dung phản hồi & Hướng xử lý <span class="text-danger">*</span>
                    </label>
                    <textarea id="adminResponseText"
                              class="form-control"
                              rows="5"
                              placeholder="Nhập giải trình, phương án hỗ trợ hoặc quyết định xử lý..."></textarea>
                    <div class="invalid-feedback d-none" id="responseNoteError"></div>
                </div>

                <div class="mb-2">
                    <label class="form-label fw-semibold">Cập nhật trạng thái:</label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="action_status" id="statusResolved" value="resolved" checked>
                            <label class="form-check-label text-success fw-semibold" for="statusResolved">
                                <i class="fa-solid fa-circle-check me-1"></i>Đã giải quyết (Resolved)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="action_status" id="statusClosed" value="closed">
                            <label class="form-check-label text-secondary fw-semibold" for="statusClosed">
                                <i class="fa-solid fa-lock me-1"></i>Đóng ticket (Closed)
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary btn-sm px-3" id="confirmTicketResponseBtn">
                    <i class="fa-solid fa-paper-plane me-1"></i>Gửi phản hồi cho Seller
                </button>
            </div>
        </div>
    </div>
</div>
