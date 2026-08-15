@extends('layouts.admin.app')

@section('page-title', 'Quản lý Đơn hàng')

@section('breadcrumb')
    <li class="breadcrumb-item active">Đơn hàng</li>
@endsection

@push('styles')
    <link href="{{ asset('admin/css/orders.css') }}" rel="stylesheet">
@endpush

@section('content')

    {{-- Stat cards --}}
    @include('admin.orders.partials._stat-cards')

    {{-- Bang danh sach --}}
    <div class="admin-card">

        {{-- Card Header --}}
        <div class="admin-card-header flex-column gap-3">

            {{-- Hang 1: Tab loc theo payment_status + nut Export --}}
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 w-100">
                <div class="seller-tabs" id="paymentStatusTabs">
                    <button class="seller-tab active" data-payment-status="">Tất cả</button>
                    <button class="seller-tab" data-payment-status="pending">
                        <span class="chip-dot grey me-1"></span>
                        Chờ TT
                        <span class="tab-badge" id="tab-badge-payment-pending">0</span>
                    </button>
                    <button class="seller-tab" data-payment-status="paid">
                        <span class="chip-dot green me-1"></span>
                        Đã TT
                        <span class="tab-badge" id="tab-badge-payment-paid">0</span>
                    </button>
                    <button class="seller-tab" data-payment-status="failed">
                        <span class="chip-dot red me-1"></span>
                        TT lỗi
                        <span class="tab-badge" id="tab-badge-payment-failed">0</span>
                    </button>
                    <button class="seller-tab" data-payment-status="refunded">
                        <span class="chip-dot blue me-1"></span>
                        Hoàn tiền
                        <span class="tab-badge" id="tab-badge-payment-refunded">0</span>
                    </button>
                </div>

                <a href="#" id="btnExportOrders" class="btn-seller-export" title="Xuất CSV">
                    <i class="fa-solid fa-file-csv"></i>
                    Export CSV
                </a>
            </div>

            {{-- Hang 2: Bo loc chi tiet + Tim kiem --}}
            <div class="d-flex flex-wrap align-items-center gap-2 w-100 pt-2 border-top">

                {{-- Loc trang thai xu ly don --}}
                <select id="sellerOrderStatusFilter" class="form-select form-select-sm" style="width: 190px;">
                    <option value="">Tất cả trạng thái XL</option>
                    <option value="pending">Chờ xác nhận</option>
                    <option value="confirmed">Đã xác nhận</option>
                    <option value="shipping">Đang giao</option>
                    <option value="completed">Hoàn thành</option>
                    <option value="cancelled">Đã hủy</option>
                </select>

                {{-- Loc theo ngay dat --}}
                <input type="date" id="dateFromFilter" class="form-control form-control-sm" style="width: 148px;" placeholder="Từ ngày">
                <input type="date" id="dateToFilter" class="form-control form-control-sm" style="width: 148px;" placeholder="Đến ngày">

                {{-- O tim kiem --}}
                <div class="seller-search-wrap ms-auto">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text"
                           id="orderSearchInput"
                           class="seller-search"
                           placeholder="Tìm mã đơn hàng, tên, SĐT...">
                </div>

                {{-- Lam moi --}}
                <button type="button" id="btnRefreshOrders" class="btn btn-sm btn-outline-secondary" title="Làm mới">
                    <i class="fa-solid fa-rotate-right"></i>
                </button>

            </div>

        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table admin-table align-middle mb-0" id="ordersTable">
                <thead>
                    <tr>
                        <th style="min-width: 160px;">Mã đơn hàng</th>
                        <th style="min-width: 160px;">Khách hàng</th>
                        <th style="min-width: 120px;">Tổng tiền</th>
                        <th style="min-width: 130px;">Thanh toán</th>
                        <th style="min-width: 100px;">Số Seller</th>
                        <th style="min-width: 130px;">Ngày đặt</th>
                        <th style="width: 90px;" class="text-end pe-4">Chi tiết</th>
                    </tr>
                </thead>
                <tbody id="ordersTableBody">
                    {{-- Render boi orders.js --}}
                    <tr id="ordersLoadingRow">
                        <td colspan="7" class="text-center py-5 text-muted">
                            <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                            Đang tải dữ liệu...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Pagination footer --}}
        <div class="admin-card-footer d-flex flex-wrap align-items-center justify-content-between gap-3" id="ordersPaginationWrap">
            <div class="text-muted fs-7" id="ordersPaginationInfo">Hiển thị 0 - 0 trong 0 kết quả</div>
            <div class="pagination-links" id="ordersPaginationLinks"></div>
        </div>

    </div>

    {{-- Toast thong bao --}}
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100;">
        <div id="ordersToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2" id="ordersToastMessage">
                    <i class="fa-solid fa-circle-check"></i> Thao tác thành công.
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    {{-- Config element cho JS doc routes --}}
    <div id="ordersAppConfig"
         data-index-url="{{ route('admin.orders.index') }}"
         data-show-url="{{ route('admin.orders.show', ['order' => '__ID__']) }}"
         data-export-url="{{ route('admin.orders.export') }}"
         data-csrf="{{ csrf_token() }}">
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('admin/js/orders.js') }}"></script>
@endpush
