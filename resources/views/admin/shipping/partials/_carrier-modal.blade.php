<div class="modal fade" id="editCarrierModal" tabindex="-1" aria-labelledby="editCarrierModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="editCarrierModalLabel">
                    <i class="fa-solid fa-truck-fast text-primary me-2"></i>Cấu hình: <span id="carrierModalName" class="text-primary"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editCarrierForm">
                <input type="hidden" id="carrierModalId">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Cước phí giao hàng cơ bản (VNĐ) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" id="carrierModalFee" class="form-control" min="0" step="1000" required>
                            <span class="input-group-text">₫</span>
                        </div>
                        <div class="form-text">Mức cước tiêu chuẩn áp dụng cho các đơn hàng mặc định của sàn.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Thời gian giao hàng dự kiến <span class="text-danger">*</span></label>
                        <input type="text" id="carrierModalDays" class="form-control" placeholder="VD: 1 - 3 ngày" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Hotline hỗ trợ</label>
                        <input type="text" id="carrierModalHotline" class="form-control" placeholder="VD: 19001221">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mô tả / Đặc điểm dịch vụ</label>
                        <textarea id="carrierModalDesc" class="form-control" rows="2" placeholder="Ghi chú về dịch vụ của đối tác..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fa-solid fa-floppy-disk me-1"></i>Lưu thay đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
