<x-modal name="editAddressModal" title="Cập nhật địa chỉ">
    <form id="editAddressForm" method="post" action="#">
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="name" value="Nguyễn Văn A">
            </div>
            <div class="col-md-6">
                <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                <input type="tel" class="form-control" name="phone" value="0987654321">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Tỉnh/Thành phố <span class="text-danger">*</span></label>
                <select class="form-select" name="province">
                    <option selected>TP. Hồ Chí Minh</option>
                    <option>Hà Nội</option>
                    <option>Đà Nẵng</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Quận/Huyện <span class="text-danger">*</span></label>
                <select class="form-select" name="district">
                    <option selected>Quận 1</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Phường/Xã <span class="text-danger">*</span></label>
                <select class="form-select" name="ward">
                    <option selected>Phường Bến Nghé</option>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <label class="form-label">Địa chỉ cụ thể <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="detail_address" value="123 Đường Lê Lợi">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <label class="form-label">Loại địa chỉ</label>
                <div class="d-flex gap-4 mt-1">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="address_type" id="editAddrTypeHome"
                            checked>
                        <label class="form-check-label" for="editAddrTypeHome">Nhà riêng</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="address_type" id="editAddrTypeOffice">
                        <label class="form-check-label" for="editAddrTypeOffice">Văn phòng</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="editSetDefaultAddress" name="is_default" checked>
            <label class="form-check-label" for="editSetDefaultAddress">Đặt làm địa chỉ mặc định</label>
        </div>
    </form>

    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
        <button type="submit" form="editAddressForm" class="btn btn-danger">
            <i class="fa-solid fa-floppy-disk me-2"></i>Lưu thay đổi
        </button>
    </x-slot>
</x-modal>
