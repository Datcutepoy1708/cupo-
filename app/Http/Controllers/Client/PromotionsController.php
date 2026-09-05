<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\FlashSale;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PromotionsController extends Controller
{
    /**
     * Trang Khuyen mai cong khai cho Khach hang.
     * Hien thi phien Flash Sale dang live/sap toi va danh sach Coupon cong khai.
     */
    public function index(): View
    {
        // 1. Phien Flash Sale dang live (uu tien) hoac sap toi
        $flashSale = FlashSale::live()
            ->with(['products' => function ($q) {
                $q->with(['product' => function ($pq) {
                    $pq->with(['images', 'variants'])->where('status', 'approved');
                }]);
            }])
            ->first();

        $flashSaleStatus = 'live';

        if (! $flashSale) {
            $flashSale = FlashSale::upcoming()
                ->with(['products' => function ($q) {
                    $q->with(['product' => function ($pq) {
                        $pq->with(['images', 'variants'])->where('status', 'approved');
                    }]);
                }])
                ->orderBy('starts_at')
                ->first();

            $flashSaleStatus = $flashSale ? 'upcoming' : 'none';
        }

        // 2. Coupon cong khai cua nen tang (seller_id = null)
        $platformCoupons = Coupon::active()
            ->whereNull('seller_id')
            ->orderByDesc('value')
            ->get();

        // 3. Coupon cong khai cua Shop (seller_id != null)
        $shopCoupons = Coupon::active()
            ->whereNotNull('seller_id')
            ->with(['seller.sellerProfile'])
            ->orderByDesc('value')
            ->take(20)
            ->get();

        // 4. Danh sach coupon da luu cua nguoi dung (de check trang thai nut)
        $savedCouponIds = collect();
        $user = Auth::user();
        if ($user instanceof User) {
            $savedCouponIds = $user
                ->savedCoupons()
                ->wherePivot('status', 'saved')
                ->pluck('coupons.id');
        }

        // 5. San pham dang giam gia sau (Seller tu cau hinh HOAC dang trong phien Flash Sale)
        $flashSaleProductIds = array_keys(Product::getActiveFlashSaleMap());

        $deepDiscountProducts = Product::where('status', 'approved')
            ->where(function ($query) use ($flashSaleProductIds) {
                $query->where(function ($q) {
                    $q->whereNotNull('sale_price')
                        ->where('sale_price', '>', 0)
                        ->whereColumn('sale_price', '<', 'price');
                });
                if (! empty($flashSaleProductIds)) {
                    $query->orWhereIn('id', $flashSaleProductIds);
                }
            })
            ->with(['seller.sellerProfile', 'category', 'images', 'variants'])
            ->take(16)
            ->get();

        return view('client.promotions.index', compact(
            'flashSale',
            'flashSaleStatus',
            'platformCoupons',
            'shopCoupons',
            'savedCouponIds',
            'deepDiscountProducts',
        ));
    }
}