<!DOCTYPE html>
<html lang="vi" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('page-title', 'Quản trị') — Cupo Admin</title>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Roboto:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    {{-- Bootstrap CSS --}}
    <link href="{{ asset('client/css/bootstrap.min.css') }}" rel="stylesheet">

    {{-- Admin CSS --}}
    <link href="{{ asset('admin/css/admin.css') }}" rel="stylesheet">
    @if (request()->routeIs('admin.roles.*'))
        <link href="{{ asset('admin/css/roles.css') }}" rel="stylesheet">
    @elseif (request()->routeIs('admin.coupons.*'))
        <link href="{{ asset('admin/css/coupons.css') }}" rel="stylesheet">
    @elseif (request()->routeIs('admin.flash-sales.*'))
        <link href="{{ asset('admin/css/flash-sales.css') }}" rel="stylesheet">
    @elseif (request()->routeIs('admin.orders.*'))
        <link href="{{ asset('admin/css/orders.css') }}" rel="stylesheet">
    @elseif (request()->routeIs('admin.settings.*'))
        <link href="{{ asset('admin/css/settings.css') }}" rel="stylesheet">
    @elseif (request()->routeIs('admin.customers.*'))
        <link href="{{ asset('admin/css/customers.css') }}" rel="stylesheet">
    @elseif (request()->routeIs('admin.disputes.*'))
        <link href="{{ asset('admin/css/disputes.css') }}" rel="stylesheet">
    @elseif (request()->routeIs('admin.support-tickets.*'))
        <link href="{{ asset('admin/css/support-tickets.css') }}" rel="stylesheet">
    @elseif (request()->routeIs('admin.withdrawals.*'))
        <link href="{{ asset('admin/css/withdrawals.css') }}" rel="stylesheet">
    @elseif (request()->routeIs('admin.shipping.*'))
        <link href="{{ asset('admin/css/shipping.css') }}" rel="stylesheet">
    @elseif (request()->routeIs('admin.activity-logs.*'))
        <link href="{{ asset('admin/css/activity-logs.css') }}" rel="stylesheet">
    @elseif (request()->routeIs('admin.reviews.*'))
        <link href="{{ asset('admin/css/reviews.css') }}" rel="stylesheet">
    @elseif (request()->routeIs('admin.analytics.*'))
        <link href="{{ asset('admin/css/analytics.css') }}" rel="stylesheet">
    @endif

    {{-- Notification Dropdown CSS --}}
    <link href="{{ asset('common/css/notifications.css') }}" rel="stylesheet">

    @stack('styles')
</head>

<body class="admin-body">

    {{-- Sidebar overlay (mobile) --}}
    <div id="sidebarOverlay" class="sidebar-overlay"></div>

    <div class="admin-wrapper">

        {{-- ===== SIDEBAR ===== --}}
        <aside class="admin-sidebar" id="adminSidebar">

            {{-- Brand --}}
            <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
                <img src="{{ asset('images/cupo-icon-transparent.svg') }}" alt="Cupo" class="brand-logo">
                <span class="brand-name">Cupo</span>
                <span class="brand-badge">Admin</span>
            </a>

            {{-- Navigation --}}
            <nav class="sidebar-nav">

                {{-- Tong quan --}}
                <div class="sidebar-nav-label">Tổng quan</div>
                <a href="{{ route('admin.dashboard') }}"
                    class="sidebar-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-gauge-high"></i>
                    Dashboard
                </a>

                {{-- Phân quyền hiển thị Menu theo Vai trò nhân viên --}}
                @php
                    $role = auth()->user()->role ?? '';
                    $isSuperOrAdmin = in_array($role, ['super-admin', 'admin']);
                    $isModerator = $role === 'moderator';
                    $isAccountant = $role === 'accountant';

                    $isFloorActive = request()->routeIs(
                        'admin.sellers.*',
                        'admin.products.*',
                        'admin.categories.*',
                        'admin.reviews.*',
                    );
                    $isBusinessActive = request()->routeIs(
                        'admin.orders.*',
                        'admin.withdrawals.*',
                        'admin.disputes.*',
                        'admin.support-tickets.*',
                        'admin.shipping.*',
                        'admin.analytics.*',
                    );
                    $isMarketingActive = request()->routeIs(
                        'admin.banners.*',
                        'admin.coupons.*',
                        'admin.flash-sales.*',
                    );
                    $isSystemActive = request()->routeIs(
                        'admin.roles.*',
                        'admin.customers.*',
                        'admin.settings.*',
                        'admin.activity-logs.*',
                    );
                @endphp

                {{-- 1. Quản lý sàn: Dành cho Admin & Moderator --}}
                @if ($isSuperOrAdmin || $isModerator)
                    <div class="sidebar-nav-label d-flex justify-content-between align-items-center {{ $isFloorActive ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#navGroupFloor"
                        aria-expanded="{{ $isFloorActive ? 'true' : 'false' }}">
                        <span>Quản lý sàn</span>
                        <i class="fa-solid fa-chevron-down nav-chevron"></i>
                    </div>

                    <div class="collapse {{ $isFloorActive ? 'show' : '' }}" id="navGroupFloor">
                        <a href="{{ route('admin.sellers.index') }}"
                            class="sidebar-nav-item {{ request()->routeIs('admin.sellers.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-store"></i>
                            Gian hàng & Seller
                        </a>

                        <a href="{{ route('admin.products.index') }}"
                            class="sidebar-nav-item {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-box-open"></i>
                            Sản phẩm
                        </a>

                        <a href="{{ route('admin.categories.index') }}"
                            class="sidebar-nav-item {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-tags"></i>
                            Danh mục
                        </a>

                        <a href="{{ route('admin.reviews.index') }}"
                            class="sidebar-nav-item {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-star"></i>
                            Quản lý Đánh giá
                        </a>
                    </div>

                    <hr class="sidebar-divider">
                @endif

                {{-- 2. Kinh doanh: Phân chia theo nghiệp vụ --}}
                <div class="sidebar-nav-label d-flex justify-content-between align-items-center {{ $isBusinessActive ? '' : 'collapsed' }}"
                    data-bs-toggle="collapse" data-bs-target="#navGroupBusiness"
                    aria-expanded="{{ $isBusinessActive ? 'true' : 'false' }}">
                    <span>Kinh doanh</span>
                    <i class="fa-solid fa-chevron-down nav-chevron"></i>
                </div>

                <div class="collapse {{ $isBusinessActive ? 'show' : '' }}" id="navGroupBusiness">
                    <a href="{{ route('admin.orders.index') }}"
                        class="sidebar-nav-item {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-bag-shopping"></i>
                        Đơn hàng
                    </a>

                    @if ($isSuperOrAdmin || $isAccountant)
                        <a href="{{ route('admin.withdrawals.index') }}"
                            class="sidebar-nav-item {{ request()->routeIs('admin.withdrawals.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-money-bill-transfer"></i>
                            Rút tiền & Seller
                        </a>
                    @endif

                    @if ($isSuperOrAdmin || $isModerator)
                        <a href="{{ route('admin.disputes.index') }}"
                            class="sidebar-nav-item {{ request()->routeIs('admin.disputes.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-scale-balanced"></i>
                            Tranh chấp Đơn hàng
                        </a>

                        <a href="{{ route('admin.support-tickets.index') }}"
                            class="sidebar-nav-item {{ request()->routeIs('admin.support-tickets.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-headset"></i>
                            Kháng nghị Seller
                        </a>
                    @endif

                    @if ($isSuperOrAdmin || $isModerator || $isAccountant)
                        <a href="{{ route('admin.shipping.carriers.index') }}"
                            class="sidebar-nav-item {{ request()->routeIs('admin.shipping.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-truck-fast"></i>
                            Vận chuyển & Đối tác
                        </a>
                    @endif

                    @if ($isSuperOrAdmin || $isAccountant)
                        <a href="{{ route('admin.analytics.index') }}"
                            class="sidebar-nav-item {{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-chart-pie"></i>
                            Báo cáo & Thống kê
                        </a>
                    @endif
                </div>

                {{-- 3. Marketing: Dành cho Admin & Moderator --}}
                @if ($isSuperOrAdmin || $isModerator)
                    <div class="sidebar-nav-label d-flex justify-content-between align-items-center {{ $isMarketingActive ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#navGroupMarketing"
                        aria-expanded="{{ $isMarketingActive ? 'true' : 'false' }}">
                        <span>Marketing</span>
                        <i class="fa-solid fa-chevron-down nav-chevron"></i>
                    </div>

                    <div class="collapse {{ $isMarketingActive ? 'show' : '' }}" id="navGroupMarketing">
                        <a href="{{ route('admin.banners.index') }}"
                            class="sidebar-nav-item {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-image"></i>
                            Banner trang chủ
                        </a>

                        <a href="{{ route('admin.coupons.index') }}"
                            class="sidebar-nav-item {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-ticket"></i>
                            Mã giảm giá
                        </a>

                        <a href="{{ route('admin.flash-sales.index') }}"
                            class="sidebar-nav-item {{ request()->routeIs('admin.flash-sales.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-bolt"></i>
                            Flash Sale
                        </a>
                    </div>
                @endif

                <hr class="sidebar-divider">

                {{-- 4. Hệ thống: Phân quyền & Cài đặt --}}
                @if ($isSuperOrAdmin || $isModerator)
                    <div class="sidebar-nav-label d-flex justify-content-between align-items-center {{ $isSystemActive ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#navGroupSystem"
                        aria-expanded="{{ $isSystemActive ? 'true' : 'false' }}">
                        <span>Hệ thống</span>
                        <i class="fa-solid fa-chevron-down nav-chevron"></i>
                    </div>

                    <div class="collapse {{ $isSystemActive ? 'show' : '' }}" id="navGroupSystem">
                        @if ($isSuperOrAdmin)
                            <a href="{{ route('admin.roles.index') }}"
                                class="sidebar-nav-item {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-shield-halved"></i>
                                Phân quyền & Chức vụ
                            </a>
                        @endif

                        <a href="{{ route('admin.customers.index') }}"
                            class="sidebar-nav-item {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-users"></i>
                            Người dùng
                        </a>

                        @if ($isSuperOrAdmin)
                            <a href="{{ route('admin.settings.index') }}"
                                class="sidebar-nav-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-gear"></i>
                                Cài đặt
                            </a>

                            <a href="{{ route('admin.activity-logs.index') }}"
                                class="sidebar-nav-item {{ request()->routeIs('admin.activity-logs.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                                Nhật ký hoạt động
                            </a>
                        @endif
                    </div>
                @endif

            </nav>

            {{-- Footer user info --}}
            <div class="sidebar-footer">
                <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="admin-avatar">
                <div class="admin-info">
                    <div class="admin-name text-truncate" style="max-width: 130px;">{{ auth()->user()->name }}</div>
                    <div class="admin-role">
                        {{ match (auth()->user()->role) {
                            'super-admin' => 'Super Admin',
                            'admin' => 'Quản trị viên',
                            'moderator' => 'Kiểm duyệt viên',
                            'accountant' => 'Kế toán sàn',
                            default => ucfirst(auth()->user()->role ?? 'Nhân viên'),
                        } }}
                    </div>
                </div>
                <button type="button" class="logout-btn" data-bs-toggle="modal" data-bs-target="#adminLogoutModal"
                    title="Đăng xuất">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </button>
            </div>

        </aside>
        {{-- END SIDEBAR --}}

        {{-- ===== TOPBAR ===== --}}
        <header class="admin-topbar">

            {{-- Hamburger (mobile) --}}
            <button class="topbar-toggle" id="sidebarToggle" type="button" aria-label="Mở sidebar">
                <i class="fa-solid fa-bars"></i>
            </button>

            {{-- Page title & breadcrumb --}}
            <div class="topbar-breadcrumb">
                <h1 class="page-title">@yield('page-title', 'Dashboard')</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">Admin</a>
                        </li>
                        @yield('breadcrumb')
                    </ol>
                </nav>
            </div>

            {{-- Actions --}}
            <div class="topbar-actions d-flex align-items-center">

                {{-- Role Badge --}}
                <span
                    class="badge rounded-pill px-3 py-2 me-1 {{ match (auth()->user()->role) {
                        'super-admin' => 'bg-danger text-white',
                        'admin' => 'bg-primary text-white',
                        'moderator' => 'bg-info text-dark',
                        'accountant' => 'bg-success text-white',
                        default => 'bg-secondary text-white',
                    } }}">
                    <i class="fa-solid fa-user-shield me-1"></i>
                    {{ match (auth()->user()->role) {
                        'super-admin' => 'Super Admin',
                        'admin' => 'Quản trị viên',
                        'moderator' => 'Kiểm duyệt viên',
                        'accountant' => 'Kế toán viên',
                        default => ucfirst(auth()->user()->role ?? 'Nhân viên'),
                    } }}
                </span>

                {{-- Notification Dropdown --}}
                <div class="dropdown" id="notificationDropdownWrap">
                    <button type="button" class="topbar-icon-btn position-relative" data-bs-toggle="dropdown"
                        id="notifBellBtn" aria-expanded="false" title="Thông báo">
                        <i class="fa-solid fa-bell"></i>
                        <span class="notif-badge-count d-none" id="notifBadgeCount">0</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end notif-dropdown-menu shadow-lg border-0 rounded-3 p-0"
                        aria-labelledby="notifBellBtn">
                        <div
                            class="notif-header p-3 border-bottom d-flex justify-content-between align-items-center bg-light rounded-top-3">
                            <h6 class="fw-bold mb-0 text-dark fs-7">
                                <i class="fa-solid fa-bell text-danger me-1"></i>Thông báo
                            </h6>
                            <button type="button" class="btn btn-link btn-sm text-decoration-none text-muted p-0"
                                id="btnMarkAllRead">
                                <i class="fa-solid fa-check-double me-1"></i>Đọc tất cả
                            </button>
                        </div>
                        <div class="notif-list-container" id="notifListContainer"
                            style="max-height: 360px; overflow-y: auto;">
                            <div class="p-4 text-center text-muted notif-empty-state">
                                <i class="fa-regular fa-bell-slash fs-3 mb-2 text-secondary"></i>
                                <div class="small">Chưa có thông báo nào mới</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Light / Dark toggle --}}
                <button type="button" id="themeToggleBtn" class="theme-toggle-btn" title="Đổi giao diện">
                    <i class="fa-solid fa-sun icon-sun"></i>
                    <i class="fa-solid fa-moon icon-moon"></i>
                </button>

                {{-- Xem trang chu --}}
                <a href="{{ route('home') }}" class="topbar-icon-btn" title="Xem trang Customer" target="_blank">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </a>

                {{-- Admin user --}}
                <div class="dropdown">
                    <a href="#" class="topbar-admin-info" data-bs-toggle="dropdown">
                        <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}"
                            class="topbar-avatar">
                        <span class="topbar-name">{{ auth()->user()->name }}</span>
                        <i class="fa-solid fa-chevron-down"
                            style="font-size:11px; color:#adb5bd; margin-left:2px;"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end mt-1">
                        <li>
                            <span class="dropdown-item-text small text-muted">
                                {{ auth()->user()->email }}
                            </span>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item text-danger" href="{{ route('home') }}">Sàn thương mại</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <button type="button" class="dropdown-item text-danger" data-bs-toggle="modal"
                                data-bs-target="#adminLogoutModal">
                                <i class="fa-solid fa-right-from-bracket me-2"></i>Đăng xuất
                            </button>
                        </li>
                    </ul>
                </div>

            </div>

        </header>
        {{-- END TOPBAR --}}

        {{-- ===== MAIN CONTENT ===== --}}
        <main class="admin-main">
            @yield('content')
        </main>

    </div>
    {{-- END WRAPPER --}}

    {{-- Logout modal --}}
    <form method="POST" action="{{ route('admin.logout') }}" id="adminLogoutForm">
        @csrf
    </form>

    <div class="modal fade" id="adminLogoutModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">Xác nhận đăng xuất</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-2">
                    Bạn có muốn đăng xuất khỏi trang quản trị?
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" form="adminLogoutForm" class="btn btn-sm btn-danger">
                        <i class="fa-solid fa-right-from-bracket me-1"></i>Đăng xuất
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Bootstrap JS --}}
    <script src="{{ asset('client/js/bootstrap.bundle.min.js') }}"></script>

    {{-- Admin JS --}}
    <script src="{{ asset('admin/js/admin.js') }}"></script>

    {{-- Notifications JS --}}
    <script src="{{ asset('common/js/notifications.js') }}"></script>

    @stack('scripts')

</body>

</html>
