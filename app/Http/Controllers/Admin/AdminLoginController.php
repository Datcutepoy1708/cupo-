<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminLoginController extends Controller
{
    /**
     * Trang đăng nhập riêng của Admin
     * URL: GET /quan_tri_vien_cupo_1708/login
     */
    public function create(): View
    {
        return view('admin.auth.login');
    }

    /**
     * Xử lý xác thực Admin
     * Sau khi đăng nhập thành công, kiểm tra role:admin
     * Nếu không phải admin -> đăng xuất ngay + trả về lỗi 403
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        if (! $request->user()->hasRole('admin')) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login')
                ->withErrors(['email' => 'Tài khoản này không có quyền truy cập trang quản trị.']);
        }

        return redirect()->route('admin.dashboard');
    }

    /**
     * Đăng xuất khỏi trang Admin
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
