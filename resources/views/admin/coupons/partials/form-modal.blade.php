{{--
    Modal: Them moi / Chinh sua Ma giam gia (Coupons / Vouchers)
--}}
<div class="modal fade" id="couponFormModal" tabindex="-1" aria-labelledby="couponFormModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">

            {{-- Modal Header --}}
            <div class="modal-header border-bottom px-4 py-3 bg-light">
                <div class="d-flex align-items-center gap-2">
                    <div class="coupon-modal-icon">
                        <i class="fa-solid fa-ticket"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="couponFormModalLabel">Thêm mã giảm giá mới</h5>
                        <small class="text-muted">Tạo khuyến mãi kích cầu mua sắm cho sàn hoặc gian hàng</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Modal Body --}}
            <div class="modal-body p-4">
                <form id="couponForm" novalidate>
                    <input type="hidden" id="couponId" name="id">

                    <div class="row g-4">

                        {{-- Cot trai: Form nhap lieu --}}
                        <div class="col-lg-7">

                            {{-- 1. Pham vi ap dung --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-dark mb-1">
                                    Phạm vi áp dụng <span class="text-danger">*</span>
                                </label>
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <label class="scope-radio-card active" id="scopePlatformCard">
                                            <input type="radio" name="scope_type" value="platform" checked class="d-none">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="fa-solid fa-globe text-primary"></i>
                                                <div>
                                                    <div class="fw-bold fs-7">Voucher Toàn Sàn</div>
                                                    <small class="text-muted" style="font-size: 11px;">Admin tạo cho mọi Shop</small>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                    <div class="col-6">
                                        <label class="scope-radio-card" id="scopeShopCard">
                                            <input type="radio" name="scope_type" value="shop" class="d-none">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="fa-solid fa-store text-warning"></i>
                                                <div>
                                                    <div class="fw-bold fs-7">Voucher Gian Hàng</div>
                                                    <small class="text-muted" style="font-size: 11px;">Áp dụng riêng 1 Shop</small>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                {{-- Select Gian hang (an neu chon toan san) --}}
                                <div id="sellerSelectWrap" class="mt-2" style="display: none;">
                                    <label for="couponSellerId" class="form-label fs-7 fw-semibold text-muted">
                                        Chọn Gian Hàng / Người Bán <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select form-select-sm" id="couponSellerId" name="seller_id">
                                        <option value="">-- Chọn gian hàng --</option>
                                        @isset($sellers)
                                            @foreach($sellers as $seller)
                                                <option value="{{ $seller->id }}">
                                                    {{ $seller->sellerProfile->shop_name ?? $seller->name }} ({{ $seller->email }})
                                                </option>
                                            @endforeach
                                        @endisset
                                    </select>
                                    <div class="invalid-feedback" id="err-seller_id"></div>
                                </div>
                            </div>

                            {{-- 2. Ma Code & Random Button --}}
                            <div class="mb-3">
                                <label for="couponCode" class="form-label fw-semibold text-dark mb-1">
                                    Mã giảm giá (Code) <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white text-muted">
                                        <i class="fa-solid fa-barcode"></i>
                                    </span>
                                    <input type="text"
                                           class="form-control text-uppercase font-monospace fw-bold"
                                           id="couponCode"
                                           name="code"
                                           placeholder="VD: CUPO50K, FREESHIP..."
                                           maxlength="50"
                                           required>
                                    <button class="btn btn-outline-secondary" type="button" id="btnGenRandomCode" title="Tạo mã ngẫu nhiên">
                                        <i class="fa-solid fa-arrows-rotate me-1"></i> Tự sinh mã
                                    </button>
                                </div>
                                <div class="form-text">Mã viết hoa liền nhau, không dấu (Ví dụ: SALEHE2026, CUPO100K).</div>
                                <div class="invalid-feedback" id="err-code"></div>
                            </div>

                            {{-- 3. Loai giam gia & Gia tri --}}
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark mb-1">
                                        Loại giảm giá <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="couponType" name="type" required>
                                        <option value="fixed_amount">Số tiền cố định (VNĐ) — Giảm tiền hàng</option>
                                        <option value="percentage">Theo phần trăm (%) — Giảm tiền hàng</option>
                                        <option value="free_shipping">Miễn phí vận chuyển — Giảm phí ship</option>
                                    </select>
                                    <div class="invalid-feedback" id="err-type"></div>
                                </div>

                                <div class="col-md-6">
                                    <label for="couponValue" class="form-label fw-semibold text-dark mb-1">
                                        Giá trị giảm <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="number"
                                               step="any"
                                               min="1"
                                               class="form-control fw-bold"
                                               id="couponValue"
                                               name="value"
                                               placeholder="VD: 50000 hoặc 15"
                                               required>
                                        <span class="input-group-text bg-light fw-bold" id="couponValueUnit">đ</span>
                                    </div>
                                    <div class="invalid-feedback" id="err-value"></div>
                                </div>
                            </div>

                            {{-- 4. Don toi thieu & Giam toi da --}}
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="couponMinOrder" class="form-label fw-semibold text-dark mb-1">
                                        Đơn hàng tối thiểu (VNĐ)
                                    </label>
                                    <div class="input-group">
                                        <input type="number"
                                               step="any"
                                               min="0"
                                               class="form-control"
                                               id="couponMinOrder"
                                               name="min_order_amount"
                                               placeholder="0"
                                               value="0">
                                        <span class="input-group-text bg-light">đ</span>
                                    </div>
                                    <div class="form-text">0 = Không giới hạn đơn tối thiểu.</div>
                                    <div class="invalid-feedback" id="err-min_order_amount"></div>
                                </div>

                                <div class="col-md-6" id="maxDiscountWrap" style="display: none;">
                                    <label for="couponMaxDiscount" class="form-label fw-semibold text-dark mb-1">
                                        Mức giảm tối đa (VNĐ)
                                    </label>
                                    <div class="input-group">
                                        <input type="number"
                                               step="any"
                                               min="0"
                                               class="form-control"
                                               id="couponMaxDiscount"
                                               name="max_discount"
                                               placeholder="Bỏ trống nếu không giới hạn">
                                        <span class="input-group-text bg-light">đ</span>
                                    </div>
                                    <div class="form-text">Chặn mức giảm trần khi giảm theo %.</div>
                                    <div class="invalid-feedback" id="err-max_discount"></div>
                                </div>
                            </div>

                            {{-- 5. Gioi han luot su dung --}}
                            <div class="mb-3">
                                <label for="couponUsageLimit" class="form-label fw-semibold text-dark mb-1">
                                    Tổng số lượt sử dụng tối đa <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white text-muted">
                                        <i class="fa-solid fa-users"></i>
                                    </span>
                                    <input type="number"
                                           min="1"
                                           class="form-control"
                                           id="couponUsageLimit"
                                           name="usage_limit"
                                           value="100"
                                           required>
                                    <span class="input-group-text bg-light">lượt</span>
                                </div>
                                <div class="invalid-feedback" id="err-usage_limit"></div>
                            </div>

                            {{-- 6. Thoi gian hieu luc --}}
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="couponStartsAt" class="form-label fw-semibold text-dark mb-1">
                                        Bắt đầu từ
                                    </label>
                                    <input type="datetime-local"
                                           class="form-control"
                                           id="couponStartsAt"
                                           name="starts_at">
                                    <div class="form-text">Bỏ trống để kích hoạt ngay.</div>
                                    <div class="invalid-feedback" id="err-starts_at"></div>
                                </div>

                                <div class="col-md-6">
                                    <label for="couponExpiresAt" class="form-label fw-semibold text-dark mb-1">
                                        Hết hạn lúc
                                    </label>
                                    <input type="datetime-local"
                                           class="form-control"
                                           id="couponExpiresAt"
                                           name="expires_at">
                                    <div class="form-text">Bỏ trống nếu voucher vô thời hạn.</div>
                                    <div class="invalid-feedback" id="err-expires_at"></div>
                                </div>
                            </div>

                            {{-- 7. Trang thai kich hoat --}}
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="couponStatus" name="status" value="1" checked>
                                <label class="form-check-label fw-semibold text-dark" for="couponStatus">
                                    Kích hoạt mã giảm giá này ngay sau khi lưu
                                </label>
                            </div>

                        </div>

                        {{-- Cot phai: Live Interactive Voucher Preview Widget --}}
                        <div class="col-lg-5">
                            <div class="coupon-preview-container p-3 rounded-3 bg-light border h-100 d-flex flex-column">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <span class="fs-7 fw-bold text-uppercase text-muted">
                                        <i class="fa-solid fa-eye me-1"></i> Xem trước hiển thị Voucher
                                    </span>
                                    <span class="badge bg-primary" id="previewBadgeType">Voucher Toàn Sàn</span>
                                </div>

                                {{-- The Voucher Simulator --}}
                                <div class="voucher-card-sim" id="voucherCardSim">
                                    <div class="voucher-sim-left">
                                        <div class="voucher-sim-icon">
                                            <i class="fa-solid fa-gift" id="voucherSimIcon"></i>
                                        </div>
                                        <span class="voucher-sim-brand" id="voucherSimBrand">CUPO MALL</span>
                                    </div>
                                    <div class="voucher-sim-right">
                                        <div class="voucher-sim-discount" id="voucherSimDiscount">Giảm 50.000đ</div>
                                        <div class="voucher-sim-min" id="voucherSimMin">Đơn tối thiểu 0đ</div>
                                        <div class="voucher-sim-max text-muted" id="voucherSimMax" style="display:none;"></div>
                                        <div class="voucher-sim-footer">
                                            <span class="voucher-sim-code" id="voucherSimCode">CUPO50K</span>
                                            <span class="voucher-sim-expiry" id="voucherSimExpiry">HSD: Vô thời hạn</span>
                                        </div>
                                    </div>
                                    <div class="voucher-sim-sawtooth"></div>
                                </div>

                                {{-- Bang thong so tom tat --}}
                                <div class="voucher-summary-box mt-4 p-3 bg-white rounded border flex-fill">
                                    <h6 class="fw-bold fs-7 text-dark mb-2 pb-2 border-bottom">Tóm tắt điều kiện mã</h6>
                                    <ul class="list-unstyled mb-0 fs-7 text-secondary space-y-1">
                                        <li class="d-flex justify-content-between py-1">
                                            <span>Mã code:</span>
                                            <strong class="text-primary font-monospace" id="sumCode">CUPO50K</strong>
                                        </li>
                                        <li class="d-flex justify-content-between py-1">
                                            <span>Mức giảm:</span>
                                            <strong class="text-danger" id="sumDiscount">50.000đ</strong>
                                        </li>
                                        <li class="d-flex justify-content-between py-1">
                                            <span>Phạm vi:</span>
                                            <span class="badge bg-secondary" id="sumScope">Toàn sàn</span>
                                        </li>
                                        <li class="d-flex justify-content-between py-1">
                                            <span>Lượt phát hành:</span>
                                            <span id="sumLimit">100 lượt</span>
                                        </li>
                                        <li class="d-flex justify-content-between py-1">
                                            <span>Thời gian:</span>
                                            <span id="sumDuration">Không thời hạn</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                    </div>
                </form>
            </div>

            {{-- Modal Footer --}}
            <div class="modal-footer border-top px-4 py-3 bg-light d-flex justify-content-between">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary px-4 fw-bold" id="btnSaveCoupon">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Lưu mã giảm giá
                </button>
            </div>

        </div>
    </div>
</div>
