<aside class="checkout-summary">
    <div class="summary-box">
        <div class="summary-head">
            <h3 class="mb-0">Xác nhận đơn hàng</h3>
            <span class="mini-pill"><i class="fa-solid fa-shield-heart me-1"></i> Bảo đảm</span>
        </div>

        <div class="summary-row">
            <span>Tổng sản phẩm</span>
            <strong>{{ $totalQty ?? 0 }}</strong>
        </div>
        <div class="summary-row">
            <span>Phí vận chuyển</span>
            <strong class="text-success">0₫</strong>
        </div>
        <div class="voucher-box">
            <div class="voucher-box-header">
                <span><i class="fa-solid fa-ticket me-2 text-danger"></i> Mã giảm giá</span>
            </div>
            <div class="voucher-input-row">
                <input type="text" class="voucher-input" placeholder="Nhập mã ưu đãi" aria-label="Mã giảm giá">
                <button type="button" class="voucher-btn">Áp dụng</button>
            </div>
            <div class="voucher-note">Bạn có thể nhập mã CUP0NEW, FREESHIP hoặc XUANSALE.</div>
        </div>

        <div class="summary-row discount-row">
            <span>Giảm giá</span>
            <strong>-0₫</strong>
        </div>

        <div class="total-row">
            <span>Tổng tiền</span>
            <strong>{{ number_format($totalPrice ?? 0, 0, ',', '.') }}₫</strong>
        </div>

        <button type="submit" form="checkoutPageForm" class="submit-btn w-100">
            <i class="fa-solid fa-lock me-2"></i> Xác nhận đơn hàng
        </button>

        <a href="{{ route('cart.index') }}" class="back-btn w-100 mt-3 d-inline-block text-center">
            <i class="fa-solid fa-arrow-left me-2"></i> Quay lại giỏ hàng
        </a>
    </div>
</aside>
