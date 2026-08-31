<div class="col-6 col-md-3">
    <div class="card h-100 border-0 shadow-sm shop-product-card">
        @if ($bestSeller ?? false)
            <span class="badge bestseller-badge">Bán chạy</span>
        @endif
        @php $prodImg = $prod->thumbnail ? (\Illuminate\Support\Str::startsWith($prod->thumbnail, ['http://', 'https://']) ? $prod->thumbnail : asset('storage/' . ltrim($prod->thumbnail, '/'))) : 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400&q=80'; @endphp
        <a href="{{ route('products.show', $prod->slug) }}"><img src="{{ $prodImg }}" class="card-img-top"
                alt="{{ $prod->name }}"></a>
        <div class="card-body"><a href="{{ route('products.show', $prod->slug) }}" class="text-decoration-none text-dark">
                <p class="small mb-1 text-truncate">{{ $prod->name }}</p>
            </a>
            <p class="fw-bold mb-0 shop-product-price">{{ number_format($prod->price, 0, ',', '.') }}đ</p>
            <p class="text-muted small mb-0">Đã bán {{ number_format($prod->views_count ?? 0) }}</p>
        </div>
    </div>
</div>
