{{-- CỘT TRÁI: HÌNH ẢNH SẢN PHẨM & GALLERY --}}
<div class="col-lg-5 col-md-6">
    <div class="prod-media-box">
        {{-- Ảnh chính lớn --}}
        <div class="prod-main-img-wrap mb-3 rounded-2 overflow-hidden border">
            <img src="{{ $mainImg }}" alt="{{ $product->name }}" id="mainProductImg"
                class="w-100 h-100 object-fit-cover">
        </div>

        {{-- Thumbnail Carousel (Ảnh chính + Gallery + Ảnh Biến thể) --}}
        <div class="d-flex gap-2 overflow-auto pb-2 prod-thumb-list">
            @foreach ($galleryList as $index => $gItem)
                <div class="prod-thumb-item {{ $index === 0 ? 'active' : '' }}" data-img-src="{{ $gItem['url'] }}"
                    data-variant-name="{{ $gItem['variant_name'] ?? '' }}"
                    onclick="changeMainImg('{{ $gItem['url'] }}', this)" title="{{ $gItem['title'] }}">
                    <img src="{{ $gItem['url'] }}" alt="{{ $gItem['title'] }}">
                </div>
            @endforeach
        </div>

        {{-- Social Share & ĐÃ THÍCH (LIKE BUTTON) --}}
        <div class="d-flex align-items-center justify-content-between mt-3 pt-3 border-top">
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small fw-semibold">Chia sẻ:</span>
                <a href="https://facebook.com" target="_blank" class="btn-social fb"><i
                        class="fa-brands fa-facebook-f"></i></a>
                <a href="https://messenger.com" target="_blank" class="btn-social msg"><i
                        class="fa-brands fa-facebook-messenger"></i></a>
                <a href="https://pinterest.com" target="_blank" class="btn-social pin"><i
                        class="fa-brands fa-pinterest-p"></i></a>
                <a href="https://twitter.com" target="_blank" class="btn-social x"><i
                        class="fa-brands fa-x-twitter"></i></a>
            </div>

            {{-- Nút Thích / Đã thích --}}
            <button type="button" class="btn btn-like-product {{ $isLiked ? 'liked' : '' }}"
                id="btnLikeProduct" onclick="toggleProductLike({{ $product->id }})">
                <i class="{{ $isLiked ? 'fa-solid' : 'fa-regular' }} fa-heart me-1" id="likeHeartIcon"></i>
                <span id="likeLabelText">{{ $isLiked ? 'Đã thích' : 'Thích' }}</span>
                (<strong id="likesCountNum">{{ number_format($likesCount) }}</strong>)
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
    <div class="prod-price-box p-3 rounded-2 mb-3 d-flex align-items-baseline gap-3" id="prodPriceBox">
        @if ($product->has_variants && $product->variants->isNotEmpty())
            <span class="prod-original-price d-none" id="prodOriginalPrice"></span>
            <span class="prod-current-price" id="prodCurrentPrice">{{ $product->price_range_display }}</span>
            <span class="badge bg-danger fs-6 d-none" id="prodDiscountBadge"></span>
        @elseif ($product->is_on_sale)
            <span class="prod-original-price" id="prodOriginalPrice">{{ number_format($product->price, 0, ',', '.') }}
                ₫</span>
            <span class="prod-current-price"
                id="prodCurrentPrice">{{ number_format($product->current_price, 0, ',', '.') }}
                ₫</span>
            <span class="badge bg-danger fs-6" id="prodDiscountBadge">-{{ $product->discount_percentage }}%</span>
        @else
            <span class="prod-current-price"
                id="prodCurrentPrice">{{ number_format($product->current_price, 0, ',', '.') }}
                ₫</span>
        @endif
    </div>

    {{-- Policy & Shipping Info --}}
    <div class="prod-policy-box mb-3 small text-secondary">
        <div class="d-flex align-items-center mb-2">
            <i class="fa-solid fa-truck-fast text-success me-2 fs-5" style="width: 24px;"></i>
            <div>
                <strong class="text-dark">Miễn phí vận chuyển:</strong> Giao hàng miễn phí cho
                đơn hàng
                từ 50.000₫
            </div>
        </div>
        <div class="d-flex align-items-center">
            <i class="fa-solid fa-shield-halved text-danger me-2 fs-5" style="width: 24px;"></i>
            <div>
                <strong class="text-dark">An tâm mua sắm:</strong> Trả hàng miễn phí 15 ngày •
                Chính
                hãng 100% • Bảo hành đầy đủ
            </div>
        </div>
    </div>

    {{-- Product Variants (Shopee / TikTok Shop Style) --}}
    @if ($product->has_variants && $product->variants->isNotEmpty())
        <div class="prod-variants-section mb-3" id="productVariantsSection" data-has-variants="true"
            data-groups-count="{{ count($attrGroups) }}" data-variants="{{ json_encode($product->variants) }}">

            @foreach ($attrGroups as $gIndex => $group)
                <div class="variant-group-row" data-group-index="{{ $gIndex }}">
                    <div class="d-flex align-items-center mb-2">
                        <span class="variant-group-label fw-bold text-dark small">{{ $group['name'] }}:</span>
                        <span class="variant-selected-hint text-danger small fw-semibold ms-2"
                            id="variant_hint_{{ $gIndex }}"></span>
                    </div>
                    <div class="d-flex flex-wrap gap-2 variant-options-list">
                        @foreach ($group['options'] as $optVal)
                            @php
                                $matchedVar = $product->variants->first(function ($v) use ($optVal) {
                                    return (Str::startsWith($v->name, $optVal) || Str::contains($v->name, $optVal)) &&
                                        !empty($v->image_path);
                                });
                                $optImg = $matchedVar ? $matchedVar->image_url : null;
                            @endphp

                            @if ($optImg && $gIndex === 0)
                                {{-- Button có thumbnail ảnh (Màu sắc) --}}
                                <button type="button" class="btn btn-variant-option btn-variant-with-img"
                                    data-group-index="{{ $gIndex }}" data-value="{{ $optVal }}"
                                    onclick="onSelectVariantOption(this, {{ $gIndex }}, '{{ addslashes($optVal) }}')">
                                    <img src="{{ $optImg }}" alt="{{ $optVal }}"
                                        class="variant-thumb-mini">
                                    <span class="variant-text">{{ $optVal }}</span>
                                    <i class="fa-solid fa-check variant-check-icon"></i>
                                </button>
                            @else
                                {{-- Button text chip --}}
                                <button type="button" class="btn btn-variant-option btn-variant-text-only"
                                    data-group-index="{{ $gIndex }}" data-value="{{ $optVal }}"
                                    onclick="onSelectVariantOption(this, {{ $gIndex }}, '{{ addslashes($optVal) }}')">
                                    <span class="variant-text">{{ $optVal }}</span>
                                    <i class="fa-solid fa-check variant-check-icon"></i>
                                </button>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endforeach

            {{-- Khung tóm tắt phân loại đã chọn --}}
            <div id="selectedVariantSummaryWrap" class="p-2 rounded bg-light border small d-none mt-2">
                <span class="text-muted"><i class="fa-solid fa-circle-check text-success me-1"></i>Đã
                    chọn phân
                    loại:</span>
                <strong class="text-danger ms-1" id="selectedVariantFullTitle">--</strong>
            </div>

        </div>
    @endif

    <div class="d-flex align-items-center gap-3 mb-4">
        <label class="fw-bold text-dark small">Số Lượng:</label>
        <div class="input-group input-group-sm qty-input-wrap" style="width: 130px;">
            <button class="btn btn-outline-secondary" type="button" onclick="adjustQty(-1)">-</button>
            <input type="number" id="buyQuantity" class="form-control text-center fw-bold" value="1"
                min="1" max="{{ $product->stock }}">
            <button class="btn btn-outline-secondary" type="button" onclick="adjustQty(1)">+</button>
        </div>
        <span class="text-muted small">Còn lại <strong id="stockDisplay">{{ $product->stock }}</strong> sản
            phẩm</span>
    </div>

    <div class="d-flex gap-3">
        <button type="button" class="btn btn-cart-add flex-fill py-2 fw-bold" id="btnAddToCart"
            onclick="addToCart({{ $product->id }}, false)">
            <i class="fa-solid fa-cart-plus me-2"></i>Thêm Vào Giỏ Hàng
        </button>

        <button type="button" class="btn btn-buy-now flex-fill py-2 fw-bold" id="btnBuyNow"
            onclick="addToCart({{ $product->id }}, true)">
            Mua Ngay
        </button>

    </div>
</div>
