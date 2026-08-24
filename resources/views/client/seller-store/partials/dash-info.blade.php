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

    {{-- Ngành hàng kinh doanh — Chỉ hiển thị các ngành hàng đã đăng ký & được Admin duyệt --}}
    <div class="dash-info-card mb-4">
        <div class="dash-info-card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <i class="fa-solid fa-tags"></i>
                <span>Ngành hàng kinh doanh đã đăng ký</span>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#categoryRequestModal">
                <i class="fa-solid fa-paper-plane me-1"></i>Đơn đăng ký thêm ngành hàng
            </button>
        </div>
        <small class="dash-info-card-note">
            <i class="fa-solid fa-shield-halved"></i>
            Danh sách bên dưới là các ngành hàng cửa hàng của bạn đã đăng ký với Quản trị viên (Admin). Nếu muốn kinh doanh thêm ngành hàng mới, vui lòng gửi đơn đăng ký bổ sung để được xét duyệt.
        </small>
        <div class="dash-info-card-body">
            @if ($shop->categories && $shop->categories->isNotEmpty())
                <div class="d-flex flex-wrap gap-2 py-2">
                    @foreach ($shop->categories as $cat)
                        <span class="badge bg-light text-dark border px-3 py-2 fs-6 rounded-pill d-inline-flex align-items-center gap-2">
                            <i class="fa-solid fa-circle-check text-success"></i>
                            {{ $cat->name }}
                            @if ($cat->parent)
                                <small class="text-muted">({{ $cat->parent->name }})</small>
                            @endif
                        </span>
                    @endforeach
                </div>
            @else
                <div class="alert alert-warning mb-0 py-2 fs-6">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i>
                    Cửa hàng chưa có ngành hàng kinh doanh nào được phê duyệt. Vui lòng gửi đơn đăng ký bổ sung.
                </div>
            @endif
        </div>
    </div>

    {{-- Modal đăng ký thêm ngành hàng kinh doanh --}}
    <div class="modal fade" id="categoryRequestModal" tabindex="-1" aria-labelledby="categoryRequestModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold" id="categoryRequestModalLabel">
                        <i class="fa-solid fa-file-pen me-2"></i>Đơn đăng ký bổ sung ngành hàng
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('seller.categories.request') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p class="small text-muted mb-3">
                            Vui lòng chọn các ngành hàng bạn muốn mở rộng kinh doanh. Đơn đăng ký sẽ được Admin kiểm tra và duyệt bổ sung.
                        </p>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Chọn ngành hàng muốn đăng ký thêm:</label>
                            @php
                                $registeredIds = $shop->categories ? $shop->categories->pluck('id')->toArray() : [];
                            @endphp
                            <div class="border rounded p-3 bg-light" style="max-height: 220px; overflow-y: auto;">
                                @forelse ($allCategoriesForSelection ?? [] as $parent)
                                    <div class="fw-bold text-dark mt-2 mb-1 fs-6">{{ $parent->name }}</div>
                                    @forelse ($parent->children as $child)
                                        @if (!in_array($child->id, $registeredIds))
                                            <div class="form-check ms-2 mb-1">
                                                <input class="form-check-input" type="checkbox" name="request_categories[]" value="{{ $child->id }}" id="reqCat{{ $child->id }}">
                                                <label class="form-check-label small" for="reqCat{{ $child->id }}">
                                                    {{ $child->name }}
                                                </label>
                                            </div>
                                        @endif
                                    @empty
                                        @if (!in_array($parent->id, $registeredIds))
                                            <div class="form-check ms-2 mb-1">
                                                <input class="form-check-input" type="checkbox" name="request_categories[]" value="{{ $parent->id }}" id="reqCat{{ $parent->id }}">
                                                <label class="form-check-label small" for="reqCat{{ $parent->id }}">
                                                    {{ $parent->name }}
                                                </label>
                                            </div>
                                        @endif
                                    @endforelse
                                @empty
                                    <p class="text-muted small mb-0">Không có ngành hàng mới để đăng ký.</p>
                                @endforelse
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Ghi chú gửi Admin:</label>
                            <textarea class="form-control form-control-sm" name="seller_note" rows="3" placeholder="Mô tả lý do hoặc thông tin sản phẩm dự kiến bán thêm..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-danger btn-sm px-3">
                            <i class="fa-solid fa-paper-plane me-1"></i>Gửi đơn xét duyệt
                        </button>
                    </div>
                </form>
            </div>
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
