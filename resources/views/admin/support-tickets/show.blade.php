@extends('layouts.admin.app')

@section('page-title', 'Yêu cầu hỗ trợ #' . $ticket->id)

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.support-tickets.index') }}">Kháng nghị & Hỗ trợ</a>
    </li>
    <li class="breadcrumb-item active">#{{ $ticket->id }}</li>
@endsection

@push('styles')
    <link href="{{ asset('admin/css/support-tickets.css') }}" rel="stylesheet">
@endpush

@section('content')

<div class="row g-4">

    {{-- ===== CỘT TRÁI: Nội dung Ticket & Lịch sử Phản hồi ===== --}}
    <div class="col-lg-7">

        {{-- Card Nội dung Ticket --}}
        <div class="admin-card mb-4">
            <div class="admin-card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-1">
                        <i class="fa-solid fa-headset me-2 text-primary"></i>
                        {{ $ticket->subject }}
                    </h5>
                    <span class="badge bg-light text-dark border me-2">{{ $ticket->category_label }}</span>
                    <span class="text-muted small">Gửi lúc {{ $ticket->created_at->format('H:i, d/m/Y') }}</span>
                </div>
                <div>
                    <span class="ticket-badge {{ $ticket->status_class }}">
                        {{ $ticket->status_label }}
                    </span>
                </div>
            </div>

            <div class="admin-card-body p-4">
                <h6 class="fw-bold text-dark mb-2">Nội dung yêu cầu từ Seller:</h6>
                <div class="ticket-message-box mb-4">
                    {!! nl2br(e($ticket->message)) !!}
                </div>

                {{-- Tệp đính kèm --}}
                @if ($ticket->attachment)
                    <h6 class="fw-bold text-dark mb-2">
                        <i class="fa-solid fa-paperclip me-1 text-primary"></i>
                        Tài liệu / Ảnh đính kèm:
                    </h6>
                    <div class="mb-4">
                        <a href="{{ asset('storage/' . $ticket->attachment) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Xem tệp đính kèm
                        </a>
                    </div>
                @endif

                {{-- Phản hồi của Admin (nếu đã có) --}}
                @if ($ticket->admin_response)
                    <div class="ticket-response-box mb-4 p-3 rounded-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold mb-0 text-success">
                                <i class="fa-solid fa-user-shield me-1"></i>
                                Phản hồi từ Ban Quản Trị:
                            </h6>
                            <span class="text-muted small">
                                Bởi {{ $ticket->resolvedBy?->name ?? 'Admin' }} ({{ $ticket->resolved_at?->format('H:i, d/m/Y') }})
                            </span>
                        </div>
                        <p class="mb-0 text-dark small" style="white-space: pre-line;">{{ $ticket->admin_response }}</p>
                    </div>
                @endif

                {{-- Nút hành động nhanh --}}
                <div class="ticket-show-actions pt-3 border-top d-flex gap-2 flex-wrap">
                    @if ($ticket->status === 'open')
                        <button type="button" class="btn btn-info text-white btn-sm px-3" id="btnInReviewTicket">
                            <i class="fa-solid fa-arrows-rotate me-1"></i>Tiếp nhận xử lý
                        </button>
                    @endif

                    @if ($ticket->status !== 'closed')
                        <button type="button" class="btn btn-primary btn-sm px-3" id="btnRespondTicket">
                            <i class="fa-solid fa-reply me-1"></i>
                            {{ $ticket->admin_response ? 'Cập nhật câu trả lời' : 'Gửi phản hồi cho Seller' }}
                        </button>
                    @endif
                </div>

            </div>
        </div>

    </div>

    {{-- ===== CỘT PHẢI: Thông tin Gian hàng & Chủ Shop ===== --}}
    <div class="col-lg-5">

        @php
            $seller = $ticket->seller;
            $shop = $seller?->sellerProfile;
        @endphp

        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h6 class="fw-bold mb-0">
                    <i class="fa-solid fa-store me-2 text-primary"></i>
                    Thông tin Gian hàng
                </h6>
            </div>
            <div class="admin-card-body p-3">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <img src="{{ $seller?->avatar_url }}"
                         alt="{{ $seller?->name }}"
                         class="rounded-circle border"
                         style="width: 48px; height: 48px; object-fit: cover;">
                    <div>
                        <div class="fw-bold fs-6">{{ $shop?->shop_name ?? 'Chưa có tên shop' }}</div>
                        <div class="text-muted small">Chủ shop: {{ $seller?->name ?? '—' }}</div>
                    </div>
                </div>

                <div class="text-muted small">
                    <div class="mb-2">
                        <i class="fa-solid fa-envelope me-2 text-secondary"></i>
                        <span>{{ $seller?->email ?? '—' }}</span>
                    </div>
                    <div class="mb-2">
                        <i class="fa-solid fa-phone me-2 text-secondary"></i>
                        <span>{{ $seller?->phone ?? 'Chưa có SĐT' }}</span>
                    </div>
                    <div class="mb-2">
                        <i class="fa-solid fa-location-dot me-2 text-secondary"></i>
                        <span>{{ $shop?->address ?? 'Chưa cập nhật địa chỉ' }}</span>
                    </div>
                    <div class="mb-2">
                        <i class="fa-solid fa-wallet me-2 text-secondary"></i>
                        <span>Số dư ví: <strong>{{ number_format($shop?->balance ?? 0) }}đ</strong></span>
                    </div>
                    <div class="mt-2 pt-2 border-top">
                        <span class="badge {{ $shop?->status === 'approved' ? 'bg-success' : 'bg-danger' }}">
                            Trạng thái gian hàng: {{ $shop?->status === 'approved' ? 'Đang hoạt động' : 'Bị khóa/Chờ' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

{{-- Modals --}}
@include('admin.support-tickets.partials._response-modal')

{{-- Toast --}}
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100;">
    <div id="ticketToast" class="toast align-items-center border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body" id="ticketToastMsg"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    <div id="ticketShowConfig"
         data-id="{{ $ticket->id }}"
         data-subject="{{ $ticket->subject }}"
         data-in-review-url="{{ route('admin.support-tickets.in-review', $ticket) }}"
         data-respond-url="{{ route('admin.support-tickets.respond', $ticket) }}"
         data-back-url="{{ route('admin.support-tickets.index') }}"
         data-csrf="{{ csrf_token() }}"
         style="display:none;"></div>
    <script src="{{ asset('admin/js/support-tickets.js') }}"></script>
@endpush
