@extends('layouts.admin.app')

@section('page-title', 'Quản lý Danh mục')

@section('breadcrumb')
    <li class="breadcrumb-item active">Danh mục</li>
@endsection

@push('styles')
    <link href="{{ asset('admin/css/categories.css') }}" rel="stylesheet">
@endpush

@section('content')

    {{-- Context cho categories.js (Rule 20) --}}
    <div id="categoriesApp"
        data-data-url="{{ route('admin.categories.data') }}"
        data-store-url="{{ route('admin.categories.store') }}"
        data-update-url="{{ url('admin/categories') }}/__ID__"
        data-destroy-url="{{ url('admin/categories') }}/__ID__"
        data-csrf="{{ csrf_token() }}"
        style="display:none;"></div>

    <div class="admin-card" style="border-radius:12px; overflow:hidden;">

        {{-- ===== Page header ===== --}}
        <div class="cat-page-header">
            <div>
                <h4 class="cat-page-title">Danh mục</h4>
                <p class="cat-page-sub">Quản lý danh mục hàng hóa</p>
            </div>
            <button type="button" id="btnAddCategory" class="btn-cat-primary">
                <i class="fa-solid fa-plus"></i>
                Thêm danh mục
            </button>
        </div>

        {{-- ===== Toolbar ===== --}}
        <div class="cat-toolbar-bar">
            <div class="cat-search-wrap">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="catSearchInput" class="cat-search" placeholder="Tìm theo tên danh mục...">
            </div>
            <div class="cat-filter-chips">
                <button class="cat-chip active" id="chipAll" data-filter="all">Tất cả</button>
                <button class="cat-chip" id="chipActive" data-filter="active">
                    <span class="chip-dot green"></span>Hoạt động
                </button>
                <button class="cat-chip" id="chipHidden" data-filter="hidden">
                    <span class="chip-dot red"></span>Đã ẩn
                </button>
                <button class="cat-chip" id="chipParent" data-filter="parent">
                    <i class="fa-solid fa-folder me-1" style="font-size:11px;"></i>Danh mục gốc
                </button>
            </div>
        </div>

        {{-- ===== Table ===== --}}
        <div class="table-responsive">
            <table class="cat-table" id="catTable">
                <thead>
                    <tr>
                        <th class="col-check">
                            <input type="checkbox" class="cat-checkbox" id="checkAll">
                        </th>
                        <th class="col-name">TÊN DANH MỤC</th>
                        <th class="col-slug">SLUG</th>
                        <th class="col-children">CON</th>
                        <th class="col-status">HIỂN THỊ</th>
                        <th class="col-actions">HÀNH ĐỘNG</th>
                    </tr>
                </thead>
                <tbody id="catTableBody">
                    <tr>
                        <td colspan="6" class="cat-loading-cell">
                            <span class="cat-spinner"></span>
                            Đang tải dữ liệu...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- ===== Footer: select info + pagination ===== --}}
        <div class="cat-table-footer" id="catTableFooter">
            <span class="cat-select-info" id="catSelectInfo">0 dòng đã chọn</span>
            <div class="cat-pagination" id="catPagination"></div>
        </div>

    </div>

    {{-- ===== Modal: Tạo / Sửa ===== --}}
    <div class="modal fade" id="catModal" tabindex="-1" aria-labelledby="catModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 460px;">
            <div class="modal-content cat-modal-content">

                <div class="cat-modal-header">
                    <h5 class="modal-title" id="catModalLabel">Thêm danh mục mới</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="cat-modal-body">
                    <form id="catForm" novalidate>

                        <div class="cat-field">
                            <label for="catName" class="cat-label">
                                Tên danh mục <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="catName" class="cat-input"
                                placeholder="VD: Thời trang, Điện tử..." maxlength="255">
                            <span class="cat-invalid" id="catNameError"></span>
                        </div>

                        <div class="cat-field">
                            <label for="catParentId" class="cat-label">Danh mục cha</label>
                            <select id="catParentId" class="cat-select">
                                <option value="">— Là danh mục gốc —</option>
                            </select>
                            <p class="cat-hint">Để trống nếu đây là danh mục cấp cao nhất.</p>
                        </div>

                        <div class="cat-field">
                            <label class="cat-label">Trạng thái</label>
                            <div class="cat-toggle-row">
                                <label class="cat-toggle-switch">
                                    <input type="checkbox" id="catStatusToggle" checked>
                                    <span class="cat-toggle-track"></span>
                                </label>
                                <span class="cat-toggle-label" id="catStatusLabel">Hiển thị</span>
                            </div>
                        </div>

                    </form>
                </div>

                <div class="cat-modal-footer">
                    <button type="button" class="btn-cat-cancel" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" id="btnCatSave" class="btn-cat-primary">Lưu</button>
                </div>

            </div>
        </div>
    </div>

    {{-- ===== Toast ===== --}}
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index:1100;">
        <div id="catToast" class="toast align-items-center border-0" role="alert" aria-live="assertive">
            <div class="d-flex">
                <div class="toast-body fw-500" id="catToastMsg"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('admin/js/categories.js') }}"></script>
@endpush
