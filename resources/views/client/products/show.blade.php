@extends('layouts.client.app')

@section('page-title', $product->name . ' — Mua ngay giá tốt | Cupo')

@push('styles')
    <link href="{{ asset('client/css/product-show.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="prod-detail-bg py-4" id="productDetailContainer"
     data-is-guest="{{ auth()->guest() ? 'true' : 'false' }}"
     data-cart-url="{{ route('cart.store') }}"
     data-cart-index-url="{{ route('cart.index') }}">
    <div class="container">

        {{-- ===== 1. BREADCRUMB ===== --}}
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb prod-breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="{{ route('categories.index') }}">Tất cả danh mục</a></li>
                @if ($product->category)
                    @if ($product->category->parent)
                        <li class="breadcrumb-item"><a href="{{ url('/categories/' . $product->category->parent->slug) }}">{{ $product->category->parent->name }}</a></li>
                    @endif
                    <li class="breadcrumb-item"><a href="{{ url('/categories/' . $product->category->slug) }}">{{ $product->category->name }}</a></li>
                @endif
                <li class="breadcrumb-item active text-truncate" style="max-width: 250px;" aria-current="page">{{ $product->name }}</li>
            </ol>
        </nav>

        {{-- ===== 2. KHU VỰC THÔNG TIN SẢN PHẨM CHÍNH (CARD TRÊN) ===== --}}
        <div class="card border-0 shadow-sm rounded-3 mb-4 overflow-hidden p-3 p-lg-4" style="background:#fff;">
            <div class="row g-4">

                {{-- CỘT TRÁI: HÌNH ẢNH SẢN PHẨM & GALLERY --}}
                <div class="col-lg-5 col-md-6">
                    <div class="prod-media-box">
                        {{-- Ảnh chính lớn --}}
                        @php
                            $mainImg = $product->thumbnail
                                ? (Str::startsWith($product->thumbnail, ['http://', 'https://']) ? $product->thumbnail : asset('storage/' . ltrim($product->thumbnail, '/')))
                                : asset('images/product-placeholder.png');
                        @endphp
                        <div class="prod-main-img-wrap mb-3 rounded-2 overflow-hidden border">
                            <img src="{{ $mainImg }}" alt="{{ $product->name }}" id="mainProductImg" class="w-100 h-100 object-fit-cover">
                        </div>

                        {{-- Thumbnail Carousel --}}
                        <div class="d-flex gap-2 overflow-auto pb-2 prod-thumb-list">
                            <div class="prod-thumb-item active" onclick="changeMainImg('{{ $mainImg }}', this)">
                                <img src="{{ $mainImg }}" alt="Thumb Main">
                            </div>
                            @foreach ($product->images as $img)
                                @php
                                    $subPath = Str::startsWith($img->image_path, ['http://', 'https://']) ? $img->image_path : asset('storage/' . ltrim($img->image_path, '/'));
                                @endphp
                                <div class="prod-thumb-item" onclick="changeMainImg('{{ $subPath }}', this)">
                                    <img src="{{ $subPath }}" alt="Thumb">
                                </div>
                            @endforeach
                        </div>

                        {{-- Social Share & ĐÃ THÍCH (LIKE BUTTON) --}}
                        <div class="d-flex align-items-center justify-content-between mt-3 pt-3 border-top">
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-muted small fw-semibold">Chia sẻ:</span>
                                <a href="https://facebook.com" target="_blank" class="btn-social fb"><i class="fa-brands fa-facebook-f"></i></a>
                                <a href="https://messenger.com" target="_blank" class="btn-social msg"><i class="fa-brands fa-facebook-messenger"></i></a>
                                <a href="https://pinterest.com" target="_blank" class="btn-social pin"><i class="fa-brands fa-pinterest-p"></i></a>
                                <a href="https://twitter.com" target="_blank" class="btn-social x"><i class="fa-brands fa-x-twitter"></i></a>
                            </div>

                            {{-- Nút Đã Thích (Like) --}}
                            <button type="button" class="btn btn-like-product {{ session('liked_product_' . $product->id) ? 'liked' : '' }}"
                                    id="btnLikeProduct" onclick="toggleProductLike({{ $product->id }})">
                                <i class="fa-solid fa-heart me-1"></i>
                                <span>Đã thích (<strong id="likesCountNum">{{ number_format($likesCount) }}</strong>)</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- CỘT PHẢI: THÔNG TIN CHI TIẾT & ĐẶT HÀNG --}}
                <div class="col-lg-7 col-md-6 d-flex flex-column">

                    {{-- Title & Mall Tag --}}
                    <h1 class="prod-title mb-2">
                        <span class="badge bg-danger me-2 align-middle fs-6" style="padding: 4px 8px;">Mall</span>
                        {{ $product->name }}
                    </h1>

                    {{-- Rating & Sales Bar --}}
                    <div class="d-flex align-items-center gap-3 flex-wrap mb-3 text-muted small">
                        <div class="d-flex align-items-center">
                            <span class="text-danger fw-bold me-1 text-decoration-underline fs-6">{{ $avgRating }}</span>
                            <div class="text-warning me-2" style="font-size: 13px;">
                                @for ($i = 1; $i <= 5; $i++)
                                    @if ($i <= floor($avgRating))
                                        <i class="fa-solid fa-star"></i>
                                    @elseif ($i - $avgRating < 1)
                                        <i class="fa-solid fa-star-half-stroke"></i>
                                    @else
                                        <i class="fa-regular fa-star"></i>
                                    @endif
                                @endfor
                            </div>
                        </div>
                        <div class="border-start ps-3">
                            <strong class="text-dark">{{ number_format($totalReviews) }}</strong> Đánh Giá
                        </div>
                        <div class="border-start ps-3">
                            <strong class="text-dark">{{ number_format($soldCount) }}</strong> Đã bán
                        </div>
                    </div>

                    {{-- Price Box --}}
                    @php
                        $fakeOriginalPrice = round(($product->price * 1.25) / 1000) * 1000;
                    @endphp
                    <div class="prod-price-box p-3 rounded-2 mb-3 d-flex align-items-baseline gap-3">
                        <span class="prod-original-price">{{ number_format($fakeOriginalPrice, 0, ',', '.') }} ₫</span>
                        <span class="prod-current-price">{{ number_format($product->price, 0, ',', '.') }} ₫</span>
                        <span class="badge bg-danger fs-6">-20% GIẢM</span>
                    </div>

                    {{-- Policy & Shipping Info --}}
                    <div class="prod-policy-box mb-3 small text-secondary">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fa-solid fa-truck-fast text-success me-2 fs-5" style="width: 24px;"></i>
                            <div>
                                <strong class="text-dark">Miễn phí vận chuyển:</strong> Giao hàng miễn phí cho đơn hàng từ 200.000₫
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="fa-solid fa-shield-halved text-danger me-2 fs-5" style="width: 24px;"></i>
                            <div>
                                <strong class="text-dark">An tâm mua sắm:</strong> Trả hàng miễn phí 15 ngày • Chính hãng 100% • Bảo hành đầy đủ
                            </div>
                        </div>
                    </div>

                    {{-- Product Variants (Nếu có) --}}
                    @if ($product->has_variants && $product->variants->isNotEmpty())
                        <div class="mb-3">
                            <label class="fw-bold text-dark small mb-2 d-block">Phân loại sản phẩm:</label>
                            <div class="d-flex flex-wrap gap-2" id="variantOptions">
                                @foreach ($product->variants as $index => $v)
                                    <button type="button" class="btn btn-outline-secondary btn-sm btn-variant {{ $index === 0 ? 'active' : '' }}"
                                            data-variant-id="{{ $v->id }}" data-price="{{ $v->price }}" data-stock="{{ $v->stock }}"
                                            onclick="selectVariant(this)">
                                        {{ $v->name ?: ('Biến thể #' . ($index + 1)) }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Quantity Selector --}}
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <label class="fw-bold text-dark small">Số Lượng:</label>
                        <div class="input-group input-group-sm qty-input-wrap" style="width: 130px;">
                            <button class="btn btn-outline-secondary" type="button" onclick="adjustQty(-1)">-</button>
                            <input type="number" id="buyQuantity" class="form-control text-center fw-bold" value="1" min="1" max="{{ $product->stock }}">
                            <button class="btn btn-outline-secondary" type="button" onclick="adjustQty(1)">+</button>
                        </div>
                        <span class="text-muted small">Còn lại <strong id="stockDisplay">{{ $product->stock }}</strong> sản phẩm</span>
                    </div>

                    {{-- ACTION BUTTONS --}}
                    <div class="d-flex gap-3 mt-auto pt-2">
                        {{-- Thêm vào giỏ hàng --}}
                        <button type="button" class="btn btn-cart-add flex-fill py-2 fw-bold" onclick="addToCart({{ $product->id }}, false)">
                            <i class="fa-solid fa-cart-plus me-2"></i>Thêm Vào Giỏ Hàng
                        </button>

                        {{-- Mua ngay --}}
                        <button type="button" class="btn btn-buy-now flex-fill py-2 fw-bold" onclick="addToCart({{ $product->id }}, true)">
                            Mua Ngay
                        </button>
                    </div>

                </div>

            </div>
        </div>

        {{-- ===== 3. BANNER SHOP SELLER (THẺ CỬA HÀNG - MATCH SCREENSHOT 2) ===== --}}
        @php
            $sellerObj = $product->seller;
            $profile = $sellerObj->sellerProfile ?? null;
            $shopName = $profile->shop_name ?? ($sellerObj->name ?? 'Gian Hàng Chính Hãng');
            $avatarUrl = $sellerObj->avatar ? asset('storage/' . ltrim($sellerObj->avatar, '/')) : asset('images/default-shop-avatar.png');
        @endphp
        <div class="card border-0 shadow-sm rounded-3 mb-4 p-3 p-lg-4" style="background:#fff;">
            <div class="row align-items-center g-3">
                {{-- Cột trái Shop info --}}
                <div class="col-lg-4 col-md-5 border-end-md">
                    <div class="d-flex align-items-center gap-3">
                        <div class="shop-avatar-wrap position-relative">
                            <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=150&q=80"
                                 alt="{{ $shopName }}" class="rounded-circle border" style="width: 72px; height: 72px; object-fit: cover;">
                            <span class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle p-1" title="Online"></span>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-1">{{ $shopName }}</h5>
                            <p class="text-muted small mb-2"><i class="fa-solid fa-circle text-success me-1" style="font-size: 8px;"></i>Online 8 phút trước</p>
                            <div class="d-flex gap-2">
                                <a href="#" class="btn btn-sm btn-outline-danger px-3 py-1"><i class="fa-solid fa-comments me-1"></i>Chat Ngay</a>
                                <a href="#" class="btn btn-sm btn-light border px-3 py-1 text-dark"><i class="fa-solid fa-store me-1"></i>Xem Shop</a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Cột phải Shop Stats Grid --}}
                <div class="col-lg-8 col-md-7">
                    <div class="row text-center g-3 shop-stats-grid">
                        <div class="col-4 col-md-2">
                            <span class="d-block text-muted small">Đánh Giá</span>
                            <strong class="text-danger">886.1k</strong>
                        </div>
                        <div class="col-4 col-md-2">
                            <span class="d-block text-muted small">Sản Phẩm</span>
                            <strong class="text-danger">71.3k</strong>
                        </div>
                        <div class="col-4 col-md-2">
                            <span class="d-block text-muted small">Tỉ Lệ Phản Hồi</span>
                            <strong class="text-danger">90%</strong>
                        </div>
                        <div class="col-4 col-md-3">
                            <span class="d-block text-muted small">Thời Gian Phản Hồi</span>
                            <strong class="text-danger">trong vài giờ</strong>
                        </div>
                        <div class="col-4 col-md-3">
                            <span class="d-block text-muted small">Người Theo Dõi</span>
                            <strong class="text-danger">678.4k</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== 4. CHI TIẾT SẢN PHẨM & MÔ TẢ ===== --}}
        <div class="card border-0 shadow-sm rounded-3 mb-4 p-3 p-lg-4" style="background:#fff;">

            {{-- Chi tiết thông số --}}
            <div class="mb-4">
                <h2 class="h5 fw-bold text-dark border-bottom pb-2 mb-3 bg-light p-2 rounded">
                    CHI TIẾT SẢN PHẨM
                </h2>
                <div class="table-responsive">
                    <table class="table table-borderless table-sm spec-table align-middle">
                        <tbody>
                            <tr>
                                <td class="text-muted fw-semibold" style="width: 200px;">Danh Mục</td>
                                <td>
                                    <a href="{{ route('categories.index') }}" class="text-danger text-decoration-none">Cupo</a> &gt;
                                    @if ($product->category)
                                        <a href="{{ url('/categories/' . $product->category->slug) }}" class="text-danger text-decoration-none">{{ $product->category->name }}</a>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold">Thương hiệu</td>
                                <td><strong class="text-primary">{{ $shopName }}</strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold">Mã SKU</td>
                                <td><code>{{ $product->sku }}</code></td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold">Tình trạng</td>
                                <td><span class="badge bg-success">Còn hàng chính hãng</span></td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold">Gửi từ</td>
                                <td>{{ $profile->address ?? 'Thành phố Hà Nội' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Mô tả chi tiết HTML --}}
            <div>
                <h2 class="h5 fw-bold text-dark border-bottom pb-2 mb-3 bg-light p-2 rounded">
                    MÔ TẢ SẢN PHẨM
                </h2>
                <div class="prod-desc-content p-2">
                    {!! $product->description !!}
                </div>
            </div>

        </div>

        {{-- ===== 5. ĐÁNH GIÁ SẢN PHẨM (5 SAO & NHẬN XÉT) ===== --}}
        <div class="card border-0 shadow-sm rounded-3 mb-4 p-3 p-lg-4" style="background:#fff;" id="reviews-section">
            <h2 class="h5 fw-bold text-dark border-bottom pb-2 mb-3">
                ĐÁNH GIÁ SẢN PHẨM (5 SAO)
            </h2>

            {{-- Rating Summary Bar --}}
            <div class="row align-items-center p-3 mb-4 rounded-3" style="background: #fffbf8; border: 1px solid #fbe3d5;">
                <div class="col-md-3 text-center border-end-md mb-3 mb-md-0">
                    <div class="display-5 fw-bold text-danger">{{ $avgRating }} <span class="fs-5 text-muted">trên 5</span></div>
                    <div class="text-warning my-1">
                        @for ($i = 1; $i <= 5; $i++)
                            <i class="fa-solid fa-star"></i>
                        @endfor
                    </div>
                    <span class="text-muted small">Dựa trên {{ number_format($totalReviews) }} nhận xét</span>
                </div>
                <div class="col-md-9">
                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-sm btn-outline-danger active">Tất Cả ({{ $totalReviews }})</button>
                        <button class="btn btn-sm btn-outline-secondary">5 Sao ({{ round($totalReviews * 0.8) }})</button>
                        <button class="btn btn-sm btn-outline-secondary">4 Sao ({{ round($totalReviews * 0.15) }})</button>
                        <button class="btn btn-sm btn-outline-secondary">3 Sao ({{ round($totalReviews * 0.05) }})</button>
                        <button class="btn btn-sm btn-outline-secondary">Có Hình Ảnh/Video</button>
                    </div>
                </div>
            </div>

            {{-- Danh sách Đánh Giá từ Khách Hàng --}}
            <div class="review-list">
                @forelse ($product->reviews as $rev)
                    <div class="review-item border-bottom pb-3 mb-3">
                        <div class="d-flex gap-3">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($rev->user->name ?? 'Khách hàng') }}&background=ee4d2d&color=fff"
                                 alt="{{ $rev->user->name ?? 'User' }}" class="rounded-circle" style="width: 42px; height: 42px;">
                            <div class="flex-fill">
                                <div class="fw-bold text-dark mb-1">{{ $rev->user->name ?? 'Khách hàng ẩn danh' }}</div>
                                <div class="text-warning small mb-1">
                                    @for ($s = 1; $s <= 5; $s++)
                                        @if ($s <= $rev->rating)
                                            <i class="fa-solid fa-star"></i>
                                        @else
                                            <i class="fa-regular fa-star text-muted"></i>
                                        @endif
                                    @endfor
                                </div>
                                <div class="text-muted extra-small mb-2">{{ $rev->created_at ? $rev->created_at->format('d/m/Y H:i') : 'Mới đây' }}</div>
                                <p class="text-dark mb-0 fs-6">{{ $rev->comment }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center py-4">Chưa có đánh giá nào cho sản phẩm này.</p>
                @endforelse
            </div>

        </div>

        {{-- ===== 6. SẢN PHẨM LIÊN QUAN ===== --}}
        @if ($relatedProducts->isNotEmpty())
            <div class="mb-4">
                <h3 class="h5 fw-bold text-dark mb-3">SẢN PHẨM TƯƠNG TỰ BẠN CÓ THỂ THÍCH</h3>
                <div class="row g-3">
                    @foreach ($relatedProducts as $rel)
                        @php
                            $relImg = $rel->thumbnail
                                ? (Str::startsWith($rel->thumbnail, ['http://', 'https://']) ? $rel->thumbnail : asset('storage/' . ltrim($rel->thumbnail, '/')))
                                : asset('images/product-placeholder.png');
                        @endphp
                        <div class="col-6 col-md-4 col-lg-2">
                            <a href="{{ url('/products/' . $rel->slug) }}" class="text-decoration-none">
                                <div class="shopee-card h-100">
                                    <div class="shopee-card-img-wrap">
                                        <img src="{{ $relImg }}" alt="{{ $rel->name }}" loading="lazy">
                                    </div>
                                    <div class="shopee-card-body">
                                        <h4 class="shopee-card-title">{{ $rel->name }}</h4>
                                        <div class="shopee-card-price">{{ number_format($rel->price, 0, ',', '.') }} ₫</div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</div>
@endsection


