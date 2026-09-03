<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('api')->name('api.')->group(function () {
    // Reserved for token-based APIs. Chat uses session routes in routes/roles/customer.php.
});
