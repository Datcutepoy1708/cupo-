@extends('layouts.admin.app')

@section('page-title', 'Quản lý Banner trang chủ')

@section('breadcrumb')
    <li class="breadcrumb-item active">Banner trang chủ</li>
@endsection

@push('styles')
    <link href="{{ asset('admin/css/banners.css') }}" rel="stylesheet">
@endpush

@section('content')

    {{-- Stat cards --}}
    @include('admin.banners.partials.stat-cards')

    {{-- Bảng danh sách Banner --}}
    <div class="admin-card">

        {{-- Card Header: Tab filter + vị trí + Tìm kiếm + Nút Xem trước + Nút Thêm --}}
        <div class="admin-card-header flex-column flex-md-row gap-3 align-items-start align-items-md-center">

            <div class="seller-tabs" id="statusTabs">
                <button class="seller-tab active" data-status="">Tất cả</button>
                <button class="seller-tab" data-status="active">
                    <span class="chip-dot green me-1"></span>
                    Đang hiển thị
                    <span class="tab-badge pending" id="tab-badge-active" style="background:#e8f5e9;color:#2e7d32;">0</span>
                </button>
                <button class="seller-tab" data-status="inactive">
                    <span class="chip-dot red me-1"></span>
                    Đã ẩn
                </button>
                <button class="seller-tab" data-status="expired">
                    <i class="fa-solid fa-clock me-1" style="font-size:11px;"></i>
                    Hết hạn
                </button>
            </div>

            <div class="ms-auto d-flex flex-wrap gap-2 align-items-center">
                {{-- Lọc vị trí --}}
                <select id="bannerPositionFilter" class="form-select form-select-sm" style="width: 160px;">
                    <option value="">Tất cả vị trí</option>
                    <option value="homepage_hero">Slide chính</option>
                    <option value="homepage_mid">Giữa trang chủ</option>
                    <option value="category_top">Đầu trang danh mục</option>
                    <option value="sidebar">Thanh bên</option>
                </select>

                {{-- Ô tìm kiếm --}}
                <div class="seller-search-wrap">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text"
                           id="bannerSearchInput"
                           class="seller-search"
                           placeholder="Tìm tiêu đề, link banner...">
                </div>

                {{-- Nút Xem trước giao diện người mua --}}
                <button type="button" id="btnPreviewStorefront" class="btn-banner-preview" title="Xem trước giao diện người mua">
                    <i class="fa-solid fa-desktop me-1"></i>
                    Xem trước trang web
                </button>

                {{-- Nút Thêm Banner --}}
                <button type="button" id="btnAddBanner" class="btn-banner-primary">
                    <i class="fa-solid fa-plus"></i>
                    Thêm Banner
                </button>
            </div>

        </div>

        {{-- Bulk action toolbar --}}
        <div class="bulk-toolbar" id="bulkToolbar" style="display:none;">
            <span class="bulk-info">
                <i class="fa-solid fa-square-check"></i>
                Đã chọn <strong id="bulkCount">0</strong> banner
            </span>
            <div class="bulk-actions">
                <button type="button" class="btn-bulk-approve" id="btnBulkShow">
                    <i class="fa-solid fa-eye"></i>
                    Hiển thị tất cả
                </button>
                <button type="button" class="btn-bulk-reject" id="btnBulkHide" style="background:#e65100;">
                    <i class="fa-solid fa-eye-slash"></i>
                    Ẩn tất cả
                </button>
                <button type="button" class="btn-bulk-reject" id="btnBulkDelete">
                    <i class="fa-solid fa-trash"></i>
                    Xóa tất cả
                </button>
                <button type="button" class="btn-bulk-clear" id="btnBulkClear">
                    <i class="fa-solid fa-xmark"></i>
                    Bỏ chọn
                </button>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="admin-table" id="bannersTable">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">
                            <input type="checkbox" class="seller-checkbox" id="checkAllBanners" title="Chọn tất cả">
                        </th>
                        <th style="width: 48px;">#</th>
                        <th>Hình ảnh & Tiêu đề</th>
                        <th>Vị trí</th>
                        <th>Đường dẫn (URL)</th>
                        <th style="text-align: center; width: 80px;">Ưu tiên</th>
                        <th style="text-align: center; width: 100px;">Trạng thái</th>
                        <th style="width: 100px; text-align: center;">Hành động</th>
                    </tr>
                </thead>
                <tbody id="bannersTableBody">
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
    @include('admin.banners.partials.form-modal')
    @include('admin.banners.partials.detail-modal')

    {{-- Modal Xem trước giao diện người mua (Client Preview Modal) --}}
    <div class="modal fade" id="bannerClientPreviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header py-2 bg-dark text-white">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-desktop text-success"></i>
                        <h6 class="modal-title text-white mb-0">Xem trước giao diện trang chủ Cupo (Client Storefront)</h6>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('home') }}" target="_blank" class="btn btn-sm btn-outline-light">
                            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Mở trong cửa sổ mới
                        </a>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                </div>
                <div class="modal-body p-0" style="background: #f8f9fa;">
                    <iframe id="clientPreviewFrame" src="" style="width: 100%; height: 100%; border: none;"></iframe>
                </div>
            </div>
        </div>
    </div>

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
    <div id="bannersAppConfig"
        data-index-url="{{ route('admin.banners.index') }}"
        data-store-url="{{ route('admin.banners.store') }}"
        data-update-url="{{ url('admin/banners') }}/__ID__"
        data-destroy-url="{{ url('admin/banners') }}/__ID__"
        data-bulk-status-url="{{ route('admin.banners.bulk-status') }}"
        data-bulk-delete-url="{{ route('admin.banners.bulk-delete') }}"
        data-upload-url="{{ route('admin.upload') }}"
        data-home-url="{{ route('home') }}"
        data-csrf="{{ csrf_token() }}"
        style="display:none;"></div>
    <script src="{{ asset('admin/js/banners.js') }}"></script>
@endpush
