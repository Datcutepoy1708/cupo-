@extends('layouts.admin.app')

@section('page-title', 'Quản lý Khách hàng')

@section('breadcrumb')
    <li class="breadcrumb-item active">Khách hàng</li>
@endsection

@push('styles')
    <link href="{{ asset('admin/css/customers.css') }}" rel="stylesheet">
@endpush

@section('content')

    {{-- Stat cards --}}
    @include('admin.customers.partials._stat-cards')

    {{-- Bảng danh sách --}}
    <div class="admin-card">

        {{-- Card Header: Tab filter + Tìm kiếm + Export --}}
        <div class="admin-card-header flex-column flex-md-row gap-3 align-items-start align-items-md-center">

            {{-- Status Tabs --}}
            <div class="customer-tabs" id="customerStatusTabs">
                <button class="customer-tab active" data-status="">Tất cả</button>
                <button class="customer-tab" data-status="active">
                    Hoạt động
                </button>
                <button class="customer-tab" data-status="blocked">
                    Bị khóa
                    <span class="tab-badge blocked" id="tab-badge-blocked">0</span>
                </button>
            </div>

            <div class="ms-auto d-flex gap-2">
                {{-- Search --}}
                <div class="customer-search-wrap">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text"
                           id="customerSearchInput"
                           class="customer-search"
                           placeholder="Tìm tên, email, SĐT...">
                </div>
                {{-- Export button --}}
                <a href="#" id="btnExportCsv" class="btn-customer-export" title="Xuất CSV">
                    <i class="fa-solid fa-file-csv"></i>
                    Export CSV
                </a>
            </div>

        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="admin-table" id="customersTable">
                <thead>
                    <tr>
                        <th style="width: 52px;">#</th>
                        <th>Khách hàng</th>
                        <th>Email</th>
                        <th>Số điện thoại</th>
                        <th style="text-align: center;">Đơn hàng</th>
                        <th>Ngày tạo</th>
                        <th>Trạng thái</th>
                        <th style="width: 100px; text-align: center;">Hành động</th>
                    </tr>
                </thead>
                <tbody id="customersTableBody">
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
    @include('admin.customers.partials._detail-modal')

    {{-- Toast --}}
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100;">
        <div id="customerToast" class="toast align-items-center border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body" id="customerToastMsg"></div>
                <button type="button"
                        class="btn-close btn-close-white me-2 m-auto"
                        data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <div id="customersAppConfig"
        data-index-url="{{ route('admin.customers.index') }}"
        data-export-url="{{ route('admin.customers.export') }}"
        data-show-url="{{ url('admin/customers') }}/__ID__"
        data-block-url="{{ url('admin/customers') }}/__ID__/block"
        data-unblock-url="{{ url('admin/customers') }}/__ID__/unblock"
        data-csrf="{{ csrf_token() }}"
        style="display:none;"></div>
    <script src="{{ asset('admin/js/customers.js') }}"></script>
@endpush
