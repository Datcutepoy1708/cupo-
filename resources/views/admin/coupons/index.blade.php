@extends('layouts.admin.app')

@section('page-title', 'Quản lý Mã giảm giá (Vouchers)')

@section('breadcrumb')
    <li class="breadcrumb-item active">Mã giảm giá</li>
@endsection

@push('styles')
    <link href="{{ asset('admin/css/coupons.css') }}" rel="stylesheet">
@endpush

@section('content')

    {{-- 1. Stat cards --}}
    @include('admin.coupons.partials.stat-cards')

    {{-- 2. Bang danh sach Voucher --}}
    <div class="admin-card">

        {{-- Card Header: Tab filter + Dropdowns + Tim kiem + Nut Them --}}
        <div class="admin-card-header flex-column gap-3">

            {{-- Hang 1: Tabs trang thai --}}
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 w-100">
                <div class="seller-tabs" id="statusTabs">
                    <button class="seller-tab active" data-status="">Tất cả</button>
                    <button class="seller-tab" data-status="active">
                        <span class="chip-dot green me-1"></span>
                        Đang áp dụng
                        <span class="tab-badge" id="tab-badge-active" style="background:#e8f5e9;color:#2e7d32;">0</span>
                    </button>
                    <button class="seller-tab" data-status="upcoming">
                        <span class="chip-dot blue me-1"></span>
                        Sắp diễn ra
                        <span class="tab-badge" id="tab-badge-upcoming" style="background:#e3f2fd;color:#1565c0;">0</span>
                    </button>
                    <button class="seller-tab" data-status="expired">
                        <span class="chip-dot red me-1"></span>
                        Hết hạn / Hết lượt
                        <span class="tab-badge" id="tab-badge-expired" style="background:#ffebee;color:#c62828;">0</span>
                    </button>
                    <button class="seller-tab" data-status="inactive">
                        <span class="chip-dot grey me-1"></span>
                        Tạm ẩn
                    </button>
                </div>

                {{-- Nut Them Voucher --}}
                <button type="button" id="btnAddCoupon" class="btn-coupon-primary">
                    <i class="fa-solid fa-plus me-1"></i>
                    Tạo mã giảm giá
                </button>
            </div>

            {{-- Hang 2: Bo loc chi tiet & Tim kiem --}}
            <div class="d-flex flex-wrap align-items-center gap-2 w-100 pt-2 border-top">

                {{-- Loc pham vi --}}
                <select id="couponScopeFilter" class="form-select form-select-sm" style="width: 170px;">
                    <option value="">Tất cả phạm vi</option>
                    <option value="platform">Voucher Toàn sàn</option>
                    <option value="shop">Voucher Gian hàng</option>
                </select>

                {{-- Loc loai giam --}}
                <select id="couponTypeFilter" class="form-select form-select-sm" style="width: 190px;">
                    <option value="">Tất cả loại giảm</option>
                    <option value="fixed_amount">Số tiền cố định (VNĐ)</option>
                    <option value="percentage">Theo phần trăm (%)</option>
                    <option value="free_shipping">Miễn phí vận chuyển (Freeship)</option>
                </select>

                {{-- O tim kiem --}}
                <div class="seller-search-wrap ms-auto">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text"
                           id="couponSearchInput"
                           class="seller-search"
                           placeholder="Tìm theo mã Code, tên Shop...">
                </div>

                {{-- Nut lam moi --}}
                <button type="button" id="btnRefreshList" class="btn btn-sm btn-outline-secondary" title="Làm mới danh sách">
                    <i class="fa-solid fa-rotate-right"></i>
                </button>
            </div>

        </div>

        {{-- Bulk action toolbar --}}
        <div class="bulk-toolbar" id="bulkToolbar" style="display:none;">
            <span class="bulk-info">
                <i class="fa-solid fa-square-check"></i>
                Đã chọn <strong id="bulkCount">0</strong> mã giảm giá
            </span>
            <div class="bulk-actions">
                <button type="button" class="btn-bulk-approve" id="btnBulkActivate">
                    <i class="fa-solid fa-circle-check me-1"></i>
                    Kích hoạt
                </button>
                <button type="button" class="btn-bulk-reject" id="btnBulkDeactivate" style="background:#e65100;">
                    <i class="fa-solid fa-ban me-1"></i>
                    Tạm ẩn
                </button>
                <button type="button" class="btn-bulk-reject" id="btnBulkDelete">
                    <i class="fa-solid fa-trash me-1"></i>
                    Xóa đã chọn
                </button>
                <button type="button" class="btn-bulk-clear" id="btnBulkClear">
                    <i class="fa-solid fa-xmark me-1"></i>
                    Bỏ chọn
                </button>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table admin-table align-middle mb-0" id="couponsTable">
                <thead>
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" class="form-check-input" id="checkAllCoupons">
                        </th>
                        <th style="min-width: 170px;">Mã Voucher</th>
                        <th style="min-width: 140px;">Phạm vi</th>
                        <th style="min-width: 180px;">Mức giảm & Điều kiện</th>
                        <th style="min-width: 160px;">Tiến độ sử dụng</th>
                        <th style="min-width: 180px;">Thời gian áp dụng</th>
                        <th style="min-width: 110px;">Trạng thái</th>
                        <th style="width: 120px;" class="text-end">Hành động</th>
                    </tr>
                </thead>
                <tbody id="couponsTableBody">
                    {{-- Render qua JS --}}
                </tbody>
            </table>
        </div>

        {{-- Pagination footer --}}
        <div class="admin-card-footer d-flex flex-wrap align-items-center justify-content-between gap-3" id="paginationWrap" style="display:none;">
            <div class="text-muted fs-7" id="paginationInfo">
                Hiển thị 0 - 0 trong 0 kết quả
            </div>
            <div class="pagination-links" id="paginationLinks"></div>
        </div>

    </div>

    {{-- Modals include --}}
    @include('admin.coupons.partials.form-modal')
    @include('admin.coupons.partials.detail-modal')

    {{-- Modal xac nhan xoa --}}
    <div class="modal fade" id="deleteCouponModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow">
                <div class="modal-body p-4 text-center">
                    <div class="text-danger mb-3" style="font-size: 40px;">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Xóa mã giảm giá?</h5>
                    <p class="text-muted fs-7 mb-4">
                        Hành động này không thể hoàn tác. Bạn có chắc chắn muốn xóa mã <strong id="deleteCouponCodeText" class="text-danger"></strong> không?
                    </p>
                    <div class="d-flex gap-2 justify-content-center">
                        <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Hủy</button>
                        <button type="button" class="btn btn-danger px-3 fw-bold" id="btnConfirmDelete">
                            Xóa vĩnh viễn
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Toast thong bao --}}
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100;">
        <div id="actionToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2" id="toastMessage">
                    <i class="fa-solid fa-circle-check"></i> Thao tác thành công.
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    {{-- Config element de JS lay routes --}}
    <div id="couponsAppConfig"
         data-index-url="{{ route('admin.coupons.index') }}"
         data-store-url="{{ route('admin.coupons.store') }}"
         data-update-url="{{ route('admin.coupons.update', ['coupon' => '__ID__']) }}"
         data-show-url="{{ route('admin.coupons.show', ['coupon' => '__ID__']) }}"
         data-destroy-url="{{ route('admin.coupons.destroy', ['coupon' => '__ID__']) }}"
         data-toggle-status-url="{{ route('admin.coupons.toggle-status', ['coupon' => '__ID__']) }}"
         data-bulk-status-url="{{ route('admin.coupons.bulk-status') }}"
         data-bulk-delete-url="{{ route('admin.coupons.bulk-delete') }}"
         data-csrf="{{ csrf_token() }}">
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('admin/js/coupons.js') }}"></script>
@endpush
