{{-- Tab 4: Cổng thanh toán Online --}}
<div class="settings-tab-pane" id="tab-payment">
    <div class="settings-section-header">
        <h5 class="settings-section-title">
            <i class="fa-solid fa-credit-card text-danger me-2"></i>Cổng Thanh Toán Trực Tuyến
        </h5>
        <p class="settings-section-desc">Cấu hình API kết nối các cổng thanh toán ngân hàng (VNPay) và ví điện tử (MoMo).</p>
    </div>

    <input type="hidden" name="_tab_enable_vnpay" value="1">
    <input type="hidden" name="_tab_vnpay_sandbox" value="1">
    <input type="hidden" name="_tab_enable_momo" value="1">
    <input type="hidden" name="_tab_momo_sandbox" value="1">

    <div class="row g-4">
        {{-- VNPay Configuration --}}
        <div class="col-12">
            <div class="card border p-3 rounded-3 shadow-none">
                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-building-columns text-primary fs-5"></i>
                        <h6 class="fw-bold mb-0">Cổng thanh toán VNPay</h6>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" role="switch" id="enable_vnpay" name="enable_vnpay" value="1"
                               {{ (!empty($settings['enable_vnpay']) && $settings['enable_vnpay'] == '1') ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold text-success" for="enable_vnpay">Kích hoạt VNPay</label>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label small fw-bold" for="vnpay_tmn_code">Mã Website (TMN Code)</label>
                        <input type="text" class="form-control form-control-sm" id="vnpay_tmn_code" name="vnpay_tmn_code"
                               value="{{ old('vnpay_tmn_code', $settings['vnpay_tmn_code'] ?? '') }}" placeholder="VD: CUPOVN01">
                    </div>

                    <div class="col-md-5">
                        <label class="form-label small fw-bold" for="vnpay_hash_secret">Chuỗi bí mật (Hash Secret)</label>
                        <div class="input-group input-group-sm">
                            <input type="password" class="form-control password-toggle-input" id="vnpay_hash_secret" name="vnpay_hash_secret"
                                   value="{{ old('vnpay_hash_secret', $settings['vnpay_hash_secret'] ?? '') }}" placeholder="Nhập Hash Secret">
                            <button class="btn btn-outline-secondary btn-toggle-password" type="button" title="Hiện/Ẩn">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <div class="form-check form-switch mb-1">
                            <input class="form-check-input" type="checkbox" role="switch" id="vnpay_sandbox" name="vnpay_sandbox" value="1"
                                   {{ (!empty($settings['vnpay_sandbox']) && $settings['vnpay_sandbox'] == '1') ? 'checked' : '' }}>
                            <label class="form-check-label small" for="vnpay_sandbox">Sandbox</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- MoMo Configuration --}}
        <div class="col-12">
            <div class="card border p-3 rounded-3 shadow-none">
                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-wallet text-danger fs-5"></i>
                        <h6 class="fw-bold mb-0">Cổng thanh toán Ví MoMo</h6>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" role="switch" id="enable_momo" name="enable_momo" value="1"
                               {{ (!empty($settings['enable_momo']) && $settings['enable_momo'] == '1') ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold text-danger" for="enable_momo">Kích hoạt MoMo</label>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold" for="momo_partner_code">Partner Code</label>
                        <input type="text" class="form-control form-control-sm" id="momo_partner_code" name="momo_partner_code"
                               value="{{ old('momo_partner_code', $settings['momo_partner_code'] ?? '') }}" placeholder="VD: MOMO...">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold" for="momo_access_key">Access Key</label>
                        <input type="text" class="form-control form-control-sm" id="momo_access_key" name="momo_access_key"
                               value="{{ old('momo_access_key', $settings['momo_access_key'] ?? '') }}" placeholder="Nhập Access Key">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-bold" for="momo_secret_key">Secret Key</label>
                        <div class="input-group input-group-sm">
                            <input type="password" class="form-control password-toggle-input" id="momo_secret_key" name="momo_secret_key"
                                   value="{{ old('momo_secret_key', $settings['momo_secret_key'] ?? '') }}" placeholder="Nhập Secret Key">
                            <button class="btn btn-outline-secondary btn-toggle-password" type="button" title="Hiện/Ẩn">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="col-md-1 d-flex align-items-end">
                        <div class="form-check form-switch mb-1">
                            <input class="form-check-input" type="checkbox" role="switch" id="momo_sandbox" name="momo_sandbox" value="1"
                                   {{ (!empty($settings['momo_sandbox']) && $settings['momo_sandbox'] == '1') ? 'checked' : '' }}>
                            <label class="form-check-label small" for="momo_sandbox">Sandbox</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
