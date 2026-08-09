{{--
    Partial: 4 Stat Cards cho Trang Quan ly San pham
--}}
<div class="row g-3 mb-4">

    {{-- Tong san pham --}}
    <div class="col-6 col-md-3">
        <div class="product-stat-card">
            <div class="d-flex align-items-center justify-content-between">
                <span class="product-stat-label">Tổng sản phẩm</span>
                <i class="fa-solid fa-box text-secondary" style="font-size: 18px;"></i>
            </div>
            <div class="product-stat-num" id="count-all">--</div>
            <div class="product-stat-sub text-muted">Toàn bộ trên sàn</div>
        </div>
    </div>

    {{-- Cho duyet --}}
    <div class="col-6 col-md-3">
        <div class="product-stat-card pending">
            <div class="d-flex align-items-center justify-content-between">
                <span class="product-stat-label text-warning-dark">Chờ duyệt</span>
                <i class="fa-solid fa-clock-rotate-left text-warning" style="font-size: 18px;"></i>
            </div>
            <div class="product-stat-num text-warning-dark" id="count-pending">--</div>
            <div class="product-stat-sub text-warning">Cần kiểm duyệt ngay</div>
        </div>
    </div>

    {{-- Da duyet --}}
    <div class="col-6 col-md-3">
        <div class="product-stat-card approved">
            <div class="d-flex align-items-center justify-content-between">
                <span class="product-stat-label text-success">Đã duyệt</span>
                <i class="fa-solid fa-circle-check text-success" style="font-size: 18px;"></i>
            </div>
            <div class="product-stat-num text-success" id="count-approved">--</div>
            <div class="product-stat-sub text-muted">Đang bán bình thường</div>
        </div>
    </div>

    {{-- Tu choi / Go --}}
    <div class="col-6 col-md-3">
        <div class="product-stat-card rejected">
            <div class="d-flex align-items-center justify-content-between">
                <span class="product-stat-label text-danger">Từ chối / Gỡ</span>
                <i class="fa-solid fa-circle-xmark text-danger" style="font-size: 18px;"></i>
            </div>
            <div class="product-stat-num text-danger" id="count-rejected">--</div>
            <div class="product-stat-sub text-muted">Vi phạm quy định</div>
        </div>
    </div>

</div>
