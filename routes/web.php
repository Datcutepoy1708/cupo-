<?php

use App\Http\Controllers\Client\ClientShopController;
use App\Models\Banner;
use App\Models\Category;
use App\Models\FlashSale;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

// Storefront Public Routes
Route::get('/', function () {
    $featuredCategories = Category::whereNull('parent_id')->with('children')->get();
    $flashSale = FlashSale::where('status', true)->first();
    $heroBanners = Banner::all();

    return view('client.home', compact('featuredCategories', 'flashSale', 'heroBanners'));
})->name('home');

Route::get('/home', function () {
    return redirect()->route('home');
});

// Storefront Shop Routes
Route::get('/shops/{sellerProfile}', [ClientShopController::class, 'show'])->name('shops.show');

// Storefront Product Routes
Route::get('/products/{slug}', function ($slug) {
    $product = Product::where('slug', $slug)->with(['images', 'variants', 'category', 'seller.sellerProfile'])->firstOrFail();

    return view('client.products.show', compact('product'));
})->name('products.show');

Route::post('/products/{product}/like', function () {
    return response()->json(['success' => true]);
})->name('products.like');

// Storefront Category Routes
Route::get('/categories', function () {
    $categories = Category::whereNull('parent_id')->with(['children.products', 'products'])->get();

    return view('client.categories.index', compact('categories'));
})->name('categories.index');

Route::get('/categories/{slug}', function ($slug) {
    $category = Category::where('slug', $slug)->firstOrFail();
    $products = Product::where('category_id', $category->id)->where('status', 'approved')->paginate(12);

    return view('client.categories.show', compact('category', 'products'));
})->name('categories.show');

Route::get('/promotions', function () {
    return view('client.promotions');
})->name('promotions');

Route::get('/help', function () {
    return view('client.help');
})->name('help');

// Breeze Authenticated routes
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/auth.php';
require __DIR__.'/roles/customer.php';
require __DIR__.'/roles/seller.php';
require __DIR__.'/roles/admin.php';
