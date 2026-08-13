@extends('layouts.client.app')

@section('title', $shop->shop_name . ' - Cửa hàng chính hãng | Cupo')

@push('styles')
    <link rel="stylesheet" href="{{ asset('client/css/shop-show.css') }}">
    <link rel="stylesheet" href="{{ asset('client/css/vouchers.css') }}">
@endpush

@section('content')
<div class="shop-page-wrapper">
    <div class="container py-4">

        {{-- 1. Shop Header Card --}}
        <div class="shop-header-card shadow-sm mb-4">
            <div class="row align-items-center g-4">
                {{-- Cột trái: Avatar & Tên shop --}}
                <div class="col-lg-4 border-end-lg">
                    <div class="d-flex align-items-center gap-3">
                        <div class="shop-avatar-wrapper position-relative">
                            @php
                                $shopLogo = $shop->logo
                                    ? (\Illuminate\Support\Str::startsWith($shop->logo, ['http://', 'https://'])
                                        ? $shop->logo
                                        : asset('storage/' . ltrim($shop->logo, '/')))
                                    : 'https://ui-avatars.com/api/?name=' . urlencode($shop->shop_name) . '&background=c62828&color=fff&size=128&bold=true';
                            @endphp
                            <img src="{{ $shopLogo }}" alt="{{ $shop->shop_name }}" class="shop-avatar">
                            <span class="shop-official-badge" title="Gian hàng chính hãng"><i class="fa-solid fa-circle-check"></i></span>
                        </div>
                        <div class="shop-info flex-grow-1 overflow-hidden">
                            <h1 class="shop-title text-truncate mb-1" title="{{ $shop->shop_name }}">{{ $shop->shop_name }}</h1>
                            <div class="shop-meta-online text-success small mb-2">
                                <i class="fa-solid fa-circle fa-2xs me-1"></i> Online vừa xong
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-follow {{ $isFollowed ? 'btn-following' : 'btn-danger' }}"
                                        id="btnFollowShop"
                                        data-shop-id="{{ $shop->id }}"
                                        data-url="{{ route('shops.follow.toggle', $shop->id) }}">
                                    <i class="fa-solid {{ $isFollowed ? 'fa-check' : 'fa-plus' }} me-1"></i>
                                    <span>{{ $isFollowed ? 'Đang Theo Dõi' : 'Theo Dõi' }}</span>
                                </button>
                                <a href="{{ Route::has('chat.room') ? route('chat.room', ['seller_id' => $shop->user_id]) : '#' }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fa-regular fa-comment-dots me-1"></i> Chat Ngay
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Cột phải: Thống kê shop --}}
                <div class="col-lg-8">
                    <div class="row g-3 text-center text-md-start">
                        <div class="col-6 col-md-4">
                            <div class="shop-stat-item">
                                <div class="shop-stat-label"><i class="fa-solid fa-box-archive text-danger me-1"></i> Sản Phẩm:</div>
                                <div class="shop-stat-value">{{ number_format($totalProducts) }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="shop-stat-item">
                                <div class="shop-stat-label"><i class="fa-solid fa-users text-danger me-1"></i> Người Theo Dõi:</div>
                                <div class="shop-stat-value" id="followersCount">{{ number_format($followersCount) }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="shop-stat-item">
                                <div class="shop-stat-label"><i class="fa-solid fa-star text-warning me-1"></i> Đánh Giá:</div>
                                <div class="shop-stat-value">4.9 <span class="text-muted fw-normal fs-6">(99% tích cực)</span></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="shop-stat-item">
                                <div class="shop-stat-label"><i class="fa-solid fa-comments text-danger me-1"></i> Tỉ Lệ Phản Hồi:</div>
                                <div class="shop-stat-value">98% <span class="text-muted fw-normal fs-6">(Trong vài phút)</span></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="shop-stat-item">
                                <div class="shop-stat-label"><i class="fa-solid fa-user-plus text-danger me-1"></i> Tham Gia:</div>
                                <div class="shop-stat-value">{{ $shop->created_at ? $shop->created_at->diffForHumans() : 'Mới đây' }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="shop-stat-item">
                                <div class="shop-stat-label"><i class="fa-solid fa-shield-halved text-success me-1"></i> Xác Thực:</div>
                                <div class="shop-stat-value text-success">Đã Xác Minh</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Mã Giảm Giá Của Shop --}}
        @if(isset($shopCoupons) && $shopCoupons->count() > 0)
            <div class="shop-vouchers-section mb-4 p-3 bg-white rounded-3 shadow-sm">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h2 class="h6 fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-ticket text-danger"></i>
                        <span>MÃ GIẢM GIÁ CỦA SHOP</span>
                    </h2>
                </div>
                <div class="row g-3">
                    @foreach($shopCoupons as $voucher)
                        @php
                            $isClaimed = in_array($voucher->id, $savedCouponIds ?? []);
                            $isExpired = $voucher->isExpired();
                            $isAvailable = $voucher->isAvailable();
                        @endphp
                        <div class="col-12 col-md-6 col-xl-4">
                            <div class="voucher-ticket {{ $isExpired || !$isAvailable ? 'opacity-75' : '' }}">
                                <div class="voucher-left">
                                    <i class="fa-solid fa-store voucher-left-icon"></i>
                                    <span class="voucher-left-tag">SHOP</span>
                                </div>
                                <div class="voucher-body">
                                    <div class="voucher-title">
                                        @if(in_array($voucher->type, ['percent', 'percentage']))
                                            Giảm {{ (float)$voucher->value }}%
                                        @else
                                            Giảm {{ number_format($voucher->value, 0, ',', '.') }}đ
                                        @endif
                                    </div>
                                    <div class="voucher-desc">
                                        @if($voucher->min_order_amount > 0)
                                            Đơn Tối Thiểu {{ number_format($voucher->min_order_amount, 0, ',', '.') }}đ
                                        @else
                                            Đơn Tối Thiểu 0đ
                                        @endif
                                        @if(in_array($voucher->type, ['percent', 'percentage']) && $voucher->max_discount_amount)
                                            - Giảm tối đa {{ number_format($voucher->max_discount_amount, 0, ',', '.') }}đ
                                        @endif
                                    </div>
                                    <div class="voucher-footer">
                                        <span class="voucher-exp">
                                            <i class="fa-regular fa-clock me-1"></i>
                                            @if($voucher->end_date)
                                                HSD: {{ \Carbon\Carbon::parse($voucher->end_date)->format('d/m/Y') }}
                                            @else
                                                HSD: Không giới hạn
                                            @endif
                                        </span>
                                        <button type="button"
                                                class="btn btn-sm btn-claim-voucher {{ $isClaimed ? 'btn-claimed' : 'btn-danger' }}"
                                                data-voucher-id="{{ $voucher->id }}"
                                                data-url="{{ route('customer.vouchers.save', $voucher->id) }}"
                                                {{ $isClaimed || $isExpired || !$isAvailable ? 'disabled' : '' }}>
                                            @if($isClaimed)
                                                <i class="fa-solid fa-check me-1"></i> Đã Lưu
                                            @elseif($isExpired)
                                                Hết hạn
                                            @elseif(!$isAvailable)
                                                Hết lượt
                                            @else
                                                Lưu
                                            @endif
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- 3. Shop Navigation Tabs --}}
        <div class="shop-nav-tabs-wrapper mb-4">
            <ul class="nav shop-nav-tabs" id="shopNavTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ !$searchQuery ? 'active' : '' }}" href="{{ route('shops.show', $shop->id) }}">
                        <i class="fa-solid fa-store me-1"></i> DẠO
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $searchQuery ? 'active' : '' }}" href="{{ route('shops.show', ['sellerProfile' => $shop->id, 'sort' => $sort]) }}">
                        <i class="fa-solid fa-boxes-stacked me-1"></i> TẤT CẢ SẢN PHẨM ({{ $totalProducts }})
                    </a>
                </li>
            </ul>

            {{-- Thanh tìm kiếm trong shop --}}
            <div class="shop-search-bar">
                <form action="{{ route('shops.show', $shop->id) }}" method="GET" class="d-flex gap-2">
                    <input type="hidden" name="sort" value="{{ $sort }}">
                    <div class="input-group">
                        <input type="text" name="q" class="form-control form-control-sm"
                               placeholder="Tìm sản phẩm trong Shop này..."
                               value="{{ $searchQuery }}">
                        <button class="btn btn-danger btn-sm" type="submit">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- 4. Phần Gợi Ý & Sản Phẩm Bán Chạy --}}
        @if(!$searchQuery && $topProducts->count() > 0)
            <div class="shop-section mb-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h2 class="shop-section-title mb-0">
                        <i class="fa-solid fa-fire text-danger me-1"></i> SẢN PHẨM BÁN CHẠY
                    </h2>
                    <a href="{{ route('shops.show', ['sellerProfile' => $shop->id, 'sort' => 'best_selling']) }}" class="text-danger small fw-semibold text-decoration-none">
                        Xem Tất Cả <i class="fa-solid fa-angle-right"></i>
                    </a>
                </div>
                <div class="row g-3">
                    @foreach ($topProducts as $prod)
                        <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                            <div class="shop-product-card">
                                <div class="shop-product-img-wrapper">
                                    <span class="product-favorite-tag">Yêu thích</span>
                                    <a href="{{ route('products.show', $prod->slug) }}">
                                        @php
                                            $prodImg = $prod->thumbnail
                                                ? (\Illuminate\Support\Str::startsWith($prod->thumbnail, ['http://', 'https://'])
                                                    ? $prod->thumbnail
                                                    : asset('storage/' . ltrim($prod->thumbnail, '/')))
                                                : 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400&q=80';
                                        @endphp
                                        <img src="{{ $prodImg }}" alt="{{ $prod->name }}" class="shop-product-img">
                                    </a>
                                </div>
                                <div class="shop-product-body">
                                    <a href="{{ route('products.show', $prod->slug) }}" class="text-decoration-none">
                                        <h3 class="shop-product-title" title="{{ $prod->name }}">{{ $prod->name }}</h3>
                                    </a>
                                    <div class="shop-product-price">{{ number_format($prod->price, 0, ',', '.') }}đ</div>
                                    <div class="shop-product-meta">
                                        <span><i class="fa-solid fa-star text-warning me-1"></i>5.0</span>
                                        <span>{{ number_format($prod->views_count ?? 0) }} đã bán</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- 5. Bộ lọc sắp xếp & Danh sách tất cả sản phẩm --}}
        <div class="shop-section">
            <div class="shop-filter-bar d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 p-3 bg-light rounded-3">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="fw-bold text-secondary small">Sắp xếp theo:</span>
                    <a href="{{ route('shops.show', ['sellerProfile' => $shop->id, 'sort' => 'newest', 'q' => $searchQuery]) }}"
                        class="btn btn-sm {{ $sort === 'newest' ? 'btn-danger' : 'btn-white border' }}">
                        Mới Nhất
                    </a>
                    <a href="{{ route('shops.show', ['sellerProfile' => $shop->id, 'sort' => 'best_selling', 'q' => $searchQuery]) }}"
                        class="btn btn-sm {{ $sort === 'best_selling' ? 'btn-danger' : 'btn-white border' }}">
                        Bán Chạy
                    </a>
                    <a href="{{ route('shops.show', ['sellerProfile' => $shop->id, 'sort' => 'price_asc', 'q' => $searchQuery]) }}"
                        class="btn btn-sm {{ $sort === 'price_asc' ? 'btn-danger' : 'btn-white border' }}">
                        Giá Thấp -> Cao
                    </a>
                    <a href="{{ route('shops.show', ['sellerProfile' => $shop->id, 'sort' => 'price_desc', 'q' => $searchQuery]) }}"
                        class="btn btn-sm {{ $sort === 'price_desc' ? 'btn-danger' : 'btn-white border' }}">
                        Giá Cao -> Thấp
                    </a>
                </div>

                @if ($searchQuery)
                    <div class="small text-muted">
                        Kết quả tìm kiếm cho: <strong class="text-danger">"{{ $searchQuery }}"</strong>
                        ({{ $products->total() }} sản phẩm)
                    </div>
                @endif
            </div>

            {{-- Grid Sản phẩm --}}
            @if ($products->count() > 0)
                <div class="row g-3 mb-4">
                    @foreach ($products as $prod)
                        <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                            <div class="shop-product-card">
                                <div class="shop-product-img-wrapper">
                                    <span class="product-favorite-tag">Yêu thích</span>
                                    <a href="{{ route('products.show', $prod->slug) }}">
                                        @php
                                            $prodImg = $prod->thumbnail
                                                ? (\Illuminate\Support\Str::startsWith($prod->thumbnail, ['http://', 'https://'])
                                                    ? $prod->thumbnail
                                                    : asset('storage/' . ltrim($prod->thumbnail, '/')))
                                                : 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400&q=80';
                                        @endphp
                                        <img src="{{ $prodImg }}" alt="{{ $prod->name }}" class="shop-product-img">
                                    </a>
                                </div>
                                <div class="shop-product-body">
                                    <a href="{{ route('products.show', $prod->slug) }}" class="text-decoration-none">
                                        <h3 class="shop-product-title" title="{{ $prod->name }}">{{ $prod->name }}</h3>
                                    </a>
                                    <div class="shop-product-price">{{ number_format($prod->price, 0, ',', '.') }}đ</div>
                                    <div class="shop-product-meta">
                                        <span><i class="fa-solid fa-star text-warning me-1"></i>5.0</span>
                                        <span>{{ number_format($prod->views_count ?? 12) }} đã bán</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination Links --}}
                <div class="d-flex justify-content-center mt-4">
                    {{ $products->links() }}
                </div>
            @else
                <div class="text-center py-5 bg-white rounded-3 shadow-sm my-4">
                    <i class="fa-solid fa-box-open fs-1 text-muted mb-3 d-block"></i>
                    <h5 class="fw-bold text-secondary">Không tìm thấy sản phẩm nào!</h5>
                    <p class="text-muted small">Vui lòng thử tìm kiếm bằng từ khóa khác hoặc bỏ chọn bộ lọc.</p>
                </div>
            @endif
        </div>

    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('client/js/shop-show.js') }}"></script>
    <script src="{{ asset('client/js/vouchers.js') }}"></script>
@endpush