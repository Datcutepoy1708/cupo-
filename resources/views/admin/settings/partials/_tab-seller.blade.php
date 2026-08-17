{{-- Tab 2: Gian hàng, Hoa hồng & Rút tiền --}}
<div class="settings-tab-pane" id="tab-seller">
    <div class="settings-section-header">
        <h5 class="settings-section-title">
            <i class="fa-solid fa-store text-danger me-2"></i>Gian Hàng & Tài Chính Seller
        </h5>
        <p class="settings-section-desc">Cấu hình mức chiết khấu hoa hồng sàn, duyệt gian hàng và hạn mức rút tiền của người bán.</p>
    </div>

    <input type="hidden" name="_tab_auto_approve_sellers" value="1">

    <div class="row g-4">
        {{-- Hoa hồng mặc định --}}
        <div class="col-md-6">
            <label class="form-label fw-bold" for="default_commission_rate">Tỷ lệ hoa hồng mặc định (%) <span class="text-danger">*</span></label>
            <div class="input-group">
                <input type="number" step="0.1" min="0" max="100" class="form-control" id="default_commission_rate" name="default_commission_rate"
                       value="{{ old('default_commission_rate', $settings['default_commission_rate'] ?? '10') }}" required>
                <span class="input-group-text">%</span>
            </div>
            <div class="form-text">Tỷ lệ sàn trích thu trên mỗi đơn hàng thành công khi tạo gian hàng mới.</div>
        </div>

        {{-- Duyệt tự động --}}
        <div class="col-md-6">
            <label class="form-label fw-bold">Quy trình duyệt Seller đăng ký</label>
            <div class="p-3 bg-light rounded-3 border">
                <div class="form-check form-switch mb-1">
                    <input class="form-check-input" type="checkbox" role="switch" id="auto_approve_sellers" name="auto_approve_sellers" value="1"
                           {{ (!empty($settings['auto_approve_sellers']) && $settings['auto_approve_sellers'] == '1') ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="auto_approve_sellers">
                        Tự động duyệt gian hàng khi đăng ký
                    </label>
                </div>
                <div class="form-text text-muted small">Nếu tắt, Admin phải kiểm tra hồ sơ và duyệt thủ công tại trang "Gian hàng & Seller".</div>
            </div>
        </div>

        <hr class="my-2">

        {{-- Hạn mức rút tiền --}}
        <div class="col-md-4">
            <label class="form-label fw-bold" for="min_withdrawal_amount">Hạn mức rút tiền tối thiểu (VNĐ)</label>
            <div class="input-group">
                <input type="number" min="10000" step="1000" class="form-control" id="min_withdrawal_amount" name="min_withdrawal_amount"
                       value="{{ old('min_withdrawal_amount', $settings['min_withdrawal_amount'] ?? '50000') }}">
                <span class="input-group-text">đ</span>
            </div>
            <div class="form-text">Số dư ví tối thiểu để Seller có thể gửi yêu cầu rút tiền.</div>
        </div>

        <div class="col-md-4">
            <label class="form-label fw-bold" for="max_withdrawal_amount">Hạn mức rút tiền tối đa / lần (VNĐ)</label>
            <div class="input-group">
                <input type="number" min="10000" step="10000" class="form-control" id="max_withdrawal_amount" name="max_withdrawal_amount"
                       value="{{ old('max_withdrawal_amount', $settings['max_withdrawal_amount'] ?? '50000000') }}">
                <span class="input-group-text">đ</span>
            </div>
            <div class="form-text">Giới hạn số tiền tối đa cho 1 lượt rút tiền.</div>
        </div>

        <div class="col-md-4">
            <label class="form-label fw-bold" for="escrow_holding_days">Thời gian giữ tiền đối soát (Ngày)</label>
            <div class="input-group">
                <input type="number" min="0" max="30" class="form-control" id="escrow_holding_days" name="escrow_holding_days"
                       value="{{ old('escrow_holding_days', $settings['escrow_holding_days'] ?? '3') }}">
                <span class="input-group-text">ngày</span>
            </div>
            <div class="form-text">Số ngày giữ tiền sau khi đơn giao thành công trước khi cộng vào ví Seller (phục vụ khiếu nại).</div>
        </div>
    </div>
</div>
