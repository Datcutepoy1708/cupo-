<?php

use App\Http\Controllers\Client\CartController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\CheckoutController;


Route::middleware('auth')->group(function () {

    // 1.  Quản lý Hồ sơ cá nhân (Profile)
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 2.  RESTful Quản lý Giỏ hàng (Cart)
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('index');
        Route::post('/add', [CartController::class, 'store'])->name('store');
        Route::put('/items/{cartItem}', [CartController::class, 'update'])->name('update');
        Route::delete('/items/{cartItem}', [CartController::class, 'destroy'])->name('destroy');
        Route::delete('/clear', [CartController::class, 'clear'])->name('clear');
    });

    // API Đặt hàng & Checkout Tách Đơn
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

});
