{{-- Tab 1: Cài đặt Chung --}}
<div class="settings-tab-pane active" id="tab-general">
    <div class="settings-section-header">
        <h5 class="settings-section-title">
            <i class="fa-solid fa-sliders text-danger me-2"></i>Cài đặt Chung
        </h5>
        <p class="settings-section-desc">Cấu hình tên sàn, nhận diện thương hiệu, thông tin liên hệ và chế độ bảo trì.</p>
    </div>

    <input type="hidden" name="_tab_maintenance_mode" value="1">

    <div class="row g-4">
        {{-- Tên sàn & Tagline --}}
        <div class="col-md-6">
            <label class="form-label fw-bold" for="site_name">Tên sàn thương mại điện tử <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="site_name" name="site_name"
                   value="{{ old('site_name', $settings['site_name'] ?? '') }}" required>
            <div class="form-text">Hiển thị trên thanh tiêu đề và các thông báo hệ thống.</div>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-bold" for="site_tagline">Khẩu hiệu / Slogan</label>
            <input type="text" class="form-control" id="site_tagline" name="site_tagline"
                   value="{{ old('site_tagline', $settings['site_tagline'] ?? '') }}">
            <div class="form-text">Mô tả ngắn gọn về sứ mệnh của sàn.</div>
        </div>

        {{-- Logo & Favicon upload --}}
        <div class="col-md-6">
            <label class="form-label fw-bold">Logo chính sàn</label>
            <div class="image-upload-box" id="logoUploadBox">
                <div class="image-preview-wrap">
                    @if(!empty($settings['site_logo']))
                        <img src="{{ asset('storage/' . $settings['site_logo']) }}" alt="Site Logo" id="logoPreview" class="preview-img">
                    @else
                        <img src="" alt="Site Logo" id="logoPreview" class="preview-img d-none">
                        <div class="upload-placeholder" id="logoPlaceholder">
                            <i class="fa-solid fa-cloud-arrow-up fa-2x mb-2 text-muted"></i>
                            <div class="small text-muted">Bấm để tải ảnh Logo (PNG, JPG, WebP, SVG)</div>
                        </div>
                    @endif
                </div>
                <input type="file" class="form-control d-none file-input-preview" id="site_logo" name="site_logo" accept="image/*" data-preview="#logoPreview" data-placeholder="#logoPlaceholder">
                <button type="button" class="btn btn-sm btn-outline-secondary mt-2 btn-trigger-upload" data-target="#site_logo">
                    <i class="fa-solid fa-camera me-1"></i> Chọn Logo mới
                </button>
            </div>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-bold">Favicon (Biểu tượng trình duyệt)</label>
            <div class="image-upload-box" id="faviconUploadBox">
                <div class="image-preview-wrap favicon-wrap">
                    @if(!empty($settings['site_favicon']))
                        <img src="{{ asset('storage/' . $settings['site_favicon']) }}" alt="Site Favicon" id="faviconPreview" class="preview-img favicon-img">
                    @else
                        <img src="" alt="Site Favicon" id="faviconPreview" class="preview-img favicon-img d-none">
                        <div class="upload-placeholder" id="faviconPlaceholder">
                            <i class="fa-solid fa-image fa-2x mb-2 text-muted"></i>
                            <div class="small text-muted">Tải ảnh Favicon (ICO, PNG, SVG)</div>
                        </div>
                    @endif
                </div>
                <input type="file" class="form-control d-none file-input-preview" id="site_favicon" name="site_favicon" accept=".ico,image/png,image/svg+xml" data-preview="#faviconPreview" data-placeholder="#faviconPlaceholder">
                <button type="button" class="btn btn-sm btn-outline-secondary mt-2 btn-trigger-upload" data-target="#site_favicon">
                    <i class="fa-solid fa-camera me-1"></i> Chọn Favicon mới
                </button>
            </div>
        </div>

        <hr class="my-2">

        {{-- Hotline & Email & Địa chỉ --}}
        <div class="col-md-4">
            <label class="form-label fw-bold" for="contact_phone">Hotline CSKH</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-phone"></i></span>
                <input type="text" class="form-control" id="contact_phone" name="contact_phone"
                       value="{{ old('contact_phone', $settings['contact_phone'] ?? '') }}">
            </div>
        </div>

        <div class="col-md-4">
            <label class="form-label fw-bold" for="contact_email">Email liên hệ / hỗ trợ</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                <input type="email" class="form-control" id="contact_email" name="contact_email"
                       value="{{ old('contact_email', $settings['contact_email'] ?? '') }}">
            </div>
        </div>

        <div class="col-md-4">
            <label class="form-label fw-bold" for="contact_address">Địa chỉ trụ sở chính</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-location-dot"></i></span>
                <input type="text" class="form-control" id="contact_address" name="contact_address"
                       value="{{ old('contact_address', $settings['contact_address'] ?? '') }}">
            </div>
        </div>

        <hr class="my-2">

        {{-- Chế độ bảo trì --}}
        <div class="col-12">
            <div class="p-3 bg-light rounded-3 border">
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" role="switch" id="maintenance_mode" name="maintenance_mode" value="1"
                           {{ (!empty($settings['maintenance_mode']) && $settings['maintenance_mode'] == '1') ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold text-danger" for="maintenance_mode">
                        <i class="fa-solid fa-wrench me-1"></i> Bật chế độ bảo trì sàn
                    </label>
                </div>
                <p class="text-muted small mb-2">Khi kích hoạt, khách hàng và người bán sẽ thấy trang thông báo bảo trì, chỉ Admin mới có thể truy cập hệ thống.</p>
                
                <div class="mt-2" id="maintenanceMessageWrap">
                    <label class="form-label small fw-semibold" for="maintenance_message">Thông điệp bảo trì hiển thị:</label>
                    <textarea class="form-control" id="maintenance_message" name="maintenance_message" rows="2">{{ old('maintenance_message', $settings['maintenance_message'] ?? '') }}</textarea>
                </div>
            </div>
        </div>
    </div>
</div>
