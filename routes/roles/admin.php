<?php

use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminSellerController;
use Illuminate\Support\Facades\Route;

// Trang đăng nhập ẩn dành riêng cho Admin (Rule 19: throttle chống brute-force)
Route::middleware(['guest', 'throttle:5,1'])->prefix('quan_tri_vien_cupo_1708')->name('admin.')->group(function () {
    Route::get('/login', [AdminLoginController::class, 'create'])->name('login');
    Route::post('/login', [AdminLoginController::class, 'store']);
});

// Đăng xuất Admin (yêu cầu đã đăng nhập)
Route::middleware('auth')->post('/quan_tri_vien_cupo_1708/logout', [AdminLoginController::class, 'destroy'])->name('admin.logout');

// Khu vực quản trị Admin (yêu cầu đăng nhập + role:admin)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    Route::get('/sellers', [AdminSellerController::class, 'index'])->name('sellers.index');
    Route::patch('/sellers/{sellerProfile}/approve', [AdminSellerController::class, 'approve'])->name('sellers.approve');
    Route::patch('/sellers/{sellerProfile}/reject', [AdminSellerController::class, 'reject'])->name('sellers.reject');
    Route::patch('/sellers/{sellerProfile}/block', [AdminSellerController::class, 'block'])->name('sellers.block');

    Route::get('/categories/data', [AdminCategoryController::class, 'data'])->name('categories.data');
    Route::apiResource('categories', AdminCategoryController::class);

    Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
    Route::patch('/products/{product}/approve', [AdminProductController::class, 'approve'])->name('products.approve');
    Route::patch('/products/{product}/reject', [AdminProductController::class, 'reject'])->name('products.reject');
});
