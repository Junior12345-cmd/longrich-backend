<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ShopController;

// Route::get('/', function () {
//     return ['Laravel' => app()->version()];
// });

Route::domain('shop.{slug}.' . env('FRONTEND_URL'))->group(function () {
    Route::get('/', [ShopController::class, 'showPublic']);
});


require __DIR__.'/auth.php';
