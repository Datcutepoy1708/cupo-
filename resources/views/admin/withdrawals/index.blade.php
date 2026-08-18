@extends('layouts.admin.app')

@section('page-title', 'Yêu cầu rút tiền')

@section('breadcrumb')
    <li class="breadcrumb-item active">Yêu cầu rút tiền</li>
@endsection

@push('styles')
    <link href="{{ asset('admin/css/withdrawals.css') }}" rel="stylesheet">
@endpush

@section('content')

    {{-- Stat cards --}}
    @include('admin.withdrawals.partials._stat-cards')

    {{-- Bảng danh sách --}}
    <div class="admin-card">

        {{-- Card Header: Tab filter + Tìm kiếm + Export --}}
        <div class="admin-card-header flex-column flex-md-row gap-3 align-items-start align-items-md-center">

            {{-- Status Tabs --}}
            <div class="withdrawal-tabs" id="withdrawalStatusTabs">
                <button class="withdrawal-tab active" data-status="">Tất cả</button>
                <button class="withdrawal-tab" data-status="pending">
                    Chờ duyệt
                    <span class="tab-badge pending" id="tab-badge-pending">0</span>
                </button>
                <button class="withdrawal-tab" data-status="approved">
                    Đã duyệt
                </button>
                <button class="withdrawal-tab" data-status="rejected">
                    Đã từ chối
                </button>
            </div>

            <div class="ms-auto d-flex gap-2">
                {{-- Search --}}
                <div class="withdrawal-search-wrap">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text"
                           id="withdrawalSearchInput"
                           class="withdrawal-search"
                           placeholder="Tìm tên shop, STK, chủ TK, ngân hàng...">
                </div>

                {{-- Export button --}}
                <a href="#" id="btnExportCsv" class="btn-withdrawal-export" title="Xuất CSV">
                    <i class="fa-solid fa-file-csv"></i>
                    Export CSV
                </a>
            </div>

        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="admin-table" id="withdrawalsTable">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Gian hàng / Người bán</th>
                        <th>Thông tin ngân hàng</th>
                        <th style="text-align: right;">Số tiền rút</th>
                        <th style="text-align: right;">Số dư ví hiện tại</th>
                        <th>Ngày yêu cầu</th>
                        <th>Trạng thái</th>
                        <th style="width: 130px; text-align: center;">Hành động</th>
                    </tr>
                </thead>
                <tbody id="withdrawalsTableBody">
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
    @include('admin.withdrawals.partials._reject-modal')

    {{-- Toast --}}
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100;">
        <div id="withdrawalToast" class="toast align-items-center border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body" id="withdrawalToastMsg"></div>
                <button type="button"
                        class="btn-close btn-close-white me-2 m-auto"
                        data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <div id="withdrawalsAppConfig"
        data-index-url="{{ route('admin.withdrawals.index') }}"
        data-export-url="{{ route('admin.withdrawals.export') }}"
        data-show-url="{{ url('admin/withdrawals') }}/__ID__"
        data-approve-url="{{ url('admin/withdrawals') }}/__ID__/approve"
        data-reject-url="{{ url('admin/withdrawals') }}/__ID__/reject"
        data-csrf="{{ csrf_token() }}"
        style="display:none;"></div>
    <script src="{{ asset('admin/js/withdrawals.js') }}"></script>
@endpush
