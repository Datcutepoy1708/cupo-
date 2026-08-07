<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Quan tri — Cupo</title>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Roboto:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    {{-- Bootstrap --}}
    <link href="{{ asset('client/css/bootstrap.min.css') }}" rel="stylesheet">

    <style>
        :root {
            --cupo-red:       #c62828;
            --cupo-red-dark:  #b71c1c;
            --cupo-red-light: #fdecea;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: "Roboto", sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-box {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 24px 64px rgba(0, 0, 0, 0.35);
            width: 100%;
            max-width: 420px;
            overflow: hidden;
        }

        /* Header box */
        .login-header {
            background: linear-gradient(135deg, var(--cupo-red) 0%, var(--cupo-red-dark) 100%);
            padding: 32px 36px 28px;
            text-align: center;
        }

        .login-header .brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 8px;
        }

        .login-header .brand img {
            width: 36px;
            height: 36px;
            object-fit: contain;
            filter: brightness(0) invert(1);
        }

        .login-header .brand-name {
            font-family: "Pacifico", cursive;
            font-size: 26px;
            color: #ffffff;
        }

        .login-header .subtitle {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.75);
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        /* Body form */
        .login-body {
            padding: 32px 36px 36px;
        }

        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: #495057;
            margin-bottom: 6px;
        }

        .form-control {
            border: 1.5px solid #dee2e6;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 14px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-control:focus {
            border-color: var(--cupo-red);
            box-shadow: 0 0 0 3px rgba(198, 40, 40, 0.12);
            outline: none;
        }

        .input-group-text {
            border: 1.5px solid #dee2e6;
            border-radius: 8px 0 0 8px;
            background: #f8f9fa;
            color: #6c757d;
            border-right: none;
            padding: 10px 12px;
        }

        .input-group .form-control {
            border-radius: 0 8px 8px 0;
            border-left: none;
        }

        .input-group:focus-within .input-group-text {
            border-color: var(--cupo-red);
        }

        .btn-admin-login {
            width: 100%;
            background: linear-gradient(135deg, var(--cupo-red) 0%, var(--cupo-red-dark) 100%);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s ease, transform 0.2s ease;
            margin-top: 4px;
        }

        .btn-admin-login:hover {
            opacity: 0.92;
            transform: translateY(-1px);
        }

        .btn-admin-login:active {
            transform: translateY(0);
        }

        /* Error */
        .alert-danger {
            background: var(--cupo-red-light);
            border: 1px solid #f5c6c6;
            border-radius: 8px;
            color: var(--cupo-red-dark);
            font-size: 13px;
            padding: 10px 14px;
            margin-bottom: 20px;
        }

        /* Footer link quay lai */
        .login-footer {
            text-align: center;
            padding: 16px;
            border-top: 1px solid #f0f0f0;
            font-size: 12px;
            color: #adb5bd;
        }

        .login-footer a {
            color: var(--cupo-red);
            text-decoration: none;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        /* Security badge */
        .security-note {
            display: flex;
            align-items: center;
            gap: 6px;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 8px 12px;
            margin-top: 16px;
            font-size: 11px;
            color: #6c757d;
        }

        .security-note i {
            color: #2e7d32;
            font-size: 13px;
        }
    </style>
</head>

<body>

    <div class="login-box">

        {{-- Header --}}
        <div class="login-header">
            <div class="brand">
                <img src="{{ asset('images/cupo-icon-transparent.svg') }}" alt="Cupo">
                <span class="brand-name">Cupo</span>
            </div>
            <p class="subtitle">Cong quan tri he thong</p>
        </div>

        {{-- Form --}}
        <div class="login-body">

            {{-- Loi dang nhap --}}
            @if ($errors->any())
                <div class="alert-danger">
                    <i class="fa-solid fa-circle-exclamation me-1"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            @if (session('status'))
                <div class="alert-danger" style="background:#e8f5e9; border-color:#c8e6c9; color:#2e7d32;">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ url('/quan_tri_vien_cupo_1708/login') }}">
                @csrf

                {{-- Email --}}
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

                {{-- Mat khau --}}
                <div class="mb-4">
                    <label for="admin_password" class="form-label">Mat khau</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input type="password"
                               id="admin_password"
                               name="password"
                               class="form-control"
                               placeholder="Nhap mat khau"
                               autocomplete="current-password"
                               required>
                    </div>
                </div>

                <button type="submit" class="btn-admin-login">
                    <i class="fa-solid fa-right-to-bracket me-2"></i>
                    Dang nhap quan tri
                </button>

                {{-- Security note --}}
                <div class="security-note">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span>Ket noi duoc bao mat. Phien dang nhap se het han sau 2 gio.</span>
                </div>

            </form>
        </div>

        {{-- Footer --}}
        <div class="login-footer">
            <a href="{{ route('home') }}">
                <i class="fa-solid fa-arrow-left me-1"></i>Quay ve trang chu
            </a>
        </div>

    </div>

    <script src="{{ asset('client/js/bootstrap.bundle.min.js') }}"></script>

</body>

</html>
