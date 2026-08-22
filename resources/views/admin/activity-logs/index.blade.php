@extends('layouts.admin.app')

@section('page-title', 'Nhật ký Hoạt động & Kiểm toán')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active" aria-current="page">Nhật ký hoạt động</li>
@endsection

@section('content')
<div class="container-fluid px-0">

    <!-- Header Actions -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1">Nhật ký Hoạt động Nhân viên (Audit Trail)</h4>
            <p class="text-muted small mb-0">Theo dõi toàn bộ lịch sử thao tác của các tài khoản quản trị, đảm bảo tính minh bạch và an toàn hệ thống.</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" id="btnExportLogs" class="btn btn-outline-success">
                <i class="fa-solid fa-file-excel me-1"></i>Xuất file CSV
            </button>
        </div>
    </div>

    <!-- 4 Stat Cards: Security & Audit Metrics -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="audit-stat-card shadow-sm">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-primary bg-opacity-10 text-primary rounded-3 p-3 me-3">
                        <i class="fa-solid fa-clipboard-list fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Tổng số nhật ký</div>
                        <h4 class="fw-bold mb-0" id="statTotalLogs">0</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="audit-stat-card shadow-sm">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-info bg-opacity-10 text-info rounded-3 p-3 me-3">
                        <i class="fa-solid fa-calendar-day fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Thao tác hôm nay</div>
                        <h4 class="fw-bold mb-0 text-info" id="statTodayLogs">0</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="audit-stat-card shadow-sm">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-danger bg-opacity-10 text-danger rounded-3 p-3 me-3">
                        <i class="fa-solid fa-triangle-exclamation fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Thao tác nhạy cảm</div>
                        <h4 class="fw-bold mb-0 text-danger" id="statSensitiveLogs">0</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="audit-stat-card shadow-sm">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-secondary bg-opacity-10 text-secondary rounded-3 p-3 me-3">
                        <i class="fa-solid fa-user-lock fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Lượt đăng nhập</div>
                        <h4 class="fw-bold mb-0 text-secondary" id="statAuthLogs">0</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center">
                <!-- Search Input -->
                <div class="col-lg-3 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" id="searchLogInput" class="form-control border-start-0" placeholder="Tìm theo mô tả, IP, email...">
                    </div>
                </div>

                <!-- User Filter -->
                <div class="col-lg-2 col-md-6">
                    <select id="userFilterSelect" class="form-select">
                        <option value="">-- Tất cả nhân viên --</option>
                        @foreach ($staffUsers as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ ucfirst($u->role) }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Module Filter -->
                <div class="col-lg-2 col-md-6">
                    <select id="moduleFilterSelect" class="form-select">
                        <option value="">-- Tất cả phân hệ --</option>
                        @foreach ($modules as $modKey => $modName)
                            <option value="{{ $modKey }}">{{ $modName }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Range -->
                <div class="col-lg-2 col-md-3 col-6">
                    <input type="date" id="dateFromInput" class="form-control" title="Từ ngày">
                </div>
                <div class="col-lg-2 col-md-3 col-6">
                    <input type="date" id="dateToInput" class="form-control" title="Đến ngày">
                </div>

                <!-- Reset Button -->
                <div class="col-lg-1 col-md-12 text-end">
                    <button type="button" id="btnResetFilters" class="btn btn-outline-secondary w-100" title="Đặt lại bộ lọc">
                        <i class="fa-solid fa-rotate-left"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Logs Datagrid Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Thời gian</th>
                        <th>Nhân viên</th>
                        <th>Phân hệ</th>
                        <th>Hành động & Mô tả</th>
                        <th>Địa chỉ IP</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="activityLogsTableBody">
                    <!-- Dynamic Rows Loaded via JS -->
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="p-3 border-top d-flex justify-content-between align-items-center" id="logsPaginationWrap" style="display: none;">
            <div class="text-muted small" id="logsPaginationInfo"></div>
            <div class="d-flex gap-1" id="logsPaginationLinks"></div>
        </div>
    </div>

</div>

<!-- Include Detail Modal -->
@include('admin.activity-logs.partials._detail-modal')

@endsection

@push('scripts')
<script src="{{ asset('admin/js/activity-logs.js') }}"></script>
@endpush
