{{--
    Modal: Xem nhanh hồ sơ + hành động (Block / Unblock) cho 1 khách hàng
    Kích hoạt bởi customers.js khi bấm nút "Xem chi tiết" trong bảng danh sách
--}}
<div class="modal fade" id="customerDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            {{-- Header --}}
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">
                    <i class="fa-solid fa-user me-2 text-primary"></i>
                    Hồ sơ khách hàng
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            {{-- Body --}}
            <div class="modal-body" id="customerDetailBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-danger" role="status"></div>
                    <p class="mt-2 text-muted">Đang tải thông tin...</p>
                </div>
            </div>

            {{-- Footer action buttons --}}
            <div class="modal-footer border-0 pt-0 gap-2" id="customerDetailFooter">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
            </div>

        </div>
    </div>
</div>

{{-- Modal xác nhận Khóa tài khoản --}}
<div class="modal fade" id="blockCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger">
                    <i class="fa-solid fa-ban me-2"></i>
                    Khóa tài khoản
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">
                    Bạn đang khóa tài khoản: <strong id="blockCustomerName"></strong><br>
                    Khách hàng sẽ không thể đăng nhập cho đến khi được mở khóa.
                </p>
                <div class="mb-3">
                    <label for="blockAdminNote" class="form-label fw-semibold">
                        Lý do khóa <span class="text-danger">*</span>
                    </label>
                    <textarea id="blockAdminNote"
                              class="form-control"
                              rows="3"
                              placeholder="Nhập lý do khóa tài khoản..."></textarea>
                    <div class="invalid-feedback" id="blockNoteError"></div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger btn-sm" id="confirmBlockBtn">
                    <i class="fa-solid fa-ban me-1"></i>Xác nhận khóa
                </button>
            </div>
        </div>
    </div>
</div>
