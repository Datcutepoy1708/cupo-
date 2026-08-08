@extends('layouts.admin.app')

@section('page-title', 'Quản lý Danh mục')

@section('breadcrumb')
    <li class="breadcrumb-item active">Quản lý Danh mục</li>
@endsection

@push('styles')
    <link href="{{ asset('admin/css/categories.css') }}" rel="stylesheet">
@endpush

@section('content')

    {{-- ===== Context cho categories.js (Rule 20: không inline JS) ===== --}}
    <div id="categoriesApp"
        data-data-url="{{ route('admin.categories.data') }}"
        data-store-url="{{ route('admin.categories.store') }}"
        data-update-url="{{ url('admin/categories') }}/__ID__"
        data-destroy-url="{{ url('admin/categories') }}/__ID__"
        data-csrf="{{ csrf_token() }}"
        style="display:none;">
    </div>

    {{-- ===== Stat cards ===== --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-4">
            <div class="cat-stat-card">
                <div class="cat-stat-icon red">
                    <i class="fa-solid fa-tags"></i>
                </div>
                <div>
                    <div class="cat-stat-num" id="statTotal">—</div>
                    <div class="cat-stat-label">Tổng danh mục</div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="cat-stat-card">
                <div class="cat-stat-icon green">
                    <i class="fa-solid fa-folder"></i>
                </div>
                <div>
                    <div class="cat-stat-num" id="statParent">—</div>
                    <div class="cat-stat-label">Danh mục gốc</div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="cat-stat-card">
                <div class="cat-stat-icon orange">
                    <i class="fa-solid fa-folder-open"></i>
                </div>
                <div>
                    <div class="cat-stat-num" id="statChildren">—</div>
                    <div class="cat-stat-label">Danh mục con</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Card chính: Tree view ===== --}}
    <div class="admin-card">

        {{-- Card header --}}
        <div class="admin-card-header">
            <div class="cat-toolbar">
                <div>
                    <h6 class="mb-0 fw-bold" style="font-size:15px;">Cây danh mục hàng hóa</h6>
                    <p class="text-muted mb-0" style="font-size:12px; margin-top:2px;">
                        Click vào danh mục gốc để xem / ẩn các danh mục con
                    </p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="cat-search-wrap">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text"
                            id="catSearchInput"
                            class="cat-search"
                            placeholder="Tìm danh mục...">
                    </div>
                    <button type="button" id="btnAddCategory" class="btn-cat-add">
                        <i class="fa-solid fa-plus"></i>
                        Thêm danh mục
                    </button>
                </div>
            </div>
        </div>

        {{-- Tree --}}
        <div id="categoryTree" class="cat-tree-wrap">
            {{-- Rendered by categories.js --}}
        </div>

    </div>

    {{-- ===== Modal: Tạo / Sửa danh mục ===== --}}
    <div class="modal fade" id="catModal" tabindex="-1" aria-labelledby="catModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-cat" style="max-width: 440px;">
            <div class="modal-content" style="border-radius:12px; border:none; box-shadow: 0 10px 40px rgba(0,0,0,0.15);">

                <div class="modal-header">
                    <h5 class="modal-title" id="catModalTitle">Thêm danh mục mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body modal-cat">
                    <form id="catForm" novalidate>

                        <div class="mb-3">
                            <label class="form-label" for="catName">
                                Tên danh mục <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                id="catName"
                                class="form-control"
                                placeholder="VD: Thời trang, Điện tử, Đồ gia dụng..."
                                maxlength="255">
                            <div class="invalid-feedback">Vui lòng nhập tên danh mục.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="catParentId">Danh mục cha</label>
                            <select id="catParentId" class="form-select">
                                <option value="">-- Là danh mục gốc --</option>
                            </select>
                            <div class="form-text">Để trống nếu đây là danh mục cấp cao nhất.</div>
                        </div>

                        <div class="mb-1">
                            <label class="form-label" for="catStatus">Trạng thái</label>
                            <select id="catStatus" class="form-select">
                                <option value="1">Hoạt động</option>
                                <option value="0">Ẩn</option>
                            </select>
                        </div>

                    </form>
                </div>

                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" id="btnCatSave" class="btn-modal-save">Lưu</button>
                </div>

            </div>
        </div>
    </div>

    {{-- ===== Toast ===== --}}
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100;">
        <div id="catToast" class="toast align-items-center border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body" id="catToastMsg"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('admin/js/categories.js') }}"></script>
@endpush
