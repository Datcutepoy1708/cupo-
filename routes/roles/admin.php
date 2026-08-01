<?php

use App\Http\Controllers\Admin\AdminSellerController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    Route::get('/sellers', [AdminSellerController::class, 'index'])->name('sellers.index');
    Route::patch('/sellers/{sellerProfile}/approve', [AdminSellerController::class, 'approve'])->name('sellers.approve');
    Route::patch('/sellers/{sellerProfile}/reject', [AdminSellerController::class, 'reject'])->name('sellers.reject');
    Route::patch('/sellers/{sellerProfile}/block', [AdminSellerController::class, 'block'])->name('sellers.block');
});
