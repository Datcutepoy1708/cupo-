{{--
    Partial: Stat cards tong quan Don Hang
    Du lieu cap nhat dong boi orders.js qua id:
    order-count-all, order-count-pending-payment, order-count-shipping, order-count-completed, order-count-cancelled
--}}
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="seller-stat-card">
            <span class="seller-stat-num" id="order-count-all">--</span>
            <span class="seller-stat-label">Tổng đơn hàng</span>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="seller-stat-card pending">
            <span class="seller-stat-num" id="order-count-pending-payment">--</span>
            <span class="seller-stat-label">Chờ thanh toán</span>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="seller-stat-card" style="border-left: 4px solid #2196f3;">
            <span class="seller-stat-num" id="order-count-shipping">--</span>
            <span class="seller-stat-label">Đang giao hàng</span>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="seller-stat-card approved">
            <span class="seller-stat-num" id="order-count-completed">--</span>
            <span class="seller-stat-label">Hoàn thành</span>
        </div>
    </div>
</div>
