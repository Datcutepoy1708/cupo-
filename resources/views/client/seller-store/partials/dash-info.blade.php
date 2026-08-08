<div class="tab-pane fade" id="dashInfo" role="tabpanel">

    {{-- Hàng 1: Trạng thái + Tài chính (đọc nhanh, đặt cạnh nhau) --}}
    <div class="row g-3 mb-4">
        <div class="col-md-12">
            <div class="dash-info-card h-100">
                <div class="dash-info-card-header">
                    <i class="fa-solid fa-wallet"></i>
                    <span>Thông tin tài chính</span>
                </div>
                <div class="dash-info-card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="dash-stat-card">
                                <i class="fa-solid fa-sack-dollar"></i>
                                <h4>{{ number_format($shop->balance) }}₫</h4>
                                <p>Số dư hiện tại</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="dash-stat-card">
                                <i class="fa-solid fa-percent"></i>
                                <h4>{{ number_format($shop->commission_rate, 2) }}%</h4>
                                <p>Tỉ lệ hoa hồng sàn</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Thông tin gian hàng (chỉnh sửa được) --}}
    <div class="dash-info-card mb-4">
        <div class="dash-info-card-header">
            <i class="fa-solid fa-store"></i>
            <span>Thông tin gian hàng</span>
        </div>
        <small class="dash-info-card-note">
            <i class="fa-solid fa-circle-info"></i>
            Một số thông tin liên quan đến thông tin định danh khi đăng ký, liên hệ quản trị viên nếu
            cần đổi.
        </small>
        <div class="dash-info-card-body">
            <form method="post" action="#">
                @csrf
                @method('PUT')
                {{-- Các trường bên dưới bị khóa vì gắn với thông tin định danh khi đăng ký:
                                     không gắn "name" vì không được phép sửa qua form này (kể cả khi bỏ disabled
                                     bằng devtools, server cũng không nhận trường không tồn tại này). --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Tên gian hàng</label>
                        <input type="text" class="form-control" value="{{ $shop->shop_name }}" disabled>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Loại hình kinh doanh</label>
                        <input type="text" class="form-control"
                            value="{{ $shop->business_type === 'company' ? 'Doanh nghiệp' : 'Cá nhân' }}" disabled>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Đường dẫn gian hàng (slug)</label>
                        <input type="text" class="form-control" value="{{ $shop->slug }}" disabled>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Địa chỉ</label>
                        <input type="text" class="form-control" value="{{ $shop->address }}" disabled>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mô tả cửa hàng</label>
                    <textarea class="form-control" name="description" rows="3">{{ $shop->description }}</textarea>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-save">
                        <i class="fa-solid fa-floppy-disk me-2"></i>Lưu thay đổi
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Ngành hàng kinh doanh — chia 2 cấp: danh mục cha (nhóm) và danh mục con (chọn được) --}}
    <div class="dash-info-card mb-4">
        <div class="dash-info-card-header">
            <i class="fa-solid fa-tags"></i>
            <span>Ngành hàng kinh doanh</span>
        </div>
        <div class="dash-info-card-body">
            <form method="post" action="#">
                @csrf
                @method('PUT')
                @php $selectedCategoryIds = $shop->categories->pluck('id')->all(); @endphp

                @forelse ($allCategories ?? [] as $parent)
                    <div class="cat-group mb-3">
                        <p class="fw-bold mb-2">{{ $parent->name }}</p>
                        <div class="row">
                            @forelse ($parent->children as $child)
                                <div class="col-6 col-md-4 col-lg-3 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="categories[]"
                                            value="{{ $child->id }}" id="cat{{ $child->id }}"
                                            @checked(in_array($child->id, $selectedCategoryIds))>
                                        <label class="form-check-label"
                                            for="cat{{ $child->id }}">{{ $child->name }}</label>
                                    </div>
                                </div>
                            @empty
                                {{-- Danh mục cha không có con: cho chọn thẳng danh mục cha --}}
                                <div class="col-6 col-md-4 col-lg-3 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="categories[]"
                                            value="{{ $parent->id }}" id="cat{{ $parent->id }}"
                                            @checked(in_array($parent->id, $selectedCategoryIds))>
                                        <label class="form-check-label"
                                            for="cat{{ $parent->id }}">{{ $parent->name }}</label>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">Chưa có danh mục ngành hàng nào trong hệ thống.</p>
                @endforelse

                <div class="d-flex justify-content-end mt-2">
                    <button type="submit" class="btn btn-save">
                        <i class="fa-solid fa-floppy-disk me-2"></i>Lưu ngành hàng
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Định danh (chỉ xem) + Ngân hàng nhận tiền (chỉnh sửa được) — gộp chung 1 card vì cùng nhóm thông tin nhạy cảm --}}
    <div class="dash-info-card">
        <div class="dash-info-card-header">
            <i class="fa-solid fa-building-columns"></i>
            <span>Định danh &amp; ngân hàng nhận tiền</span>
        </div>
        <div class="dash-info-card-body">
            <div class="mb-3">
                <label class="form-label text-muted mb-1">Số CCCD/CMND</label>
                <p class="mb-0 fw-semibold">
                    {{ $shop->national_id ? \Illuminate\Support\Str::mask($shop->national_id, '*', 3, -4) : 'Chưa cập nhật' }}
                </p>
                <small class="dash-info-card-note">
                    <i class="fa-solid fa-circle-info"></i>
                    Thông tin định danh không thể tự chỉnh sửa, liên hệ quản trị
                    viên nếu cần thay đổi.
                </small>
            </div>

            <hr class="dash-info-divider">

            <form method="post" action="#">
                @csrf
                @method('PUT')
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Ngân hàng</label>
                        <input type="text" class="form-control" name="bank_name" value="{{ $shop->bank_name }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Số tài khoản</label>
                        <input type="text" class="form-control" name="bank_account"
                            value="{{ $shop->bank_account }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Chủ tài khoản</label>
                        <input type="text" class="form-control" name="bank_owner"
                            value="{{ $shop->bank_owner }}">
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-save">
                        <i class="fa-solid fa-floppy-disk me-2"></i>Lưu thay đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
