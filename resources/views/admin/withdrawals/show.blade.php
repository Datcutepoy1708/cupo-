@extends('layouts.admin.app')

@section('page-title', 'Yêu cầu rút tiền #' . $withdrawal->id)

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.withdrawals.index') }}">Yêu cầu rút tiền</a>
    </li>
    <li class="breadcrumb-item active">#{{ $withdrawal->id }}</li>
@endsection

@push('styles')
    <link href="{{ asset('admin/css/withdrawals.css') }}" rel="stylesheet">
@endpush

@section('content')

<div class="row g-4">

    {{-- ===== CỘT TRÁI: Chi tiết rút tiền & Nút duyệt/từ chối ===== --}}
    <div class="col-lg-7">

        {{-- Card Chi tiết rút tiền --}}
        <div class="admin-card mb-4">
            <div class="admin-card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-1">
                        <i class="fa-solid fa-money-bill-transfer me-2 text-danger"></i>
                        Lệnh rút tiền #{{ $withdrawal->id }}
                    </h5>
                    <span class="text-muted small">Tạo lúc {{ $withdrawal->created_at->format('H:i, d/m/Y') }}</span>
                </div>
                <div>
                    <span class="withdrawal-badge {{ $withdrawal->status_class }}">
                        {{ $withdrawal->status_label }}
                    </span>
                </div>
            </div>

            <div class="admin-card-body p-4">

                {{-- Khối số tiền rút nổi bật --}}
                <div class="withdrawal-amount-banner text-center p-4 rounded-3 mb-4">
                    <span class="text-muted small d-block mb-1">Số tiền yêu cầu rút</span>
                    <h2 class="fw-bold text-danger mb-0">{{ number_format($withdrawal->amount) }} <span class="fs-5">VND</span></h2>
                </div>

                {{-- Chi tiết tài khoản ngân hàng nhận tiền --}}
                <h6 class="fw-bold text-dark mb-3">
                    <i class="fa-solid fa-building-columns me-2 text-primary"></i>
                    Tài khoản ngân hàng nhận tiền
                </h6>

                <div class="list-group list-group-flush border rounded-3 mb-4">
                    <div class="list-group-item d-flex justify-content-between py-2 px-3">
                        <span class="text-muted">Ngân hàng:</span>
                        <strong class="text-dark">{{ $withdrawal->bank_name }}</strong>
                    </div>
                    <div class="list-group-item d-flex justify-content-between py-2 px-3">
                        <span class="text-muted">Số tài khoản:</span>
                        <strong class="text-primary fs-6 font-monospace">{{ $withdrawal->bank_account }}</strong>
                    </div>
                    <div class="list-group-item d-flex justify-content-between py-2 px-3">
                        <span class="text-muted">Chủ tài khoản:</span>
                        <strong class="text-dark text-uppercase">{{ $withdrawal->bank_owner }}</strong>
                    </div>
                </div>

                {{-- Ghi chú của Admin (nếu có) --}}
                @if ($withdrawal->admin_note)
                    <div class="p-3 rounded-3 mb-4 {{ $withdrawal->status === 'rejected' ? 'bg-danger-subtle border border-danger-subtle' : 'bg-light border' }}">
                        <h6 class="fw-bold mb-1 {{ $withdrawal->status === 'rejected' ? 'text-danger' : 'text-dark' }}">
                            <i class="fa-solid fa-circle-info me-1"></i>
                            Ghi chú từ Admin:
                        </h6>
                        <p class="mb-0 small text-dark">{{ $withdrawal->admin_note }}</p>
                    </div>
                @endif

                {{-- Thao tác --}}
                @if ($withdrawal->status === 'pending')
                    <div class="withdrawal-show-actions pt-3 border-top d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-success btn-sm px-4" id="btnApproveWithdrawal">
                            <i class="fa-solid fa-check me-1"></i>Duyệt & Chuyển tiền
                        </button>
                        <button type="button" class="btn btn-danger btn-sm px-3" id="btnRejectWithdrawal">
                            <i class="fa-solid fa-ban me-1"></i>Từ chối yêu cầu
                        </button>
                    </div>
                @endif

            </div>
        </div>

    </div>

    {{-- ===== CỘT PHẢI: Thông tin Gian hàng & Lịch sử giao dịch ===== --}}
    <div class="col-lg-5">

        @php
            $seller = $withdrawal->seller;
            $shop = $seller?->sellerProfile;
        @endphp

        {{-- Thông tin Gian hàng --}}
        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h6 class="fw-bold mb-0">
                    <i class="fa-solid fa-store me-2 text-primary"></i>
                    Thông tin Gian hàng & Số dư
                </h6>
            </div>
            <div class="admin-card-body p-3">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <img src="{{ $seller?->avatar_url }}"
                         alt="{{ $seller?->name }}"
                         class="rounded-circle border"
                         style="width: 48px; height: 48px; object-fit: cover;">
                    <div>
                        <div class="fw-bold fs-6">{{ $shop?->shop_name ?? '—' }}</div>
                        <div class="text-muted small">Chủ shop: {{ $seller?->name ?? '—' }}</div>
                    </div>
                </div>

                <div class="text-muted small mb-3">
                    <div class="mb-1"><i class="fa-solid fa-envelope me-2 text-secondary"></i>{{ $seller?->email ?? '—' }}</div>
                    <div><i class="fa-solid fa-phone me-2 text-secondary"></i>{{ $seller?->phone ?? 'Chưa có SĐT' }}</div>
                </div>

                <div class="p-3 bg-light rounded-3 d-flex justify-content-between align-items-center">
                    <span class="text-muted">Số dư ví khả dụng:</span>
                    <strong class="fs-5 text-success">{{ number_format($shop?->balance ?? 0) }}đ</strong>
                </div>
            </div>
        </div>

        {{-- Lịch sử 5 biến động số dư gần nhất --}}
        <div class="admin-card">
            <div class="admin-card-header">
                <h6 class="fw-bold mb-0">
                    <i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>
                    Biến động số dư gần đây
                </h6>
            </div>
            <div class="admin-card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse ($recentLogs as $log)
                        <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                            <div>
                                <div class="fw-semibold small text-dark">
                                    {{ match($log->type) {
                                        'order_earning' => 'Doanh thu đơn hàng',
                                        'withdrawal'    => 'Rút tiền về ngân hàng',
                                        'refund'        => 'Trừ tiền hoàn khiếu nại',
                                        default         => $log->type,
                                    } }}
                                </div>
                                <div class="text-muted" style="font-size: 11px;">{{ $log->created_at->format('d/m/Y H:i') }}</div>
                            </div>
                            <span class="fw-bold small {{ $log->amount >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $log->amount >= 0 ? '+' : '' }}{{ number_format($log->amount) }}đ
                            </span>
                        </div>
                    @empty
                        <div class="p-3 text-muted small text-center">Chưa có lịch sử giao dịch</div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

</div>

{{-- Modals --}}
@include('admin.withdrawals.partials._reject-modal')

{{-- Toast --}}
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100;">
    <div id="withdrawalToast" class="toast align-items-center border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body" id="withdrawalToastMsg"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    <div id="withdrawalShowConfig"
         data-id="{{ $withdrawal->id }}"
         data-shop-name="{{ $shop?->shop_name ?? $seller?->name }}"
         data-approve-url="{{ route('admin.withdrawals.approve', $withdrawal) }}"
         data-reject-url="{{ route('admin.withdrawals.reject', $withdrawal) }}"
         data-back-url="{{ route('admin.withdrawals.index') }}"
         data-csrf="{{ csrf_token() }}"
         style="display:none;"></div>
    <script src="{{ asset('admin/js/withdrawals.js') }}"></script>
@endpush
