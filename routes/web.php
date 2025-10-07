<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ShopController;

// Route::get('/', function () {
//     return ['Laravel' => app()->version()];
// });


Route::get('/{any}', function () {
    return view('app'); // On servira React
})->where('any', '.*');

// Route::domain('shop.{slug}.' . env('FRONTEND_URL'))->group(function () {
//     Route::get('/', [ShopController::class, 'showPublic']);
// });

// Route::get('/sanctum/csrf-cookie', [\Laravel\Sanctum\Http\Controllers\CsrfCookieController::class, 'show']);


require __DIR__.'/auth.php';
