<!-- Modal Tao / Sua Phiên Flash Sale -->
<div class="modal fade" id="flashSaleFormModal" tabindex="-1" aria-labelledby="flashSaleFormModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-header-title" id="flashSaleFormModalLabel">Tạo phiên Flash Sale mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="flashSaleForm" method="POST" action="{{ route('admin.flash-sales.store') }}">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="flash_sale_name" class="form-label font-weight-bold">Tên chương trình / Phiên Flash Sale <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="flash_sale_name" name="name" placeholder="VD: Flash Sale Giờ Vàng 12h-14h" required>
                        <div class="invalid-feedback" id="error-name"></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="flash_sale_starts_at" class="form-label font-weight-bold">Bắt đầu <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="flash_sale_starts_at" name="starts_at" required>
                            <div class="invalid-feedback" id="error-starts_at"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="flash_sale_ends_at" class="form-label font-weight-bold">Kết thúc <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="flash_sale_ends_at" name="ends_at" required>
                            <div class="invalid-feedback" id="error-ends_at"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="flash_sale_registration_deadline" class="form-label font-weight-bold">Hạn chót đăng ký (Seller)</label>
                        <input type="datetime-local" class="form-control" id="flash_sale_registration_deadline" name="registration_deadline">
                        <div class="form-text text-muted small">Để trống nếu không nhận đăng ký từ Seller. Phải trước giờ bắt đầu ít nhất 10 phút.</div>
                        <div class="invalid-feedback" id="error-registration_deadline"></div>
                    </div>

                    <div class="mb-3 form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="flash_sale_status" name="status" value="1" checked>
                        <label class="form-check-label font-weight-bold" for="flash_sale_status">Kích hoạt ngay</label>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveFlashSale">
                        <i class="fa-solid fa-save me-1"></i> Lưu thông tin
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
