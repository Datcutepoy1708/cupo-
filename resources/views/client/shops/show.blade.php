@extends('layouts.client.app')

@section('page-title', $shop->shop_name . ' — Gian hàng chính hãng | Cupo')

@push('styles')
    <link href="{{ asset('client/css/shop-show.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="shop-detail-bg py-4">
        <div class="container">

            {{-- ===== 1. BREADCRUMB ===== --}}
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb prod-breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('categories.index') }}">Gian hàng</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $shop->shop_name }}</li>
                </ol>
            </nav>

            {{-- ===== 2. HEADER BANNER GIAN HÀNG ===== --}}
            <div class="shop-header-banner">
                <div class="row align-items-center g-4">
                    {{-- Trái: Khung Profile Shop --}}
                    <div class="col-lg-5 col-xl-4">
                        <div class="shop-profile-card d-flex align-items-center gap-3">
                            <div class="shop-avatar-wrapper">
                                @php
                                    $logoUrl = $shop->logo
                                        ? (\Illuminate\Support\Str::startsWith($shop->logo, ['http://', 'https://'])
                                            ? $shop->logo
                                            : asset('storage/' . ltrim($shop->logo, '/')))
                                        : 'https://ui-avatars.com/api/?name=' . urlencode($shop->shop_name) . '&background=ee4d2d&color=fff';
                                @endphp
                                <img src="{{ $logoUrl }}" alt="{{ $shop->shop_name }}" class="shop-avatar-img">
                                <span class="badge-favorite-shop"><i class="fa-solid fa-heart me-1"></i>Yêu Thích</span>
                            </div>


                            <div class="flex-fill">
                                <h1 class="shop-name-title text-truncate">{{ $shop->shop_name }}</h1>
                                <div class="shop-online-status mb-3">
                                    <i class="fa-solid fa-circle text-success me-1 extra-small"></i>Online 45 phút trước
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn-follow-shop {{ $isFollowed ? 'following' : '' }}"
                                        id="btnFollowShop" data-shop-id="{{ $shop->id }}">
                                        @if ($isFollowed)
                                            <i class="fa-solid fa-check me-1"></i>Đang Theo Dõi
                                        @else
                                            <i class="fa-solid fa-plus me-1"></i>Theo Dõi
                                        @endif
                                    </button>
                                    <button type="button" class="btn-chat-shop" id="btnChatShop">
                                        <i class="fa-solid fa-comments me-1"></i>Chat Ngay
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Phải: Lưới Thống Kê Gian Hàng --}}
                    <div class="col-lg-7 col-xl-8">
                        <div class="shop-stats-grid">
                            <div class="shop-stat-item">
                                <i class="fa-solid fa-box-open shop-stat-icon"></i>
                                <div>Sản Phẩm: <span class="shop-stat-value">{{ number_format($totalProducts) }}</span>
                                </div>
                            </div>
                            <div class="shop-stat-item">
                                <i class="fa-solid fa-users shop-stat-icon"></i>
                                <div>Người Theo Dõi: <span class="shop-stat-value"
                                        id="shopFollowersCount">{{ number_format($followersCount) }}</span></div>
                            </div>
                            <div class="shop-stat-item">
                                <i class="fa-solid fa-user-plus shop-stat-icon"></i>
                                <div>Đang Theo: <span class="shop-stat-value">6</span></div>
                            </div>
                            <div class="shop-stat-item">
                                <i class="fa-solid fa-star shop-stat-icon"></i>
                                <div>Đánh Giá: <span class="shop-stat-value">4.9</span> <span
                                        class="small text-white-50">(1.8k Đánh giá)</span></div>
                            </div>
                            <div class="shop-stat-item">
                                <i class="fa-solid fa-comment-dots shop-stat-icon"></i>
                                <div>Tỷ Lệ Phản Hồi Chat: <span class="shop-stat-value">98%</span> <span
                                        class="small text-white-50">(Trong vài giờ)</span></div>
                            </div>
                            <div class="shop-stat-item">
                                <i class="fa-solid fa-calendar-days shop-stat-icon"></i>
                                <div>Tham Gia: <span
                                        class="shop-stat-value">{{ $shop->created_at ? $shop->created_at->diffForHumans() : '2 Năm Trước' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== 3. THANH ĐIỀU HƯỚNG TABS & TÌM KIẾM SHOP ===== --}}
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="shop-nav-tabs d-flex align-items-center justify-content-between">
                    <ul class="nav nav-tabs border-bottom-0" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link {{ !request('sort') && !request('q') ? 'active' : '' }}"
                                href="{{ route('shops.show', $shop->id) }}">
                                <i class="fa-solid fa-store me-1"></i>Dạo
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link {{ request('sort') || request('q') ? 'active' : '' }}"
                                href="{{ route('shops.show', ['sellerProfile' => $shop->id, 'sort' => 'all']) }}">
                                <i class="fa-solid fa-boxes-stacked me-1"></i>TẤT CẢ SẢN PHẨM ({{ $totalProducts }})
                            </a>
                        </li>
                    </ul>

                    {{-- Ô Tìm kiếm sản phẩm trong Shop --}}
                    <div class="py-2 pe-3">
                        <form action="{{ route('shops.show', $shop->id) }}" method="GET" class="d-flex" id="shopSearchForm">
                            <input type="text" name="q" value="{{ $searchQuery }}"
                                class="form-control form-control-sm shop-search-input"
                                placeholder="Tìm sản phẩm trong Shop này...">
                            <button type="submit" class="btn btn-sm shop-search-btn">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ===== 4. SẢN PHẨM BÁN CHẠY (TOP BEST SELLERS) ===== --}}
            @if ($topProducts->count() > 0 && !request('q'))
                <div class="mb-5">
                    <div class="shop-section-header">
                        <h2 class="shop-section-title">
                            <i class="fa-solid fa-fire me-2"></i>SẢN PHẨM BÁN CHẠY
                        </h2>
                        <a href="{{ route('shops.show', ['sellerProfile' => $shop->id, 'sort' => 'best_selling']) }}"
                            class="text-danger small fw-bold text-decoration-none">
                            Xem Tất Cả <i class="fa-solid fa-chevron-right ms-1"></i>
                        </a>
                    </div>

                    <div class="row g-3">
                        @foreach ($topProducts as $index => $prod)
                            <div class="col-6 col-md-4 col-lg-2">
                                <div class="shop-product-card">
                                    <div class="shop-product-img-wrapper">
                                        <span class="product-favorite-tag">Yêu thích</span>

                                        <a href="{{ route('products.show', $prod->slug) }}">
                                            @php
                                                $prodImg = $prod->thumbnail
                                                    ? (\Illuminate\Support\Str::startsWith($prod->thumbnail, ['http://', 'https://'])
                                                        ? $prod->thumbnail
                                                        : asset('storage/' . ltrim($prod->thumbnail, '/')))
                                                    : 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400&q=80';
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
                                            <span>{{ number_format($prod->views_count ?? 35) }} đã bán</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- ===== 5. BỘ LỌC & TẤT CẢ SẢN PHẨM CỦA SHOP ===== --}}
            <div>
                <div class="shop-filter-bar d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                    <div class="d-flex align-items-center gap-2">
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
@endpush