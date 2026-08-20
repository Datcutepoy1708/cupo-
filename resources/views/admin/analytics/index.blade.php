@extends('layouts.admin.app')

@section('page-title', 'Báo cáo & Thống kê Doanh thu')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active">Báo cáo Tài chính</li>
@endsection

@push('styles')
<link href="{{ asset('admin/css/analytics.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid px-0">

    <!-- Header Actions & Date Range Picker -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1">Báo cáo Doanh thu & Phân tích Tài chính</h4>
            <p class="text-muted small mb-0">Theo dõi toàn diện GMV giao dịch, phí hoa hồng thực nhận của sàn và đối soát công nợ Seller.</p>
        </div>

        <div class="d-flex flex-wrap gap-2 align-items-center">
            <form method="GET" action="{{ route('admin.analytics.index') }}" class="d-flex gap-2 align-items-center">
                <select name="period" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="7_days" {{ $period === '7_days' ? 'selected' : '' }}>7 ngày qua</option>
                    <option value="30_days" {{ $period === '30_days' ? 'selected' : '' }}>30 ngày qua</option>
                    <option value="this_month" {{ $period === 'this_month' ? 'selected' : '' }}>Tháng này</option>
                </select>
            </form>

            <a href="{{ route('admin.analytics.export') }}" class="btn btn-outline-success btn-sm">
                <i class="fa-solid fa-file-excel me-1"></i>Xuất Excel / CSV
            </a>
        </div>
    </div>

    <!-- 4 Big Financial KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="kpi-card shadow-sm">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="text-muted small text-uppercase fw-bold">Tổng GMV Toàn Sàn</span>
                        <h4 class="fw-bold text-primary mt-1 mb-0">{{ number_format($kpis['gmv']) }}₫</h4>
                    </div>
                    <div class="kpi-icon-wrap bg-primary bg-opacity-10 text-primary">
                        <i class="fa-solid fa-sack-dollar"></i>
                    </div>
                </div>
                <div class="text-muted small mt-2">
                    Từ <strong>{{ number_format($kpis['completed_orders']) }}</strong> đơn hàng hoàn tất
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="kpi-card shadow-sm">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="text-muted small text-uppercase fw-bold">Hoa Hồng Sàn Thu</span>
                        <h4 class="fw-bold text-success mt-1 mb-0">{{ number_format($kpis['commission_revenue']) }}₫</h4>
                    </div>
                    <div class="kpi-icon-wrap bg-success bg-opacity-10 text-success">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                </div>
                <div class="text-muted small mt-2">
                    Doanh thu thực chảy vào quỹ sàn Cupo
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="kpi-card shadow-sm">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="text-muted small text-uppercase fw-bold">Tiền Đã Giải Ngân</span>
                        <h4 class="fw-bold text-danger mt-1 mb-0">{{ number_format($kpis['disbursed_withdrawals']) }}₫</h4>
                    </div>
                    <div class="kpi-icon-wrap bg-danger bg-opacity-10 text-danger">
                        <i class="fa-solid fa-money-bill-transfer"></i>
                    </div>
                </div>
                <div class="text-muted small mt-2">
                    Đã thanh toán rút tiền cho các Shop
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="kpi-card shadow-sm">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="text-muted small text-uppercase fw-bold">Tiền Ký Quỹ Đang Giữ</span>
                        <h4 class="fw-bold text-warning mt-1 mb-0">{{ number_format($kpis['escrow_balance']) }}₫</h4>
                    </div>
                    <div class="kpi-icon-wrap bg-warning bg-opacity-10 text-warning">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                </div>
                <div class="text-muted small mt-2">
                    Đơn hàng đang xử lý & giao bưu cục
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row g-3 mb-4">
        <!-- Revenue Trend Line Chart -->
        <div class="col-lg-8">
            <div class="chart-card shadow-sm h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Biến động Doanh thu & Dòng tiền</h6>
                    <span class="badge bg-light text-muted border">Cập nhật thời gian thực</span>
                </div>
                <div style="height: 300px;">
                    <canvas id="revenueTrendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Category Share Donut Chart -->
        <div class="col-lg-4">
            <div class="chart-card shadow-sm h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Tỷ trọng Doanh thu Ngành hàng</h6>
                </div>
                <div style="height: 300px; position: relative;">
                    <canvas id="categoryShareChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Rankings: Top Sellers & Top Products -->
    <div class="row g-3 mb-4">
        <!-- Top 5 Sellers -->
        <div class="col-lg-6">
            <div class="ranking-card shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0"><i class="fa-solid fa-crown text-warning me-2"></i>Top 5 Gian Hàng Doanh Thu Cao Nhất</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Tên Gian Hàng</th>
                                <th class="text-center">Đơn hoàn tất</th>
                                <th class="text-end">Doanh số (GMV)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topSellers as $idx => $seller)
                                <tr>
                                    <td>
                                        <span class="rank-badge {{ $idx === 0 ? 'rank-badge-1' : ($idx === 1 ? 'rank-badge-2' : ($idx === 2 ? 'rank-badge-3' : 'rank-badge-normal')) }}">
                                            {{ $idx + 1 }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $seller['shop_name'] }}</div>
                                        <div class="text-muted" style="font-size: 11px;">Chủ shop: {{ $seller['owner_name'] }}</div>
                                    </td>
                                    <td class="text-center">{{ number_format($seller['orders_count']) }}</td>
                                    <td class="text-end fw-bold text-primary">{{ number_format($seller['total_gmv']) }}₫</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">Chưa có dữ liệu giao dịch</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Top 5 Products -->
        <div class="col-lg-6">
            <div class="ranking-card shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0"><i class="fa-solid fa-fire text-danger me-2"></i>Top 5 Sản Phẩm Bán Chạy Nhất</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Sản Phẩm</th>
                                <th class="text-center">Đã bán</th>
                                <th class="text-end">Doanh số</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topProducts as $idx => $prod)
                                <tr>
                                    <td>
                                        <span class="rank-badge {{ $idx === 0 ? 'rank-badge-1' : ($idx === 1 ? 'rank-badge-2' : ($idx === 2 ? 'rank-badge-3' : 'rank-badge-normal')) }}">
                                            {{ $idx + 1 }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark text-truncate" style="max-width: 220px;">{{ $prod['name'] }}</div>
                                    </td>
                                    <td class="text-center fw-semibold">{{ number_format($prod['total_qty']) }}</td>
                                    <td class="text-end fw-bold text-success">{{ number_format($prod['total_revenue']) }}₫</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">Chưa có dữ liệu bán hàng</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Seller Financial Reconciliation Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h6 class="fw-bold mb-0">Báo Cáo Đối Soát Công Nợ & Tài Chính Từng Gian Hàng</h6>
            <form method="GET" action="{{ route('admin.analytics.index') }}" class="d-flex gap-2">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Tìm tên shop, email..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-sm btn-primary">Tìm</button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th>Tên Gian Hàng</th>
                        <th>Chủ Shop / Email</th>
                        <th class="text-end">Tổng GMV</th>
                        <th class="text-end">Phí Sàn Thu (5%)</th>
                        <th class="text-end">Doanh Thu Shop</th>
                        <th class="text-end">Đã Rút Tiền</th>
                        <th class="text-end">Số Dư Khả Dụng</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reconciliations as $row)
                        <tr>
                            <td class="fw-bold text-dark">{{ $row['shop_name'] }}</td>
                            <td>
                                <div>{{ $row['owner_name'] }}</div>
                                <div class="text-muted" style="font-size: 11px;">{{ $row['email'] }}</div>
                            </td>
                            <td class="text-end fw-semibold">{{ number_format($row['total_gmv']) }}₫</td>
                            <td class="text-end text-danger">{{ number_format($row['total_commission']) }}₫</td>
                            <td class="text-end text-success fw-bold">{{ number_format($row['net_earnings']) }}₫</td>
                            <td class="text-end text-muted">{{ number_format($row['total_withdrawn']) }}₫</td>
                            <td class="text-end text-primary fw-bold">{{ number_format($row['available_balance']) }}₫</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-4 text-muted">Không tìm thấy dữ liệu đối soát</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="p-3 border-top">
            {{ $reconciliations->links() }}
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    window.revenueTrendData = @json($trend);
    window.categoryShareData = @json($categoryShare);
</script>
<script src="{{ asset('admin/js/analytics.js') }}"></script>
@endpush
