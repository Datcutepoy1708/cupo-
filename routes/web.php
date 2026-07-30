<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// ── Storefront (public) ───────────────────────────────────────────────────────
Route::get('/', function () {
    return view('client.home');
});
Route::get('/home', function () {
    return view('client.home');
})->name('home');

Route::get('/promotions', function () {
    return view('client.promotions');
})->name('promotions');

Route::get('/help', function () {
    return view('client.help');
})->name('help');

// ── Authenticated routes ──────────────────────────────────────────────────────
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
