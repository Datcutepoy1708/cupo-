{{--
    Partial: 4 Stat Cards cho Trang Quản lý Banner
--}}
<div class="row g-3 mb-4">

    {{-- Tổng Banner --}}
    <div class="col-6 col-md-3">
        <div class="banner-stat-card">
            <div class="d-flex align-items-center justify-content-between">
                <span class="banner-stat-label">Tổng Banner</span>
                <i class="fa-solid fa-images text-secondary" style="font-size: 18px;"></i>
            </div>
            <div class="banner-stat-num" id="count-all">--</div>
            <div class="banner-stat-sub text-muted">Toàn bộ trên trang</div>
        </div>
    </div>

    {{-- Đang hiển thị --}}
    <div class="col-6 col-md-3">
        <div class="banner-stat-card active-card">
            <div class="d-flex align-items-center justify-content-between">
                <span class="banner-stat-label text-success">Đang hiển thị</span>
                <i class="fa-solid fa-circle-check text-success" style="font-size: 18px;"></i>
            </div>
            <div class="banner-stat-num text-success" id="count-active">--</div>
            <div class="banner-stat-sub text-muted">Hoạt động bình thường</div>
        </div>
    </div>

    {{-- Đã ẩn --}}
    <div class="col-6 col-md-3">
        <div class="banner-stat-card inactive-card">
            <div class="d-flex align-items-center justify-content-between">
                <span class="banner-stat-label text-muted">Đã ẩn</span>
                <i class="fa-solid fa-eye-slash text-muted" style="font-size: 18px;"></i>
            </div>
            <div class="banner-stat-num text-muted" id="count-inactive">--</div>
            <div class="banner-stat-sub text-muted">Tắt hiển thị thủ công</div>
        </div>
    </div>

    {{-- Hết hạn --}}
    <div class="col-6 col-md-3">
        <div class="banner-stat-card expired-card">
            <div class="d-flex align-items-center justify-content-between">
                <span class="banner-stat-label text-danger">Hết hạn</span>
                <i class="fa-solid fa-clock text-danger" style="font-size: 18px;"></i>
            </div>
            <div class="banner-stat-num text-danger" id="count-expired">--</div>
            <div class="banner-stat-sub text-muted">Đã qua ngày kết thúc</div>
        </div>
    </div>

</div>
