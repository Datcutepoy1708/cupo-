{{--
    Partial: Stat cards tổng quan Khách hàng
    Hiển thị 4 số đếm: Tổng / Active / Bị khóa / Mới 30 ngày
    Dữ liệu được cập nhật động bởi customers.js qua id:
    count-all, count-active, count-blocked, count-new-30d
--}}
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="customer-stat-card">
            <div class="customer-stat-icon">
                <i class="fa-solid fa-users"></i>
            </div>
            <div class="customer-stat-body">
                <span class="customer-stat-num" id="count-all">--</span>
                <span class="customer-stat-label">Tổng khách hàng</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="customer-stat-card active">
            <div class="customer-stat-icon">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div class="customer-stat-body">
                <span class="customer-stat-num" id="count-active">--</span>
                <span class="customer-stat-label">Đang hoạt động</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="customer-stat-card blocked">
            <div class="customer-stat-icon">
                <i class="fa-solid fa-ban"></i>
            </div>
            <div class="customer-stat-body">
                <span class="customer-stat-num" id="count-blocked">--</span>
                <span class="customer-stat-label">Tài khoản bị khóa</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="customer-stat-card new">
            <div class="customer-stat-icon">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <div class="customer-stat-body">
                <span class="customer-stat-num" id="count-new-30d">--</span>
                <span class="customer-stat-label">Mới trong 30 ngày</span>
            </div>
        </div>
    </div>
</div>
