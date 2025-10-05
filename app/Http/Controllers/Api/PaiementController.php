<?php

namespace App\Http\Controllers;

use App\Models\Paiement;
use Illuminate\Http\Request;

class PaiementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function createTransaction(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:100',
            'description' => 'required|string',
            'customer' => 'required|array',
        ]);

        // Appel API FedaPay
        $response = Http::withToken(env('FEDAPAY_SECRET_KEY')) // sk_live_xxx
            ->post('https://sandbox-api.fedapay.com/v1/transactions', [
                'transaction' => [
                    'amount' => $validated['amount'],
                    'description' => $validated['description'],
                    'currency' => ['iso' => 'XOF'],
                ],
                'customer' => $validated['customer'],
            ]);

        if ($response->failed()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la transaction',
                'error' => $response->json(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'transaction' => $response->json()['v1/transaction'],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Paiement $paiement)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Paiement $paiement)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Paiement $paiement)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Paiement $paiement)
    {
        //
    }
}
