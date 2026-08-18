{{--
    Partial: Stat cards tổng quan Yêu cầu Rút tiền
    Hiển thị: Tổng yêu cầu / Chờ duyệt / Đã duyệt / Đã từ chối / Tổng tiền đã chi
--}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl">
        <div class="withdrawal-stat-card">
            <div class="withdrawal-stat-icon">
                <i class="fa-solid fa-money-bill-transfer"></i>
            </div>
            <div class="withdrawal-stat-body">
                <span class="withdrawal-stat-num" id="count-all">--</span>
                <span class="withdrawal-stat-label">Tổng yêu cầu</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="withdrawal-stat-card pending-card">
            <div class="withdrawal-stat-icon">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div class="withdrawal-stat-body">
                <span class="withdrawal-stat-num" id="count-pending">--</span>
                <span class="withdrawal-stat-label">Chờ duyệt</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="withdrawal-stat-card approved-card">
            <div class="withdrawal-stat-icon">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div class="withdrawal-stat-body">
                <span class="withdrawal-stat-num" id="count-approved">--</span>
                <span class="withdrawal-stat-label">Đã duyệt</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-6 col-xl">
        <div class="withdrawal-stat-card rejected-card">
            <div class="withdrawal-stat-icon">
                <i class="fa-solid fa-circle-xmark"></i>
            </div>
            <div class="withdrawal-stat-body">
                <span class="withdrawal-stat-num" id="count-rejected">--</span>
                <span class="withdrawal-stat-label">Đã từ chối</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-6 col-xl">
        <div class="withdrawal-stat-card paid-card">
            <div class="withdrawal-stat-icon">
                <i class="fa-solid fa-vault"></i>
            </div>
            <div class="withdrawal-stat-body">
                <span class="withdrawal-stat-num" id="count-total-paid">--</span>
                <span class="withdrawal-stat-label">Tổng tiền đã chi trả</span>
            </div>
        </div>
    </div>
</div>
