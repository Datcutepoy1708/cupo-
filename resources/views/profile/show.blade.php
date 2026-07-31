@extends('layouts.client.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            {{-- ===== SIDEBAR ===== --}}
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="profile-section">
                    <img src="{{ asset('images/avatar-default.png') }}" alt="Avatar" class="profile-img" id="sidebar-avatar">
                    <div class="profile-name" id="username-display">Nguyễn Văn A</div>
                </div>

                <nav class="nav flex-column nav-pills px-3 mt-3" role="tablist">
                    <a class="nav-link active" data-bs-toggle="pill" href="#personal" role="tab">
                        <i class="fa-solid fa-id-card"></i> Thông tin cá nhân
                    </a>
                    <a class="nav-link" data-bs-toggle="pill" href="#changePassword" role="tab">
                        <i class="fa-solid fa-key"></i> Đổi mật khẩu
                    </a>

                    {{-- Dropdown lịch sử --}}
                    <a class="nav-link dropdown-toggle" data-bs-toggle="collapse" href="#historyDropdown" role="button"
                        aria-expanded="false">
                        <span><i class="fa-solid fa-clock-rotate-left"></i> Lịch sử của tôi</span>
                    </a>
                    <div class="collapse dropdown-menu-custom" id="historyDropdown">
                        <a class="dropdown-item-custom" data-bs-toggle="pill" href="#historyOrder" role="tab">
                            <i class="fa-solid fa-box"></i> Đơn hàng
                        </a>
                        <a class="dropdown-item-custom" data-bs-toggle="pill" href="#historyWishlist" role="tab">
                            <i class="fa-solid fa-heart"></i> Yêu thích
                        </a>
                    </div>

                    <a class="nav-link" data-bs-toggle="pill" href="#sellerChannel" role="tab">
                        <i class="fa-solid fa-shop"></i> Kênh người bán
                    </a>
                </nav>
            </div>

            {{-- ===== MAIN CONTENT ===== --}}
            <div class="col-md-9 col-lg-10">
                <div class="content-area">
                    <div class="tab-content">

                        {{-- ===== TAB: THÔNG TIN CÁ NHÂN ===== --}}
                        <div class="tab-pane fade show active" id="personal" role="tabpanel">
                            <div class="content-card">
                                <h2 class="content-title">Thông tin cá nhân</h2>

                                <form method="post" action="#" enctype="multipart/form-data" id="profile-form">
                                    {{-- Upload avatar --}}
                                    <div class="avatar-upload-section">
                                        <div class="avatar-preview-container"
                                            onclick="document.getElementById('avatar-input').click()">
                                            <img src="{{ asset('images/avatar-default.png') }}" alt="Avatar"
                                                class="avatar-preview" id="avatar-preview">
                                            <div class="camera-overlay">
                                                <i class="fa-solid fa-camera"></i>
                                            </div>
                                            <input type="file" id="avatar-input" name="avatar"
                                                accept="image/jpeg,image/png,image/jpg" style="display:none;">
                                        </div>
                                        <div class="avatar-upload-controls">
                                            <h5><i class="fa-solid fa-circle-user me-2"></i>Ảnh đại diện</h5>
                                            <p class="text-muted mb-2">Nhấn vào ảnh để thay đổi</p>
                                            <p class="text-muted small mb-3">
                                                <i class="fa-solid fa-circle-info me-1"></i>Định dạng: JPG, PNG | Tối đa:
                                                5MB
                                            </p>
                                            <span class="file-name-display" id="file-name">
                                                <i class="fa-solid fa-file-image me-1"></i>Chưa chọn file
                                            </span>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="full_name"
                                                value="Nguyễn Văn A">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Ngày sinh</label>
                                            <input type="date" class="form-control" name="birth_day" value="2000-01-01">
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Địa chỉ</label>
                                            <input type="text" class="form-control" name="address"
                                                value="123 Đường Lê Lợi, Q.1, TP.HCM">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Giới tính</label>
                                            <select class="form-select" name="gender">
                                                <option value="male" selected>Nam</option>
                                                <option value="female">Nữ</option>
                                                <option value="other">Khác</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label class="form-label">Email <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" name="email"
                                                value="nguyenvana@gmail.com">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Số điện thoại</label>
                                            <input type="tel" class="form-control" name="phone"
                                                value="0987654321">
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-save"><i
                                                class="fa-solid fa-floppy-disk me-2"></i>Cập nhật</button>
                                        <button type="reset" class="btn btn-cancel"><i
                                                class="fa-solid fa-xmark me-2"></i>Hủy</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- ===== TAB: ĐỔI MẬT KHẨU ===== --}}
                        <div class="tab-pane fade" id="changePassword" role="tabpanel">
                            <div class="content-card">
                                <h2 class="content-title">Đổi mật khẩu</h2>

                                <form class="change-pass" method="post" action="#">
                                    <div class="row mb-4">
                                        <div class="col-md-6 position-relative">
                                            <label class="form-label">Mật khẩu hiện tại <span
                                                    class="text-danger">*</span></label>
                                            <input type="password" class="form-control password-field"
                                                name="current_password" placeholder="Nhập mật khẩu cũ">
                                            <i class="fa-solid fa-eye toggle-password"></i>
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-md-6 position-relative">
                                            <label class="form-label">Mật khẩu mới <span
                                                    class="text-danger">*</span></label>
                                            <input type="password" class="form-control password-field"
                                                name="new_password" placeholder="Nhập mật khẩu mới">
                                            <i class="fa-solid fa-eye toggle-password"></i>
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-md-6 position-relative">
                                            <label class="form-label">Xác nhận mật khẩu mới <span
                                                    class="text-danger">*</span></label>
                                            <input type="password" class="form-control password-field"
                                                name="confirm_password" placeholder="Nhập lại mật khẩu mới">
                                            <i class="fa-solid fa-eye toggle-password"></i>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-save"><i
                                                class="fa-solid fa-key me-2"></i>Lưu thay đổi</button>
                                        <button type="reset" class="btn btn-cancel"><i
                                                class="fa-solid fa-xmark me-2"></i>Hủy</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- ===== TAB: LỊCH SỬ ĐƠN HÀNG ===== --}}
                        <div class="tab-pane fade" id="historyOrder" role="tabpanel">
                            <div class="content-card">
                                <h2 class="content-title">Đơn hàng của tôi</h2>

                                @php
                                    $demoOrders = [
                                        [
                                            'id' => 'DH0231',
                                            'name' => 'Tai nghe Bluetooth XZ200 (+2 SP khác)',
                                            'date' => '28/07/2026',
                                            'total' => '1.250.000',
                                            'status' => 'confirmed',
                                        ],
                                        [
                                            'id' => 'DH0228',
                                            'name' => 'Đồng hồ thông minh Fit3',
                                            'date' => '25/07/2026',
                                            'total' => '1.590.000',
                                            'status' => 'unpaid',
                                        ],
                                        [
                                            'id' => 'DH0225',
                                            'name' => 'Áo thun nam form rộng',
                                            'date' => '20/07/2026',
                                            'total' => '259.000',
                                            'status' => 'completed',
                                        ],
                                        [
                                            'id' => 'DH0219',
                                            'name' => 'Nồi chiên không dầu 5L',
                                            'date' => '10/07/2026',
                                            'total' => '890.000',
                                            'status' => 'processing',
                                        ],
                                        [
                                            'id' => 'DH0210',
                                            'name' => 'Giày sneaker unisex',
                                            'date' => '05/07/2026',
                                            'total' => '650.000',
                                            'status' => 'returned',
                                        ],
                                        [
                                            'id' => 'DH0204',
                                            'name' => 'Balo laptop chống nước',
                                            'date' => '02/07/2026',
                                            'total' => '420.000',
                                            'status' => 'cancelled',
                                        ],
                                    ];

                                    $statusMeta = [
                                        'unpaid' => ['label' => 'Chờ thanh toán', 'badge' => 'bg-warning text-dark'],
                                        'processing' => ['label' => 'Đang xử lý', 'badge' => 'bg-info text-dark'],
                                        'confirmed' => ['label' => 'Đang giao', 'badge' => 'bg-success'],
                                        'completed' => ['label' => 'Hoàn thành', 'badge' => 'bg-primary'],
                                        'cancelled' => ['label' => 'Đã hủy', 'badge' => 'bg-danger'],
                                        'returned' => ['label' => 'Trả hàng/Hoàn tiền', 'badge' => 'bg-secondary'],
                                    ];

                                    $orderFilters = [
                                        'all' => 'Tất cả',
                                        'unpaid' => 'Chờ thanh toán',
                                        'processing' => 'Đang xử lý',
                                        'confirmed' => 'Đang giao',
                                        'completed' => 'Hoàn thành',
                                        'cancelled' => 'Đã hủy',
                                        'returned' => 'Trả hàng/Hoàn tiền',
                                    ];
                                @endphp

                                {{-- Tabs trạng thái kiểu Shopee --}}
                                <ul class="nav order-status-tabs mb-4" role="tablist">
                                    @foreach ($orderFilters as $key => $label)
                                        @php
                                            $count =
                                                $key === 'all'
                                                    ? count($demoOrders)
                                                    : count(array_filter($demoOrders, fn($o) => $o['status'] === $key));
                                        @endphp
                                        <li class="nav-item">
                                            <a class="nav-link {{ $key === 'all' ? 'active' : '' }}" data-bs-toggle="pill"
                                                href="#orderStatus-{{ $key }}" role="tab">
                                                {{ $label }}
                                                @if ($count > 0)
                                                    <span class="status-count">{{ $count }}</span>
                                                @endif
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>

                                <div class="tab-content">
                                    @foreach ($orderFilters as $key => $label)
                                        @php
                                            $filteredOrders =
                                                $key === 'all'
                                                    ? $demoOrders
                                                    : array_filter($demoOrders, fn($o) => $o['status'] === $key);
                                        @endphp
                                        <div class="tab-pane fade {{ $key === 'all' ? 'show active' : '' }}"
                                            id="orderStatus-{{ $key }}" role="tabpanel">

                                            @if (count($filteredOrders) > 0)
                                                @foreach ($filteredOrders as $order)
                                                    <div class="order-row">
                                                        <div class="order-row-top">
                                                            <span class="order-id">Đơn hàng #{{ $order['id'] }}</span>
                                                            <span
                                                                class="badge {{ $statusMeta[$order['status']]['badge'] }}">
                                                                {{ $statusMeta[$order['status']]['label'] }}
                                                            </span>
                                                        </div>
                                                        <div class="order-row-body">
                                                            <div class="order-info">
                                                                <p class="mb-1">{{ $order['name'] }}</p>
                                                                <p class="text-muted small mb-0">Ngày đặt:
                                                                    {{ $order['date'] }}</p>
                                                            </div>
                                                            <div class="order-total text-end">
                                                                <p class="text-muted small mb-1">Tổng tiền</p>
                                                                <p class="fw-bold mb-0"
                                                                    style="color: var(--primary-red);">
                                                                    {{ $order['total'] }}₫</p>
                                                            </div>
                                                        </div>
                                                        <div class="order-row-actions">
                                                            <button class="btn btn-sm btn-outline-secondary"
                                                                data-bs-toggle="modal" data-bs-target="#orderDetailModal">
                                                                Xem chi tiết
                                                            </button>
                                                            @if ($order['status'] === 'unpaid')
                                                                <button class="btn btn-sm btn-save">Thanh toán
                                                                    ngay</button>
                                                            @elseif($order['status'] === 'confirmed')
                                                                <button class="btn btn-sm btn-outline-danger">Đã nhận được
                                                                    hàng</button>
                                                            @elseif($order['status'] === 'completed')
                                                                <button class="btn btn-sm btn-outline-secondary">Mua
                                                                    lại</button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="empty-state">
                                                    <i class="fa-solid fa-inbox"></i>
                                                    <p class="mb-0">Không có đơn hàng nào ở trạng thái này</p>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- ===== TAB: SẢN PHẨM YÊU THÍCH ===== --}}
                        <div class="tab-pane fade" id="historyWishlist" role="tabpanel">
                            <div class="content-card">
                                <h2 class="content-title">Sản phẩm yêu thích</h2>

                                <div class="row g-3">
                                    @for ($i = 0; $i < 4; $i++)
                                        <div class="col-6 col-md-3">
                                            <div class="card h-100 border-0 shadow-sm wishlist-card">
                                                <button class="btn-remove-wishlist"><i
                                                        class="fa-solid fa-heart"></i></button>
                                                <img src="{{ asset('images/products/sample-' . $i . '.jpg') }}"
                                                    class="card-img-top" alt="Sản phẩm">
                                                <div class="card-body">
                                                    <p class="small mb-1 text-truncate">Sản phẩm yêu thích
                                                        {{ $i + 1 }}</p>
                                                    <p class="fw-bold mb-0" style="color: var(--primary-red);">
                                                        {{ number_format(199000 + $i * 60000) }}₫</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        </div>

                        {{-- ===== TAB: KÊNH NGƯỜI BÁN ===== --}}
                        <div class="tab-pane fade" id="sellerChannel" role="tabpanel">
                            <div class="content-card">

                                {{-- Trạng thái: CHƯA đăng ký — hiển thị form đăng ký --}}
                                <div class="seller-intro text-center mb-4">
                                    <i class="fa-solid fa-shop seller-intro-icon"></i>
                                    <h2 class="content-title border-0 mb-2" style="display:block;">Trở thành người bán
                                        trên Cupo</h2>
                                    <p class="text-muted mb-0">Mở gian hàng miễn phí, tiếp cận hàng triệu khách hàng và bắt
                                        đầu kinh doanh ngay hôm nay.</p>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <div class="seller-benefit">
                                            <i class="fa-solid fa-sack-dollar"></i>
                                            <h6>Miễn phí đăng ký</h6>
                                            <p class="small text-muted mb-0">Không tốn phí mở gian hàng, chỉ thu phí trên
                                                mỗi đơn thành công.</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="seller-benefit">
                                            <i class="fa-solid fa-users"></i>
                                            <h6>Tiếp cận khách hàng</h6>
                                            <p class="small text-muted mb-0">Hàng triệu người mua đang hoạt động mỗi ngày
                                                trên Cupo.</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="seller-benefit">
                                            <i class="fa-solid fa-chart-line"></i>
                                            <h6>Công cụ quản lý</h6>
                                            <p class="small text-muted mb-0">Quản lý đơn hàng, tồn kho, khuyến mãi ngay
                                                trên Kênh người bán.</p>
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-4">

                                <h5 class="fw-bold mb-3">Thông tin đăng ký gian hàng</h5>

                                <form method="post" action="#">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Tên gian hàng <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="shop_name"
                                                placeholder="VD: Cupo Store - Điện tử chính hãng">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Ngành hàng kinh doanh <span
                                                    class="text-danger">*</span></label>
                                            <select class="form-select" name="shop_category">
                                                <option value="">Chọn ngành hàng</option>
                                                <option>Điện tử - Công nghệ</option>
                                                <option>Thời trang</option>
                                                <option>Đồ gia dụng</option>
                                                <option>Làm đẹp - Sức khỏe</option>
                                                <option>Mẹ & bé</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Loại hình kinh doanh <span
                                                    class="text-danger">*</span></label>
                                            <div class="d-flex gap-4 mt-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="business_type"
                                                        id="typePersonal" checked>
                                                    <label class="form-check-label" for="typePersonal">Cá nhân</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="business_type"
                                                        id="typeCompany">
                                                    <label class="form-check-label" for="typeCompany">Doanh nghiệp</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Số CCCD / Mã số thuế <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="tax_id"
                                                placeholder="Nhập số CCCD hoặc MST">
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Số điện thoại liên hệ <span
                                                    class="text-danger">*</span></label>
                                            <input type="tel" class="form-control" name="shop_phone"
                                                value="0987654321">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Email liên hệ <span
                                                    class="text-danger">*</span></label>
                                            <input type="email" class="form-control" name="shop_email"
                                                value="nguyenvana@gmail.com">
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-12">
                                            <label class="form-label">Địa chỉ lấy hàng <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="pickup_address"
                                                placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành">
                                        </div>
                                    </div>

                                    <div class="form-check mb-4">
                                        <input class="form-check-input" type="checkbox" id="agreeTerms">
                                        <label class="form-check-label small" for="agreeTerms">
                                            Tôi đã đọc và đồng ý với <a href="#"
                                                style="color: var(--primary-red);">Điều khoản người bán</a> của Cupo
                                        </label>
                                    </div>

                                    <button type="submit" class="btn btn-save px-4">
                                        <i class="fa-solid fa-store me-2"></i>Đăng ký bán hàng
                                    </button>
                                </form>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== MODAL: CHI TIẾT ĐƠN HÀNG ===== --}}
    <div class="modal fade modal-detail" id="orderDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-file-invoice me-2"></i>Chi tiết đơn hàng #DH0231</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <div class="mb-4">
                        <h6><i class="fa-solid fa-box me-2"></i>Sản phẩm trong đơn</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sản phẩm</th>
                                        <th class="text-center">SL</th>
                                        <th class="text-end">Đơn giá</th>
                                        <th class="text-end">Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Tai nghe Bluetooth XZ200</td>
                                        <td class="text-center">1</td>
                                        <td class="text-end">590.000₫</td>
                                        <td class="text-end">590.000₫</td>
                                    </tr>
                                    <tr>
                                        <td>Ốp lưng chống sốc</td>
                                        <td class="text-center">2</td>
                                        <td class="text-end">120.000₫</td>
                                        <td class="text-end">240.000₫</td>
                                    </tr>
                                    <tr>
                                        <td>Cáp sạc nhanh Type-C</td>
                                        <td class="text-center">2</td>
                                        <td class="text-end">210.000₫</td>
                                        <td class="text-end">420.000₫</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6><i class="fa-solid fa-location-dot me-2"></i>Thông tin giao hàng</h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="text-muted">Người nhận:</td>
                                <td class="fw-bold text-end">Nguyễn Văn A</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Số điện thoại:</td>
                                <td class="text-end">0987 654 321</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Địa chỉ:</td>
                                <td class="text-end">123 Đường Lê Lợi, Q.1, TP.HCM</td>
                            </tr>
                        </table>
                    </div>

                    <div class="mb-4">
                        <h6><i class="fa-solid fa-money-bill-wave me-2"></i>Chi tiết thanh toán</h6>
                        <table class="table table-sm">
                            <tr>
                                <td>Tạm tính:</td>
                                <td class="text-end fw-bold">1.250.000₫</td>
                            </tr>
                            <tr>
                                <td>Phí vận chuyển:</td>
                                <td class="text-end">0₫</td>
                            </tr>
                            <tr>
                                <td>Giảm giá:</td>
                                <td class="text-end text-danger">-30.000₫</td>
                            </tr>
                            <tr class="border-top">
                                <td><strong>Tổng cộng:</strong></td>
                                <td class="text-end fs-5 fw-bold">1.220.000₫</td>
                            </tr>
                        </table>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <p class="mb-2"><strong>Phương thức thanh toán:</strong> COD</p>
                        </div>
                        <div class="col-6">
                            <p class="mb-2"><strong>Trạng thái:</strong> <span class="badge bg-success">Đang giao</span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark me-2"></i>Đóng
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
@endpush
