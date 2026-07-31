<div class="tab-pane fade" id="sellerChannel" role="tabpanel">
    <div class="content-card">

        {{-- Trạng thái: CHƯA đăng ký — hiển thị form đăng ký --}}
        <div class="seller-intro text-center mb-4">
            <i class="fa-solid fa-shop seller-intro-icon"></i>
            <h2 class="content-title border-0 mb-2" style="display:block;">Trở thành người bán
                trên Cupo</h2>
            <p class="text-muted mb-0">Mở gian hàng miễn phí, tiếp cận hàng triệu khách hàng và bắt
                đầu kinh doanh ngay hôm nay.</p>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="seller-benefit">
                    <i class="fa-solid fa-sack-dollar"></i>
                    <h6>Miễn phí đăng ký</h6>
                    <p class="small text-muted mb-0">Không tốn phí mở gian hàng, chỉ thu phí trên
                        mỗi đơn thành công.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="seller-benefit">
                    <i class="fa-solid fa-users"></i>
                    <h6>Tiếp cận khách hàng</h6>
                    <p class="small text-muted mb-0">Hàng triệu người mua đang hoạt động mỗi ngày
                        trên Cupo.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="seller-benefit">
                    <i class="fa-solid fa-chart-line"></i>
                    <h6>Công cụ quản lý</h6>
                    <p class="small text-muted mb-0">Quản lý đơn hàng, tồn kho, khuyến mãi ngay
                        trên Kênh người bán.</p>
                </div>
            </div>
        </div>

        <hr class="my-4">

        <h5 class="fw-bold mb-3">Thông tin đăng ký gian hàng</h5>

        <form method="post" action="#">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Tên gian hàng <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="shop_name"
                        placeholder="VD: Cupo Store - Điện tử chính hãng">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Ngành hàng kinh doanh <span class="text-danger">*</span></label>
                    <select class="form-select" name="shop_category">
                        <option value="">Chọn ngành hàng</option>
                        <option>Điện tử - Công nghệ</option>
                        <option>Thời trang</option>
                        <option>Đồ gia dụng</option>
                        <option>Làm đẹp - Sức khỏe</option>
                        <option>Mẹ & bé</option>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Loại hình kinh doanh <span class="text-danger">*</span></label>
                    <div class="d-flex gap-4 mt-2">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="business_type" id="typePersonal"
                                checked>
                            <label class="form-check-label" for="typePersonal">Cá nhân</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="business_type" id="typeCompany">
                            <label class="form-check-label" for="typeCompany">Doanh nghiệp</label>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Số CCCD / Mã số thuế <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="tax_id" placeholder="Nhập số CCCD hoặc MST">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Số điện thoại liên hệ <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control" name="shop_phone" value="0987654321">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email liên hệ <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" name="shop_email" value="nguyenvana@gmail.com">
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-12">
                    <label class="form-label">Địa chỉ lấy hàng <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="pickup_address"
                        placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành">
                </div>
            </div>

            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" id="agreeTerms">
                <label class="form-check-label small" for="agreeTerms">
                    Tôi đã đọc và đồng ý với <a href="#" style="color: var(--primary-red);">Điều khoản người
                        bán</a> của Cupo
                </label>
            </div>

            <button type="submit" class="btn btn-save px-4">
                <i class="fa-solid fa-store me-2"></i>Đăng ký bán hàng
            </button>
        </form>

    </div>
</div>
