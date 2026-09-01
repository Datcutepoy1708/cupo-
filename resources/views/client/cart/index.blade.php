@extends('layouts.client.app')

@section('page-title', 'Giỏ hàng — Cupo')

@section('content')
    <div class="container py-4 cart-page-wrapper" id="cartMainWrapper">
        {{-- Flash Session Alerts --}}
        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show mb-3 shadow-sm" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i>{{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @elseif (session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-3 shadow-sm" role="alert">
                <i class="fa-solid fa-circle-exclamation me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Top Promotion & Guarantee Notice --}}
        <div class="cart-top-notice shadow-sm">
            <i class="fa-solid fa-truck-fast text-danger"></i>
            <div>
                <strong>Miễn phí vận chuyển</strong> cho đơn hàng từ 50.000₫. Áp dụng mã giảm giá tốt nhất khi thanh toán!
            </div>
        </div>

        {{-- Trạng thái Giỏ hàng Trống (Empty State) --}}
        <div class="cart-empty-container {{ $groupedShops->isEmpty() ? '' : 'd-none' }}" id="cartEmptyContainer">
            <div class="cart-empty-icon-wrap">
                <i class="fa-solid fa-bag-shopping"></i>
            </div>
            <h4 class="cart-empty-title">Giỏ hàng của bạn còn trống</h4>
            <p class="cart-empty-desc">Hãy khám phá hàng ngàn sản phẩm chất lượng, giá sốc và ưu đãi độc quyền trên Cupo
                ngay hôm nay!</p>
            <a href="{{ route('home') }}" class="btn-shop-now">
                <i class="fa-solid fa-arrow-left me-1"></i> Mua sắm ngay
            </a>
        </div>

        {{-- Nội dung Giỏ hàng khi có sản phẩm --}}
        <div class="{{ $groupedShops->isEmpty() ? 'd-none' : '' }}" id="cartContentContainer">
            {{-- Header Bar (Desktop Table Style) --}}
            <div class="cart-header-card">
                <div class="col-prod-main">
                    <label class="cupo-checkbox">
                        <input type="checkbox" class="cart-select-all" checked>
                        <span class="checkmark"></span>
                        <span class="checkbox-label">Sản phẩm ({{ $totalItems }})</span>
                    </label>
                </div>
                <div class="col-unit-price">Đơn giá</div>
                <div class="col-quantity">Số lượng</div>
                <div class="col-subtotal">Số tiền</div>
                <div class="col-action">Thao tác</div>
            </div>

            {{-- Danh sách các Shop & Sản phẩm --}}
            <div id="cartShopList">
                @foreach ($groupedShops as $shopData)
                    <div class="cart-shop-group" data-seller-id="{{ $shopData['seller_id'] }}">
                        {{-- Shop Header --}}
                        <div class="cart-shop-header">
                            <label class="cupo-checkbox">
                                <input type="checkbox" class="shop-select-checkbox" checked>
                                <span class="checkmark"></span>
                            </label>

                            <span class="shop-badge-preferred">Yêu thích</span>

                            @if (!empty($shopData['shop_profile']))
                                <a href="{{ route('shops.show', $shopData['shop_profile']) }}" class="cart-shop-title"
                                    title="Xem gian hàng">
                                    <i class="fa-solid fa-store text-danger"></i>
                                    <span>{{ $shopData['shop_name'] }}</span>
                                    <i class="fa-solid fa-chevron-right text-muted" style="font-size: 0.75rem;"></i>
                                </a>
                            @else
                                <span class="cart-shop-title">
                                    <i class="fa-solid fa-store text-danger"></i>
                                    <span>{{ $shopData['shop_name'] }}</span>
                                </span>
                            @endif

                            <button type="button" class="btn-chat-shop"
                                onclick="window.showCartToast('Tính năng Chat với người bán đang được kích hoạt!', 'info')">
                                <i class="fa-regular fa-comment-dots"></i> Chat ngay
                            </button>
                        </div>

                        {{-- Danh sách sản phẩm của Shop --}}
                        <div class="cart-items-wrapper">
                            @foreach ($shopData['items'] as $item)
                                @php
                                    $unitPrice = $item->variant
                                        ? $item->variant->current_price
                                        : $item->product->current_price;
                                    $originalPrice = $item->variant ? $item->variant->price : $item->product->price;
                                    $isOnSale = $item->variant
                                        ? $item->variant->is_on_sale
                                        : $item->product->is_on_sale;
                                    $discountPct = $item->variant
                                        ? $item->variant->discount_percentage
                                        : $item->product->discount_percentage;
                                    $maxStock = $item->variant ? $item->variant->stock : $item->product->stock;
                                    $itemSubtotal = $unitPrice * $item->quantity;
                                    $thumbUrl =
                                        $item->variant?->image_url ??
                                        ($item->product->thumbnail_url ?? 'https://via.placeholder.com/80');
                                @endphp

                                <div class="cart-item-row" data-item-id="{{ $item->id }}"
                                    data-current-price="{{ $unitPrice }}" data-original-price="{{ $originalPrice }}"
                                    data-current-qty="{{ $item->quantity }}" data-max-stock="{{ $maxStock }}"
                                    data-product-name="{{ $item->product->name }}"
                                    data-variant-name="{{ $item->variant ? $item->variant->name : '' }}"
                                    data-update-url="{{ route('cart.update', $item->id) }}"
                                    data-delete-url="{{ route('cart.destroy', $item->id) }}">

                                    {{-- Cột Checkbox & Thông tin Sản phẩm --}}
                                    <div class="cart-product-cell col-prod-main">
                                        <label class="cupo-checkbox me-2">
                                            <input type="checkbox" class="item-select-checkbox" checked>
                                            <span class="checkmark"></span>
                                        </label>

                                        <a href="{{ route('products.show', $item->product->slug) }}">
                                            <img src="{{ $thumbUrl }}" alt="{{ $item->product->name }}"
                                                class="cart-product-thumb">
                                        </a>

                                        <div class="cart-product-meta">
                                            <a href="{{ route('products.show', $item->product->slug) }}"
                                                class="cart-product-name" title="{{ $item->product->name }}">
                                                {{ $item->product->name }}
                                            </a>

                                            @if ($item->variant)
                                                <div class="cart-variant-tag">
                                                    <span>Phân loại:</span>
                                                    <strong>{{ $item->variant->name }}</strong>
                                                </div>
                                            @endif

                                            <div class="cart-guarantee-tag">
                                                <i class="fa-solid fa-shield-check"></i> Đổi ý miễn phí 15 ngày
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Cột Đơn giá --}}
                                    <div class="cart-unit-price-wrap col-unit-price">
                                        @if ($isOnSale && $originalPrice > $unitPrice)
                                            <span
                                                class="original-price-val">{{ number_format($originalPrice, 0, ',', '.') }}₫</span>
                                        @endif
                                        <div>
                                            <span
                                                class="current-price-val">{{ number_format($unitPrice, 0, ',', '.') }}₫</span>
                                            @if ($isOnSale && $discountPct > 0)
                                                <span class="discount-badge-val">-{{ $discountPct }}%</span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Cột Bộ tăng giảm số lượng --}}
                                    <div class="col-quantity text-center">
                                        <div class="cart-quantity-stepper">
                                            <button type="button" class="stepper-btn btn-minus"
                                                {{ $item->quantity <= 1 ? 'disabled' : '' }}>
                                                <i class="fa-solid fa-minus" style="font-size: 0.75rem;"></i>
                                            </button>
                                            <input type="number" class="stepper-input" value="{{ $item->quantity }}"
                                                min="1" max="{{ $maxStock }}">
                                            <button type="button" class="stepper-btn btn-plus"
                                                {{ $item->quantity >= $maxStock ? 'disabled' : '' }}>
                                                <i class="fa-solid fa-plus" style="font-size: 0.75rem;"></i>
                                            </button>
                                        </div>
                                        @if ($maxStock <= 5)
                                            <div class="stock-warning-text">Chỉ còn {{ $maxStock }} sản phẩm</div>
                                        @endif
                                    </div>

                                    {{-- Cột Thành tiền --}}
                                    <div class="cart-subtotal-wrap col-subtotal">
                                        <span
                                            class="subtotal-price-val">{{ number_format($itemSubtotal, 0, ',', '.') }}₫</span>
                                    </div>

                                    {{-- Cột Thao tác xóa --}}
                                    <div class="cart-action-wrap col-action">
                                        <button type="button" class="btn-remove-item" title="Xóa sản phẩm">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Dải Cupo Voucher --}}
            <div class="cart-voucher-strip">
                <div class="cart-voucher-left">
                    <i class="fa-solid fa-ticket"></i>
                    <span>Cupo Voucher</span>
                </div>
                <a href="{{ route('promotions') }}" class="cart-voucher-link">
                    <span>Xem thêm mã giảm giá</span>
                    <i class="fa-solid fa-chevron-right" style="font-size: 0.75rem;"></i>
                </a>
            </div>

            {{-- Thanh Thanh Toán Thông Minh Dính Đáy (Sticky Bottom Checkout Bar) --}}
            <div class="cart-sticky-bottom" id="cartStickyBar">
                <div class="container">
                    <div class="sticky-bar-inner">
                        <div class="sticky-actions-left">
                            <label class="cupo-checkbox">
                                <input type="checkbox" class="cart-select-all" checked>
                                <span class="checkmark"></span>
                                <span class="checkbox-label">Chọn tất cả (<span
                                        class="selected-items-count">{{ $totalItems }}</span>)</span>
                            </label>

                            <button type="button" class="btn-bulk-delete" id="btnBulkDelete">
                                <i class="fa-solid fa-trash-can me-1"></i> Xóa các mục đã chọn
                            </button>

                            <a href="{{ route('home') }}" class="btn-bulk-delete text-decoration-none">
                                <i class="fa-solid fa-bag-shopping me-1"></i> Tiếp tục mua sắm
                            </a>
                        </div>

                        <div class="sticky-summary-right">
                            <div class="total-payment-box">
                                <div class="total-payment-label">
                                    Tổng thanh toán (<span class="selected-items-count">{{ $totalItems }}</span> sản
                                    phẩm):
                                    <span class="total-payment-amount"
                                        id="totalPaymentAmount">{{ number_format($totalPrice, 0, ',', '.') }}₫</span>
                                </div>
                                <div class="total-saved-label d-none" id="totalSavedBox">
                                    Tiết kiệm: <span id="totalSavedAmount" class="fw-semibold text-danger">0₫</span>
                                </div>
                            </div>

                            <button type="button" class="btn-checkout-now" id="btnCheckoutNow"
                                data-checkout-url="{{ route('checkout.index') }}">
                                <i class="fa-solid fa-credit-card"></i> Mua Hàng
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
