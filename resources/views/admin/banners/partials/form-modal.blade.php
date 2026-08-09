{{--
    Partial: Modal Tạo / Sửa Banner
--}}
<div class="modal fade" id="bannerFormModal" tabindex="-1" aria-labelledby="bannerFormModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header" style="background: linear-gradient(135deg, #c62828, #b71c1c);">
                <h5 class="modal-title text-white" id="bannerFormModalLabel">Thêm Banner Mới</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">
                <form id="bannerForm" novalidate>

                    {{-- Tiêu đề --}}
                    <div class="mb-3">
                        <label for="bannerTitle" class="form-label fw-semibold">
                            Tiêu đề Banner <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="bannerTitle" class="form-control"
                               placeholder="VD: Siêu khuyến mãi mùa hè - Giảm 50%" required maxlength="255">
                        <div class="invalid-feedback" id="bannerTitleError"></div>
                    </div>

                    {{-- Vị trí + Thứ tự --}}
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="bannerPosition" class="form-label fw-semibold">
                                Vị trí hiển thị <span class="text-danger">*</span>
                            </label>
                            <select id="bannerPosition" class="form-select" required>
                                <option value="homepage_hero">Slide chính Trang chủ (Hero Banner)</option>
                                <option value="homepage_mid">Giữa Trang chủ (Mid Banner)</option>
                                <option value="category_top">Đầu Trang Danh mục (Category Banner)</option>
                                <option value="sidebar">Thanh bên (Sidebar Banner)</option>
                            </select>
                            <div class="invalid-feedback" id="bannerPositionError"></div>
                        </div>

                        <div class="col-md-4">
                            <label for="bannerSortOrder" class="form-label fw-semibold">Thứ tự ưu tiên</label>
                            <input type="number" id="bannerSortOrder" class="form-control" value="0" min="0" placeholder="0">
                        </div>
                    </div>

                    {{-- Đường dẫn ảnh & Upload --}}
                    <div class="mb-3">
                        <label for="bannerImagePath" class="form-label fw-semibold">
                            Hình ảnh Banner <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="text" id="bannerImagePath" class="form-control"
                                   placeholder="Dán URL ảnh hoặc bấm Tải ảnh lên..." required>
                            <input type="file" id="bannerFilePicker" class="d-none" accept="image/jpeg,image/png,image/jpg,image/webp,image/gif">
                            <button type="button" class="btn btn-outline-secondary" id="btnUploadBannerFile" title="Tải tệp từ máy tính">
                                <i class="fa-solid fa-cloud-arrow-up me-1"></i> Tải ảnh lên
                            </button>
                        </div>
                        <div class="invalid-feedback" id="bannerImagePathError"></div>
                        <small class="text-muted d-block mt-1">Dán URL ảnh từ internet hoặc chọn tệp từ máy tính để lưu vào hệ thống.</small>
                    </div>

                    {{-- Preview ảnh --}}
                    <div class="mb-3 d-none" id="formImagePreviewWrap">
                        <label class="form-label fw-semibold">Xem trước hình ảnh:</label>
                        <div class="banner-form-preview-box">
                            <img id="formImagePreview" src="" alt="Preview" class="img-fluid rounded">
                        </div>
                    </div>

                    {{-- Đường dẫn đích (Link URL) --}}
                    <div class="mb-3">
                        <label for="bannerLinkUrl" class="form-label fw-semibold">Đường dẫn khi nhấp (Link URL)</label>
                        <input type="url" id="bannerLinkUrl" class="form-control"
                               placeholder="https://cupo.vn/khuyen-mai-mua-he">
                        <div class="invalid-feedback" id="bannerLinkUrlError"></div>
                    </div>

                    {{-- Thời gian chạy (Starts at / Ends at) --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="bannerStartsAt" class="form-label fw-semibold">Ngày bắt đầu</label>
                            <input type="datetime-local" id="bannerStartsAt" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label for="bannerEndsAt" class="form-label fw-semibold">Ngày kết thúc</label>
                            <input type="datetime-local" id="bannerEndsAt" class="form-control">
                            <div class="invalid-feedback" id="bannerEndsAtError"></div>
                        </div>
                    </div>

                    {{-- Trạng thái active --}}
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="bannerIsActive" checked>
                        <label class="form-check-label fw-semibold ms-2" for="bannerIsActive">
                            Bật hiển thị Banner ngay
                        </label>
                    </div>

                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Hủy</button>
                <button type="button" id="btnSaveBanner" class="btn btn-danger btn-sm px-4">
                    <i class="fa-solid fa-save me-1"></i> Lưu thông tin
                </button>
            </div>

        </div>
    </div>
</div>
