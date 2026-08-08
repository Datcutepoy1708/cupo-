<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('page-title', 'Quản trị') — Cupo Admin</title>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Roboto:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    {{-- Bootstrap CSS --}}
    <link href="{{ asset('client/css/bootstrap.min.css') }}" rel="stylesheet">

    {{-- Admin CSS --}}
    <link href="{{ asset('admin/css/admin.css') }}" rel="stylesheet">

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
                <div class="sidebar-nav-label">Tong quan</div>
                <a href="{{ route('admin.dashboard') }}"
                   class="sidebar-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-gauge-high"></i>
                    Dashboard
                </a>

                <hr class="sidebar-divider">

                {{-- San xuat --}}
                <div class="sidebar-nav-label">Quan ly san</div>

                <a href="{{ route('admin.sellers.index') }}"
                   class="sidebar-nav-item {{ request()->routeIs('admin.sellers.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-store"></i>
                    Gian hang & Seller
                </a>

                <a href="{{ route('admin.products.index') }}"
                   class="sidebar-nav-item {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-box-open"></i>
                    San pham
                </a>

                <a href="{{ route('admin.categories.index') }}"
                   class="sidebar-nav-item {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-tags"></i>
                    Danh muc
                </a>

                <hr class="sidebar-divider">

                {{-- Kinh doanh --}}
                <div class="sidebar-nav-label">Kinh doanh</div>

                <a href="#"
                   class="sidebar-nav-item {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-bag-shopping"></i>
                    Don hang
                </a>

                <a href="#"
                   class="sidebar-nav-item {{ request()->routeIs('admin.withdrawals.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-money-bill-transfer"></i>
                    Rut tien Seller
                </a>

                <a href="#"
                   class="sidebar-nav-item {{ request()->routeIs('admin.disputes.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-scale-balanced"></i>
                    Tranh chap
                </a>

                <hr class="sidebar-divider">

                {{-- Marketing --}}
                <div class="sidebar-nav-label">Marketing</div>

                <a href="#"
                   class="sidebar-nav-item {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-image"></i>
                    Banner trang chu
                </a>

                <a href="#"
                   class="sidebar-nav-item {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-ticket"></i>
                    Ma giam gia
                </a>

                <a href="#"
                   class="sidebar-nav-item {{ request()->routeIs('admin.flash-sales.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-bolt"></i>
                    Flash Sale
                </a>

                <hr class="sidebar-divider">

                {{-- He thong --}}
                <div class="sidebar-nav-label">He thong</div>

                <a href="#"
                   class="sidebar-nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users"></i>
                    Nguoi dung
                </a>

                <a href="#" class="sidebar-nav-item">
                    <i class="fa-solid fa-gear"></i>
                    Cai dat
                </a>

            </nav>

            {{-- Footer user info --}}
            <div class="sidebar-footer">
                <img src="{{ auth()->user()->avatar_url }}"
                     alt="{{ auth()->user()->name }}"
                     class="admin-avatar">
                <div class="admin-info">
                    <div class="admin-name">{{ auth()->user()->name }}</div>
                    <div class="admin-role">Quan tri vien</div>
                </div>
                <button type="button"
                        class="logout-btn"
                        data-bs-toggle="modal"
                        data-bs-target="#adminLogoutModal"
                        title="Dang xuat">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </button>
            </div>

        </aside>
        {{-- END SIDEBAR --}}

        {{-- ===== TOPBAR ===== --}}
        <header class="admin-topbar">

            {{-- Hamburger (mobile) --}}
            <button class="topbar-toggle" id="sidebarToggle" type="button" aria-label="Mo sidebar">
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
            <div class="topbar-actions">

                {{-- Thong bao --}}
                <button type="button" class="topbar-icon-btn" title="Thong bao">
                    <i class="fa-solid fa-bell"></i>
                    <span class="notif-dot"></span>
                </button>

                {{-- Xem trang chu --}}
                <a href="{{ route('home') }}"
                   class="topbar-icon-btn"
                   title="Xem trang Customer"
                   target="_blank">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </a>

                {{-- Admin user --}}
                <div class="dropdown">
                    <a href="#" class="topbar-admin-info" data-bs-toggle="dropdown">
                        <img src="{{ auth()->user()->avatar_url }}"
                             alt="{{ auth()->user()->name }}"
                             class="topbar-avatar">
                        <span class="topbar-name">{{ auth()->user()->name }}</span>
                        <i class="fa-solid fa-chevron-down" style="font-size:11px; color:#adb5bd; margin-left:2px;"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end mt-1">
                        <li>
                            <span class="dropdown-item-text small text-muted">
                                {{ auth()->user()->email }}
                            </span>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <button type="button"
                                    class="dropdown-item text-danger"
                                    data-bs-toggle="modal"
                                    data-bs-target="#adminLogoutModal">
                                <i class="fa-solid fa-right-from-bracket me-2"></i>Dang xuat
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
                    <h5 class="modal-title">Xac nhan dang xuat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-2">
                    Ban co chac chan muon dang xuat khoi trang quan tri?
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Huy</button>
                    <button type="submit"
                            form="adminLogoutForm"
                            class="btn btn-sm btn-danger">
                        <i class="fa-solid fa-right-from-bracket me-1"></i>Dang xuat
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Bootstrap JS --}}
    <script src="{{ asset('client/js/bootstrap.bundle.min.js') }}"></script>

    {{-- Admin JS --}}
    <script src="{{ asset('admin/js/admin.js') }}"></script>

    @stack('scripts')

</body>

</html>
