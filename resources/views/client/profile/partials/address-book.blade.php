<div class="tab-pane fade {{ $activeTab === 'addressBook' ? 'show active' : '' }}" id="addressBook" role="tabpanel">
    <div class="content-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="content-title mb-0">Sổ địa chỉ</h2>
            <button type="button" class="btn btn-save" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                <i class="fa-solid fa-plus me-2"></i>Thêm địa chỉ mới
            </button>
        </div>

        @if (session('status') === 'address-created')
            <div class="alert alert-success">Thêm địa chỉ mới thành công!</div>
        @elseif (session('status') === 'address-updated')
            <div class="alert alert-success">Cập nhật địa chỉ thành công!</div>
        @elseif (session('status') === 'address-deleted')
            <div class="alert alert-success">Đã xóa địa chỉ!</div>
        @elseif (session('status') === 'address-default-updated')
            <div class="alert alert-success">Đã cập nhật địa chỉ mặc định!</div>
        @endif

        @forelse (auth()->user()->addresses as $addr)
            <div class="address-row">
                <div class="address-row-body">
                    <div class="address-info">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="fw-bold">{{ $addr->recipient_name }}</span>
                            <span class="text-muted">|</span>
                            <span class="text-muted">{{ $addr->recipient_phone }}</span>
                            @if ($addr->is_default)
                                <span class="badge address-default-badge">Mặc định</span>
                            @endif
                        </div>
                        <p class="text-muted mb-0">
                            {{ $addr->address_detail }}, {{ $addr->ward }}, {{ $addr->district }}, {{ $addr->province }}
                        </p>
                    </div>
                    <div class="address-actions">
                        <form action="{{ route('addresses.destroy', $addr) }}" method="POST"
                            onsubmit="return confirm('Bạn có chắc muốn xóa địa chỉ này?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="fa-solid fa-trash"></i> Xóa
                            </button>
                        </form>
                    </div>
                </div>
                @if (!$addr->is_default)
                    <div class="address-row-footer">
                        <form action="{{ route('addresses.set-default', $addr) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-link p-0">Đặt làm mặc định</button>
                        </form>
                    </div>
                @endif
            </div>
        @empty
            <div class="empty-state">
                <i class="fa-solid fa-location-dot"></i>
                <p>Bạn chưa có địa chỉ nào. Hãy thêm địa chỉ mới!</p>
            </div>
        @endforelse
    </div>
</div>