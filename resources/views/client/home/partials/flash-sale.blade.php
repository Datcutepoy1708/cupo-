{{-- ===== FLASH SALE (dạng slider ngang) ===== --}}
<section class="flash-sale-slider">
    <div class="flash-sale-header">
        <div class="text">
            <h2><i class="fa-brands fa-shopify" style="color: #c62828;"></i> Flash Sale</h2>
            <p>Giá sốc mỗi ngày, số lượng có hạn</p>
        </div>
        <div class="countdown">
            <i class="fa-solid fa-clock"></i>
            <span id="flashSaleCountdown">02:45:10</span>
        </div>
    </div>

    <div class="fs-slider-container" data-aos="zoom-in" data-aos-duration="1000">
        <button class="btn fs-nav-left" onclick="moveSlide('flashSaleSlider', -1)">&#10094;</button>

        <div class="fs-slider" id="flashSaleSlider">
            @php
                $flashSaleProducts = [
                    [
                        'id' => 1,
                        'name' => 'Tai nghe Bluetooth XZ200',
                        'image' => 'sample-0.jpg',
                        'price' => 299000,
                        'old_price' => 429000,
                        'discount' => 30,
                        'sold_percent' => 68,
                    ],
                    [
                        'id' => 2,
                        'name' => 'Đồng hồ thông minh Fit3',
                        'image' => 'sample-1.jpg',
                        'price' => 890000,
                        'old_price' => 1290000,
                        'discount' => 31,
                        'sold_percent' => 42,
                    ],
                    [
                        'id' => 3,
                        'name' => 'Áo thun nam form rộng',
                        'image' => 'sample-2.jpg',
                        'price' => 129000,
                        'old_price' => 199000,
                        'discount' => 35,
                        'sold_percent' => 90,
                    ],
                    [
                        'id' => 4,
                        'name' => 'Nồi chiên không dầu 5L',
                        'image' => 'sample-3.jpg',
                        'price' => 690000,
                        'old_price' => 990000,
                        'discount' => 30,
                        'sold_percent' => 55,
                    ],
                    [
                        'id' => 5,
                        'name' => 'Balo laptop chống nước',
                        'image' => 'sample-4.jpg',
                        'price' => 259000,
                        'old_price' => 390000,
                        'discount' => 34,
                        'sold_percent' => 77,
                    ],
                    [
                        'id' => 6,
                        'name' => 'Giày sneaker unisex',
                        'image' => 'sample-5.jpg',
                        'price' => 450000,
                        'old_price' => 650000,
                        'discount' => 31,
                        'sold_percent' => 23,
                    ],
                ];
            @endphp

            @foreach ($flashSaleProducts as $product)
                <div class="fs-card" data-id="{{ $product['id'] }}">
                    <div class="fs-image">
                        <span class="discount-badge">-{{ $product['discount'] }}%</span>
                        <img src="https://picsum.photos/1600/700" alt="{{ $product['name'] }}">
                    </div>

                    <div class="fs-info">
                        <h3>{{ $product['name'] }}</h3>
                        <div class="price-row">
                            <span class="price">{{ number_format($product['price']) }}₫</span>
                            <del class="old-price">{{ number_format($product['old_price']) }}₫</del>
                        </div>
                    </div>

                    <div class="fs-footer">
                        <div class="sold-progress">
                            <div class="progress-bar-bg">
                                <div class="progress-bar-fill" style="width: {{ $product['sold_percent'] }}%;"></div>
                            </div>
                            <span class="sold-text">Đã bán {{ $product['sold_percent'] }}%</span>
                        </div>
                        <button class="btn btn-flash-buy"
                            onclick="window.location.href='{{ url('/product/' . $product['id']) }}'">
                            <i class="fa-solid fa-bolt"></i> Mua ngay
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <button class="btn fs-nav-right" onclick="moveSlide('flashSaleSlider', 1)">&#10095;</button>
    </div>

    <a href="{{ route('promotions') }}" class="view-all flash-sale">Xem tất cả Flash Sale</a>
</section>
