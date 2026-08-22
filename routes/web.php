<?php

use App\Http\Controllers\Client\ClientCategoryController;
use App\Http\Controllers\Client\ClientProductController;
use App\Http\Controllers\Client\ClientShopController;
use App\Http\Controllers\Client\HomeController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

// Storefront Public Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/home', [HomeController::class, 'index']);

// Storefront Shop Routes
Route::get('/shops/{sellerProfile}', [ClientShopController::class, 'show'])->name('shops.show');

// Storefront Product Routes
Route::get('/products/{slug}', [ClientProductController::class, 'show'])->name('products.show');
Route::post('/products/{product}/like', [ClientProductController::class, 'toggleLike'])->name('products.like');

// Storefront Category Routes
Route::get('/categories', [ClientCategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{slug}', [ClientCategoryController::class, 'show'])->name('categories.show');

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

// Notifications API (auth required)
Route::middleware('auth')->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
    Route::patch('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
    Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');
});

require __DIR__.'/auth.php';
require __DIR__.'/roles/customer.php';
require __DIR__.'/roles/seller.php';
require __DIR__.'/roles/admin.php';
