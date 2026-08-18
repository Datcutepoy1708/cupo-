{{-- ===== GỢI Ý SẢN PHẨM ===== --}}
{{-- <section class="suggest-section">
    <div class="suggest-header">
        <div class="text">
            <h2 class="h4 fw-bold">Gợi ý hôm nay</h2>
            <p>Sản phẩm được chọn riêng dựa trên sở thích của bạn</p>
        </div>

        <div class="suggest-tabs" role="tablist">
            <button type="button" class="btn-tab active" data-filter="foryou">Dành cho bạn</button>
            <button type="button" class="btn-tab" data-filter="newest">Mới nhất</button>
            <button type="button" class="btn-tab" data-filter="bestseller">Bán chạy</button>
        </div>
    </div>

    <div class="suggest-grid">
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-3">
            @forelse ($suggestedProducts ?? [] as $product)
                <div class="col">
                    <div class="suggest-card">
                        <a href="{{ route('products.show', $product->slug) }}" class="suggest-image">
                            @if (($product->discount_percent ?? 0) > 0)
                                <span class="discount-badge">-{{ $product->discount_percent }}%</span>
                            @endif
                            <img src="{{ $product->thumbnail }}" alt="{{ $product->name }}" loading="lazy">
                        </a>

                        <div class="suggest-info">
                            <a href="{{ route('products.show', $product->slug) }}" class="suggest-name-link">
                                <h3>{{ $product->name }}</h3>
                            </a>
                            <div class="price-row">
                                <span class="price">{{ number_format($product->price) }}₫</span>
                                @if ($product->old_price)
                                    <span class="old-price">{{ number_format($product->old_price) }}₫</span>
                                @endif
                            </div>
                        </div>

                        <div class="suggest-meta">
                            <span class="suggest-rating">
                                <i class="fa-solid fa-star"></i> {{ number_format($product->rating ?? 0, 1) }}
                            </span>
                            <span class="suggest-sold">Đã bán {{ number_format($product->sold_count ?? 0) }}</span>
                            <button type="button" class="btn-add-cart" data-id="{{ $product->id }}"
                                title="Thêm vào giỏ">
                                <i class="fa-solid fa-cart-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="suggest-empty text-center text-muted py-5">
                        <i class="fa-solid fa-box-open d-block mb-2"></i>
                        Chưa có sản phẩm gợi ý cho bạn lúc này
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    @if (($suggestedProducts ?? collect())->count() > 0)
        <div class="suggest-loadmore">
            <button type="button" id="btnLoadMoreSuggest" class="btn-load-more">
                Xem thêm sản phẩm <i class="fa-solid fa-chevron-down ms-1"></i>
            </button>
        </div>
    @endif
</section> --}}
{{-- ===== GỢI Ý SẢN PHẨM (DỮ LIỆU FIX CỨNG - CHỜ DATA THẬT) ===== --}}
@php
    $suggestedProducts = [
        [
            'id' => 1,
            'name' => 'Áo thun nam cotton form rộng phong cách Hàn Quốc',
            'thumbnail' => 'https://picsum.photos/seed/p1/400/400',
            'price' => 159000,
            'old_price' => 259000,
            'discount_percent' => 39,
            'rating' => 4.8,
            'sold_count' => 1200,
        ],
        [
            'id' => 2,
            'name' => 'Tai nghe Bluetooth chống ồn chủ động',
            'thumbnail' => 'https://picsum.photos/seed/p2/400/400',
            'price' => 499000,
            'old_price' => 799000,
            'discount_percent' => 38,
            'rating' => 4.6,
            'sold_count' => 856,
        ],
        [
            'id' => 3,
            'name' => 'Giày sneaker unisex đế êm đi cả ngày không mỏi',
            'thumbnail' => 'https://picsum.photos/seed/p3/400/400',
            'price' => 349000,
            'old_price' => null,
            'discount_percent' => 0,
            'rating' => 4.9,
            'sold_count' => 2300,
        ],
        [
            'id' => 4,
            'name' => 'Balo laptop chống nước 15.6 inch',
            'thumbnail' => 'https://picsum.photos/seed/p4/400/400',
            'price' => 279000,
            'old_price' => 399000,
            'discount_percent' => 30,
            'rating' => 4.5,
            'sold_count' => 640,
        ],
        [
            'id' => 5,
            'name' => 'Nồi chiên không dầu 5.5L đa năng',
            'thumbnail' => 'https://picsum.photos/seed/p5/400/400',
            'price' => 890000,
            'old_price' => 1290000,
            'discount_percent' => 31,
            'rating' => 4.7,
            'sold_count' => 512,
        ],
        [
            'id' => 6,
            'name' => 'Đồng hồ thông minh đo nhịp tim, chống nước',
            'thumbnail' => 'https://picsum.photos/seed/p6/400/400',
            'price' => 599000,
            'old_price' => 899000,
            'discount_percent' => 33,
            'rating' => 4.4,
            'sold_count' => 378,
        ],
        [
            'id' => 7,
            'name' => 'Quần jean nam form slim fit basic',
            'thumbnail' => 'https://picsum.photos/seed/p7/400/400',
            'price' => 289000,
            'old_price' => null,
            'discount_percent' => 0,
            'rating' => 4.6,
            'sold_count' => 990,
        ],
        [
            'id' => 8,
            'name' => 'Bàn phím cơ RGB chống nước gaming',
            'thumbnail' => 'https://picsum.photos/seed/p8/400/400',
            'price' => 459000,
            'old_price' => 690000,
            'discount_percent' => 33,
            'rating' => 4.8,
            'sold_count' => 720,
        ],
        [
            'id' => 9,
            'name' => 'Kem chống nắng SPF50 dạng gel không nhờn rít',
            'thumbnail' => 'https://picsum.photos/seed/p9/400/400',
            'price' => 189000,
            'old_price' => 259000,
            'discount_percent' => 27,
            'rating' => 4.9,
            'sold_count' => 3100,
        ],
        [
            'id' => 10,
            'name' => 'Loa Bluetooth mini âm bass căng, pin 12h',
            'thumbnail' => 'https://picsum.photos/seed/p10/400/400',
            'price' => 329000,
            'old_price' => 499000,
            'discount_percent' => 34,
            'rating' => 4.3,
            'sold_count' => 245,
        ],
        [
            'id' => 11,
            'name' => 'Ốp lưng điện thoại chống sốc trong suốt',
            'thumbnail' => 'https://picsum.photos/seed/p11/400/400',
            'price' => 39000,
            'old_price' => 69000,
            'discount_percent' => 43,
            'rating' => 4.5,
            'sold_count' => 5400,
        ],
        [
            'id' => 12,
            'name' => 'Bình giữ nhiệt inox 500ml giữ nóng lạnh 12h',
            'thumbnail' => 'https://picsum.photos/seed/p12/400/400',
            'price' => 129000,
            'old_price' => null,
            'discount_percent' => 0,
            'rating' => 4.7,
            'sold_count' => 1780,
        ],
        [
            'id' => 13,
            'name' => 'Chuột không dây văn phòng êm ái, pin lâu',
            'thumbnail' => 'https://picsum.photos/seed/p13/400/400',
            'price' => 149000,
            'old_price' => 219000,
            'discount_percent' => 32,
            'rating' => 4.6,
            'sold_count' => 890,
        ],
        [
            'id' => 14,
            'name' => 'Túi xách nữ da PU thời trang công sở',
            'thumbnail' => 'https://picsum.photos/seed/p14/400/400',
            'price' => 259000,
            'old_price' => 399000,
            'discount_percent' => 35,
            'rating' => 4.4,
            'sold_count' => 430,
        ],
        [
            'id' => 15,
            'name' => 'Sạc dự phòng 10000mAh sạc nhanh 2 chiều',
            'thumbnail' => 'https://picsum.photos/seed/p15/400/400',
            'price' => 219000,
            'old_price' => 319000,
            'discount_percent' => 31,
            'rating' => 4.8,
            'sold_count' => 1050,
        ],
    ];
@endphp

<section class="suggest-section">
    <div class="suggest-header">
        <div class="text">
            <h2 class="h4 fw-bold">Gợi ý hôm nay</h2>
            <p>Sản phẩm được chọn riêng dựa trên sở thích của bạn</p>
        </div>

        <div class="suggest-tabs" role="tablist">
            <button type="button" class="btn-tab active" data-filter="foryou">Dành cho bạn</button>
            <button type="button" class="btn-tab" data-filter="newest">Mới nhất</button>
            <button type="button" class="btn-tab" data-filter="bestseller">Bán chạy</button>
        </div>
    </div>

    <div class="suggest-grid">
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-3">
            @forelse ($suggestedProducts as $product)
                <div class="col">
                    <div class="suggest-card">
                        <a href="#" class="suggest-image">
                            @if (($product['discount_percent'] ?? 0) > 0)
                                <span class="discount-badge">-{{ $product['discount_percent'] }}%</span>
                            @endif
                            <img src="{{ $product['thumbnail'] }}" alt="{{ $product['name'] }}" loading="lazy">
                        </a>

                        <div class="suggest-info">
                            <a href="#" class="suggest-name-link">
                                <h3>{{ $product['name'] }}</h3>
                            </a>
                            <div class="price-row">
                                <span class="price">{{ number_format($product['price']) }}₫</span>
                                @if (!empty($product['old_price']))
                                    <span class="old-price">{{ number_format($product['old_price']) }}₫</span>
                                @endif
                            </div>
                        </div>

                        <div class="suggest-meta">
                            <span class="suggest-rating">
                                <i class="fa-solid fa-star"></i> {{ number_format($product['rating'], 1) }}
                            </span>
                            <span class="suggest-sold">Đã bán {{ number_format($product['sold_count']) }}</span>
                            <button type="button" class="btn-add-cart" data-id="{{ $product['id'] }}"
                                title="Thêm vào giỏ">
                                <i class="fa-solid fa-cart-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="suggest-empty text-center text-muted py-5">
                        <i class="fa-solid fa-box-open d-block mb-2"></i>
                        Chưa có sản phẩm gợi ý cho bạn lúc này
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <div class="suggest-loadmore">
        <button type="button" id="btnLoadMoreSuggest" class="btn-load-more">
            Xem thêm sản phẩm <i class="fa-solid fa-chevron-down ms-1"></i>
        </button>
    </div>
</section>
