<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — Không có quyền truy cập | Cupo Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('client/css/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #0f172a;
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .error-card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 20px;
            padding: 40px 30px;
            max-width: 520px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        }
        .error-icon {
            width: 80px;
            height: 80px;
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            margin: 0 auto 20px;
        }
        .error-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 12px;
            color: #fff;
        }
        .error-desc {
            color: #94a3b8;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 25px;
        }
        .btn-home {
            background: #ef4444;
            color: #fff;
            font-weight: 600;
            padding: 10px 24px;
            border-radius: 10px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        .btn-home:hover {
            background: #dc2626;
            color: #fff;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-icon">
            <i class="fa-solid fa-shield-halved"></i>
        </div>
        <h1 class="error-title">Không có quyền truy cập</h1>
        <p class="error-desc">
            Tài khoản của bạn không được phân quyền để truy cập vào khu vực này.
            Vui lòng liên hệ <strong>Quản Trị Viên (Super Admin)</strong> nếu bạn cần được cấp thêm quyền hạn nghiệp vụ.
        </p>
        <div>
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('admin.dashboard') }}" class="btn-home">
                <i class="fa-solid fa-arrow-left"></i>
                Quay lại trang trước
            </a>
        </div>
    </div>
</body>
</html>
