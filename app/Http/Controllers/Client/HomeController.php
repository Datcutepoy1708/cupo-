<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
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
        $heroBanners = collect(Cache::store('redis')->remember('banners:homepage_hero', 3600, function () {
            return Banner::active()->atPosition('homepage_hero')->get()->toArray();
        }))->map(fn ($item) => (object) $item);

        $midBanners = collect(Cache::store('redis')->remember('banners:homepage_mid', 3600, function () {
            return Banner::active()->atPosition('homepage_mid')->get()->toArray();
        }))->map(fn ($item) => (object) $item);

        $sideBanners = collect(Cache::store('redis')->remember('banners:sidebar', 3600, function () {
            return Banner::active()->atPosition('sidebar')->get()->toArray();
        }))->map(fn ($item) => (object) $item);

        // 2. Redis Cache cho Cây Danh Mục Nổi Bật (TTL 24h)
        $featuredCategories = collect(Cache::store('redis')->remember('categories:tree', 86400, function () {
            return Category::with(['children' => function ($q) {
                $q->where('status', true);
            }])
                ->withCount(['sellerProfiles', 'children', 'products'])
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

        // 3. Redis Cache cho Sản Phẩm Flash Sale & Mới nhất
        $flashSaleProducts = collect(Cache::store('redis')->remember('products:flash_sale', 600, function () {
            return Product::with(['seller.sellerProfile', 'category'])
                ->where('status', 'approved')
                ->latest()
                ->take(8)
                ->get()
                ->toArray();
        }))->map(function ($p) {
            $pObj = (object) $p;
            $pObj->seller = (object) ($p['seller'] ?? []);
            if (isset($p['seller']['seller_profile'])) {
                $pObj->seller->sellerProfile = (object) $p['seller']['seller_profile'];
            }
            $pObj->category = (object) ($p['category'] ?? []);

            return $pObj;
        });

        $latestProducts = collect(Cache::store('redis')->remember('products:latest', 600, function () {
            return Product::with(['seller.sellerProfile', 'category'])
                ->where('status', 'approved')
                ->latest()
                ->take(16)
                ->get()
                ->toArray();
        }))->map(function ($p) {
            $pObj = (object) $p;
            $pObj->seller = (object) ($p['seller'] ?? []);
            if (isset($p['seller']['seller_profile'])) {
                $pObj->seller->sellerProfile = (object) $p['seller']['seller_profile'];
            }
            $pObj->category = (object) ($p['category'] ?? []);

            return $pObj;
        });

        return view('client.home.index', compact(
            'heroBanners',
            'midBanners',
            'sideBanners',
            'featuredCategories',
            'flashSaleProducts',
            'latestProducts'
        ));
    }
}
