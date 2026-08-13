<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerVoucherController extends Controller
{
    /**
     * Claim/Save a coupon to customer's wallet.
     */
    public function save(Request $request, Coupon $coupon): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để lưu mã giảm giá.',
                'require_login' => true,
            ], 401);
        }

        if (! $coupon->isAvailable()) {
            return response()->json([
                'success' => false,
                'message' => 'Mã giảm giá này đã hết lượt dùng hoặc đã hết hạn.',
            ], 422);
        }

        $alreadySaved = $user->savedCoupons()->where('coupon_id', $coupon->id)->exists();

        if ($alreadySaved) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn đã lưu mã giảm giá này vào ví rồi.',
                'already_saved' => true,
            ], 409);
        }

        $user->savedCoupons()->attach($coupon->id, [
            'status' => 'saved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $ownedCount = $user->savedCoupons()
            ->wherePivot('status', 'saved')
            ->count();

        return response()->json([
            'success' => true,
            'message' => 'Lưu mã giảm giá vào ví thành công!',
            'coupon_id' => $coupon->id,
            'owned_count' => $ownedCount,
        ]);
    }
}
