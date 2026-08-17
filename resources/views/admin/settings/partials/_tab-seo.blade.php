{{-- Tab 6: SEO & Mạng xã hội --}}
<div class="settings-tab-pane" id="tab-seo">
    <div class="settings-section-header">
        <h5 class="settings-section-title">
            <i class="fa-solid fa-share-nodes text-danger me-2"></i>SEO & Mạng Xã Hội
        </h5>
        <p class="settings-section-desc">Cấu hình thẻ Meta mặc định cho công cụ tìm kiếm (Google) và liên kết kênh mạng xã hội của sàn.</p>
    </div>

    <div class="row g-4">
        {{-- Meta Title, Description, Keywords --}}
        <div class="col-md-12">
            <label class="form-label fw-bold" for="meta_title">Tiêu đề trang mặc định (Meta Title)</label>
            <input type="text" class="form-control" id="meta_title" name="meta_title"
                   value="{{ old('meta_title', $settings['meta_title'] ?? '') }}" placeholder="Cupo — Sàn Thương Mại Điện Tử...">
            <div class="form-text">Độ dài khuyến nghị: 50 - 60 ký tự.</div>
        </div>

        <div class="col-md-12">
            <label class="form-label fw-bold" for="meta_description">Mô tả trang mặc định (Meta Description)</label>
            <textarea class="form-control" id="meta_description" name="meta_description" rows="3"
                      placeholder="Mô tả tóm tắt về sàn Cupo khi hiển thị trên kết quả tìm kiếm Google...">{{ old('meta_description', $settings['meta_description'] ?? '') }}</textarea>
            <div class="form-text">Độ dài khuyến nghị: 120 - 160 ký tự.</div>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-bold" for="meta_keywords">Từ khóa tìm kiếm (Meta Keywords)</label>
            <input type="text" class="form-control" id="meta_keywords" name="meta_keywords"
                   value="{{ old('meta_keywords', $settings['meta_keywords'] ?? '') }}" placeholder="cupo, mua sam, e-commerce...">
            <div class="form-text">Phân cách nhau bằng dấu phẩy.</div>
        </div>

        {{-- OpenGraph Image Upload --}}
        <div class="col-md-6">
            <label class="form-label fw-bold">Ảnh chia sẻ Facebook / Zalo (OpenGraph Image)</label>
            <div class="image-upload-box" id="ogImageUploadBox">
                <div class="image-preview-wrap">
                    @if(!empty($settings['og_image']))
                        <img src="{{ asset('storage/' . $settings['og_image']) }}" alt="OG Image" id="ogPreview" class="preview-img">
                    @else
                        <img src="" alt="OG Image" id="ogPreview" class="preview-img d-none">
                        <div class="upload-placeholder" id="ogPlaceholder">
                            <i class="fa-solid fa-image fa-2x mb-2 text-muted"></i>
                            <div class="small text-muted">Tải ảnh OpenGraph (Khuyến nghị 1200x630px)</div>
                        </div>
                    @endif
                </div>
                <input type="file" class="form-control d-none file-input-preview" id="og_image" name="og_image" accept="image/*" data-preview="#ogPreview" data-placeholder="#ogPlaceholder">
                <button type="button" class="btn btn-sm btn-outline-secondary mt-2 btn-trigger-upload" data-target="#og_image">
                    <i class="fa-solid fa-camera me-1"></i> Chọn ảnh đại diện share
                </button>
            </div>
        </div>

        <hr class="my-2">

        {{-- Social Links --}}
        <div class="col-md-6">
            <label class="form-label fw-bold" for="social_facebook">Trang Fanpage Facebook</label>
            <div class="input-group">
                <span class="input-group-text text-primary"><i class="fa-brands fa-facebook"></i></span>
                <input type="url" class="form-control" id="social_facebook" name="social_facebook"
                       value="{{ old('social_facebook', $settings['social_facebook'] ?? '') }}" placeholder="https://facebook.com/cupo.vietnam">
            </div>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-bold" for="social_tiktok">Kênh TikTok</label>
            <div class="input-group">
                <span class="input-group-text text-dark"><i class="fa-brands fa-tiktok"></i></span>
                <input type="url" class="form-control" id="social_tiktok" name="social_tiktok"
                       value="{{ old('social_tiktok', $settings['social_tiktok'] ?? '') }}" placeholder="https://tiktok.com/@cupo.official">
            </div>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-bold" for="social_youtube">Kênh YouTube</label>
            <div class="input-group">
                <span class="input-group-text text-danger"><i class="fa-brands fa-youtube"></i></span>
                <input type="url" class="form-control" id="social_youtube" name="social_youtube"
                       value="{{ old('social_youtube', $settings['social_youtube'] ?? '') }}" placeholder="https://youtube.com/@cupovietnam">
            </div>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-bold" for="social_zalo">Kênh Zalo Official</label>
            <div class="input-group">
                <span class="input-group-text text-info"><i class="fa-solid fa-comment-dots"></i></span>
                <input type="url" class="form-control" id="social_zalo" name="social_zalo"
                       value="{{ old('social_zalo', $settings['social_zalo'] ?? '') }}" placeholder="https://zalo.me/cupo">
            </div>
        </div>
    </div>
</div>
