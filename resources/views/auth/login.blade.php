@extends('layouts.auth')

@section('content')
    <div class="auth-wrapper">
        <div class="card auth-card">
            <div class="card-body auth-card-body">
                <!-- Header -->
                <div class="auth-header">
                    <img src="{{ asset('images/cupo-icon.svg') }}" alt="Cupo" class="brand-logo">
                    <h1 class="auth-title">Đăng nhập</h1>
                    <p class="auth-subtitle">Chào mừng bạn quay trở lại với Cupo!</p>
                </div>

                <!-- Session Status Alert -->
                @if (session('status'))
                    <div class="alert alert-success mb-3" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger mb-3" role="alert">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <!-- Form -->
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Địa chỉ Email</label>
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                            name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                            placeholder="nhapemail@example.com">
                        @error('email')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="password" class="form-label mb-0">Mật khẩu</label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="auth-link">
                                    Quên mật khẩu?
                                </a>
                            @endif
                        </div>
                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                            name="password" required autocomplete="current-password" placeholder="••••••••">
                        @error('password')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Remember Me -->
                    <div class="mb-4 form-check">
                        <input type="checkbox" class="form-check-input" id="remember_me" name="remember">
                        <label class="form-check-label" for="remember_me">Ghi nhớ đăng nhập</label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-auth-primary">
                        Đăng nhập
                    </button>

                    <!-- Footer Link -->
                    <div class="auth-footer-text">
                        Chưa có tài khoản?
                        <a href="{{ route('register') }}" class="auth-link ms-1">Đăng ký ngay</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
