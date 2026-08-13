<?php

<<<<<<< feature/be-updated
use App\Http\Controllers\CartController;
=======
use App\Http\Controllers\Client\AddressController;
use App\Http\Controllers\Client\CartController;
use App\Http\Controllers\Client\CheckoutController;
use App\Http\Controllers\Client\CustomerOrderController;
use App\Http\Controllers\Client\CustomerVoucherController;
use App\Http\Controllers\Client\ProductReviewController;
use App\Http\Controllers\Client\ShopFollowController;
>>>>>>> local
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
<<<<<<< feature/be-updated
=======

    // 2. RESTful Quản lý Giỏ hàng (Cart)
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('index');
        Route::post('/add', [CartController::class, 'store'])->name('store');
        Route::put('/items/{cartItem}', [CartController::class, 'update'])->name('update');
        Route::delete('/items/{cartItem}', [CartController::class, 'destroy'])->name('destroy');
        Route::delete('/clear', [CartController::class, 'clear'])->name('clear');
    });

    // API Đặt hàng & Checkout Tách Đơn
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::put('/reviews/{review}', [ProductReviewController::class, 'update'])->name('products.reviews.update');
    Route::delete('/reviews/{review}', [ProductReviewController::class, 'destroy'])->name('products.reviews.destroy');

    // API Lịch sử Đơn hàng cá nhân
    Route::get('/customer/orders', [CustomerOrderController::class, 'index'])->name('customer.orders.index');
    Route::get('/customer/orders/{order}', [CustomerOrderController::class, 'show'])->name('customer.orders.show');

    // API Đánh giá Sản phẩm
    Route::post('/products/{product}/reviews', [ProductReviewController::class, 'store'])->name('products.reviews.store');

    // Quản lý Sổ địa chỉ
    Route::post('/addresses', [AddressController::class, 'store'])->name('addresses.store');
    Route::put('/addresses/{address}', [AddressController::class, 'update'])->name('addresses.update');
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy'])->name('addresses.destroy');
    Route::patch('/addresses/{address}/set-default', [AddressController::class, 'setDefault'])->name('addresses.set-default');

    // Theo dõi Gian hàng (Shop Follow)
    Route::post('/shops/{sellerProfile}/follow', [ShopFollowController::class, 'toggle'])->name('shops.follow.toggle');
    Route::get('/customer/followed-shops', [ShopFollowController::class, 'index'])->name('customer.followed-shops.index');

    // Lưu / Nhận Mã giảm giá (Customer Vouchers)
    Route::post('/customer/vouchers/{coupon}/save', [CustomerVoucherController::class, 'save'])->name('customer.vouchers.save');

>>>>>>> local
});
