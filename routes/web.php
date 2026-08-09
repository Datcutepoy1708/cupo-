<?php

use App\Http\Controllers\Client\HomeController;
use Illuminate\Support\Facades\Route;

// Storefront Public Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/home', [HomeController::class, 'index']);

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
