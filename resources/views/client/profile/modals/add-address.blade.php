<x-modal name="addAddressModal" title="Thêm địa chỉ mới">
    <form id="addAddressForm" method="post" action="#">
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="name" placeholder="Nhập họ và tên người nhận">
            </div>
            <div class="col-md-6">
                <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                <input type="tel" class="form-control" name="phone" placeholder="Nhập số điện thoại">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Tỉnh/Thành phố <span class="text-danger">*</span></label>
                <select class="form-select" name="province">
                    <option value="">Chọn Tỉnh/Thành</option>
                    <option>TP. Hồ Chí Minh</option>
                    <option>Hà Nội</option>
                    <option>Đà Nẵng</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Quận/Huyện <span class="text-danger">*</span></label>
                <select class="form-select" name="district">
                    <option value="">Chọn Quận/Huyện</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Phường/Xã <span class="text-danger">*</span></label>
                <select class="form-select" name="ward">
                    <option value="">Chọn Phường/Xã</option>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <label class="form-label">Địa chỉ cụ thể <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="detail_address" placeholder="Số nhà, tên đường...">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <label class="form-label">Loại địa chỉ</label>
                <div class="d-flex gap-4 mt-1">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="address_type" id="addrTypeHome" checked>
                        <label class="form-check-label" for="addrTypeHome">Nhà riêng</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="address_type" id="addrTypeOffice">
                        <label class="form-check-label" for="addrTypeOffice">Văn phòng</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="setDefaultAddress" name="is_default">
            <label class="form-check-label" for="setDefaultAddress">Đặt làm địa chỉ mặc định</label>
        </div>
    </form>

    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
        <button type="submit" form="addAddressForm" class="btn btn-danger">
            <i class="fa-solid fa-floppy-disk me-2"></i>Lưu địa chỉ
        </button>
    </x-slot>
</x-modal>
