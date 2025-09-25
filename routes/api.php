<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ShopController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Api\AuthController;

Route::post('auth/register', [AuthController::class,'register']);
Route::post('auth/login', [AuthController::class,'login']);
Route::post('auth/forgot-password', [AuthController::class,'forgotPassword']);
Route::post('auth/reset-password', [AuthController::class,'resetPassword']);
Route::get('auth/email/verify/{id}/{hash}', [AuthController::class,'verifyEmail'])->name('verification.verify');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('  ', function (\Illuminate\Http\Request $request){
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Déconnexion réussie']);
    });

    Route::get('profile', [AuthController::class,'profile']);

    // Shops
    Route::get('shops', [ShopController::class, 'index']);
    Route::post('shops/create', [ShopController::class, 'store']);
    Route::get('shops/{id}', [ShopController::class, 'show']);
    Route::put('shops/{id}/update', [ShopController::class, 'update']);
    Route::put('shops/{id}/update-template', [ShopController::class, 'updateTemplate']);
    Route::post('shops/{id}/desactivate', [ShopController::class, 'deactivate']);
    Route::post('shops/{id}/reactivate', [ShopController::class, 'reactivate']);
    Route::get('/shops/{slug}', [ShopController::class, 'showPublic']);

    // Products
    Route::get('products', [ProductController::class, 'index']);
    Route::post('products', [ProductController::class, 'store']);
    Route::get('products/{id}', [ProductController::class, 'show']);
    Route::put('products/{id}', [ProductController::class, 'update']);
    Route::delete('products/{id}', [ProductController::class, 'destroy']);

    // Categories
    Route::get('categories', [CategoryController::class, 'index']);
    Route::post('categories', [CategoryController::class, 'store']);
    Route::get('categories/{id}', [CategoryController::class, 'show']);

});
