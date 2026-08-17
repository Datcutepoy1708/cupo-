{{--
    Partial: Stat cards tổng quan Tranh chấp / Khiếu nại
    Hiển thị 5 số đếm: Tổng / Chờ xử lý / Đang xử lý / Đã hoàn tiền / Đã từ chối
    Dữ liệu được cập nhật động bởi disputes.js qua id:
    count-all, count-pending, count-in-progress, count-refunded, count-rejected
--}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl">
        <div class="dispute-stat-card">
            <div class="dispute-stat-icon">
                <i class="fa-solid fa-scale-balanced"></i>
            </div>
            <div class="dispute-stat-body">
                <span class="dispute-stat-num" id="count-all">--</span>
                <span class="dispute-stat-label">Tổng khiếu nại</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="dispute-stat-card pending">
            <div class="dispute-stat-icon">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div class="dispute-stat-body">
                <span class="dispute-stat-num" id="count-pending">--</span>
                <span class="dispute-stat-label">Chờ xử lý</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="dispute-stat-card progress-card">
            <div class="dispute-stat-icon">
                <i class="fa-solid fa-spinner"></i>
            </div>
            <div class="dispute-stat-body">
                <span class="dispute-stat-num" id="count-in-progress">--</span>
                <span class="dispute-stat-label">Đang xử lý</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-6 col-xl">
        <div class="dispute-stat-card refunded">
            <div class="dispute-stat-icon">
                <i class="fa-solid fa-money-bill-transfer"></i>
            </div>
            <div class="dispute-stat-body">
                <span class="dispute-stat-num" id="count-refunded">--</span>
                <span class="dispute-stat-label">Đã hoàn tiền</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-6 col-xl">
        <div class="dispute-stat-card rejected">
            <div class="dispute-stat-icon">
                <i class="fa-solid fa-circle-xmark"></i>
            </div>
            <div class="dispute-stat-body">
                <span class="dispute-stat-num" id="count-rejected">--</span>
                <span class="dispute-stat-label">Đã từ chối</span>
            </div>
        </div>
    </div>
</div>
