<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Quan tri — Cupo</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Roboto:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="{{ asset('client/css/bootstrap.min.css') }}" rel="stylesheet">

    {{-- CSS rieng cua trang dang nhap Admin --}}
    <link href="{{ asset('admin/css/admin-login.css') }}" rel="stylesheet">
</head>

<body>

    <div class="login-box">

        {{-- Header --}}
        <div class="login-header">
            <div class="brand">
                <img src="{{ asset('images/cupo-icon-transparent.svg') }}" alt="Cupo">
                <span class="brand-name">Cupo</span>
            </div>
            <p class="subtitle">Cổng quản trị hệ thống</p>
        </div>

        {{-- Form --}}
        <div class="login-body">

            @if ($errors->any())
                <div class="alert-login-error">
                    <i class="fa-solid fa-circle-exclamation me-1"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            @if (session('status'))
                <div class="alert-login-success">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ url('/quan_tri_vien_cupo_1708/login') }}">
                @csrf

                <div class="mb-3">
                    <label for="admin_email" class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fa-solid fa-envelope"></i>
                        </span>
                        <input type="email"
                               id="admin_email"
                               name="email"
                               class="form-control"
                               value="{{ old('email') }}"
                               placeholder="admin@cupo.vn"
                               autocomplete="email"
                               autofocus
                               required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="admin_password" class="form-label">Mật khẩu</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input type="password"
                               id="admin_password"
                               name="password"
                               class="form-control"
                               placeholder="Nhập mật khẩu"
                               autocomplete="current-password"
                               required>
                    </div>
                </div>

                <button type="submit" class="btn-admin-login">
                    <i class="fa-solid fa-right-to-bracket me-2"></i>
                    Đăng nhập quản trị
                </button>

                <div class="security-note">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span>Kết nối được bảo mật. Phiên đăng nhập sẽ hết hạn sau 2 giờ.</span>
                </div>
            </form>
        </div>

        <div class="login-footer">
            <a href="{{ route('home') }}">
                <i class="fa-solid fa-arrow-left me-1"></i>Quay về trang chủ
            </a>
        </div>

    </div>

    <script src="{{ asset('client/js/bootstrap.bundle.min.js') }}"></script>

</body>

</html>
