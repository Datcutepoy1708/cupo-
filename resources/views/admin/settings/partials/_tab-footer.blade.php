{{-- Tab 7: Chân Trang & Thông Tin Liên Hệ --}}
<div class="settings-tab-pane" id="tab-footer">
    <div class="settings-section-header">
        <h5 class="settings-section-title">
            <i class="fa-solid fa-shoe-prints text-danger me-2"></i>Chân Trang (Footer) & Thông Tin Liên Hệ
        </h5>
        <p class="settings-section-desc">Cấu hình thông tin pháp lý doanh nghiệp, hotline CSKH, chứng nhận Bộ Công Thương và mạng xã hội hiển thị ở chân trang sàn.</p>
    </div>

    <input type="hidden" name="_tab_bct_registered" value="1">
    <input type="hidden" name="_tab_dmca_protected" value="1">

    <div class="row g-4">
        {{-- Tên công ty --}}
        <div class="col-md-6">
            <label class="form-label fw-bold" for="company_name">Tên Công ty / Đơn vị chủ quản sàn <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="company_name" name="company_name"
                   value="{{ old('company_name', $settings['company_name'] ?? 'Công ty Cổ phần Thương Mại Điện Tử Cupo Việt Nam') }}"
                   placeholder="VD: Công ty Cổ phần TMĐT Cupo Việt Nam">
            <div class="form-text">Tên pháp nhân hiển thị ở dòng thông tin pháp lý chân trang.</div>
        </div>

        {{-- Giấy phép ĐKKD / MST --}}
        <div class="col-md-6">
            <label class="form-label fw-bold" for="business_license">Số ĐKKD / Mã số thuế doanh nghiệp</label>
            <input type="text" class="form-control" id="business_license" name="business_license"
                   value="{{ old('business_license', $settings['business_license'] ?? 'Mã số doanh nghiệp: 0109876543 do Sở Kế hoạch & Đầu tư TP. Hà Nội cấp') }}"
                   placeholder="VD: Mã số doanh nghiệp: 0109876543 do Sở KH&ĐT cấp...">
            <div class="form-text">Mã số thuế & nơi cấp giấy phép kinh doanh theo quy định TMĐT.</div>
        </div>

        {{-- Slogan / Mô tả ngắn chân trang --}}
        <div class="col-12">
            <label class="form-label fw-bold" for="footer_slogan">Mô tả ngắn / Slogan chân trang</label>
            <textarea class="form-control" id="footer_slogan" name="footer_slogan" rows="2"
                      placeholder="Mô tả tóm tắt về sứ mệnh và tiện ích mua sắm của sàn...">{{ old('footer_slogan', $settings['footer_slogan'] ?? 'Cupo — Nền tảng sàn thương mại điện tử mua sắm trực tuyến hàng đầu, kết nối hàng triệu người mua và người bán uy tín trên toàn quốc.') }}</textarea>
        </div>

        <hr class="my-2">

        {{-- Hotline CSKH --}}
        <div class="col-md-4">
            <label class="form-label fw-bold" for="contact_phone_footer">Hotline CSKH / Khiếu nại</label>
            <input type="text" class="form-control" id="contact_phone_footer" name="contact_phone"
                   value="{{ old('contact_phone', $settings['contact_phone'] ?? '1900 8888') }}"
                   placeholder="VD: 1900 8888">
        </div>

        {{-- Email hỗ trợ --}}
        <div class="col-md-4">
            <label class="form-label fw-bold" for="contact_email_footer">Email tiếp nhận hỗ trợ</label>
            <input type="email" class="form-control" id="contact_email_footer" name="contact_email"
                   value="{{ old('contact_email', $settings['contact_email'] ?? 'support@cupo.vn') }}"
                   placeholder="VD: support@cupo.vn">
        </div>

        {{-- Giờ làm việc --}}
        <div class="col-md-4">
            <label class="form-label fw-bold" for="working_hours">Khung giờ làm việc / Trực tổng đài</label>
            <input type="text" class="form-control" id="working_hours" name="working_hours"
                   value="{{ old('working_hours', $settings['working_hours'] ?? '08:00 - 22:00 hàng ngày') }}"
                   placeholder="VD: 08:00 - 22:00 hàng ngày">
        </div>

        {{-- Địa chỉ trụ sở --}}
        <div class="col-12">
            <label class="form-label fw-bold" for="contact_address_footer">Địa chỉ trụ sở / Tiếp nhận đổi trả</label>
            <input type="text" class="form-control" id="contact_address_footer" name="contact_address"
                   value="{{ old('contact_address', $settings['contact_address'] ?? 'Tầng 12, Tòa nhà Cupo Tower, Cầu Giấy, Hà Nội') }}"
                   placeholder="Địa chỉ trụ sở chính...">
        </div>

        <hr class="my-2">

        {{-- Chứng nhận Bộ Công Thương & DMCA --}}
        <div class="col-md-6">
            <label class="form-label fw-bold">Chứng nhận Pháp lý Sàn</label>
            <div class="p-3 bg-light rounded-3 border">
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" role="switch" id="bct_registered" name="bct_registered" value="1"
                           {{ (!empty($settings['bct_registered']) && $settings['bct_registered'] == '1') ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="bct_registered">
                        <i class="fa-solid fa-shield-halved text-danger me-1"></i> Hiển thị huy hiệu "Đã đăng ký Bộ Công Thương"
                    </label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="dmca_protected" name="dmca_protected" value="1"
                           {{ (!empty($settings['dmca_protected']) && $settings['dmca_protected'] == '1') ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="dmca_protected">
                        <i class="fa-solid fa-lock text-primary me-1"></i> Hiển thị huy hiệu "DMCA Protected"
                    </label>
                </div>
            </div>
        </div>

        {{-- Dòng chữ bản quyền --}}
        <div class="col-md-6">
            <label class="form-label fw-bold" for="copyright_text">Dòng chữ bản quyền (Copyright)</label>
            <input type="text" class="form-control" id="copyright_text" name="copyright_text"
                   value="{{ old('copyright_text', $settings['copyright_text'] ?? '© 2026 Cupo. Tất cả quyền được bảo lưu.') }}"
                   placeholder="VD: © 2026 Cupo. Tất cả quyền được bảo lưu.">
            <div class="form-text">Dòng bản quyền hiển thị ở thanh đáy chân trang.</div>
        </div>
    </div>
</div>
