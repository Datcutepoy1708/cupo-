<x-modal name="addAddressModal" title="Thêm địa chỉ mới">
    <form id="addAddressForm" method="post" action="{{ route('addresses.store') }}">
        @csrf
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Họ và tên người nhận <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="recipient_name" placeholder="Nhập họ và tên người nhận"
                    required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                <input type="tel" class="form-control" name="recipient_phone" placeholder="Nhập số điện thoại" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Tỉnh/Thành phố <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="province" placeholder="Tỉnh/Thành phố" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Quận/Huyện <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="district" placeholder="Quận/Huyện" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Phường/Xã <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="ward" placeholder="Phường/Xã" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <label class="form-label">Địa chỉ cụ thể <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="address_detail" placeholder="Số nhà, tên đường..."
                    required>
            </div>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="setDefaultAddress" name="is_default" value="1">
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