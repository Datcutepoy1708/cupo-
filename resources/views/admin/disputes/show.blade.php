@extends('layouts.admin.app')

@section('page-title', 'Khiếu nại #' . $dispute->id)

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.disputes.index') }}">Tranh chấp & Khiếu nại</a>
    </li>
    <li class="breadcrumb-item active">#{{ $dispute->id }}</li>
@endsection

@push('styles')
    <link href="{{ asset('admin/css/disputes.css') }}" rel="stylesheet">
@endpush

@section('content')

<div class="row g-4">

    {{-- ===== CỘT TRÁI: Nội dung khiếu nại & Hành động ===== --}}
    <div class="col-lg-7">

        {{-- Card Nội dung khiếu nại --}}
        <div class="admin-card mb-4">
            <div class="admin-card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-1">
                        <i class="fa-solid fa-scale-balanced me-2 text-danger"></i>
                        Khiếu nại #{{ $dispute->id }}
                    </h5>
                    <span class="text-muted small">Tạo lúc {{ $dispute->created_at->format('H:i, d/m/Y') }}</span>
                </div>
                <div>
                    <span class="dispute-badge {{ $dispute->status_class }}">
                        {{ $dispute->status_label }}
                    </span>
                </div>
            </div>

            <div class="admin-card-body p-4">
                <h6 class="fw-bold text-dark mb-2">Lý do khiếu nại từ khách hàng:</h6>
                <div class="dispute-reason-box mb-4">
                    {{ $dispute->reason }}
                </div>

                {{-- Bằng chứng hình ảnh --}}
                <h6 class="fw-bold text-dark mb-2">
                    <i class="fa-solid fa-images me-1 text-primary"></i>
                    Bằng chứng / Hình ảnh đính kèm:
                </h6>
                @if ($dispute->evidence_images && count($dispute->evidence_images) > 0)
                    <div class="dispute-evidence-grid mb-4">
                        @foreach ($dispute->evidence_images as $img)
                            <a href="{{ asset('storage/' . $img) }}" target="_blank" class="evidence-thumb-wrap">
                                <img src="{{ asset('storage/' . $img) }}" alt="Bằng chứng" class="evidence-thumb">
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted small mb-4">Không có hình ảnh đính kèm.</p>
                @endif

                {{-- Quyết định Admin --}}
                @if ($dispute->admin_decision)
                    <div class="dispute-decision-result mb-3 p-3 rounded {{ $dispute->status === 'refunded' ? 'decision-refunded' : 'decision-rejected' }}">
                        <h6 class="fw-bold mb-1">
                            <i class="fa-solid {{ $dispute->status === 'refunded' ? 'fa-circle-check text-success' : 'fa-circle-xmark text-danger' }} me-1"></i>
                            Phán quyết của Quản trị viên:
                        </h6>
                        <p class="mb-0 small text-dark">{{ $dispute->admin_decision }}</p>
                        <div class="text-muted text-end" style="font-size: 11px;">
                            Cập nhật lúc: {{ $dispute->updated_at->format('H:i, d/m/Y') }}
                        </div>
                    </div>
                @endif

                {{-- Khối hành động Admin --}}
                @if (in_array($dispute->status, ['pending', 'in_progress']))
                    <div class="dispute-show-actions pt-3 border-top d-flex gap-2 flex-wrap">
                        @if ($dispute->status === 'pending')
                            <button type="button" class="btn btn-primary btn-sm px-3" id="btnProcessDispute">
                                <i class="fa-solid fa-spinner me-1"></i>Tiếp nhận xử lý
                            </button>
                        @endif

                        <button type="button" class="btn btn-success btn-sm px-3" id="btnRefundDispute">
                            <i class="fa-solid fa-money-bill-transfer me-1"></i>Chấp thuận hoàn tiền
                        </button>

                        <button type="button" class="btn btn-danger btn-sm px-3" id="btnRejectDispute">
                            <i class="fa-solid fa-ban me-1"></i>Từ chối khiếu nại
                        </button>
                    </div>
                @endif

            </div>
        </div>

    </div>

    {{-- ===== CỘT PHẢI: Thông tin Khách hàng & Đơn hàng liên quan ===== --}}
    <div class="col-lg-5">

        {{-- Thông tin Khách hàng (Buyer) --}}
        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h6 class="fw-bold mb-0">
                    <i class="fa-solid fa-user me-2 text-primary"></i>
                    Thông tin người mua (Khách hàng)
                </h6>
            </div>
            <div class="admin-card-body p-3">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <img src="{{ $dispute->buyer?->avatar_url }}"
                         alt="{{ $dispute->buyer?->name }}"
                         class="rounded-circle border"
                         style="width: 48px; height: 48px; object-fit: cover;">
                    <div>
                        <div class="fw-bold">{{ $dispute->buyer?->name ?? '—' }}</div>
                        <div class="text-muted small">{{ $dispute->buyer?->email ?? '—' }}</div>
                    </div>
                </div>
                <div class="text-muted small">
                    <div><i class="fa-solid fa-phone me-2 text-secondary"></i>{{ $dispute->buyer?->phone ?? 'Chưa có SĐT' }}</div>
                    <div class="mt-1"><i class="fa-solid fa-calendar me-2 text-secondary"></i>Tham gia: {{ $dispute->buyer?->created_at?->format('d/m/Y') }}</div>
                </div>
            </div>
        </div>

        {{-- Thông tin Đơn hàng & Gian hàng --}}
        <div class="admin-card">
            <div class="admin-card-header">
                <h6 class="fw-bold mb-0">
                    <i class="fa-solid fa-store me-2 text-primary"></i>
                    Đơn hàng & Gian hàng liên quan
                </h6>
            </div>
            <div class="admin-card-body p-3">
                @php
                    $sellerOrder = $dispute->sellerOrder;
                    $order = $sellerOrder?->order;
                    $shop = $sellerOrder?->seller?->sellerProfile;
                @endphp

                <div class="mb-3">
                    <div class="text-muted small">Mã đơn hàng:</div>
                    @if ($order)
                        <a href="{{ route('admin.orders.show', $order) }}" class="fw-bold text-decoration-none text-primary fs-6">
                            #{{ $order->order_number }}
                        </a>
                    @else
                        <span class="fw-bold">#{{ $sellerOrder?->id }}</span>
                    @endif
                </div>

                <div class="mb-3">
                    <div class="text-muted small">Gian hàng:</div>
                    <div class="fw-bold text-dark">{{ $shop?->shop_name ?? '—' }}</div>
                    <div class="text-muted small">{{ $sellerOrder?->seller?->email ?? '' }}</div>
                </div>

                <div class="mb-3">
                    <div class="text-muted small">Giá trị đơn gian hàng:</div>
                    <div class="fw-bold text-danger fs-6">{{ number_format($sellerOrder?->grand_total ?? 0) }}đ</div>
                </div>

                <div class="mb-3">
                    <div class="text-muted small mb-1">Sản phẩm trong đơn:</div>
                    <div class="list-group list-group-flush border rounded-2">
                        @forelse ($sellerOrder?->items ?? [] as $item)
                            <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                                <div>
                                    <div class="fw-semibold small text-truncate" style="max-width: 200px;">
                                        {{ $item->product_name ?? $item->product?->name ?? 'Sản phẩm' }}
                                    </div>
                                    <div class="text-muted" style="font-size: 11px;">Số lượng: x{{ $item->quantity }}</div>
                                </div>
                                <span class="fw-bold small text-dark">{{ number_format($item->price * $item->quantity) }}đ</span>
                            </div>
                        @empty
                            <div class="p-2 text-muted small text-center">Không có sản phẩm</div>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>

    </div>

</div>

{{-- Modals --}}
@include('admin.disputes.partials._decision-modal')

{{-- Toast --}}
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100;">
    <div id="disputeToast" class="toast align-items-center border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body" id="disputeToastMsg"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    <div id="disputeShowConfig"
         data-id="{{ $dispute->id }}"
         data-process-url="{{ route('admin.disputes.process', $dispute) }}"
         data-refund-url="{{ route('admin.disputes.refund', $dispute) }}"
         data-reject-url="{{ route('admin.disputes.reject', $dispute) }}"
         data-back-url="{{ route('admin.disputes.index') }}"
         data-csrf="{{ csrf_token() }}"
         style="display:none;"></div>
    <script src="{{ asset('admin/js/disputes.js') }}"></script>
@endpush
