<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;

// Storefront routes
Route::get('/' , function () {
    return view('client.home');
})->name('home');
Route::get('/home', function () {
    return view('client.home');
})->name('home');

Route::get('/promotions', function () {
    return view('client.promotions');
})->name('promotions');

Route::get('/help', function () {
    return view('client.help');
})->name('help');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');

// Breeze Authenticated routes
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';