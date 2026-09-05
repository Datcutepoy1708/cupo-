<div class="col-6 col-md-3">
    <div class="card h-100 border-0 shadow-sm shop-product-card {{ ($prod->is_flash_sale ?? false) ? 'shopee-card--flash-sale' : '' }}">
        @if ($bestSeller ?? false)
            <span class="badge bestseller-badge">Bán chạy</span>
        @endif
        @php
            $prodImg = $prod->thumbnail ? (\Illuminate\Support\Str::startsWith($prod->thumbnail, ['http://', 'https://']) ? $prod->thumbnail : asset('storage/' . ltrim($prod->thumbnail, '/'))) : 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400&q=80';
            $isFs = $prod->is_flash_sale ?? false;
            if ($isFs && $prod->flash_sale_info) {
                $displayPrice = $prod->flash_sale_info['price'];
                $origPrice = $prod->flash_sale_info['original_price'];
                $discountPercent = $prod->flash_sale_info['discount_percentage'];
            } elseif ($prod->is_on_sale) {
                $displayPrice = $prod->sale_price;
                $origPrice = $prod->price;
                $discountPercent = $prod->discount_percentage;
            } else {
                $displayPrice = $prod->price;
                $origPrice = null;
                $discountPercent = 0;
            }
        @endphp
        <div class="position-relative">
            <a href="{{ route('products.show', $prod->slug) }}">
                <img src="{{ $prodImg }}" class="card-img-top" alt="{{ $prod->name }}">
            </a>
            @if ($isFs)
                <span class="fs-flame-badge" title="Flash Sale">
                    <i class="fa-solid fa-fire-flame-curved"></i>
                </span>
            @endif
        </div>
        <div class="card-body">
            @if ($isFs)
                <div class="mb-1">
                    <span class="badge-flash-sale-card"><i class="fa-solid fa-fire-flame-curved"></i> Flash Sale</span>
                </div>
            @endif
            <a href="{{ route('products.show', $prod->slug) }}" class="text-decoration-none text-dark">
                <p class="small mb-1 text-truncate" title="{{ $prod->name }}">{{ $prod->name }}</p>
            </a>
            <div class="d-flex align-items-baseline gap-1 flex-wrap">
                <p class="fw-bold mb-0 shop-product-price {{ $isFs ? 'text-danger' : '' }}">{{ number_format($displayPrice, 0, ',', '.') }}đ</p>
                @if ($origPrice && $origPrice > $displayPrice)
                    <del class="text-muted small" style="font-size: 11px;">{{ number_format($origPrice, 0, ',', '.') }}đ</del>
                @endif
                @if ($discountPercent > 0)
                    <span class="badge bg-danger-subtle text-danger" style="font-size: 10px;">-{{ $discountPercent }}%</span>
                @endif
            </div>
            <p class="text-muted small mb-0">Đã bán {{ number_format($prod->views_count ?? 0) }}</p>
        </div>
    </div>
</div>
