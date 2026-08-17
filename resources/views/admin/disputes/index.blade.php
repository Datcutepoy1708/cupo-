@extends('layouts.admin.app')

@section('page-title', 'Tranh chấp & Khiếu nại')

@section('breadcrumb')
    <li class="breadcrumb-item active">Tranh chấp & Khiếu nại</li>
@endsection

@push('styles')
    <link href="{{ asset('admin/css/disputes.css') }}" rel="stylesheet">
@endpush

@section('content')

    {{-- Stat cards --}}
    @include('admin.disputes.partials._stat-cards')

    {{-- Bảng danh sách --}}
    <div class="admin-card">

        {{-- Card Header: Tab filter + Tìm kiếm + Export --}}
        <div class="admin-card-header flex-column flex-md-row gap-3 align-items-start align-items-md-center">

            {{-- Status Tabs --}}
            <div class="dispute-tabs" id="disputeStatusTabs">
                <button class="dispute-tab active" data-status="">Tất cả</button>
                <button class="dispute-tab" data-status="pending">
                    Chờ xử lý
                    <span class="tab-badge pending" id="tab-badge-pending">0</span>
                </button>
                <button class="dispute-tab" data-status="in_progress">
                    Đang xử lý
                </button>
                <button class="dispute-tab" data-status="refunded">
                    Đã hoàn tiền
                </button>
                <button class="dispute-tab" data-status="rejected">
                    Đã từ chối
                </button>
            </div>

            <div class="ms-auto d-flex gap-2">
                {{-- Search --}}
                <div class="dispute-search-wrap">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text"
                           id="disputeSearchInput"
                           class="dispute-search"
                           placeholder="Tìm mã đơn, email người mua...">
                </div>
                {{-- Export button --}}
                <a href="#" id="btnExportCsv" class="btn-dispute-export" title="Xuất CSV">
                    <i class="fa-solid fa-file-csv"></i>
                    Export CSV
                </a>
            </div>

        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="admin-table" id="disputesTable">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Khách hàng</th>
                        <th>Đơn hàng</th>
                        <th>Gian hàng</th>
                        <th style="max-width: 250px;">Lý do khiếu nại</th>
                        <th>Ngày tạo</th>
                        <th>Trạng thái</th>
                        <th style="width: 140px; text-align: center;">Hành động</th>
                    </tr>
                </thead>
                <tbody id="disputesTableBody">
                    <tr>
                        <td colspan="8" class="text-center py-4">
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
    @include('admin.disputes.partials._decision-modal')

    {{-- Toast --}}
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100;">
        <div id="disputeToast" class="toast align-items-center border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body" id="disputeToastMsg"></div>
                <button type="button"
                        class="btn-close btn-close-white me-2 m-auto"
                        data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <div id="disputesAppConfig"
        data-index-url="{{ route('admin.disputes.index') }}"
        data-export-url="{{ route('admin.disputes.export') }}"
        data-show-url="{{ url('admin/disputes') }}/__ID__"
        data-process-url="{{ url('admin/disputes') }}/__ID__/process"
        data-refund-url="{{ url('admin/disputes') }}/__ID__/refund"
        data-reject-url="{{ url('admin/disputes') }}/__ID__/reject"
        data-csrf="{{ csrf_token() }}"
        style="display:none;"></div>
    <script src="{{ asset('admin/js/disputes.js') }}"></script>
@endpush
