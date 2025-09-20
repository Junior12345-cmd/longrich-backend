<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ShopController;

// Route::get('/', function () {
//     return ['Laravel' => app()->version()];
// });

Route::domain('{shop}.localhost')->group(function () {
    Route::get('/', [ShopController::class, 'showBySubdomain']);
});


require __DIR__.'/auth.php';
