<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- embed font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Pacifico&family=Playfair+Display:wght@700&family=Bebas+Neue&display=swap"
        rel="stylesheet">
    {{-- embed icon --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    {{-- embed bootstrap css --}}
    <link href="{{ asset('client/css/bootstrap.min.css') }}" rel="stylesheet">
    {{-- embed common css --}}
    <link href="{{ asset('client/css/common.css') }}" rel="stylesheet">
    {{-- embed header css --}}
    <link href="{{ asset('client/css/header.css') }}" rel="stylesheet">
    {{-- embed footer css --}}
    <link href="{{ asset('client/css/footer.css') }}" rel="stylesheet">
    {{-- embed content css --}}
    @if (request()->routeIs('promotions'))
        <link href="{{ asset('client/css/promotions.css') }}" rel="stylesheet">
    @elseif (request()->routeIs('categories.*'))
        <link href="{{ asset('client/css/category-show.css') }}" rel="stylesheet">
    @elseif (request()->routeIs('products.*'))
        <link href="{{ asset('client/css/product-show.css') }}" rel="stylesheet">
    @elseif (request()->routeIs('profile.*'))
        <link href="{{ asset('client/css/profile.css') }}" rel="stylesheet">
    @elseif (request()->routeIs('seller.shop'))
        <link href="{{ asset('client/css/seller-store.css') }}" rel="stylesheet">
    @endif
    @stack('styles')
    {{-- embed modal css --}}
    <link href="{{ asset('client/css/modal.css') }}" rel="stylesheet">
    <title>@yield('page-title', 'Cupo — Sàn Thương Mại Điện Tử')</title>
</head>

<body>
    <header>
        @include('layouts.client.header')
    </header>
    <main>
        @yield('content')
    </main>
    <footer>
        @include('layouts.client.footer')
    </footer>

    @auth
        <form method="POST" action="{{ route('logout') }}" id="logoutForm">
            @csrf
        </form>

        <x-modal name="logoutModal" title="Xác nhận đăng xuất" max-width="sm">
            <p class="mb-0">Bạn có chắc chắn muốn đăng xuất không?</p>

            <x-slot name="footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" form="logoutForm" class="btn btn-danger">
                    <i class="fa-solid fa-right-from-bracket me-2"></i>Đăng xuất
                </button>
            </x-slot>
        </x-modal>
    @endauth

    {{-- embed bootstrap js --}}
    <script src="{{ asset('client/js/bootstrap.bundle.min.js') }}"></script>
    {{-- embed page js --}}
    @if (request()->routeIs('profile.*'))
        <script src="{{ asset('client/js/profile.js') }}"></script>
        <script src="{{ asset('client/js/seller-register.js') }}"></script>
    @elseif (request()->routeIs('home'))
        <script src="{{ asset('client/js/home.js') }}"></script>
    @elseif (request()->routeIs('promotions'))
        <script src="{{ asset('client/js/promotions.js') }}"></script>
    @elseif (request()->routeIs('categories.*'))
        <script src="{{ asset('client/js/category-show.js') }}"></script>
    @elseif (request()->routeIs('products.*'))
        <script src="{{ asset('client/js/product-show.js') }}"></script>
    @elseif (request()->routeIs('seller.shop'))
        <script src="{{ asset('client/js/seller-store.js') }}"></script>
    @endif

    {{-- embed cart js --}}
    <script src="{{ asset('client/js/cart.js') }}"></script>

    @stack('scripts')
</body>

</html>
