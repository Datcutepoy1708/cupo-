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
        data-export-url="{{ route('admin.categories.export') }}"
        data-bulk-status-url="{{ route('admin.categories.bulk-status') }}"
        data-bulk-delete-url="{{ route('admin.categories.bulk-delete') }}"
        data-upload-url="{{ route('admin.upload') }}"
        data-csrf="{{ csrf_token() }}"
        style="display:none;"></div>

    <div class="admin-card" style="border-radius:12px; overflow:hidden;">

        {{-- ===== Page header ===== --}}
        <div class="cat-page-header">
            <div>
                <h4 class="cat-page-title">Danh mục</h4>
                <p class="cat-page-sub">Quản lý danh mục hàng hóa</p>
            </div>
            <div class="d-flex gap-2">
                <a href="#" id="btnExportCatCsv" class="btn-cat-export" title="Xuất file CSV">
                    <i class="fa-solid fa-file-csv"></i>
                    Export CSV
                </a>
                <button type="button" id="btnAddCategory" class="btn-cat-primary">
                    <i class="fa-solid fa-plus"></i>
                    Thêm danh mục
                </button>
            </div>
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

        {{-- ===== Bulk Action Toolbar ===== --}}
        <div class="cat-bulk-toolbar" id="catBulkToolbar" style="display: none;">
            <span class="cat-bulk-info">
                <i class="fa-solid fa-square-check"></i>
                Đã chọn <strong id="catBulkCount">0</strong> danh mục
            </span>
            <div class="cat-bulk-actions">
                <button type="button" class="btn-cat-bulk-show" id="btnBulkShow">
                    <i class="fa-solid fa-eye me-1"></i>Hiển thị tất cả
                </button>
                <button type="button" class="btn-cat-bulk-hide" id="btnBulkHide">
                    <i class="fa-solid fa-eye-slash me-1"></i>Ẩn tất cả
                </button>
                <button type="button" class="btn-cat-bulk-delete" id="btnBulkDelete">
                    <i class="fa-solid fa-trash me-1"></i>Xóa tất cả
                </button>
                <button type="button" class="btn-cat-bulk-clear" id="btnBulkClear">
                    <i class="fa-solid fa-xmark me-1"></i>Bỏ chọn
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
                            <p class="cat-hint">Dể trống nếu đây là danh mục cấp cao nhất.</p>
                        </div>

                        {{-- Hình ảnh danh mục --}}
                        <div class="cat-field">
                            <label class="cat-label">Hình ảnh danh mục</label>
                            <div class="input-group">
                                <input type="text" id="catImage" class="cat-input" style="border-radius: 8px 0 0 8px;"
                                    placeholder="Dán URL ảnh hoặc bấm chọn tệp...">
                                <input type="file" id="catFilePicker" class="d-none" accept="image/jpeg,image/png,image/jpg,image/webp,image/gif">
                                <button type="button" id="btnUploadCatImage" class="btn btn-outline-secondary" style="border-radius: 0 8px 8px 0;" title="Tải ảnh lên từ máy tính">
                                    <i class="fa-solid fa-cloud-arrow-up me-1"></i> Tải lên
                                </button>
                            </div>
                            <div id="catImagePreviewWrap" class="d-none mt-2">
                                <div class="cat-img-preview-box">
                                    <img id="catImagePreview" src="" alt="Preview" class="img-fluid rounded">
                                    <button type="button" id="btnClearCatImage" class="cat-img-clear-btn" title="Xóa ảnh">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                            </div>
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
