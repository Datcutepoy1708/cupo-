{{--
    Partial: Stat cards tổng quan Kháng nghị & Hỗ trợ Seller
    Hiển thị 5 số đếm: Tổng / Mới mở / Đang xử lý / Đã giải quyết / Đã đóng
--}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl">
        <div class="ticket-stat-card">
            <div class="ticket-stat-icon">
                <i class="fa-solid fa-headset"></i>
            </div>
            <div class="ticket-stat-body">
                <span class="ticket-stat-num" id="count-all">--</span>
                <span class="ticket-stat-label">Tổng yêu cầu</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="ticket-stat-card open-card">
            <div class="ticket-stat-icon">
                <i class="fa-solid fa-envelope-open-text"></i>
            </div>
            <div class="ticket-stat-body">
                <span class="ticket-stat-num" id="count-open">--</span>
                <span class="ticket-stat-label">Mới mở (Chờ)</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="ticket-stat-card review-card">
            <div class="ticket-stat-icon">
                <i class="fa-solid fa-arrows-rotate"></i>
            </div>
            <div class="ticket-stat-body">
                <span class="ticket-stat-num" id="count-in-review">--</span>
                <span class="ticket-stat-label">Đang xử lý</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-6 col-xl">
        <div class="ticket-stat-card resolved-card">
            <div class="ticket-stat-icon">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div class="ticket-stat-body">
                <span class="ticket-stat-num" id="count-resolved">--</span>
                <span class="ticket-stat-label">Đã giải quyết</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-6 col-xl">
        <div class="ticket-stat-card closed-card">
            <div class="ticket-stat-icon">
                <i class="fa-solid fa-lock"></i>
            </div>
            <div class="ticket-stat-body">
                <span class="ticket-stat-num" id="count-closed">--</span>
                <span class="ticket-stat-label">Đã đóng</span>
            </div>
        </div>
    </div>
</div>
