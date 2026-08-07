@extends('layouts.admin.app')

@section('page-title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')

    {{-- ===== STAT CARDS ===== --}}
    <div class="row g-3 mb-4">

        <div class="col-xl-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon red">
                    <i class="fa-solid fa-bag-shopping"></i>
                </div>
                <div>
                    <div class="stat-value">1,248</div>
                    <div class="stat-label">Tong don hang</div>
                </div>
                <span class="stat-trend up">+12%</span>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fa-solid fa-money-bill-wave"></i>
                </div>
                <div>
                    <div class="stat-value">84.5M</div>
                    <div class="stat-label">Doanh thu thang nay</div>
                </div>
                <span class="stat-trend up">+8%</span>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div>
                    <div class="stat-value">3,491</div>
                    <div class="stat-label">Nguoi dung</div>
                </div>
                <span class="stat-trend up">+5%</span>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fa-solid fa-store"></i>
                </div>
                <div>
                    <div class="stat-value">142</div>
                    <div class="stat-label">Gian hang hoat dong</div>
                </div>
                <span class="stat-trend down">-2%</span>
            </div>
        </div>

    </div>

    {{-- ===== MAIN GRID ===== --}}
    <div class="row g-3">

        {{-- Gian hang cho duyet --}}
        <div class="col-lg-6">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">
                        <i class="fa-solid fa-store me-2" style="color: #c62828;"></i>
                        Gian hang cho duyet
                    </h2>
                    <a href="{{ route('admin.sellers.index') }}?status=pending"
                       class="btn-admin-outline" style="padding: 5px 12px; font-size: 12px;">
                        Xem tat ca
                    </a>
                </div>
                <div class="admin-card-body" style="padding: 0;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Ten gian hang</th>
                                <th>Nguoi ban</th>
                                <th>Ngay dang ky</th>
                                <th>Trang thai</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Shop Thoi Trang A</strong></td>
                                <td>Nguyen Van A</td>
                                <td>08/08/2026</td>
                                <td><span class="badge-status badge-pending">Cho duyet</span></td>
                            </tr>
                            <tr>
                                <td><strong>Dien Tu XYZ</strong></td>
                                <td>Tran Thi B</td>
                                <td>07/08/2026</td>
                                <td><span class="badge-status badge-pending">Cho duyet</span></td>
                            </tr>
                            <tr>
                                <td><strong>Sach & Van Phong</strong></td>
                                <td>Le Van C</td>
                                <td>06/08/2026</td>
                                <td><span class="badge-status badge-pending">Cho duyet</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- San pham cho duyet --}}
        <div class="col-lg-6">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">
                        <i class="fa-solid fa-box-open me-2" style="color: #c62828;"></i>
                        San pham cho duyet
                    </h2>
                    <a href="{{ route('admin.products.index') }}?status=pending"
                       class="btn-admin-outline" style="padding: 5px 12px; font-size: 12px;">
                        Xem tat ca
                    </a>
                </div>
                <div class="admin-card-body" style="padding: 0;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Ten san pham</th>
                                <th>Gian hang</th>
                                <th>Gia</th>
                                <th>Trang thai</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Ao Phong Nam Basic</strong></td>
                                <td>Shop Thoi Trang A</td>
                                <td>150,000 d</td>
                                <td><span class="badge-status badge-pending">Cho duyet</span></td>
                            </tr>
                            <tr>
                                <td><strong>Tai Nghe Bluetooth X5</strong></td>
                                <td>Dien Tu XYZ</td>
                                <td>450,000 d</td>
                                <td><span class="badge-status badge-pending">Cho duyet</span></td>
                            </tr>
                            <tr>
                                <td><strong>But Muc Cao Cap</strong></td>
                                <td>Sach & Van Phong</td>
                                <td>35,000 d</td>
                                <td><span class="badge-status badge-pending">Cho duyet</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Don hang gan day --}}
        <div class="col-12">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">
                        <i class="fa-solid fa-clock-rotate-left me-2" style="color: #c62828;"></i>
                        Don hang gan day
                    </h2>
                </div>
                <div class="admin-card-body" style="padding: 0;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Ma don hang</th>
                                <th>Khach hang</th>
                                <th>Gian hang</th>
                                <th>Tong tien</th>
                                <th>Phuong thuc TT</th>
                                <th>Trang thai</th>
                                <th>Ngay dat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>#ORD-2026-001</strong></td>
                                <td>Nguyen Van A</td>
                                <td>Shop Thoi Trang A</td>
                                <td>320,000 d</td>
                                <td>COD</td>
                                <td><span class="badge-status badge-completed">Hoan thanh</span></td>
                                <td>08/08/2026</td>
                            </tr>
                            <tr>
                                <td><strong>#ORD-2026-002</strong></td>
                                <td>Tran Thi B</td>
                                <td>Dien Tu XYZ</td>
                                <td>950,000 d</td>
                                <td>VNPay</td>
                                <td><span class="badge-status badge-pending">Cho xu ly</span></td>
                                <td>08/08/2026</td>
                            </tr>
                            <tr>
                                <td><strong>#ORD-2026-003</strong></td>
                                <td>Le Van C</td>
                                <td>Sach & Van Phong</td>
                                <td>75,000 d</td>
                                <td>COD</td>
                                <td><span class="badge-status badge-approved">Dang xu ly</span></td>
                                <td>07/08/2026</td>
                            </tr>
                            <tr>
                                <td><strong>#ORD-2026-004</strong></td>
                                <td>Pham Thi D</td>
                                <td>Shop Thoi Trang A</td>
                                <td>215,000 d</td>
                                <td>VNPay</td>
                                <td><span class="badge-status badge-canceled">Da huy</span></td>
                                <td>06/08/2026</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

@endsection
