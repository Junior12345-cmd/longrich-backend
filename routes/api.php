<?php

use App\Models\User;
use FedaPay\FedaPay;
use GuzzleHttp\Client;
use FedaPay\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\{Shop,Formation,Commande};
use App\Http\Controllers\Api\{AuthController,ShopController,ProductController,CommandeController,CategoryController,ChapitreController,FormationController,PacksController,CountryController,PaiementController};
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\CommandeStatusMail;

Route::post('auth/register', [AuthController::class,'register']);
Route::post('auth/login', [AuthController::class,'login']);
Route::post('auth/forgot-password', [AuthController::class,'forgotPassword']);
Route::post('auth/reset-password', [AuthController::class,'resetPassword']);
Route::get('auth/email/verify/{id}/{hash}', [AuthController::class,'verifyEmail'])->name('verification.verify');
Route::get('/shops/{slug}', [ShopController::class, 'showPublic']);


//COUNTRY
Route::prefix('countries')->group(function () {
    Route::get('/', [CountryController::class, 'index']);
});

 //Packs
 Route::prefix('packs')->group(function () {
    Route::get('/', [PacksController::class, 'index']);
    Route::get('/search', [PacksController::class, 'indexSearch']);
    Route::get('show/{id}', [PacksController::class, 'show']);
});

Route::get('products/search/', [ProductController::class, 'search']);
Route::get('products/show/{id}', [ProductController::class, 'show']);
Route::get('/stockists/search', [AuthController::class, 'search']);

Route::post('/commandes/create', [CommandeController::class, 'store_commande_produit']);

// Route::post('/verify-fedapay-transaction', function (Request $request) {
//     $transactionId = $request->input('transaction_id');
//     $commandeId = $request->input('commande_id');

//     $existTransactionId = Commande::where('transaction_id', $transactionId)->first();

//     if ($existTransactionId) {
//         return response()->json([
//             'status' => 'error',
//             'message' => "Cette transaction existe déjà."
//         ], 409);
//     }

//     try {
//         FedaPay::setApiKey(env('FEDAPAY_SECRET_KEY'));
//         FedaPay::setEnvironment(env('FEDAPAY_ENV', 'sandbox'));

//         $transaction = Transaction::retrieve($transactionId);
//         $status = $transaction->status;

//         $commande = Commande::find($commandeId);
//         if ($commande) {
//             $commande->status = match ($status) {
//                 'approved' => 'approved',
//                 'declined' => 'canceled',
//                 default => 'pending',
//             };
//             $commande->transaction_id = $transactionId;
//             $commande->save();
//         }

//         return response()->json([
//             'status' => 'success',
//             'transaction_status' => $status,
//             'commande_status' => $commande->status ?? 'not_found'
//         ]);

//     } catch (\Exception $e) {
//         Log::error('Erreur FedaPay: ' . $e->getMessage());
//         return response()->json([
//             'status' => 'error',
//             'message' => $e->getMessage()
//         ], 500);
//     }
// });

Route::post('/verify-fedapay-transaction', function (Request $request) {
    $transactionId = $request->input('transaction_id');
    $commandeId = $request->input('commande_id');

    // 🔹 Vérifie d'abord si la transaction a déjà été enregistrée
    $existTransactionId = Commande::where('transaction_id', $transactionId)->first();
    if ($existTransactionId) {
        return response()->json([
            'status' => 'errorTraitement',
            'message' => "Cette transaction existe déjà."
        ], 211);
    }

    try {
        // 🔹 Configuration FedaPay
        FedaPay::setApiKey(env('FEDAPAY_SECRET_KEY'));
        FedaPay::setEnvironment(env('FEDAPAY_ENV', 'sandbox'));

        // 🔹 Récupération de la transaction
        $transaction = Transaction::retrieve($transactionId);
        $status = $transaction->status;

        // 🔹 Mise à jour de la commande
        $commande = Commande::find($commandeId);
        if ($commande) {
            $commande->status = match ($status) {
                'approved' => 'approved',
                'declined' => 'canceled',
                default => 'pending',
            };
            $commande->transaction_id = $transactionId;
            $montant = $commande->amount;
            $commande->amount_with_taxe = round($montant * 1.07, 2); 
            $commande->transaction = json_encode($transaction);
            $commande->save();
            
            // 🔔 Envoi des emails
            try {
              
                $commande = Commande::with('product.shop')->find($commandeId);

                $customer = json_decode($commande->customer, true); 
            
                // ✅ Envoi au client
                $shopEmail = $commande->product->shop->mail ?? null;
                if ($shopEmail) {
                    Mail::to($shopEmail)->send(new CommandeStatusMail($commande, $commande->product->shop, true)); // admin = true
                }
                
                $clientEmail = $customer['email'] ?? null;
                if ($clientEmail) {
                    Mail::to($clientEmail)->send(new CommandeStatusMail($commande, $commande->product->shop, false)); // admin = false
                }
                

            } catch (\Exception $mailErr) {
                Log::error("Erreur envoi email commande #{$commande->id} : " . $mailErr->getMessage());
                // ne bloque pas le flux principal si l'email échoue
            }

            return response()->json([
                'status' => 'success',
                'transaction_status' => $status,
                'commande_status' => $commande->status,
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Commande non trouvée.',
        ], 404);

    } catch (\Exception $e) {
        Log::error('Erreur FedaPay: ' . $e->getMessage());
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});




Route::middleware('auth:sanctum')->group(function () {
    Route::get('auth/verify', [AuthController::class,'verifyToken']);
    Route::post('auth/logout', [AuthController::class,'logout']);

    Route::post('  ', function (\Illuminate\Http\Request $request){
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Déconnexion réussie']);
    });

    Route::get('profile', [AuthController::class,'profile']);
    Route::get('stats', function () {
        $stats = [
            [
                'label' => 'Membres actifs',
                'value' => number_format(User::where('status', 'active')->count(), 0, ',', ','),
                'icon' => 'Users',
                'color' => 'text-primary'
            ],
            [
                'label' => 'Boutiques',
                'value' => number_format(Shop::count(), 0, ',', ','),
                'icon' => 'Store',
                'color' => 'text-secondary'
            ],
            [
                'label' => 'Formations',
                'value' => number_format(Formation::count(), 0, ',', ','),
                'icon' => 'GraduationCap',
                'color' => 'text-accent'
            ],
            [
                'label' => 'Lives cette semaine',
                'value' => '24',
                'icon' => 'Video',
                'color' => 'text-success'
            ]
            // [
            //     'label' => 'Lives cette semaine',
            //     'value' => number_format(LiveEvent::where('date', '>=', now()->startOfWeek())
            //         ->where('date', '<=', now()->endOfWeek())
            //         ->count(), 0, ',', ','),
            //     'icon' => 'Video',
            //     'color' => 'text-success'
            // ]
        ];

        \Log::info('Stats fetched:', ['count' => count($stats)]);

        return response()->json($stats);
    });



    // Shops
    Route::get('shops', [ShopController::class, 'index']);
    Route::post('shops/create', [ShopController::class, 'store']);
    Route::get('shops/{id}/show', [ShopController::class, 'show']);
    Route::put('shops/{id}/update', [ShopController::class, 'update']);
    Route::put('shops/{id}/update-template', [ShopController::class, 'updateTemplate']);
    Route::post('shops/{id}/desactivate', [ShopController::class, 'deactivate']);
    Route::post('shops/{id}/reactivate', [ShopController::class, 'reactivate']);

    // Products
    Route::get('products/{shopId}', [ProductController::class, 'index']);
    Route::post('products/create', [ProductController::class, 'store']);
    Route::post('products/{id}/update', [ProductController::class, 'update']);
    Route::post('products/delete/{id}', [ProductController::class, 'destroy']);
    Route::post('products/import/{id}', [ProductController::class, 'import']);

    // Categories
    Route::get('categories', [CategoryController::class, 'index']);
    Route::post('categories', [CategoryController::class, 'store']);
    Route::get('categories/{id}', [CategoryController::class, 'show']);

    // Commandes
    Route::prefix('commandes')->group(function () {
        Route::get('/{shopId}', [CommandeController::class, 'index']);
        // Route::post('/create', [CommandeController::class, 'store_commande_produit']);
        Route::get('show/{id}', [CommandeController::class, 'show']);
        Route::post('/{id}/status', [CommandeController::class, 'updateStatus']);
        Route::post('update/{id}', [CommandeController::class, 'update']);
        Route::post('delete/{id}', [CommandeController::class, 'destroy']);
    });

    //Packs
    Route::prefix('packs')->group(function () {
        Route::post('/create', [PacksController::class, 'store']);
        Route::get('show/{id}', [PacksController::class, 'show']);
        Route::post('change-status/{id}', [PacksController::class, 'changeStatus']);
        Route::post('update/{id}', [PacksController::class, 'update']);
        Route::post('delete/{id}', [PacksController::class, 'destroy']);
    });

    //Chapitres
    Route::prefix('chapitres')->group(function () {
        Route::get('/', [ChapitreController::class, 'index']);
        Route::post('create', [ChapitreController::class, 'store']);
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

    // routes/api.php
    Route::post('/fedapay/create-transaction', [PaiementController::class, 'createTransaction']);

    
});
