{{-- ===== TAB: VÍ VOUCHER CỦA TÔI ===== --}}
<div class="tab-pane fade {{ $activeTab === 'myVouchers' ? 'show active' : '' }}" id="myVouchers" role="tabpanel">
    <div class="card border-0 shadow-sm rounded-3 p-4">
        {{-- Header --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-3 border-bottom">
            <div>
                <h4 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-ticket text-danger"></i> Kho Voucher Của Tôi
                </h4>
                <p class="text-muted small mb-0">Quản lý mã giảm giá của bạn và khám phá thêm ưu đãi hấp dẫn từ Cupo</p>
            </div>
            <a href="{{ route('promotions') }}" class="btn btn-sm btn-outline-danger px-3 py-1">
                <i class="fa-solid fa-fire me-1"></i> Trang Khuyến Mãi
            </a>
        </div>

        {{-- Sub Tabs: Đang có vs Nhận thêm voucher --}}
        <div class="voucher-tabs-header">
            <button class="voucher-tab-btn active" id="tabOwnedBtn" onclick="switchVoucherTab('owned')">
                <i class="fa-solid fa-wallet"></i> Voucher Đang Có
                <span class="voucher-tab-badge owned-voucher-count-badge">{{ $savedCoupons->count() }}</span>
            </button>
            <button class="voucher-tab-btn" id="tabDiscoverBtn" onclick="switchVoucherTab('discover')">
                <i class="fa-solid fa-gift"></i> Nhận Thêm Voucher
                <span class="voucher-tab-badge">{{ $discoverableCoupons->count() }}</span>
            </button>
        </div>

        {{-- 1. SUB TAB: VOUCHER ĐANG CÓ --}}
        <div id="ownedVouchersContent">
            @if ($savedCoupons->count() > 0)
                <div class="voucher-grid">
                    @foreach ($savedCoupons as $coupon)
                        @php
                            $isShop = $coupon->seller_id && $coupon->seller?->sellerProfile;
                            $shopName = $isShop ? $coupon->seller->sellerProfile->shop_name : 'Cupo Mall';
                            $isExpired = $coupon->isExpired() || $coupon->pivot->status === 'expired';
                            $isUsed = $coupon->pivot->status === 'used';
                            $isAvailable = $coupon->isAvailable() && $coupon->pivot->status === 'saved';

                            $discountText = $coupon->type === 'percentage'
                                ? 'Giảm ' . intval($coupon->value) . '%'
                                : 'Giảm ' . number_format($coupon->value, 0, ',', '.') . 'đ';
                        @endphp
                        <div class="voucher-ticket">
                            <div class="voucher-left {{ $isUsed ? 'used' : ($isExpired ? 'expired' : ($isShop ? 'shop' : 'platform')) }}">
                                <i class="fa-solid {{ $isShop ? 'fa-store' : 'fa-tag' }} voucher-left-icon"></i>
                                <span class="voucher-left-type">{{ $isShop ? 'Shop' : 'Toàn Sàn' }}</span>
                            </div>
                            <div class="voucher-divider"></div>
                            <div class="voucher-right">
                                <div>
                                    <div class="voucher-title text-truncate">{{ $discountText }}</div>
                                    @if ($coupon->type === 'percentage' && $coupon->max_discount)
                                        <div class="voucher-condition">Tối đa {{ number_format($coupon->max_discount, 0, ',', '.') }}đ</div>
                                    @endif
                                    <div class="voucher-condition text-truncate">Đơn Tối Thiểu {{ number_format($coupon->min_order_amount, 0, ',', '.') }}đ</div>
                                    @if ($isShop)
                                        <div class="voucher-condition text-truncate text-primary"><i class="fa-solid fa-shop me-1"></i>{{ $shopName }}</div>
                                    @endif
                                </div>
                                <div class="voucher-footer">
                                    <div class="voucher-expiry {{ $coupon->expires_at && $coupon->expires_at->diffInDays(now()) <= 3 ? 'urgent' : '' }}">
                                        <i class="fa-regular fa-clock"></i>
                                        HSD: {{ $coupon->expires_at ? $coupon->expires_at->format('d/m/Y') : 'Không giới hạn' }}
                                    </div>
                                    @if ($isAvailable)
                                        @if ($isShop)
                                            <a href="{{ route('shops.show', $coupon->seller->sellerProfile->id ?? $coupon->seller_id) }}" class="btn-voucher-action btn-voucher-use">Dùng Ngay</a>
                                        @else
                                            <a href="{{ route('home') }}" class="btn-voucher-action btn-voucher-use">Dùng Ngay</a>
                                        @endif
                                    @elseif ($isUsed)
                                        <span class="btn-voucher-action btn-voucher-disabled">Đã dùng</span>
                                    @else
                                        <span class="btn-voucher-action btn-voucher-disabled">Hết hạn</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="voucher-empty-box">
                    <i class="fa-solid fa-ticket-simple voucher-empty-icon"></i>
                    <h6 class="fw-bold text-secondary mb-1">Ví của bạn chưa có mã giảm giá nào</h6>
                    <p class="text-muted small mb-3">Hãy khám phá các voucher ưu đãi từ toàn sàn và các shop để mua sắm tiết kiệm hơn!</p>
                    <button type="button" class="btn btn-sm btn-danger px-4 py-2" onclick="switchVoucherTab('discover')">
                        <i class="fa-solid fa-gift me-1"></i> Nhận Voucher Ngay
                    </button>
                </div>
            @endif
        </div>

        {{-- 2. SUB TAB: NHẬN THÊM VOUCHER --}}
        <div id="discoverVouchersContent" style="display: none;">
            @if ($discoverableCoupons->count() > 0)
                <div class="voucher-grid">
                    @foreach ($discoverableCoupons as $coupon)
                        @php
                            $isShop = $coupon->seller_id && $coupon->seller?->sellerProfile;
                            $shopName = $isShop ? $coupon->seller->sellerProfile->shop_name : 'Cupo Mall';

                            $discountText = $coupon->type === 'percentage'
                                ? 'Giảm ' . intval($coupon->value) . '%'
                                : 'Giảm ' . number_format($coupon->value, 0, ',', '.') . 'đ';
                        @endphp
                        <div class="voucher-ticket">
                            <div class="voucher-left {{ $isShop ? 'shop' : 'platform' }}">
                                <i class="fa-solid {{ $isShop ? 'fa-store' : 'fa-tag' }} voucher-left-icon"></i>
                                <span class="voucher-left-type">{{ $isShop ? 'Shop' : 'Toàn Sàn' }}</span>
                            </div>
                            <div class="voucher-divider"></div>
                            <div class="voucher-right">
                                <div>
                                    <div class="voucher-title text-truncate">{{ $discountText }}</div>
                                    @if ($coupon->type === 'percentage' && $coupon->max_discount)
                                        <div class="voucher-condition">Tối đa {{ number_format($coupon->max_discount, 0, ',', '.') }}đ</div>
                                    @endif
                                    <div class="voucher-condition text-truncate">Đơn Tối Thiểu {{ number_format($coupon->min_order_amount, 0, ',', '.') }}đ</div>
                                    @if ($isShop)
                                        <div class="voucher-condition text-truncate text-primary"><i class="fa-solid fa-shop me-1"></i>{{ $shopName }}</div>
                                    @endif
                                </div>
                                <div class="voucher-footer">
                                    <div class="voucher-expiry">
                                        <i class="fa-regular fa-clock"></i>
                                        HSD: {{ $coupon->expires_at ? $coupon->expires_at->format('d/m/Y') : 'Không giới hạn' }}
                                    </div>
                                    <button type="button"
                                            class="btn-voucher-action btn-voucher-claim"
                                            data-coupon-id="{{ $coupon->id }}"
                                            data-claim-url="{{ route('customer.vouchers.save', $coupon->id) }}">
                                        Lưu
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="voucher-empty-box">
                    <i class="fa-solid fa-box-open voucher-empty-icon"></i>
                    <h6 class="fw-bold text-secondary mb-1">Hiện không có mã giảm giá mới nào</h6>
                    <p class="text-muted small mb-0">Các mã giảm giá mới sẽ được cập nhật liên tục. Vui lòng quay lại sau!</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function switchVoucherTab(type) {
    const ownedContent = document.getElementById('ownedVouchersContent');
    const discoverContent = document.getElementById('discoverVouchersContent');
    const tabOwnedBtn = document.getElementById('tabOwnedBtn');
    const tabDiscoverBtn = document.getElementById('tabDiscoverBtn');

    if (type === 'owned') {
        ownedContent.style.display = 'block';
        discoverContent.style.display = 'none';
        tabOwnedBtn.classList.add('active');
        tabDiscoverBtn.classList.remove('active');
    } else {
        ownedContent.style.display = 'none';
        discoverContent.style.display = 'block';
        tabOwnedBtn.classList.remove('active');
        tabDiscoverBtn.classList.add('active');
    }
}
</script>