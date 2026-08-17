@extends('layouts.admin.app')

@section('page-title', 'Kháng nghị & Hỗ trợ Seller')

@section('breadcrumb')
    <li class="breadcrumb-item active">Kháng nghị & Hỗ trợ Seller</li>
@endsection

@push('styles')
    <link href="{{ asset('admin/css/support-tickets.css') }}" rel="stylesheet">
@endpush

@section('content')

    {{-- Stat cards --}}
    @include('admin.support-tickets.partials._stat-cards')

    {{-- Bảng danh sách --}}
    <div class="admin-card">

        {{-- Card Header: Tab filter + Dropdown Category + Tìm kiếm + Export --}}
        <div class="admin-card-header flex-column flex-md-row gap-3 align-items-start align-items-md-center">

            {{-- Status Tabs --}}
            <div class="ticket-tabs" id="ticketStatusTabs">
                <button class="ticket-tab active" data-status="">Tất cả</button>
                <button class="ticket-tab" data-status="open">
                    Mới mở
                    <span class="tab-badge open" id="tab-badge-open">0</span>
                </button>
                <button class="ticket-tab" data-status="in_review">
                    Đang xử lý
                </button>
                <button class="ticket-tab" data-status="resolved">
                    Đã giải quyết
                </button>
                <button class="ticket-tab" data-status="closed">
                    Đã đóng
                </button>
            </div>

            <div class="ms-auto d-flex gap-2 flex-wrap">
                {{-- Category Filter --}}
                <select id="categoryFilter" class="form-select form-select-sm ticket-category-select">
                    <option value="">-- Tất cả danh mục --</option>
                    <option value="account_blocked">Kháng nghị khóa tài khoản</option>
                    <option value="withdrawal_issue">Sự cố rút tiền</option>
                    <option value="product_rejected">Kháng nghị duyệt sản phẩm</option>
                    <option value="commission_fee">Thắc mắc hoa hồng & phí</option>
                    <option value="other">Hỗ trợ chung / Khác</option>
                </select>

                {{-- Search --}}
                <div class="ticket-search-wrap">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text"
                           id="ticketSearchInput"
                           class="ticket-search"
                           placeholder="Tìm tiêu đề, tên shop, email...">
                </div>

                {{-- Export button --}}
                <a href="#" id="btnExportCsv" class="btn-ticket-export" title="Xuất CSV">
                    <i class="fa-solid fa-file-csv"></i>
                    Export CSV
                </a>
            </div>

        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="admin-table" id="ticketsTable">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Gian hàng / Seller</th>
                        <th>Danh mục</th>
                        <th>Tiêu đề yêu cầu</th>
                        <th>Ngày tạo</th>
                        <th>Trạng thái</th>
                        <th style="width: 120px; text-align: center;">Hành động</th>
                    </tr>
                </thead>
                <tbody id="ticketsTableBody">
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <div class="spinner-border spinner-border-sm text-danger me-2" role="status"></div>
                            Đang tải dữ liệu...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="d-flex align-items-center justify-content-between px-4 py-3 border-top"
             id="paginationWrap"
             style="display: none !important;">
            <span class="text-muted" style="font-size: 13px;" id="paginationInfo"></span>
            <div id="paginationLinks" class="d-flex gap-1"></div>
        </div>

    </div>

    {{-- Modals --}}
    @include('admin.support-tickets.partials._response-modal')

    {{-- Toast --}}
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100;">
        <div id="ticketToast" class="toast align-items-center border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body" id="ticketToastMsg"></div>
                <button type="button"
                        class="btn-close btn-close-white me-2 m-auto"
                        data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <div id="ticketsAppConfig"
        data-index-url="{{ route('admin.support-tickets.index') }}"
        data-export-url="{{ route('admin.support-tickets.export') }}"
        data-show-url="{{ url('admin/support-tickets') }}/__ID__"
        data-in-review-url="{{ url('admin/support-tickets') }}/__ID__/in-review"
        data-respond-url="{{ url('admin/support-tickets') }}/__ID__/respond"
        data-csrf="{{ csrf_token() }}"
        style="display:none;"></div>
    <script src="{{ asset('admin/js/support-tickets.js') }}"></script>
@endpush
