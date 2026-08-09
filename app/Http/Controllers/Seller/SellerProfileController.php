<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\SellerOrder;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SellerProfileController extends Controller
{
    public function index(Request $request)
    {
        $shop = Auth::user()->sellerProfile;

        abort_if(!$shop, 404, 'Bạn chưa đăng ký gian hàng.');

        $allCategories = collect();

        if ($shop->status === 'approved') {
            $shop->product_count = Product::where('seller_id', $shop->id)->count();

            $shop->pending_orders = SellerOrder::where('seller_id', $shop->user_id)
                ->where('status', 'pending')
                ->count();

            $shop->revenue_month = SellerOrder::where('seller_id', $shop->user_id)
                ->where('status', 'completed')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('grand_total');

            $shop->recentOrders = SellerOrder::with(['order.user'])
                ->where('seller_id', $shop->user_id)
                ->latest()
                ->take(5)
                ->get();

            $shop->pendingOrdersList = SellerOrder::with(['order.user', 'items'])
                ->where('seller_id', $shop->user_id)
                ->where('status', 'pending')
                ->latest()
                ->get();

            $shop->products = Product::where('seller_id', $shop->id)
                ->latest()
                ->get();

            $shop->reviews = Review::whereHas('product', function ($q) use ($shop) {
                    $q->where('seller_id', $shop->id);
                })
                ->latest()
                ->get();

            $shop->review_count = $shop->reviews->count();
            $shop->rating = round($shop->reviews->avg('rating') ?? 0, 1);

            $shop->followers_count = $shop->followers()->count();

            // load quan hệ categories() có sẵn để blade dùng $shop->categories
            $shop->load('categories');

            // Ngành hàng chia 2 cấp: danh mục cha kèm danh mục con đang bật (status = true)
            $allCategories = Category::where('status', true)
                ->whereNull('parent_id')
                ->with(['children' => function ($q) {
                    $q->where('status', true)->orderBy('name');
                }])
                ->orderBy('name')
                ->get();
        }

        return view('client.seller-store.index', compact('shop', 'allCategories'));
    }
}