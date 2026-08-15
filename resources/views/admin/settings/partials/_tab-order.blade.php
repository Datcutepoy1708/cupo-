{{-- Tab 3: Đơn hàng & Vận chuyển --}}
<div class="settings-tab-pane" id="tab-order">
    <div class="settings-section-header">
        <h5 class="settings-section-title">
            <i class="fa-solid fa-truck-fast text-danger me-2"></i>Đơn Hàng & Vận Chuyển
        </h5>
        <p class="settings-section-desc">Cấu hình phí giao hàng tiêu chuẩn, ngưỡng miễn phí vận chuyển và thời hạn xử lý đơn hàng.</p>
    </div>

    <input type="hidden" name="_tab_enable_cod" value="1">

    <div class="row g-4">
        {{-- Phí ship mặc định & Freeship threshold --}}
        <div class="col-md-6">
            <label class="form-label fw-bold" for="default_shipping_fee">Phí giao hàng tiêu chuẩn (VNĐ)</label>
            <div class="input-group">
                <input type="number" min="0" step="1000" class="form-control" id="default_shipping_fee" name="default_shipping_fee"
                       value="{{ old('default_shipping_fee', $settings['default_shipping_fee'] ?? '30000') }}">
                <span class="input-group-text">đ</span>
            </div>
            <div class="form-text">Mức phí áp dụng cho các đơn hàng thông thường trên sàn.</div>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-bold" for="free_shipping_threshold">Ngưỡng miễn phí vận chuyển (Freeship)</label>
            <div class="input-group">
                <input type="number" min="0" step="10000" class="form-control" id="free_shipping_threshold" name="free_shipping_threshold"
                       value="{{ old('free_shipping_threshold', $settings['free_shipping_threshold'] ?? '300000') }}">
                <span class="input-group-text">đ</span>
            </div>
            <div class="form-text">Đơn hàng có tổng tiền hàng đạt hoặc vượt mức này sẽ được miễn phí giao hàng (nhập 0 để tắt).</div>
        </div>

        <hr class="my-2">

        {{-- Thời gian tự hủy đơn & COD --}}
        <div class="col-md-6">
            <label class="form-label fw-bold" for="auto_cancel_pending_hours">Thời hạn chờ xác nhận đơn (Giờ)</label>
            <div class="input-group">
                <input type="number" min="1" max="168" class="form-control" id="auto_cancel_pending_hours" name="auto_cancel_pending_hours"
                       value="{{ old('auto_cancel_pending_hours', $settings['auto_cancel_pending_hours'] ?? '48') }}">
                <span class="input-group-text">giờ</span>
            </div>
            <div class="form-text">Nếu Seller không xác nhận đơn trong khoảng thời gian này, hệ thống sẽ tự động hủy đơn.</div>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-bold">Phương thức Thanh toán khi nhận hàng (COD)</label>
            <div class="p-3 bg-light rounded-3 border">
                <div class="form-check form-switch mb-1">
                    <input class="form-check-input" type="checkbox" role="switch" id="enable_cod" name="enable_cod" value="1"
                           {{ (!empty($settings['enable_cod']) && $settings['enable_cod'] == '1') ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="enable_cod">
                        Cho phép thanh toán COD trên toàn sàn
                    </label>
                </div>
                <div class="form-text text-muted small">Khách hàng có thể chọn thanh toán tiền mặt khi shipper giao hàng tới nơi.</div>
            </div>
        </div>
    </div>
</div>
