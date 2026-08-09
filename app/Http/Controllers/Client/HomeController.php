<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Trang chủ cho người mua (Client Homepage).
     * Đồng bộ nạp Banner active từ DB theo từng vị trí (homepage_hero, homepage_mid, sidebar).
     */
    public function index(): View
    {
        $heroBanners = Banner::active()->atPosition('homepage_hero')->get();
        $midBanners = Banner::active()->atPosition('homepage_mid')->get();
        $sideBanners = Banner::active()->atPosition('sidebar')->get();

        $featuredCategories = Category::withCount('sellerProfiles')
            ->whereNull('parent_id')
            ->where('status', true)
            ->take(8)
            ->get();

        $flashSaleProducts = Product::with(['seller.sellerProfile', 'category'])
            ->where('status', 'approved')
            ->latest()
            ->take(6)
            ->get();

        return view('client.home.index', compact(
            'heroBanners',
            'midBanners',
            'sideBanners',
            'featuredCategories',
            'flashSaleProducts'
        ));
    }
}
