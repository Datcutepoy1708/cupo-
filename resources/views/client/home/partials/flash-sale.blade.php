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
                if (isset($liveFlashSale) && $liveFlashSale && $liveFlashSale->products->isNotEmpty()) {
                    $flashSaleProducts = $liveFlashSale->products->filter(fn($fsp) => $fsp->product !== null)->map(function ($fsp) {
                        $p = $fsp->product;
                        $hasVars = $p->has_variants && $p->relationLoaded('variants') && $p->variants->isNotEmpty();
                        if ($hasVars) {
                            $cheapest = $p->variants->sortBy('price')->first();
                            $origPrice = (float) ($cheapest->price ?? $p->price);
                        } else {
                            $origPrice = (float) $p->price;
                        }
                        $salePrice = (float) $fsp->flash_sale_price;
                        if ($p->price > 0 && $salePrice >= $origPrice) {
                            $pct = round((($p->price - $salePrice) / $p->price) * 100);
                            if ($pct >= 10) {
                                $salePrice = round(($origPrice * (100 - $pct) / 100) / 1000) * 1000;
                            }
                        }
                        $disc = $origPrice > 0 ? (int) round((($origPrice - $salePrice) / $origPrice) * 100) : 0;
                        $rawPath = $p->thumbnail;
                        if (!$rawPath) {
                            $thumb = 'https://placehold.co/320x200?text=No+Image';
                        } elseif (\Illuminate\Support\Str::startsWith($rawPath, ['http://', 'https://'])) {
                            $thumb = $rawPath;
                        } elseif (\Illuminate\Support\Str::startsWith($rawPath, ['/storage/', 'storage/'])) {
                            $thumb = asset(ltrim($rawPath, '/'));
                        } else {
                            $thumb = asset('storage/' . $rawPath);
                        }
                        $soldPct = $fsp->quantity_limit > 0 ? min(100, round(($fsp->quantity_sold / $fsp->quantity_limit) * 100)) : 0;
                        return [
                            'id' => $p->id,
                            'slug' => $p->slug,
                            'name' => $p->name,
                            'image' => $thumb,
                            'price' => $salePrice,
                            'old_price' => $origPrice,
                            'discount' => $disc,
                            'sold_percent' => $soldPct,
                        ];
                    })->all();
                } else {
                    $flashSaleProducts = [
                        [
                            'id' => 1,
                            'slug' => null,
                            'name' => 'Tai nghe Bluetooth XZ200',
                            'image' => 'https://picsum.photos/400/400?random=1',
                            'price' => 299000,
                            'old_price' => 429000,
                            'discount' => 30,
                            'sold_percent' => 68,
                        ],
                        [
                            'id' => 2,
                            'slug' => null,
                            'name' => 'Đồng hồ thông minh Fit3',
                            'image' => 'https://picsum.photos/400/400?random=2',
                            'price' => 890000,
                            'old_price' => 1290000,
                            'discount' => 31,
                            'sold_percent' => 42,
                        ],
                        [
                            'id' => 3,
                            'slug' => null,
                            'name' => 'Áo thun nam form rộng',
                            'image' => 'https://picsum.photos/400/400?random=3',
                            'price' => 129000,
                            'old_price' => 199000,
                            'discount' => 35,
                            'sold_percent' => 90,
                        ],
                        [
                            'id' => 4,
                            'slug' => null,
                            'name' => 'Nồi chiên không dầu 5L',
                            'image' => 'https://picsum.photos/400/400?random=4',
                            'price' => 690000,
                            'old_price' => 990000,
                            'discount' => 30,
                            'sold_percent' => 55,
                        ],
                    ];
                }
            @endphp

            @foreach ($flashSaleProducts as $product)
                @php
                    $prodUrl = !empty($product['slug']) ? route('products.show', $product['slug']) : url('/products/' . $product['id']);
                @endphp
                <div class="fs-card" data-id="{{ $product['id'] }}" onclick="window.location.href='{{ $prodUrl }}'">
                    <div class="fs-image position-relative">
                        @if ($product['discount'] > 0)
                            <span class="discount-badge">-{{ $product['discount'] }}%</span>
                        @endif
                        <span class="fs-flame-badge" title="Flash Sale">
                            <i class="fa-solid fa-fire-flame-curved"></i>
                        </span>
                        <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" loading="lazy">
                    </div>

                    <div class="fs-info">
                        <h3 title="{{ $product['name'] }}">{{ $product['name'] }}</h3>
                        <div class="price-row">
                            <span class="price">{{ number_format($product['price']) }}₫</span>
                            @if (!empty($product['old_price']) && $product['old_price'] > $product['price'])
                                <del class="old-price">{{ number_format($product['old_price']) }}₫</del>
                            @endif
                        </div>
                    </div>

                    <div class="fs-footer">
                        <div class="sold-progress">
                            <div class="progress-bar-bg">
                                <div class="progress-bar-fill" style="width: {{ $product['sold_percent'] }}%;"></div>
                            </div>
                            <span class="sold-text">Đã bán {{ $product['sold_percent'] }}%</span>
                        </div>
                        <button type="button" class="btn btn-flash-buy"
                            onclick="event.stopPropagation(); window.location.href='{{ $prodUrl }}'">
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
