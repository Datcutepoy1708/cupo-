<div class="tab-pane fade" id="sellerChannel" role="tabpanel">
    <div class="content-card">

        @php
            $sellerProfile = $user->sellerProfile;
            $status = $sellerProfile->status ?? null;
        @endphp

        @if ($status === 'approved')
            {{-- TRẠNG THÁI 1: GIAN HÀNG ĐÃ ĐƯỢC DUYỆT (ACTIVE) --}}
            <div class="text-center py-4">
                <div class="display-5 text-success mb-3">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <h4 class="fw-bold mb-2">Gian hàng "{{ $sellerProfile->shop_name }}" đang hoạt động</h4>
                <p class="text-muted mb-4">Bạn đã là người bán chính thức trên sàn Cupo. Truy cập Kênh Người Bán để đăng sản phẩm và quản lý đơn hàng.</p>
                <a href="{{ route('seller.dashboard') }}" class="btn btn-danger px-4 py-2 fw-semibold">
                    <i class="fa-solid fa-gauge me-2"></i>Truy cập Kênh Người Bán
                </a>
            </div>

        @elseif ($status === 'pending')
            {{-- TRẠNG THÁI 2: ĐANG CHỜ DUYỆT (PENDING) --}}
            <div class="text-center py-4">
                <div class="display-5 text-warning mb-3">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <h4 class="fw-bold mb-2">Hồ sơ đăng ký đang chờ phê duyệt</h4>
                <p class="text-muted mb-4">Đơn mở gian hàng <strong>"{{ $sellerProfile->shop_name }}"</strong> đã được gửi tới Ban Quản Trị. Thời gian xét duyệt từ 12-24 giờ làm việc.</p>
                <div class="p-3 bg-light rounded-3 text-start small mx-auto mb-3" style="max-width: 480px;">
                    <div class="text-muted">• Tên shop: <span class="text-dark fw-medium">{{ $sellerProfile->shop_name }}</span></div>
                    <div class="text-muted">• Địa chỉ: <span class="text-dark fw-medium">{{ $sellerProfile->address }}</span></div>
                    <div class="text-muted">• CCCD/MST: <span class="text-dark fw-medium">{{ $sellerProfile->national_id }}</span></div>
                </div>
            </div>

        @elseif ($status === 'blocked')
            {{-- TRẠNG THÁI 3: GIAN HÀNG BỊ TẠM KHÓA (BLOCKED) --}}
            <div class="text-center py-4">
                <div class="display-5 text-secondary mb-3">
                    <i class="fa-solid fa-lock"></i>
                </div>
                <h4 class="fw-bold text-danger mb-2">Gian hàng hiện đang bị tạm khóa</h4>
                <p class="text-muted mb-4">Gian hàng của bạn hiện đang tạm ngưng hoạt động. Vui lòng liên hệ bộ phận hỗ trợ hoặc gửi kháng nghị.</p>
            </div>

        @else
            {{-- TRẠNG THÁI 4: CHƯA ĐĂNG KÝ HOẶC BỊ TỪ CHỐI (REJECTED) -> HIỂN THỊ FORM ĐĂNG KÝ / NỘP LẠI --}}

            @if ($status === 'rejected')
                {{-- Alert lý do từ chối từ Admin --}}
                <div class="alert alert-danger bg-danger bg-opacity-10 border-danger border-opacity-25 rounded-3 p-3 mb-4">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="fa-solid fa-circle-xmark text-danger fs-5"></i>
                        <h6 class="fw-bold text-danger mb-0">Hồ sơ đăng ký trước đó của bạn đã bị từ chối</h6>
                    </div>
                    <p class="mb-2 text-dark small" style="white-space: pre-line;">
                        <strong>Lý do từ chối:</strong> {{ $sellerProfile->admin_note ?: 'Hồ sơ chưa đáp ứng tiêu chuẩn kiểm duyệt của sàn Cupo.' }}
                    </p>
                    <div class="text-muted small">
                        👉 <em>Vui lòng chỉnh sửa lại các thông tin cần thiết bên dưới và bấm <strong>"Cập nhật & Nộp lại hồ sơ"</strong> để Ban Quản Trị xem xét lại.</em>
                    </div>
                </div>
            @else
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
            @endif

            <hr class="my-4">

            <h5 class="fw-bold mb-3">
                {{ $status === 'rejected' ? 'Cập nhật lại thông tin gian hàng' : 'Thông tin đăng ký gian hàng' }}
            </h5>

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

                {{-- Tên gian hàng + Loại hình --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Tên gian hàng <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="shop_name"
                            value="{{ old('shop_name', $sellerProfile->shop_name ?? '') }}"
                            placeholder="VD: Cupo Store - Thời trang cao cấp" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Loại hình kinh doanh <span class="text-danger">*</span></label>
                        @php
                            $currentType = old('business_type', $sellerProfile->business_type ?? 'personal');
                        @endphp
                        <div class="d-flex gap-4 mt-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="business_type" id="typePersonal"
                                    value="personal" {{ $currentType === 'personal' ? 'checked' : '' }}>
                                <label class="form-check-label" for="typePersonal">Cá nhân</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="business_type" id="typeCompany"
                                    value="company" {{ $currentType === 'company' ? 'checked' : '' }}>
                                <label class="form-check-label" for="typeCompany">Doanh nghiệp</label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== TAG MULTI-SELECT: Lĩnh vực kinh doanh ===== --}}
                <div class="row mb-3">
                    <div class="col-12">
                        <label class="form-label">Lĩnh vực / Danh mục hàng hóa buôn bán <span class="text-danger">*</span></label>

                        <div class="cat-browse-row">
                            <select id="parentCategorySelect" class="form-select">
                                <option value="" disabled selected>-- Chọn ngành hàng --</option>
                                @if (isset($categories) && count($categories) > 0)
                                    @foreach ($categories as $parentCat)
                                        <option value="{{ $parentCat->id }}" data-children='@json($parentCat->children ?? [])'>
                                            {{ $parentCat->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>

                            <select id="childCategorySelect" class="form-select" disabled>
                                <option value="" disabled selected>-- Chọn mặt hàng cụ thể --</option>
                            </select>

                            <button type="button" id="addCategoryTagBtn" class="cat-add-btn" disabled>
                                + Thêm
                            </button>
                        </div>

                        <div id="categoryTagsWrap" class="cat-tags-wrap">
                            <span id="categoryTagsPlaceholder" class="cat-tags-placeholder">
                                Chưa chọn danh mục nào — hãy chọn ở trên rồi nhấn + Thêm
                            </span>
                        </div>

                        <div id="categoryHiddenInputs"></div>
                        <small class="text-muted mt-1 d-block">Có thể chọn nhiều lĩnh vực kinh doanh cho 1 gian hàng.</small>
                    </div>
                </div>

                {{-- Liên hệ + Ngày sinh --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Số điện thoại liên hệ <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" name="phone"
                            value="{{ old('phone', Auth::user()->phone) }}"
                            placeholder="0987654321" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Ngày sinh (dd/mm/yyyy) <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="date_of_birth"
                            value="{{ old('date_of_birth', Auth::user()->date_of_birth?->format('Y-m-d')) }}"
                            placeholder="VD: 15/08/2000" required>
                        <small class="text-muted">Người bán phải từ đủ 18 tuổi trở lên.</small>
                    </div>
                </div>

                {{-- CCCD + Địa chỉ --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Số CCCD / Mã số thuế (12 chữ số) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="national_id"
                            value="{{ old('national_id', $sellerProfile->national_id ?? '') }}"
                            placeholder="Nhập đúng 12 chữ số CCCD/MST" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Địa chỉ gian hàng <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="address"
                            value="{{ old('address', $sellerProfile->address ?? '') }}"
                            placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành" required>
                    </div>
                </div>

                {{-- Mô tả --}}
                <div class="row mb-4">
                    <div class="col-md-12">
                        <label class="form-label">Mô tả gian hàng</label>
                        <textarea class="form-control" name="description" rows="3"
                            placeholder="Mô tả ngắn gọn về cửa hàng của bạn">{{ old('description', $sellerProfile->description ?? '') }}</textarea>
                    </div>
                </div>

                {{-- Điều khoản --}}
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" id="agreeTerms" required checked>
                    <label class="form-check-label small" for="agreeTerms">
                        Tôi đã đọc và đồng ý với <a href="#" style="color: var(--primary-red);">Điều khoản người bán</a> của Cupo
                    </label>
                </div>

                <button type="submit" class="btn btn-save px-4">
                    <i class="fa-solid fa-store me-2"></i>
                    {{ $status === 'rejected' ? 'Cập nhật & Nộp lại hồ sơ' : 'Đăng ký bán hàng' }}
                </button>
            </form>
        @endif

    </div>
</div>