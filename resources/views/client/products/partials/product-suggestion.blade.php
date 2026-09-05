@if ($relatedProducts->isNotEmpty())
    <div class="mb-4">
        <h3 class="h5 fw-bold text-dark mb-3">SẢN PHẨM TƯƠNG TỰ BẠN CÓ THỂ THÍCH</h3>
        <div class="row g-3">
            @foreach ($relatedProducts as $rel)
                @php
                    $relImg = $rel->thumbnail_url ?? asset('images/product-placeholder.png');
                    $isFs = $rel->is_flash_sale;
                    if ($isFs && $rel->flash_sale_info) {
                        $displayPrice = $rel->flash_sale_info['price'];
                        $origPrice = $rel->flash_sale_info['original_price'];
                        $discountPercent = $rel->flash_sale_info['discount_percentage'];
                    } elseif ($rel->is_on_sale) {
                        $displayPrice = $rel->sale_price;
                        $origPrice = $rel->price;
                        $discountPercent = $rel->discount_percentage;
                    } else {
                        $displayPrice = $rel->price;
                        $origPrice = null;
                        $discountPercent = 0;
                    }
                @endphp
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="{{ url('/products/' . $rel->slug) }}" class="text-decoration-none">
                        <div class="shopee-card h-100 {{ $isFs ? 'shopee-card--flash-sale' : '' }}">
                            <div class="shopee-card-img-wrap position-relative">
                                <img src="{{ $relImg }}" alt="{{ $rel->name }}" loading="lazy">
                                @if ($isFs)
                                    <span class="fs-flame-badge" title="Flash Sale">
                                        <i class="fa-solid fa-fire-flame-curved"></i>
                                    </span>
                                @endif
                                @if ($discountPercent > 0)
                                    <span class="shopee-badge-discount">-{{ $discountPercent }}%</span>
                                @endif
                            </div>
                            <div class="shopee-card-body">
                                @if ($isFs)
                                    <div class="mb-1">
                                        <span class="badge-flash-sale-card"><i class="fa-solid fa-fire-flame-curved"></i> Flash Sale</span>
                                    </div>
                                @endif
                                <h4 class="shopee-card-title">{{ $rel->name }}</h4>
                                <div class="shopee-card-price {{ $isFs ? 'text-danger fw-bold' : '' }}">
                                    {{ number_format($displayPrice, 0, ',', '.') }} ₫
                                </div>
                                @if ($origPrice && $origPrice > $displayPrice)
                                    <del class="text-muted small" style="font-size: 11px;">{{ number_format($origPrice, 0, ',', '.') }} ₫</del>
                                @endif
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
@endif
