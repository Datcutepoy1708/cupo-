@extends('layouts.client.app')

@section('content')
    <div class="shop-page">
        <div class="container">

            @if ($shop->status === 'approved')
                {{-- ===== BANNER + AVATAR + THÔNG TIN SHOP (chế độ quản lý) ===== --}}
                <div class="shop-banner" style="background-image: url('{{ $shop->banner }}');">
                    <button type="button" class="btn banner-edit-btn" data-bs-toggle="modal" data-bs-target="#editBannerModal">
                        <i class="fa-solid fa-camera"></i> Đổi ảnh bìa
                    </button>
                </div>
                <div class="shop-header">
                    <div class="shop-avatar-wrap">
                        <img src="{{ $shop->logo }}" alt="{{ $shop->shop_name }}" class="shop-avatar">
                        <button type="button" class="avatar-edit-btn" data-bs-toggle="modal"
                            data-bs-target="#editAvatarModal">
                            <i class="fa-solid fa-camera"></i>
                        </button>
                    </div>

                    <div class="shop-info">
                        <div class="shop-info-top">
                            <div>
                                <h1 class="shop-name">{{ $shop->shop_name }}</h1>
                                <div class="shop-stats">
                                    <span><i class="fa-solid fa-box"></i> {{ $shop->product_count }} sản phẩm</span>
                                    <span class="divider">|</span>
                                    <span class="stars">
                                        <i class="fa-solid fa-star"></i> {{ number_format($shop->rating, 1) }}
                                        <span class="text-muted">({{ $shop->review_count }} đánh giá)</span>
                                    </span>
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
                            @if ($shop->pending_orders > 0)
                                <span class="tab-badge">{{ $shop->pending_orders }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="pill" href="#dashReviews" role="tab">Đánh giá</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="pill" href="#dashInfo" role="tab">Thông tin</a>
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
                                    <h4>{{ number_format($shop->revenue_month) }}₫</h4>
                                    <p>Doanh thu tháng này</p>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="dash-stat-card">
                                    <i class="fa-solid fa-cart-shopping"></i>
                                    <h4>{{ $shop->pending_orders }}</h4>
                                    <p>Đơn cần xử lý</p>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="dash-stat-card">
                                    <i class="fa-solid fa-box"></i>
                                    <h4>{{ $shop->product_count }}</h4>
                                    <p>Sản phẩm đang bán</p>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="dash-stat-card">
                                    <i class="fa-solid fa-star"></i>
                                    <h4>{{ number_format($shop->rating, 1) }}/5</h4>
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
                                    @forelse ($shop->recentOrders ?? [] as $sellerOrder)
                                        <tr>
                                            <td>#{{ $sellerOrder->order->order_number ?? $sellerOrder->order_id }}</td>
                                            <td>{{ $sellerOrder->order->user->name ?? 'Khách vãng lai' }}</td>
                                            <td>{{ number_format($sellerOrder->grand_total) }}₫</td>
                                            <td>
                                                @switch($sellerOrder->status)
                                                    @case('pending')
                                                        <span class="badge bg-warning text-dark">Chờ xác nhận</span>
                                                    @break

                                                    @case('shipping')
                                                        <span class="badge bg-success">Đang giao</span>
                                                    @break

                                                    @case('completed')
                                                        <span class="badge bg-primary">Hoàn thành</span>
                                                    @break

                                                    @default
                                                        <span class="badge bg-secondary">{{ $sellerOrder->status }}</span>
                                                @endswitch
                                            </td>
                                        </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted">Chưa có đơn hàng nào</td>
                                            </tr>
                                        @endforelse
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
                                        @forelse ($shop->products ?? [] as $product)
                                            <tr>
                                                <td><img src="{{ $product->thumbnail }}" class="dash-product-thumb"></td>
                                                <td>{{ $product->name }}</td>
                                                <td>{{ number_format($product->price) }}₫</td>
                                                <td>{{ $product->stock }}</td>
                                                <td>
                                                    @if ($product->stock <= 0)
                                                        <span class="badge bg-secondary">Hết hàng</span>
                                                    @else
                                                        <span class="badge bg-success">Đang bán</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-secondary"
                                                        data-id="{{ $product->id }}"><i class="fa-solid fa-pen"></i></button>
                                                    <button class="btn btn-sm btn-outline-danger"
                                                        data-id="{{ $product->id }}"><i
                                                            class="fa-solid fa-trash"></i></button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">Chưa có sản phẩm nào</td>
                                            </tr>
                                        @endforelse
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
                                        @forelse ($shop->pendingOrdersList ?? [] as $sellerOrder)
                                            <tr>
                                                <td>#{{ $sellerOrder->order->order_number ?? $sellerOrder->order_id }}</td>
                                                <td>{{ $sellerOrder->order->user->name ?? 'Khách vãng lai' }}</td>
                                                <td>
                                                    @php $itemNames = $sellerOrder->items->pluck('product_name'); @endphp
                                                    @if ($itemNames->isNotEmpty())
                                                        {{ $itemNames->first() }}
                                                        @if ($itemNames->count() > 1)
                                                            (+{{ $itemNames->count() - 1 }} SP)
                                                        @endif
                                                    @endif
                                                </td>
                                                <td>{{ number_format($sellerOrder->grand_total) }}₫</td>
                                                <td><span class="badge bg-warning text-dark">Chờ xác nhận</span></td>
                                                <td>
                                                    <form method="post"
                                                        action="{{ route('seller.orders.update-status', $sellerOrder) }}"
                                                        class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="confirmed">
                                                        <button type="submit" class="btn btn-sm btn-save">Xác nhận</button>
                                                    </form>
                                                    <form method="post"
                                                        action="{{ route('seller.orders.update-status', $sellerOrder) }}"
                                                        class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="rejected">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">Từ
                                                            chối</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">Không có đơn hàng cần xử lý
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- TAB: ĐÁNH GIÁ --}}
                        <div class="tab-pane fade" id="dashReviews" role="tabpanel">
                            <div class="dash-section-title">Đánh giá từ khách hàng</div>

                            @forelse ($shop->reviews ?? [] as $review)
                                <div class="review-row">
                                    <div class="review-row-top">
                                        <span class="fw-bold">{{ $review->customer_name }}</span>
                                        <span class="text-muted small">{{ $review->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="review-stars mb-2">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <i
                                                class="fa-solid fa-star {{ $i <= $review->rating ? '' : 'text-muted opacity-25' }}"></i>
                                        @endfor
                                    </div>
                                    <p class="mb-2 text-muted">{{ $review->content }}</p>

                                    @if ($review->reply)
                                        <span class="badge bg-secondary">Đã phản hồi</span>
                                    @else
                                        <button class="btn btn-sm btn-outline-secondary" data-id="{{ $review->id }}">
                                            <i class="fa-solid fa-reply me-1"></i>Phản hồi
                                        </button>
                                    @endif
                                </div>
                            @empty
                                <p class="text-muted">Chưa có đánh giá nào</p>
                            @endforelse
                        </div>

                        {{-- TAB: THÔNG TIN --}}
                        <div class="tab-pane fade" id="dashInfo" role="tabpanel">

                            {{-- Trạng thái duyệt --}}
                            <div class="dash-section-title">Trạng thái gian hàng</div>
                            <div class="mb-4">
                                @switch($shop->status)
                                    @case('approved')
                                        <span class="badge bg-success">Đã duyệt</span>
                                    @break

                                    @case('pending')
                                        <span class="badge bg-warning text-dark">Chờ duyệt</span>
                                    @break

                                    @case('rejected')
                                        <span class="badge bg-danger">Bị từ chối</span>
                                    @break

                                    @case('suspended')
                                        <span class="badge bg-secondary">Bị khóa</span>
                                    @break

                                    @default
                                        <span class="badge bg-secondary">{{ $shop->status }}</span>
                                @endswitch

                                @if ($shop->admin_note)
                                    <p class="text-muted mt-2 mb-0">Ghi chú từ quản trị viên: {{ $shop->admin_note }}</p>
                                @endif
                            </div>

                            {{-- Thông tin tài chính (chỉ xem) --}}
                            <div class="dash-section-title">Thông tin tài chính</div>
                            <div class="row g-3 mb-4">
                                <div class="col-6 col-md-4">
                                    <div class="dash-stat-card">
                                        <i class="fa-solid fa-wallet"></i>
                                        <h4>{{ number_format($shop->balance) }}₫</h4>
                                        <p>Số dư hiện tại</p>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <div class="dash-stat-card">
                                        <i class="fa-solid fa-percent"></i>
                                        <h4>{{ number_format($shop->commission_rate, 2) }}%</h4>
                                        <p>Tỉ lệ hoa hồng sàn</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Thông tin gian hàng (chỉnh sửa được) --}}
                            <div class="dash-section-title">Thông tin gian hàng</div>
                            <form method="post" action="#" class="mb-5">
                                @csrf
                                @method('PUT')
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Tên gian hàng</label>
                                        <input type="text" class="form-control" name="shop_name"
                                            value="{{ $shop->shop_name }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Ngành hàng</label>
                                        <select class="form-select" name="business_type">
                                            <option value="electronics" @selected($shop->business_type === 'electronics')>Điện tử - Công nghệ
                                            </option>
                                            <option value="fashion" @selected($shop->business_type === 'fashion')>Thời trang</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Đường dẫn gian hàng (slug)</label>
                                        <input type="text" class="form-control" value="{{ $shop->slug }}" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Địa chỉ</label>
                                        <input type="text" class="form-control" name="address"
                                            value="{{ $shop->address }}">
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <div class="col-md-12">
                                        <label class="form-label">Mô tả cửa hàng</label>
                                        <textarea class="form-control" name="description" rows="3">{{ $shop->description }}</textarea>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-save">
                                    <i class="fa-solid fa-floppy-disk me-2"></i>Lưu thay đổi
                                </button>
                            </form>

                            {{-- Thông tin định danh (chỉ xem, dữ liệu nhạy cảm) --}}
                            <div class="dash-section-title">Thông tin định danh</div>
                            <table class="table shop-info-table mb-5">
                                <tbody>
                                    <tr>
                                        <td class="text-muted" style="width: 220px;">Số CCCD/CMND</td>
                                        <td>{{ $shop->national_id ? \Illuminate\Support\Str::mask($shop->national_id, '*', 3, -4) : 'Chưa cập nhật' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            {{-- Thông tin ngân hàng nhận tiền (chỉnh sửa được) --}}
                            <div class="dash-section-title">Thông tin ngân hàng nhận tiền</div>
                            <form method="post" action="#">
                                @csrf
                                @method('PUT')
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Ngân hàng</label>
                                        <input type="text" class="form-control" name="bank_name"
                                            value="{{ $shop->bank_name }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Số tài khoản</label>
                                        <input type="text" class="form-control" name="bank_account"
                                            value="{{ $shop->bank_account }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Chủ tài khoản</label>
                                        <input type="text" class="form-control" name="bank_owner"
                                            value="{{ $shop->bank_owner }}">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-save">
                                    <i class="fa-solid fa-floppy-disk me-2"></i>Lưu thay đổi
                                </button>
                            </form>
                        </div>

                    </div>

                    {{-- ===== MODAL: THÊM SẢN PHẨM ===== --}}
                    <x-modal name="addProductModal" title="Thêm sản phẩm mới" max-width="lg">
                        <form id="addProductForm" method="post" action="#">
                            @csrf
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="product_name"
                                        placeholder="Nhập tên sản phẩm">
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
                                    <input type="file" class="form-control" name="images[]" multiple>
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

                    {{-- ===== MODAL: ĐỔI AVATAR / BANNER ===== --}}
                    <x-modal name="editAvatarModal" title="Đổi ảnh đại diện cửa hàng" max-width="sm">
                        <form id="editAvatarForm" method="post" action="#" enctype="multipart/form-data">
                            @csrf
                            <input type="file" class="form-control" name="avatar" accept="image/*">
                        </form>
                        <x-slot name="footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" form="editAvatarForm" class="btn btn-danger">Cập nhật</button>
                        </x-slot>
                    </x-modal>

                    <x-modal name="editBannerModal" title="Đổi ảnh bìa cửa hàng">
                        <form id="editBannerForm" method="post" action="#" enctype="multipart/form-data">
                            @csrf
                            <input type="file" class="form-control" name="banner" accept="image/*">
                        </form>
                        <x-slot name="footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" form="editBannerForm" class="btn btn-danger">Cập nhật</button>
                        </x-slot>
                    </x-modal>
                @else
                    {{-- ===== SHOP CHƯA ĐƯỢC DUYỆT ===== --}}
                    <div class="shop-not-approved text-center py-5">
                        @switch($shop->status)
                            @case('pending')
                                <i class="fa-solid fa-hourglass-half fa-3x text-warning mb-3"></i>
                                <h3>Gian hàng đang chờ duyệt</h3>
                                <p class="text-muted">
                                    Hồ sơ đăng ký gian hàng <strong>{{ $shop->shop_name }}</strong> của bạn đang được xét duyệt.
                                    Vui lòng quay lại sau.
                                </p>
                            @break

                            @case('rejected')
                                <i class="fa-solid fa-circle-xmark fa-3x text-danger mb-3"></i>
                                <h3>Gian hàng bị từ chối</h3>
                                <p class="text-muted">
                                    Hồ sơ gian hàng của bạn không được duyệt.
                                </p>
                                @if ($shop->admin_note)
                                    <p class="text-danger">Lý do: {{ $shop->admin_note }}</p>
                                @endif
                            @break

                            @case('suspended')
                                <i class="fa-solid fa-ban fa-3x text-secondary mb-3"></i>
                                <h3>Gian hàng đã bị khóa</h3>
                                <p class="text-muted">
                                    Gian hàng của bạn hiện đang bị tạm khóa. Vui lòng liên hệ bộ phận hỗ trợ để biết thêm chi tiết.
                                </p>
                            @break

                            @default
                                <i class="fa-solid fa-triangle-exclamation fa-3x text-muted mb-3"></i>
                                <h3>Gian hàng chưa sẵn sàng</h3>
                                <p class="text-muted">Vui lòng liên hệ quản trị viên để biết thêm thông tin.</p>
                        @endswitch
                    </div>
                @endif

            </div>
        </div>
    @endsection
