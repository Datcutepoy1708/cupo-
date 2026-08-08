<?php

use App\Http\Controllers\Seller\SellerOrderController;
use App\Http\Controllers\Seller\SellerProductController;
use App\Http\Controllers\Seller\SellerProfileController;
use App\Http\Controllers\Seller\SellerRegistrationController;
use Illuminate\Support\Facades\Route;

Route::prefix('seller')->group(function () {

    // Route trả về view cửa hàng của seller
    Route::middleware(['auth', 'role:seller'])
        ->get('/shop', [SellerProfileController::class, 'index'])
        ->name('seller.shop');

    // 1. Route Đăng ký làm Người bán (Cho phép cả Customer & Seller)
    Route::middleware(['auth', 'role:customer,seller'])->group(function () {
        Route::post('/register', [SellerRegistrationController::class, 'store'])->name('seller.register.store');
    });

    // 2. Route Trang thông báo chờ Admin duyệt
    Route::middleware(['auth', 'role:seller'])->group(function () {
        Route::get('/pending-approval', [SellerRegistrationController::class, 'pendingApproval'])->name('seller.pending-approval');
    });

    // 3. Kênh Người Bán (Bắt buộc đã được Admin DUYỆT - approved)
    Route::middleware(['auth', 'role:seller', 'seller.approved'])->name('seller.')->group(function () {

        Route::get('/dashboard', function () {
            return view('seller.dashboard');
        })->name('dashboard');

        // API RESTful Seller Quản lý Sản phẩm (/seller/products)
        Route::apiResource('products', SellerProductController::class);

        // API Seller Quản lý Đơn hàng
        Route::get('/orders', [SellerOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{sellerOrder}', [SellerOrderController::class, 'show'])->name('orders.show');
        Route::patch('/orders/{sellerOrder}/status', [SellerOrderController::class, 'updateStatus'])->name('orders.update-status');
    });
});