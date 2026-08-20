@extends('layouts.admin.app')

@section('page-title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')

    {{-- ===== WELCOME BANNER ===== --}}
    <div class="admin-card mb-4 p-4 border-0" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #fff; border-radius: 16px;">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                    <h4 class="fw-bold mb-0 text-white">Xin chào, {{ auth()->user()->name }}! 👋</h4>
                    <span class="badge {{ match(auth()->user()->role) {
                        'super-admin' => 'bg-danger',
                        'admin'       => 'bg-primary',
                        'moderator'   => 'bg-info text-dark',
                        'accountant'  => 'bg-success',
                        default       => 'bg-secondary',
                    } }} px-3 py-1 rounded-pill">
                        {{ match(auth()->user()->role) {
                            'super-admin' => 'Super Admin',
                            'admin'       => 'Quản trị viên',
                            'moderator'   => 'Kiểm duyệt viên',
                            'accountant'  => 'Kế toán viên',
                            default       => ucfirst(auth()->user()->role ?? 'Nhân viên'),
                        } }}
                    </span>
                </div>
                <p class="text-white-50 small mb-0">
                    {{ match(auth()->user()->role) {
                        'super-admin' => 'Bạn có toàn quyền quản trị, cấu hình hệ thống và phân quyền nhân sự.',
                        'admin'       => 'Bạn có quyền quản lý toàn bộ gian hàng, sản phẩm, đơn hàng và tài chính.',
                        'moderator'   => 'Không gian làm việc: Kiểm duyệt hồ sơ Gian hàng (KYC), duyệt Sản phẩm và giải quyết Tranh chấp.',
                        'accountant'  => 'Không gian làm việc: Quản lý Yêu cầu rút tiền, đối soát Doanh thu và Đơn hàng.',
                        default       => 'Chúc bạn một ngày làm việc hiệu quả tại hệ thống Cupo!',
                    } }}
                </p>
            </div>
            <div class="text-md-end text-white-50 small text-nowrap">
                <i class="fa-solid fa-clock me-1"></i>{{ now()->format('H:i, d/m/Y') }}
            </div>
        </div>
    </div>

    {{-- ===== STAT CARDS ===== --}}
    <div class="row g-3 mb-4">

        <div class="col-xl-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon red">
                    <i class="fa-solid fa-bag-shopping"></i>
                </div>
                <div>
                    <div class="stat-value">{{ number_format($totalOrders) }}</div>
                    <div class="stat-label">Tổng đơn hàng</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fa-solid fa-money-bill-wave"></i>
                </div>
                <div>
                    <div class="stat-value">
                        @php
                            $rev = $revenueThisMonth;
                            echo $rev >= 1_000_000
                                ? number_format($rev / 1_000_000, 1) . 'M'
                                : number_format($rev / 1000, 0) . 'K';
                        @endphp
                    </div>
                    <div class="stat-label">Doanh thu tháng này</div>
                </div>
                @if ($revenueGrowth !== 0)
                    <span class="stat-trend {{ $revenueGrowth >= 0 ? 'up' : 'down' }}">
                        {{ $revenueGrowth >= 0 ? '+' : '' }}{{ $revenueGrowth }}%
                    </span>
                @endif
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div>
                    <div class="stat-value">{{ number_format($totalCustomers) }}</div>
                    <div class="stat-label">Khách hàng</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fa-solid fa-store"></i>
                </div>
                <div>
                    <div class="stat-value">{{ number_format($activeShops) }}</div>
                    <div class="stat-label">Gian hàng hoạt động</div>
                </div>
            </div>
        </div>

    </div>

    {{-- ===== MAIN GRID ===== --}}
    <div class="row g-3">

        {{-- Gian hàng chờ duyệt --}}
        <div class="col-lg-6">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">
                        <i class="fa-solid fa-store me-2" style="color: #c62828;"></i>
                        Gian hàng chờ duyệt
                        @if ($pendingSellers->count())
                            <span class="badge bg-danger ms-1" style="font-size: 11px;">{{ $pendingSellers->count() }}</span>
                        @endif
                    </h2>
                    <a href="{{ route('admin.sellers.index') }}?status=pending"
                       class="btn-admin-outline" style="padding: 5px 12px; font-size: 12px;">
                        Xem tất cả
                    </a>
                </div>
                <div class="admin-card-body" style="padding: 0;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Tên gian hàng</th>
                                <th>Người bán</th>
                                <th>Ngày đăng ký</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pendingSellers as $seller)
                                <tr>
                                    <td><strong>{{ $seller->shop_name }}</strong></td>
                                    <td>{{ $seller->user?->name ?? '—' }}</td>
                                    <td>{{ $seller->created_at->format('d/m/Y') }}</td>
                                    <td><span class="badge-status badge-pending">Chờ duyệt</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">
                                        <i class="fa-solid fa-check-circle text-success me-1"></i>
                                        Không có gian hàng nào chờ duyệt.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Sản phẩm chờ duyệt --}}
        <div class="col-lg-6">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">
                        <i class="fa-solid fa-box-open me-2" style="color: #c62828;"></i>
                        Sản phẩm chờ duyệt
                        @if ($pendingProducts->count())
                            <span class="badge bg-danger ms-1" style="font-size: 11px;">{{ $pendingProducts->count() }}</span>
                        @endif
                    </h2>
                    <a href="{{ route('admin.products.index') }}?status=pending"
                       class="btn-admin-outline" style="padding: 5px 12px; font-size: 12px;">
                        Xem tất cả
                    </a>
                </div>
                <div class="admin-card-body" style="padding: 0;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Tên sản phẩm</th>
                                <th>Gian hàng</th>
                                <th>Giá</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pendingProducts as $product)
                                <tr>
                                    <td><strong>{{ Str::limit($product->name, 30) }}</strong></td>
                                    <td>{{ $product->seller?->sellerProfile?->shop_name ?? '—' }}</td>
                                    <td>{{ number_format($product->price) }}đ</td>
                                    <td><span class="badge-status badge-pending">Chờ duyệt</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">
                                        <i class="fa-solid fa-check-circle text-success me-1"></i>
                                        Không có sản phẩm nào chờ duyệt.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Đơn hàng gần đây --}}
        <div class="col-12">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">
                        <i class="fa-solid fa-clock-rotate-left me-2" style="color: #c62828;"></i>
                        Đơn hàng gần đây
                    </h2>
                    <a href="{{ route('admin.orders.index') }}"
                       class="btn-admin-outline" style="padding: 5px 12px; font-size: 12px;">
                        Xem tất cả
                    </a>
                </div>
                <div class="admin-card-body" style="padding: 0;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Mã đơn hàng</th>
                                <th>Khách hàng</th>
                                <th>Tổng tiền</th>
                                <th>Phương thức TT</th>
                                <th>TT Thanh toán</th>
                                <th>Ngày đặt</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentOrders as $order)
                                @php
                                    $payBadge = match($order->payment_status) {
                                        'paid'     => ['Đã TT', 'badge-completed'],
                                        'failed'   => ['Lỗi TT', 'badge-canceled'],
                                        'refunded' => ['Hoàn tiền', 'badge-approved'],
                                        default    => ['Chờ TT', 'badge-pending'],
                                    };
                                    $methodLabel = match($order->payment_method) {
                                        'vnpay' => 'VNPay',
                                        'momo'  => 'MoMo',
                                        default => 'COD',
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.orders.show', $order) }}"
                                           class="fw-semibold text-decoration-none text-danger">
                                            #{{ $order->order_number }}
                                        </a>
                                    </td>
                                    <td>{{ $order->user?->name ?? $order->shipping_name }}</td>
                                    <td class="fw-semibold">{{ number_format($order->grand_total) }}đ</td>
                                    <td>{{ $methodLabel }}</td>
                                    <td><span class="badge-status {{ $payBadge[1] }}">{{ $payBadge[0] }}</span></td>
                                    <td class="text-muted">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3">
                                        Chưa có đơn hàng nào.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

@endsection
