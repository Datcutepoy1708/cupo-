@extends('layouts.client.app')

@section('page-title', $category->name . ' — Mua sắm Online | Cupo')

@push('styles')
    <link href="{{ asset('client/css/category-show.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="cat-page-bg">
    <div class="container py-3">

        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb cat-breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="{{ route('categories.index') }}">Tất cả danh mục</a></li>
                @if ($rootCategory && $rootCategory->id !== $category->id)
                    <li class="breadcrumb-item"><a href="{{ url('/categories/' . $rootCategory->slug) }}">{{ $rootCategory->name }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $category->name }}</li>
                @else
                    <li class="breadcrumb-item active" aria-current="page">{{ $category->name }}</li>
                @endif
            </ol>
        </nav>

        {{-- Top Category Banner (nếu có) --}}
        @if (isset($categoryBanner) && $categoryBanner)
            <div class="cat-top-banner mb-4">
                <a href="{{ $categoryBanner->link_url ?: '#' }}">
                    <img src="{{ Str::contains($categoryBanner->image_path, 'http') ? $categoryBanner->image_path : asset('storage/' . $categoryBanner->image_path) }}"
                         alt="{{ $categoryBanner->title }}" class="img-fluid rounded-3 w-100 shadow-sm" style="max-height: 200px; object-fit: cover;">
                </a>
            </div>
        @endif

        {{-- Main 2 Columns Layout --}}
        <div class="row g-4">

            {{-- ===== CỘT TRÁI: SIDEBAR LỌC & CÂY DANH MỤC ===== --}}
            <div class="col-lg-3 col-md-4">
                <div class="cat-sidebar">

                    {{-- 1. TẤT CẢ DANH MỤC --}}
                    <div class="cat-widget mb-4">
                        <div class="cat-widget-title">
                            <i class="fa-solid fa-list-ul me-2"></i>
                            Tất Cả Danh Mục
                        </div>
                        <ul class="cat-tree-list">
                            @foreach ($allRootCategories as $root)
                                @php
                                    $isCurrentRoot = ($rootCategory && $rootCategory->id === $root->id);
                                @endphp
                                <li class="cat-tree-item {{ $isCurrentRoot ? 'open active-root' : '' }}">
                                    <a href="{{ url('/categories/' . $root->slug) }}"
                                       class="cat-root-link {{ $isCurrentRoot ? 'active' : '' }}">
                                        @if ($isCurrentRoot)
                                            <i class="fa-solid fa-caret-right text-danger me-1"></i>
                                        @endif
                                        {{ $root->name }}
                                    </a>

                                    {{-- Subcategories list --}}
                                    @if ($root->children && $root->children->isNotEmpty() && $isCurrentRoot)
                                        <ul class="cat-sub-list">
                                            @foreach ($root->children as $child)
                                                @php
                                                    $isChildActive = ($selectedChild && $selectedChild->id === $child->id);
                                                @endphp
                                                <li>
                                                    <a href="{{ url('/categories/' . $child->slug) }}"
                                                       class="cat-child-link {{ $isChildActive ? 'active' : '' }}">
                                                        {{ $child->name }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- 2. BỘ LỌC TÌM KIẾM --}}
                    <div class="cat-widget">
                        <div class="cat-widget-title border-bottom pb-2 mb-3">
                            <i class="fa-solid fa-filter me-2"></i>
                            BỘ LỌC TÌM KIẾM
                        </div>

                        <form action="{{ url('/categories/' . $category->slug) }}" method="GET" id="filterForm">
                            <input type="hidden" name="sort" value="{{ request('sort', 'popular') }}">

                            {{-- Nơi bán / Địa điểm --}}
                            <div class="filter-group mb-4">
                                <label class="filter-label">Nơi Bán</label>
                                @php
                                    $locations = ['Hà Nội', 'Hồ Chí Minh', 'Đà Nẵng', 'Hải Phòng', 'Cần Thơ'];
                                    $selectedLoc = request('location');
                                @endphp
                                @foreach ($locations as $loc)
                                    <div class="form-check cat-check">
                                        <input class="form-check-input" type="radio" name="location"
                                               id="loc_{{ $loop->index }}" value="{{ $loc }}"
                                               {{ $selectedLoc === $loc ? 'checked' : '' }}
                                               onchange="this.form.submit()">
                                        <label class="form-check-label" for="loc_{{ $loop->index }}">
                                            {{ $loc }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Khoảng Giá --}}
                            <div class="filter-group mb-4">
                                <label class="filter-label">Khoảng Giá (VNĐ)</label>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <input type="number" name="price_min" class="form-control form-control-sm cat-price-input"
                                           placeholder="Từ ₫" value="{{ request('price_min') }}" min="0">
                                    <span class="text-muted">—</span>
                                    <input type="number" name="price_max" class="form-control form-control-sm cat-price-input"
                                           placeholder="Đến ₫" value="{{ request('price_max') }}" min="0">
                                </div>
                                <button type="submit" class="btn btn-sm btn-cat-apply w-100 fw-semibold">
                                    ÁP DỤNG
                                </button>
                            </div>

                            {{-- Nút Xóa bộ lọc --}}
                            @if (request()->hasAny(['price_min', 'price_max', 'location', 'sort']))
                                <a href="{{ url('/categories/' . $category->slug) }}" class="btn btn-sm btn-outline-secondary w-100">
                                    <i class="fa-solid fa-rotate-left me-1"></i> Xóa tất cả bộ lọc
                                </a>
                            @endif
                        </form>
                    </div>

                </div>
            </div>

            {{-- ===== CỘT PHẢI: KẾT QUẢ SẢN PHẨM & TOOLBAR SẮP XẾP ===== --}}
            <div class="col-lg-9 col-md-8">

                {{-- Toolbar Sắp Xếp (Shopee Bar) --}}
                <div class="cat-sort-bar d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="sort-label me-2">Sắp xếp theo</span>

                        {{-- Button Phổ biến --}}
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'popular']) }}"
                           class="btn-sort-chip {{ $sort === 'popular' ? 'active' : '' }}">
                            Phổ Biến
                        </a>

                        {{-- Button Mới nhất --}}
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'latest']) }}"
                           class="btn-sort-chip {{ $sort === 'latest' ? 'active' : '' }}">
                            Mới Nhất
                        </a>

                        {{-- Button Bán chạy --}}
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'bestseller']) }}"
                           class="btn-sort-chip {{ $sort === 'bestseller' ? 'active' : '' }}">
                            Bán Chạy
                        </a>

                        {{-- Select Giá --}}
                        <select class="form-select form-select-sm cat-sort-select"
                                onchange="location = this.value;">
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'popular']) }}" {{ !in_array($sort, ['price_asc', 'price_desc']) ? 'selected' : '' }}>
                                Giá
                            </option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_asc']) }}" {{ $sort === 'price_asc' ? 'selected' : '' }}>
                                Giá: Thấp đến Cao
                            </option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_desc']) }}" {{ $sort === 'price_desc' ? 'selected' : '' }}>
                                Giá: Cao đến Thấp
                            </option>
                        </select>
                    </div>

                    {{-- Đếm số lượng --}}
                    <span class="text-muted small">
                        Hiển thị <strong>{{ $products->total() }}</strong> sản phẩm
                    </span>
                </div>

                {{-- Lưới Sản Phẩm (Product Grid) --}}
                @if ($products->count() > 0)
                    <div class="row g-3">
                        @foreach ($products as $p)
                            @php
                                $shopName = $p->seller->sellerProfile->shop_name ?? ($p->seller->name ?? 'Shop Official');
                                $imgUrl = $p->thumbnail
                                    ? (Str::startsWith($p->thumbnail, ['http://', 'https://'])
                                        ? $p->thumbnail
                                        : asset('storage/' . ltrim($p->thumbnail, '/')))
                                    : asset('images/product-placeholder.png');
                                $discountPercent = rand(10, 45); // Demo % giảm giá
                            @endphp
                            <div class="col-6 col-lg-3 col-md-4">
                                <a href="{{ url('/products/' . $p->slug) }}" class="text-decoration-none text-dark">
                                    <div class="shopee-card h-100">

                                        {{-- Image + Discount Badge --}}
                                        <div class="shopee-card-img-wrap">
                                            <img src="{{ $imgUrl }}" alt="{{ $p->name }}" loading="lazy">
                                            <span class="shopee-badge-discount">-{{ $discountPercent }}%</span>
                                        </div>

                                        {{-- Card Body --}}
                                        <div class="shopee-card-body">
                                            <h3 class="shopee-card-title">{{ $p->name }}</h3>

                                            {{-- Tag yêu thích/mall --}}
                                            <div class="shopee-card-tags mb-1">
                                                <span class="shopee-tag-fav">Yêu thích</span>
                                            </div>

                                            {{-- Price & Sold --}}
                                            <div class="d-flex align-items-baseline justify-content-between mt-auto">
                                                <span class="shopee-card-price">{{ number_format($p->price, 0, ',', '.') }} ₫</span>
                                                <span class="shopee-card-sold">Đã bán {{ rand(50, 999) }}</span>
                                            </div>

                                            {{-- Location / Shop --}}
                                            <div class="shopee-card-footer mt-2 pt-2 border-top">
                                                <i class="fa-solid fa-store text-muted me-1" style="font-size: 11px;"></i>
                                                <span class="shopee-card-shop">{{ $shopName }}</span>
                                            </div>
                                        </div>

                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>

                    {{-- Phân trang (Pagination) --}}
                    <div class="d-flex justify-content-center mt-4">
                        {{ $products->links() }}
                    </div>

                @else
                    {{-- Trang trống khi không tìm thấy sản phẩm --}}
                    <div class="cat-empty-box text-center py-5 bg-white rounded shadow-sm">
                        <i class="fa-solid fa-box-open text-muted mb-3" style="font-size: 56px;"></i>
                        <h5 class="fw-bold text-dark">Không tìm thấy sản phẩm nào!</h5>
                        <p class="text-muted small">Hãy thử điều chỉnh lại bộ lọc giá hoặc chọn danh mục khác.</p>
                        <a href="{{ url('/categories/' . $category->slug) }}" class="btn btn-danger btn-sm px-4 rounded-pill">
                            Đặt lại bộ lọc
                        </a>
                    </div>
                @endif

            </div>

        </div>

    </div>
</div>
@endsection
