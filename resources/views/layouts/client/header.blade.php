<nav class="navbar navbar-expand-lg bg-body-tertiary p-3">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center me-5" href="{{ route('home') }}">
            <img src="{{ asset('images/cupo-icon-transparent.svg') }}" alt="Cupo" width="36" height="36"
                class="d-block mb-1">
            <span class="logo-text">Cupo</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarScroll"
            aria-controls="navbarScroll" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarScroll">
            <ul class="navbar-nav me-auto my-2 my-lg-0 navbar-nav-scroll" style="--bs-scroll-height: 100px;">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}"
                        href="{{ route('home') }}">Trang chủ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('promotions') ? 'active' : '' }}"
                        href="{{ route('promotions') }}">Khuyến mãi</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        Danh mục
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#">Bán nhiều nhất</a></li>
                        <li><a class="dropdown-item" href="#">Đánh giá cao nhất</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('help') ? 'active' : '' }}" href="{{ route('help') }}">Trợ
                        giúp</a>
                </li>
            </ul>

            <form class="d-flex flex-grow-1 ms-5 me-5" role="search">
                <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search" />
                <button class="btn btn-outline-success" type="submit">Search</button>
            </form>

            {{-- Nút đăng nhập / đăng ký --}}
            <div class="d-flex">
                @guest
                    <a href="{{ route('login') }}" class="btn btn-outline-primary flex-fill flex-lg-grow-0 me-2">Đăng
                        nhập</a>
                    <a href="{{ route('register') }}" class="btn btn-primary flex-fill flex-lg-grow-0">Đăng ký</a>
                @else
                    <a href="{{ route('cart.index') }}"
                        class="btn btn-outline-light position-relative me-2 d-flex align-items-center justify-content-center"
                        style="width: 38px; height: 38px;">
                        <i class="fa-solid fa-cart-shopping" style="font-size: 18px;"></i>
                        @if (auth()->user()->cart_count ?? 0 > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                style="font-size: 10px;">
                                {{ auth()->user()->cart_count }}
                            </span>
                        @endif
                    </a>
                    <div class="dropdown">
                        <a class="btn btn-outline-secondary dropdown-toggle" href="#" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            {{ auth()->user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('profile.show') }}">Tài khoản</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <button type="button" class="dropdown-item" data-bs-toggle="modal"
                                    data-bs-target="#logoutModal">
                                    Đăng xuất
                                </button>
                            </li>
                        </ul>
                    </div>
                @endguest
            </div>
        </div>
    </div>
</nav>
