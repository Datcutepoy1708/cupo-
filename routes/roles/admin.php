<?php

use App\Http\Controllers\Admin\AdminBannerController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminSellerController;
use App\Http\Controllers\Admin\AdminUploadController;
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
    Route::get('/sellers/export', [AdminSellerController::class, 'export'])->name('sellers.export');
    Route::post('/sellers/bulk-approve', [AdminSellerController::class, 'bulkApprove'])->name('sellers.bulk-approve');
    Route::patch('/sellers/{sellerProfile}/approve', [AdminSellerController::class, 'approve'])->name('sellers.approve');
    Route::patch('/sellers/{sellerProfile}/reject', [AdminSellerController::class, 'reject'])->name('sellers.reject');
    Route::patch('/sellers/{sellerProfile}/block', [AdminSellerController::class, 'block'])->name('sellers.block');

    Route::get('/categories/data', [AdminCategoryController::class, 'data'])->name('categories.data');
    Route::get('/categories/export', [AdminCategoryController::class, 'export'])->name('categories.export');
    Route::post('/categories/bulk-status', [AdminCategoryController::class, 'bulkStatus'])->name('categories.bulk-status');
    Route::post('/categories/bulk-delete', [AdminCategoryController::class, 'bulkDestroy'])->name('categories.bulk-delete');
    Route::apiResource('categories', AdminCategoryController::class);

    Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
    Route::get('/products/export', [AdminProductController::class, 'export'])->name('products.export');
    Route::post('/products/bulk-approve', [AdminProductController::class, 'bulkApprove'])->name('products.bulk-approve');
    Route::post('/products/bulk-reject', [AdminProductController::class, 'bulkReject'])->name('products.bulk-reject');
    Route::patch('/products/{product}/approve', [AdminProductController::class, 'approve'])->name('products.approve');
    Route::patch('/products/{product}/reject', [AdminProductController::class, 'reject'])->name('products.reject');

    Route::get('/banners', [AdminBannerController::class, 'index'])->name('banners.index');
    Route::post('/banners/bulk-status', [AdminBannerController::class, 'bulkStatus'])->name('banners.bulk-status');
    Route::post('/banners/bulk-delete', [AdminBannerController::class, 'bulkDestroy'])->name('banners.bulk-delete');
    Route::apiResource('banners', AdminBannerController::class)->except(['index']);

    Route::post('/upload', [AdminUploadController::class, 'upload'])->name('upload');
});
