{{--
    Modal: Nhập lý do từ chối yêu cầu rút tiền
--}}
<div class="modal fade" id="rejectWithdrawalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger">
                    <i class="fa-solid fa-ban me-2"></i>
                    Từ chối yêu cầu rút tiền
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">
                    Bạn đang từ chối yêu cầu rút tiền của gian hàng <strong id="modalRejectShopName" class="text-dark">—</strong>.
                    Số dư của Seller sẽ <strong>không bị trừ</strong>.
                </p>
                <div class="mb-3">
                    <label for="rejectAdminNote" class="form-label fw-semibold">
                        Lý do từ chối <span class="text-danger">*</span>
                    </label>
                    <textarea id="rejectAdminNote"
                              class="form-control"
                              rows="4"
                              placeholder="Ví dụ: Tên chủ tài khoản không khớp với hồ sơ KYC, nghi vấn giao dịch bất thường..."></textarea>
                    <div class="invalid-feedback d-none" id="rejectNoteError"></div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger btn-sm px-3" id="confirmRejectBtn">
                    <i class="fa-solid fa-ban me-1"></i>Xác nhận từ chối
                </button>
            </div>
        </div>
    </div>
</div>
