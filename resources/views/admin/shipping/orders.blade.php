@extends('layouts.admin.app')

@section('page-title', 'Quản lý Kiện hàng & Vận đơn')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.shipping.carriers.index') }}">Vận chuyển</a></li>
    <li class="breadcrumb-item active" aria-current="page">Kiện hàng</li>
@endsection

@section('content')
<div class="container-fluid px-0">

    <!-- Header Actions -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1">Danh sách Kiện hàng & Vận đơn</h4>
            <p class="text-muted small mb-0">Theo dõi toàn bộ bưu kiện đang luân chuyển trên sàn, tra cứu lộ trình bưu cục và mô phỏng giao hàng.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.shipping.carriers.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-truck-fast me-1"></i>Đối tác Vận chuyển
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <!-- Tabs Status Filter -->
            <div class="d-flex flex-wrap gap-2 pb-3 border-bottom mb-3">
                <button type="button" class="btn btn-sm btn-outline-secondary shipment-filter-tab active" data-status="">
                    Tất cả (<span id="countAll">0</span>)
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary shipment-filter-tab" data-status="pending">
                    Chờ xác nhận (<span id="countPending">0</span>)
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary shipment-filter-tab" data-status="confirmed">
                    Đang chuẩn bị (<span id="countConfirmed">0</span>)
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary shipment-filter-tab" data-status="shipping">
                    Đang vận chuyển (<span id="countShipping">0</span>)
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary shipment-filter-tab" data-status="completed">
                    Đã giao hàng (<span id="countCompleted">0</span>)
                </button>
            </div>

            <!-- Search & Carrier Select -->
            <div class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" id="searchShipmentInput" class="form-control border-start-0" placeholder="Tìm theo Mã đơn, Mã vận đơn, Tên shop, Người nhận...">
                    </div>
                </div>
                <div class="col-md-4">
                    <select id="carrierFilterSelect" class="form-select">
                        <option value="">-- Tất cả đơn vị vận chuyển --</option>
                        @foreach ($carriers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Mã Đơn / Mã Vận Đơn</th>
                        <th>Người gửi (Shop)</th>
                        <th>Người nhận (Khách)</th>
                        <th>Đơn vị / Phí ship</th>
                        <th>Trạng thái</th>
                        <th style="width: 170px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="shipmentsTableBody">
                    <!-- Dynamic Rows Loaded via JS -->
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="p-3 border-top d-flex justify-content-between align-items-center" id="shipmentsPaginationWrap" style="display: none;">
            <div class="text-muted small" id="shipmentsPaginationInfo"></div>
            <div class="d-flex gap-1" id="shipmentsPaginationLinks"></div>
        </div>
    </div>

</div>

<!-- Include Tracking Modal -->
@include('admin.shipping.partials._tracking-modal')

@endsection

@push('scripts')
<script src="{{ asset('admin/js/shipping.js') }}"></script>
@endpush
