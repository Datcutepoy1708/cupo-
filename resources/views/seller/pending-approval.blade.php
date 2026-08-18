@extends('layouts.client')

@section('title', 'Trạng thái xét duyệt gian hàng - Cupo')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @php
                $status = $sellerProfile->status ?? 'pending';
                $adminNote = $sellerProfile->admin_note ?? '';
            @endphp

            @if ($status === 'rejected')
                {{-- TRẠNG THÁI: BỊ TỪ CHỐI -> CHO PHÉP NỘP LẠI HỒ SƠ --}}
                <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="card-header bg-danger text-white p-4 text-center">
                        <div class="display-5 mb-2">
                            <i class="fa-solid fa-circle-xmark"></i>
                        </div>
                        <h3 class="fw-bold mb-1">Hồ sơ đăng ký đã bị từ chối</h3>
                        <p class="text-white-50 mb-0">Ban Quản Trị Cupo đã xem xét và chưa thể phê duyệt gian hàng của bạn.</p>
                    </div>
                    <div class="card-body p-4 p-md-5">
                        <div class="alert alert-danger bg-danger bg-opacity-10 border-danger border-opacity-25 rounded-3 p-3 mb-4">
                            <h6 class="fw-bold text-danger mb-2">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i>Lý do từ chối từ Admin:
                            </h6>
                            <p class="mb-0 text-dark" style="white-space: pre-line;">{{ $adminNote ?: 'Thông tin hồ sơ chưa đáp ứng tiêu chuẩn kiểm duyệt của sàn Cupo.' }}</p>
                        </div>

                        <div class="text-muted small mb-4">
                            <p class="mb-2"><strong>Bạn cần làm gì tiếp theo?</strong></p>
                            <ul class="ps-3 mb-0">
                                <li>Kiểm tra lại thông tin số CCCD/MST, địa chỉ kinh doanh hoặc ảnh chứng từ theo lý do trên.</li>
                                <li>Bấm nút <strong>"Cập nhật & Nộp lại hồ sơ"</strong> bên dưới để chỉnh sửa và gửi lại cho Ban Quản Trị.</li>
                            </ul>
                        </div>

                        <div class="d-flex flex-wrap gap-2 justify-content-center">
                            <a href="{{ route('profile.show', ['tab' => 'sellerChannel']) }}" class="btn btn-danger px-4 py-2 fw-semibold">
                                <i class="fa-solid fa-pen-to-square me-1"></i>Cập nhật & Nộp lại hồ sơ
                            </a>
                            <a href="{{ route('home') }}" class="btn btn-outline-secondary px-4 py-2">
                                <i class="fa-solid fa-house me-1"></i>Về trang chủ
                            </a>
                        </div>
                    </div>
                </div>

            @elseif ($status === 'blocked')
                {{-- TRẠNG THÁI: BỊ KHÓA --}}
                <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="card-header bg-dark text-white p-4 text-center">
                        <div class="display-5 mb-2 text-warning">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <h3 class="fw-bold mb-1">Gian hàng đang bị tạm khóa</h3>
                        <p class="text-white-50 mb-0">Gian hàng của bạn hiện đang tạm ngưng hoạt động trên sàn Cupo.</p>
                    </div>
                    <div class="card-body p-4 p-md-5 text-center">
                        <p class="text-muted mb-4">
                            Nếu bạn cho rằng đây là sự nhầm lẫn, vui lòng liên hệ bộ phận Chăm sóc khách hàng hoặc gửi yêu cầu kháng nghị để được hỗ trợ mở lại.
                        </p>
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="{{ route('home') }}" class="btn btn-primary px-4">
                                <i class="fa-solid fa-house me-1"></i>Về trang chủ
                            </a>
                        </div>
                    </div>
                </div>

            @else
                {{-- TRẠNG THÁI: CHỜ DUYỆT (PENDING) --}}
                <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="card-header text-white p-4 text-center" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                        <div class="display-5 mb-2">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                        <h3 class="fw-bold mb-1">Hồ sơ đang chờ phê duyệt</h3>
                        <p class="text-white-50 mb-0">Cảm ơn bạn đã đăng ký mở gian hàng trên sàn TMĐT Cupo!</p>
                    </div>
                    <div class="card-body p-4 p-md-5 text-center">
                        <p class="text-secondary fs-6 mb-4">
                            Đơn đăng ký gian hàng <strong>"{{ $sellerProfile->shop_name ?? 'Của bạn' }}"</strong> đã được tiếp nhận và đang được Ban Quản Trị Cupo xem xét.
                            Thời gian xử lý thông thường từ <strong>12 đến 24 giờ làm việc</strong>.
                        </p>
                        <div class="p-3 bg-light rounded-3 mb-4 text-start small">
                            <div class="fw-semibold text-dark mb-1">Thông tin đã nộp:</div>
                            <div class="text-muted">• Tên shop: <span class="text-dark fw-medium">{{ $sellerProfile->shop_name ?? '—' }}</span></div>
                            <div class="text-muted">• Địa chỉ: <span class="text-dark fw-medium">{{ $sellerProfile->address ?? '—' }}</span></div>
                            <div class="text-muted">• CCCD/MST: <span class="text-dark fw-medium">{{ $sellerProfile->national_id ?? '—' }}</span></div>
                        </div>
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="{{ route('home') }}" class="btn btn-primary px-4">
                                <i class="fa-solid fa-house me-1"></i>Tiếp tục mua sắm
                            </a>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection
