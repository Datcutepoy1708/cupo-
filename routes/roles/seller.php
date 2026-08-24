<?php

use App\Http\Controllers\Seller\SellerFlashSaleRegistrationController;
use App\Http\Controllers\Seller\SellerOrderController;
use App\Http\Controllers\Seller\SellerProductController;
use App\Http\Controllers\Seller\SellerProfileController;
use App\Http\Controllers\Seller\SellerRegistrationController;
use App\Http\Controllers\Seller\SellerReviewController;
use Illuminate\Support\Facades\Route;

Route::prefix('seller')->group(function () {

    // Route tra ve view cua hang cua seller
    Route::middleware(['auth', 'role:seller'])
        ->get('/shop', [SellerProfileController::class, 'index'])
        ->name('seller.shop');

    // 1. Route Dang ky lam Nguoi ban (Cho phep ca Customer & Seller)
    Route::middleware(['auth', 'role:customer,seller'])->group(function () {
        Route::get('/register', [SellerRegistrationController::class, 'create'])->name('seller.register');
        Route::post('/register', [SellerRegistrationController::class, 'store'])->name('seller.register.store');
    });

    // 2. Route Trang thong bao cho Admin duyet
    Route::middleware(['auth', 'role:seller'])->group(function () {
        Route::get('/pending-approval', [SellerRegistrationController::class, 'pendingApproval'])->name('seller.pending-approval');
    });

    // 3. Kenh Nguoi Ban (Bat buoc da duoc Admin DUYET - approved)
    Route::middleware(['auth', 'role:seller', 'seller.approved'])->name('seller.')->group(function () {

        Route::get('/dashboard', function () {
            return view('seller.dashboard');
        })->name('dashboard');

        // Route dang ky bo sung nganh hang kinh doanh
        Route::post('/categories/request', [SellerProfileController::class, 'requestCategories'])->name('categories.request');

        // API RESTful Seller Quan ly San pham (/seller/products)
        Route::apiResource('products', SellerProductController::class);

        // API Seller Quan ly Don hang
        Route::get('/orders', [SellerOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{sellerOrder}', [SellerOrderController::class, 'show'])->name('orders.show');
        Route::patch('/orders/{sellerOrder}/status', [SellerOrderController::class, 'updateStatus'])->name('orders.update-status');

        // Flash Sale Registration — Seller dang ky san pham vao phien Flash Sale
        Route::prefix('flash-sale-registrations')->name('flash-sale-registrations.')->group(function () {
            Route::get('/', [SellerFlashSaleRegistrationController::class, 'index'])->name('index');
            Route::post('/', [SellerFlashSaleRegistrationController::class, 'store'])->name('store');
            Route::get('/mine', [SellerFlashSaleRegistrationController::class, 'myRegistrations'])->name('mine');
            Route::delete('/{registration}', [SellerFlashSaleRegistrationController::class, 'destroy'])->name('destroy');
        });

        // Reviews — Seller Quan ly & Phan hoi Danh gia
        Route::prefix('reviews')->name('reviews.')->group(function () {
            Route::get('/', [SellerReviewController::class, 'index'])->name('index');
            Route::post('/{review}/reply', [SellerReviewController::class, 'reply'])->name('reply');
            Route::post('/{review}/report', [SellerReviewController::class, 'report'])->name('report');
        });
    });
});
