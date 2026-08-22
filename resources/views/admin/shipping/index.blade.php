@extends('layouts.admin.app')

@section('page-title', 'Quản lý Vận chuyển & Đối tác Giao hàng')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active" aria-current="page">Vận chuyển</li>
@endsection

@section('content')
<div class="container-fluid px-0">

    <!-- Header Actions -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1">Đối tác Vận chuyển & Cước phí</h4>
            <p class="text-muted small mb-0">Quản lý danh sách các hãng chuyển phát, cấu hình phí ship cơ bản và chính sách vận hành.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.shipping.orders') }}" class="btn btn-primary">
                <i class="fa-solid fa-boxes-packing me-1"></i>Xem danh sách Kiện hàng
            </a>
            <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-danger">
                <i class="fa-solid fa-ticket me-1"></i>Quản lý Voucher Freeship
            </a>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 p-3">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-primary bg-opacity-10 text-primary rounded-3 p-3 me-3">
                        <i class="fa-solid fa-truck-fast fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Tổng đối tác</div>
                        <h4 class="fw-bold mb-0">{{ $stats['total_carriers'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 p-3">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-success bg-opacity-10 text-success rounded-3 p-3 me-3">
                        <i class="fa-solid fa-circle-check fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Đang kích hoạt</div>
                        <h4 class="fw-bold mb-0 text-success">{{ $stats['active_carriers'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 p-3">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-warning bg-opacity-10 text-warning rounded-3 p-3 me-3">
                        <i class="fa-solid fa-box-open fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Đang luân chuyển</div>
                        <h4 class="fw-bold mb-0 text-warning">{{ $stats['in_transit_count'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 p-3">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-indigo bg-opacity-10 text-indigo rounded-3 p-3 me-3">
                        <i class="fa-solid fa-barcode fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Tổng vận đơn sàn</div>
                        <h4 class="fw-bold mb-0">{{ $stats['total_shipments'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Carriers Cards Grid -->
    <div class="row g-4" id="carriersGridWrap">
        @foreach ($carriers as $carrier)
            <div class="col-xl-4 col-md-6">
                <div class="carrier-card {{ $carrier->is_default ? 'is-default' : '' }}">
                    @if ($carrier->is_default)
                        <span class="carrier-badge-default">
                            <i class="fa-solid fa-star"></i> Mặc định sàn
                        </span>
                    @endif

                    <div class="d-flex align-items-center mb-3">
                        <div class="carrier-icon-wrap me-3">
                            <i class="fa-solid fa-truck"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-0">{{ $carrier->name }}</h5>
                            <span class="badge bg-light text-muted border small">{{ strtoupper($carrier->code) }}</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="carrier-fee">{{ number_format($carrier->base_fee) }}₫</div>
                        <div class="carrier-days"><i class="fa-solid fa-clock me-1"></i>Thời gian giao: {{ $carrier->estimated_days }}</div>
                    </div>

                    <p class="text-muted small mb-4 flex-grow-1">
                        {{ $carrier->description ?: 'Chưa có thông tin mô tả chi tiết cho đối tác này.' }}
                    </p>

                    @if ($carrier->hotline)
                        <div class="small text-muted mb-3">
                            <i class="fa-solid fa-phone me-1 text-primary"></i>Hotline: <strong>{{ $carrier->hotline }}</strong>
                        </div>
                    @endif

                    @if (in_array(auth()->user()->role ?? '', ['super-admin', 'admin']))
                        <div class="d-flex gap-2 pt-3 border-top mt-auto">
                            <button type="button" class="btn btn-sm btn-outline-primary btn-edit-carrier flex-grow-1"
                                data-id="{{ $carrier->id }}"
                                data-name="{{ $carrier->name }}"
                                data-fee="{{ (int)$carrier->base_fee }}"
                                data-days="{{ $carrier->estimated_days }}"
                                data-hotline="{{ $carrier->hotline }}"
                                data-desc="{{ $carrier->description }}">
                                <i class="fa-solid fa-pen-to-square me-1"></i>Cấu hình
                            </button>

                            @if (! $carrier->is_default)
                                <button type="button" class="btn btn-sm btn-outline-secondary btn-set-default-carrier"
                                    data-id="{{ $carrier->id }}" title="Đặt làm mặc định">
                                    <i class="fa-regular fa-star"></i>
                                </button>
                            @endif

                            <button type="button" class="btn btn-sm {{ $carrier->is_active ? 'btn-outline-danger' : 'btn-outline-success' }} btn-toggle-carrier"
                                data-id="{{ $carrier->id }}" title="{{ $carrier->is_active ? 'Tạm ngưng' : 'Kích hoạt' }}">
                                <i class="fa-solid {{ $carrier->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

</div>

<!-- Include Carrier Modal -->
@include('admin.shipping.partials._carrier-modal')

@endsection

@push('scripts')
<script src="{{ asset('admin/js/shipping.js') }}"></script>
@endpush
