@extends('layouts.client.app')

@section('content')
    @php
        $shop = [
            'name' => 'Cupo Store - Điện tử chính hãng',
            'avatar' => 'https://picsum.photos/200/200',
            'banner' => 'https://picsum.photos/1600/400',
            'product_count' => 128,
            'rating' => 4.8,
            'review_count' => 356,
            'followers' => 2450,
            'pending_orders' => 7,
            'revenue_month' => 18500000,
        ];
    @endphp

    <div class="shop-page">

        {{-- ===== BANNER + AVATAR + THÔNG TIN SHOP (chế độ quản lý) ===== --}}
        <div class="container">
            <div class="shop-banner" style="background-image: url('{{ $shop['banner'] }}');">
                <button type="button" class="btn banner-edit-btn" data-bs-toggle="modal" data-bs-target="#editBannerModal">
                    <i class="fa-solid fa-camera"></i> Đổi ảnh bìa
                </button>
            </div>
            <div class="shop-header">
                <div class="shop-avatar-wrap">
                    <img src="{{ $shop['avatar'] }}" alt="{{ $shop['name'] }}" class="shop-avatar">
                    <button type="button" class="avatar-edit-btn" data-bs-toggle="modal" data-bs-target="#editAvatarModal">
                        <i class="fa-solid fa-camera"></i>
                    </button>
                </div>

                <div class="shop-info">
                    <div class="shop-info-top">
                        <div>
                            <h1 class="shop-name">{{ $shop['name'] }}</h1>
                            <div class="shop-stats">
                                <span><i class="fa-solid fa-box"></i> {{ $shop['product_count'] }} sản phẩm</span>
                                <span class="divider">|</span>
                                <span class="stars">
                                    <i class="fa-solid fa-star"></i> {{ number_format($shop['rating'], 1) }}
                                    <span class="text-muted">({{ $shop['review_count'] }} đánh giá)</span>
                                </span>
                                <span class="divider">|</span>
                                <span><i class="fa-solid fa-users"></i> {{ number_format($shop['followers']) }} người theo
                                    dõi</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== THANH NAV TAB (chức năng quản lý) ===== --}}
            <ul class="nav shop-nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="pill" href="#dashOverview" role="tab">Tổng quan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="pill" href="#dashProducts" role="tab">Sản phẩm</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="pill" href="#dashOrders" role="tab">
                        Đơn hàng
                        @if ($shop['pending_orders'] > 0)
                            <span class="tab-badge">{{ $shop['pending_orders'] }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="pill" href="#dashReviews" role="tab">Đánh giá</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="pill" href="#dashSettings" role="tab">Cài đặt</a>
                </li>
            </ul>

            {{-- ===== DASHBOARD NỘI DUNG ===== --}}
            <div class="tab-content shop-dashboard">

                {{-- TAB: TỔNG QUAN --}}
                <div class="tab-pane fade show active" id="dashOverview" role="tabpanel">
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-3">
                            <div class="dash-stat-card">
                                <i class="fa-solid fa-sack-dollar"></i>
                                <h4>{{ number_format($shop['revenue_month']) }}₫</h4>
                                <p>Doanh thu tháng này</p>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="dash-stat-card">
                                <i class="fa-solid fa-cart-shopping"></i>
                                <h4>{{ $shop['pending_orders'] }}</h4>
                                <p>Đơn cần xử lý</p>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="dash-stat-card">
                                <i class="fa-solid fa-box"></i>
                                <h4>{{ $shop['product_count'] }}</h4>
                                <p>Sản phẩm đang bán</p>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="dash-stat-card">
                                <i class="fa-solid fa-star"></i>
                                <h4>{{ number_format($shop['rating'], 1) }}/5</h4>
                                <p>Đánh giá trung bình</p>
                            </div>
                        </div>
                    </div>

                    <div class="dash-section-title">Đơn hàng gần đây</div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Khách hàng</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>#DH0231</td>
                                    <td>Nguyễn Văn A</td>
                                    <td>1.250.000₫</td>
                                    <td><span class="badge bg-warning text-dark">Chờ xác nhận</span></td>
                                </tr>
                                <tr>
                                    <td>#DH0228</td>
                                    <td>Trần Thị B</td>
                                    <td>590.000₫</td>
                                    <td><span class="badge bg-success">Đang giao</span></td>
                                </tr>
                                <tr>
                                    <td>#DH0225</td>
                                    <td>Lê Văn C</td>
                                    <td>259.000₫</td>
                                    <td><span class="badge bg-primary">Hoàn thành</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- TAB: SẢN PHẨM (quản lý) --}}
                <div class="tab-pane fade" id="dashProducts" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="dash-section-title mb-0">Sản phẩm của tôi</div>
                        <button type="button" class="btn btn-save" data-bs-toggle="modal"
                            data-bs-target="#addProductModal">
                            <i class="fa-solid fa-plus me-1"></i> Thêm sản phẩm
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Tên sản phẩm</th>
                                    <th>Giá</th>
                                    <th>Kho</th>
                                    <th>Trạng thái</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @for ($i = 0; $i < 5; $i++)
                                    <tr>
                                        <td><img src="https://picsum.photos/seed/{{ $i }}/60/60"
                                                class="dash-product-thumb"></td>
                                        <td>Sản phẩm mẫu số {{ $i + 1 }}</td>
                                        <td>{{ number_format(199000 + $i * 50000) }}₫</td>
                                        <td>{{ 50 - $i * 8 }}</td>
                                        <td>
                                            @if ($i === 2)
                                                <span class="badge bg-secondary">Hết hàng</span>
                                            @else
                                                <span class="badge bg-success">Đang bán</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-secondary"><i
                                                    class="fa-solid fa-pen"></i></button>
                                            <button class="btn btn-sm btn-outline-danger"><i
                                                    class="fa-solid fa-trash"></i></button>
                                        </td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- TAB: ĐƠN HÀNG --}}
                <div class="tab-pane fade" id="dashOrders" role="tabpanel">
                    <div class="dash-section-title">Đơn hàng cần xử lý</div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Khách hàng</th>
                                    <th>Sản phẩm</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>#DH0231</td>
                                    <td>Nguyễn Văn A</td>
                                    <td>Tai nghe Bluetooth (+2 SP)</td>
                                    <td>1.250.000₫</td>
                                    <td><span class="badge bg-warning text-dark">Chờ xác nhận</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-save">Xác nhận</button>
                                        <button class="btn btn-sm btn-outline-danger">Từ chối</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>#DH0230</td>
                                    <td>Trần Thị B</td>
                                    <td>Đồng hồ thông minh</td>
                                    <td>890.000₫</td>
                                    <td><span class="badge bg-warning text-dark">Chờ xác nhận</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-save">Xác nhận</button>
                                        <button class="btn btn-sm btn-outline-danger">Từ chối</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- TAB: ĐÁNH GIÁ --}}
                <div class="tab-pane fade" id="dashReviews" role="tabpanel">
                    <div class="dash-section-title">Đánh giá từ khách hàng</div>

                    @php
                        $demoReviews = [
                            [
                                'name' => 'Trần Thị B',
                                'rating' => 5,
                                'date' => '2 ngày trước',
                                'content' => 'Sản phẩm đúng như mô tả, giao hàng nhanh.',
                                'replied' => true,
                            ],
                            [
                                'name' => 'Lê Văn C',
                                'rating' => 3,
                                'date' => '1 tuần trước',
                                'content' => 'Giao hơi trễ so với dự kiến.',
                                'replied' => false,
                            ],
                        ];
                    @endphp

                    @foreach ($demoReviews as $review)
                        <div class="review-row">
                            <div class="review-row-top">
                                <span class="fw-bold">{{ $review['name'] }}</span>
                                <span class="text-muted small">{{ $review['date'] }}</span>
                            </div>
                            <div class="review-stars mb-2">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i
                                        class="fa-solid fa-star {{ $i <= $review['rating'] ? '' : 'text-muted opacity-25' }}"></i>
                                @endfor
                            </div>
                            <p class="mb-2 text-muted">{{ $review['content'] }}</p>

                            @if ($review['replied'])
                                <span class="badge bg-secondary">Đã phản hồi</span>
                            @else
                                <button class="btn btn-sm btn-outline-secondary">
                                    <i class="fa-solid fa-reply me-1"></i>Phản hồi
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- TAB: CÀI ĐẶT --}}
                <div class="tab-pane fade" id="dashSettings" role="tabpanel">
                    <div class="dash-section-title">Cài đặt cửa hàng</div>

                    <form method="post" action="#">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Tên gian hàng</label>
                                <input type="text" class="form-control" value="{{ $shop['name'] }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Ngành hàng</label>
                                <select class="form-select">
                                    <option selected>Điện tử - Công nghệ</option>
                                    <option>Thời trang</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label class="form-label">Mô tả cửa hàng</label>
                                <textarea class="form-control" rows="3">Chuyên cung cấp thiết bị điện tử chính hãng, giá tốt.</textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-save">
                            <i class="fa-solid fa-floppy-disk me-2"></i>Lưu thay đổi
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    {{-- ===== MODAL: THÊM SẢN PHẨM ===== --}}
    <x-modal name="addProductModal" title="Thêm sản phẩm mới" max-width="lg">
        <form id="addProductForm" method="post" action="#">
            <div class="row mb-3">
                <div class="col-md-12">
                    <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="product_name" placeholder="Nhập tên sản phẩm">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Giá bán <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" name="price" placeholder="VNĐ">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Số lượng kho <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" name="stock" placeholder="Số lượng">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-12">
                    <label class="form-label">Mô tả sản phẩm</label>
                    <textarea class="form-control" name="description" rows="3"></textarea>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-12">
                    <label class="form-label">Ảnh sản phẩm</label>
                    <input type="file" class="form-control" name="images" multiple>
                </div>
            </div>
        </form>
        <x-slot name="footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
            <button type="submit" form="addProductForm" class="btn btn-danger">
                <i class="fa-solid fa-floppy-disk me-2"></i>Lưu sản phẩm
            </button>
        </x-slot>
    </x-modal>

    {{-- ===== MODAL: SỬA THÔNG TIN CỬA HÀNG ===== --}}
    <x-modal name="editShopModal" title="Chỉnh sửa thông tin cửa hàng">
        <form id="editShopForm" method="post" action="#">
            <div class="row mb-3">
                <div class="col-md-12">
                    <label class="form-label">Tên gian hàng</label>
                    <input type="text" class="form-control" value="{{ $shop['name'] }}">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-12">
                    <label class="form-label">Mô tả cửa hàng</label>
                    <textarea class="form-control" rows="3">Chuyên cung cấp thiết bị điện tử chính hãng, giá tốt.</textarea>
                </div>
            </div>
        </form>
        <x-slot name="footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
            <button type="submit" form="editShopForm" class="btn btn-danger">Lưu thay đổi</button>
        </x-slot>
    </x-modal>

    {{-- ===== MODAL: ĐỔI AVATAR / BANNER ===== --}}
    <x-modal name="editAvatarModal" title="Đổi ảnh đại diện cửa hàng" max-width="sm">
        <form id="editAvatarForm" method="post" action="#" enctype="multipart/form-data">
            <input type="file" class="form-control" name="avatar" accept="image/*">
        </form>
        <x-slot name="footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
            <button type="submit" form="editAvatarForm" class="btn btn-danger">Cập nhật</button>
        </x-slot>
    </x-modal>

    <x-modal name="editBannerModal" title="Đổi ảnh bìa cửa hàng">
        <form id="editBannerForm" method="post" action="#" enctype="multipart/form-data">
            <input type="file" class="form-control" name="banner" accept="image/*">
        </form>
        <x-slot name="footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
            <button type="submit" form="editBannerForm" class="btn btn-danger">Cập nhật</button>
        </x-slot>
    </x-modal>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/shop.css') }}">
@endpush
