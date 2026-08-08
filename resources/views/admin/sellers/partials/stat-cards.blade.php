{{--
    Partial: Stat cards tong quan Seller
    Hien thi 4 so dem: Tong / Cho duyet / Da duyet / Da khoa
    Du lieu duoc cap nhat dong boi sellers.js qua id: count-all, count-pending, count-approved, count-blocked
--}}
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="seller-stat-card">
            <span class="seller-stat-num" id="count-all">--</span>
            <span class="seller-stat-label">Tổng số gian hàng</span>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="seller-stat-card pending">
            <span class="seller-stat-num" id="count-pending">--</span>
            <span class="seller-stat-label">Chờ duyệt</span>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="seller-stat-card approved">
            <span class="seller-stat-num" id="count-approved">--</span>
            <span class="seller-stat-label">Đã duyệt</span>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="seller-stat-card blocked">
            <span class="seller-stat-num" id="count-blocked">--</span>
            <span class="seller-stat-label">Đã khóa / Từ chối</span>
        </div>
    </div>
</div>
