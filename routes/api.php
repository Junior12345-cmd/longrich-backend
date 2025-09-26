<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{AuthController,ShopController,ProductController,CommandeController,CategoryController,ChapitreController,FormationController};


Route::post('auth/register', [AuthController::class,'register']);
Route::post('auth/login', [AuthController::class,'login']);
Route::post('auth/forgot-password', [AuthController::class,'forgotPassword']);
Route::post('auth/reset-password', [AuthController::class,'resetPassword']);
Route::get('auth/email/verify/{id}/{hash}', [AuthController::class,'verifyEmail'])->name('verification.verify');
Route::get('/shops/{slug}', [ShopController::class, 'showPublic']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class,'logout']);

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

    // Products
    Route::get('products', [ProductController::class, 'index']);
    Route::post('products/create', [ProductController::class, 'store']);
    Route::get('products/show/{id}', [ProductController::class, 'show']);
    Route::post('products/update/{id}', [ProductController::class, 'update']);
    Route::post('products/delete/{id}', [ProductController::class, 'destroy']);
    Route::post('products/import/{id}', [ProductController::class, 'import']);

    // Categories
    Route::get('categories', [CategoryController::class, 'index']);
    Route::post('categories', [CategoryController::class, 'store']);
    Route::get('categories/{id}', [CategoryController::class, 'show']);

    // Commandes
    Route::prefix('commandes')->group(function () {
        Route::get('/', [CommandeController::class, 'index']);
        Route::post('/create', [CommandeController::class, 'store']);
        Route::get('show/{id}', [CommandeController::class, 'show']);
        Route::post('update/{id}', [CommandeController::class, 'update']);
        Route::post('delete/{id}', [CommandeController::class, 'destroy']);
    });

    //Packs
    Route::prefix('packs')->group(function () {
        Route::get('/', [PacksController::class, 'index']);
        Route::post('create', [PacksController::class, 'store']);
        Route::get('show/{id}', [PacksController::class, 'show']);
        Route::post('update/{id}', [PacksController::class, 'update']);
        Route::post('delete/{id}', [PacksController::class, 'destroy']);
    });

    //Chapitres
    Route::prefix('chapitres')->group(function () {
        Route::get('/', [ChapitreController::class, 'index']);
        Route::post('create/', [ChapitreController::class, 'store']);
        Route::get('show/{id}', [ChapitreController::class, 'show']);
        Route::post('update/{id}', [ChapitreController::class, 'update']);
        Route::post('delete/{id}', [ChapitreController::class, 'destroy']);
    });

    //Formations
    Route::prefix('formations')->group(function () {
        Route::get('/', [FormationController::class, 'index']);
        Route::post('create/', [FormationController::class, 'store']);
        Route::get('show/{id}', [FormationController::class, 'show']);
        Route::post('update/{id}', [FormationController::class, 'update']);
        Route::post('delete/{id}', [FormationController::class, 'destroy']);
    });
});
