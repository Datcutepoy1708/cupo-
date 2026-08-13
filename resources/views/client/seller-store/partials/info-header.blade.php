<div class="shop-banner" style="background-image: url('{{ $shop->banner }}');">
    <button type="button" class="btn banner-edit-btn" data-bs-toggle="modal" data-bs-target="#editBannerModal">
        <i class="fa-solid fa-camera"></i> Đổi ảnh bìa
    </button>
</div>
<div class="shop-header">
    <div class="shop-avatar-wrap">
        @if ($shop->logo)
            <img src="{{ $shop->logo }}" alt="{{ $shop->shop_name }}" class="shop-avatar">
        @else
            @php
                $words = preg_split('/\s+/', trim($shop->shop_name));
                $initials = mb_strtoupper(
                    mb_substr($words[0], 0, 1) . (count($words) > 1 ? mb_substr(end($words), 0, 1) : ''),
                );
            @endphp
            <div class="shop-avatar shop-avatar-fallback">{{ $initials }}</div>
        @endif

        <button type="button" class="avatar-edit-btn" data-bs-toggle="modal" data-bs-target="#editAvatarModal">
            <i class="fa-solid fa-camera"></i>
        </button>
    </div>

    <div class="shop-info">
        <div class="shop-info-top">
            <div>
                <h1 class="shop-name">{{ $shop->shop_name }}</h1>
                <div class="shop-stats">
                    <span><i class="fa-solid fa-box"></i> {{ $shop->product_count }} sản phẩm</span>
                    <span class="divider">|</span>
                    <span class="stars">
                        <i class="fa-solid fa-star"></i> {{ number_format($shop->rating, 1) }}
                        <span class="text-muted">({{ $shop->review_count }} đánh giá)</span>
                    </span>
                    <span class="divider">|</span>
                    <span><i class="fa-solid fa-users"></i> {{ number_format($shop->followers_count) }}
                        người theo
                        dõi</span>
                </div>
            </div>
        </div>
    </div>
</div>
