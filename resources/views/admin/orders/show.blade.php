@extends('layouts.admin.app')

@section('page-title', 'Chi tiết đơn hàng #' . $order->order_number)

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.orders.index') }}">Đơn hàng</a>
    </li>
    <li class="breadcrumb-item active">{{ $order->order_number }}</li>
@endsection

@push('styles')
    <link href="{{ asset('admin/css/orders.css') }}" rel="stylesheet">
@endpush

@section('content')

<div class="row g-4">

    {{-- ===== COT TRAI: Thong tin don hang ===== --}}
    <div class="col-lg-8">

        {{-- Header don hang --}}
        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <div>
                    <h5 class="fw-bold mb-1">
                        <i class="fa-solid fa-bag-shopping me-2 text-primary"></i>
                        Đơn hàng #{{ $order->order_number }}
                    </h5>
                    <div class="text-muted small">Đặt lúc {{ $order->created_at->format('H:i, d/m/Y') }}</div>
                </div>
                <div class="d-flex gap-2 ms-auto">
                    @php
                        $paymentBadgeClass = match($order->payment_status) {
                            'paid'     => 'bg-success',
                            'failed'   => 'bg-danger',
                            'refunded' => 'bg-info',
                            default    => 'bg-warning text-dark',
                        };
                        $paymentLabel = match($order->payment_status) {
                            'paid'     => 'Đã thanh toán',
                            'failed'   => 'Thanh toán lỗi',
                            'refunded' => 'Đã hoàn tiền',
                            default    => 'Chờ thanh toán',
                        };
                        $paymentMethodLabel = match($order->payment_method) {
                            'vnpay' => 'VNPay',
                            'momo'  => 'MoMo',
                            default => 'COD (Tiền mặt)',
                        };
                    @endphp
                    <span class="badge {{ $paymentBadgeClass }} fs-7 px-3 py-2">{{ $paymentLabel }}</span>
                    <span class="badge bg-secondary fs-7 px-3 py-2">{{ $paymentMethodLabel }}</span>
                </div>
            </div>
        </div>

        {{-- Danh sach Seller Orders (accordion) --}}
        <div class="accordion order-accordion" id="sellerOrdersAccordion">
            @foreach($order->sellerOrders as $sellerOrder)
                @php
                    $statusBadge = match($sellerOrder->status) {
                        'confirmed'  => ['label' => 'Đã xác nhận', 'class' => 'bg-info text-dark'],
                        'shipping'   => ['label' => 'Đang giao',   'class' => 'bg-primary'],
                        'completed'  => ['label' => 'Hoàn thành',  'class' => 'bg-success'],
                        'cancelled'  => ['label' => 'Đã hủy',     'class' => 'bg-danger'],
                        default      => ['label' => 'Chờ xác nhận','class' => 'bg-warning text-dark'],
                    };
                @endphp
                <div class="accordion-item border-0 admin-card mb-3" id="sellerOrderCard{{ $sellerOrder->id }}">
                    <h2 class="accordion-header">
                        <button class="accordion-button order-accordion-btn collapsed" type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#sellerOrderBody{{ $sellerOrder->id }}"
                                aria-expanded="false">
                            <div class="d-flex align-items-center gap-3 w-100 me-3">
                                <i class="fa-solid fa-store text-muted"></i>
                                <span class="fw-semibold">{{ $sellerOrder->seller?->sellerProfile?->shop_name ?? $sellerOrder->seller?->name ?? 'Seller #' . $sellerOrder->seller_id }}</span>
                                <span class="badge {{ $statusBadge['class'] }} ms-2">{{ $statusBadge['label'] }}</span>
                                <span class="ms-auto fw-bold text-dark">{{ number_format($sellerOrder->grand_total, 0, ',', '.') }}đ</span>
                            </div>
                        </button>
                    </h2>
                    <div id="sellerOrderBody{{ $sellerOrder->id }}" class="accordion-collapse collapse">
                        <div class="accordion-body p-0">

                            {{-- Danh sach items --}}
                            <div class="table-responsive">
                                <table class="table align-middle mb-0 order-items-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-4" style="min-width: 240px;">Sản phẩm</th>
                                            <th style="min-width: 110px;">Đơn giá</th>
                                            <th style="min-width: 80px;">SL</th>
                                            <th style="min-width: 110px;">Thành tiền</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($sellerOrder->items as $item)
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="d-flex align-items-center gap-3">
                                                        @if($item->product_image)
                                                            <img src="{{ asset('storage/' . $item->product_image) }}"
                                                                 alt="{{ $item->product_name }}"
                                                                 class="order-item-img">
                                                        @else
                                                            <div class="order-item-img-placeholder">
                                                                <i class="fa-solid fa-image text-muted"></i>
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <div class="fw-semibold text-dark">{{ $item->product_name }}</div>
                                                            @if($item->variant)
                                                                <div class="text-muted small">{{ $item->variant->name ?? '' }}</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>{{ number_format($item->price, 0, ',', '.') }}đ</td>
                                                <td>{{ $item->quantity }}</td>
                                                <td class="fw-bold">{{ number_format($item->total, 0, ',', '.') }}đ</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td colspan="3" class="text-end fw-semibold ps-4">Tiền hàng:</td>
                                            <td>{{ number_format($sellerOrder->sub_total, 0, ',', '.') }}đ</td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-end fw-semibold ps-4">Phí giao hàng:</td>
                                            <td>{{ number_format($sellerOrder->shipping_fee, 0, ',', '.') }}đ</td>
                                        </tr>
                                        @if($sellerOrder->discount_amount > 0)
                                            <tr>
                                                <td colspan="3" class="text-end fw-semibold text-success ps-4">Giảm giá:</td>
                                                <td class="text-success">-{{ number_format($sellerOrder->discount_amount, 0, ',', '.') }}đ</td>
                                            </tr>
                                        @endif
                                        <tr>
                                            <td colspan="3" class="text-end fw-bold ps-4">Tổng đơn:</td>
                                            <td class="fw-bold text-primary">{{ number_format($sellerOrder->grand_total, 0, ',', '.') }}đ</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            {{-- Tracking number --}}
                            @if($sellerOrder->tracking_number)
                                <div class="px-4 py-3 border-top">
                                    <span class="text-muted small me-2"><i class="fa-solid fa-truck me-1"></i>Mã vận đơn:</span>
                                    <strong class="text-dark">{{ $sellerOrder->tracking_number }}</strong>
                                </div>
                            @endif

                            {{-- Form cap nhat trang thai --}}
                            <div class="px-4 py-3 border-top bg-light rounded-bottom">
                                <div class="d-flex flex-wrap align-items-end gap-3">
                                    <div>
                                        <label class="form-label fw-semibold small mb-1">Cập nhật trạng thái</label>
                                        <select class="form-select form-select-sm seller-order-status-select"
                                                style="width: 200px;"
                                                data-seller-order-id="{{ $sellerOrder->id }}"
                                                data-update-url="{{ route('admin.orders.seller-orders.update-status', $sellerOrder) }}">
                                            <option value="pending"    {{ $sellerOrder->status === 'pending'    ? 'selected' : '' }}>Chờ xác nhận</option>
                                            <option value="confirmed"  {{ $sellerOrder->status === 'confirmed'  ? 'selected' : '' }}>Đã xác nhận</option>
                                            <option value="shipping"   {{ $sellerOrder->status === 'shipping'   ? 'selected' : '' }}>Đang giao hàng</option>
                                            <option value="completed"  {{ $sellerOrder->status === 'completed'  ? 'selected' : '' }}>Hoàn thành</option>
                                            <option value="cancelled"  {{ $sellerOrder->status === 'cancelled'  ? 'selected' : '' }}>Hủy đơn</option>
                                        </select>
                                    </div>

                                    {{-- Tracking number input (hien khi chon shipping) --}}
                                    <div class="tracking-input-wrap" style="display:none;">
                                        <label class="form-label fw-semibold small mb-1">Mã vận đơn <span class="text-danger">*</span></label>
                                        <input type="text"
                                               class="form-control form-control-sm tracking-number-input"
                                               placeholder="VD: VN123456789"
                                               value="{{ $sellerOrder->tracking_number ?? '' }}"
                                               maxlength="100">
                                    </div>

                                    {{-- Cancel reason (hien khi chon cancelled) --}}
                                    <div class="cancel-reason-wrap" style="display:none;">
                                        <label class="form-label fw-semibold small mb-1">Lý do hủy <span class="text-danger">*</span></label>
                                        <input type="text"
                                               class="form-control form-control-sm cancel-reason-input"
                                               placeholder="Nhập lý do hủy đơn..."
                                               maxlength="500">
                                    </div>

                                    <button type="button"
                                            class="btn btn-sm btn-primary btn-update-seller-order-status"
                                            data-seller-order-id="{{ $sellerOrder->id }}">
                                        <i class="fa-solid fa-floppy-disk me-1"></i> Lưu
                                    </button>
                                </div>
                                <div class="invalid-feedback d-block mt-1 status-error-msg" id="statusError{{ $sellerOrder->id }}"></div>
                            </div>

                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    </div>

    {{-- ===== COT PHAI: Thong tin khach hang + Tong tien ===== --}}
    <div class="col-lg-4">

        {{-- Thong tin khach hang & giao hang --}}
        <div class="admin-card mb-4">
            <div class="admin-card-header border-bottom pb-3 mb-3">
                <h6 class="fw-bold mb-0"><i class="fa-solid fa-user me-2 text-muted"></i>Thông tin giao hàng</h6>
            </div>
            <div class="px-4 pb-4">
                <div class="info-row">
                    <span class="info-label">Người nhận</span>
                    <span class="info-value fw-semibold">{{ $order->shipping_name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Số điện thoại</span>
                    <span class="info-value">{{ $order->shipping_phone }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Địa chỉ</span>
                    <span class="info-value">{{ $order->shipping_address }}</span>
                </div>
                @if($order->notes)
                    <div class="info-row">
                        <span class="info-label">Ghi chú</span>
                        <span class="info-value text-muted">{{ $order->notes }}</span>
                    </div>
                @endif
                @if($order->user)
                    <div class="info-row">
                        <span class="info-label">Tài khoản</span>
                        <span class="info-value">{{ $order->user->name }}<br><small class="text-muted">{{ $order->user->email }}</small></span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Tong tien don hang --}}
        <div class="admin-card mb-4">
            <div class="admin-card-header border-bottom pb-3 mb-3">
                <h6 class="fw-bold mb-0"><i class="fa-solid fa-receipt me-2 text-muted"></i>Tổng tiền</h6>
            </div>
            <div class="px-4 pb-4">
                <div class="info-row">
                    <span class="info-label">Tiền hàng</span>
                    <span class="info-value">{{ number_format($order->total_item_amount, 0, ',', '.') }}đ</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Phí giao hàng</span>
                    <span class="info-value">{{ number_format($order->total_shipping_fee, 0, ',', '.') }}đ</span>
                </div>
                @if($order->total_discount > 0)
                    <div class="info-row">
                        <span class="info-label text-success">Giảm giá</span>
                        <span class="info-value text-success">-{{ number_format($order->total_discount, 0, ',', '.') }}đ</span>
                    </div>
                @endif
                <hr>
                <div class="info-row">
                    <span class="info-label fw-bold fs-6">Tổng thanh toán</span>
                    <span class="info-value fw-bold fs-5 text-primary">{{ number_format($order->grand_total, 0, ',', '.') }}đ</span>
                </div>
            </div>
        </div>

        {{-- Lich su giao dich thanh toan --}}
        @if($order->paymentTransactions->count() > 0)
            <div class="admin-card">
                <div class="admin-card-header border-bottom pb-3 mb-3">
                    <h6 class="fw-bold mb-0"><i class="fa-solid fa-clock-rotate-left me-2 text-muted"></i>Lịch sử giao dịch</h6>
                </div>
                <div class="px-4 pb-4">
                    @foreach($order->paymentTransactions as $txn)
                        <div class="info-row">
                            <div>
                                <div class="small fw-semibold">{{ $txn->gateway ?? 'Giao dịch' }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">{{ $txn->created_at?->format('H:i d/m/Y') }}</div>
                            </div>
                            <span class="badge {{ $txn->status === 'success' ? 'bg-success' : 'bg-danger' }}">
                                {{ $txn->status === 'success' ? 'Thành công' : 'Thất bại' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>

</div>

{{-- Toast thong bao --}}
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100;">
    <div id="ordersToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2" id="ordersToastMessage">
                <i class="fa-solid fa-circle-check"></i> Cập nhật thành công.
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

{{-- Config cho JS --}}
<div id="ordersAppConfig"
     data-index-url="{{ route('admin.orders.index') }}"
     data-update-status-url="{{ route('admin.orders.seller-orders.update-status', ['sellerOrder' => '__ID__']) }}"
     data-csrf="{{ csrf_token() }}">
</div>

@endsection

@push('scripts')
    <script src="{{ asset('admin/js/orders.js') }}"></script>
@endpush
