@extends('layouts.auth')

@section('content')
    <div class="auth-wrapper">
        <div class="card auth-card">
            <div class="card-body auth-card-body">
                <!-- Header -->
                <div class="auth-header">
                    <img src="{{ asset('images/cupo-icon.svg') }}" alt="Cupo" class="brand-logo">
                    <h1 class="auth-title">Quên mật khẩu?</h1>
                    <p class="auth-subtitle">Nhập email của bạn để nhận liên kết đặt lại mật khẩu.</p>
                </div>

                <!-- Session Status Alert -->
                @if (session('status'))
                    <div class="alert alert-success mb-3" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                <!-- Form -->
                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <!-- Email -->
                    <div class="mb-4">
                        <label for="email" class="form-label">Địa chỉ Email</label>
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                            name="email" value="{{ old('email') }}" required autofocus
                            placeholder="nhapemail@example.com">
                        @error('email')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-auth-primary">
                        Gửi liên kết đặt lại mật khẩu
                    </button>

                    <!-- Footer Link -->
                    <div class="auth-footer-text">
                        Quay lại
                        <a href="{{ route('login') }}" class="auth-link ms-1">Đăng nhập</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
