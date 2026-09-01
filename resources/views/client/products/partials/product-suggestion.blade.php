@if ($relatedProducts->isNotEmpty())
    <div class="mb-4">
        <h3 class="h5 fw-bold text-dark mb-3">SẢN PHẨM TƯƠNG TỰ BẠN CÓ THỂ THÍCH</h3>
        <div class="row g-3">
            @foreach ($relatedProducts as $rel)
                @php
                    $relImg = $rel->thumbnail_url ?? asset('images/product-placeholder.png');
                @endphp
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="{{ url('/products/' . $rel->slug) }}" class="text-decoration-none">
                        <div class="shopee-card h-100">
                            <div class="shopee-card-img-wrap">
                                <img src="{{ $relImg }}" alt="{{ $rel->name }}" loading="lazy">
                            </div>
                            <div class="shopee-card-body">
                                <h4 class="shopee-card-title">{{ $rel->name }}</h4>
                                <div class="shopee-card-price">
                                    {{ number_format($rel->current_price, 0, ',', '.') }} ₫</div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
@endif
