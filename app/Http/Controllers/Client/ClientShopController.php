<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\SellerProfile;
use Illuminate\Http\Request;

class ClientShopController extends Controller
{
    /**
     * Hiển thị trang Chi tiết Gian hàng / Shop Storefront
     * URL: /shops/{sellerProfile}
     */
    public function show($id, Request $request)
    {
        // Tìm SellerProfile theo ID hoặc Slug
        $shop = SellerProfile::with(['user'])
            ->where('id', $id)
            ->orWhere('slug', $id)
            ->firstOrFail();

        // 1. Thống kê sản phẩm & người theo dõi
        $sellerUserId = $shop->user_id;

        $productsQuery = Product::where('seller_id', $sellerUserId)
            ->where('status', 'approved');

        $totalProducts = (clone $productsQuery)->count();
        $followersCount = $shop->followers()->count();

        // Kiểm tra người dùng hiện tại đã theo dõi shop này chưa
        $isFollowed = auth()->check()
            ? $shop->followers()->where('user_id', auth()->id())->exists()
            : false;

        // 2. Lấy danh sách Voucher của Shop đang khả dụng
        $shopCoupons = Coupon::where('seller_id', $sellerUserId)
            ->active()
            ->where(function ($q) {
                $q->where('usage_limit', 0)
                    ->orWhereColumn('used_count', '<', 'usage_limit');
            })
            ->latest()
            ->get();

        // Danh sách ID các voucher mà người dùng hiện tại đã lưu vào ví
        $savedCouponIds = auth()->check()
            ? auth()->user()->savedCoupons()->pluck('coupons.id')->toArray()
            : [];

        // 3. Lấy từ khóa tìm kiếm trong shop & lọc sản phẩm
        $searchQuery = $request->get('q');
        $sort = $request->get('sort', 'newest');

        $products = (clone $productsQuery)
            ->when($searchQuery, function ($q) use ($searchQuery) {
                $q->where('name', 'like', "%{$searchQuery}%");
            })
            ->when($sort === 'best_selling', function ($q) {
                $q->orderBy('views_count', 'desc');
            })
            ->when($sort === 'price_asc', function ($q) {
                $q->orderBy('price', 'asc');
            })
            ->when($sort === 'price_desc', function ($q) {
                $q->orderBy('price', 'desc');
            })
            ->when($sort === 'newest', function ($q) {
                $q->latest('id');
            })
            ->paginate(12)
            ->withQueryString();

        // 4. Sản phẩm bán chạy (Top Best Sellers cho mục GỢI Ý & SẢN PHẨM BÁN CHẠY)
        $topProducts = (clone $productsQuery)
            ->orderBy('views_count', 'desc')
            ->take(6)
            ->get();

        // 5. Danh mục sản phẩm mà shop có kinh doanh
        $shopCategories = Product::where('seller_id', $sellerUserId)
            ->where('status', 'approved')
            ->with('category')
            ->get()
            ->pluck('category')
            ->filter()
            ->unique('id')
            ->values();

        return view('client.shops.show', compact(
            'shop',
            'totalProducts',
            'followersCount',
            'isFollowed',
            'shopCoupons',
            'savedCouponIds',
            'products',
            'topProducts',
            'shopCategories',
            'searchQuery',
            'sort'
        ));
    }
}
