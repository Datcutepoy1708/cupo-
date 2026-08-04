<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSellerApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // 1. Kiểm tra tài khoản đã đăng nhập và có role seller chưa
        if (! $user || $user->role !== 'seller') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }

            return redirect()->route('login');
        }

        // 2. Kiểm tra gian hàng sellerProfile đã được Admin duyệt (approved) hay chưa
        $sellerProfile = $user->sellerProfile;

        if (! $sellerProfile || $sellerProfile->status !== 'approved') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Gian hàng của bạn chưa được duyệt.'], 403);
            }

            return redirect()->route('seller.pending-approval');
        }

        return $next($request);
    }
}
