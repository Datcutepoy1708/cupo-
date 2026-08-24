{{--
    Partial: Coupon ticket card cho trang Khuyen mai.
    Props:
        $coupon         -- App\Models\Coupon instance
        $savedCouponIds -- Collection cua coupon_id da luu
        $isPlatform     -- bool: true = coupon Cupo, false = coupon Shop
--}}
@php
    $isSaved = $savedCouponIds->contains($coupon->id);

    // Tieu de giam gia
    if ($coupon->type === 'percentage') {
        $discountLabel = 'Giảm ' . number_format($coupon->value) . '%';
    } elseif ($coupon->type === 'fixed_amount') {
        $discountLabel = 'Giảm ' . number_format($coupon->value) . '₫';
    } else {
        $discountLabel = 'Freeship';
    }

    // Ten icon loai
    $iconClass = $isPlatform ? 'fa-tag' : 'fa-store';
    $typeLabel  = $isPlatform ? 'Cupo' : ($coupon->seller?->sellerProfile?->shop_name ?? 'Shop');
    $leftClass  = $isPlatform ? 'promo-ticket-left--platform' : 'promo-ticket-left--shop';

    // Han su dung
    $expiryText  = $coupon->expires_at ? 'HSD: ' . $coupon->expires_at->format('d/m/Y') : 'Không giới hạn';
    $isUrgent    = $coupon->expires_at && $coupon->expires_at->diffInDays(now()) <= 3;

    // Dieu kien ap dung
    $condText = $coupon->min_order_amount > 0
        ? 'Đơn tối thiểu ' . number_format($coupon->min_order_amount) . '₫'
        : 'Không giới hạn đơn tối thiểu';
@endphp

<div class="promo-ticket" data-coupon-id="{{ $coupon->id }}" data-scope="{{ $isPlatform ? 'platform' : 'shop' }}">

    {{-- Phần trái: màu + icon + loại --}}
    <div class="promo-ticket-left {{ $leftClass }}">
        <i class="fa-solid {{ $iconClass }} promo-ticket-icon"></i>
        <span class="promo-ticket-type">{{ $typeLabel }}</span>
    </div>

    {{-- Đường cắt dashed --}}
    <div class="promo-ticket-divider"></div>

    {{-- Phần phải: thông tin & hành động --}}
    <div class="promo-ticket-right">
        <div class="promo-ticket-body">
            <div class="promo-ticket-discount">{{ $discountLabel }}</div>
            <div class="promo-ticket-condition">{{ $condText }}</div>
            @if($coupon->max_discount && $coupon->type === 'percentage')
                <div class="promo-ticket-condition">Giảm tối đa {{ number_format($coupon->max_discount) }}₫</div>
            @endif
        </div>

        <div class="promo-ticket-footer">
            <span class="promo-ticket-expiry {{ $isUrgent ? 'urgent' : '' }}">
                <i class="fa-regular fa-clock"></i>
                {{ $expiryText }}
            </span>

            @if($isSaved)
                <span class="promo-btn-saved">
                    <i class="fa-solid fa-check me-1"></i>Đã lưu
                </span>
            @else
                <button class="promo-btn-save"
                        data-coupon-id="{{ $coupon->id }}"
                        data-code="{{ $coupon->code }}"
                        aria-label="Lưu mã {{ $coupon->code }}">
                    Lưu mã
                </button>
            @endif
        </div>
    </div>

</div>
