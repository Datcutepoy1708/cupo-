<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\FlashSale;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Trang chủ cho người mua (Client Homepage).
     * Nạp dữ liệu qua Redis Cache (Banners, Cây danh mục, Flash Sale & Sản phẩm mới).
     */
    public function index(): View
    {
        // 1. Redis Cache cho các vị trí Banner (lưu dạng array an toàn)
        $heroBanners = collect(Cache::remember('banners:homepage_hero', 3600, function () {
            return Banner::active()->atPosition('homepage_hero')->get()->toArray();
        }))->map(fn ($item) => (object) $item);

        $midBanners = collect(Cache::remember('banners:homepage_mid', 3600, function () {
            return Banner::active()->atPosition('homepage_mid')->get()->toArray();
        }))->map(fn ($item) => (object) $item);

        $sideBanners = collect(Cache::remember('banners:sidebar', 3600, function () {
            return Banner::active()->atPosition('sidebar')->get()->toArray();
        }))->map(fn ($item) => (object) $item);

        // 2. Redis Cache cho Cây danh mục nổi bật (cha + con)
        $featuredCategories = collect(Cache::remember('categories:featured_tree', 600, function () {
            return Category::with(['children' => fn ($q) => $q->where('status', true)])
                ->whereNull('parent_id')
                ->where('status', true)
                ->take(16)
                ->get()
                ->toArray();
        }))->map(function ($cat) {
            $catObj = (object) $cat;
            $catObj->children = collect($cat['children'] ?? [])->map(fn ($c) => (object) $c);

            return $catObj;
        });

        // 3. Phiên Flash Sale đang Live (nếu có)
        $liveFlashSale = FlashSale::live()
            ->with(['products' => function ($q) {
                $q->with(['product' => function ($pq) {
                    $pq->with(['images', 'variants'])->where('status', 'approved');
                }]);
            }])
            ->first();

        // 4. Sản phẩm gợi ý hôm nay / mới nhất (dữ liệu thật từ DB)
        $latestProducts = Product::with(['seller.sellerProfile', 'category', 'variants'])
            ->where('status', 'approved')
            ->latest()
            ->take(15)
            ->get();

        return view('client.home.index', compact(
            'heroBanners',
            'midBanners',
            'sideBanners',
            'featuredCategories',
            'liveFlashSale',
            'latestProducts'
        ));
    }
}
