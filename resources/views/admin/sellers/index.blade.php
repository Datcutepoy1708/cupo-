@extends('layouts.admin.app')

@section('page-title', 'Quản lý Seller')

@section('breadcrumb')
    <li class="breadcrumb-item active">Quản lý Seller</li>
@endsection

@push('styles')
    <link href="{{ asset('admin/css/sellers.css') }}" rel="stylesheet">
@endpush

@section('content')

    {{-- Stat cards --}}
    @include('admin.sellers.partials.stat-cards')

    {{-- Bảng danh sách --}}
    <div class="admin-card">

        {{-- Card Header: Tab filter + Tìm kiếm --}}
        <div class="admin-card-header flex-column flex-md-row gap-3 align-items-start align-items-md-center">

            <div class="seller-tabs" id="statusTabs">
                <button class="seller-tab active" data-status="">Tất cả</button>
                <button class="seller-tab" data-status="pending">
                    Chờ duyệt
                    <span class="tab-badge pending" id="tab-badge-pending">0</span>
                </button>
                <button class="seller-tab" data-status="approved">Đã duyệt</button>
                <button class="seller-tab" data-status="rejected">Từ chối</button>
                <button class="seller-tab" data-status="blocked">Đã khóa</button>
            </div>

            <div class="ms-auto d-flex gap-2">
                <div class="seller-search-wrap">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text"
                           id="sellerSearchInput"
                           class="seller-search"
                           placeholder="Tìm tên shop, email...">
                </div>
            </div>

        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="admin-table" id="sellersTable">
                <thead>
                    <tr>
                        <th style="width: 48px;">#</th>
                        <th>Gian hàng</th>
                        <th>Chủ shop</th>
                        <th>Loại hình</th>
                        <th>Hoa hồng</th>
                        <th>Ngày đăng ký</th>
                        <th>Trạng thái</th>
                        <th style="width: 80px; text-align: center;">Hành động</th>
                    </tr>
                </thead>
                <tbody id="sellersTableBody">
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
    @include('admin.sellers.partials.detail-modal')
    @include('admin.sellers.partials.action-modal')

    {{-- Toast thông báo --}}
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100;">
        <div id="actionToast" class="toast align-items-center border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body" id="toastMsg"></div>
                <button type="button"
                        class="btn-close btn-close-white me-2 m-auto"
                        data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        window.ADMIN_SELLERS_ROUTES = {
            index:   '{{ route('admin.sellers.index') }}',
            approve: '{{ url('admin/sellers') }}/__ID__/approve',
            reject:  '{{ url('admin/sellers') }}/__ID__/reject',
            block:   '{{ url('admin/sellers') }}/__ID__/block',
            csrf:    '{{ csrf_token() }}',
        };
    </script>
    <script src="{{ asset('admin/js/sellers.js') }}"></script>
@endpush