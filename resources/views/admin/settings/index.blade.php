@extends('layouts.admin.app')

@section('page-title', 'Cài đặt hệ thống')

@section('breadcrumb')
    <li class="breadcrumb-item active">Cài đặt hệ thống</li>
@endsection

@push('styles')
    <link href="{{ asset('admin/css/settings.css') }}" rel="stylesheet">
@endpush

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4 d-flex align-items-center gap-2" role="alert">
        <i class="fa-solid fa-circle-check fs-5"></i>
        <div>{{ session('success') }}</div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <div class="fw-bold mb-1"><i class="fa-solid fa-triangle-exclamation me-1"></i> Có lỗi xảy ra trong quá trình lưu:</div>
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<form id="settingsForm" action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="row g-4">
        {{-- ===== COT TRAI: Danh sach Tab dieu huong ===== --}}
        <div class="col-lg-3">
            <div class="settings-nav-card">
                <div class="settings-nav-header">
                    <h6 class="fw-bold mb-0">Cài đặt hệ thống</h6>
                    <small class="text-muted">Chọn nhóm cấu hình</small>
                </div>
                <div class="settings-nav-list" id="settingsNavList">
                    <button type="button" class="settings-nav-item active" data-target="#tab-general">
                        <i class="fa-solid fa-sliders"></i>
                        <span>Cài đặt Chung</span>
                    </button>
                    <button type="button" class="settings-nav-item" data-target="#tab-seller">
                        <i class="fa-solid fa-store"></i>
                        <span>Gian hàng & Hoa hồng</span>
                    </button>
                    <button type="button" class="settings-nav-item" data-target="#tab-order">
                        <i class="fa-solid fa-truck-fast"></i>
                        <span>Đơn hàng & Vận chuyển</span>
                    </button>
                    <button type="button" class="settings-nav-item" data-target="#tab-payment">
                        <i class="fa-solid fa-credit-card"></i>
                        <span>Cổng thanh toán</span>
                    </button>
                    <button type="button" class="settings-nav-item" data-target="#tab-mail">
                        <i class="fa-solid fa-envelope-open-text"></i>
                        <span>Cấu hình Email (SMTP)</span>
                    </button>
                    <button type="button" class="settings-nav-item" data-target="#tab-seo">
                        <i class="fa-solid fa-share-nodes"></i>
                        <span>SEO & Mạng xã hội</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- ===== COT PHAI: Noi dung tab tuong ung ===== --}}
        <div class="col-lg-9">
            <div class="settings-content-card">
                @include('admin.settings.partials._tab-general')
                @include('admin.settings.partials._tab-seller')
                @include('admin.settings.partials._tab-order')
                @include('admin.settings.partials._tab-payment')
                @include('admin.settings.partials._tab-mail')
                @include('admin.settings.partials._tab-seo')

                {{-- Footer Save Button --}}
                <div class="settings-footer-actions mt-4 pt-3 border-top d-flex align-items-center justify-content-between">
                    <span class="text-muted small">
                        <i class="fa-solid fa-circle-info me-1"></i>Thay đổi sẽ có hiệu lực ngay lập tức trên toàn hệ thống.
                    </span>
                    <button type="submit" class="btn btn-danger px-4 fw-bold" id="btnSaveSettings">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Lưu thay đổi
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- Toast thong bao --}}
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100;">
    <div id="settingsToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2" id="settingsToastMessage">
                <i class="fa-solid fa-circle-check"></i> Thao tác thành công.
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

{{-- Config Element cho JS --}}
<div id="settingsAppConfig"
     data-csrf="{{ csrf_token() }}"
     data-test-mail-url="{{ route('admin.settings.test-mail') }}">
</div>

@endsection

@push('scripts')
    <script src="{{ asset('admin/js/settings.js') }}"></script>
@endpush
