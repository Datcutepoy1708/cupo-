<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\SellerProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShopFollowController extends Controller
{
    /**
     * Toggle theo dõi / bỏ theo dõi gian hàng
     * URL: POST /shops/{sellerProfile}/follow
     */
    public function toggle(Request $request, SellerProfile $sellerProfile): JsonResponse
    {
        $user = $request->user();

        // Không tự follow gian hàng của chính mình
        if ($sellerProfile->user_id === $user->id) {
            return response()->json([
                'message' => 'Bạn không thể tự theo dõi gian hàng của chính mình.',
            ], 422);
        }

        $isFollowed = $user->followedShops()->where('seller_profile_id', $sellerProfile->id)->exists();

        if ($isFollowed) {
            $user->followedShops()->detach($sellerProfile->id);
            $followed = false;
            $message = 'Đã bỏ theo dõi gian hàng.';
        } else {
            $user->followedShops()->attach($sellerProfile->id);
            $followed = true;
            $message = 'Đã theo dõi gian hàng thành công!';
        }

        $followersCount = $sellerProfile->followers()->count();

        return response()->json([
            'message' => $message,
            'is_followed' => $followed,
            'followers_count' => $followersCount,
        ]);
    }

    /**
     * Danh sách gian hàng mà customer đang theo dõi
     * URL: GET /customer/followed-shops
     */
    public function index(Request $request): JsonResponse
    {
        $shops = $request->user()
            ->followedShops()
            ->with(['categories'])
            ->withCount('followers')
            ->latest('shop_follows.followed_at')
            ->paginate(12);

        return response()->json($shops);
    }
}
