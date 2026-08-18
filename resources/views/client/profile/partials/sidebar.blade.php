{{-- ===== SIDEBAR ===== --}}
<div class="col-md-3 col-lg-2 px-0 sidebar">
    <div class="profile-section">
        <img src="{{ auth()->user()->avatar_url }}" alt="Ảnh đại diện của {{ auth()->user()->name }}" class="profile-img"
            id="sidebar-avatar">
        <div class="profile-name" id="username-display">{{ auth()->user()->name }}</div>
    </div>

    <nav class="nav flex-column nav-pills px-3 mt-3" role="tablist">
        <a class="nav-link {{ $activeTab === 'personal' ? 'active' : '' }}" data-bs-toggle="pill" href="#personal"
            role="tab">
            <i class="fa-solid fa-id-card"></i> Thông tin cá nhân
        </a>
        <a class="nav-link {{ $activeTab === 'myVouchers' ? 'active' : '' }}" data-bs-toggle="pill" href="#myVouchers"
            role="tab">
            <i class="fa-solid fa-ticket"></i> Voucher
        </a>
        <a class="nav-link {{ $activeTab === 'changePassword' ? 'active' : '' }}" data-bs-toggle="pill"
            href="#changePassword" role="tab">
            <i class="fa-solid fa-key"></i> Đổi mật khẩu
        </a>
        <a class="nav-link {{ $activeTab === 'addressBook' ? 'active' : '' }}" data-bs-toggle="pill" href="#addressBook"
            role="tab">
            <i class="fa-solid fa-location-dot"></i> Sổ địa chỉ
        </a>

        <a class="nav-link dropdown-toggle" data-bs-toggle="collapse" href="#historyDropdown" role="button"
            aria-expanded="false">
            <span><i class="fa-solid fa-clock-rotate-left"></i> Lịch sử của tôi</span>
        </a>
        <div class="collapse dropdown-menu-custom" id="historyDropdown">
            <a class="dropdown-item-custom" data-bs-toggle="pill" href="#historyOrder" role="tab">
                <i class="fa-solid fa-box"></i> Đơn hàng
            </a>
            <a class="dropdown-item-custom" data-bs-toggle="pill" href="#historyWishlist" role="tab">
                <i class="fa-solid fa-heart"></i> Yêu thích
            </a>
        </div>
        @if (auth()->user()->role === 'customer')
            <a class="nav-link {{ $activeTab === 'sellerChannel' ? 'active' : '' }}" data-bs-toggle="pill"
                href="#sellerChannel" role="tab">
                <i class="fa-solid fa-shop"></i> Đăng ký bán hàng
            </a>
        @endif
    </nav>
</div>
