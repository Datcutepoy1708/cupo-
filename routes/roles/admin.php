<?php

use App\Http\Controllers\Admin\AdminBannerController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminCouponController;
use App\Http\Controllers\Admin\AdminFlashSaleController;
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminRoleController;
use App\Http\Controllers\Admin\AdminSellerController;
use App\Http\Controllers\Admin\AdminSettingController;
use App\Http\Controllers\Admin\AdminStaffController;
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

    // Quản lý Mã giảm giá (Coupons / Vouchers)
    Route::get('/coupons', [AdminCouponController::class, 'index'])->name('coupons.index');
    Route::post('/coupons/bulk-status', [AdminCouponController::class, 'bulkStatus'])->name('coupons.bulk-status');
    Route::post('/coupons/bulk-delete', [AdminCouponController::class, 'bulkDestroy'])->name('coupons.bulk-delete');
    Route::patch('/coupons/{coupon}/toggle-status', [AdminCouponController::class, 'toggleStatus'])->name('coupons.toggle-status');
    Route::apiResource('coupons', AdminCouponController::class)->except(['index']);

    Route::post('/upload', [AdminUploadController::class, 'upload'])->name('upload');

    // Quản lý Phân Quyền & Chức Vụ (RBAC)
    Route::get('/roles/data', [AdminRoleController::class, 'data'])->name('roles.data');
    Route::post('/roles/assign-user', [AdminRoleController::class, 'assignUserRole'])->name('roles.assign-user');
    Route::apiResource('roles', AdminRoleController::class)->where(['role' => '[0-9]+']);

    // Quản lý Nhân viên Admin (Staff)
    Route::post('/staff', [AdminStaffController::class, 'store'])->name('staff.store');
    Route::put('/staff/{user}', [AdminStaffController::class, 'update'])->name('staff.update');
    Route::post('/staff/{user}/reset-password', [AdminStaffController::class, 'resetPassword'])->name('staff.reset-password');
    Route::delete('/staff/{user}', [AdminStaffController::class, 'destroy'])->name('staff.destroy');

    // Quan ly Don Hang
    Route::prefix('orders')->name('orders.')->group(function () {
        // /export truoc /{order} de tranh conflict model binding
        Route::get('/export', [AdminOrderController::class, 'export'])->name('export');
        Route::get('/', [AdminOrderController::class, 'index'])->name('index');
        Route::get('/{order}', [AdminOrderController::class, 'show'])->name('show');
        Route::patch(
            '/seller-orders/{sellerOrder}/status',
            [AdminOrderController::class, 'updateSellerOrderStatus']
        )->name('seller-orders.update-status');
    });

    // Quan ly Flash Sale
    Route::prefix('flash-sales')->name('flash-sales.')->group(function () {
        Route::get('/', [AdminFlashSaleController::class, 'index'])->name('index');
        Route::post('/', [AdminFlashSaleController::class, 'store'])->name('store');
        Route::put('/{flashSale}', [AdminFlashSaleController::class, 'update'])->name('update');
        Route::delete('/{flashSale}', [AdminFlashSaleController::class, 'destroy'])->name('destroy');
        Route::patch('/{flashSale}/toggle', [AdminFlashSaleController::class, 'toggleStatus'])->name('toggle');
        Route::put('/{flashSale}/products', [AdminFlashSaleController::class, 'syncProducts'])->name('products.sync');

        // Quan ly Dang ky Flash Sale (Seller -> Admin duyet)
        Route::get('/{flashSale}/registrations', [AdminFlashSaleController::class, 'registrations'])->name('registrations.index');
        Route::patch('/registrations/{registration}/approve', [AdminFlashSaleController::class, 'approveRegistration'])->name('registrations.approve');
        Route::patch('/registrations/{registration}/reject', [AdminFlashSaleController::class, 'rejectRegistration'])->name('registrations.reject');
    });

    // Quan ly Cai dat he thong (Settings)
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [AdminSettingController::class, 'index'])->name('index');
        Route::post('/update', [AdminSettingController::class, 'update'])->name('update');
        Route::post('/test-mail', [AdminSettingController::class, 'testMail'])->name('test-mail');
    });
});
