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
                <div class="promo-fs-wrap" id="promoFsData" data-ends-at="{{ $flashSale->ends_at?->toIso8601String() }}"
                    data-save-url="{{ url('/customer/vouchers/__ID__/save') }}" data-login-url="{{ route('login') }}"
                    data-csrf="{{ csrf_token() }}">

                    <button class="promo-fs-nav promo-fs-prev" id="promoPrev" aria-label="Trước">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>

                    <div class="promo-fs-track" id="promoFsTrack">
                        @foreach ($flashSale->products as $fsp)
                            @if ($fsp->product)
                                @php
                                    $product = $fsp->product;
                                    $thumb = $product->thumbnail
                                        ? asset('storage/' . $product->thumbnail)
                                        : 'https://placehold.co/320x200?text=No+Image';
                                    $origPrice = (float) $product->price;
                                    $salePrice = (float) $fsp->flash_sale_price;
                                    $discount =
                                        $origPrice > 0 ? round((($origPrice - $salePrice) / $origPrice) * 100) : 0;
                                    $soldPct =
                                        $fsp->quantity_limit > 0
                                            ? min(100, round(($fsp->quantity_sold / $fsp->quantity_limit) * 100))
                                            : 0;
                                @endphp
                                <a href="{{ route('products.show', $product->slug) }}" class="promo-fs-card">
                                    <div class="promo-fs-img">
                                        <span class="promo-discount-badge">-{{ $discount }}%</span>
                                        <img src="{{ $thumb }}" alt="{{ $product->name }}" loading="lazy">
                                    </div>
                                    <div class="promo-fs-info">
                                        <h3 class="promo-fs-name">{{ $product->name }}</h3>
                                        <div class="promo-price-row">
                                            <span class="promo-price-sale">{{ number_format($salePrice) }}₫</span>
                                            <del class="promo-price-orig">{{ number_format($origPrice) }}₫</del>
                                        </div>
                                    </div>
                                    <div class="promo-fs-footer">
                                        <div class="promo-sold-bar">
                                            <div class="promo-sold-fill" style="width:{{ $soldPct }}%"></div>
                                        </div>
                                        <span class="promo-sold-text">Đã bán {{ $soldPct }}%</span>
                                    </div>
                                </a>
                            @endif
                        @endforeach
                    </div>

                    <button class="promo-fs-nav promo-fs-next" id="promoNext" aria-label="Tiếp">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
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

    {{-- Config data cho promotions.js --}}
    <div id="promotionsAppConfig" data-save-url="{{ url('/customer/vouchers/__ID__/save') }}"
        data-login-url="{{ route('login') }}" data-csrf="{{ csrf_token() }}">
    </div>

    {{-- Toast thông báo --}}
    <div class="promo-toast-wrap" id="promoToast" aria-live="polite">
        <span id="promoToastMsg"></span>
    </div>

@endsection
