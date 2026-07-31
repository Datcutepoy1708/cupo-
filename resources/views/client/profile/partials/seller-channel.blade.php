<div class="tab-pane fade" id="sellerChannel" role="tabpanel">
    <div class="content-card">

        {{-- Trạng thái: CHƯA đăng ký — hiển thị form đăng ký --}}
        <div class="seller-intro text-center mb-4">
            <i class="fa-solid fa-shop seller-intro-icon"></i>
            <h2 class="content-title border-0 mb-2" style="display:block;">Trở thành người bán trên Cupo</h2>
            <p class="text-muted mb-0">Mở gian hàng miễn phí, tiếp cận hàng triệu khách hàng và bắt đầu kinh doanh ngay hôm nay.</p>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="seller-benefit">
                    <i class="fa-solid fa-sack-dollar"></i>
                    <h6>Miễn phí đăng ký</h6>
                    <p class="small text-muted mb-0">Không tốn phí mở gian hàng, chỉ thu phí trên mỗi đơn thành công.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="seller-benefit">
                    <i class="fa-solid fa-users"></i>
                    <h6>Tiếp cận khách hàng</h6>
                    <p class="small text-muted mb-0">Hàng triệu người mua đang hoạt động mỗi ngày trên Cupo.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="seller-benefit">
                    <i class="fa-solid fa-chart-line"></i>
                    <h6>Công cụ quản lý</h6>
                    <p class="small text-muted mb-0">Quản lý đơn hàng, tồn kho, khuyến mãi ngay trên Kênh người bán.</p>
                </div>
            </div>
        </div>

        <hr class="my-4">

        <h5 class="fw-bold mb-3">Thông tin đăng ký gian hàng</h5>

        @if ($errors->any())
            <div class="alert alert-danger mb-3" role="alert">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('seller.register.store') }}">
            @csrf
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Tên gian hàng <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="shop_name" value="{{ old('shop_name') }}"
                        placeholder="VD: Cupo Store - Thời trang cao cấp" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Loại hình kinh doanh <span class="text-danger">*</span></label>
                    <div class="d-flex gap-4 mt-2">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="business_type" id="typePersonal" value="personal"
                                {{ old('business_type', 'personal') === 'personal' ? 'checked' : '' }}>
                            <label class="form-check-label" for="typePersonal">Cá nhân</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="business_type" id="typeCompany" value="company"
                                {{ old('business_type') === 'company' ? 'checked' : '' }}>
                            <label class="form-check-label" for="typeCompany">Doanh nghiệp</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Số điện thoại liên hệ <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control" name="phone" value="{{ old('phone', Auth::user()->phone) }}" placeholder="0987654321" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Ngày sinh (dd/mm/yyyy) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="date_of_birth" value="{{ old('date_of_birth', Auth::user()->date_of_birth?->format('d/m/Y')) }}" placeholder="VD: 15/08/2000" required>
                    <small class="text-muted">Người bán phải từ đủ 18 tuổi trở lên.</small>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Số CCCD / Mã số thuế (12 chữ số) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="national_id" value="{{ old('national_id') }}" placeholder="Nhập đúng 12 chữ số CCCD/MST" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Địa chỉ gian hàng <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="address" value="{{ old('address') }}"
                        placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành" required>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-12">
                    <label class="form-label">Mô tả gian hàng</label>
                    <textarea class="form-control" name="description" rows="3" placeholder="Mô tả ngắn gọn về cửa hàng của bạn">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" id="agreeTerms" required checked>
                <label class="form-check-label small" for="agreeTerms">
                    Tôi đã đọc và đồng ý với <a href="#" style="color: var(--primary-red);">Điều khoản người bán</a> của Cupo
                </label>
            </div>

            <button type="submit" class="btn btn-save px-4">
                <i class="fa-solid fa-store me-2"></i>Đăng ký bán hàng
            </button>
        </form>

    </div>
</div>
