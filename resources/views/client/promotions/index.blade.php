@extends('layouts.client.app')

@section('page-title', 'Khuyến Mãi — Cupo')

@section('content')
    {{-- ===== Mã Giảm Giá Section ===== --}}
    <section class="promo-section promo-section--light">
        <div class="container-xl px-3 px-md-4">

            <div class="promo-section-header">
                <div class="promo-section-title-group">
                    <div>
                        <h2 class="promo-section-title">Mã Giảm Giá</h2>
                        <p class="promo-section-sub">Lưu ngay để dùng khi thanh toán</p>
                    </div>
                </div>
                <div class="promo-coupon-tabs" id="couponTabs">
                    <button class="promo-tab-btn active" data-tab="all">Tất cả</button>
                    <button class="promo-tab-btn" data-tab="platform">Cupo</button>
                    <button class="promo-tab-btn" data-tab="shop">Gian hàng</button>
                </div>
            </div>

            {{-- Tab: Tất cả & Cupo --}}
            <div class="promo-coupon-pane" id="pane-all">
                @php $allCoupons = $platformCoupons->merge($shopCoupons)->sortByDesc('value'); @endphp
                @if ($allCoupons->isEmpty())
                    <div class="promo-empty-box">
                        <i class="fa-solid fa-ticket fa-2x mb-2"></i>
                        <p>Hiện chưa có mã giảm giá nào khả dụng.</p>
                    </div>
                @else
                    <div class="promo-coupon-grid">
                        @foreach ($allCoupons as $coupon)
                            @include('client.promotions.partials.coupon-card', [
                                'coupon' => $coupon,
                                'savedCouponIds' => $savedCouponIds,
                                'isPlatform' => is_null($coupon->seller_id),
                            ])
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="promo-coupon-pane d-none" id="pane-platform">
                @if ($platformCoupons->isEmpty())
                    <div class="promo-empty-box">
                        <i class="fa-solid fa-ticket fa-2x mb-2"></i>
                        <p>Hiện chưa có mã giảm giá Cupo nào.</p>
                    </div>
                @else
                    <div class="promo-coupon-grid">
                        @foreach ($platformCoupons as $coupon)
                            @include('client.promotions.partials.coupon-card', [
                                'coupon' => $coupon,
                                'savedCouponIds' => $savedCouponIds,
                                'isPlatform' => true,
                            ])
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="promo-coupon-pane d-none" id="pane-shop">
                @if ($shopCoupons->isEmpty())
                    <div class="promo-empty-box">
                        <i class="fa-solid fa-store fa-2x mb-2"></i>
                        <p>Hiện chưa có mã giảm giá của gian hàng nào.</p>
                    </div>
                @else
                    <div class="promo-coupon-grid">
                        @foreach ($shopCoupons as $coupon)
                            @include('client.promotions.partials.coupon-card', [
                                'coupon' => $coupon,
                                'savedCouponIds' => $savedCouponIds,
                                'isPlatform' => false,
                            ])
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </section>
    {{-- ===== Flash Sale Section ===== --}}
    <section class="promo-section">
        <div class="container-xl px-3 px-md-4">

            <div class="promo-section-header">
                <div class="promo-section-title-group">
                    <div>
                        <h2 class="promo-section-title">Flash Sale</h2>
                        <p class="promo-section-sub">Giá sốc có hạn, nhanh tay kẻo lỡ</p>
                    </div>
                </div>

                @if ($flashSale && $flashSaleStatus === 'live')
                    <div class="promo-countdown-badge" id="promoCountdown"
                        data-ends-at="{{ $flashSale->ends_at?->toIso8601String() }}">
                        <i class="fa-solid fa-clock"></i>
                        <span id="promoCountdownTime">--:--:--</span>
                        <span class="promo-countdown-label">còn lại</span>
                    </div>
                @elseif($flashSale && $flashSaleStatus === 'upcoming')
                    <div class="promo-upcoming-badge">
                        <i class="fa-solid fa-calendar-days"></i>
                        Bắt đầu {{ $flashSale->starts_at->format('d/m H:i') }}
                    </div>
                @endif
            </div>

            @if ($flashSale && $flashSale->products->isNotEmpty())
                <div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-3">
                    @foreach ($flashSale->products as $fsp)
                        @if ($fsp->product)
                            @php
                                $product = $fsp->product;
                                $rawPath = $product->thumbnail;
                                if (!$rawPath) {
                                    $thumb = 'https://placehold.co/320x200?text=No+Image';
                                } elseif (Str::startsWith($rawPath, ['http://', 'https://'])) {
                                    $thumb = $rawPath;
                                } elseif (Str::startsWith($rawPath, ['/storage/', 'storage/'])) {
                                    $thumb = asset(ltrim($rawPath, '/'));
                                } else {
                                    $thumb = asset('storage/' . $rawPath);
                                }
                                $hasVariants = $product->has_variants && $product->relationLoaded('variants') && $product->variants->isNotEmpty();
                                if ($hasVariants) {
                                    $cheapestVariant = $product->variants->sortBy('price')->first();
                                    $origPrice = (float) ($cheapestVariant->price ?? $product->price);
                                    $salePrice = (float) $fsp->flash_sale_price;
                                    if ($product->price > 0 && $salePrice >= $origPrice) {
                                        $pctDiscount = round((($product->price - $salePrice) / $product->price) * 100);
                                        if ($pctDiscount >= 10) {
                                            $salePrice = round(($origPrice * (100 - $pctDiscount) / 100) / 1000) * 1000;
                                        }
                                    }
                                    $discount = $origPrice > 0 ? round((($origPrice - $salePrice) / $origPrice) * 100) : 0;
                                } else {
                                    $origPrice = (float) $product->price;
                                    $salePrice = (float) $fsp->flash_sale_price;
                                    $discount = $origPrice > 0 ? round((($origPrice - $salePrice) / $origPrice) * 100) : 0;
                                }
                                $soldPct =
                                    $fsp->quantity_limit > 0
                                        ? min(100, round(($fsp->quantity_sold / $fsp->quantity_limit) * 100))
                                        : 0;
                            @endphp
                            <div class="col">
                                <div class="fs-card h-100 d-flex flex-column" onclick="window.location.href='{{ route('products.show', $product->slug) }}'">
                                    <div class="fs-image position-relative">
                                        @if ($discount > 0)
                                            <span class="discount-badge">-{{ $discount }}%</span>
                                        @endif
                                        <span class="fs-flame-badge" title="Flash Sale">
                                            <i class="fa-solid fa-fire-flame-curved"></i>
                                        </span>
                                        <img src="{{ $thumb }}" alt="{{ $product->name }}" loading="lazy">
                                    </div>

                                    <div class="fs-info flex-grow-1">
                                        <h3 title="{{ $product->name }}">{{ $product->name }}</h3>
                                        <div class="price-row">
                                            <span class="price">{{ number_format($salePrice) }}₫</span>
                                            @if ($origPrice > $salePrice)
                                                <del class="old-price">{{ number_format($origPrice) }}₫</del>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="fs-footer">
                                        <div class="sold-progress">
                                            <div class="progress-bar-bg">
                                                <div class="progress-bar-fill" style="width: {{ $soldPct }}%;"></div>
                                            </div>
                                            <span class="sold-text">Đã bán {{ $soldPct }}%</span>
                                        </div>
                                        <a href="{{ route('products.show', $product->slug) }}" class="btn btn-flash-buy" onclick="event.stopPropagation();">
                                            <i class="fa-solid fa-bolt"></i> Mua ngay
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @elseif($flashSaleStatus === 'upcoming' && $flashSale)
                <div class="promo-empty-box">
                    <i class="fa-solid fa-clock fa-2x mb-2"></i>
                    <p>Phiên Flash Sale sắp bắt đầu vào <strong>{{ $flashSale->starts_at->format('H:i d/m/Y') }}</strong>.
                        Hãy quay lại sau nhé!</p>
                </div>
            @else
                <div class="promo-empty-box">
                    <i class="fa-solid fa-bolt fa-2x mb-2"></i>
                    <p>Hiện chưa có phiên Flash Sale nào đang diễn ra.</p>
                </div>
            @endif

        </div>
    </section>

    {{-- ===== Đang Giảm Giá Sâu Section ===== --}}
    <section class="promo-section promo-section--deep-deals">
        <div class="container-xl px-3 px-md-4">
            <div class="promo-section-header">
                <div class="promo-section-title-group">
                    <div>
                        <h2 class="promo-section-title">Đang Giảm Giá Sâu</h2>
                        <p class="promo-section-sub">Ưu đãi giảm giá cực tốt do các gian hàng tự cấu hình & các siêu phẩm Flash Sale</p>
                    </div>
                </div>
                <div class="promo-badge-hot">
                    <i class="fa-solid fa-bolt-lightning me-1"></i> Deal Hời Mỗi Ngày
                </div>
            </div>

            @if ($deepDiscountProducts->isNotEmpty())
                <div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-3">
                    @foreach ($deepDiscountProducts as $product)
                        @php
                            $rawPath = $product->thumbnail;
                            if (!$rawPath) {
                                $thumb = 'https://placehold.co/320x240?text=No+Image';
                            } elseif (Str::startsWith($rawPath, ['http://', 'https://'])) {
                                $thumb = $rawPath;
                            } elseif (Str::startsWith($rawPath, ['/storage/', 'storage/'])) {
                                $thumb = asset(ltrim($rawPath, '/'));
                            } else {
                                $thumb = asset('storage/' . $rawPath);
                            }

                            $isFs = $product->is_flash_sale && !empty($product->flash_sale_info);
                            if ($isFs) {
                                $salePrice = (float) $product->flash_sale_info['price'];
                                $origPrice = (float) $product->flash_sale_info['original_price'];
                                $discount = (int) $product->flash_sale_info['discount_percentage'];
                            } else {
                                $hasDeepVariants = $product->has_variants && $product->relationLoaded('variants') && $product->variants->isNotEmpty();
                                if ($hasDeepVariants) {
                                    $cheapestVariant = $product->variants->sortBy('current_price')->first();
                                    $origPrice = (float) ($cheapestVariant->price ?? $product->price);
                                    $salePrice = (float) ($cheapestVariant->sale_price ?? $cheapestVariant->price ?? $product->sale_price);
                                } else {
                                    $origPrice = (float) $product->price;
                                    $salePrice = (float) $product->sale_price;
                                }
                                $discount =
                                    $product->discount_percentage ??
                                    ($origPrice > 0 ? round((($origPrice - $salePrice) / $origPrice) * 100) : 0);
                            }
                            $savedAmount = max(0, $origPrice - $salePrice);
                            $shopName =
                                $product->seller?->sellerProfile?->shop_name ??
                                ($product->seller?->name ?? 'Gian hàng Cupo');
                        @endphp
                        <div class="col">
                            <div class="promo-deal-card">
                                <a href="{{ route('products.show', $product->slug) }}" class="promo-deal-img-link">
                                    <div class="promo-deal-img-wrap">
                                        @if ($discount > 0)
                                            <span class="promo-deal-badge">-{{ $discount }}%</span>
                                        @endif
                                        @if ($isFs)
                                            <span class="fs-flame-badge" title="Flash Sale">
                                                <i class="fa-solid fa-fire-flame-curved"></i>
                                            </span>
                                        @endif
                                        <img src="{{ $thumb }}" alt="{{ $product->name }}" loading="lazy">
                                    </div>
                                </a>
                                <div class="promo-deal-body">
                                    <div class="promo-deal-shop">
                                        @if ($isFs)
                                            <span class="badge-flash-sale-card me-1"><i class="fa-solid fa-fire-flame-curved"></i> Flash Sale</span>
                                        @else
                                            <i class="fa-solid fa-store me-1 text-danger"></i>
                                        @endif
                                        <span class="text-truncate">{{ $shopName }}</span>
                                    </div>
                                    <h3 class="promo-deal-name">
                                        <a href="{{ route('products.show', $product->slug) }}">{{ $product->name }}</a>
                                    </h3>
                                    <div class="promo-deal-prices">
                                        <span class="promo-deal-price-sale">{{ number_format($salePrice) }}₫</span>
                                        @if ($origPrice > $salePrice)
                                            <del class="promo-deal-price-orig">{{ number_format($origPrice) }}₫</del>
                                        @endif
                                    </div>
                                    <div class="promo-deal-saving">
                                        <i class="fa-solid fa-circle-check me-1"></i>Tiết kiệm
                                        <strong>{{ number_format($savedAmount) }}₫</strong>
                                    </div>
                                </div>
                                <div class="promo-deal-action">
                                    <a href="{{ route('products.show', $product->slug) }}" class="btn promo-deal-btn">
                                        <i class="fa-solid fa-bag-shopping me-1"></i> Xem chi tiết
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="promo-empty-box">
                    <i class="fa-solid fa-tags fa-2x mb-2"></i>
                    <p>Hiện chưa có sản phẩm nào đang giảm giá sâu. Hãy quay lại sau nhé!</p>
                </div>
            @endif
        </div>
    </section>

    {{-- Config data cho promotions.js --}}
    <div id="promotionsAppConfig" data-save-url="{{ url('/customer/vouchers/__ID__/save') }}"
        data-login-url="{{ route('login') }}" data-csrf="{{ csrf_token() }}">
    </div>

    {{-- Toast thông báo --}}
    <div class="promo-toast-wrap" id="promoToast" aria-live="polite">
        <span id="promoToastMsg"></span>
    </div>

@endsection
