<?php

use App\Http\Controllers\Seller\SellerRegistrationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:customer,seller'])->group(function () {
    Route::post('/seller/register', [SellerRegistrationController::class, 'store'])->name('seller.register.store');
});

Route::middleware(['auth', 'role:seller'])->group(function () {
    Route::get('/seller/pending-approval', [SellerRegistrationController::class, 'pendingApproval'])->name('seller.pending-approval');
});

Route::middleware(['auth', 'role:seller', 'seller.approved'])->prefix('seller')->name('seller.')->group(function () {
    Route::get('/dashboard', function () {
        return view('seller.dashboard');
    })->name('dashboard');
});
