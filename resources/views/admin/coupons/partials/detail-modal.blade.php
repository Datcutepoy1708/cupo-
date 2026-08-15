{{--
    Modal: Xem chi tiet Ma giam gia & Lich su su dung (Coupons / Vouchers)
--}}
<div class="modal fade" id="couponDetailModal" tabindex="-1" aria-labelledby="couponDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">

            {{-- Modal Header --}}
            <div class="modal-header border-bottom px-4 py-3 bg-light">
                <div class="d-flex align-items-center gap-2">
                    <div class="coupon-modal-icon">
                        <i class="fa-solid fa-circle-info"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="couponDetailModalLabel">Chi tiết mã giảm giá</h5>
                        <small class="text-muted">Thông số cấu hình và lịch sử khách hàng áp dụng</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Modal Body --}}
            <div class="modal-body p-4">
                <div class="row g-4">

                    {{-- Khung tong quan ma giam gia --}}
                    <div class="col-12">
                        <div class="coupon-detail-header-card p-3 rounded-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="coupon-code-badge-lg font-monospace fw-bold" id="detailCodeBadge">
                                    CODE
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-1" id="detailDiscountText">Giảm 50.000đ</h5>
                                    <span class="badge" id="detailScopeBadge">Toàn sàn</span>
                                    <span class="badge ms-1" id="detailStatusBadge">Đang áp dụng</span>
                                </div>
                            </div>

                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnCopyDetailCode">
                                <i class="fa-solid fa-copy me-1"></i> Sao chép mã
                            </button>
                        </div>
                    </div>

                    {{-- Thong so ky thuat --}}
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded border h-100">
                            <h6 class="fw-bold fs-7 text-dark mb-3 pb-2 border-bottom">
                                <i class="fa-solid fa-sliders text-primary me-1"></i> Điều kiện & Hạn mức
                            </h6>
                            <table class="table table-sm table-borderless fs-7 mb-0">
                                <tbody>
                                    <tr>
                                        <td class="text-muted" style="width: 140px;">Loại giảm giá:</td>
                                        <td class="fw-semibold text-dark" id="detailType">--</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Giá trị giảm:</td>
                                        <td class="fw-bold text-danger" id="detailValue">--</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Đơn hàng tối thiểu:</td>
                                        <td class="fw-semibold text-dark" id="detailMinOrder">--</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Giảm tối đa:</td>
                                        <td class="fw-semibold text-dark" id="detailMaxDiscount">--</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Gian hàng áp dụng:</td>
                                        <td class="fw-semibold text-dark" id="detailShopName">--</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Thoi gian & Tien do su dung --}}
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded border h-100">
                            <h6 class="fw-bold fs-7 text-dark mb-3 pb-2 border-bottom">
                                <i class="fa-solid fa-chart-pie text-success me-1"></i> Thời gian & Tiến độ
                            </h6>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between fs-7 mb-1">
                                    <span class="text-muted">Lượt đã sử dụng:</span>
                                    <span class="fw-bold" id="detailUsageText">0 / 100 (0%)</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" id="detailProgressBar" role="progressbar" style="width: 0%;"></div>
                                </div>
                            </div>

                            <table class="table table-sm table-borderless fs-7 mb-0">
                                <tbody>
                                    <tr>
                                        <td class="text-muted" style="width: 120px;">Bắt đầu từ:</td>
                                        <td class="fw-semibold text-dark" id="detailStartsAt">--</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Hết hạn lúc:</td>
                                        <td class="fw-semibold text-dark" id="detailExpiresAt">--</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Ngày tạo:</td>
                                        <td class="fw-semibold text-dark" id="detailCreatedAt">--</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Bang Lich su su dung --}}
                    <div class="col-12">
                        <h6 class="fw-bold fs-7 text-dark mb-2">
                            <i class="fa-solid fa-clock-rotate-left text-secondary me-1"></i> Lịch sử sử dụng gần nhất
                        </h6>
                        <div class="table-responsive border rounded bg-white" style="max-height: 200px;">
                            <table class="table table-sm table-hover align-middle mb-0 fs-7">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>#</th>
                                        <th>Khách hàng</th>
                                        <th>Mã đơn hàng</th>
                                        <th>Thời gian áp dụng</th>
                                    </tr>
                                </thead>
                                <tbody id="detailUsagesTableBody">
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">Chưa có lượt sử dụng nào</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="modal-footer border-top px-4 py-3 bg-light d-flex justify-content-between">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary px-4" id="btnEditFromDetail">
                    <i class="fa-solid fa-pen-to-square me-1"></i> Chỉnh sửa voucher này
                </button>
            </div>

        </div>
    </div>
</div>
