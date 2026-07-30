@extends('layouts.auth')

@section('content')
    <div class="auth-wrapper">
        <div class="card auth-card">
            <div class="card-body auth-card-body">
                <!-- Header -->
                <div class="auth-header">
                    <img src="{{ asset('images/cupo-icon.svg') }}" alt="Cupo" class="brand-logo">
                    <h1 class="auth-title">Tạo tài khoản</h1>
                    <p class="auth-subtitle">Tham gia cộng đồng Cupo ngay hôm nay!</p>
                </div>

                <!-- Form -->
                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <!-- Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label">Họ và tên</label>
                        <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                            name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                            placeholder="Nguyễn Văn A">
                        @error('name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Địa chỉ Email</label>
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                            name="email" value="{{ old('email') }}" required autocomplete="username"
                            placeholder="nhapemail@example.com">
                        @error('email')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label">Mật khẩu</label>
                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                            name="password" required autocomplete="new-password" placeholder="••••••••">
                        @error('password')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">Xác nhận mật khẩu</label>
                        <input id="password_confirmation" type="password"
                            class="form-control @error('password_confirmation') is-invalid @enderror"
                            name="password_confirmation" required autocomplete="new-password" placeholder="••••••••">
                        @error('password_confirmation')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-auth-primary">
                        Đăng ký tài khoản
                    </button>

                    <!-- Footer Link -->
                    <div class="auth-footer-text">
                        Đã có tài khoản?
                        <a href="{{ route('login') }}" class="auth-link ms-1">Đăng nhập</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
