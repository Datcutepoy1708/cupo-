@extends('layouts.auth')

@section('content')
    <div class="auth-wrapper">
        <div class="card auth-card">
            <div class="card-body auth-card-body">
                <!-- Header -->
                <div class="auth-header">
                    <img src="{{ asset('images/cupo-icon.svg') }}" alt="Cupo" class="brand-logo">
                    <h1 class="auth-title">Đặt lại mật khẩu</h1>
                    <p class="auth-subtitle">Vui lòng nhập mật khẩu mới cho tài khoản của bạn.</p>
                </div>

                <!-- Form -->
                <form method="POST" action="{{ route('password.store') }}">
                    @csrf

                    <!-- Password Reset Token -->
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <!-- Email Address -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Địa chỉ Email</label>
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                            name="email" value="{{ old('email', $request->email) }}" required autofocus
                            autocomplete="username" placeholder="nhapemail@example.com">
                        @error('email')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label">Mật khẩu mới</label>
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
                        <label for="password_confirmation" class="form-label">Xác nhận mật khẩu mới</label>
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
                        Đặt lại mật khẩu
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
