<?php

use Illuminate\Support\Facades\Route;

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

require __DIR__.'/auth.php';
