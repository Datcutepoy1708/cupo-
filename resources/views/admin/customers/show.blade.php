@extends('layouts.admin.app')

@section('page-title', 'Hồ sơ: ' . $user->name)

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.customers.index') }}">Khách hàng</a>
    </li>
    <li class="breadcrumb-item active">{{ $user->name }}</li>
@endsection

@push('styles')
    <link href="{{ asset('admin/css/customers.css') }}" rel="stylesheet">
@endpush

@section('content')

<div class="row g-4">

    {{-- ===== CỘT TRÁI: Thông tin khách hàng ===== --}}
    <div class="col-lg-4">

        {{-- Card hồ sơ --}}
        <div class="admin-card customer-profile-card mb-4">
            <div class="customer-profile-header">
                <img src="{{ $user->avatar_url }}"
                     alt="{{ $user->name }}"
                     class="customer-profile-avatar">
                <h4 class="customer-profile-name">{{ $user->name }}</h4>
                <span class="customer-status-badge {{ $user->status === 'active' ? 'active' : 'blocked' }}">
                    <i class="fa-solid {{ $user->status === 'active' ? 'fa-circle-check' : 'fa-ban' }} me-1"></i>
                    {{ $user->status === 'active' ? 'Hoạt động' : 'Đã khóa' }}
                </span>
            </div>

            <div class="customer-profile-body">
                <div class="customer-info-row">
                    <i class="fa-solid fa-envelope"></i>
                    <span>{{ $user->email }}</span>
                </div>
                <div class="customer-info-row">
                    <i class="fa-solid fa-phone"></i>
                    <span>{{ $user->phone ?? 'Chưa cập nhật' }}</span>
                </div>
                <div class="customer-info-row">
                    <i class="fa-solid fa-cake-candles"></i>
                    <span>{{ $user->date_of_birth?->format('d/m/Y') ?? 'Chưa cập nhật' }}</span>
                </div>
                <div class="customer-info-row">
                    <i class="fa-solid fa-calendar-plus"></i>
                    <span>Tham gia {{ $user->created_at->format('d/m/Y') }}</span>
                </div>
            </div>

            {{-- Action buttons --}}
            <div class="customer-profile-actions">
                @if ($user->status === 'active')
                    <button type="button"
                            class="btn-customer-block"
                            id="btnBlockCustomer"
                            data-user-id="{{ $user->id }}"
                            data-user-name="{{ $user->name }}"
                            data-block-url="{{ route('admin.customers.block', $user) }}"
                            data-csrf="{{ csrf_token() }}">
                        <i class="fa-solid fa-ban me-1"></i>Khóa tài khoản
                    </button>
                @else
                    <button type="button"
                            class="btn-customer-unblock"
                            id="btnUnblockCustomer"
                            data-user-id="{{ $user->id }}"
                            data-user-name="{{ $user->name }}"
                            data-unblock-url="{{ route('admin.customers.unblock', $user) }}"
                            data-csrf="{{ csrf_token() }}">
                        <i class="fa-solid fa-circle-check me-1"></i>Mở khóa tài khoản
                    </button>
                @endif
            </div>
        </div>

        {{-- Card địa chỉ --}}
        <div class="admin-card">
            <div class="admin-card-header">
                <h6 class="fw-bold mb-0">
                    <i class="fa-solid fa-location-dot me-2 text-primary"></i>
                    Địa chỉ giao hàng
                </h6>
            </div>
            <div class="admin-card-body p-3">
                @forelse ($user->addresses as $address)
                    <div class="customer-address-item {{ $loop->last ? '' : 'mb-3' }}">
                        @if ($address->is_default)
                            <span class="badge bg-success-subtle text-success mb-1">Mặc định</span>
                        @endif
                        <div class="fw-semibold small">{{ $address->full_name ?? $user->name }}</div>
                        <div class="text-muted small">{{ $address->phone ?? '' }}</div>
                        <div class="text-muted small">
                            {{ $address->address_line }}, {{ $address->ward }},
                            {{ $address->district }}, {{ $address->province }}
                        </div>
                    </div>
                    @if (! $loop->last)
                        <hr class="my-2">
                    @endif
                @empty
                    <p class="text-muted small mb-0">Chưa có địa chỉ giao hàng nào.</p>
                @endforelse
            </div>
        </div>

    </div>

    {{-- ===== CỘT PHẢI: Lịch sử đơn hàng ===== --}}
    <div class="col-lg-8">
        <div class="admin-card">
            <div class="admin-card-header">
                <h6 class="fw-bold mb-0">
                    <i class="fa-solid fa-bag-shopping me-2 text-primary"></i>
                    Lịch sử đơn hàng (10 đơn gần nhất)
                </h6>
                <span class="text-muted small ms-auto">
                    Tổng: {{ $user->orders->count() }} đơn
                </span>
            </div>

            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Ngày đặt</th>
                            <th style="text-align: right;">Tổng tiền</th>
                            <th>Thanh toán</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($user->orders as $order)
                            @php
                                $statusLabel = match($order->status) {
                                    'pending'    => ['Chờ xác nhận', 'bg-warning text-dark'],
                                    'processing' => ['Đang xử lý', 'bg-info text-white'],
                                    'shipped'    => ['Đang giao', 'bg-primary'],
                                    'delivered'  => ['Đã giao', 'bg-success'],
                                    'cancelled'  => ['Đã hủy', 'bg-danger'],
                                    default      => [$order->status, 'bg-secondary'],
                                };
                                $paymentLabel = match($order->payment_status) {
                                    'paid'     => ['Đã TT', 'bg-success'],
                                    'failed'   => ['Lỗi', 'bg-danger'],
                                    'refunded' => ['Hoàn tiền', 'bg-info text-white'],
                                    default    => ['Chờ TT', 'bg-warning text-dark'],
                                };
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order) }}"
                                       class="fw-semibold text-decoration-none text-primary">
                                        #{{ $order->order_number }}
                                    </a>
                                </td>
                                <td class="text-muted small">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                <td style="text-align: right;" class="fw-semibold">
                                    {{ number_format($order->total_amount) }}đ
                                </td>
                                <td>
                                    <span class="badge {{ $paymentLabel[1] }}">{{ $paymentLabel[0] }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ $statusLabel[1] }}">{{ $statusLabel[0] }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="fa-solid fa-bag-shopping fa-2x mb-2 d-block opacity-25"></i>
                                    Khách hàng chưa có đơn hàng nào.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- Modal xác nhận Khóa --}}
<div class="modal fade" id="blockCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger">
                    <i class="fa-solid fa-ban me-2"></i>Khóa tài khoản
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">
                    Bạn đang khóa tài khoản: <strong>{{ $user->name }}</strong><br>
                    Khách hàng sẽ không thể đăng nhập cho đến khi được mở khóa.
                </p>
                <div class="mb-3">
                    <label for="blockAdminNote" class="form-label fw-semibold">
                        Lý do khóa <span class="text-danger">*</span>
                    </label>
                    <textarea id="blockAdminNote"
                              class="form-control"
                              rows="3"
                              placeholder="Nhập lý do khóa tài khoản..."></textarea>
                    <div class="invalid-feedback d-block d-none" id="blockNoteError"></div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger btn-sm" id="confirmBlockBtn">
                    <i class="fa-solid fa-ban me-1"></i>Xác nhận khóa
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Toast --}}
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100;">
    <div id="customerToast" class="toast align-items-center border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body" id="customerToastMsg"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    <div id="customerShowConfig"
         data-block-url="{{ route('admin.customers.block', $user) }}"
         data-unblock-url="{{ route('admin.customers.unblock', $user) }}"
         data-back-url="{{ route('admin.customers.index') }}"
         data-csrf="{{ csrf_token() }}"
         data-status="{{ $user->status }}"
         style="display:none;"></div>
    <script src="{{ asset('admin/js/customers.js') }}"></script>
@endpush
