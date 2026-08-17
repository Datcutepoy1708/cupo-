@extends('layouts.admin.app')

@section('page-title', 'Quản lý Flash Sale')

@section('breadcrumb')
    <li class="breadcrumb-item active">Flash Sale</li>
@endsection

@push('styles')
    <link href="{{ asset('admin/css/flash-sales.css') }}" rel="stylesheet">
@endpush

@section('content')

    {{-- 1. Stat cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card border-0 shadow-sm rounded-3 p-3 bg-white d-flex align-items-center">
                <div class="stat-icon bg-primary-subtle text-primary rounded-circle me-3 p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-bolt fa-lg"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Tổng số phiên</span>
                    <h4 class="fw-bold mb-0">{{ number_format($totalSales) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card border-0 shadow-sm rounded-3 p-3 bg-white d-flex align-items-center">
                <div class="stat-icon bg-success-subtle text-success rounded-circle me-3 p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-fire fa-lg"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Đang diễn ra (Live)</span>
                    <h4 class="fw-bold mb-0 text-success">{{ number_format($liveSales) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card border-0 shadow-sm rounded-3 p-3 bg-white d-flex align-items-center">
                <div class="stat-icon bg-warning-subtle text-warning rounded-circle me-3 p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-clock fa-lg"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Sắp diễn ra</span>
                    <h4 class="fw-bold mb-0 text-warning">{{ number_format($upcomingSales) }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. Bang danh sach Flash Sale --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
            <h5 class="mb-0 fw-bold"><i class="fa-solid fa-list me-2 text-primary"></i>Danh sách các phiên Flash Sale</h5>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#flashSaleFormModal" id="btnOpenCreateModal">
                <i class="fa-solid fa-plus me-1"></i> Tạo phiên mới
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Tên phiên</th>
                            <th>Thời gian Bắt đầu</th>
                            <th>Thời gian Kết thúc</th>
                            <th>Số sản phẩm</th>
                            <th>Trạng thái Kích hoạt</th>
                            <th>Trạng thái Vận hành</th>
                            <th class="text-end pe-4">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($flashSales as $sale)
                            <tr>
                                <td class="ps-4 fw-semibold text-dark">
                                    <div>{{ $sale->name }}</div>
                                    @if($sale->registration_deadline)
                                        <div class="text-muted small">
                                            <i class="fa-solid fa-clock-rotate-left text-danger me-1"></i>Hạn ĐK: {{ $sale->registration_deadline->format('d/m/Y H:i') }}
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $sale->starts_at ? $sale->starts_at->format('d/m/Y H:i') : '--' }}</td>
                                <td>{{ $sale->ends_at ? $sale->ends_at->format('d/m/Y H:i') : '--' }}</td>
                                <td>
                                    <span class="badge bg-secondary rounded-pill">{{ $sale->products_count }} SP</span>
                                </td>
                                <td>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input toggle-status-btn" 
                                               type="checkbox" 
                                               role="switch" 
                                               data-id="{{ $sale->id }}"
                                               data-url="{{ route('admin.flash-sales.toggle', $sale) }}"
                                               {{ $sale->status ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $execStatus = $sale->execution_status;
                                    @endphp
                                    @if($execStatus === 'live')
                                        <span class="badge bg-danger text-white"><i class="fa-solid fa-fire me-1"></i>Đang diễn ra</span>
                                    @elseif($execStatus === 'upcoming')
                                        <span class="badge bg-warning text-dark"><i class="fa-solid fa-clock me-1"></i>Sắp diễn ra</span>
                                    @elseif($execStatus === 'expired')
                                        <span class="badge bg-secondary text-white"><i class="fa-solid fa-calendar-xmark me-1"></i>Đã kết thúc</span>
                                    @else
                                        <span class="badge bg-light text-muted border"><i class="fa-solid fa-ban me-1"></i>Đã tắt</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button"
                                                class="btn btn-outline-secondary btn-view-registrations"
                                                data-id="{{ $sale->id }}"
                                                data-name="{{ $sale->name }}"
                                                data-registrations-url="{{ route('admin.flash-sales.registrations.index', $sale) }}"
                                                data-approve-url="{{ url('admin/flash-sales/registrations') }}"
                                                data-reject-url="{{ url('admin/flash-sales/registrations') }}">
                                            <i class="fa-solid fa-clipboard-list me-1"></i> Dang ky
                                        </button>
                                        <button type="button" 
                                                class="btn btn-outline-info btn-manage-products" 
                                                data-id="{{ $sale->id }}"
                                                data-name="{{ $sale->name }}"
                                                data-sync-url="{{ route('admin.flash-sales.products.sync', $sale) }}"
                                                data-products='@json($sale->products->load("product"))'>
                                            <i class="fa-solid fa-boxes-stacked me-1"></i> Sản phẩm
                                        </button>
                                        <button type="button" 
                                                class="btn btn-outline-primary btn-edit-sale"
                                                data-id="{{ $sale->id }}"
                                                data-name="{{ $sale->name }}"
                                                data-starts-at="{{ $sale->starts_at ? $sale->starts_at->format('Y-m-d\TH:i') : '' }}"
                                                data-ends-at="{{ $sale->ends_at ? $sale->ends_at->format('Y-m-d\TH:i') : '' }}"
                                                data-registration-deadline="{{ $sale->registration_deadline ? $sale->registration_deadline->format('Y-m-d\TH:i') : '' }}"
                                                data-status="{{ $sale->status }}"
                                                data-update-url="{{ route('admin.flash-sales.update', $sale) }}">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button type="button" 
                                                class="btn btn-outline-danger btn-delete-sale"
                                                data-id="{{ $sale->id }}"
                                                data-delete-url="{{ route('admin.flash-sales.destroy', $sale) }}"
                                                {{ $execStatus === 'live' ? 'disabled' : '' }}>
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-bolt fa-3x mb-3 opacity-25"></i>
                                    <p class="mb-0">Chưa có phiên Flash Sale nào được khởi tạo.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($flashSales->hasPages())
            <div class="card-footer bg-white py-3">
                {{ $flashSales->links() }}
            </div>
        @endif
    </div>

    {{-- Modals --}}
    @include('admin.flash-sales.partials._form-modal')
    @include('admin.flash-sales.partials._products-modal')
    @include('admin.flash-sales.partials._registrations-modal')

@endsection

@push('scripts')
    <script src="{{ asset('admin/js/flash-sales.js') }}"></script>
    <script src="{{ asset('admin/js/flash-sale-registrations.js') }}"></script>
@endpush
