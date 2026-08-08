{{--
    Partial: Modal Tu choi / Khoa Seller (yeu cau nhap ly do)
    Mo bang JS: openActionModal('reject' | 'block')
    Xac nhan bang: confirmActionBtn (xu ly trong sellers.js)
--}}
<div class="modal fade" id="sellerActionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 460px;">
        <div class="modal-content">

            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="actionModalTitle">Xác nhận hành động</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p class="text-muted mb-3" style="font-size: 14px;" id="actionModalDesc"></p>

                <label class="form-label fw-semibold" style="font-size: 13px;" for="actionNote">
                    Lý do <span class="text-danger">*</span>
                </label>
                <textarea id="actionNote"
                          class="form-control"
                          rows="4"
                          style="font-size: 14px; resize: none;"
                          placeholder="Nhập lý do cụ thể (tối thiểu 10 ký tự)..."></textarea>
                <div id="actionNoteError" class="text-danger mt-1 d-none" style="font-size: 12px;">
                    Vui lòng nhập lý do (tối thiểu 10 ký tự).
                </div>
            </div>

            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-sm" id="confirmActionBtn">Xác nhận</button>
            </div>

        </div>
    </div>
</div>
