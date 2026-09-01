<div class="checkout-main-card">
    <div class="checkout-header d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
        <div>
            <div class="small text-uppercase fw-bold text-danger mb-1">Mua sắm</div>
            <h2 class="h4 fw-bold mb-0">Thông tin thanh toán</h2>
        </div>
        <span class="badge rounded-pill text-bg-light border">{{ $totalQty ?? 0 }} sản phẩm</span>
    </div>

    <div id="checkoutErrorBox" class="alert alert-danger d-none mb-3"></div>

    <form id="checkoutPageForm" action="{{ route('checkout.store') }}" method="POST"
        data-redirect-url="{{ route('customer.orders.index') }}">
        @csrf
        <input type="hidden" name="checkout_mode" value="{{ $checkoutMode ?? 'cart' }}">
        @if (($checkoutMode ?? 'cart') === 'cart')
            <input type="hidden" name="cart_item_ids"
                value="{{ isset($cartItemIds) ? implode(',', $cartItemIds) : '' }}">
        @else
            <input type="hidden" name="product_id" value="{{ $product->id ?? '' }}">
            <input type="hidden" name="product_variant_id" value="{{ $variant->id ?? '' }}">
            <input type="hidden" name="qty" value="{{ $qty ?? 1 }}">
        @endif

        <div class="checkout-card mb-4">
            <div class="checkout-card-title">
                <i class="fa-solid fa-location-dot"></i> Địa chỉ nhận hàng
            </div>

            @if (isset($addresses) && $addresses->isNotEmpty())
                <div class="mb-3">
                    <label class="form-label small text-muted">Chọn địa chỉ đã lưu</label>
                    <select class="form-select form-select-sm" id="checkoutAddressSelect">
                        @foreach ($addresses as $addr)
                            @php
                                $fullAddr =
                                    $addr->full_address ??
                                    trim(
                                        implode(
                                            ', ',
                                            array_filter([
                                                $addr->address,
                                                $addr->ward,
                                                $addr->district,
                                                $addr->province,
                                            ]),
                                        ),
                                    );
                            @endphp
                            <option value="{{ $addr->id }}"
                                data-name="{{ $addr->recipient_name ?? auth()->user()->name }}"
                                data-phone="{{ $addr->phone ?? auth()->user()->phone }}"
                                data-address="{{ $fullAddr }}" {{ $addr->is_default ? 'selected' : '' }}>
                                {{ $addr->recipient_name ?? auth()->user()->name }} |
                                {{ $addr->phone ?? auth()->user()->phone }} ({{ $fullAddr }})
                                {{ $addr->is_default ? '[Mặc định]' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small text-muted mb-1">Họ tên người nhận <span
                            class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="recipient_name" name="recipient_name"
                        value="{{ $defaultAddress->recipient_name ?? auth()->user()->name }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small text-muted mb-1">Số điện thoại <span
                            class="text-danger">*</span></label>
                    <input type="tel" class="form-control" id="recipient_phone" name="phone"
                        value="{{ $defaultAddress->phone ?? auth()->user()->phone }}" required>
                </div>
                <div class="col-12">
                    <label class="form-label small text-muted mb-1">Địa chỉ giao hàng <span
                            class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="recipient_address" name="shipping_address"
                        value="{{ $defaultAddress->full_address ??
                            ($defaultAddress
                                ? trim(
                                    implode(
                                        ', ',
                                        array_filter([
                                            $defaultAddress->address,
                                            $defaultAddress->ward,
                                            $defaultAddress->district,
                                            $defaultAddress->province,
                                        ]),
                                    ),
                                )
                                : '') }}"
                        required>
                </div>
            </div>
        </div>

        <div class="checkout-card mb-4">
            <div class="checkout-card-title">
                <i class="fa-solid fa-boxes-stacked"></i> Sản phẩm đặt hàng
            </div>

            @foreach ($groupedShops as $shop)
                <div class="shop-box mb-3">
                    <div class="shop-box-header">
                        <strong><i class="fa-solid fa-store me-2 text-danger"></i>{{ $shop['shop_name'] }}</strong>
                    </div>

                    @foreach ($shop['items'] as $item)
                        @php
                            $product = data_get($item, 'product', $item->product ?? null);
                            $variant = data_get($item, 'variant', $item->variant ?? null);
                            $quantity = data_get($item, 'quantity', $item->quantity ?? 1);
                            $unitPrice = data_get(
                                $item,
                                'unit_price',
                                $variant ? $variant->current_price : $product->current_price ?? 0,
                            );
                            $subtotal = data_get($item, 'subtotal', $unitPrice * $quantity);
                            $thumb =
                                $variant?->image_url ??
                                ($product->thumbnail_url ?? ($product->thumbnail ?? 'https://via.placeholder.com/80'));
                        @endphp

                        <div class="item-row d-flex gap-3 align-items-center">
                            <img src="{{ $thumb }}" alt="{{ $product->name }}">
                            <div class="flex-grow-1">
                                <div class="fw-semibold text-dark">{{ $product->name }}</div>
                                @if ($variant)
                                    <div class="small text-muted">Phân loại: {{ $variant->name }}</div>
                                @endif
                                <div class="small text-muted">Số lượng: {{ $quantity }}</div>
                            </div>
                            <div class="fw-bold text-danger">{{ number_format($subtotal, 0, ',', '.') }}₫</div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>

        <div class="checkout-card mb-4">
            <div class="checkout-card-title">
                <i class="fa-solid fa-wallet"></i> Phương thức thanh toán
            </div>

            <label class="payment-card active w-100 mb-3">
                <input type="radio" name="payment_method" value="cod" checked>
                <i class="fa-solid fa-hand-holding-dollar text-success fs-5"></i>
                <div>
                    <div class="fw-semibold">Thanh toán khi nhận hàng (COD)</div>
                    <div class="small text-muted">Trả tiền mặt khi giao hàng</div>
                </div>
            </label>

            <label class="payment-card w-100 mb-3">
                <input type="radio" name="payment_method" value="vnpay">
                <i class="fa-solid fa-qrcode text-primary fs-5"></i>
                <div>
                    <div class="fw-semibold">VNPAY</div>
                    <div class="small text-muted">Quét mã QR / ATM</div>
                </div>
            </label>

            <label class="payment-card w-100">
                <input type="radio" name="payment_method" value="momo">
                <i class="fa-solid fa-mobile-screen-button text-danger fs-5"></i>
                <div>
                    <div class="fw-semibold">Ví MoMo</div>
                    <div class="small text-muted">Thanh toán nhanh, tiện lợi</div>
                </div>
            </label>
        </div>

        <div class="checkout-card">
            <div class="checkout-card-title">
                <i class="fa-solid fa-note-sticky"></i> Ghi chú
            </div>
            <textarea class="form-control" id="order_note" name="note" rows="3"
                placeholder="Ví dụ: Giao hàng vào giờ hành chính..."></textarea>
        </div>
    </form>
</div>
