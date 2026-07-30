@extends('layouts.auth')

@section('content')
    <div class="auth-wrapper">
        <div class="card auth-card">
            <div class="card-body auth-card-body">
                <!-- Header -->
                <div class="auth-header">
                    <img src="{{ asset('images/cupo-icon.svg') }}" alt="Cupo" class="brand-logo">
                    <h1 class="auth-title">Xác minh Email</h1>
                    <p class="auth-subtitle">Cảm ơn bạn đã đăng ký tài khoản tại Cupo!</p>
                </div>

                <div class="alert alert-info mb-4" role="alert">
                    Vui lòng kiểm tra hòm thư của bạn và nhấp vào liên kết xác minh để hoàn tất quá trình đăng ký. Nếu bạn
                    không nhận được email, chúng tôi sẵn sàng gửi lại.
                </div>

                @if (session('status') == 'verification-link-sent')
                    <div class="alert alert-success mb-4" role="alert">
                        Liên kết xác minh mới đã được gửi tới địa chỉ email bạn đã cung cấp khi đăng ký.
                    </div>
                @endif

                <div class="d-flex flex-column gap-2">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="btn btn-auth-primary">
                            Gửi lại email xác minh
                        </button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}" class="text-center mt-2">
                        @csrf
                        <button type="submit" class="btn btn-link auth-link text-decoration-none p-0">
                            Đăng xuất
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
