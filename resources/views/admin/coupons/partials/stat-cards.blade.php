{{--
    Partial: 5 Stat Cards cho Trang Quan ly Ma giam gia (Coupons / Vouchers)
--}}
<div class="row g-3 mb-4">

    {{-- Tong Ma giam gia --}}
    <div class="col-6 col-lg-auto flex-fill">
        <div class="coupon-stat-card">
            <div class="d-flex align-items-center justify-content-between">
                <span class="coupon-stat-label">Tổng Voucher</span>
                <i class="fa-solid fa-ticket text-secondary coupon-stat-icon"></i>
            </div>
            <div class="coupon-stat-num" id="count-all">--</div>
            <div class="coupon-stat-sub text-muted">Toàn sàn & Shop</div>
        </div>
    </div>

    {{-- Dang hoat dong --}}
    <div class="col-6 col-lg-auto flex-fill">
        <div class="coupon-stat-card active-card">
            <div class="d-flex align-items-center justify-content-between">
                <span class="coupon-stat-label text-success">Đang áp dụng</span>
                <i class="fa-solid fa-circle-check text-success coupon-stat-icon"></i>
            </div>
            <div class="coupon-stat-num text-success" id="count-active">--</div>
            <div class="coupon-stat-sub text-muted">Có thể sử dụng ngay</div>
        </div>
    </div>

    {{-- Sap dien ra --}}
    <div class="col-6 col-lg-auto flex-fill">
        <div class="coupon-stat-card upcoming-card">
            <div class="d-flex align-items-center justify-content-between">
                <span class="coupon-stat-label text-primary">Sắp diễn ra</span>
                <i class="fa-solid fa-calendar-plus text-primary coupon-stat-icon"></i>
            </div>
            <div class="coupon-stat-num text-primary" id="count-upcoming">--</div>
            <div class="coupon-stat-sub text-muted">Chưa tới ngày bắt đầu</div>
        </div>
    </div>

    {{-- Het han / Het luot --}}
    <div class="col-6 col-lg-auto flex-fill">
        <div class="coupon-stat-card expired-card">
            <div class="d-flex align-items-center justify-content-between">
                <span class="coupon-stat-label text-danger">Hết hạn / Hết lượt</span>
                <i class="fa-solid fa-clock-rotate-left text-danger coupon-stat-icon"></i>
            </div>
            <div class="coupon-stat-num text-danger" id="count-expired">--</div>
            <div class="coupon-stat-sub text-muted">Quá hạn hoặc hết lượt</div>
        </div>
    </div>

    {{-- Tong luot su dung --}}
    <div class="col-6 col-lg-auto flex-fill">
        <div class="coupon-stat-card used-card">
            <div class="d-flex align-items-center justify-content-between">
                <span class="coupon-stat-label text-warning">Tổng lượt dùng</span>
                <i class="fa-solid fa-fire text-warning coupon-stat-icon"></i>
            </div>
            <div class="coupon-stat-num text-warning" id="count-used">--</div>
            <div class="coupon-stat-sub text-muted">Khách đã áp dụng</div>
        </div>
    </div>

</div>
