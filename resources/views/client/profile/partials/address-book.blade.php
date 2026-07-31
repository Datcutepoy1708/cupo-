<div class="tab-pane fade" id="addressBook" role="tabpanel">
    <div class="content-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="content-title mb-0">Sổ địa chỉ</h2>
            <button type="button" class="btn btn-save" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                <i class="fa-solid fa-plus me-2"></i>Thêm địa chỉ mới
            </button>
        </div>

        @php
            $demoAddresses = [
                [
                    'id' => 1,
                    'name' => 'Nguyễn Văn A',
                    'phone' => '0987 654 321',
                    'address' => '123 Đường Lê Lợi, Phường Bến Nghé, Quận 1, TP. Hồ Chí Minh',
                    'type' => 'Nhà riêng',
                    'default' => true,
                ],
                [
                    'id' => 2,
                    'name' => 'Nguyễn Văn A',
                    'phone' => '0912 345 678',
                    'address' => '45 Đường Nguyễn Huệ, Phường Bến Nghé, Quận 1, TP. Hồ Chí Minh',
                    'type' => 'Văn phòng',
                    'default' => false,
                ],
                [
                    'id' => 3,
                    'name' => 'Trần Thị B',
                    'phone' => '0977 111 222',
                    'address' => '78 Đường Trần Hưng Đạo, Phường 7, Quận 5, TP. Hồ Chí Minh',
                    'type' => 'Nhà riêng',
                    'default' => false,
                ],
            ];
        @endphp

        @foreach ($demoAddresses as $addr)
            <div class="address-row">
                <div class="address-row-body">
                    <div class="address-info">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="fw-bold">{{ $addr['name'] }}</span>
                            <span class="text-muted">|</span>
                            <span class="text-muted">{{ $addr['phone'] }}</span>
                            @if ($addr['default'])
                                <span class="badge address-default-badge">Mặc định</span>
                            @endif
                        </div>
                        <p class="text-muted mb-2">{{ $addr['address'] }}</p>
                        <span class="address-type-tag">{{ $addr['type'] }}</span>
                    </div>
                    <div class="address-actions">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                            data-bs-target="#editAddressModal">
                            <i class="fa-solid fa-pen"></i> Sửa
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger">
                            <i class="fa-solid fa-trash"></i> Xóa
                        </button>
                    </div>
                </div>
                @if (!$addr['default'])
                    <div class="address-row-footer">
                        <button type="button" class="btn btn-sm btn-link p-0">Đặt làm mặc
                            định</button>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
