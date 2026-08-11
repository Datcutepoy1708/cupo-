@extends('layouts.admin.app')

@section('title', 'Quản Lý Nhân Viên & Phân Quyền — Cupo Admin')

@push('styles')
    <link href="{{ asset('admin/css/roles.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="container-fluid p-4" id="rolesContainer" data-api-url="{{ route('admin.roles.data') }}"
        data-store-url="{{ route('admin.roles.store') }}" data-assign-url="{{ route('admin.roles.assign-user') }}">

        {{-- HEADER PAGE --}}
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
            <div>
                <h1 class="h3 fw-bold text-slate-800 mb-1">
                    <i class="fa-solid fa-users-gear me-2 text-danger"></i>Quản Lý Nhân Viên & Phân Quyền
                </h1>
                <p class="text-muted small mb-0">Quản lý danh sách tài khoản nhân viên ban quản trị, phân quyền theo chức vụ
                    và cấp tài khoản làm việc.</p>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-danger fw-semibold" data-bs-toggle="modal"
                    data-bs-target="#createStaffModal">
                    <i class="fa-solid fa-user-plus me-1"></i>Thêm Nhân Viên Mới
                </button>
                <button type="button" class="btn btn-danger fw-semibold" data-bs-toggle="modal"
                    data-bs-target="#createRoleModal">
                    <i class="fa-solid fa-shield-plus me-1"></i>Tạo Chức Vụ Mới
                </button>
            </div>
        </div>

        {{-- STAT CARDS --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="role-stat-card">
                    <div class="role-stat-icon success">
                        <i class="fa-solid fa-id-badge"></i>
                    </div>
                    <div>
                        <div class="role-stat-number" id="totalStaffCount">0</div>
                        <div class="role-stat-label">Tài Khoản Nhân Viên Admin</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="role-stat-card">
                    <div class="role-stat-icon primary">
                        <i class="fa-solid fa-user-tag"></i>
                    </div>
                    <div>
                        <div class="role-stat-number" id="totalRolesCount">0</div>
                        <div class="role-stat-label">Tổng Chức Vụ / Role</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="role-stat-card">
                    <div class="role-stat-icon purple">
                        <i class="fa-solid fa-key"></i>
                    </div>
                    <div>
                        <div class="role-stat-number" id="totalPermsCount">0</div>
                        <div class="role-stat-label">Quyền Hạn CRUD Hệ Thống</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- NAV TABS (2 TABS: NHÂN VIÊN & CHỨC VỤ) --}}
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white pt-3 pb-0 border-bottom-0">
                <ul class="nav nav-tabs nav-tabs-custom" id="rbacTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="staff-tab" data-bs-toggle="tab" data-bs-target="#tab-staff"
                            type="button" role="tab">
                            <i class="fa-solid fa-users me-2"></i>Danh Sách Nhân Viên
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="roles-tab" data-bs-toggle="tab" data-bs-target="#tab-roles"
                            type="button" role="tab">
                            <i class="fa-solid fa-user-shield me-2"></i>Danh Sách Chức Vụ & Phân Quyền (Role List)
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-0">
                <div class="tab-content" id="rbacTabContent">

                    {{-- ===== TAB 1: DANH SÁCH NHÂN VIÊN ===== --}}
                    <div class="tab-pane fade show active" id="tab-staff" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light text-secondary small text-uppercase fw-bold">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th style="width: 250px;">Thông Tin Nhân Viên</th>
                                        <th style="width: 140px;">Số Điện Thoại</th>
                                        <th style="width: 160px;">Chức Vụ</th>
                                        <th style="width: 140px;">Trạng Thái</th>
                                        <th style="width: 140px;">Ngày Tạo</th>
                                        <th class="text-end" style="width: 200px;">Thao Tác</th>
                                    </tr>
                                </thead>
                                <tbody id="staffTableBody">
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            <i class="fa-solid fa-spinner fa-spin me-2"></i>Đang tải danh sách nhân viên...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- ===== TAB 2: DANH SÁCH CHỨC VỤ ===== --}}
                    <div class="tab-pane fade" id="tab-roles" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light text-secondary small text-uppercase fw-bold">
                                    <tr>
                                        <th style="width: 60px;">#</th>
                                        <th style="width: 220px;">Tên Chức Vụ</th>
                                        <th style="width: 180px;">Số Quyền Đã Gán</th>
                                        <th style="width: 180px;">Số Nhân Viên Được Gán</th>
                                        <th class="text-end" style="width: 220px;">Thao Tác</th>
                                    </tr>
                                </thead>
                                <tbody id="rolesTableBody">
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            <i class="fa-solid fa-spinner fa-spin me-2"></i>Đang tải danh sách chức vụ...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>

    {{-- ===== MODAL 1: TẠO NHÂN VIÊN MỚI ===== --}}
    <div class="modal fade" id="createStaffModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold fs-6">
                        <i class="fa-solid fa-user-plus me-2"></i>Tạo Tài Khoản Nhân Viên Mới
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="createStaffForm">
                    <div class="modal-body p-4">

                        <div class="mb-3">
                            <label for="createStaffName" class="form-label fw-bold text-dark">Họ & Tên Nhân Viên <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="createStaffName"
                                placeholder="Ví dụ: Nguyễn Văn Quản Trị" required>
                        </div>
                        <div class="mb-3">
                            <label for="createStaffEmail" class="form-label fw-bold text-dark">Địa Chỉ Email Đăng Nhập <span
                                    class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="createStaffEmail" placeholder="nhanvien@cupo.vn"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="createStaffPhone" class="form-label fw-bold text-dark">Số Điện Thoại Liên Hệ</label>
                            <input type="text" class="form-control" id="createStaffPhone" placeholder="0987654321">
                        </div>
                        <div class="mb-3">
                            <label for="createStaffPassword" class="form-label fw-bold text-dark">Mật Khẩu Khởi Tạo <span
                                    class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="createStaffPassword" value="password123"
                                    required minlength="6">
                                <button class="btn btn-outline-secondary" type="button"
                                    onclick="document.getElementById('createStaffPassword').value = 'password' + Math.floor(100000 + Math.random() * 900000)">
                                    <i class="fa-solid fa-arrows-rotate me-1"></i>Tạo ngẫu nhiên
                                </button>
                            </div>
                            <div class="form-text extra-small text-muted">Bạn có thể thay đổi hoặc nhấn nút để tạo mật khẩu
                                ngẫu nhiên cho nhân viên.</div>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-6 mb-3">
                                <label for="createStaffRoleSelect" class="form-label fw-bold text-dark">Chức Vụ / Role <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="createStaffRoleSelect" required>
                                    <option value="">-- Chọn chức vụ --</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="createStaffStatusSelect" class="form-label fw-bold text-dark">Trạng Thái Tài
                                    Khoản</label>
                                <select class="form-select" id="createStaffStatusSelect">
                                    <option value="active">Hoạt động</option>
                                    <option value="blocked">Bị khóa</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-danger fw-semibold">
                            <i class="fa-solid fa-save me-1"></i>Tạo Tài Khoản
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ===== MODAL 2: QUẢN LÝ THÔNG TIN CÁ NHÂN NHÂN VIÊN ===== --}}
    <div class="modal fade" id="editStaffModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold fs-6">
                        <i class="fa-solid fa-user-pen me-2"></i>Quản Lý Thông Tin Cá Nhân Nhân Viên
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="editStaffForm">
                    <input type="hidden" id="editStaffId">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label for="editStaffName" class="form-label fw-bold text-dark">Họ & Tên Nhân Viên <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editStaffName" required>
                        </div>
                        <div class="mb-3">
                            <label for="editStaffEmail" class="form-label fw-bold text-dark">Địa Chỉ Email <span
                                    class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="editStaffEmail" required>
                        </div>
                        <div class="mb-3">
                            <label for="editStaffPhone" class="form-label fw-bold text-dark">Số Điện Thoại</label>
                            <input type="text" class="form-control" id="editStaffPhone">
                        </div>
                        <div class="row g-2">
                            <div class="col-md-6 mb-3">
                                <label for="editStaffRoleSelect" class="form-label fw-bold text-dark">Chức Vụ <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="editStaffRoleSelect" required>
                                    <option value="">-- Chọn chức vụ --</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editStaffStatusSelect" class="form-label fw-bold text-dark">Trạng Thái Tài
                                    Khoản</label>
                                <select class="form-select" id="editStaffStatusSelect">
                                    <option value="active">Hoạt động</option>
                                    <option value="blocked">Khóa tài khoản</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary fw-semibold">
                            <i class="fa-solid fa-save me-1"></i>Cập Nhật Thông Tin
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ===== MODAL 3: ĐẶT LẠI MẬT KHẨU NHÂN VIÊN ===== --}}
    <div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title fw-bold fs-6">
                        <i class="fa-solid fa-key me-2"></i>Đổi Mật Khẩu
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="resetPasswordForm">
                    <input type="hidden" id="resetStaffId">
                    <div class="modal-body p-3">
                        <p class="small text-muted mb-2">Đổi mật khẩu cho: <strong id="resetStaffNameDisplay"
                                class="text-dark"></strong></p>
                        <div class="mb-2">
                            <label for="resetStaffPassword" class="form-label small fw-bold">Mật Khẩu Mới</label>
                            <input type="password" class="form-control form-control-sm" id="resetStaffPassword"
                                placeholder="Nhập mật khẩu mới" required minlength="6">
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-2">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-sm btn-warning fw-bold">Xác Nhận Đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ===== MODAL 4: TẠO CHỨC VỤ MỚI ===== --}}
    <div class="modal fade" id="createRoleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold fs-6">
                        <i class="fa-solid fa-shield-plus me-2"></i>Tạo Chức Vụ & Ma Trận Quyền Mới
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="createRoleForm">
                    <div class="modal-body p-4">
                        <div class="mb-4">
                            <label for="createRoleNameInput" class="form-label fw-bold text-dark">Tên Chức Vụ / Role Name
                                <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="createRoleNameInput"
                                placeholder="Ví dụ: content-editor, accountant, cs-khach-hang" required>
                        </div>

                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                            <i class="fa-solid fa-table-cells me-2 text-danger"></i>Chọn Quyền Hạn Cho Chức Vụ
                        </h6>

                        <div id="createRolePermissionsContainer">
                            {{-- JS render --}}
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-danger fw-semibold">
                            <i class="fa-solid fa-save me-1"></i>Lưu Chức Vụ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ===== MODAL 5: CHỈNH SỬA QUYỀN HẠN CHỨC VỤ ===== --}}
    <div class="modal fade" id="editRoleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold fs-6">
                        <i class="fa-solid fa-sliders me-2"></i>Cập Nhật Quyền Hạn Chức Vụ
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="editRoleForm">
                    <input type="hidden" id="editRoleIdInput">
                    <div class="modal-body p-4">
                        <div class="mb-4">
                            <label for="editRoleNameInput" class="form-label fw-bold text-dark">Tên Chức Vụ</label>
                            <input type="text" class="form-control" id="editRoleNameInput" required>
                        </div>

                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                            <i class="fa-solid fa-table-cells me-2 text-primary"></i>Chọn Quyền Hạn Cho Chức Vụ
                        </h6>

                        <div id="editRolePermissionsContainer">
                            {{-- JS render --}}
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary fw-semibold">
                            <i class="fa-solid fa-save me-1"></i>Lưu Thay Đổi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('admin/js/roles.js') }}"></script>
@endpush